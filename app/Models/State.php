<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'country_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the country that owns this state
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Scope to get only active states
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
