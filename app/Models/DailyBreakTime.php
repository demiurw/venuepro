<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class DailyBreakTime extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'room_availability_id',
        'start_time',
        'end_time',
        'company_id',
    ];

    public function roomAvailability()
    {
        return $this->belongsTo(RoomAvailability::class);
    }
}
