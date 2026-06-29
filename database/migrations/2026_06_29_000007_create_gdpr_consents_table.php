<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gdpr_consents', function (Blueprint $table) {
            $table->id();
            $table->string('consent_type', 50);
            $table->boolean('granted');
            $table->string('ip_hash', 64);
            $table->timestamp('consented_at');
            $table->timestamps();

            $table->index(['consent_type', 'consented_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_consents');
    }
};
