<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Content extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'type', // e.g., 'page', 'post'
        'status',
        'company_id',
    ];
}
