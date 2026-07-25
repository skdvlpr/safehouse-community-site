<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donation_campaigns', function (Blueprint $table) {
            $table->boolean('allows_recurring')->default(false)->after('allow_custom_amount');
        });
    }

    public function down(): void
    {
        Schema::table('donation_campaigns', function (Blueprint $table) {
            $table->dropColumn('allows_recurring');
        });
    }
};
