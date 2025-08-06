<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GroupController extends Controller
{
    protected GroupRepositoryInterface $groupRepository;

    /**
     * Allowed user types that can manage groups
     */
    protected array $allowedManagerTypes = ['system_admin', 'hod'];

    /**
     * Allowed user types that can be added to groups
     */
    protected array $allowedMemberTypes = ['booking_agent', 'invitee', 'hod'];

    /**
     * Allowed roles within groups
     */
    protected array $allowedGroupRoles = ['member', 'manager', 'admin'];

    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
        
        // Apply middleware to ensure only authorized users can access group management
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || !in_array($user->user_type, $this->allowedManagerTypes)) {
                return response()->json([
                    'message' => 'Unauthorized access. Only System Administrators and Head of Departments can manage groups.',
                    'error' => 'UNAUTHORIZED_ACCESS'
                ], 403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of groups for the current tenant.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'search' => 'nullable|string|max:255',
                'status' => 'nullable|in:active,inactive',
                'per_page' => 'nullable|integer|min:1|max:100',
                'page' => 'nullable|integer|min:1',
            ]);

            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');
            $status = $request->input('status');

            // Get groups based on filters
            if ($search) {
                $groups = $this->groupRepository->searchByName($search);
            } elseif ($status === 'active') {
                $groups = $this->groupRepository->getActiveGroups();
            } elseif ($status === 'inactive') {
                $groups = $this->groupRepository->getInactiveGroups();
            } else {
                $groups = $this->groupRepository->all();
            }

            // Get statistics
            $stats = [
                'total' => $this->groupRepository->count(),
                'active' => $this->groupRepository->activeCount(),
                'inactive' => $this->groupRepository->inactiveCount(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'groups' => $groups,
                    'stats' => $stats,
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => $perPage,
                        'total' => $groups->count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch groups', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'company_id' => Auth::user()?->company_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch groups',
                'error' => 'FETCH_GROUPS_FAILED'
            ], 500);
        }
    }

    /**
     * Store a newly created group.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
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
                'is_active' => 'nullable|boolean',
            ]);

            DB::beginTransaction();

            $group = $this->groupRepository->create($validated);

            DB::commit();

            Log::info('Group created successfully', [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'created_by' => $user->id,
                'company_id' => $user->company_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Group created successfully',
                'data' => [
                    'group' => $group->load(['creator', 'members'])
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create group', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'company_id' => Auth::user()?->company_id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create group',
                'error' => 'CREATE_GROUP_FAILED'
            ], 500);
        }
    }

    /**
     * Display the specified group.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $group = $this->groupRepository->findOrFail($id);

            // Get group statistics
            $stats = [
                'total_members' => $group->members->count(),
                'active_members' => $group->members->where('status', 'active')->count(),
                'managers' => $group->members->where('pivot.role', 'manager')->count(),
                'admins' => $group->members->where('pivot.role', 'admin')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'group' => $group,
                    'stats' => $stats
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
                'error' => 'GROUP_NOT_FOUND'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Failed to fetch group', [
                'error' => $e->getMessage(),
                'group_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch group',
                'error' => 'FETCH_GROUP_FAILED'
            ], 500);
        }
    }

    /**
     * Update the specified group.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:191',
                    Rule::unique('groups')->where(function ($query) use ($user) {
                        return $query->where('company_id', $user->company_id);
                    })->ignore($id)
                ],
                'description' => 'nullable|string|max:1000',
                'logo' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);

            DB::beginTransaction();

            $group = $this->groupRepository->update($id, $validated);

            DB::commit();

            Log::info('Group updated successfully', [
                'group_id' => $group->id,
                'updated_by' => $user->id,
                'changes' => array_keys($validated),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Group updated successfully',
                'data' => [
                    'group' => $group
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
                'error' => 'GROUP_NOT_FOUND'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update group', [
                'error' => $e->getMessage(),
                'group_id' => $id,
                'user_id' => Auth::id(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update group',
                'error' => 'UPDATE_GROUP_FAILED'
            ], 500);
        }
    }

    /**
     * Remove the specified group.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $group = $this->groupRepository->findOrFail($id);

            // Check if group has any access controls
            $accessControlsCount = $group->accessControls()->count();
            
            if ($accessControlsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete group. It has {$accessControlsCount} resource access control(s). Please remove all access controls first.",
                    'error' => 'GROUP_HAS_ACCESS_CONTROLS'
                ], 422);
            }

            DB::beginTransaction();

            $groupName = $group->name;
            $this->groupRepository->delete($id);

            DB::commit();

            Log::info('Group deleted successfully', [
                'deleted_group_id' => $id,
                'deleted_group_name' => $groupName,
                'deleted_by' => $user->id,
                'company_id' => $user->company_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Group deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
                'error' => 'GROUP_NOT_FOUND'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete group', [
                'error' => $e->getMessage(),
                'group_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group',
                'error' => 'DELETE_GROUP_FAILED'
            ], 500);
        }
    }

    /**
     * Activate a group.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            DB::beginTransaction();

            $group = $this->groupRepository->activate($id);

            DB::commit();

            Log::info('Group activated successfully', [
                'group_id' => $group->id,
                'activated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Group activated successfully',
                'data' => [
                    'group' => $group
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
                'error' => 'GROUP_NOT_FOUND'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to activate group', [
                'error' => $e->getMessage(),
                'group_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to activate group',
                'error' => 'ACTIVATE_GROUP_FAILED'
            ], 500);
        }
    }

    /**
     * Deactivate a group.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deactivate(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            DB::beginTransaction();

            $group = $this->groupRepository->deactivate($id, $user->id);

            DB::commit();

            Log::info('Group deactivated successfully', [
                'group_id' => $group->id,
                'deactivated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Group deactivated successfully',
                'data' => [
                    'group' => $group
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found',
                'error' => 'GROUP_NOT_FOUND'
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to deactivate group', [
                'error' => $e->getMessage(),
                'group_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate group',
                'error' => 'DEACTIVATE_GROUP_FAILED'
            ], 500);
        }
    }

    /**
     * Get groups with member counts.
     *
     * @return JsonResponse
     */
    public function withMemberCounts(): JsonResponse
    {
        try {
            $groups = $this->groupRepository->getGroupsWithMemberCounts();

            return response()->json([
                'success' => true,
                'data' => [
                    'groups' => $groups
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch groups with member counts', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch groups with member counts',
                'error' => 'FETCH_GROUPS_WITH_COUNTS_FAILED'
            ], 500);
        }
    }
}