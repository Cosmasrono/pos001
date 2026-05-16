<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $company = auth()->user()->company;

        $plans = [
            'monthly' => [
                'label'       => 'Monthly',
                'amount'      => config('mpesa.monthly_amount', 1500),
                'description' => 'Billed every month. Cancel anytime.',
                'duration'    => '1 month',
            ],
            'annual' => [
                'label'       => 'Annual',
                'amount'      => config('mpesa.annual_amount', 15000),
                'description' => 'Best value — save 2 months.',
                'duration'    => '12 months',
            ],
        ];

        return view('subscription.plans', compact('company', 'plans'));
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'plan'  => 'required|in:monthly,annual',
            'phone' => ['required', 'string', 'regex:/^(\+?254|0)[17]\d{8}$/'],
        ], [
            'phone.regex' => 'Enter a valid Safaricom number (e.g. 07XX or 01XX).',
        ]);

        $company = auth()->user()->company;
        $plan    = $request->plan;
        $amount  = $plan === 'annual'
            ? config('mpesa.annual_amount', 15000)
            : config('mpesa.monthly_amount', 1500);

        $payment = SubscriptionPayment::create([
            'company_id' => $company->id,
            'plan'       => $plan,
            'amount'     => $amount,
            'phone'      => $request->phone,
            'status'     => 'pending',
        ]);

        try {
            $result = app(MpesaService::class)->initiateSubscriptionPush(
                $request->phone,
                $amount,
                'WingPOS-' . $company->id,
                ucfirst($plan) . ' Subscription'
            );

            if (($result['ResponseCode'] ?? null) === '0') {
                $payment->update([
                    'checkout_request_id' => $result['CheckoutRequestID'],
                    'merchant_request_id' => $result['MerchantRequestID'],
                ]);

                return redirect()->route('subscribe.pending', $payment->id);
            }

            $reason = $result['errorMessage'] ?? ($result['ResponseDescription'] ?? 'STK Push failed');
            $payment->update(['status' => 'failed', 'failure_reason' => $reason]);

            return back()->withInput()->with('error', "Could not send M-Pesa prompt: {$reason}");

        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed', 'failure_reason' => $e->getMessage()]);
            Log::error('Subscription STK Push failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Payment initiation failed. Please try again.');
        }
    }

    public function pending(SubscriptionPayment $payment)
    {
        abort_if($payment->company_id !== auth()->user()->company->id, 403);

        return view('subscription.pending', compact('payment'));
    }

    public function checkStatus(SubscriptionPayment $payment)
    {
        abort_if($payment->company_id !== auth()->user()->company->id, 403);

        if ($payment->status === 'completed') {
            return response()->json(['status' => 'completed']);
        }

        if ($payment->status === 'failed') {
            return response()->json(['status' => 'failed', 'reason' => $payment->failure_reason]);
        }

        // Poll Daraja if we have a checkout request ID
        if ($payment->checkout_request_id) {
            try {
                $result = app(MpesaService::class)->querySubscriptionStatus($payment->checkout_request_id);

                $resultCode = $result['ResultCode'] ?? null;

                if ($resultCode === '0' || $resultCode === 0) {
                    $this->activateSubscription($payment);
                    return response()->json(['status' => 'completed']);
                }

                if ($resultCode !== null && $resultCode != 0) {
                    $reason = $result['ResultDesc'] ?? 'Payment declined.';
                    $payment->update(['status' => 'failed', 'failure_reason' => $reason]);
                    return response()->json(['status' => 'failed', 'reason' => $reason]);
                }
            } catch (\Throwable $e) {
                // Silently ignore — rely on callback as the source of truth
            }
        }

        return response()->json(['status' => 'pending']);
    }

    public function callback(Request $request)
    {
        $payload = $request->all();
        Log::info('M-Pesa subscription callback', $payload);

        $stk = $payload['Body']['stkCallback'] ?? null;
        if (!$stk) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $checkoutRequestId = $stk['CheckoutRequestID'] ?? null;
        $resultCode        = $stk['ResultCode'] ?? null;

        $payment = SubscriptionPayment::where('checkout_request_id', $checkoutRequestId)->first();
        if (!$payment) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        if ((int) $resultCode === 0) {
            $items      = collect($stk['CallbackMetadata']['Item'] ?? []);
            $receiptNo  = $items->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;

            $payment->update([
                'mpesa_receipt_number' => $receiptNo,
                'status'               => 'completed',
                'paid_at'              => now(),
            ]);

            $this->activateSubscription($payment);
        } else {
            $payment->update([
                'status'         => 'failed',
                'failure_reason' => $stk['ResultDesc'] ?? 'Payment failed',
            ]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    private function activateSubscription(SubscriptionPayment $payment): void
    {
        $expiresAt = $payment->plan === 'annual'
            ? now()->addYear()
            : now()->addMonth();

        $payment->company->update([
            'subscription_status'    => 'active',
            'subscription_expires_at' => $expiresAt,
        ]);

        Log::info('Subscription activated', [
            'company_id' => $payment->company_id,
            'plan'       => $payment->plan,
            'expires_at' => $expiresAt,
        ]);
    }
}
