<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyModuleTable extends Migration
{
    public function up()
    {
        Schema::create('company_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained('module')->onDelete('cascade');
            $table->enum('subscription_level', ['basic', 'professional', 'enterprise'])->default('basic');
            $table->integer('room_quota')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('stripe_subscription_id')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'module_id']);
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_module');
    }
}
