<?php

use App\Support\LegacyUserNameParser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name')->default('')->after('id');
            $table->string('last_name')->default('')->after('first_name');
            $table->string('job_title')->nullable()->after('last_name');
        });

        foreach (DB::table('users')->select('id', 'name')->cursor() as $user) {
            $parsed = LegacyUserNameParser::split($user->name);

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parsed['first_name'],
                'last_name' => $parsed['last_name'],
                'job_title' => $parsed['job_title'],
            ]);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('name')->default('')->after('id');
        });

        foreach (DB::table('users')->select('id', 'first_name', 'last_name', 'job_title')->cursor() as $user) {
            $label = trim("{$user->first_name} {$user->last_name}");
            $title = trim((string) ($user->job_title ?? ''));

            if ($title !== '') {
                $label = trim("{$label} [{$title}]");
            }

            DB::table('users')->where('id', $user->id)->update([
                'name' => $label,
            ]);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['first_name', 'last_name', 'job_title']);
        });
    }
};
