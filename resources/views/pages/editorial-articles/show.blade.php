@include('pages.articles.show', [
    'article' => $article,
    'isPreview' => $isPreview ?? false,
    'indexRoute' => $indexRoute ?? 'editorial-articles.index',
    'backLabel' => 'site.pages.editorial_back',
])
