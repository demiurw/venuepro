<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class SystemSetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'key',
        'value',
        'company_id',
    ];
}
