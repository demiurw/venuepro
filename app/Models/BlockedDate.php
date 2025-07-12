<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class BlockedDate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'room_id',
        'date',
        'reason',
        'company_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
