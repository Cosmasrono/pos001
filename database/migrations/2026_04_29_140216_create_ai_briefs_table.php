<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ai_briefs')) {
            Schema::create('ai_briefs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->date('brief_date');
                $table->text('content');
                $table->text('input_summary')->nullable();
                $table->string('model')->nullable();
                $table->integer('tokens_used')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('no action');
                $table->unique(['company_id', 'brief_date'], 'ai_briefs_company_date_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_briefs');
    }
};
