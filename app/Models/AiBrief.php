<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiBrief extends Model
{
    use HasFactory;
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id',
        'brief_date',
        'content',
        'input_summary',
        'model',
        'tokens_used',
    ];

    protected $casts = [
        'brief_date' => 'date',
    ];
}
