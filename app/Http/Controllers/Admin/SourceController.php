<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CrawlMethod;
use App\Enums\SourceType;
use App\Enums\TrustLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSourceRequest;
use App\Http\Requests\Admin\UpdateSourceRequest;
use App\Jobs\CrawlSourceJob;
use App\Jobs\ProcessArtifactExtractionJob;
use App\Models\Institution;
use App\Models\Source;
use App\Models\SourceArtifact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SourceController extends Controller
{
    /**
     * Display a listing of sources.
     */
    public function index(Request $request): Response
    {
        $query = Source::query()
            ->with('institution:id,name,short_name,slug')
            ->withCount(['crawlRuns', 'artifacts']);

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                    ->orWhere('domain', 'ilike', "%{$term}%")
                    ->orWhere('url', 'ilike', "%{$term}%")
                    ->orWhereHas('institution', fn ($sub) => $sub->where('name', 'ilike', "%{$term}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('trust_level')) {
            $query->where('trust_level', $request->string('trust_level')->toString());
        }

        $sources = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('admin/sources/Index', [
            'sources' => $sources,
            'filters' => [
                'search' => $request->query('search', ''),
                'status' => $request->query('status', ''),
                'trust_level' => $request->query('trust_level', ''),
            ],
            'sourceTypes' => array_map(fn (SourceType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ], SourceType::cases()),
            'trustLevels' => array_map(fn (TrustLevel $level) => [
                'value' => $level->value,
                'label' => $level->label(),
            ], TrustLevel::cases()),
        ]);
    }

    /**
     * Show the form for creating a new source.
     */
    public function create(): Response
    {
        return Inertia::render('admin/sources/Create', [
            'institutions' => Institution::orderBy('name')->get(['id', 'name', 'short_name']),
            'sourceTypes' => array_map(fn (SourceType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ], SourceType::cases()),
            'crawlMethods' => array_map(fn (CrawlMethod $method) => [
                'value' => $method->value,
                'label' => $method->label(),
            ], CrawlMethod::cases()),
            'trustLevels' => array_map(fn (TrustLevel $level) => [
                'value' => $level->value,
                'label' => $level->label(),
            ], TrustLevel::cases()),
            'sourceStatuses' => array_map(fn (SourceStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ], SourceStatus::cases()),
        ]);
    }

    /**
     * Store a newly created source in storage.
     */
    public function store(StoreSourceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $parsedHost = parse_url($validated['url'], PHP_URL_HOST);
        $domain = is_string($parsedHost) ? strtolower($parsedHost) : '';

        $source = Source::create([
            ...$validated,
            'domain' => $domain,
            'next_crawl_at' => now(),
            'metadata' => [],
        ]);

        return redirect()->route('admin.sources.show', $source)
            ->with('status', 'Source created successfully.');
    }

    /**
     * Display the specified source with its runs and artifacts.
     */
    public function show(Source $source): Response
    {
        $source->load([
            'institution:id,name,short_name,slug,official_domain',
            'crawlRuns' => fn ($q) => $q->latest('started_at')->limit(20),
            'artifacts' => fn ($q) => $q->with([
                'extractions' => fn ($eq) => $eq->latest('started_at')->limit(5),
                'notices:id,source_artifact_id,title,slug,status,total_vacancies,confidence_score',
            ])->latest('retrieved_at')->limit(20),
        ]);

        return Inertia::render('admin/sources/Show', [
            'source' => $source,
        ]);
    }

    /**
     * Show the form for editing the specified source.
     */
    public function edit(Source $source): Response
    {
        return Inertia::render('admin/sources/Edit', [
            'source' => $source,
            'institutions' => Institution::orderBy('name')->get(['id', 'name', 'short_name']),
            'sourceTypes' => array_map(fn (SourceType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ], SourceType::cases()),
            'crawlMethods' => array_map(fn (CrawlMethod $method) => [
                'value' => $method->value,
                'label' => $method->label(),
            ], CrawlMethod::cases()),
            'trustLevels' => array_map(fn (TrustLevel $level) => [
                'value' => $level->value,
                'label' => $level->label(),
            ], TrustLevel::cases()),
            'sourceStatuses' => array_map(fn (SourceStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ], SourceStatus::cases()),
        ]);
    }

    /**
     * Update the specified source in storage.
     */
    public function update(UpdateSourceRequest $request, Source $source): RedirectResponse
    {
        $validated = $request->validated();
        $parsedHost = parse_url($validated['url'], PHP_URL_HOST);
        $domain = is_string($parsedHost) ? strtolower($parsedHost) : '';

        $source->update([
            ...$validated,
            'domain' => $domain,
        ]);

        return redirect()->route('admin.sources.show', $source)
            ->with('status', 'Source updated successfully.');
    }

    /**
     * Remove the specified source from storage.
     */
    public function destroy(Source $source): RedirectResponse
    {
        $source->delete();

        return redirect()->route('admin.sources.index')
            ->with('status', 'Source deleted successfully.');
    }

    /**
     * Dispatch an immediate crawl job for the source.
     */
    public function crawlNow(Source $source): RedirectResponse
    {
        CrawlSourceJob::dispatch($source);

        return back()->with('status', "Crawl run queued for {$source->name}.");
    }

    /**
     * Trigger extraction for an artifact.
     */
    public function extractArtifact(SourceArtifact $artifact): RedirectResponse
    {
        ProcessArtifactExtractionJob::dispatchSync($artifact);

        return back()->with('status', "Document extraction executed for artifact {$artifact->title}.");
    }
}
