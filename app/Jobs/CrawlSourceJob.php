<?php

namespace App\Jobs;

use App\Enums\QueueName;
use App\Models\Source;
use App\Services\Crawling\SourceCrawlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(public Source $source)
    {
        $this->onQueue(QueueName::Crawl->value);
    }

    /**
     * Execute the job.
     */
    public function handle(SourceCrawlService $crawler): void
    {
        $crawler->crawl($this->source);
    }
}
