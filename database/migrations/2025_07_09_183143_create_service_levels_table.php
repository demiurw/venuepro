<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceLevelsTable extends Migration
{
    public function up()
    {
        Schema::create('service_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->integer('buffer_time_minutes')->default(0);
            $table->integer('min_notice_minutes')->default(0);
            $table->integer('max_notice_days')->default(365);
            $table->integer('max_daily_bookings')->nullable();
            $table->integer('max_hours_per_day')->nullable();
            $table->boolean('allow_external_booking')->default(false);
            $table->decimal('default_hourly_rate', 10, 2)->nullable();
            $table->decimal('default_daily_rate', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_level');
    }
}
