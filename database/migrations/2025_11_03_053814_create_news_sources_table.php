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
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->string('source_type');
            $table->string('selector_type')->default('css');
            $table->string('title_selector')->nullable();
            $table->string('body_selector')->nullable();
            $table->string('date_selector')->nullable();
            $table->string('image_selector')->nullable();
            $table->string('link_selector')->nullable();
            $table->string('fetch_frequency');
            $table->text('keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetched_at')->nullable();
            $table->string('last_fetch_status')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('fetch_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
