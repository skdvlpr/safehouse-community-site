<?php

namespace App\Models;

use App\Enums\ArticleSection;
use App\Models\Concerns\SyncsUrlSlugFromLabel;
use Database\Factories\ArticleCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ArticleCategory extends Model
{
    /** @use HasFactory<ArticleCategoryFactory> */
    use HasFactory, HasTranslations, SyncsUrlSlugFromLabel;

    /**
     * @var list<string>
     */
    public array $translatable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'section',
        'name',
        'slug',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'section' => ArticleSection::class,
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
