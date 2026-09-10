<?php

namespace Tests\Feature\Distribution;

use App\Jobs\DispatchN8nWebhookJob;
use App\Models\Institution;
use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class N8nWebhookTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_cryptographically_signed_webhook_to_n8n(): void
    {
        Http::fake([
            'http://127.0.0.1:5678/*' => Http::response(['success' => true], 200),
            '*' => Http::response(['success' => true], 200),
        ]);

        $institution = Institution::factory()->create([
            'name' => 'Staff Selection Commission',
            'slug' => 'ssc',
        ]);

        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'title' => 'Multi Tasking Staff (MTS) Examination 2026',
            'total_vacancies' => 8326,
            'application_end_at' => '2026-11-15 23:00:00',
        ]);

        $job = new DispatchN8nWebhookJob($notice);
        $job->handle();

        Http::assertSent(function ($request) use ($notice) {
            $hasHeader = $request->hasHeader('X-Suchak-Signature');
            $signatureHeader = $request->header('X-Suchak-Signature')[0] ?? '';
            $isSha256Prefixed = str_starts_with($signatureHeader, 'sha256=');

            $data = json_decode($request->body(), true);
            $matchesNotice = isset($data['notice_id']) && $data['notice_id'] === $notice->id;
            $matchesTitle = isset($data['title']) && $data['title'] === 'Multi Tasking Staff (MTS) Examination 2026';
            $hasTelegramCard = isset($data['telegram_formatted_text']) && str_contains($data['telegram_formatted_text'], '8326 Posts');

            return $hasHeader && $isSha256Prefixed && $matchesNotice && $matchesTitle && $hasTelegramCard;
        });
    }
}
