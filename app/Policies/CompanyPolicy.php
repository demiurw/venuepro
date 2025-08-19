<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompanyPolicy
{
    /**
     * Determine whether the user can manage company settings.
     */
    public function manageSettings(User $user): bool
    {
        return $user->can('manage_company_settings');
    }

    /**
     * Determine whether the user can manage buildings.
     */
    public function manageBuildings(User $user): bool
    {
        return $user->can('manage_buildings');
    }

    /**
     * Determine whether the user can manage rooms.
     */
    public function manageRooms(User $user): bool
    {
        return $user->can('manage_rooms');
    }

    /**
     * Determine whether the user can manage company users.
     */
    public function manageUsers(User $user): bool
    {
        return $user->can('manage_company_users');
    }

    /**
     * Determine whether the user can access billing management.
     */
    public function manageBilling(User $user): bool
    {
        return $user->can('internal_billing_management');
    }

    /**
     * Determine whether the user can book on behalf of company users.
     */
    public function bookOnBehalfOfUsers(User $user): bool
    {
        return $user->can('book_on_behalf_of_company_users');
    }

    /**
     * Determine whether the user can transfer all company bookings.
     */
    public function transferAllBookings(User $user): bool
    {
        return $user->can('transfer_all_company_bookings');
    }
}