<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyBreakTimeTable extends Migration
{
    public function up()
    {
        Schema::create('daily_break_time', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('room')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('building')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'all']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'day_of_week', 'is_active']);
            $table->index(['building_id', 'day_of_week', 'is_active']);
            $table->index(['company_id', 'day_of_week', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_break_time');
    }
}
