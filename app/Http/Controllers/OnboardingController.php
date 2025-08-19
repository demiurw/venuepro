<?php

namespace App\Http\Controllers;

use App\Http\Requests\Onboarding\CreateBuildingsRequest;
use App\Http\Requests\Onboarding\CreateRoomsRequest;
use App\Http\Requests\Onboarding\CreateGroupsRequest;
use App\Http\Requests\Onboarding\CreateUsersRequest;
use App\Http\Requests\Onboarding\SaveLabelsRequest;
use App\Services\OnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    protected OnboardingService $onboardingService;

    public function __construct(OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
        
        // Ensure only System Admins can access onboarding
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || $user->user_type !== 'system_admin') {
                abort(403, 'Unauthorized access. Only System Administrators can access onboarding.');
            }
            return $next($request);
        });
    }

    /**
     * Show the buildings onboarding step
     */
    public function buildings(Request $request): Response
    {
        $user = Auth::user();
        $progress = $this->onboardingService->getOnboardingProgress($user->company_id);

        return Inertia::render('Onboarding/Buildings', [
            'progress' => $progress,
            'currentStep' => 'buildings',
            'nextStep' => 'rooms',
        ]);
    }

    /**
     * Store buildings during onboarding
     */
    public function storeBuildings(CreateBuildingsRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $result = $this->onboardingService->createBuildings($user, $validated['buildings']);

        if (!$result['success']) {
            return back()->withErrors(['buildings' => $result['message']])->withInput();
        }

        return redirect()->route('onboarding.rooms')
            ->with('success', $result['message']);
    }

    /**
     * Show the rooms onboarding step
     */
    public function rooms(Request $request): Response
    {
        $user = Auth::user();
        $progress = $this->onboardingService->getOnboardingProgress($user->company_id);

        // Redirect if buildings not completed
        if (!$progress['buildings']) {
            return redirect()->route('onboarding.buildings')
                ->with('error', 'Please complete the buildings setup first.');
        }

        $buildings = $this->onboardingService->getAvailableBuildings($user->company_id);

        return Inertia::render('Onboarding/Rooms', [
            'progress' => $progress,
            'currentStep' => 'rooms',
            'nextStep' => 'groups',
            'previousStep' => 'buildings',
            'buildings' => $buildings,
        ]);
    }

    /**
     * Store rooms during onboarding
     */
    public function storeRooms(CreateRoomsRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $result = $this->onboardingService->createRooms($user, $validated['rooms']);

        if (!$result['success']) {
            return back()->withErrors(['rooms' => $result['message']])->withInput();
        }

        return redirect()->route('onboarding.groups')
            ->with('success', $result['message']);
    }

    /**
     * Show the groups onboarding step
     */
    public function groups(Request $request): Response
    {
        $user = Auth::user();
        $progress = $this->onboardingService->getOnboardingProgress($user->company_id);

        // Redirect if previous steps not completed
        if (!$progress['buildings'] || !$progress['rooms']) {
            return redirect()->route('onboarding.buildings')
                ->with('error', 'Please complete the previous steps first.');
        }

        return Inertia::render('Onboarding/Groups', [
            'progress' => $progress,
            'currentStep' => 'groups',
            'nextStep' => 'users',
            'previousStep' => 'rooms',
        ]);
    }

    /**
     * Store groups during onboarding
     */
    public function storeGroups(CreateGroupsRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $result = $this->onboardingService->createGroups($user, $validated['groups']);

        if (!$result['success']) {
            return back()->withErrors(['groups' => $result['message']])->withInput();
        }

        return redirect()->route('onboarding.users')
            ->with('success', $result['message']);
    }

    /**
     * Show the users onboarding step
     */
    public function users(Request $request): Response
    {
        $user = Auth::user();
        $progress = $this->onboardingService->getOnboardingProgress($user->company_id);

        // Redirect if previous steps not completed
        if (!$progress['buildings'] || !$progress['rooms'] || !$progress['groups']) {
            return redirect()->route('onboarding.buildings')
                ->with('error', 'Please complete the previous steps first.');
        }

        $groups = $this->onboardingService->getAvailableGroups($user->company_id);
        $allowedRoles = ['booking_agent', 'hod', 'invitee'];
        $roleRequirements = [
            'booking_agent' => ['group_required' => true],
            'hod' => ['group_required' => true],
            'invitee' => ['group_required' => false],
        ];

        return Inertia::render('Onboarding/Users', [
            'progress' => $progress,
            'currentStep' => 'users',
            'nextStep' => 'labels',
            'previousStep' => 'groups',
            'groups' => $groups,
            'allowedRoles' => $allowedRoles,
            'roleRequirements' => $roleRequirements,
        ]);
    }

    /**
     * Store users during onboarding
     */
    public function storeUsers(CreateUsersRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $result = $this->onboardingService->createUsers($user, $validated['users']);

        if (!$result['success']) {
            return back()->withErrors(['users' => $result['message']])->withInput();
        }

        return redirect()->route('onboarding.labels')
            ->with('success', $result['message']);
    }

    /**
     * Show the labels onboarding step (final step)
     */
    public function labels(Request $request): Response
    {
        $user = Auth::user();
        $progress = $this->onboardingService->getOnboardingProgress($user->company_id);

        // Redirect if previous steps not completed
        if (!$progress['buildings'] || !$progress['rooms'] || !$progress['groups'] || !$progress['users']) {
            return redirect()->route('onboarding.buildings')
                ->with('error', 'Please complete the previous steps first.');
        }

        $labelTypes = [
            'department' => 'Department',
            'division' => 'Division',
            'team' => 'Team',
            'category' => 'Category',
            'location' => 'Location',
            'custom' => 'Custom',
        ];

        return Inertia::render('Onboarding/Labels', [
            'progress' => $progress,
            'currentStep' => 'labels',
            'previousStep' => 'users',
            'labelTypes' => $labelTypes,
            'isLastStep' => true,
        ]);
    }

    /**
     * Store labels during onboarding (final step)
     */
    public function storeLabels(SaveLabelsRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $result = $this->onboardingService->saveCompanyLabels($user, $validated['labels']);

        if (!$result['success']) {
            return back()->withErrors(['labels' => $result['message']])->withInput();
        }

        // Onboarding complete, redirect to admin dashboard
        return redirect()->route('admin.dashboard')
            ->with('success', 'Onboarding completed successfully! Welcome to VenuePro.');
    }

    /**
     * Skip a step (if allowed) - redirect to next step
     */
    public function skipStep(Request $request, string $step): RedirectResponse
    {
        $user = Auth::user();
        $progress = $this->onboardingService->getOnboardingProgress($user->company_id);
        
        $stepRoutes = [
            'buildings' => 'onboarding.rooms',
            'rooms' => 'onboarding.groups',
            'groups' => 'onboarding.users',
            'users' => 'onboarding.labels',
            'labels' => 'admin.dashboard',
        ];

        // Only allow skipping to next step if current and previous requirements are met
        $canSkip = false;
        switch ($step) {
            case 'buildings':
                $canSkip = true; // First step
                break;
            case 'rooms':
                $canSkip = $progress['buildings'];
                break;
            case 'groups':
                $canSkip = $progress['buildings'] && $progress['rooms'];
                break;
            case 'users':
                $canSkip = $progress['buildings'] && $progress['rooms'] && $progress['groups'];
                break;
            case 'labels':
                $canSkip = $progress['buildings'] && $progress['rooms'] && $progress['groups'] && $progress['users'];
                break;
        }

        if (!$canSkip) {
            return redirect()->route('onboarding.buildings')
                ->with('error', 'Cannot skip this step. Please complete the previous steps first.');
        }

        if (isset($stepRoutes[$step])) {
            return redirect()->route($stepRoutes[$step])
                ->with('info', 'Step skipped. You can complete it later from the admin dashboard.');
        }

        return redirect()->route('onboarding.buildings')
            ->with('error', 'Invalid step specified.');
    }

    /**
     * Go back to a previous step
     */
    public function previousStep(Request $request, string $step): RedirectResponse
    {
        $stepRoutes = [
            'rooms' => 'onboarding.buildings',
            'groups' => 'onboarding.rooms',
            'users' => 'onboarding.groups',
            'labels' => 'onboarding.users',
        ];

        if (isset($stepRoutes[$step])) {
            return redirect()->route($stepRoutes[$step]);
        }

        return redirect()->route('onboarding.buildings');
    }

    /**
     * Get onboarding progress as JSON (for AJAX requests)
     */
    public function getProgress(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $progress = $this->onboardingService->getOnboardingProgress($user->company_id);
        $isComplete = $this->onboardingService->isOnboardingComplete($user->company_id);

        return response()->json([
            'progress' => $progress,
            'isComplete' => $isComplete,
        ]);
    }
}