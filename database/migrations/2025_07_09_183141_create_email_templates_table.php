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
