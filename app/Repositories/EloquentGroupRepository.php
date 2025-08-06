<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\User;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class EloquentGroupRepository implements GroupRepositoryInterface
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
     * Get the base query for the current tenant.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBaseQuery()
    {
        $companyId = $this->getCurrentCompanyId();
        
        if (!$companyId) {
            throw new \RuntimeException('No authenticated user or company context available.');
        }

        return Group::where('company_id', $companyId);
    }

    /**
     * Get all groups for the current tenant.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->getBaseQuery()
            ->with(['creator', 'members'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Find a group by ID within the current tenant.
     *
     * @param int $id
     * @return Group|null
     */
    public function find(int $id): ?Group
    {
        return $this->getBaseQuery()
            ->with(['creator', 'members', 'deactivatedBy'])
            ->find($id);
    }

    /**
     * Find a group by ID within the current tenant or fail.
     *
     * @param int $id
     * @return Group
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Group
    {
        return $this->getBaseQuery()
            ->with(['creator', 'members', 'deactivatedBy'])
            ->findOrFail($id);
    }

    /**
     * Create a new group for the current tenant.
     *
     * @param array $data
     * @return Group
     */
    public function create(array $data): Group
    {
        $companyId = $this->getCurrentCompanyId();
        $userId = Auth::id();

        if (!$companyId || !$userId) {
            throw new \RuntimeException('No authenticated user or company context available.');
        }

        $data['company_id'] = $companyId;
        $data['created_by'] = $userId;
        
        // Set defaults
        $data['is_active'] = $data['is_active'] ?? true;

        return Group::create($data);
    }

    /**
     * Update a group within the current tenant.
     *
     * @param int $id
     * @param array $data
     * @return Group
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Group
    {
        $group = $this->findOrFail($id);
        
        // Remove company_id and created_by from update data to prevent tampering
        unset($data['company_id'], $data['created_by']);
        
        $group->update($data);
        
        return $group->fresh(['creator', 'members', 'deactivatedBy']);
    }

    /**
     * Delete a group within the current tenant.
     *
     * @param int $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $group = $this->findOrFail($id);
        
        // Detach all members before deleting
        $group->members()->detach();
        
        return $group->delete();
    }

    /**
     * Get active groups for the current tenant.
     *
     * @return Collection
     */
    public function getActiveGroups(): Collection
    {
        return $this->getBaseQuery()
            ->where('is_active', true)
            ->with(['creator', 'members'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get inactive groups for the current tenant.
     *
     * @return Collection
     */
    public function getInactiveGroups(): Collection
    {
        return $this->getBaseQuery()
            ->where('is_active', false)
            ->with(['creator', 'members', 'deactivatedBy'])
            ->orderBy('deactivated_at', 'desc')
            ->get();
    }

    /**
     * Get groups with their member counts for the current tenant.
     *
     * @return Collection
     */
    public function getGroupsWithMemberCounts(): Collection
    {
        return $this->getBaseQuery()
            ->withCount('members')
            ->with(['creator'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Activate a group within the current tenant.
     *
     * @param int $id
     * @return Group
     * @throws ModelNotFoundException
     */
    public function activate(int $id): Group
    {
        $group = $this->findOrFail($id);
        $group->activate();
        
        return $group->fresh(['creator', 'members']);
    }

    /**
     * Deactivate a group within the current tenant.
     *
     * @param int $id
     * @param int $deactivatedBy
     * @return Group
     * @throws ModelNotFoundException
     */
    public function deactivate(int $id, int $deactivatedBy): Group
    {
        $group = $this->findOrFail($id);
        $deactivatedByUser = User::find($deactivatedBy);
        
        if (!$deactivatedByUser) {
            throw new \InvalidArgumentException('Invalid user ID for deactivation.');
        }
        
        $group->deactivate($deactivatedByUser);
        
        return $group->fresh(['creator', 'members', 'deactivatedBy']);
    }

    /**
     * Get total count of groups for the current tenant.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->getBaseQuery()->count();
    }

    /**
     * Get count of active groups for the current tenant.
     *
     * @return int
     */
    public function activeCount(): int
    {
        return $this->getBaseQuery()
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get count of inactive groups for the current tenant.
     *
     * @return int
     */
    public function inactiveCount(): int
    {
        return $this->getBaseQuery()
            ->where('is_active', false)
            ->count();
    }

    /**
     * Search groups by name within the current tenant.
     *
     * @param string $query
     * @return Collection
     */
    public function searchByName(string $query): Collection
    {
        return $this->getBaseQuery()
            ->where('name', 'LIKE', "%{$query}%")
            ->with(['creator', 'members'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get groups created by a specific user within the current tenant.
     *
     * @param int $userId
     * @return Collection
     */
    public function getGroupsCreatedBy(int $userId): Collection
    {
        return $this->getBaseQuery()
            ->where('created_by', $userId)
            ->with(['creator', 'members'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}