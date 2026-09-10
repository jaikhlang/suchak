<?php

namespace App\Console\Commands;

use App\Jobs\ProcessArtifactExtractionJob;
use App\Models\SourceArtifact;
use Illuminate\Console\Command;

class ExtractArtifactCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'suchak:extract-artifact {artifact_id : ULID of the source artifact} {--sync : Run extraction synchronously}';

    /**
     * The console command description.
     */
    protected $description = 'Trigger document intelligence extraction and notice generation for a source artifact';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $artifactId = $this->argument('artifact_id');
        $runSync = (bool) $this->option('sync');

        $artifact = SourceArtifact::with('source.institution')->find($artifactId);

        if (! $artifact) {
            $this->error("Source artifact [{$artifactId}] not found.");

            return self::FAILURE;
        }

        $this->info("Found artifact: {$artifact->title} ({$artifact->canonical_url})");
        $this->line("Source: {$artifact->source?->name} ({$artifact->source?->institution?->name})");

        if ($runSync) {
            $this->line('Running extraction synchronously...');
            ProcessArtifactExtractionJob::dispatchSync($artifact);
            $this->info('Extraction job finished synchronously.');
        } else {
            ProcessArtifactExtractionJob::dispatch($artifact);
            $this->info('Extraction job successfully dispatched to queue.');
        }

        return self::SUCCESS;
    }
}
