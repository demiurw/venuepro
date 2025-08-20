<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\OnboardingMiddleware;
use App\Models\User;
use App\Models\Company;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class OnboardingMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected OnboardingMiddleware $middleware;
    protected User $systemAdmin;
    protected User $regularUser;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->middleware = new OnboardingMiddleware();
        $this->company = Company::factory()->create();
        
        $this->systemAdmin = User::factory()->create([
            'user_type' => 'system_admin',
            'company_id' => $this->company->id,
            'onboarding_step_completed' => 0,
            'status' => UserStatus::ACTIVE,
        ]);
        
        $this->regularUser = User::factory()->create([
            'user_type' => 'booking_agent',
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    /** @test */
    public function it_allows_non_system_admin_users_to_pass_through()
    {
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->regularUser);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function it_allows_system_admin_to_access_onboarding_routes()
    {
        $request = Request::create('/onboarding/buildings', 'GET');
        $request->setRouteResolver(function () use ($request) {
            $route = new \Illuminate\Routing\Route('GET', '/onboarding/buildings', []);
            $route->name('onboarding.buildings');
            return $route;
        });
        
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Onboarding Page', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Onboarding Page', $response->getContent());
    }

    /** @test */
    public function it_redirects_system_admin_to_buildings_when_no_steps_completed()
    {
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/onboarding/buildings', $response->headers->get('location'));
    }

    /** @test */
    public function it_redirects_system_admin_to_rooms_when_buildings_completed()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 1]);
        
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/onboarding/rooms', $response->headers->get('location'));
    }

    /** @test */
    public function it_redirects_system_admin_to_groups_when_rooms_completed()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 2]);
        
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/onboarding/groups', $response->headers->get('location'));
    }

    /** @test */
    public function it_redirects_system_admin_to_users_when_groups_completed()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 3]);
        
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/onboarding/users', $response->headers->get('location'));
    }

    /** @test */
    public function it_redirects_system_admin_to_labels_when_users_completed()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 4]);
        
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/onboarding/labels', $response->headers->get('location'));
    }

    /** @test */
    public function it_allows_system_admin_to_access_dashboard_when_onboarding_complete()
    {
        $this->systemAdmin->update(['onboarding_step_completed' => 5]);
        
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Dashboard', $response->getContent());
    }

    /** @test */
    public function it_allows_access_to_all_onboarding_subroutes()
    {
        $onboardingRoutes = [
            '/onboarding/buildings',
            '/onboarding/rooms',
            '/onboarding/groups',
            '/onboarding/users',
            '/onboarding/labels',
            '/onboarding/skip/buildings',
            '/onboarding/previous/rooms',
            '/onboarding/progress'
        ];

        foreach ($onboardingRoutes as $route) {
            $request = Request::create($route, 'GET');
            $request->setRouteResolver(function () use ($route) {
                $routeObj = new \Illuminate\Routing\Route('GET', $route, []);
                $routeObj->name('onboarding.test');
                return $routeObj;
            });
            
            $this->be($this->systemAdmin);

            $response = $this->middleware->handle($request, function ($req) {
                return new Response('Onboarding', 200);
            });

            $this->assertEquals(200, $response->getStatusCode(), "Failed for route: {$route}");
        }
    }

    /** @test */
    public function it_handles_unauthenticated_user_gracefully()
    {
        $request = Request::create('/admin/dashboard', 'GET');
        // No authenticated user

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        // Should pass through since no user is authenticated
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_handles_user_without_company_gracefully()
    {
        $userWithoutCompany = User::factory()->create([
            'user_type' => 'system_admin',
            'company_id' => null,
            'onboarding_step_completed' => 0,
            'status' => UserStatus::ACTIVE,
        ]);
        
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($userWithoutCompany);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        // Should redirect to buildings step even without company
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/onboarding/buildings', $response->headers->get('location'));
    }

    /** @test */
    public function it_redirects_to_buildings_for_invalid_step_values()
    {
        // Test negative step value
        $this->systemAdmin->update(['onboarding_step_completed' => -1]);
        
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/onboarding/buildings', $response->headers->get('location'));
    }

    /** @test */
    public function it_handles_step_values_greater_than_maximum()
    {
        // Test step value greater than 5
        $this->systemAdmin->update(['onboarding_step_completed' => 10]);
        
        $request = Request::create('/admin/dashboard', 'GET');
        $this->be($this->systemAdmin);

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Dashboard', 200);
        });

        // Should allow access since onboarding is "complete"
        $this->assertEquals(200, $response->getStatusCode());
    }

    /** @test */
    public function it_works_with_different_user_types()
    {
        $userTypes = ['hod', 'booking_agent', 'invitee', 'external'];

        foreach ($userTypes as $userType) {
            $user = User::factory()->create([
                'user_type' => $userType,
                'company_id' => $this->company->id,
                'status' => UserStatus::ACTIVE,
            ]);
            
            $request = Request::create('/admin/dashboard', 'GET');
            $this->be($user);

            $response = $this->middleware->handle($request, function ($req) {
                return new Response('Dashboard', 200);
            });

            // Non-system admin users should pass through
            $this->assertEquals(200, $response->getStatusCode(), "Failed for user type: {$userType}");
        }
    }
}