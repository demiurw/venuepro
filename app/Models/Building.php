<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Building extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'address',
        'company_id',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
