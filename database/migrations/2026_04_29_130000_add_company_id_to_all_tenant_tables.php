<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    private array $tables = [
        'branches', 'sales', 'customers', 'shifts',
        'categories', 'suppliers', 'purchase_orders',
        'expenses', 'expense_categories', 'invoices',
        'loans', 'promotions',
    ];

    public function up(): void
    {
        // 1. Add company_id to every table that doesn't have it
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->unsignedBigInteger('company_id')->nullable();
                });
            }
        }

        // 2. Ensure a Default Company (id=1) exists for pre-existing data
        $existing = DB::table('companies')->where('slug', 'default-company')->first();
        if (!$existing) {
            DB::table('companies')->insert([
                'name'                    => 'Default Company',
                'slug'                    => 'default-company',
                'email'                   => 'admin@default.local',
                'is_active'               => true,
                'subscription_status'     => 'active',
                'subscription_expires_at' => Carbon::now()->addYears(10),
                'currency'                => 'KES',
                'timezone'                => 'Africa/Nairobi',
                'country'                 => 'KE',
                'mpesa_environment'       => 'sandbox',
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);
        }

        $defaultCompanyId = DB::table('companies')->where('slug', 'default-company')->value('id');

        // 3. Backfill all NULL company_id rows to the Default Company
        $allTables = array_merge($this->tables, ['products']);
        foreach ($allTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }
        }

        // 4. Backfill users that have no company_id
        if (Schema::hasColumn('users', 'company_id')) {
            // Find the owner of the default company
            $ownerId = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->whereIn('roles.name', ['owner', 'super_admin'])
                ->value('role_user.user_id');

            DB::table('users')->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);

            if ($ownerId) {
                DB::table('companies')->where('id', $defaultCompanyId)->update(['owner_id' => $ownerId]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('company_id');
                });
            }
        }
        DB::table('companies')->where('slug', 'default-company')->delete();
    }
};
