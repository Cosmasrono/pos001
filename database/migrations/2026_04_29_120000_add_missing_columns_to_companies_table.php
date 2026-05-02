<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
            if (!Schema::hasColumn('companies', 'email')) {
                $table->string('email')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('companies', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('companies', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('companies', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('address');
            }
            if (!Schema::hasColumn('companies', 'subscription_status')) {
                $table->string('subscription_status')->default('trial')->after('is_active');
            }
            if (!Schema::hasColumn('companies', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            }
            if (!Schema::hasColumn('companies', 'subscription_expires_at')) {
                $table->timestamp('subscription_expires_at')->nullable()->after('trial_ends_at');
            }
            if (!Schema::hasColumn('companies', 'currency')) {
                $table->string('currency', 3)->default('KES')->after('subscription_expires_at');
            }
            if (!Schema::hasColumn('companies', 'timezone')) {
                $table->string('timezone')->default('Africa/Nairobi')->after('currency');
            }
            if (!Schema::hasColumn('companies', 'country')) {
                $table->string('country', 2)->default('KE')->after('timezone');
            }
            if (!Schema::hasColumn('companies', 'mpesa_consumer_key')) {
                $table->text('mpesa_consumer_key')->nullable()->after('country');
            }
            if (!Schema::hasColumn('companies', 'mpesa_consumer_secret')) {
                $table->text('mpesa_consumer_secret')->nullable()->after('mpesa_consumer_key');
            }
            if (!Schema::hasColumn('companies', 'mpesa_shortcode')) {
                $table->string('mpesa_shortcode')->nullable()->after('mpesa_consumer_secret');
            }
            if (!Schema::hasColumn('companies', 'mpesa_passkey')) {
                $table->text('mpesa_passkey')->nullable()->after('mpesa_shortcode');
            }
            if (!Schema::hasColumn('companies', 'mpesa_environment')) {
                $table->string('mpesa_environment')->default('sandbox')->after('mpesa_passkey');
            }
            if (!Schema::hasColumn('companies', 'owner_id')) {
                $table->unsignedBigInteger('owner_id')->nullable()->after('mpesa_environment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $cols = [
                'slug', 'email', 'phone', 'address', 'is_active',
                'subscription_status', 'trial_ends_at', 'subscription_expires_at',
                'currency', 'timezone', 'country',
                'mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_shortcode',
                'mpesa_passkey', 'mpesa_environment', 'owner_id',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('companies', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
