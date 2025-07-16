<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, BelongsToTenant, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'otp_secret',
        'remember_token',
        'email_verification_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'oauth_providers' => 'array',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }






    public function getUserTypeDisplayNameAttribute()
    {
        return UserNavigationHelper::getUserTypeDisplayName($this);
    }

    public function getNavigationMenuAttribute()
    {
        return UserNavigationHelper::getNavigationMenu($this);
    }

    public function getDashboardRouteAttribute()
    {
        return UserNavigationHelper::getDashboardRoute($this->user_type);
    }

    public function canAccessFeature(string $feature): bool
    {
        return UserNavigationHelper::canAccessFeature($this, $feature);
    }
    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the user's display name (for UI purposes).
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Check if the user is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isOtpUser()
    {
        return $this->usesOtpAuth();
    }

    /**
     * Check if the user uses OTP authentication.
     *
     * @return bool
     */
    public function usesOtpAuth()
    {
        return $this->auth_method === 'otp';
    }

    /**
     * Check if the user uses OAuth authentication.
     *
     * @return bool
     */
    public function usesOAuthAuth()
    {
        return $this->auth_method === 'oauth';
    }

    /**
     * Relationship: User belongs to a role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relationship: User belongs to a group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Relationship: User has many bookings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Relationship: User can attend many bookings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function attendingBookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_attendee')
            ->withTimestamps();
    }

    /**
     * Relationship: User belongs to many groups (through group_member pivot).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_member')
            ->withPivot(['role', 'added_by'])
            ->withTimestamps();
    }

    /**
     * Relationship: User has many audit logs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Relationship: User has many OTP attempts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function otpAttempts()
    {
        return $this->hasMany(OtpAttempt::class);
    }

    /**
     * Relationship: User has many active (unused and non-expired) OTP attempts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeOtpAttempts()
    {
        return $this->hasMany(OtpAttempt::class)
            ->where('is_used', false)
            ->where('expires_at', '>', now());
    }

    /**
     * Relationship: User has many notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Record last login timestamp.
     *
     * @return void
     */
    public function recordLogin()
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Generate a new email verification token.
     *
     * @return string
     */
    public function generateEmailVerificationToken()
    {
        $token = bin2hex(random_bytes(50));
        $this->update(['email_verification_token' => $token]);
        return $token;
    }

    /**
     * Mark email as verified.
     *
     * @return void
     */
    public function markEmailAsVerified()
    {
        $this->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);
    }

    /**
     * Check if user has verified their email.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Generate and store OTP secret for TOTP.
     *
     * @return string
     */
    public function generateOtpSecret()
    {
        $secret = bin2hex(random_bytes(20));
        $this->update(['otp_secret' => $secret]);
        return $secret;
    }

    /**
     * Check if user has an OTP secret configured.
     *
     * @return bool
     */
    public function hasOtpSecret()
    {
        return !is_null($this->otp_secret);
    }
}
