<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomAmenityTable extends Migration
{
    public function up()
    {
        Schema::create('room_amenity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('room')->onDelete('cascade');
            $table->foreignId('amenity_id')->constrained('amenity')->onDelete('cascade');
            $table->decimal('additional_cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'room_id', 'amenity_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('room_amenity');
    }
}
