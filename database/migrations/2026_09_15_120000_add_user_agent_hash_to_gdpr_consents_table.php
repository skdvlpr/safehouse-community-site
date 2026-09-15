<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gdpr_consents', function (Blueprint $table) {
            $table->string('user_agent_hash', 64)->nullable()->after('ip_hash');
        });
    }

    public function down(): void
    {
        Schema::table('gdpr_consents', function (Blueprint $table) {
            $table->dropColumn('user_agent_hash');
        });
    }
};
