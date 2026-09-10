<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\State;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function latest(): Response
    {
        $posts = Post::with(['notice.institution.state'])
            ->where('status', 'published')
            ->latest('published_at')
            ->take(30)
            ->get();

        return $this->buildRssFeed(
            title: 'Project Suchak (सूचक) - Latest Government Recruitment Notifications',
            description: 'Real-time verified notifications and gazettes from Indian government recruiting authorities.',
            feedUrl: url('/feeds/latest.xml'),
            posts: $posts
        );
    }

    public function byState(string $stateSlug): Response
    {
        $normalized = strtolower(str_replace('-', ' ', $stateSlug));

        $state = State::where('iso_code', strtoupper($stateSlug))
            ->orWhereRaw('LOWER(name) = ?', [$normalized])
            ->firstOrFail();

        $posts = Post::with(['notice.institution.state'])
            ->where('status', 'published')
            ->whereHas('notice.institution', function ($q) use ($state) {
                $q->where('state_id', $state->id);
            })
            ->latest('published_at')
            ->take(30)
            ->get();

        return $this->buildRssFeed(
            title: "Project Suchak (सूचक) - Recruitment Notifications for {$state->name}",
            description: "Verified recruitment notifications and gazettes for {$state->name}.",
            feedUrl: url("/feeds/state/{$stateSlug}.xml"),
            posts: $posts
        );
    }

    /**
     * @param  Collection<int, Post>  $posts
     */
    protected function buildRssFeed(string $title, string $description, string $feedUrl, $posts): Response
    {
        $appUrl = rtrim(config('app.url', 'http://suchak.test'), '/');
        $lastBuildDate = now()->toRfc2822String();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
        $xml .= "  <channel>\n";
        $xml .= "    <title><![CDATA[{$title}]]></title>\n";
        $xml .= "    <link>{$appUrl}</link>\n";
        $xml .= "    <description><![CDATA[{$description}]]></description>\n";
        $xml .= "    <language>en-IN</language>\n";
        $xml .= "    <lastBuildDate>{$lastBuildDate}</lastBuildDate>\n";
        $xml .= "    <atom:link href=\"{$feedUrl}\" rel=\"self\" type=\"application/rss+xml\" />\n";

        foreach ($posts as $post) {
            $institution = $post->notice?->institution;
            $instSlug = $institution?->slug ?? 'authority';
            $instName = $institution?->name ?? 'Government Authority';
            $postUrl = "{$appUrl}/recruitment/{$instSlug}/{$post->slug}";
            $pubDate = ($post->published_at ?? $post->created_at)->toRfc2822String();
            $vacancies = $post->notice?->total_vacancies ? " ({$post->notice->total_vacancies} Vacancies)" : '';
            $itemTitle = "{$post->title} - {$instName}{$vacancies}";

            $xml .= "    <item>\n";
            $xml .= "      <title><![CDATA[{$itemTitle}]]></title>\n";
            $xml .= "      <link>{$postUrl}</link>\n";
            $xml .= "      <guid isPermaLink=\"true\">{$postUrl}</guid>\n";
            $xml .= "      <pubDate>{$pubDate}</pubDate>\n";
            $xml .= "      <description><![CDATA[{$post->excerpt}]]></description>\n";
            if ($institution?->state) {
                $xml .= "      <category><![CDATA[{$institution->state->name}]]></category>\n";
            }
            $xml .= "      <category><![CDATA[{$instName}]]></category>\n";
            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n";
        $xml .= '</rss>';

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
        ]);
    }
}
