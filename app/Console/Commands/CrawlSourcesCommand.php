<?php

namespace App\Console\Commands;

use App\Jobs\CrawlSourceJob;
use App\Models\Source;
use Illuminate\Console\Command;

class CrawlSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'suchak:crawl-sources {--source= : Specific source ULID to crawl immediately} {--sync : Run crawl synchronously instead of queueing}';

    /**
     * The console command description.
     */
    protected $description = 'Dispatch crawl jobs for active sources due for scraping';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $specificSourceId = $this->option('source');
        $runSync = (bool) $this->option('sync');

        if ($specificSourceId) {
            $sources = Source::where('id', $specificSourceId)->get();
            if ($sources->isEmpty()) {
                $this->error("Source with ID [{$specificSourceId}] not found.");

                return self::FAILURE;
            }
        } else {
            $sources = Source::dueForCrawl()->get();
        }

        if ($sources->isEmpty()) {
            $this->info('No sources are currently due for crawling.');

            return self::SUCCESS;
        }

        $this->info("Found {$sources->count()} source(s) to crawl.");

        foreach ($sources as $source) {
            $this->line("Dispatching crawl for [{$source->name}] ({$source->domain})...");

            if ($runSync) {
                CrawlSourceJob::dispatchSync($source);
            } else {
                CrawlSourceJob::dispatch($source);
            }
        }

        $this->info('Crawl jobs successfully dispatched.');

        return self::SUCCESS;
    }
}
