@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('katalog') }}</loc>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('about') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>{{ route('konsultasi.index') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    @foreach($katalogs as $katalog)
        <url>
            <loc>{{ route('katalog.detail', $katalog->id) }}</loc>
            @if($katalog->updated_at)
                <lastmod>{{ $katalog->updated_at->toAtomString() }}</lastmod>
            @endif
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach
</urlset>
