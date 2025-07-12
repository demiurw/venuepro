<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class RoomAvailability extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'room_availability';

    protected $fillable = [
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'company_id',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
