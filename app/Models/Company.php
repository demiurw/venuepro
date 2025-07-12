<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Tenant;

class Company extends Tenant
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'subscription_level',
        'subscription_expires_at',
        'trial_ends_at',
        'max_users',
        'max_rooms',
        'allow_external_bookings',
        'external_booking_domain_whitelist',
        'contact_email',
        'contact_phone',
        'billing_email',
        'billing_contact_name',
        'stripe_customer_id',
        'payment_method_id',
        'address_line1',
        'address_line2',
        'city',
        'state_id',
        'country_id',
        'postal_code',
        'is_active',
    ];

    /**
     * Get the users associated with the company.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
