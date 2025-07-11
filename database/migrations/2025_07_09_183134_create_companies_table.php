<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->enum('subscription_level', ['trial', 'basic', 'professional', 'enterprise'])->default('trial');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('max_users')->default(5);
            $table->integer('max_rooms')->default(10);
            $table->boolean('allow_external_bookings')->default(false);
            $table->text('external_booking_domain_whitelist')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_contact_name')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('payment_method_id')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('state_id')->nullable()->constrained();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->string('postal_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('subscription_level');
            $table->index('is_active');
            $table->index('stripe_customer_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
