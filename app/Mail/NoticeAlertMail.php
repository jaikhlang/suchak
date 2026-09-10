<?php

namespace App\Mail;

use App\Models\CandidateSubscription;
use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NoticeAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Notice $notice,
        public CandidateSubscription $subscription
    ) {}

    public function envelope(): Envelope
    {
        $instName = $this->notice->institution?->short_name ?: ($this->notice->institution?->name ?? 'Government Authority');

        return new Envelope(
            subject: "🚨 [Suchak Alert] {$this->notice->title} announced by {$instName} ({$this->notice->total_vacancies} Posts)",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    protected function buildHtml(): string
    {
        $inst = $this->notice->institution;
        $instName = $inst?->name ?? 'Government Authority';
        $deadline = $this->notice->application_end_at?->format('d M Y') ?? 'Refer Official Notification';
        $appUrl = rtrim(config('app.url', 'http://suchak.test'), '/');
        $postSlug = $this->notice->post?->slug ?? $this->notice->slug;
        $instSlug = $inst?->slug ?? 'authority';
        $publicUrl = "{$appUrl}/recruitment/{$instSlug}/{$postSlug}";
        $unsubscribeUrl = "{$appUrl}/subscriptions/{$this->subscription->verification_token}";

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recruitment Alert</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #0f172a;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="background-color: #0f172a; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 20px; font-weight: bold; letter-spacing: -0.5px;">Project Suchak (सूचक)</h1>
            <p style="margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;">Authoritative Indian Government Recruitment Discovery</p>
        </div>

        <div style="padding: 28px;">
            <div style="display: inline-block; padding: 4px 10px; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 9999px; font-size: 11px; font-weight: 600; color: #065f46; margin-bottom: 12px;">
                ✓ 100% Evidence Grounded Gazette Notice
            </div>

            <h2 style="font-size: 20px; font-weight: 800; line-height: 1.3; margin: 0 0 8px 0; color: #0f172a;">
                {$this->notice->title}
            </h2>

            <div style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
                <strong>Authority:</strong> {$instName} | <strong>Advt:</strong> {$this->notice->reference_number}
            </div>

            <div style="background-color: #f1f5f9; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Total Vacancies:</td>
                        <td style="padding: 6px 0; font-weight: bold; text-align: right; color: #0f172a;">{$this->notice->total_vacancies} Posts</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Application Deadline:</td>
                        <td style="padding: 6px 0; font-weight: bold; text-align: right; color: #0f172a;">{$deadline}</td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin: 28px 0;">
                <a href="{$publicUrl}" style="display: inline-block; padding: 14px 28px; background-color: #0f172a; color: #ffffff; text-decoration: none; border-radius: 10px; font-weight: 600; font-size: 14px;">
                    Inspect Notice, Quotas & Apply ↗
                </a>
            </div>

            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">

            <p style="font-size: 11px; color: #94a3b8; line-height: 1.5; margin: 0;">
                You are receiving this automated alert because you subscribed to recruitment updates on Project Suchak.<br>
                <a href="{$unsubscribeUrl}" style="color: #64748b; text-decoration: underline;">One-Click Unsubscribe</a>
            </p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
