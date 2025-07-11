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
