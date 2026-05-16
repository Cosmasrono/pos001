<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Drop the global unique index on code alone
            $table->dropUnique('branches_code_unique');
            // Codes must only be unique within a company
            $table->unique(['company_id', 'code'], 'branches_company_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_company_code_unique');
            $table->unique('code', 'branches_code_unique');
        });
    }
};
