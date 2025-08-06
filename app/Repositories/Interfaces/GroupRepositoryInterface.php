<?php

namespace App\Repositories\Interfaces;

use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;

interface GroupRepositoryInterface
{
    /**
     * Get all groups for the current tenant.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find a group by ID within the current tenant.
     *
     * @param int $id
     * @return Group|null
     */
    public function find(int $id): ?Group;

    /**
     * Find a group by ID within the current tenant or fail.
     *
     * @param int $id
     * @return Group
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Group;

    /**
     * Create a new group for the current tenant.
     *
     * @param array $data
     * @return Group
     */
    public function create(array $data): Group;

    /**
     * Update a group within the current tenant.
     *
     * @param int $id
     * @param array $data
     * @return Group
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Group;

    /**
     * Delete a group within the current tenant.
     *
     * @param int $id
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): bool;

    /**
     * Get active groups for the current tenant.
     *
     * @return Collection
     */
    public function getActiveGroups(): Collection;

    /**
     * Get inactive groups for the current tenant.
     *
     * @return Collection
     */
    public function getInactiveGroups(): Collection;

    /**
     * Get groups with their member counts for the current tenant.
     *
     * @return Collection
     */
    public function getGroupsWithMemberCounts(): Collection;

    /**
     * Activate a group within the current tenant.
     *
     * @param int $id
     * @return Group
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function activate(int $id): Group;

    /**
     * Deactivate a group within the current tenant.
     *
     * @param int $id
     * @param int $deactivatedBy
     * @return Group
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deactivate(int $id, int $deactivatedBy): Group;

    /**
     * Get total count of groups for the current tenant.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get count of active groups for the current tenant.
     *
     * @return int
     */
    public function activeCount(): int;

    /**
     * Get count of inactive groups for the current tenant.
     *
     * @return int
     */
    public function inactiveCount(): int;

    /**
     * Search groups by name within the current tenant.
     *
     * @param string $query
     * @return Collection
     */
    public function searchByName(string $query): Collection;

    /**
     * Get groups created by a specific user within the current tenant.
     *
     * @param int $userId
     * @return Collection
     */
    public function getGroupsCreatedBy(int $userId): Collection;
}