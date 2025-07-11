<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('system_setting', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->text('value');
            $table->enum('type', ['string', 'integer', 'boolean', 'json', 'array'])->default('string');
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('key');
            $table->index('is_public');
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_setting');
    }
}
