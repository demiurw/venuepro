<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedDatesTable extends Migration
{
    public function up()
    {
        Schema::create('blocked_date', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('room')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('building')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('title');
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->boolean('is_recurring')->default(false);
            $table->json('recurring_pattern')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'date']);
            $table->index(['building_id', 'date']);
            $table->index(['company_id', 'date']);
            $table->index('is_holiday');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blocked_date');
    }
}
