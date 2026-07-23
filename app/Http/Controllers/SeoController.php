<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $static = [
            '/', '/vehicle-reports', '/how-it-works', '/for-inspectors', '/about',
            '/contact', '/pricing', '/faq', '/reviews', '/imprint', '/privacy',
            '/terms', '/cookie-policy', '/request',
        ];

        $urls = collect($static)->map(fn ($path) => ['loc' => $base.$path, 'priority' => $path === '/' ? '1.0' : '0.7']);

        foreach (ServiceType::where('is_active', true)->pluck('slug') as $slug) {
            $urls->push(['loc' => $base.'/vehicle-reports/'.$slug, 'priority' => '0.8']);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= "  <url><loc>{$u['loc']}</loc><changefreq>weekly</changefreq><priority>{$u['priority']}</priority></url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $body = "User-agent: *\n";
        $body .= "Disallow: /account\n";
        $body .= "Disallow: /inspector\n";
        $body .= "Disallow: /admin\n";
        $body .= "Allow: /\n\n";
        $body .= "Sitemap: {$base}/sitemap.xml\n";

        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
