<?php

namespace App\Jobs;

use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchN8nWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public Notice $notice
    ) {}

    public function handle(): void
    {
        $webhookUrl = config('services.n8n.webhook_url', env('N8N_WEBHOOK_URL', 'http://127.0.0.1:5678/webhook/notice-published'));
        $secret = config('services.n8n.webhook_secret', env('N8N_WEBHOOK_SECRET', 'n8n-signature-secret-key'));

        $this->notice->loadMissing(['institution', 'sourceArtifact']);

        $deadline = $this->notice->application_end_at?->format('d F Y') ?? 'Refer Official Notification';
        $publicUrl = "https://suchak.test/recruitment/{$this->notice->institution->slug}/{$this->notice->slug}";
        $officialPdfUrl = $this->notice->sourceArtifact?->url ?? $this->notice->canonical_source_url;

        $telegramCard = "📢 *New Government Recruitment Verified*\n\n"
            ."🏢 *Authority:* {$this->notice->institution->name}\n"
            ."📝 *Notice:* {$this->notice->title}\n"
            ."👥 *Total Vacancies:* {$this->notice->total_vacancies} Posts\n"
            ."⏰ *Application Deadline:* {$deadline}\n\n"
            ."🔗 *Full Verified Breakdown & Apply:* [View on Suchak]({$publicUrl})\n"
            ."📄 *Official PDF:* [Download Gazette]({$officialPdfUrl})";

        $payload = [
            'event' => 'notice.published',
            'notice_id' => $this->notice->id,
            'institution' => $this->notice->institution->name,
            'institution_slug' => $this->notice->institution->slug,
            'title' => $this->notice->title,
            'total_vacancies' => $this->notice->total_vacancies,
            'application_deadline' => $deadline,
            'public_url' => $publicUrl,
            'official_pdf_url' => $officialPdfUrl,
            'telegram_formatted_text' => $telegramCard,
            'published_at' => now()->toIso8601String(),
        ];

        $encodedPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $encodedPayload, $secret);

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Suchak-Signature' => "sha256={$signature}",
                ])
                ->withBody($encodedPayload, 'application/json')
                ->post($webhookUrl);

            if (! $response->successful()) {
                Log::warning("n8n webhook dispatch returned HTTP {$response->status()}: {$response->body()}");
            }
        } catch (\Throwable $e) {
            Log::info('n8n webhook target unreachable: '.$e->getMessage());
        }
    }
}
