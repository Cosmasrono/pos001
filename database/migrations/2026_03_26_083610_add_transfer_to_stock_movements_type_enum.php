<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlsrv') {
            // SQL Server: drop CHECK constraint and recreate with new values
            $constraint = DB::selectOne("
                SELECT name 
                FROM sys.check_constraints 
                WHERE parent_object_id = OBJECT_ID('stock_movements') 
                AND definition LIKE '%type%'
            ");

            if ($constraint) {
                DB::statement("ALTER TABLE stock_movements DROP CONSTRAINT [{$constraint->name}]");
            }

            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('type', 50)->change();
            });

            DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT [CK_stock_movements_type] 
                CHECK ([type] IN ('purchase', 'sale', 'adjustment', 'damage', 'return', 'transfer', 'restock'))");
        } else {
            // MySQL / MariaDB: just modify the ENUM directly
            DB::statement("ALTER TABLE stock_movements 
                MODIFY COLUMN type ENUM('purchase', 'sale', 'adjustment', 'damage', 'return', 'transfer', 'restock') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlsrv') {
            DB::statement("ALTER TABLE stock_movements DROP CONSTRAINT [CK_stock_movements_type]");

            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('type', 50)->change();
            });

            DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT [CK_stock_movements_type_orig] 
                CHECK ([type] IN ('purchase', 'sale', 'adjustment', 'damage', 'return'))");
        } else {
            DB::statement("ALTER TABLE stock_movements 
                MODIFY COLUMN type ENUM('purchase', 'sale', 'adjustment', 'damage', 'return') NOT NULL");
        }
    }
};