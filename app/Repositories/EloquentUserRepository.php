<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Booking;
use App\Enums\UserStatus;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Get the current tenant's company ID.
     *
     * @return int|null
     */
    protected function getCurrentCompanyId(): ?int
    {
        return Auth::user()?->company_id;
    }

    /**
     * Get the base query for company users in the current tenant.
     *
     * @param array $allowedRoles
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBaseQuery(array $allowedRoles = [])
    {
        $companyId = $this->getCurrentCompanyId();
        
        if (!$companyId) {
            throw new \RuntimeException('No authenticated user or company context available.');
        }

        $query = User::where('company_id', $companyId);

        if (!empty($allowedRoles)) {
            $query->whereIn('user_type', $allowedRoles);
        }

        return $query;
    }

    /**
     * Get all company users for the current tenant.
     *
     * @param array $allowedRoles
     * @return Collection
     */
    public function getAllCompanyUsers(array $allowedRoles = []): Collection
    {
        return $this->getBaseQuery($allowedRoles)
            ->with(['role', 'group'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get paginated company users for the current tenant.
     *
     * @param array $filters
     * @param array $allowedRoles
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedCompanyUsers(array $filters = [], array $allowedRoles = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->getBaseQuery($allowedRoles)
            ->with(['role', 'group']);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter
        if (!empty($filters['role']) && in_array($filters['role'], $allowedRoles)) {
            $query->where('user_type', $filters['role']);
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $validStatuses = UserStatus::values();
            if (in_array($filters['status'], $validStatuses)) {
                $query->where('status', $filters['status']);
            }
        }

        // Apply group filter
        if (!empty($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($perPage)
                    ->withQueryString();
    }

    /**
     * Find a company user by ID within the current tenant.
     *
     * @param int $id
     * @param array $allowedRoles
     * @return User|null
     */
    public function findCompanyUser(int $id, array $allowedRoles = []): ?User
    {
        return $this->getBaseQuery($allowedRoles)
            ->with(['role', 'group', 'groups', 'bookings' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }])
            ->find($id);
    }

    /**
     * Find a company user by ID within the current tenant or fail.
     *
     * @param int $id
     * @param array $allowedRoles
     * @return User
     * @throws ModelNotFoundException
     */
    public function findCompanyUserOrFail(int $id, array $allowedRoles = []): User
    {
        return $this->getBaseQuery($allowedRoles)
            ->with(['role', 'group', 'groups'])
            ->findOrFail($id);
    }

    /**
     * Create a new company user for the current tenant.
     *
     * @param array $data
     * @return User
     */
    public function createCompanyUser(array $data): User
    {
        $companyId = $this->getCurrentCompanyId();

        if (!$companyId) {
            throw new \RuntimeException('No authenticated user or company context available.');
        }

        $data['company_id'] = $companyId;
        
        return User::create($data);
    }

    /**
     * Update a company user within the current tenant.
     *
     * @param int $id
     * @param array $data
     * @param array $allowedRoles
     * @return User
     * @throws ModelNotFoundException
     */
    public function updateCompanyUser(int $id, array $data, array $allowedRoles = []): User
    {
        $user = $this->findCompanyUserOrFail($id, $allowedRoles);
        
        // Remove company_id from update data to prevent tampering
        unset($data['company_id']);
        
        $user->update($data);
        
        return $user->fresh(['role', 'group', 'groups']);
    }

    /**
     * Get users by group within the current tenant.
     *
     * @param int $groupId
     * @param array $allowedRoles
     * @return Collection
     */
    public function getUsersByGroup(int $groupId, array $allowedRoles = []): Collection
    {
        return $this->getBaseQuery($allowedRoles)
            ->where('group_id', $groupId)
            ->with(['role'])
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Get users accessible by HOD (users in their group).
     *
     * @param int $hodUserId
     * @param array $allowedRoles
     * @return Collection
     */
    public function getUsersAccessibleByHod(int $hodUserId, array $allowedRoles = []): Collection
    {
        $hodUser = User::find($hodUserId);
        
        if (!$hodUser || !$hodUser->group_id) {
            return new Collection([]);
        }

        return $this->getBaseQuery($allowedRoles)
            ->where('group_id', $hodUser->group_id)
            ->where('id', '!=', $hodUserId) // Exclude the HOD themselves
            ->with(['role'])
            ->orderBy('first_name')
            ->get();
    }

    /**
     * Check if user can be updated by the current user.
     *
     * @param int $targetUserId
     * @param int $currentUserId
     * @return bool
     */
    public function canUserBeUpdatedBy(int $targetUserId, int $currentUserId): bool
    {
        $currentUser = User::find($currentUserId);
        $targetUser = User::find($targetUserId);

        if (!$currentUser || !$targetUser) {
            return false;
        }

        // Must be same company
        if ($currentUser->company_id !== $targetUser->company_id) {
            return false;
        }

        // System admin can update any company user (except other system_admin)
        if ($currentUser->user_type === 'system_admin') {
            return !in_array($targetUser->user_type, ['system_admin', 'venuepro_admin']);
        }

        // HOD can only update users in their group (and only certain roles)
        if ($currentUser->user_type === 'hod' && $currentUser->group_id) {
            return $targetUser->group_id === $currentUser->group_id &&
                   in_array($targetUser->user_type, ['booking_agent', 'invitee']);
        }

        return false;
    }

    /**
     * Get role statistics for company users.
     *
     * @param array $allowedRoles
     * @return array
     */
    public function getRoleStatistics(array $allowedRoles = []): array
    {
        $stats = $this->getBaseQuery($allowedRoles)
            ->selectRaw('user_type, COUNT(*) as count')
            ->groupBy('user_type')
            ->pluck('count', 'user_type')
            ->toArray();

        // Add total count
        $stats['total'] = array_sum($stats);

        return $stats;
    }

    /**
     * Get users with booking statistics.
     *
     * @param int $userId
     * @return array
     */
    public function getUserBookingStatistics(int $userId): array
    {
        $user = $this->findCompanyUser($userId);
        
        if (!$user) {
            return [
                'total_bookings' => 0,
                'active_bookings' => 0,
                'groups_count' => 0,
                'last_activity' => null,
            ];
        }

        return [
            'total_bookings' => $user->allBookings()->count(),
            'active_bookings' => $user->allBookings()
                ->where(function($query) {
                    $query->where('date', '>', now()->toDateString())
                          ->orWhere(function($q) {
                              $q->where('date', '=', now()->toDateString())
                                ->whereTime('start_time', '>=', now()->toTimeString());
                          });
                })
                ->where('status', '!=', 'cancelled')
                ->count(),
            'groups_count' => $user->groups()->count(),
            'last_activity' => $user->last_login_at,
        ];
    }

    /**
     * Check if email is unique within the company (excluding specific user).
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function isEmailUniqueInCompany(string $email, ?int $excludeUserId = null): bool
    {
        $query = $this->getBaseQuery()
            ->where('email', $email);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->count() === 0;
    }

    /**
     * Deactivate a company user.
     *
     * @param int $id
     * @param array $allowedRoles
     * @return User
     * @throws ModelNotFoundException
     */
    public function deactivateCompanyUser(int $id, array $allowedRoles = []): User
    {
        $user = $this->findCompanyUserOrFail($id, $allowedRoles);
        $user->deactivate();
        
        return $user->fresh(['role', 'group']);
    }

    /**
     * Reactivate a company user.
     *
     * @param int $id
     * @param array $allowedRoles
     * @return User
     * @throws ModelNotFoundException
     */
    public function reactivateCompanyUser(int $id, array $allowedRoles = []): User
    {
        $user = $this->findCompanyUserOrFail($id, $allowedRoles);
        $user->activate();
        
        return $user->fresh(['role', 'group']);
    }
}