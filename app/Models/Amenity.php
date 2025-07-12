<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Amenity extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'company_id',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_amenity');
    }
}
