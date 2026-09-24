<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->json('seo')->nullable();
        });

        Schema::table('donation_campaigns', function (Blueprint $table): void {
            $table->json('seo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('seo');
        });

        Schema::table('donation_campaigns', function (Blueprint $table): void {
            $table->dropColumn('seo');
        });
    }
};
