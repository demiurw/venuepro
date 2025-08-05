<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Company;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UserStatusMethodsTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'TS',
            'zip' => '12345',
            'country' => 'Test Country',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_correctly_identifies_active_users()
    {
        $activeUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $inactiveUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::INACTIVE,
        ]);

        $pendingUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        $this->assertTrue($activeUser->isActive());
        $this->assertFalse($inactiveUser->isActive());
        $this->assertFalse($pendingUser->isActive());
    }

    /** @test */
    public function it_correctly_identifies_pending_users()
    {
        $activeUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $inactiveUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::INACTIVE,
        ]);

        $pendingUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        $this->assertFalse($activeUser->isPending());
        $this->assertFalse($inactiveUser->isPending());
        $this->assertTrue($pendingUser->isPending());
    }

    /** @test */
    public function it_correctly_identifies_inactive_users()
    {
        $activeUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $inactiveUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::INACTIVE,
        ]);

        $pendingUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        $this->assertFalse($activeUser->isInactive());
        $this->assertTrue($inactiveUser->isInactive());
        $this->assertFalse($pendingUser->isInactive());
    }

    /** @test */
    public function it_correctly_determines_system_access()
    {
        $activeUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $inactiveUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::INACTIVE,
        ]);

        $pendingUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        $this->assertTrue($activeUser->canAccessSystem());
        $this->assertFalse($inactiveUser->canAccessSystem());
        $this->assertFalse($pendingUser->canAccessSystem());
    }

    /** @test */
    public function it_correctly_determines_verification_requirement()
    {
        $activeUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $inactiveUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::INACTIVE,
        ]);

        $pendingUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        $this->assertFalse($activeUser->requiresVerification());
        $this->assertFalse($inactiveUser->requiresVerification());
        $this->assertTrue($pendingUser->requiresVerification());
    }

    /** @test */
    public function it_can_activate_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
            'email_verified_at' => null,
        ]);

        $result = $user->activate();

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue($user->isActive());
        $this->assertNotNull($user->email_verified_at);
    }

    /** @test */
    public function it_preserves_existing_email_verification_date_when_activating()
    {
        $verificationDate = Carbon::now()->subDays(5);
        
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
            'email_verified_at' => $verificationDate,
        ]);

        $user->activate();
        $user->refresh();

        $this->assertEquals($verificationDate->format('Y-m-d H:i:s'), $user->email_verified_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_deactivate_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $result = $user->deactivate();

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue($user->isInactive());
    }

    /** @test */
    public function it_can_set_user_to_pending()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $result = $user->setPending();

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue($user->isPending());
    }

    /** @test */
    public function it_can_record_successful_login()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
            'last_login_at' => null,
        ]);

        $beforeLogin = Carbon::now()->subSecond();
        $user->recordSuccessfulLogin();
        $afterLogin = Carbon::now()->addSecond();

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertTrue($user->last_login_at->between($beforeLogin, $afterLogin));
    }

    /** @test */
    public function it_auto_activates_pending_user_with_verified_email_on_login()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
            'email_verified_at' => Carbon::now()->subHour(),
            'last_login_at' => null,
        ]);

        $user->recordSuccessfulLogin();
        $user->refresh();

        $this->assertTrue($user->isActive());
        $this->assertNotNull($user->last_login_at);
    }

    /** @test */
    public function it_does_not_auto_activate_pending_user_without_verified_email()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
            'email_verified_at' => null,
            'last_login_at' => null,
        ]);

        $user->recordSuccessfulLogin();
        $user->refresh();

        $this->assertTrue($user->isPending());
        $this->assertNotNull($user->last_login_at);
    }

    /** @test */
    public function it_can_generate_email_verification_token()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'email_verification_token' => null,
        ]);

        $token = $user->generateEmailVerificationToken();

        $this->assertIsString($token);
        $this->assertEquals(100, strlen($token)); // 50 bytes = 100 hex chars
        
        $user->refresh();
        $this->assertEquals($token, $user->email_verification_token);
    }

    /** @test */
    public function it_can_mark_email_as_verified()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'email_verified_at' => null,
            'email_verification_token' => 'some_token',
        ]);

        $beforeVerification = Carbon::now()->subSecond();
        $user->markEmailAsVerified();
        $afterVerification = Carbon::now()->addSecond();

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->email_verified_at->between($beforeVerification, $afterVerification));
        $this->assertNull($user->email_verification_token);
    }

    /** @test */
    public function it_correctly_checks_email_verification_status()
    {
        $verifiedUser = User::factory()->create([
            'company_id' => $this->company->id,
            'email_verified_at' => Carbon::now(),
        ]);

        $unverifiedUser = User::factory()->create([
            'company_id' => $this->company->id,
            'email_verified_at' => null,
        ]);

        $this->assertTrue($verifiedUser->hasVerifiedEmail());
        $this->assertFalse($unverifiedUser->hasVerifiedEmail());
    }

    /** @test */
    public function it_can_generate_otp_secret()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'otp_secret' => null,
        ]);

        $secret = $user->generateOtpSecret();

        $this->assertIsString($secret);
        $this->assertEquals(40, strlen($secret)); // 20 bytes = 40 hex chars
        
        $user->refresh();
        $this->assertEquals($secret, $user->otp_secret);
    }

    /** @test */
    public function it_correctly_checks_otp_secret_existence()
    {
        $userWithSecret = User::factory()->create([
            'company_id' => $this->company->id,
            'otp_secret' => 'some_secret',
        ]);

        $userWithoutSecret = User::factory()->create([
            'company_id' => $this->company->id,
            'otp_secret' => null,
        ]);

        $this->assertTrue($userWithSecret->hasOtpSecret());
        $this->assertFalse($userWithoutSecret->hasOtpSecret());
    }

    /** @test */
    public function it_correctly_identifies_auth_methods()
    {
        $otpUser = User::factory()->create([
            'company_id' => $this->company->id,
            'auth_method' => 'otp',
        ]);

        $oauthUser = User::factory()->create([
            'company_id' => $this->company->id,
            'auth_method' => 'oauth',
        ]);

        $this->assertTrue($otpUser->usesOtpAuth());
        $this->assertTrue($otpUser->isOtpUser());
        $this->assertFalse($otpUser->usesOAuthAuth());

        $this->assertFalse($oauthUser->usesOtpAuth());
        $this->assertFalse($oauthUser->isOtpUser());
        $this->assertTrue($oauthUser->usesOAuthAuth());
    }

    /** @test */
    public function it_provides_full_name_attribute()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->full_name);
    }

    /** @test */
    public function it_provides_display_name_attribute()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $this->assertEquals('Jane Smith', $user->display_name);
    }

    /** @test */
    public function it_can_record_basic_login()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'last_login_at' => null,
        ]);

        $beforeLogin = Carbon::now()->subSecond();
        $user->recordLogin();
        $afterLogin = Carbon::now()->addSecond();

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertTrue($user->last_login_at->between($beforeLogin, $afterLogin));
    }

    /** @test */
    public function status_transitions_work_correctly()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        // Pending -> Active
        $this->assertTrue($user->isPending());
        $user->activate();
        $user->refresh();
        $this->assertTrue($user->isActive());

        // Active -> Inactive
        $user->deactivate();
        $user->refresh();
        $this->assertTrue($user->isInactive());

        // Inactive -> Pending
        $user->setPending();
        $user->refresh();
        $this->assertTrue($user->isPending());

        // Pending -> Active again
        $user->activate();
        $user->refresh();
        $this->assertTrue($user->isActive());
    }
}