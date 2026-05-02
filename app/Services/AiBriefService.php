<?php

namespace App\Services;

use App\Models\AiBrief;
use App\Models\Company;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiBriefService
{
    public function getOrGenerateToday(Company $company): ?AiBrief
    {
        $today = Carbon::today();

        $existing = AiBrief::withoutCompanyScope()
            ->where('company_id', $company->id)
            ->where('brief_date', $today)
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            $stats      = $this->gatherStats($company);
            $aiResponse = $this->callGroq($stats);

            if (!$aiResponse) {
                return null;
            }

            return AiBrief::create([
                'company_id'    => $company->id,
                'brief_date'    => $today,
                'content'       => $aiResponse['content'],
                'input_summary' => json_encode($stats),
                'model'         => $aiResponse['model'] ?? 'unknown',
                'tokens_used'   => $aiResponse['tokens_used'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI brief generation failed', [
                'company_id' => $company->id,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function gatherStats(Company $company): array
    {
        $companyId = $company->id;
        $yesterday = Carbon::yesterday();
        $weekAgo   = Carbon::today()->subDays(7);
        $monthAgo  = Carbon::today()->subDays(30);

        $salesYesterday = Sale::withoutCompanyScope()
            ->where('company_id', $companyId)
            ->whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total')
            ->first();

        $salesDayBefore = Sale::withoutCompanyScope()
            ->where('company_id', $companyId)
            ->whereDate('created_at', $yesterday->copy()->subDay())
            ->where('status', 'completed')
            ->sum('total_amount');

        $salesThisWeek = Sale::withoutCompanyScope()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $weekAgo)
            ->where('status', 'completed')
            ->sum('total_amount');

        $salesThisMonth = Sale::withoutCompanyScope()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', $monthAgo)
            ->where('status', 'completed')
            ->sum('total_amount');

        $topProductsYesterday = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.company_id', $companyId)
            ->whereDate('sales.created_at', $yesterday)
            ->where('sales.status', 'completed')
            ->selectRaw('products.name, SUM(sale_items.subtotal) as revenue')
            ->groupBy('products.name')
            ->orderByDesc('revenue')
            ->limit(3)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'revenue' => round($r->revenue, 0)])
            ->toArray();

        $lowStock = Product::withoutCompanyScope()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereRaw('quantity_in_stock <= COALESCE(reorder_level, 0)')
            ->where('quantity_in_stock', '>', 0)
            ->orderByRaw('(quantity_in_stock - COALESCE(reorder_level, 0))')
            ->limit(5)
            ->get(['name', 'quantity_in_stock', 'reorder_level'])
            ->map(fn($p) => [
                'name'          => $p->name,
                'quantity'      => (int) $p->quantity_in_stock,
                'reorder_level' => (int) ($p->reorder_level ?? 0),
            ])
            ->toArray();

        $outOfStock = Product::withoutCompanyScope()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('quantity_in_stock', 0)
            ->count();

        $inactiveCustomers = DB::table('customers')
            ->where('customers.company_id', $companyId)
            ->leftJoin('sales', function ($join) use ($monthAgo) {
                $join->on('sales.customer_id', '=', 'customers.id')
                     ->where('sales.created_at', '>=', $monthAgo);
            })
            ->whereNull('sales.id')
            ->select('customers.name')
            ->limit(3)
            ->get()
            ->pluck('name')
            ->toArray();

        $trendPct = $salesDayBefore > 0
            ? round((($salesYesterday->total - $salesDayBefore) / $salesDayBefore) * 100, 1)
            : null;

        return [
            'shop_name'              => $company->name,
            'currency'               => $company->currency ?? 'KES',
            'date'                   => Carbon::today()->format('l, j F Y'),
            'sales_yesterday_count'  => (int) $salesYesterday->count,
            'sales_yesterday_total'  => round($salesYesterday->total, 0),
            'sales_day_before_total' => round($salesDayBefore, 0),
            'sales_trend_percent'    => $trendPct,
            'sales_this_week'        => round($salesThisWeek, 0),
            'sales_this_month'       => round($salesThisMonth, 0),
            'top_products_yesterday' => $topProductsYesterday,
            'low_stock_items'        => $lowStock,
            'out_of_stock_count'     => $outOfStock,
            'inactive_customers'     => $inactiveCustomers,
        ];
    }

    private function callGroq(array $stats): ?array
    {
        $apiKey = env('GROQ_API_KEY');
        if (!$apiKey) {
            Log::warning('GROQ_API_KEY missing — cannot generate AI brief');
            return null;
        }

        $systemPrompt = <<<PROMPT
You are a friendly, practical business advisor for a small retail shop in Kenya.
You write daily briefs for the shop owner — concise, actionable, and warm.

Style rules:
- Write 4-6 short bullet points, each starting with a relevant emoji.
- Use the currency code provided (e.g. KES) before any money number.
- Keep each point to ONE sentence — under 25 words.
- Be specific: name products and customers from the data, never invent any.
- If sales went up, celebrate. If down, be supportive but practical.
- End with ONE clear action the owner could take today.
- Do NOT use markdown headings, asterisks, or formatting — just bullet points.
- Output the bullets only — no preamble, no signature.
PROMPT;

        $userPrompt = "Here is today's data for the shop:\n\n"
            . json_encode($stats, JSON_PRETTY_PRINT)
            . "\n\nWrite the daily brief now.";

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => 'llama-3.3-70b-versatile',
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt],
                ],
                'temperature' => 0.6,
                'max_tokens'  => 600,
            ]);

            if (!$response->successful()) {
                Log::warning('Groq API call failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return null;
            }

            $data    = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                return null;
            }

            return [
                'content'     => trim($content),
                'model'       => $data['model'] ?? 'unknown',
                'tokens_used' => $data['usage']['total_tokens'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Groq call exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
