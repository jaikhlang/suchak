<?php

namespace App\Actions\Publishing;

use App\Models\Notice;
use App\Models\Post;
use Illuminate\Support\Str;

class GeneratePublicPostAction
{
    public function execute(Notice $notice): Post
    {
        $notice->loadMissing(['institution', 'positions.reservations', 'applicationDetail', 'eligibilityRule']);

        $institution = $notice->institution;
        $instName = $institution->name;
        $instShort = $institution->short_name ?: $instName;
        $totalVacancies = $notice->total_vacancies;
        $deadline = $notice->application_end_at?->format('d M Y') ?? 'Refer Official Notification';

        $title = $notice->title;
        $slug = "{$institution->slug}-".Str::slug($notice->title);

        $excerpt = "{$instName} has announced {$totalVacancies} vacancies for {$title}. Candidates can apply online through the official portal until {$deadline}.";

        $seoTitle = "{$title} - {$instShort} ({$totalVacancies} Posts) | Suchak";
        $seoDesc = Str::limit("Official recruitment notification for {$title} by {$instName}. Total vacancies: {$totalVacancies}. Application deadline: {$deadline}.", 250);
        $canonicalUrl = "http://suchak.test/recruitment/{$institution->slug}/{$slug}";

        // Build structured editorial HTML
        $contentHtml = $this->buildContentHtml($notice, $instName, $totalVacancies, $deadline);

        return Post::updateOrCreate(
            ['notice_id' => $notice->id],
            [
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content_html' => $contentHtml,
                'status' => 'published',
                'seo_title' => $seoTitle,
                'seo_description' => $seoDesc,
                'canonical_url' => $canonicalUrl,
                'published_at' => now(),
            ]
        );
    }

    protected function buildContentHtml(Notice $notice, string $instName, int $totalVacancies, string $deadline): string
    {
        $appMode = ucfirst($notice->applicationDetail?->application_mode ?? 'Online');
        $applyUrl = $notice->applicationDetail?->apply_url ?? $notice->canonical_source_url;
        $officialPdfUrl = $notice->sourceArtifact?->url ?? $notice->canonical_source_url;
        $feeGeneral = $notice->applicationDetail?->general_fee ? "₹{$notice->applicationDetail->general_fee}" : 'Free / Nil';

        $positionsHtml = '';
        foreach ($notice->positions as $pos) {
            $pay = $pos->pay_scale_text ?: ($pos->pay_level ?: 'As per Government Rules');
            $positionsHtml .= "<tr><td class='p-2 font-medium'>{$pos->title}</td><td class='p-2 text-center'>{$pos->total_vacancies}</td><td class='p-2'>{$pay}</td></tr>";
        }

        return <<<HTML
<div class="space-y-6">
    <section class="bg-card p-6 rounded-xl border">
        <h2 class="text-xl font-bold text-foreground mb-3">Overview</h2>
        <p class="text-muted-foreground leading-relaxed">
            <strong>{$instName}</strong> invites applications from eligible Indian citizens for <strong>{$notice->title}</strong>.
            A total of <strong>{$totalVacancies} vacancies</strong> have been notified across cadres.
        </p>
    </section>

    <section class="bg-card p-6 rounded-xl border">
        <h2 class="text-xl font-bold text-foreground mb-4">Important Dates & Key Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="p-3 bg-muted/40 rounded-lg">
                <span class="text-xs text-muted-foreground block">Application Deadline</span>
                <span class="font-bold text-foreground text-base">{$deadline}</span>
            </div>
            <div class="p-3 bg-muted/40 rounded-lg">
                <span class="text-xs text-muted-foreground block">Application Mode</span>
                <span class="font-bold text-foreground text-base">{$appMode}</span>
            </div>
            <div class="p-3 bg-muted/40 rounded-lg">
                <span class="text-xs text-muted-foreground block">Application Fee (General)</span>
                <span class="font-bold text-foreground text-base">{$feeGeneral}</span>
            </div>
        </div>
    </section>

    <section class="bg-card p-6 rounded-xl border">
        <h2 class="text-xl font-bold text-foreground mb-4">Vacancy & Cadre Breakdown</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b bg-muted/30">
                        <th class="p-2">Position Cadre</th>
                        <th class="p-2 text-center">Vacancies</th>
                        <th class="p-2">Pay Level / Scale</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    {$positionsHtml}
                </tbody>
            </table>
        </div>
    </section>

    <section class="flex flex-wrap gap-4 pt-2">
        <a href="{$applyUrl}" target="_blank" rel="noopener noreferrer" class="px-5 py-2.5 rounded-lg font-medium bg-primary text-primary-foreground shadow-xs hover:bg-primary/90 transition-all">
            Official Application Portal ↗
        </a>
        <a href="{$officialPdfUrl}" target="_blank" rel="noopener noreferrer" class="px-5 py-2.5 rounded-lg font-medium border bg-card text-foreground shadow-xs hover:bg-muted/40 transition-all">
            Download Official Notification PDF ↗
        </a>
    </section>
</div>
HTML;
    }
}
