<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get states that belong to this country
     */
    public function states()
    {
        return $this->hasMany(State::class);
    }

    /**
     * Scope to get only active countries
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
