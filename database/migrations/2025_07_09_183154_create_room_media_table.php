<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomMediaTable extends Migration
{
    public function up()
    {
        Schema::create('room_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('room')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 100);
            $table->bigInteger('file_size');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'is_primary']);
            $table->index('sort_order');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('room_media');
    }
}
