<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_categories', function (Blueprint $table): void {
            $table->string('section', 32)->default('news')->after('id');
            $table->index('section');
        });

        Schema::table('articles', function (Blueprint $table): void {
            $table->string('section', 32)->default('news')->after('id');
            $table->foreignId('author_id')->nullable()->after('article_category_id')->constrained('users')->nullOnDelete();
            $table->index('section');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('author_id');
            $table->dropIndex(['section']);
            $table->dropColumn('section');
        });

        Schema::table('article_categories', function (Blueprint $table): void {
            $table->dropIndex(['section']);
            $table->dropColumn('section');
        });
    }
};
