<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeBotRegistrations extends Command
{
    protected $signature   = 'bots:purge {--dry-run : Show what would be deleted without deleting}';
    protected $description = 'Delete companies whose owner never verified their email and never logged in';

    public function handle(): int
    {
        // Bot accounts: owner never logged in AND no products/sales AND older than 1 hour
        $botCompanies = Company::whereHas('owner', function ($q) {
            $q->whereNull('last_login_at');
        })
        ->doesntHave('products')
        ->doesntHave('branches.sales')
        ->where('created_at', '<', now()->subHour())
        ->with('owner')
        ->get();

        if ($botCompanies->isEmpty()) {
            $this->info('No bot registrations found.');
            return self::SUCCESS;
        }

        $this->warn("Found {$botCompanies->count()} suspected bot registration(s):");

        $rows = $botCompanies->map(fn($c) => [
            $c->name,
            $c->owner?->email ?? '—',
            $c->created_at->toDateString(),
        ])->toArray();

        $this->table(['Shop', 'Owner Email', 'Registered'], $rows);

        if ($this->option('dry-run')) {
            $this->info('Dry run — nothing deleted.');
            return self::SUCCESS;
        }

        if (!$this->confirm("Delete all {$botCompanies->count()} bot account(s)? This cannot be undone.")) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($botCompanies as $company) {
            DB::transaction(function () use ($company) {
                $companyId = $company->id;

                // Delete in dependency order to avoid FK violations
                DB::table('branch_product')->whereIn(
                    'branch_id',
                    DB::table('branches')->where('company_id', $companyId)->pluck('id')
                )->delete();

                DB::table('sale_items')->whereIn(
                    'sale_id',
                    DB::table('sales')->where('company_id', $companyId)->pluck('id')
                )->delete();

                DB::table('sales')->where('company_id', $companyId)->delete();
                DB::table('products')->where('company_id', $companyId)->delete();
                DB::table('branches')->where('company_id', $companyId)->delete();

                // Remove role pivot before deleting users
                $userIds = DB::table('users')->where('company_id', $companyId)->pluck('id');
                DB::table('role_user')->whereIn('user_id', $userIds)->delete();
                DB::table('users')->where('company_id', $companyId)->delete();

                DB::table('companies')->where('id', $companyId)->delete();
            });

            $this->line("  Deleted: {$company->name} ({$company->owner?->email})");
            $deleted++;
        }

        $this->info("Done. Deleted {$deleted} bot account(s).");
        return self::SUCCESS;
    }
}
