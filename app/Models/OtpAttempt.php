<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class OtpAttempt extends Model
{
    use HasFactory, BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'user_id',
        'otp_code',
        'expires_at',
        'is_used',
        'attempt_count',
        'last_attempt_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_attempt_at' => 'datetime',
        'is_used' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_used', false)
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    // Helper methods
    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    public function isActive()
    {
        return !$this->is_used && !$this->isExpired();
    }

    public function markAsUsed()
    {
        $this->update([
            'is_used' => true,
            'last_attempt_at' => now(),
        ]);
    }

    public function incrementAttempt()
    {
        $this->increment('attempt_count');
        $this->update(['last_attempt_at' => now()]);
    }

    public function hasExceededMaxAttempts($maxAttempts = 3)
    {
        return $this->attempt_count >= $maxAttempts;
    }
}
