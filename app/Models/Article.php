<?php

namespace App\Models;

use App\Enums\ArticleSection;
use App\Models\Concerns\SyncsUrlSlugFromLabel;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory, HasTranslations, SyncsUrlSlugFromLabel;

    /**
     * @var list<string>
     */
    public array $translatable = [
        'title',
        'slug',
        'excerpt',
        'body',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'section',
        'article_category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'meta',
        'is_published',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'section' => ArticleSection::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
