<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingAttendeeTable extends Migration
{
    public function up()
    {
        Schema::create('booking_attendee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('booking')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('attendee_type', ['internal', 'external', 'guest'])->default('internal');
            $table->enum('status', ['pending', 'accepted', 'declined', 'maybe'])->default('pending');
            $table->text('response_message')->nullable();
            $table->boolean('is_organizer')->default(false);
            $table->string('access_token', 100)->nullable();
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'status']);
            $table->index('email');
            $table->index('access_token');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_attendee');
    }
}
