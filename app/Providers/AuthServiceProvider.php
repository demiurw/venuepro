<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Policies\CompanyPolicy;
use App\Policies\BookingPolicy;
use App\Models\Company;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyPolicy::class,
        // Add other model => policy mappings here
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register gates for booking permissions
        Gate::define('create-bookings', [BookingPolicy::class, 'create']);
        Gate::define('modify-bookings', [BookingPolicy::class, 'modify']);
        Gate::define('invite-attendees', [BookingPolicy::class, 'inviteAttendees']);
        Gate::define('view-group-bookings', [BookingPolicy::class, 'viewGroupBookings']);
        Gate::define('manage-group-bookings', [BookingPolicy::class, 'manageGroupBookings']);
        Gate::define('book-on-behalf-of-group-members', [BookingPolicy::class, 'bookOnBehalfOfGroupMembers']);
        Gate::define('transfer-group-bookings', [BookingPolicy::class, 'transferGroupBookings']);
        Gate::define('view-public-rooms', [BookingPolicy::class, 'viewPublicRooms']);
        Gate::define('respond-to-invites', [BookingPolicy::class, 'respondToInvites']);
        Gate::define('view-meeting-details', [BookingPolicy::class, 'viewMeetingDetails']);

        // Register gates for company permissions
        Gate::define('manage-settings', [CompanyPolicy::class, 'manageSettings']);
        Gate::define('manage-buildings', [CompanyPolicy::class, 'manageBuildings']);
        Gate::define('manage-rooms', [CompanyPolicy::class, 'manageRooms']);
        Gate::define('manage-users', [CompanyPolicy::class, 'manageUsers']);
        Gate::define('manage-billing', [CompanyPolicy::class, 'manageBilling']);
        Gate::define('book-on-behalf-of-users', [CompanyPolicy::class, 'bookOnBehalfOfUsers']);
        Gate::define('transfer-all-bookings', [CompanyPolicy::class, 'transferAllBookings']);
    }
}
