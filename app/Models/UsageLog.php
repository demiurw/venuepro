<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class UsageLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'feature',
        'usage_count',
        'log_date',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
