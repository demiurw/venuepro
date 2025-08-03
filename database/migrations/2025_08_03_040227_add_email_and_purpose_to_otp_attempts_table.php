<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('otp_attempts', function (Blueprint $table) {
            // Add email field for registration OTPs that don't have a user_id yet
            $table->string('email')->nullable()->after('user_id');
            
            // Add purpose field to track different OTP types (login, registration, account_verification, etc.)
            $table->string('purpose', 50)->default('login')->after('attempt_count');
            
            // Make user_id and company_id nullable to support registration OTPs
            $table->foreignId('company_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->change();
            
            // Add index for email-based lookups
            $table->index(['email', 'is_used', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otp_attempts', function (Blueprint $table) {
            $table->dropIndex(['email', 'is_used', 'expires_at']);
            $table->dropColumn(['email', 'purpose']);
            
            // Note: Reversing nullable constraints requires manual intervention
            // as Laravel doesn't handle this well. For safety, we'll leave them nullable.
        });
    }
};
