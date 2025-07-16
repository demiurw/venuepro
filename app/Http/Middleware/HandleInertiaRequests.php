<?php

namespace App\Http\Middleware;

use App\Helpers\UserNavigationHelper;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'name' => $user->first_name . ' ' . $user->last_name, // For compatibility
                    'full_name' => $user->first_name . ' ' . $user->last_name,
                    'role_id' => $user->role_id,
                    'group_id' => $user->group_id,
                    'company_id' => $user->company_id,
                    'status' => $user->status,
                    'user_type' => $user->user_type,
                    'user_type_display_name' => UserNavigationHelper::getUserTypeDisplayName($user),
                    'auth_method' => $user->auth_method,
                    'avatar' => $user->avatar ?? null,
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'last_login_at' => $user->last_login_at?->toISOString(),
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),

                    // Relationships
                    'company' => $user->company ? [
                        'id' => $user->company->id,
                        'name' => $user->company->name,
                        'slug' => $user->company->slug ?? null,
                        'status' => $user->company->status ?? 'active',
                        'created_at' => $user->company->created_at->toISOString(),
                        'updated_at' => $user->company->updated_at->toISOString(),
                    ] : null,

                    'group' => $user->group ? [
                        'id' => $user->group->id,
                        'name' => $user->group->name,
                        'description' => $user->group->description ?? null,
                        'company_id' => $user->group->company_id,
                        'created_at' => $user->group->created_at->toISOString(),
                        'updated_at' => $user->group->updated_at->toISOString(),
                    ] : null,

                    'role' => $user->role ? [
                        'id' => $user->role->id,
                        'name' => $user->role->name,
                        'guard_name' => $user->role->guard_name ?? 'web',
                        'team_id' => $user->role->team_id ?? null,
                        'created_at' => $user->role->created_at->toISOString(),
                        'updated_at' => $user->role->updated_at->toISOString(),
                    ] : null,

                    // Role-based computed properties
                    'navigation_menu' => UserNavigationHelper::getNavigationMenu($user),
                    'dashboard_route' => UserNavigationHelper::getDashboardRoute($user->user_type),
                    'permissions' => UserNavigationHelper::getUserPermissionsSummary($user),
                ] : null,
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],

            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },

            // App-wide configuration that might be useful
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
            ],

            // Tenant information if using multi-tenancy
            'tenant' => $request->tenant ? [
                'id' => $request->tenant->id,
                'name' => $request->tenant->name,
                'slug' => $request->tenant->slug,
            ] : null,
        ]);
    }
}
