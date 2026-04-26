<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Drop existing unique constraint if it exists
        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropUnique('products_barcode_unique');
            } catch (\Exception $e) {
                // Constraint might not exist - that's fine
            }
        });

        if ($driver === 'sqlsrv') {
            // SQL Server: filtered unique index (allows multiple NULLs)
            DB::statement('
                CREATE UNIQUE INDEX products_barcode_unique 
                ON products(barcode) 
                WHERE barcode IS NOT NULL
            ');
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: partial unique index
            DB::statement('
                CREATE UNIQUE INDEX products_barcode_unique 
                ON products(barcode) 
                WHERE barcode IS NOT NULL
            ');
        } else {
            // MySQL / MariaDB: regular unique index 
            // (NULL values are already treated as distinct in MySQL unique indexes)
            Schema::table('products', function (Blueprint $table) {
                $table->unique('barcode', 'products_barcode_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['sqlsrv', 'pgsql'])) {
            DB::statement('DROP INDEX IF EXISTS products_barcode_unique ON products');
        } else {
            Schema::table('products', function (Blueprint $table) {
                try {
                    $table->dropUnique('products_barcode_unique');
                } catch (\Exception $e) {
                    // Index might not exist
                }
            });
        }
    }
};