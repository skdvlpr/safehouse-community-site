<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->boolean('show_author')->default(false)->after('author_id');
        });

        DB::table('articles')
            ->whereNotNull('author_id')
            ->update(['show_author' => true]);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('show_author');
        });
    }
};
