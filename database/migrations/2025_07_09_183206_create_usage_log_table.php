<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsageLogTable extends Migration
{
    public function up()
    {
        Schema::create('usage_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('resource', 100);
            $table->integer('usage_value')->default(1);
            $table->timestamp('logged_at')->useCurrent();

            $table->index(['company_id', 'resource', 'logged_at']);
            $table->index(['group_id', 'resource']);
            $table->index('logged_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('usage_log');
    }
}
