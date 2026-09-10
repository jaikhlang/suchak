<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Sources', href: '/admin/sources' },
            { title: 'Source Details', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Clock from '@lucide/svelte/icons/clock';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import FileText from '@lucide/svelte/icons/file-text';
    import Globe from '@lucide/svelte/icons/globe';
    import Play from '@lucide/svelte/icons/play';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import XCircle from '@lucide/svelte/icons/x-circle';
    import AlertCircle from '@lucide/svelte/icons/alert-circle';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Sparkles from '@lucide/svelte/icons/sparkles';

    interface InstitutionInfo {
        id: string;
        name: string;
        short_name: string | null;
        slug: string;
        official_domain: string;
    }

    interface CrawlRunItem {
        id: string;
        status: string;
        started_at: string;
        finished_at: string | null;
        items_discovered: number;
        items_fetched: number;
        items_failed: number;
        http_status: number | null;
        error_code: string | null;
        error_message: string | null;
    }

    interface ExtractionSummary {
        id: string;
        method: string;
        status: string;
        confidence_score: number | null;
        processor_name: string;
    }

    interface NoticeSummary {
        id: string;
        source_artifact_id: string;
        title: string;
        slug: string;
        status: string;
        total_vacancies: number;
        confidence_score: number;
    }

    interface ArtifactItem {
        id: string;
        type: string;
        url: string;
        canonical_url: string;
        content_hash: string;
        mime_type: string;
        file_size_bytes: number;
        title: string | null;
        retrieved_at: string;
        extractions?: ExtractionSummary[];
        notices?: NoticeSummary[];
    }

    interface SourceDetail {
        id: string;
        name: string;
        type: string;
        url: string;
        domain: string;
        crawl_method: string;
        crawl_frequency_minutes: number;
        status: string;
        trust_level: string;
        consecutive_failures: number;
        last_crawled_at: string | null;
        next_crawl_at: string | null;
        last_success_at: string | null;
        last_failure_at: string | null;
        configuration: Record<string, any>;
        institution: InstitutionInfo | null;
        crawl_runs: CrawlRunItem[];
        artifacts: ArtifactItem[];
    }

    interface Props {
        source: SourceDetail;
    }

    let { source }: Props = $props();

    let isTriggering = $state(false);

    function triggerCrawl() {
        isTriggering = true;
        router.post(
            `/admin/sources/${source.id}/crawl`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    isTriggering = false;
                },
            }
        );
    }

    let extractingArtifactId = $state<string | null>(null);

    function triggerExtract(artifactId: string) {
        extractingArtifactId = artifactId;
        router.post(
            `/admin/sources/artifacts/${artifactId}/extract`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    extractingArtifactId = null;
                },
            }
        );
    }

    function formatBytes(bytes: number): string {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
</script>

<AppHead title={source.name} />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
    <!-- Top Navigation & Action -->
    <div class="flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <Link
                href="/admin/sources"
                class="p-2 rounded-lg border hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
            >
                <ArrowLeft class="w-4 h-4" />
            </Link>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        {source.name}
                    </h1>
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {source.status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : source.status === 'paused' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : source.status === 'failing' ? 'bg-destructive/10 text-destructive' : 'bg-muted text-muted-foreground'}"
                    >
                        {source.status}
                    </span>
                </div>
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <span>{source.domain}</span>
                    <span>•</span>
                    <a
                        href={source.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-primary hover:underline"
                    >
                        {source.url}
                        <ExternalLink class="h-3 w-3" />
                    </a>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <Link
                href={`/admin/sources/${source.id}/edit`}
                class="inline-flex items-center gap-1.5 rounded-lg border hover:bg-muted px-3.5 py-2 text-sm font-medium text-foreground transition-colors"
            >
                <Pencil class="w-4 h-4" />
                Edit
            </Link>

            <button
                type="button"
                onclick={triggerCrawl}
                disabled={isTriggering}
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs disabled:opacity-50 transition-all"
            >
                <Play class="w-4 h-4 {isTriggering ? 'animate-spin' : ''}" />
                {isTriggering ? 'Queuing Crawl...' : 'Trigger Crawl Now'}
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border bg-card text-card-foreground p-4 shadow-xs">
            <div class="text-xs font-medium text-muted-foreground">Institution</div>
            <div class="mt-1 text-base font-semibold text-foreground">
                {source.institution?.short_name ?? source.institution?.name ?? 'None'}
            </div>
            <div class="text-xs text-muted-foreground">{source.institution?.name}</div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground p-4 shadow-xs">
            <div class="text-xs font-medium text-muted-foreground">Crawl Method & Schedule</div>
            <div class="mt-1 text-base font-semibold text-foreground">
                {source.crawl_method}
            </div>
            <div class="text-xs text-muted-foreground">Every {source.crawl_frequency_minutes} minutes</div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground p-4 shadow-xs">
            <div class="text-xs font-medium text-muted-foreground">Trust Level</div>
            <div class="mt-1 text-base font-semibold text-foreground">
                {source.trust_level.replace('_', ' ')}
            </div>
            <div class="text-xs text-primary">Strict SSRF validation active</div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground p-4 shadow-xs">
            <div class="text-xs font-medium text-muted-foreground">Next Scheduled Run</div>
            <div class="mt-1 text-base font-semibold text-foreground">
                {#if source.next_crawl_at}
                    {new Date(source.next_crawl_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                {:else}
                    Immediate
                {/if}
            </div>
            <div class="text-xs text-muted-foreground">
                Last: {source.last_crawled_at ? new Date(source.last_crawled_at).toLocaleTimeString() : 'Never'}
            </div>
        </div>
    </div>

    <!-- Crawl Runs History -->
    <div class="rounded-xl border bg-card text-card-foreground p-6 shadow-xs">
        <h2 class="text-lg font-semibold text-foreground mb-4">
            Recent Crawl Executions ({source.crawl_runs.length})
        </h2>

        {#if source.crawl_runs.length === 0}
            <div class="py-8 text-center text-sm text-muted-foreground">
                No crawl executions recorded yet. Click "Trigger Crawl Now" to begin scraping.
            </div>
        {:else}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-muted/50 border-b text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5">Started At</th>
                            <th class="px-4 py-2.5">Duration</th>
                            <th class="px-4 py-2.5 text-center">Discovered</th>
                            <th class="px-4 py-2.5 text-center">Fetched</th>
                            <th class="px-4 py-2.5 text-center">Failed</th>
                            <th class="px-4 py-2.5">Details / Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        {#each source.crawl_runs as run (run.id)}
                            <tr class="hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3">
                                    {#if run.status === 'completed'}
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            <CheckCircle2 class="h-3 w-3" />
                                            Completed
                                        </span>
                                    {:else if run.status === 'running'}
                                        <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                                            <Clock class="h-3 w-3 animate-spin" />
                                            Running
                                        </span>
                                    {:else}
                                        <span class="inline-flex items-center gap-1 rounded-full bg-destructive/10 px-2 py-0.5 text-xs font-medium text-destructive">
                                            <XCircle class="h-3 w-3" />
                                            Failed
                                        </span>
                                    {/if}
                                </td>
                                <td class="px-4 py-3 text-xs text-muted-foreground">
                                    {new Date(run.started_at).toLocaleString()}
                                </td>
                                <td class="px-4 py-3 text-xs text-muted-foreground">
                                    {#if run.finished_at}
                                        {Math.round((new Date(run.finished_at).getTime() - new Date(run.started_at).getTime()) / 1000)}s
                                    {:else}
                                        -
                                    {/if}
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-foreground">{run.items_discovered}</td>
                                <td class="px-4 py-3 text-center font-medium text-emerald-600 dark:text-emerald-400">{run.items_fetched}</td>
                                <td class="px-4 py-3 text-center font-medium text-destructive">{run.items_failed}</td>
                                <td class="px-4 py-3 text-xs">
                                    {#if run.error_message}
                                        <span class="text-destructive truncate max-w-xs block" title={run.error_message}>
                                            {run.error_message}
                                        </span>
                                    {:else}
                                        <span class="text-muted-foreground">HTTP {run.http_status ?? 200}</span>
                                    {/if}
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>

    <!-- Downloaded Artifacts -->
    <div class="rounded-xl border bg-card text-card-foreground p-6 shadow-xs">
        <h2 class="text-lg font-semibold text-foreground mb-4">
            Ingested Artifacts ({source.artifacts.length})
        </h2>

        {#if source.artifacts.length === 0}
            <div class="py-8 text-center text-sm text-muted-foreground">
                No artifacts downloaded yet for this source.
            </div>
        {:else}
            <div class="divide-y">
                {#each source.artifacts as artifact (artifact.id)}
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 py-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="rounded-lg bg-primary/10 p-2 text-primary shrink-0 mt-0.5">
                                <FileText class="h-4 w-4" />
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium text-foreground flex flex-wrap items-center gap-2">
                                    <span>{artifact.title ?? 'Official Notice Document'}</span>
                                    <span class="rounded bg-muted px-2 py-0.5 text-[10px] font-semibold text-muted-foreground uppercase">
                                        {artifact.type}
                                    </span>
                                </div>
                                <div class="text-xs text-muted-foreground break-all mt-0.5">
                                    {artifact.canonical_url}
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted-foreground font-mono">
                                    <span>SHA: {artifact.content_hash.substring(0, 12)}...</span>
                                    <span>•</span>
                                    <span>Size: {formatBytes(artifact.file_size_bytes)}</span>
                                    <span>•</span>
                                    <span>Retrieved: {new Date(artifact.retrieved_at).toLocaleString()}</span>
                                </div>

                                <!-- Extractions & Generated Notices Status -->
                                <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                    {#if artifact.extractions && artifact.extractions.length > 0}
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            <CheckCircle2 class="h-3 w-3" />
                                            Extracted ({artifact.extractions[0].processor_name})
                                        </span>
                                    {:else}
                                        <span class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground">
                                            Not Extracted
                                        </span>
                                    {/if}

                                    {#if artifact.notices && artifact.notices.length > 0}
                                        {#each artifact.notices as n (n.id)}
                                            <Link
                                                href={n.status === 'pending_review' ? `/admin/moderation/${n.id}` : `/admin/notices/${n.id}`}
                                                class="inline-flex items-center gap-1.5 rounded-md border bg-card hover:bg-muted/50 px-2 py-0.5 text-xs font-medium text-foreground transition-colors"
                                            >
                                                <Sparkles class="h-3 w-3 text-primary" />
                                                <span>{n.title}</span>
                                                <span class="rounded px-1.5 py-0.2 text-[10px] uppercase font-semibold {n.status === 'published' ? 'bg-emerald-500/10 text-emerald-600' : n.status === 'pending_review' ? 'bg-amber-500/10 text-amber-600' : 'bg-muted text-muted-foreground'}">
                                                    {n.status}
                                                </span>
                                            </Link>
                                        {/each}
                                    {/if}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-start">
                            <a
                                href={artifact.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1 rounded-lg border hover:bg-muted/50 px-2.5 py-1.5 text-xs font-medium text-muted-foreground hover:text-foreground transition-colors"
                            >
                                <ExternalLink class="h-3.5 w-3.5" />
                                View Source
                            </a>

                            <button
                                type="button"
                                onclick={() => triggerExtract(artifact.id)}
                                disabled={extractingArtifactId === artifact.id}
                                class="inline-flex items-center gap-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-primary-foreground px-3 py-1.5 text-xs font-medium shadow-xs disabled:opacity-50 transition-all"
                            >
                                <Sparkles class="h-3.5 w-3.5 {extractingArtifactId === artifact.id ? 'animate-spin' : ''}" />
                                {extractingArtifactId === artifact.id ? 'Extracting...' : 'Extract Facts'}
                            </button>
                        </div>
                    </div>
                {/each}
            </div>
        {/if}
    </div>
</div>
