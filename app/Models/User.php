<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;
use Spatie\Permission\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'role_id',
        'group_id',
        'company_id',
        'status',
        'user_type',
        'auth_method',
        'email_verification_token',
        'email_verified_at',
        'otp_secret',
        'oauth_providers',
        'oauth_id',
        'last_login_at',
    ];

    protected $hidden = [
        'otp_secret',
        'email_verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'oauth_providers' => 'array',
    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function otpAttempts()
    {
        return $this->hasMany(OtpAttempt::class);
    }

    public function activeOtpAttempts()
    {
        return $this->hasMany(OtpAttempt::class)
            ->where('is_used', false)
            ->where('expires_at', '>', now());
    }

    // Helper methods for OTP authentication
    public function isOtpUser()
    {
        return $this->auth_method === 'otp';
    }

    public function canReceiveOtp()
    {
        return $this->isOtpUser() && $this->status === 'active';
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
