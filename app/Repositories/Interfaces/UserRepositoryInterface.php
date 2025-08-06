<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Get all company users for the current tenant.
     *
     * @param array $allowedRoles
     * @return Collection
     */
    public function getAllCompanyUsers(array $allowedRoles = []): Collection;

    /**
     * Get paginated company users for the current tenant.
     *
     * @param array $filters
     * @param array $allowedRoles
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedCompanyUsers(array $filters = [], array $allowedRoles = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a company user by ID within the current tenant.
     *
     * @param int $id
     * @param array $allowedRoles
     * @return User|null
     */
    public function findCompanyUser(int $id, array $allowedRoles = []): ?User;

    /**
     * Find a company user by ID within the current tenant or fail.
     *
     * @param int $id
     * @param array $allowedRoles
     * @return User
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findCompanyUserOrFail(int $id, array $allowedRoles = []): User;

    /**
     * Create a new company user for the current tenant.
     *
     * @param array $data
     * @return User
     */
    public function createCompanyUser(array $data): User;

    /**
     * Update a company user within the current tenant.
     *
     * @param int $id
     * @param array $data
     * @param array $allowedRoles
     * @return User
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateCompanyUser(int $id, array $data, array $allowedRoles = []): User;

    /**
     * Get users by group within the current tenant.
     *
     * @param int $groupId
     * @param array $allowedRoles
     * @return Collection
     */
    public function getUsersByGroup(int $groupId, array $allowedRoles = []): Collection;

    /**
     * Get users accessible by HOD (users in their group).
     *
     * @param int $hodUserId
     * @param array $allowedRoles
     * @return Collection
     */
    public function getUsersAccessibleByHod(int $hodUserId, array $allowedRoles = []): Collection;

    /**
     * Check if user can be updated by the current user.
     *
     * @param int $targetUserId
     * @param int $currentUserId
     * @return bool
     */
    public function canUserBeUpdatedBy(int $targetUserId, int $currentUserId): bool;

    /**
     * Get role statistics for company users.
     *
     * @param array $allowedRoles
     * @return array
     */
    public function getRoleStatistics(array $allowedRoles = []): array;

    /**
     * Get users with booking statistics.
     *
     * @param int $userId
     * @return array
     */
    public function getUserBookingStatistics(int $userId): array;

    /**
     * Check if email is unique within the company (excluding specific user).
     *
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function isEmailUniqueInCompany(string $email, ?int $excludeUserId = null): bool;

    /**
     * Deactivate a company user.
     *
     * @param int $id
     * @param array $allowedRoles
     * @return User
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deactivateCompanyUser(int $id, array $allowedRoles = []): User;

    /**
     * Reactivate a company user.
     *
     * @param int $id
     * @param array $allowedRoles
     * @return User
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function reactivateCompanyUser(int $id, array $allowedRoles = []): User;
}