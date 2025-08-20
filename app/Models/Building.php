<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Building extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'building';


    protected $fillable = [
        'name',
        'description',
        'address_line1',
        'address_line2',
        'city',
        'state_id',
        'country_id',
        'postal_code',
        'timezone',
        'buffer_time_minutes',
        'is_active',
        'company_id',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
