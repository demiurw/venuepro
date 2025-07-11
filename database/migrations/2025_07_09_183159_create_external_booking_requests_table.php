<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalBookingRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('external_booking_request', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('room')->onDelete('cascade');
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('requester_phone', 20)->nullable();
            $table->string('company_name')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('attendee_count')->default(1);
            $table->enum('status', ['pending', 'approved', 'denied', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('booking_id')->nullable()->constrained('booking');
            $table->string('token', 100)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'status']);
            $table->index(['date', 'status']);
            $table->index('requester_email');
            $table->index('token');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('external_booking_request');
    }
}
