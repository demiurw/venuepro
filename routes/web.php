<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CompanyUserController;
use App\Http\Controllers\Admin\CompanyGroupController;
use App\Http\Controllers\Hod\HodDashboardController;
use App\Http\Controllers\Agent\AgentDashboardController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\External\ExternalDashboardController;
use App\Http\Controllers\VenueProAdmin\VenueProAdminDashboardController;
use App\Http\Middleware\DashboardRedirectMiddleware;
use App\Http\Middleware\RoleAccessMiddleware;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Account pending route for inactive users - redirect active users to dashboard
Route::get('/account/pending', function () {
    $user = Auth::user();
    
    // Redirect active users to their proper dashboard
    if ($user && $user->isActive()) {
        $dashboardRoute = DashboardRedirectMiddleware::getUserDashboardRoute($user);
        return redirect($dashboardRoute);
    }
    
    return Inertia::render('auth/AccountPending');
})->name('account.pending')->middleware('auth');

// Generic dashboard route - will redirect based on user role
Route::middleware([
    'tenant',
    'auth',
    'verified',
    DashboardRedirectMiddleware::class
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// VenuePro Admin Routes
Route::middleware([
    'tenant',
    'auth',
    'verified',
    RoleAccessMiddleware::class . ':venuepro_admin'
])->prefix('venuepro-admin')->name('venuepro-admin.')->group(function () {
    Route::get('/dashboard', [VenueProAdminDashboardController::class, 'index'])->name('dashboard');

    // Add other VenuePro admin routes here
    Route::get('/companies', [VenueProAdminDashboardController::class, 'companies'])->name('companies');
    Route::get('/companies/create', [VenueProAdminDashboardController::class, 'createCompany'])->name('companies.create');
    Route::get('/system-settings', [VenueProAdminDashboardController::class, 'systemSettings'])->name('system-settings');
    Route::get('/system/health', [VenueProAdminDashboardController::class, 'systemHealth'])->name('system.health');
    Route::get('/reports', [VenueProAdminDashboardController::class, 'globalReports'])->name('reports');
    Route::get('/analytics', [VenueProAdminDashboardController::class, 'analytics'])->name('analytics');
});

// System Admin Routes
Route::middleware([
    'tenant',
    'auth',
    'verified',
    RoleAccessMiddleware::class . ':system_admin'
])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Company management
    Route::get('/buildings', [AdminDashboardController::class, 'buildings'])->name('buildings');
    Route::get('/rooms', [AdminDashboardController::class, 'rooms'])->name('rooms');
    Route::get('/rooms/create', [AdminDashboardController::class, 'createRoom'])->name('rooms.create');
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    
    // User Management Routes
    Route::resource('users', CompanyUserController::class);
    Route::post('users/{user}/resend-otp', [CompanyUserController::class, 'resendOtp'])->name('users.resend-otp');
    Route::post('users/{user}/reactivate', [CompanyUserController::class, 'reactivate'])->name('users.reactivate');
    
    // Group Management Routes
    Route::resource('groups', CompanyGroupController::class);
    Route::post('groups/{group}/members', [CompanyGroupController::class, 'addMembers'])->name('groups.add-members');
    Route::delete('groups/{group}/members', [CompanyGroupController::class, 'removeMembers'])->name('groups.remove-members');
    Route::patch('groups/{group}/members/{user}', [CompanyGroupController::class, 'updateMemberRole'])->name('groups.update-member-role');
});

// Head of Department Routes
Route::middleware([
    'tenant',
    'auth',
    'verified',
    RoleAccessMiddleware::class . ':hod'
])->prefix('hod')->name('hod.')->group(function () {
    Route::get('/dashboard', [HodDashboardController::class, 'index'])->name('dashboard');

    // Department management
    Route::get('/team', [HodDashboardController::class, 'team'])->name('team');
    Route::get('/bookings', [HodDashboardController::class, 'bookings'])->name('bookings');
    Route::get('/approvals', [HodDashboardController::class, 'approvals'])->name('approvals');
    Route::get('/reports', [HodDashboardController::class, 'reports'])->name('reports');
});

// Booking Agent Routes
Route::middleware([
    'tenant',
    'auth',
    'verified',
    RoleAccessMiddleware::class . ':booking_agent'
])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');

    // Booking management
    Route::get('/bookings', [AgentDashboardController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/create', [AgentDashboardController::class, 'createBooking'])->name('bookings.create');
    Route::get('/calendar', [AgentDashboardController::class, 'calendar'])->name('calendar');
    Route::get('/clients', [AgentDashboardController::class, 'clients'])->name('clients');
    Route::get('/clients/create', [AgentDashboardController::class, 'createClient'])->name('clients.create');
});

// User Routes (for invitees and general users)
Route::middleware([
    'tenant',
    'auth',
    'verified',
    RoleAccessMiddleware::class . ':invitee,external'
])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // User features
    Route::get('/bookings', [UserDashboardController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/create', [UserDashboardController::class, 'createBooking'])->name('bookings.create');
    Route::get('/calendar', [UserDashboardController::class, 'calendar'])->name('calendar');
    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
});

// External User Routes (for external clients with limited access)
Route::middleware([
    'tenant',
    'auth',
    'verified',
    RoleAccessMiddleware::class . ':external'
])->prefix('external')->name('external.')->group(function () {
    Route::get('/dashboard', [ExternalDashboardController::class, 'index'])->name('dashboard');

    // Limited external features
    Route::get('/bookings', [ExternalDashboardController::class, 'bookings'])->name('bookings');
    Route::get('/requests', [ExternalDashboardController::class, 'requests'])->name('requests');
    Route::get('/requests/create', [ExternalDashboardController::class, 'createRequest'])->name('requests.create');
});

// Shared routes accessible to all authenticated users
Route::middleware([
    'tenant',
    'auth',
    'verified'
])->group(function () {
    // Profile and settings (accessible to all)
    require __DIR__.'/settings.php';

    // API routes that might be shared
    Route::get('/api/user/permissions', function () {
        return response()->json([
            'user' => auth()->user(),
            'permissions' => auth()->user()->getAllPermissions(),
            'roles' => auth()->user()->getRoleNames(),
            'accessible_routes' => RoleAccessMiddleware::getAccessibleRoutes(auth()->user())
        ]);
    });
});


// Auth routes should remain accessible for tenants to log in
require __DIR__.'/auth.php';
