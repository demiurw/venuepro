<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Booking extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'room_id',
        'user_id',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'company_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'booking_attendee');
    }
}
