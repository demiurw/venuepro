<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyLabelsTable extends Migration
{
    public function up()
    {
        Schema::create('company_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('entity_type', 50);
            $table->string('singular_label', 100);
            $table->string('plural_label', 100);
            $table->string('display_label', 100)->nullable();
            $table->string('short_label', 50)->nullable();
            $table->string('description_label')->nullable();
            $table->string('label_category', 50)->nullable();
            $table->boolean('is_system_default')->default(false);
            $table->boolean('is_company_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'entity_type', 'is_company_default']);
            $table->index(['entity_type', 'is_system_default']);
            $table->index('label_category');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_labels');
    }
}
