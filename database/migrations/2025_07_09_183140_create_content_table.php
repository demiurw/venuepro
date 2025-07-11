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
