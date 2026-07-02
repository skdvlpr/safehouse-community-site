<?php

namespace App\DataTransferObjects;

use Illuminate\Http\Request;

readonly class ArticleListingFilters
{
    /**
     * @param  list<string>  $categorySlugs
     */
    public function __construct(
        public array $categorySlugs = [],
        public ?string $publishedFrom = null,
        public ?string $publishedTo = null,
        public string $layout = 'feed',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $categories = $request->query('categories', []);
        if (! is_array($categories)) {
            $categories = [$categories];
        }

        $categorySlugs = array_values(array_unique(array_filter(array_map(
            static fn (mixed $value): string => is_string($value) ? trim($value) : '',
            $categories,
        ), static fn (string $slug): bool => $slug !== '')));

        $from = self::normalizeDate($request->query('from'));
        $to = self::normalizeDate($request->query('to'));

        if ($from !== null && $to !== null && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $layout = $request->query('layout', 'feed');

        return new self(
            categorySlugs: $categorySlugs,
            publishedFrom: $from,
            publishedTo: $to,
            layout: $layout === 'list' ? 'list' : 'feed',
        );
    }

    public function hasActiveFilters(): bool
    {
        return $this->categorySlugs !== []
            || $this->publishedFrom !== null
            || $this->publishedTo !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function queryParameters(): array
    {
        $params = [];

        if ($this->categorySlugs !== []) {
            $params['categories'] = $this->categorySlugs;
        }

        if ($this->publishedFrom !== null) {
            $params['from'] = $this->publishedFrom;
        }

        if ($this->publishedTo !== null) {
            $params['to'] = $this->publishedTo;
        }

        if ($this->layout === 'list') {
            $params['layout'] = 'list';
        }

        return $params;
    }

    /**
     * @return array<string, mixed>
     */
    public function routeParameters(string $locale): array
    {
        return array_merge(['locale' => $locale], $this->queryParameters());
    }

    public function withLayout(string $layout): self
    {
        return new self(
            categorySlugs: $this->categorySlugs,
            publishedFrom: $this->publishedFrom,
            publishedTo: $this->publishedTo,
            layout: $layout === 'list' ? 'list' : 'feed',
        );
    }

    /**
     * @return list<string>
     */
    public function toggledCategorySlugs(string $slug): array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return $this->categorySlugs;
        }

        if (in_array($slug, $this->categorySlugs, true)) {
            return array_values(array_filter(
                $this->categorySlugs,
                static fn (string $value): bool => $value !== $slug,
            ));
        }

        return [...$this->categorySlugs, $slug];
    }

    public function withCategorySlugs(array $slugs): self
    {
        return new self(
            categorySlugs: array_values(array_unique(array_filter(array_map(
                static fn (mixed $value): string => is_string($value) ? trim($value) : '',
                $slugs,
            ), static fn (string $slug): bool => $slug !== ''))),
            publishedFrom: $this->publishedFrom,
            publishedTo: $this->publishedTo,
            layout: $this->layout,
        );
    }

    private static function normalizeDate(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date !== false ? $date->format('Y-m-d') : null;
    }
}
