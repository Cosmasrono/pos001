<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'purchase_unit')) {
                $table->string('purchase_unit', 50)->default('unit')->after('selling_price');
            }

            if (!Schema::hasColumn('products', 'selling_unit')) {
                $table->string('selling_unit', 50)->default('unit')->after('purchase_unit');
            }

            if (!Schema::hasColumn('products', 'units_per_purchase_unit')) {
                $table->integer('units_per_purchase_unit')->default(1)->after('selling_unit');
            }

            if (!Schema::hasColumn('products', 'purchase_price')) {
                $table->decimal('purchase_price', 12, 2)->nullable()->after('units_per_purchase_unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['purchase_price', 'units_per_purchase_unit', 'selling_unit', 'purchase_unit'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
