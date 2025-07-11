<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpAttemptsTable extends Migration
{
    public function up()
    {
        Schema::create('otp_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('otp_code', 10);
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->integer('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'is_used', 'expires_at']);
            $table->index('otp_code');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_attempts');
    }
}
