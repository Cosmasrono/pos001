<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SuspendExpiredTrials extends Command
{
    protected $signature = 'companies:suspend-expired-trials';
    protected $description = 'Suspend companies whose free trial has expired';

    public function handle(): int
    {
        $expired = Company::where('subscription_status', 'trial')
            ->where('trial_ends_at', '<=', Carbon::now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired trials found.');
            return self::SUCCESS;
        }

        foreach ($expired as $company) {
            $company->update(['subscription_status' => 'suspended']);
            $this->line("Suspended: {$company->name} (trial ended {$company->trial_ends_at->toDateString()})");
        }

        $this->info("Suspended {$expired->count()} company/companies.");
        return self::SUCCESS;
    }
}
