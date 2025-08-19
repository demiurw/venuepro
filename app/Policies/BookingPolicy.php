<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user): bool
    {
        return $user->can('create_bookings') || $user->can('create_public_bookings');
    }

    /**
     * Determine whether the user can modify bookings.
     */
    public function modify(User $user): bool
    {
        return $user->can('modify_bookings');
    }

    /**
     * Determine whether the user can invite attendees to bookings.
     */
    public function inviteAttendees(User $user): bool
    {
        return $user->can('invite_attendees') || $user->can('invite_to_own_bookings');
    }

    /**
     * Determine whether the user can view group bookings.
     */
    public function viewGroupBookings(User $user): bool
    {
        return $user->can('view_group_bookings');
    }

    /**
     * Determine whether the user can manage group bookings.
     */
    public function manageGroupBookings(User $user): bool
    {
        return $user->can('manage_group_bookings');
    }

    /**
     * Determine whether the user can book on behalf of group members.
     */
    public function bookOnBehalfOfGroupMembers(User $user): bool
    {
        return $user->can('book_on_behalf_of_group_members');
    }

    /**
     * Determine whether the user can transfer group bookings.
     */
    public function transferGroupBookings(User $user): bool
    {
        return $user->can('transfer_group_bookings');
    }

    /**
     * Determine whether the user can view public rooms.
     */
    public function viewPublicRooms(User $user): bool
    {
        return $user->can('view_public_rooms');
    }

    /**
     * Determine whether the user can accept or decline invites.
     */
    public function respondToInvites(User $user): bool
    {
        return $user->can('accept_decline_invites');
    }

    /**
     * Determine whether the user can view meeting details.
     */
    public function viewMeetingDetails(User $user): bool
    {
        return $user->can('view_meeting_details');
    }
}