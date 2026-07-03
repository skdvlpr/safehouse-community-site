<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->string('desk', 32)->default('general')->after('email');
            $table->string('correlation_token', 64)->nullable()->unique()->after('desk');
            $table->string('outbound_message_id')->nullable()->after('correlation_token');
            $table->string('crm_case_id', 32)->nullable()->after('outbound_message_id');
            $table->string('crm_lead_id', 32)->nullable()->after('crm_case_id');
            $table->string('crm_link_status', 32)->default('none')->after('crm_lead_id');
        });
    }

    public function down(): void
    {
        Schema::table('contact_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'desk',
                'correlation_token',
                'outbound_message_id',
                'crm_case_id',
                'crm_lead_id',
                'crm_link_status',
            ]);
        });
    }
};
