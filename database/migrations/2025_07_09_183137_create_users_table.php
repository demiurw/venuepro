<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->foreignId('role_id')->constrained();
            $table->unsignedBigInteger('group_id')->nullable(); // Just the column, no foreign key yet
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->enum('user_type', ['venuepro_admin', 'system_admin', 'hod', 'booking_agent', 'invitee', 'external'])->default('invitee');
            $table->enum('auth_method', ['password', 'otp', 'oauth'])->default('otp');
            $table->string('email_verification_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('otp_secret')->nullable();
            $table->json('oauth_providers')->nullable();
            $table->string('oauth_id')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('status');
            $table->index('user_type');
            $table->index('company_id');
            $table->index('group_id');
            $table->index('email_verification_token');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
