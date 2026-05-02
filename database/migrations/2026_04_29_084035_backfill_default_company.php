<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('companies')) {
            return;
        }

        if (DB::table('companies')->first()) {
            return; // already has data
        }

        $companyId = DB::table('companies')->insertGetId([
            'name'                    => 'Default Company',
            'slug'                    => 'default',
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

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'company_id')) {
            DB::table('users')->whereNull('company_id')->update(['company_id' => $companyId]);
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'company_id')) {
            DB::table('products')->whereNull('company_id')->update(['company_id' => $companyId]);
        }

        if (Schema::hasTable('role_user') && Schema::hasTable('roles')) {
            $ownerRoleId = DB::table('roles')->where('name', 'owner')->value('id');
            if ($ownerRoleId) {
                $firstOwnerUserId = DB::table('role_user')
                    ->where('role_id', $ownerRoleId)
                    ->value('user_id');

                if ($firstOwnerUserId) {
                    DB::table('companies')
                        ->where('id', $companyId)
                        ->update(['owner_id' => $firstOwnerUserId]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('companies')) {
            DB::table('companies')->where('slug', 'default')->delete();
        }
    }
};