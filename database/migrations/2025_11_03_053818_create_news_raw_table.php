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
        Schema::create('news_raw', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('source_name');
            $table->text('source_url');
            $table->text('cover_image_url')->nullable();
            $table->string('url_hash', 64)->unique();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_raw');
    }
};
