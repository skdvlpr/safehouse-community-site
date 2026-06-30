<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('description')->nullable();
            $table->json('privacy_notice')->nullable();
            $table->json('form_notice')->nullable();
            $table->json('preset_amounts')->nullable();
            $table->boolean('allow_custom_amount')->default(true);
            $table->unsignedInteger('min_amount_cents')->default(50);
            $table->string('currency', 3)->default('EUR');
            $table->string('espocrm_finanziamento_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_campaigns');
    }
};
