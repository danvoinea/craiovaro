<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('category_slug');
            $table->string('category_label');
            $table->text('summary')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('hero_image_url')->nullable();
            $table->timestamp('published_at');
            $table->boolean('is_highlighted')->default(true);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['category_slug', 'slug']);
            $table->index(['is_highlighted', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_posts');
    }
};
