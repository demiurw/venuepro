<#
.SYNOPSIS
    Creates Laravel migration files for the VenuePro project in the correct dependency order.

.DESCRIPTION
    This script generates PHP migration files in the 'database/migrations' directory.
    It uses a predefined order to ensure that tables are created before their dependencies,
    preventing foreign key constraint errors during migration.

.NOTES
    - Run this script from the root directory of your Laravel project.
    - It's best to delete any old, incorrectly ordered migrations before running this.
    - After running, execute 'php artisan migrate:fresh' to apply the database changes.
#>

# Define the base directory for migrations
$migrationsDir = "database/migrations"

# Ensure the migrations directory exists
if (-not (Test-Path $migrationsDir)) {
    New-Item -ItemType Directory -Path $migrationsDir | Out-Null
}

# Get current timestamp for sequential ordering
$baseTimestamp = Get-Date -Format "yyyy_MM_dd_HHmmss"
$script:counter = 0 # Initialize the script-scoped counter

function Get-NextTimestamp {
    param (
        [string]$base
    )
    $script:counter++
    $dateFormat = "yyyy_MM_dd_HHmmss"
    try {
        $parsedBase = [datetime]::ParseExact($base, $dateFormat, [System.Globalization.CultureInfo]::InvariantCulture)
        $newTime = $parsedBase.AddSeconds($script:counter)
        return $newTime.ToString("yyyy_MM_dd_HHmmss")
    } catch {
        Write-Error "Error parsing base date '$base'. Please ensure it's in '$dateFormat' format. Details: $($_.Exception.Message)"
        return $null
    }
}

# --- CORRECT MIGRATION ORDER ---
# This array defines the logical order to prevent dependency errors.
$migrationOrder = @(
# LEVEL 0: Fundamental tables with minimal external dependencies.
    "create_countries_table",
    "create_states_table",
    "create_roles_table",
    "create_companies_table",
    "create_modules_table",
    "create_subscription_plans_table",
    "create_users_table", # Users WITHOUT group_id foreign key
    "create_groups_table", # Groups can now reference users
    "create_system_settings_table",
    "create_content_table",
    "create_email_templates_table",
    # LEVEL 1: Depends on Level 0 tables
    "create_buildings_table",
    "create_service_levels_table",
    "create_amenities_table",
    "create_company_module_table",
    "create_invoice_templates_table",
    "create_company_labels_table",
    # LEVEL 2: Depends on Level 1 tables
    "create_rooms_table",
    "create_group_member_table",
    "create_access_control_table",
    "create_usage_limits_table",
    # LEVEL 3: Depends on Level 2 tables
    "create_room_amenity_table",
    "create_room_availability_table",
    "create_room_media_table",
    "create_daily_break_time_table",
    "create_blocked_dates_table",
    # LEVEL 4: invoicing and bookings
    "create_invoices_table",
    "create_bookings_table",
    "create_external_booking_requests_table",


    # LEVEL 5: Depends on bookings and invoices
    "create_booking_attendee_table",
    "create_invoice_items_table",
    "create_payments_table",
    # LEVEL 6: Logging and utility tables
    "create_otp_attempts_table",
    "create_notifications_table",
    "create_audit_log_table",
    "create_usage_log_table",
    # FINAL: Add all foreign key constraints that couldn't be added earlier
    "add_foreign_key_constraints"
)

# --- Migration Contents ---
$migrations = @{
    "create_countries_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique();
            $table->string('name', 100);
            $table->string('phone_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
'@;

    "create_states_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->string('code', 10);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['country_id', 'code']);
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('states');
    }
}
'@;

    "create_roles_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->unique();
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system_role')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('is_system_role');
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
'@;

    "create_companies_table" = @'
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
'@;

    "create_users_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->foreignId('role_id')->constrained();
            $table->unsignedBigInteger('group_id')->nullable(); // Just the column, no foreign key yet
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->enum('user_type', ['venuepro_admin', 'system_admin', 'hod', 'booking_agent', 'invitee', 'external'])->default('invitee');
            $table->enum('auth_method', ['password', 'otp', 'oauth'])->default('otp');
            $table->string('email_verification_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('otp_secret')->nullable();
            $table->json('oauth_providers')->nullable();
            $table->string('oauth_id')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('status');
            $table->index('user_type');
            $table->index('company_id');
            $table->index('group_id');
            $table->index('email_verification_token');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
'@;

    "create_groups_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('deactivated_at')->nullable();
            $table->unsignedBigInteger('deactivated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name']);
            $table->index('is_active');
            $table->index('created_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
'@;

    "create_modules_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    public function up()
    {
        Schema::create('module', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();
            $table->json('features_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module');
    }
}
'@;

    "create_buildings_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsTable extends Migration
{
    public function up()
    {
        Schema::create('building', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('state_id')->nullable()->constrained();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->string('postal_code', 20)->nullable();
            $table->string('timezone', 50)->default('UTC');
            $table->integer('buffer_time_minutes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('building');
    }
}
'@;

    "create_service_levels_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceLevelsTable extends Migration
{
    public function up()
    {
        Schema::create('service_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->integer('buffer_time_minutes')->default(0);
            $table->integer('min_notice_minutes')->default(0);
            $table->integer('max_notice_days')->default(365);
            $table->integer('max_daily_bookings')->nullable();
            $table->integer('max_hours_per_day')->nullable();
            $table->boolean('allow_external_booking')->default(false);
            $table->decimal('default_hourly_rate', 10, 2)->nullable();
            $table->decimal('default_daily_rate', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_level');
    }
}
'@;

    "create_rooms_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('building_id')->constrained('building')->onDelete('cascade');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->enum('room_type', ['meeting_room', 'conference_room', 'board_room', 'training_room', 'event_space', 'other'])->default('meeting_room');
            $table->integer('capacity')->default(1);
            $table->string('floor', 50)->nullable();
            $table->string('room_number', 50)->nullable();
            $table->boolean('is_private')->default(false);
            $table->string('color_scheme', 7)->default('#3B82F6');
            $table->foreignId('module_id')->nullable()->constrained('module');
            $table->enum('availability_type', ['standard', 'custom', '24/7'])->default('standard');
            $table->foreignId('service_level_id')->nullable()->constrained('service_level');
            $table->integer('buffer_time_minutes')->nullable();
            $table->integer('min_notice_minutes')->nullable();
            $table->integer('max_notice_days')->nullable();
            $table->integer('max_hours_per_day')->nullable();
            $table->boolean('allow_external_booking')->default(false);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['building_id', 'is_active']);
            $table->index('room_type');
            $table->index('capacity');
            $table->index('is_private');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('room');
    }
}
'@;

    "create_amenities_table" = @'
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
'@;

    "create_room_amenity_table" = @'
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
'@;

    "create_room_availability_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomAvailabilityTable extends Migration
{
    public function up()
    {
        Schema::create('room_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('room')->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'room_id', 'day_of_week']);
            $table->index('day_of_week');
        });
    }

    public function down()
    {
        Schema::dropIfExists('room_availability');
    }
}
'@;

    "create_room_media_table" = @'
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
'@;

    "create_invoices_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained();
            $table->unsignedBigInteger('external_booking_request_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('group_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice');
    }
}
'@;

    "create_bookings_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('room')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->enum('booking_type', ['internal', 'external'])->default('internal');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('booked_for_user_id')->nullable()->constrained('users');
            $table->enum('delegation_type', ['self', 'other'])->default('self');
            $table->string('external_reference')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->json('recurring_pattern')->nullable();
            $table->unsignedBigInteger('parent_booking_id')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoice');
            $table->boolean('has_been_billed')->default(false);
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'date', 'status']);
            $table->index(['created_by', 'status']);
            $table->index('booking_type');
            $table->index('parent_booking_id');
            $table->index(['date', 'start_time', 'end_time']);
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking');
    }
}
'@;

    "create_booking_attendee_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingAttendeeTable extends Migration
{
    public function up()
    {
        Schema::create('booking_attendee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('booking')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('attendee_type', ['internal', 'external', 'guest'])->default('internal');
            $table->enum('status', ['pending', 'accepted', 'declined', 'maybe'])->default('pending');
            $table->text('response_message')->nullable();
            $table->boolean('is_organizer')->default(false);
            $table->string('access_token', 100)->nullable();
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'status']);
            $table->index('email');
            $table->index('access_token');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('booking_attendee');
    }
}
'@;

    "create_external_booking_requests_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalBookingRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('external_booking_request', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('room')->onDelete('cascade');
            $table->string('requester_name');
            $table->string('requester_email');
            $table->string('requester_phone', 20)->nullable();
            $table->string('company_name')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('attendee_count')->default(1);
            $table->enum('status', ['pending', 'approved', 'denied', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('booking_id')->nullable()->constrained('booking');
            $table->string('token', 100)->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'status']);
            $table->index(['date', 'status']);
            $table->index('requester_email');
            $table->index('token');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('external_booking_request');
    }
}
'@;

    "create_invoice_items_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoice')->onDelete('cascade');
            $table->string('resource', 100);
            $table->text('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->string('reference_table', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['invoice_id']);
            $table->index(['reference_table', 'reference_id']);
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_item');
    }
}
'@;

    "create_payments_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoice');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('payment_method', ['credit_card', 'bank_transfer', 'paypal', 'stripe', 'other'])->default('other');
            $table->string('payment_reference')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('invoice_number')->nullable();
            $table->string('invoice_url')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('invoice_id');
            $table->index('payment_date');
            $table->index('stripe_payment_intent_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment');
    }
}
'@;

    "create_otp_attempts_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOtpAttemptsTable extends Migration
{
    public function up()
    {
        Schema::create('otp_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('otp_code', 10);
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->integer('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'is_used', 'expires_at']);
            $table->index('otp_code');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('otp_attempts');
    }
}
'@;

    "create_notifications_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'success', 'warning', 'error'])->default('info');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->enum('notification_type', ['booking', 'invoice', 'system', 'user'])->default('system');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_type', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'is_read']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('notification_type');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification');
    }
}
'@;

    "create_audit_log_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogTable extends Migration
{
    public function up()
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->enum('action', ['create', 'update', 'delete', 'login', 'logout', 'other'])->default('other');
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
            $table->index('created_at');
            $table->index('company_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_log');
    }
}
'@;

    "create_group_member_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupMemberTable extends Migration
{
    public function up()
    {
        Schema::create('group_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['member', 'manager', 'admin'])->default('member');
            $table->foreignId('added_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'group_id', 'user_id']);
            $table->index('role');
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_member');
    }
}
'@;

    "create_access_control_table" = @'
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
'@;

    "create_company_module_table" = @'
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
'@;

    "create_usage_limits_table" = @'
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
'@;

    "create_usage_log_table" = @'
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
'@;

    "create_daily_break_time_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyBreakTimeTable extends Migration
{
    public function up()
    {
        Schema::create('daily_break_time', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('room')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('building')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'all']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'day_of_week', 'is_active']);
            $table->index(['building_id', 'day_of_week', 'is_active']);
            $table->index(['company_id', 'day_of_week', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_break_time');
    }
}
'@;

    "create_blocked_dates_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockedDatesTable extends Migration
{
    public function up()
    {
        Schema::create('blocked_date', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('room')->onDelete('cascade');
            $table->foreignId('building_id')->nullable()->constrained('building')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('title');
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->boolean('is_recurring')->default(false);
            $table->json('recurring_pattern')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'date']);
            $table->index(['building_id', 'date']);
            $table->index(['company_id', 'date']);
            $table->index('is_holiday');
        });
    }

    public function down()
    {
        Schema::dropIfExists('blocked_date');
    }
}
'@;

    "create_content_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTable extends Migration
{
    public function up()
    {
        Schema::create('content', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->string('title');
            $table->longText('content');
            $table->enum('type', ['page', 'article', 'faq', 'testimonial', 'help'])->default('page');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('featured_image')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index(['type', 'status']);
            $table->index(['status', 'published_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content');
    }
}
'@;

    "create_email_templates_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('email_template', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->json('variables_json')->nullable();
            $table->boolean('is_system')->default(false);
            $table->enum('template_type', ['booking', 'invoice', 'notification', 'welcome', 'other'])->default('other');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'company_id']);
            $table->index('template_type');
            $table->index('is_system');
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_template');
    }
}
'@;

    "create_invoice_templates_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->longText('header_html')->nullable();
            $table->longText('footer_html')->nullable();
            $table->longText('css_styles')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_default']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_template');
    }
}
'@;

    "create_subscription_plans_table" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_plan', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->text('description')->nullable();
            $table->foreignId('module_id')->nullable()->constrained('module');
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2);
            $table->string('stripe_monthly_price_id')->nullable();
            $table->string('stripe_yearly_price_id')->nullable();
            $table->integer('room_quota')->nullable();
            $table->integer('user_quota')->nullable();
            $table->json('features_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('module_id');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plan');
    }
}
'@;

    "create_system_settings_table" = @'
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
'@;

    "create_company_labels_table" = @'
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
'@;

    "add_foreign_key_constraints" = @'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyConstraints extends Migration
{
    public function up()
    {
        // Add foreign key for users.group_id (now that groups table exists)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('set null');
        });

        // Add foreign key for groups.created_by and deactivated_by
        Schema::table('groups', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deactivated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Add foreign key for bookings.parent_booking_id
        Schema::table('booking', function (Blueprint $table) {
            $table->foreign('parent_booking_id')->references('id')->on('booking')->onDelete('set null');
        });

        // Add foreign key for invoice.external_booking_request_id
        Schema::table('invoice', function (Blueprint $table) {
            $table->foreign('external_booking_request_id')->references('id')->on('external_booking_request')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['deactivated_by']);
        });

        Schema::table('booking', function (Blueprint $table) {
            $table->dropForeign(['parent_booking_id']);
        });

        Schema::table('invoice', function (Blueprint $table) {
            $table->dropForeign(['external_booking_request_id']);
        });
    }
}
'@;
}

# --- SCRIPT EXECUTION ---
# Process and create each migration file USING THE CORRECT ORDER
foreach ($migrationName in $migrationOrder) {
    if (-not $migrations.ContainsKey($migrationName)) {
        Write-Warning "Migration '$migrationName' is defined in the order but not in the content map. Skipping."
        continue
    }

    $timestamp = Get-NextTimestamp -base $baseTimestamp
    $fileName = "${timestamp}_${migrationName}.php"
    $filePath = Join-Path $migrationsDir $fileName
    $content = $migrations[$migrationName].Replace("{timestamp}", $timestamp)

    Write-Host "Creating migration: $fileName"
    Set-Content -Path $filePath -Value $content -Encoding UTF8
}

Write-Host "`nAll migration files created successfully in '$migrationsDir'."
Write-Host "Remember to run 'php artisan migrate:fresh' to apply them to your database."
