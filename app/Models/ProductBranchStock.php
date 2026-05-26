<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBranchStock extends Model
{
    use \App\Traits\Auditable, \App\Traits\BranchScoped, \App\Traits\BelongsToCompany;

    protected $table = 'product_branch_stocks';

    protected $fillable = [
        'company_id',
        'product_id',
        'branch_id',
        'quantity_in_stock',
        'initial_allocation',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'product_id' => 'integer',
        'branch_id' => 'integer',
        'quantity_in_stock' => 'integer',
        'initial_allocation' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}