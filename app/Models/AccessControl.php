<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class AccessControl extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'access_control';

    protected $fillable = [
        'group_id',
        'resource_id',
        'resource_type', // e.g., 'App\\Models\\Room'
        'company_id',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function resource()
    {
        return $this->morphTo();
    }
}
