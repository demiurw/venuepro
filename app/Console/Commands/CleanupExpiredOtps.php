<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Auth\OtpService;

class CleanupExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup
                            {--dry-run : Show what would be cleaned up without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTP attempts from the database';

    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        parent::__construct();
        $this->otpService = $otpService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting OTP cleanup...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');

            $expiredCount = \App\Models\OtpAttempt::where('expires_at', '<', now())
                ->where('is_used', false)
                ->count();

            $this->info("Would mark {$expiredCount} expired OTP attempts as used.");
        } else {
            $cleanedCount = $this->otpService->cleanupExpiredOtps();

            $this->info("Successfully marked {$cleanedCount} expired OTP attempts as used.");
        }

        // Show statistics
        $this->showStatistics();

        $this->info('OTP cleanup completed.');
    }

    protected function showStatistics()
    {
        $this->newLine();
        $this->line('<info>Current OTP Statistics:</info>');

        $totalOtps = \App\Models\OtpAttempt::count();
        $activeOtps = \App\Models\OtpAttempt::active()->count();
        $expiredOtps = \App\Models\OtpAttempt::expired()->where('is_used', false)->count();
        $usedOtps = \App\Models\OtpAttempt::used()->count();

        $this->table(
            ['Status', 'Count'],
            [
                ['Total OTPs', $totalOtps],
                ['Active OTPs', $activeOtps],
                ['Expired (unused)', $expiredOtps],
                ['Used OTPs', $usedOtps],
            ]
        );

        if ($expiredOtps > 0) {
            $this->warn("There are {$expiredOtps} expired OTP attempts that should be cleaned up.");
        }
    }
}
