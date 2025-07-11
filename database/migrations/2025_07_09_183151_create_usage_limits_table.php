<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsageLimitsTable extends Migration
{
    public function up()
    {
        Schema::create('usage_limit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->string('resource', 100);
            $table->integer('limit_value');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'group_id', 'resource']);
            $table->index('resource');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_limit');
    }
}
