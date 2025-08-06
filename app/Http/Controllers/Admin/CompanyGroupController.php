<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Models\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompanyGroupController extends Controller
{
    /**
     * Allowed user types that can be added to groups
     */
    protected $allowedMemberTypes = ['booking_agent', 'invitee', 'hod'];

    /**
     * Get user type display name
     */
    private function getUserTypeDisplayName($userType): string
    {
        return match($userType) {
            'hod' => 'Head of Department',
            'booking_agent' => 'Booking Agent',
            'invitee' => 'Invitee',
            'system_admin' => 'System Admin',
            'external' => 'External User',
            default => ucfirst(str_replace('_', ' ', $userType))
        };
    }

    public function __construct()
    {
        // Ensure only system_admin users can access this controller
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || $user->user_type !== 'system_admin') {
                abort(403, 'Unauthorized access. Only System Administrators can manage company groups.');
            }
            return $next($request);
        });
    }

    /**
     * Display a paginated list of company groups
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Build query for groups in the same company
        $query = Group::where('company_id', $user->company_id)
            ->with(['creator', 'members', 'accessControls']);

        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply status filter if provided
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Order by created date (newest first)
        $query->orderBy('created_at', 'desc');

        // Paginate results
        $groups = $query->paginate(15)->withQueryString();

        // Get group statistics
        $groupStats = [
            'total' => Group::where('company_id', $user->company_id)->count(),
            'active' => Group::where('company_id', $user->company_id)->where('is_active', true)->count(),
            'inactive' => Group::where('company_id', $user->company_id)->where('is_active', false)->count(),
        ];

        // Get total members across all groups
        $totalMembers = DB::table('group_member')
            ->join('groups', 'group_member.group_id', '=', 'groups.id')
            ->where('groups.company_id', $user->company_id)
            ->count();

        return Inertia::render('Admin/Groups/Index', [
            'groups' => $groups,
            'filters' => $request->only(['search', 'status']),
            'groupStats' => $groupStats,
            'totalMembers' => $totalMembers,
        ]);
    }

    /**
     * Show the form for creating a new group
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Groups/Create', [
            'allowedMemberTypes' => $this->allowedMemberTypes,
        ]);
    }

    /**
     * Create a new group
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        // Validate the request
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('groups')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ],
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Create the new group
            $group = Group::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'logo' => $validated['logo'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            DB::commit();

            Log::info('New group created by system admin', [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'created_by' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return redirect()->route('admin.groups.index')
                ->with('success', "Group '{$group->name}' has been created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create new group', [
                'error' => $e->getMessage(),
                'created_by' => $user->id,
                'company_id' => $user->company_id,
                'data' => $validated
            ]);

            return back()->withErrors([
                'name' => 'Failed to create group. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Display the specified group with its details and members
     */
    public function show(Group $group): Response
    {
        $user = auth()->user();
        
        // Ensure the group belongs to the same company
        if ($group->company_id !== $user->company_id) {
            abort(403, 'Group not found or access denied.');
        }

        // Load relationships
        $group->load([
            'creator',
            'deactivatedBy',
            'members' => function ($query) {
                $query->orderBy('group_member.created_at', 'desc');
            },
            'accessControls.resource'
        ]);

        // Add display names to members
        $group->members->each(function ($member) {
            $member->user_type_display_name = $this->getUserTypeDisplayName($member->user_type);
        });

        // Get group statistics
        $groupStats = [
            'total_members' => $group->members->count(),
            'active_members' => $group->members->where('status', 'active')->count(),
            'hods' => $group->members->where('user_type', 'hod')->count(),
            'booking_agents' => $group->members->where('user_type', 'booking_agent')->count(),
            'invitees' => $group->members->where('user_type', 'invitee')->count(),
            'access_controls' => $group->accessControls->count(),
        ];

        // Get available users to add to group
        $availableUsers = User::where('company_id', $user->company_id)
            ->whereIn('user_type', $this->allowedMemberTypes)
            ->whereNotIn('id', $group->members->pluck('id')->toArray())
            ->where('status', 'active')
            ->select(['id', 'first_name', 'last_name', 'email', 'user_type'])
            ->orderBy('first_name')
            ->get()
            ->map(function ($user) {
                $user->user_type_display_name = $this->getUserTypeDisplayName($user->user_type);
                return $user;
            });

        return Inertia::render('Admin/Groups/Show', [
            'group' => $group,
            'groupStats' => $groupStats,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Update the specified group
     */
    public function update(Request $request, Group $group): RedirectResponse
    {
        $user = auth()->user();
        
        // Ensure the group belongs to the same company
        if ($group->company_id !== $user->company_id) {
            abort(403, 'Group not found or access denied.');
        }

        // Validate the request
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('groups')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })->ignore($group->id)
            ],
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $wasActive = $group->is_active;
            
            // Update group details
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'logo' => $validated['logo'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ];

            // If deactivating, add deactivation info
            if ($wasActive && !($validated['is_active'] ?? true)) {
                $updateData['deactivated_at'] = now();
                $updateData['deactivated_by'] = $user->id;
            }
            // If reactivating, clear deactivation info
            elseif (!$wasActive && ($validated['is_active'] ?? true)) {
                $updateData['deactivated_at'] = null;
                $updateData['deactivated_by'] = null;
            }

            $group->update($updateData);

            Log::info('Group updated by system admin', [
                'group_id' => $group->id,
                'updated_by' => $user->id,
                'changes' => $group->getChanges(),
                'was_active' => $wasActive,
                'now_active' => $group->is_active
            ]);

            return redirect()->route('admin.groups.show', $group)
                ->with('success', "Group '{$group->name}' has been updated successfully.");

        } catch (\Exception $e) {
            Log::error('Failed to update group', [
                'error' => $e->getMessage(),
                'group_id' => $group->id,
                'updated_by' => $user->id,
                'data' => $validated
            ]);

            return back()->withErrors([
                'name' => 'Failed to update group. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Remove the specified group from storage
     */
    public function destroy(Group $group): RedirectResponse
    {
        $user = auth()->user();
        
        // Ensure the group belongs to the same company
        if ($group->company_id !== $user->company_id) {
            abort(403, 'Group not found or access denied.');
        }

        try {
            DB::beginTransaction();

            $groupName = $group->name;
            $groupId = $group->id;

            // Check if group has any access controls
            $accessControlsCount = $group->accessControls()->count();
            
            if ($accessControlsCount > 0) {
                return back()->withErrors([
                    'group' => "Cannot delete group '{$groupName}'. It has {$accessControlsCount} resource access control(s). Please remove all access controls first."
                ]);
            }

            // Soft delete the group (this will also remove group members due to cascade)
            $group->delete();

            DB::commit();

            Log::info('Group deleted by system admin', [
                'deleted_group_id' => $groupId,
                'deleted_group_name' => $groupName,
                'deleted_by' => $user->id,
                'company_id' => $user->company_id
            ]);

            return redirect()->route('admin.groups.index')
                ->with('success', "Group '{$groupName}' has been deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete group', [
                'error' => $e->getMessage(),
                'group_id' => $group->id,
                'deleted_by' => $user->id
            ]);

            return back()->withErrors([
                'group' => 'Failed to delete group. Please try again.'
            ]);
        }
    }

    /**
     * Add users to a group
     */
    public function addMembers(Request $request, Group $group): RedirectResponse
    {
        $user = auth()->user();
        
        // Ensure the group belongs to the same company
        if ($group->company_id !== $user->company_id) {
            abort(403, 'Group not found or access denied.');
        }

        // Validate the request
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id)
                                 ->whereIn('user_type', $this->allowedMemberTypes)
                                 ->where('status', 'active');
                })
            ],
        ]);

        try {
            DB::beginTransaction();

            $addedUsers = [];
            $skippedUsers = [];

            foreach ($validated['user_ids'] as $userId) {
                // Check if user is already a member
                if ($group->members()->where('user_id', $userId)->exists()) {
                    $targetUser = User::find($userId);
                    $skippedUsers[] = $targetUser->full_name;
                    continue;
                }

                // Add user to group
                $group->members()->attach($userId, [
                    'company_id' => $user->company_id,
                    'added_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $targetUser = User::find($userId);
                $addedUsers[] = $targetUser->full_name;
            }

            DB::commit();

            if (!empty($addedUsers)) {
                Log::info('Users added to group by system admin', [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'added_users' => $addedUsers,
                    'added_by' => $user->id,
                ]);
            }

            $message = '';
            if (!empty($addedUsers)) {
                $message .= count($addedUsers) . ' user(s) added successfully: ' . implode(', ', $addedUsers) . '.';
            }
            if (!empty($skippedUsers)) {
                $message .= ' Skipped ' . count($skippedUsers) . ' user(s) already in group: ' . implode(', ', $skippedUsers) . '.';
            }

            return redirect()->route('admin.groups.show', $group)
                ->with('success', trim($message));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to add members to group', [
                'error' => $e->getMessage(),
                'group_id' => $group->id,
                'user_ids' => $validated['user_ids'],
                'added_by' => $user->id
            ]);

            return back()->withErrors([
                'user_ids' => 'Failed to add members to group. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Remove users from a group
     */
    public function removeMembers(Request $request, Group $group): RedirectResponse
    {
        $user = auth()->user();
        
        // Ensure the group belongs to the same company
        if ($group->company_id !== $user->company_id) {
            abort(403, 'Group not found or access denied.');
        }

        // Validate the request
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ],
        ]);

        try {
            DB::beginTransaction();

            $removedUsers = [];

            foreach ($validated['user_ids'] as $userId) {
                // Check if user is a member of this group
                if ($group->members()->where('user_id', $userId)->exists()) {
                    $targetUser = User::find($userId);
                    $removedUsers[] = $targetUser->full_name;
                    
                    // Remove user from group
                    $group->members()->detach($userId);
                }
            }

            DB::commit();

            if (!empty($removedUsers)) {
                Log::info('Users removed from group by system admin', [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'removed_users' => $removedUsers,
                    'removed_by' => $user->id,
                ]);

                return redirect()->route('admin.groups.show', $group)
                    ->with('success', count($removedUsers) . ' user(s) removed successfully: ' . implode(', ', $removedUsers) . '.');
            } else {
                return redirect()->route('admin.groups.show', $group)
                    ->with('info', 'No valid members found to remove.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to remove members from group', [
                'error' => $e->getMessage(),
                'group_id' => $group->id,
                'user_ids' => $validated['user_ids'],
                'removed_by' => $user->id
            ]);

            return back()->withErrors([
                'user_ids' => 'Failed to remove members from group. Please try again.'
            ]);
        }
    }

    /**
     * Update member role method removed - groups no longer have member-specific roles.
     * Users only have system-wide roles (hod, booking_agent, invitee, etc.).
     */
}