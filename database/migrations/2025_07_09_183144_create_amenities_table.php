<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmenitiesTable extends Migration
{
    public function up()
    {
        Schema::create('amenity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->string('category', 50)->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('is_chargeable')->default(false);
            $table->decimal('default_cost', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->index('category');
        });
    }

    public function down()
    {
        Schema::dropIfExists('amenity');
    }
}
