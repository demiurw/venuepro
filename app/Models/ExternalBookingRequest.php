<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ExternalBookingRequest extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'room_id',
        'requester_name',
        'requester_email',
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
}
