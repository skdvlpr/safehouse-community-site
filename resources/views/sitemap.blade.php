<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($rows as $row)
    <url>
        <loc>{{ $row['loc'] }}</loc>
        @if ($row['lastmod'])
            <lastmod>{{ $row['lastmod'] }}</lastmod>
        @endif
    </url>
@endforeach
</urlset>
