<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['product_branch_stocks', 'stock_movements', 'sale_items'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('company_id')->nullable();
                    $t->index('company_id');
                });
            }
        }

        $this->backfill('product_branch_stocks', 'products', 'product_id');
        $this->backfill('stock_movements', 'products', 'product_id');
        $this->backfill('sale_items', 'sales', 'sale_id');

        $defaultCompanyId = DB::table('companies')
            ->whereIn('slug', ['default-company', 'default'])
            ->value('id');

        if ($defaultCompanyId) {
            foreach ($this->tables as $table) {
                if (Schema::hasColumn($table, 'company_id')) {
                    DB::table($table)
                        ->whereNull('company_id')
                        ->update(['company_id' => $defaultCompanyId]);
                }
            }
        }
    }

    private function backfill(string $childTable, string $parentTable, string $foreignKey): void
    {
        if (! Schema::hasColumn($childTable, 'company_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlsrv') {
            DB::statement("
                UPDATE c
                SET c.company_id = p.company_id
                FROM {$childTable} c
                INNER JOIN {$parentTable} p ON p.id = c.{$foreignKey}
                WHERE c.company_id IS NULL
            ");
        } elseif ($driver === 'pgsql') {
            DB::statement("
                UPDATE {$childTable} AS c
                SET company_id = p.company_id
                FROM {$parentTable} AS p
                WHERE p.id = c.{$foreignKey}
                  AND c.company_id IS NULL
            ");
        } else {
            DB::statement("
                UPDATE {$childTable} c
                INNER JOIN {$parentTable} p ON p.id = c.{$foreignKey}
                SET c.company_id = p.company_id
                WHERE c.company_id IS NULL
            ");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    try {
                        $t->dropIndex($table . '_company_id_index');
                    } catch (\Exception $e) {
                    }

                    $t->dropColumn('company_id');
                });
            }
        }
    }
};