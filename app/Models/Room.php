<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Room extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'room';


    protected $fillable = [
        'name',
        'building_id',
        'capacity',
        'type',
        'company_id',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenity');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
