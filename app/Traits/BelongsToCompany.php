<?php

namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $companyId = app()->bound('current_company_id') ? app('current_company_id') : null;

            if ($companyId === null) {
                $builder->whereRaw('1 = 0');
                return;
            }

            $table = $builder->getModel()->getTable();
            $builder->where("{$table}.company_id", $companyId);
        });

        static::creating(function (Model $model) {
            if (empty($model->company_id)) {
                $companyId = app()->bound('current_company_id') ? app('current_company_id') : null;
                if ($companyId !== null) {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function withoutCompanyScope(): Builder
    {
        return static::query()->withoutGlobalScope('company');
    }
}