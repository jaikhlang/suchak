<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::with('notice.institution')
            ->where('status', 'published')
            ->latest('updated_at')
            ->get();

        $appUrl = rtrim(config('app.url', 'http://suchak.test'), '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        // Home page
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$appUrl}</loc>\n";
        $xml .= '    <lastmod>'.now()->toAtomString()."</lastmod>\n";
        $xml .= "    <changefreq>hourly</changefreq>\n";
        $xml .= "    <priority>1.0</priority>\n";
        $xml .= "  </url>\n";

        // Recruitment Catalog
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$appUrl}/recruitment</loc>\n";
        $xml .= '    <lastmod>'.now()->toAtomString()."</lastmod>\n";
        $xml .= "    <changefreq>hourly</changefreq>\n";
        $xml .= "    <priority>0.9</priority>\n";
        $xml .= "  </url>\n";

        // Posts
        foreach ($posts as $post) {
            $institutionSlug = $post->notice?->institution?->slug ?? 'authority';
            $postUrl = "{$appUrl}/recruitment/{$institutionSlug}/{$post->slug}";
            $lastmod = ($post->updated_at ?? now())->toAtomString();

            $xml .= "  <url>\n";
            $xml .= "    <loc>{$postUrl}</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "    <changefreq>daily</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
