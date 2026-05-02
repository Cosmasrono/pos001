<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();

                // Identity
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();

                // Status & subscription
                $table->boolean('is_active')->default(true);
                $table->string('subscription_status')->default('trial');
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('subscription_expires_at')->nullable();

                // Localization
                $table->string('currency', 3)->default('KES');
                $table->string('timezone')->default('Africa/Nairobi');
                $table->string('country', 2)->default('KE');

                // M-Pesa per company (encrypted via model casts)
                $table->text('mpesa_consumer_key')->nullable();
                $table->text('mpesa_consumer_secret')->nullable();
                $table->string('mpesa_shortcode')->nullable();
                $table->text('mpesa_passkey')->nullable();
                $table->string('mpesa_environment')->default('sandbox');

                // Owner
                $table->unsignedBigInteger('owner_id')->nullable();

                $table->timestamps();

                $table->index('subscription_status');
                $table->index('is_active');
                $table->index('owner_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};