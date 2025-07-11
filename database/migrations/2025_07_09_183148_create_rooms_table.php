<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('building_id')->constrained('building')->onDelete('cascade');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->enum('room_type', ['meeting_room', 'conference_room', 'board_room', 'training_room', 'event_space', 'other'])->default('meeting_room');
            $table->integer('capacity')->default(1);
            $table->string('floor', 50)->nullable();
            $table->string('room_number', 50)->nullable();
            $table->boolean('is_private')->default(false);
            $table->string('color_scheme', 7)->default('#3B82F6');
            $table->foreignId('module_id')->nullable()->constrained('module');
            $table->enum('availability_type', ['standard', 'custom', '24/7'])->default('standard');
            $table->foreignId('service_level_id')->nullable()->constrained('service_level');
            $table->integer('buffer_time_minutes')->nullable();
            $table->integer('min_notice_minutes')->nullable();
            $table->integer('max_notice_days')->nullable();
            $table->integer('max_hours_per_day')->nullable();
            $table->boolean('allow_external_booking')->default(false);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['building_id', 'is_active']);
            $table->index('room_type');
            $table->index('capacity');
            $table->index('is_private');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('room');
    }
}
