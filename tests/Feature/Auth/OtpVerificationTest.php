<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Company;
use App\Models\OtpAttempt;
use App\Services\Auth\OtpService;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Carbon\Carbon;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected OtpService $otpService;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->otpService = app(OtpService::class);
        
        // Create a test company for multi-tenancy
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
        
        // Fake mail to prevent actual emails being sent
        Mail::fake();
    }

    /** @test */
    public function it_can_generate_otp_for_existing_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
            'auth_method' => 'otp',
        ]);

        $result = $this->otpService->generateOtp($user, 'login');

        $this->assertTrue($result['success']);
        $this->assertEquals('OTP sent to your email address.', $result['message']);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertArrayHasKey('otp_id', $result);

        // Verify OTP attempt was created
        $this->assertDatabaseHas('otp_attempts', [
            'user_id' => $user->id,
            'email' => $user->email,
            'company_id' => $user->company_id,
            'is_used' => false,
            'purpose' => 'login',
        ]);
    }

    /** @test */
    public function it_can_generate_otp_for_email_registration()
    {
        $email = 'newuser@test.com';

        $result = $this->otpService->generateOtpForEmail($email, 'registration');

        $this->assertTrue($result['success']);
        $this->assertEquals('OTP sent to your email address.', $result['message']);

        // Verify OTP attempt was created
        $this->assertDatabaseHas('otp_attempts', [
            'email' => $email,
            'user_id' => null,
            'company_id' => null,
            'is_used' => false,
            'purpose' => 'registration',
        ]);
    }

    /** @test */
    public function it_prevents_otp_generation_for_already_registered_email()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'existing@test.com',
        ]);

        $result = $this->otpService->generateOtpForEmail($user->email, 'registration');

        $this->assertFalse($result['success']);
        $this->assertEquals('Email address is already registered.', $result['message']);
    }

    /** @test */
    public function it_enforces_rate_limiting_for_otp_generation()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate first OTP
        $result1 = $this->otpService->generateOtp($user, 'login');
        $this->assertTrue($result1['success']);

        // Try to generate second OTP immediately (should be rate limited)
        $result2 = $this->otpService->generateOtp($user, 'login');
        $this->assertFalse($result2['success']);
        $this->assertEquals('Please wait before requesting another OTP.', $result2['message']);
        $this->assertArrayHasKey('wait_time', $result2);
        $this->assertGreaterThan(0, $result2['wait_time']);
    }

    /** @test */
    public function it_can_verify_valid_otp_for_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate OTP
        $generateResult = $this->otpService->generateOtp($user, 'login');
        $this->assertTrue($generateResult['success']);

        // Get the generated OTP from the database (for testing)
        $otpAttempt = OtpAttempt::where('user_id', $user->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Verify the OTP
        $verifyResult = $this->otpService->verifyOtp($user, $otpAttempt->otp_code);

        $this->assertTrue($verifyResult['success']);
        $this->assertEquals('OTP verified successfully.', $verifyResult['message']);

        // Verify OTP attempt was marked as used
        $this->assertDatabaseHas('otp_attempts', [
            'id' => $otpAttempt->id,
            'is_used' => true,
        ]);
    }

    /** @test */
    public function it_rejects_invalid_otp_code()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');

        // Try to verify with wrong OTP
        $result = $this->otpService->verifyOtp($user, '999999');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid OTP code.', $result['message']);
        $this->assertArrayHasKey('attempts_remaining', $result);
    }

    /** @test */
    public function it_handles_expired_otp()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Create an expired OTP attempt
        $expiredOtp = OtpAttempt::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '123456',
            'expires_at' => Carbon::now()->subMinutes(15), // Expired
            'is_used' => false,
            'attempt_count' => 0,
            'purpose' => 'login',
        ]);

        $result = $this->otpService->verifyOtp($user, '123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('OTP has expired. Please request a new one.', $result['message']);
        $this->assertTrue($result['expired'] ?? false);
    }

    /** @test */
    public function it_invalidates_otp_after_max_attempts()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');
        
        $otpAttempt = OtpAttempt::where('user_id', $user->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Make 3 failed attempts
        for ($i = 0; $i < 3; $i++) {
            $result = $this->otpService->verifyOtp($user, '999999');
            $this->assertFalse($result['success']);
        }

        // The OTP should now be invalidated
        $this->assertDatabaseHas('otp_attempts', [
            'id' => $otpAttempt->id,
            'is_used' => true,
            'attempt_count' => 3,
        ]);

        // Fourth attempt should indicate max attempts exceeded
        $result = $this->otpService->verifyOtp($user, '999999');
        $this->assertFalse($result['success']);
        $this->assertEquals('No valid OTP found. Please request a new one.', $result['message']);
    }

    /** @test */
    public function it_can_verify_and_activate_pending_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
            'email' => 'pending@test.com',
        ]);

        // Generate OTP for email
        $this->otpService->generateOtpForEmail($user->email, 'registration');
        
        $otpAttempt = OtpAttempt::where('email', $user->email)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Verify and activate
        $result = $this->otpService->verifyOtpAndActivateUser($user->email, $otpAttempt->otp_code);

        $this->assertTrue($result['success']);
        $this->assertEquals('Account activated successfully.', $result['message']);

        // Verify user is now active
        $user->refresh();
        $this->assertTrue($user->isActive());
        $this->assertNotNull($user->email_verified_at);
    }

    /** @test */
    public function it_can_verify_otp_and_login_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
            'last_login_at' => null,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');
        
        $otpAttempt = OtpAttempt::where('user_id', $user->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Verify and login
        $result = $this->otpService->verifyOtpAndLogin($user, $otpAttempt->otp_code);

        $this->assertTrue($result['success']);
        $this->assertEquals('Login successful.', $result['message']);

        // Verify last login was updated
        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    /** @test */
    public function it_activates_pending_user_during_login_verification()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');
        
        $otpAttempt = OtpAttempt::where('user_id', $user->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Verify and login (should activate pending user)
        $result = $this->otpService->verifyOtpAndLogin($user, $otpAttempt->otp_code);

        $this->assertTrue($result['success']);

        // Verify user was activated
        $user->refresh();
        $this->assertTrue($user->isActive());
    }

    /** @test */
    public function it_prevents_login_for_inactive_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::INACTIVE,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');
        
        $otpAttempt = OtpAttempt::where('user_id', $user->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Try to verify and login (should fail)
        $result = $this->otpService->verifyOtpAndLogin($user, $otpAttempt->otp_code);

        $this->assertFalse($result['success']);
        $this->assertEquals('Your account is not active. Please contact support.', $result['message']);
        $this->assertTrue($result['account_inactive'] ?? false);
    }

    /** @test */
    public function it_invalidates_existing_otps_when_generating_new_one()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Create an existing valid OTP
        $existingOtp = OtpAttempt::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '111111',
            'expires_at' => Carbon::now()->addMinutes(10),
            'is_used' => false,
            'attempt_count' => 0,
            'purpose' => 'login',
        ]);

        // Wait to avoid rate limiting
        Carbon::setTestNow(Carbon::now()->addMinutes(3));

        // Generate new OTP (should invalidate existing one)
        $result = $this->otpService->generateOtp($user, 'login');
        $this->assertTrue($result['success']);

        // Verify existing OTP was invalidated
        $existingOtp->refresh();
        $this->assertTrue($existingOtp->is_used);
    }

    /** @test */
    public function it_respects_multi_tenant_isolation()
    {
        // Create another company
        $company2 = Company::create([
            'name' => 'Company 2',
            'email' => 'test2@company.com',
            'phone' => '9876543210',
            'address' => '456 Test Ave',
            'city' => 'Test City 2',
            'state' => 'TS',
            'zip' => '54321',
            'country' => 'Test Country',
            'is_active' => true,
        ]);

        $user1 = User::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'user1@test.com',
            'status' => UserStatus::ACTIVE,
        ]);

        $user2 = User::factory()->create([
            'company_id' => $company2->id,
            'email' => 'user2@test.com',
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate OTP for user1
        $this->otpService->generateOtp($user1, 'login');
        
        $otpAttempt1 = OtpAttempt::where('user_id', $user1->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Try to verify user1's OTP with user2 (different tenant)
        $result = $this->otpService->verifyOtp($user2, $otpAttempt1->otp_code);

        $this->assertFalse($result['success']);
        $this->assertEquals('No valid OTP found. Please request a new one.', $result['message']);

        // Verify OTP attempts are isolated by company
        $user1Attempts = OtpAttempt::where('company_id', $this->company->id)->count();
        $user2Attempts = OtpAttempt::where('company_id', $company2->id)->count();

        $this->assertEquals(1, $user1Attempts);
        $this->assertEquals(0, $user2Attempts);
    }

    /** @test */
    public function it_cleans_up_expired_otps()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Create expired OTP attempts
        OtpAttempt::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '111111',
            'expires_at' => Carbon::now()->subMinutes(15),
            'is_used' => false,
            'attempt_count' => 0,
            'purpose' => 'login',
        ]);

        OtpAttempt::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '222222',
            'expires_at' => Carbon::now()->subMinutes(5),
            'is_used' => false,
            'attempt_count' => 0,
            'purpose' => 'login',
        ]);

        // Run cleanup
        $cleanedCount = $this->otpService->cleanupExpiredOtps();

        $this->assertEquals(2, $cleanedCount);

        // Verify OTPs were marked as used
        $this->assertDatabaseCount('otp_attempts', 2);
        $this->assertDatabaseMissing('otp_attempts', [
            'is_used' => false,
            'expires_at' => '<=', Carbon::now(),
        ]);
    }

    /** @test */
    public function it_handles_no_user_found_for_email_verification()
    {
        $email = 'nonexistent@test.com';

        $result = $this->otpService->verifyUserAndGenerateOtp($email, 'login');

        $this->assertFalse($result['success']);
        $this->assertEquals('No account found with this email address.', $result['message']);
    }

    /** @test */
    public function it_prevents_otp_generation_for_inactive_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::INACTIVE,
        ]);

        $result = $this->otpService->verifyUserAndGenerateOtp($user->email, 'login');

        $this->assertFalse($result['success']);
        $this->assertEquals('Your account is not active. Please contact support.', $result['message']);
        $this->assertTrue($result['account_inactive'] ?? false);
    }

    /** @test */
    public function it_allows_otp_generation_for_pending_user_with_account_verification()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        $result = $this->otpService->verifyUserAndGenerateOtp($user->email, 'account_verification');

        $this->assertTrue($result['success']);
        $this->assertEquals('OTP sent to your email address.', $result['message']);
    }

    /** @test */
    public function it_prevents_activation_of_non_pending_user()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
            'email' => 'active@test.com',
        ]);

        // Try to activate already active user
        $result = $this->otpService->verifyOtpAndActivateUser($user->email, '123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('No valid OTP found. Please request a new one.', $result['message']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset Carbon test time
        parent::tearDown();
    }
}