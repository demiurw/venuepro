<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Booking extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'room_id',
        'title',
        'description',
        'date',
        'start_time',
        'end_time',
        'status',
        'booking_type',
        'created_by',
        'booked_for_user_id',
        'delegation_type',
        'external_reference',
        'is_all_day',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
        'recurring_pattern',
        'parent_booking_id',
        'invoice_id',
        'has_been_billed',
        'check_in_time',
        'check_out_time',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'cancelled_at' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'recurring_pattern' => 'json',
        'is_all_day' => 'boolean',
        'has_been_billed' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookedForUser()
    {
        return $this->belongsTo(User::class, 'booked_for_user_id');
    }

    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function attendees()
    {
        return $this->belongsToMany(User::class, 'booking_attendee');
    }
}
