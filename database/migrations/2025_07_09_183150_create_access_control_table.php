<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessControlTable extends Migration
{
    public function up()
    {
        Schema::create('access_control', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->enum('entity_type', ['room', 'building']);
            $table->unsignedBigInteger('entity_id');
            $table->enum('access_level', ['view', 'book', 'manage'])->default('view');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['company_id', 'group_id', 'entity_type', 'entity_id']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('access_level');
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_control');
    }
}
