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
