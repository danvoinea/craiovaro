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
        Schema::table('news_sources', function (Blueprint $table): void {
            $table->string('scope', 20)->default('local');
            $table->index('scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news_sources', function (Blueprint $table): void {
            $table->dropIndex(['scope']);
            $table->dropColumn('scope');
        });
    }
};
