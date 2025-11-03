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
        Schema::table('news_raw', function (Blueprint $table): void {
            $table->index('published_at', 'news_raw_published_at_index');
            $table->index('created_at', 'news_raw_created_at_index');
            $table->index(['news_source_id', 'published_at'], 'news_raw_source_published_index');
        });

        Schema::table('news_source_logs', function (Blueprint $table): void {
            $table->index('ran_at', 'news_source_logs_ran_at_index');
            $table->index('status', 'news_source_logs_status_index');
        });

        Schema::table('short_links', function (Blueprint $table): void {
            $table->index('last_clicked_at', 'short_links_last_clicked_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_raw', function (Blueprint $table): void {
            $table->dropIndex('news_raw_published_at_index');
            $table->dropIndex('news_raw_created_at_index');
            $table->dropIndex('news_raw_source_published_index');
        });

        Schema::table('news_source_logs', function (Blueprint $table): void {
            $table->dropIndex('news_source_logs_ran_at_index');
            $table->dropIndex('news_source_logs_status_index');
        });

        Schema::table('short_links', function (Blueprint $table): void {
            $table->dropIndex('short_links_last_clicked_at_index');
        });
    }
};
