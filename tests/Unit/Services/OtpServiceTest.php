<?php

namespace Tests\Unit\Services;

use App\Services\Auth\OtpService;
use App\Models\User;
use App\Models\Company;
use App\Models\OtpAttempt;
use App\Mail\OtpMail;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;
use Mockery;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OtpService $otpService;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->otpService = app(OtpService::class);
        
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
        
        Mail::fake();
    }

    /** @test */
    public function it_generates_otp_with_correct_length()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $result = $this->otpService->generateOtp($user, 'login');

        $this->assertTrue($result['success']);
        
        $otpAttempt = OtpAttempt::where('user_id', $user->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        $this->assertNotNull($otpAttempt);
        $this->assertEquals(6, strlen($otpAttempt->otp_code));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otpAttempt->otp_code);
    }

    /** @test */
    public function it_sets_correct_expiration_time()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $beforeGeneration = Carbon::now();
        $result = $this->otpService->generateOtp($user, 'login');
        $afterGeneration = Carbon::now();

        $this->assertTrue($result['success']);
        
        $otpAttempt = OtpAttempt::where('user_id', $user->id)
            ->where('is_used', false)
            ->latest()
            ->first();

        $expectedMinExpiry = $beforeGeneration->addMinutes(10);
        $expectedMaxExpiry = $afterGeneration->addMinutes(10);

        $this->assertTrue($otpAttempt->expires_at->between($expectedMinExpiry, $expectedMaxExpiry));
    }

    /** @test */
    public function it_stores_correct_metadata_for_user_otp()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
            'email' => 'test@example.com',
        ]);

        $result = $this->otpService->generateOtp($user, 'account_verification');

        $this->assertTrue($result['success']);
        
        $this->assertDatabaseHas('otp_attempts', [
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'purpose' => 'account_verification',
            'is_used' => false,
            'attempt_count' => 0,
        ]);
    }

    /** @test */
    public function it_stores_correct_metadata_for_email_otp()
    {
        $email = 'newuser@example.com';

        $result = $this->otpService->generateOtpForEmail($email, 'registration');

        $this->assertTrue($result['success']);
        
        $this->assertDatabaseHas('otp_attempts', [
            'company_id' => null,
            'user_id' => null,
            'email' => $email,
            'purpose' => 'registration',
            'is_used' => false,
            'attempt_count' => 0,
        ]);
    }

    /** @test */
    public function it_sends_email_with_correct_content()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
            'email' => 'user@example.com',
            'first_name' => 'John',
        ]);

        $result = $this->otpService->generateOtp($user, 'login');

        $this->assertTrue($result['success']);
        
        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /** @test */
    public function it_invalidates_existing_otps_when_generating_new_one()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Create existing OTP
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

        $result = $this->otpService->generateOtp($user, 'login');

        $this->assertTrue($result['success']);
        
        $existingOtp->refresh();
        $this->assertTrue($existingOtp->is_used);
    }

    /** @test */
    public function it_enforces_rate_limiting()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate first OTP
        $result1 = $this->otpService->generateOtp($user, 'login');
        $this->assertTrue($result1['success']);

        // Try to generate second OTP immediately
        $result2 = $this->otpService->generateOtp($user, 'login');
        $this->assertFalse($result2['success']);
        $this->assertEquals('Please wait before requesting another OTP.', $result2['message']);
        $this->assertArrayHasKey('wait_time', $result2);
        $this->assertGreaterThan(0, $result2['wait_time']);
    }

    /** @test */
    public function it_calculates_remaining_wait_time_correctly()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');

        // Wait 1 minute
        Carbon::setTestNow(Carbon::now()->addMinute());

        // Try to generate again
        $result = $this->otpService->generateOtp($user, 'login');
        $this->assertFalse($result['success']);
        
        // Should have approximately 1 minute remaining (2 minutes total - 1 minute elapsed)
        $this->assertGreaterThan(50, $result['wait_time']);
        $this->assertLessThan(70, $result['wait_time']);
    }

    /** @test */
    public function it_verifies_correct_otp_successfully()
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

        $result = $this->otpService->verifyOtp($user, $otpAttempt->otp_code);

        $this->assertTrue($result['success']);
        $this->assertEquals('OTP verified successfully.', $result['message']);
        
        $otpAttempt->refresh();
        $this->assertTrue($otpAttempt->is_used);
    }

    /** @test */
    public function it_rejects_incorrect_otp()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');

        $result = $this->otpService->verifyOtp($user, '999999');

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid OTP code.', $result['message']);
        $this->assertArrayHasKey('attempts_remaining', $result);
        $this->assertEquals(2, $result['attempts_remaining']);
    }

    /** @test */
    public function it_tracks_verification_attempts()
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

        // Make two incorrect attempts
        $this->otpService->verifyOtp($user, '111111');
        $this->otpService->verifyOtp($user, '222222');

        $otpAttempt->refresh();
        $this->assertEquals(2, $otpAttempt->attempt_count);
        $this->assertFalse($otpAttempt->is_used);
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

        // Make 3 incorrect attempts (max attempts)
        for ($i = 0; $i < 3; $i++) {
            $result = $this->otpService->verifyOtp($user, '999999');
            
            if ($i < 2) {
                $this->assertFalse($result['success']);
                $this->assertEquals('Invalid OTP code.', $result['message']);
            } else {
                $this->assertFalse($result['success']);
                $this->assertEquals('Maximum attempts exceeded. Please request a new OTP.', $result['message']);
                $this->assertTrue($result['max_attempts_exceeded'] ?? false);
            }
        }

        // Verify OTP is now invalidated
        $otpAttempt = OtpAttempt::where('user_id', $user->id)
            ->latest()
            ->first();
        
        $this->assertTrue($otpAttempt->is_used);
        $this->assertEquals(3, $otpAttempt->attempt_count);
    }

    /** @test */
    public function it_handles_expired_otp_verification()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Create expired OTP
        $expiredOtp = OtpAttempt::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '123456',
            'expires_at' => Carbon::now()->subMinutes(15),
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
    public function it_provides_helpful_error_for_no_otp()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $result = $this->otpService->verifyOtp($user, '123456');

        $this->assertFalse($result['success']);
        $this->assertEquals('No valid OTP found. Please request a new one.', $result['message']);
    }

    /** @test */
    public function it_provides_better_error_for_expired_otp_when_none_active()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Create expired OTP
        OtpAttempt::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '123456',
            'expires_at' => Carbon::now()->subMinutes(15),
            'is_used' => false,
            'attempt_count' => 0,
            'purpose' => 'login',
        ]);

        $result = $this->otpService->verifyOtp($user, '999999');

        $this->assertFalse($result['success']);
        $this->assertEquals('OTP has expired. Please request a new one.', $result['message']);
        $this->assertTrue($result['expired'] ?? false);
    }

    /** @test */
    public function it_verifies_user_existence_for_email_otp_generation()
    {
        $result = $this->otpService->verifyUserAndGenerateOtp('nonexistent@example.com', 'login');

        $this->assertFalse($result['success']);
        $this->assertEquals('No account found with this email address.', $result['message']);
    }

    /** @test */
    public function it_checks_user_status_for_otp_generation()
    {
        $inactiveUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::INACTIVE,
        ]);

        $result = $this->otpService->verifyUserAndGenerateOtp($inactiveUser->email, 'login');

        $this->assertFalse($result['success']);
        $this->assertEquals('Your account is not active. Please contact support.', $result['message']);
        $this->assertTrue($result['account_inactive'] ?? false);
    }

    /** @test */
    public function it_allows_otp_for_pending_user_with_account_verification()
    {
        $pendingUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        $result = $this->otpService->verifyUserAndGenerateOtp($pendingUser->email, 'account_verification');

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_prevents_otp_for_pending_user_with_login_purpose()
    {
        $pendingUser = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
        ]);

        $result = $this->otpService->verifyUserAndGenerateOtp($pendingUser->email, 'login');

        $this->assertFalse($result['success']);
        $this->assertEquals('Your account is not active. Please contact support.', $result['message']);
    }

    /** @test */
    public function it_performs_user_activation_in_transaction()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
            'email' => 'pending@example.com',
        ]);

        // Generate OTP for email
        $this->otpService->generateOtpForEmail($user->email, 'registration');
        
        $otpAttempt = OtpAttempt::where('email', $user->email)
            ->where('is_used', false)
            ->latest()
            ->first();

        // Mock database to test transaction
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $result = $this->otpService->verifyOtpAndActivateUser($user->email, $otpAttempt->otp_code);

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_updates_otp_attempt_with_user_info_on_activation()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::PENDING,
            'email' => 'pending@example.com',
        ]);

        // Generate OTP for email
        $this->otpService->generateOtpForEmail($user->email, 'registration');
        
        $otpAttempt = OtpAttempt::where('email', $user->email)
            ->where('is_used', false)
            ->latest()
            ->first();

        $this->assertNull($otpAttempt->user_id);
        $this->assertNull($otpAttempt->company_id);

        $result = $this->otpService->verifyOtpAndActivateUser($user->email, $otpAttempt->otp_code);

        $this->assertTrue($result['success']);
        
        $otpAttempt->refresh();
        $this->assertEquals($user->id, $otpAttempt->user_id);
        $this->assertEquals($user->company_id, $otpAttempt->company_id);
    }

    /** @test */
    public function it_handles_login_with_pending_user_activation()
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

        $result = $this->otpService->verifyOtpAndLogin($user, $otpAttempt->otp_code);

        $this->assertTrue($result['success']);
        
        $user->refresh();
        $this->assertTrue($user->isActive());
        $this->assertNotNull($user->last_login_at);
    }

    /** @test */
    public function it_cleans_up_expired_otps()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Create expired OTPs
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

        // Create valid OTP (should not be cleaned up)
        OtpAttempt::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'otp_code' => '333333',
            'expires_at' => Carbon::now()->addMinutes(5),
            'is_used' => false,
            'attempt_count' => 0,
            'purpose' => 'login',
        ]);

        $cleanedCount = $this->otpService->cleanupExpiredOtps();

        $this->assertEquals(2, $cleanedCount);
        
        // Verify expired OTPs were marked as used
        $this->assertDatabaseHas('otp_attempts', [
            'otp_code' => '111111',
            'is_used' => true,
        ]);
        
        $this->assertDatabaseHas('otp_attempts', [
            'otp_code' => '222222',
            'is_used' => true,
        ]);
        
        // Verify valid OTP was not affected
        $this->assertDatabaseHas('otp_attempts', [
            'otp_code' => '333333',
            'is_used' => false,
        ]);
    }

    /** @test */
    public function it_provides_debug_otp_in_debug_mode()
    {
        config(['app.debug' => true]);
        
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $result = $this->otpService->generateOtp($user, 'login');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('debug_otp', $result);
        $this->assertNotNull($result['debug_otp']);
        $this->assertEquals(6, strlen($result['debug_otp']));
    }

    /** @test */
    public function it_hides_debug_otp_in_production_mode()
    {
        config(['app.debug' => false]);
        
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        $result = $this->otpService->generateOtp($user, 'login');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('debug_otp', $result);
        $this->assertNull($result['debug_otp']);
    }

    /** @test */
    public function it_gets_latest_otp_for_user_in_debug_mode()
    {
        config(['app.debug' => true]);
        
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');
        
        $otpCode = $this->otpService->getLatestOtpForUser($user);

        $this->assertNotNull($otpCode);
        $this->assertEquals(6, strlen($otpCode));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otpCode);
    }

    /** @test */
    public function it_returns_null_for_latest_otp_in_production_mode()
    {
        config(['app.debug' => false]);
        
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'status' => UserStatus::ACTIVE,
        ]);

        // Generate OTP
        $this->otpService->generateOtp($user, 'login');
        
        $otpCode = $this->otpService->getLatestOtpForUser($user);

        $this->assertNull($otpCode);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();
        parent::tearDown();
    }
}