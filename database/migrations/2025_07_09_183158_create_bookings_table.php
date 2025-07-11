<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('room')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->enum('booking_type', ['internal', 'external'])->default('internal');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('booked_for_user_id')->nullable()->constrained('users');
            $table->enum('delegation_type', ['self', 'other'])->default('self');
            $table->string('external_reference')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->json('recurring_pattern')->nullable();
            $table->unsignedBigInteger('parent_booking_id')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoice');
            $table->boolean('has_been_billed')->default(false);
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'date', 'status']);
            $table->index(['created_by', 'status']);
            $table->index('booking_type');
            $table->index('parent_booking_id');
            $table->index(['date', 'start_time', 'end_time']);
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking');
    }
}
