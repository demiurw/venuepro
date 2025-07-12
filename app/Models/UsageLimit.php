<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class UsageLimit extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'feature', // e.g., 'users', 'rooms', 'bookings'
        'limit',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
