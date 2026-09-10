<?php

namespace Tests\Feature\Crawling;

use App\Models\Institution;
use App\Models\Source;
use App\Models\SourceArtifact;
use App\Services\Crawling\SafeHttpClient;
use App\Services\Crawling\SourceCrawlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SourceCrawlServiceTest extends TestCase
{
    use RefreshDatabase;

    private Source $source;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $institution = Institution::factory()->create();
        $this->source = Source::factory()->create([
            'institution_id' => $institution->id,
            'url' => 'https://example.gov.in/notices',
            'crawl_frequency_minutes' => 60,
        ]);
    }

    #[Test]
    public function it_crawls_html_noticeboard_and_fetches_discovered_pdfs(): void
    {
        $htmlBody = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
    <h1>Recruitment Circulars</h1>
    <div class="notices">
        <a href="/downloads/advt-01-2026.pdf">Advt 01/2026 Assistant Engineer</a>
        <a href="https://example.gov.in/downloads/circular-02-2026.pdf">Circular 02/2026</a>
        <a href="/about-us">About Us</a>
    </div>
</body>
</html>
HTML;

        $pdfBody1 = '%PDF-1.4 Mock PDF Content 01';
        $pdfBody2 = '%PDF-1.4 Mock PDF Content 02';

        $mockClient = Mockery::mock(SafeHttpClient::class, function (MockInterface $mock) use ($htmlBody, $pdfBody1, $pdfBody2) {
            // Initial crawl of noticeboard
            $htmlResponse = Mockery::mock(Response::class);
            $htmlResponse->shouldReceive('successful')->andReturn(true);
            $htmlResponse->shouldReceive('status')->andReturn(200);
            $htmlResponse->shouldReceive('header')->with('Content-Type')->andReturn('text/html; charset=UTF-8');
            $htmlResponse->shouldReceive('body')->andReturn($htmlBody);

            $mock->shouldReceive('get')
                ->with('https://example.gov.in/notices')
                ->once()
                ->andReturn($htmlResponse);

            // Fetch first PDF
            $pdf1Response = Mockery::mock(Response::class);
            $pdf1Response->shouldReceive('successful')->andReturn(true);
            $pdf1Response->shouldReceive('header')->with('Content-Type')->andReturn('application/pdf');
            $pdf1Response->shouldReceive('body')->andReturn($pdfBody1);

            $mock->shouldReceive('get')
                ->with('https://example.gov.in/downloads/advt-01-2026.pdf')
                ->once()
                ->andReturn($pdf1Response);

            // Fetch second PDF
            $pdf2Response = Mockery::mock(Response::class);
            $pdf2Response->shouldReceive('successful')->andReturn(true);
            $pdf2Response->shouldReceive('header')->with('Content-Type')->andReturn('application/pdf');
            $pdf2Response->shouldReceive('body')->andReturn($pdfBody2);

            $mock->shouldReceive('get')
                ->with('https://example.gov.in/downloads/circular-02-2026.pdf')
                ->once()
                ->andReturn($pdf2Response);
        });

        $this->app->instance(SafeHttpClient::class, $mockClient);

        $service = $this->app->make(SourceCrawlService::class);
        $run = $service->crawl($this->source);

        $this->assertSame('completed', $run->status);
        $this->assertSame(2, $run->items_discovered);
        $this->assertSame(3, $run->items_fetched); // 1 HTML snapshot + 2 PDFs
        $this->assertSame(0, $run->items_failed);

        $this->assertDatabaseHas('crawl_runs', [
            'id' => $run->id,
            'source_id' => $this->source->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseCount('source_artifacts', 3);

        $this->source->refresh();
        $this->assertNotNull($this->source->last_crawled_at);
        $this->assertNotNull($this->source->last_success_at);
        $this->assertSame(0, $this->source->consecutive_failures);
    }

    #[Test]
    public function it_deduplicates_identical_artifacts_by_sha256_hash(): void
    {
        $pdfBody = '%PDF-1.4 Identical Content File';
        $hash = hash('sha256', $pdfBody);

        // Pre-existing artifact
        SourceArtifact::factory()->create([
            'source_id' => $this->source->id,
            'content_hash' => $hash,
        ]);

        $htmlBody = '<a href="https://example.gov.in/advt.pdf">Download</a>';

        $mockClient = Mockery::mock(SafeHttpClient::class, function (MockInterface $mock) use ($htmlBody, $pdfBody) {
            $htmlResponse = Mockery::mock(Response::class);
            $htmlResponse->shouldReceive('successful')->andReturn(true);
            $htmlResponse->shouldReceive('status')->andReturn(200);
            $htmlResponse->shouldReceive('header')->with('Content-Type')->andReturn('text/html');
            $htmlResponse->shouldReceive('body')->andReturn($htmlBody);

            $mock->shouldReceive('get')
                ->with('https://example.gov.in/notices')
                ->once()
                ->andReturn($htmlResponse);

            $pdfResponse = Mockery::mock(Response::class);
            $pdfResponse->shouldReceive('successful')->andReturn(true);
            $pdfResponse->shouldReceive('header')->with('Content-Type')->andReturn('application/pdf');
            $pdfResponse->shouldReceive('body')->andReturn($pdfBody);

            $mock->shouldReceive('get')
                ->with('https://example.gov.in/advt.pdf')
                ->once()
                ->andReturn($pdfResponse);
        });

        $this->app->instance(SafeHttpClient::class, $mockClient);

        $service = $this->app->make(SourceCrawlService::class);
        $run = $service->crawl($this->source);

        $this->assertSame('completed', $run->status);

        // 1 pre-existing artifact + 1 new HTML snapshot artifact = 2 total
        $this->assertDatabaseCount('source_artifacts', 2);
    }

    #[Test]
    public function it_handles_crawl_http_failure_and_marks_run_as_failed(): void
    {
        $mockClient = Mockery::mock(SafeHttpClient::class, function (MockInterface $mock) {
            $failResponse = Mockery::mock(Response::class);
            $failResponse->shouldReceive('successful')->andReturn(false);
            $failResponse->shouldReceive('status')->andReturn(503);

            $mock->shouldReceive('get')
                ->with('https://example.gov.in/notices')
                ->once()
                ->andReturn($failResponse);
        });

        $this->app->instance(SafeHttpClient::class, $mockClient);

        $service = $this->app->make(SourceCrawlService::class);
        $run = $service->crawl($this->source);

        $this->assertSame('failed', $run->status);
        $this->assertSame(503, $run->http_status);
        $this->assertStringContainsString('Crawl failed with HTTP status 503', $run->error_message ?? '');

        $this->source->refresh();
        $this->assertNotNull($this->source->last_failure_at);
        $this->assertSame(1, $this->source->consecutive_failures);
    }
}
