<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Link } from '@inertiajs/svelte';
    import AlertTriangle from '@lucide/svelte/icons/alert-triangle';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import Bell from '@lucide/svelte/icons/bell';
    import Building2 from '@lucide/svelte/icons/building-2';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import FileText from '@lucide/svelte/icons/file-text';
    import Globe from '@lucide/svelte/icons/globe';
    import RefreshCw from '@lucide/svelte/icons/refresh-cw';
    import ShieldAlert from '@lucide/svelte/icons/shield-alert';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Users from '@lucide/svelte/icons/users';
    import XCircle from '@lucide/svelte/icons/x-circle';

    interface InstitutionSummary {
        id: string;
        name: string;
        short_name: string | null;
        slug: string;
        institution_type: string;
        is_verified: boolean;
        published_notices_count?: number;
        pending_notices_count?: number;
    }

    interface UrgentNotice {
        id: string;
        institution_id: string;
        title: string;
        slug: string;
        reference_number: string | null;
        confidence_score: number;
        total_vacancies: number;
        application_end_at: string | null;
        is_corrigendum: boolean;
        metadata: {
            hallucination_warning?: boolean;
            grounding_failures?: Array<{ field: string }>;
        };
        institution: InstitutionSummary;
        created_at: string;
    }

    interface CrawlRunSummary {
        id: string;
        source_id: string;
        status: string;
        started_at: string;
        finished_at: string | null;
        items_discovered: number;
        items_fetched: number;
        items_failed: number;
        http_status: number | null;
        error_code: string | null;
        source: {
            id: string;
            name: string;
            domain: string;
            institution?: InstitutionSummary;
        };
    }

    interface Props {
        metrics: {
            total_vacancies: number;
            published_notices: number;
            pending_moderation: number;
            high_risk_notices: number;
            active_sources: number;
            failing_sources: number;
            total_subscribers: number;
        };
        urgent_notices: UrgentNotice[];
        recent_crawl_runs: CrawlRunSummary[];
        top_institutions: InstitutionSummary[];
        system_health: {
            sidecar_online: boolean;
            database_type: string;
            queue_driver: string;
        };
    }

    let { metrics, urgent_notices, recent_crawl_runs, top_institutions, system_health }: Props = $props();

    function formatNumber(num: number): string {
        return new Intl.NumberFormat('en-IN').format(num);
    }

    function formatConfidence(score: number): string {
        return Math.round(score * 100) + '%';
    }

    function formatDate(dateStr: string | null): string {
        if (!dateStr) return 'TBD';
        return new Date(dateStr).toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    }

    function formatTimeAgo(dateStr: string): string {
        const diffMs = Date.now() - new Date(dateStr).getTime();
        const mins = Math.floor(diffMs / 60000);
        if (mins < 1) return 'just now';
        if (mins < 60) return `${mins}m ago`;
        const hours = Math.floor(mins / 60);
        if (hours < 24) return `${hours}h ago`;
        return `${Math.floor(hours / 24)}d ago`;
    }
</script>

<AppHead title="Intelligence Operations Dashboard" />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                    Platform Control Room
                </span>
                <span class="inline-flex items-center gap-1 text-xs text-muted-foreground">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Intelligence System
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-foreground mt-1">
                Suchak Operations Dashboard
            </h1>
            <p class="text-sm text-muted-foreground mt-1">
                Real-time recruitment telemetry, automated gazette ingestion, and zero-hallucination verification metrics.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <Link
                href="/admin/moderation"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all"
            >
                <ShieldCheck class="w-4 h-4" />
                Moderation Queue
                {#if metrics.pending_moderation > 0}
                    <span class="ml-1 px-1.5 py-0.5 text-xs font-semibold rounded-full bg-primary-foreground text-primary">
                        {metrics.pending_moderation}
                    </span>
                {/if}
            </Link>

            <a
                href="/recruitment"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg border bg-card text-foreground hover:bg-muted/50 transition-colors shadow-xs"
            >
                <ExternalLink class="w-4 h-4 text-muted-foreground" />
                Candidate Portal
            </a>
        </div>
    </div>

    <!-- System Diagnostic Health Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-xl border bg-card/60 shadow-xs text-xs">
        <div class="flex items-center justify-between">
            <span class="text-muted-foreground">Python Sidecar (Docling / PaddleOCR):</span>
            {#if system_health.sidecar_online}
                <span class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                    <CheckCircle2 class="w-3.5 h-3.5" /> Online (Port 8001)
                </span>
            {:else}
                <span class="inline-flex items-center gap-1 font-semibold text-amber-600 dark:text-amber-400">
                    <AlertTriangle class="w-3.5 h-3.5" /> Standby / Local Fallback
                </span>
            {/if}
        </div>

        <div class="flex items-center justify-between border-t sm:border-t-0 sm:border-l sm:pl-4 border-border">
            <span class="text-muted-foreground">Anti-Hallucination Guardrails:</span>
            <span class="inline-flex items-center gap-1 font-semibold text-primary">
                <ShieldCheck class="w-3.5 h-3.5" /> Verbatim Grounding Active
            </span>
        </div>

        <div class="flex items-center justify-between border-t sm:border-t-0 sm:border-l sm:pl-4 border-border">
            <span class="text-muted-foreground">Outbound Distribution Gateway:</span>
            <span class="inline-flex items-center gap-1 font-semibold text-muted-foreground">
                HMAC-SHA256 Signed (n8n + RSS)
            </span>
        </div>
    </div>

    <!-- Core Metrics KPI Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Metric 1: Total Active Vacancies -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Active Vacancies</span>
                <span class="p-2 rounded-lg bg-primary/10 text-primary">
                    <Users class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground tracking-tight">
                {formatNumber(metrics.total_vacancies)}
            </div>
            <div class="text-xs text-muted-foreground mt-1">Across verified opportunities</div>
        </div>

        <!-- Metric 2: Published Notices -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Published Gazettes</span>
                <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <FileText class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground tracking-tight">
                {formatNumber(metrics.published_notices)}
            </div>
            <div class="text-xs text-muted-foreground mt-1">Schema.org JobPosting active</div>
        </div>

        <!-- Metric 3: Moderation Pending -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Pending Review</span>
                <span class="p-2 rounded-lg {metrics.high_risk_notices > 0 ? 'bg-destructive/10 text-destructive' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'}">
                    {#if metrics.high_risk_notices > 0}
                        <ShieldAlert class="w-4 h-4" />
                    {:else}
                        <ShieldCheck class="w-4 h-4" />
                    {/if}
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground tracking-tight">
                {metrics.pending_moderation}
            </div>
            <div class="text-xs text-muted-foreground mt-1">
                {#if metrics.high_risk_notices > 0}
                    <span class="text-destructive font-medium">{metrics.high_risk_notices} high risk / ungrounded</span>
                {:else}
                    Standard verification
                {/if}
            </div>
        </div>

        <!-- Metric 4: Crawlers & Sources -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Active Crawlers</span>
                <span class="p-2 rounded-lg bg-primary/10 text-primary">
                    <Globe class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground tracking-tight">
                {metrics.active_sources}
            </div>
            <div class="text-xs text-muted-foreground mt-1">
                {#if metrics.failing_sources > 0}
                    <span class="text-destructive font-medium">{metrics.failing_sources} failing health check</span>
                {:else}
                    All sources healthy
                {/if}
            </div>
        </div>

        <!-- Metric 5: Alert Subscribers -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Alert Subscribers</span>
                <span class="p-2 rounded-lg bg-primary/10 text-primary">
                    <Bell class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground tracking-tight">
                {formatNumber(metrics.total_subscribers)}
            </div>
            <div class="text-xs text-muted-foreground mt-1">Direct gazette alert reach</div>
        </div>
    </div>

    <!-- Main Dashboard Section: Urgent Moderation & Ingestion Stream -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Panel: Urgent Moderation Workbench Queue (7 Cols) -->
        <div class="lg:col-span-7 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-foreground flex items-center gap-2">
                        <ShieldAlert class="w-5 h-5 text-amber-500" />
                        Urgent Verification Queue
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        Notices requiring human grounding audit before publication.
                    </p>
                </div>

                <Link
                    href="/admin/moderation"
                    class="text-xs font-medium text-primary hover:underline flex items-center gap-1"
                >
                    View All ({metrics.pending_moderation}) <ArrowRight class="w-3 h-3" />
                </Link>
            </div>

            {#if urgent_notices.length === 0}
                <div class="p-8 rounded-xl border bg-card text-card-foreground text-center shadow-xs">
                    <CheckCircle2 class="w-10 h-10 text-emerald-500 mx-auto mb-2 opacity-80" />
                    <div class="text-sm font-semibold text-foreground">Queue is Clear!</div>
                    <p class="text-xs text-muted-foreground mt-1">
                        All extracted recruitment notices have been verified and processed.
                    </p>
                </div>
            {:else}
                <div class="flex flex-col gap-3">
                    {#each urgent_notices as item (item.id)}
                        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs hover:border-primary/40 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-muted text-muted-foreground">
                                        {item.institution.short_name ?? item.institution.name}
                                    </span>

                                    {#if item.is_corrigendum}
                                        <span class="text-xs font-medium px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            शुद्धिपत्र Corrigendum
                                        </span>
                                    {/if}

                                    {#if item.metadata?.hallucination_warning}
                                        <span class="text-xs font-medium px-2 py-0.5 rounded bg-destructive/10 text-destructive border border-destructive/20 flex items-center gap-1">
                                            <AlertTriangle class="w-3 h-3" /> Ungrounded
                                        </span>
                                    {/if}

                                    <span class="text-xs text-muted-foreground">
                                        {formatDate(item.created_at)}
                                    </span>
                                </div>

                                <div class="font-semibold text-foreground text-sm mt-1.5 truncate">
                                    {item.title}
                                </div>

                                <div class="flex items-center gap-3 text-xs text-muted-foreground mt-1">
                                    {#if item.reference_number}
                                        <span>Advt: <span class="font-mono text-foreground">{item.reference_number}</span></span>
                                    {/if}
                                    <span>Vacancies: <strong class="text-foreground">{formatNumber(item.total_vacancies)}</strong></span>
                                    <span>Confidence: <strong class="{item.confidence_score < 0.75 ? 'text-destructive' : 'text-amber-500'}">{formatConfidence(item.confidence_score)}</strong></span>
                                </div>
                            </div>

                            <div>
                                <Link
                                    href={`/admin/moderation/${item.id}`}
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-all shadow-xs shrink-0"
                                >
                                    <Sparkles class="w-3.5 h-3.5" />
                                    Verify
                                </Link>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- Right Panel: Live Ingestion & Crawl Telemetry (5 Cols) -->
        <div class="lg:col-span-5 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-foreground flex items-center gap-2">
                        <Globe class="w-5 h-5 text-primary" />
                        Crawler Ingestion Stream
                    </h2>
                    <p class="text-xs text-muted-foreground">
                        Recent runs monitoring official portals.
                    </p>
                </div>

                <Link
                    href="/admin/sources"
                    class="text-xs font-medium text-primary hover:underline flex items-center gap-1"
                >
                    Sources <ArrowRight class="w-3 h-3" />
                </Link>
            </div>

            <div class="rounded-xl border bg-card text-card-foreground shadow-xs overflow-hidden">
                {#if recent_crawl_runs.length === 0}
                    <div class="p-6 text-center text-xs text-muted-foreground">
                        No crawl runs recorded yet.
                    </div>
                {:else}
                    <div class="divide-y divide-border/60 text-xs">
                        {#each recent_crawl_runs as run (run.id)}
                            <div class="p-3 hover:bg-muted/30 transition-colors flex items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-foreground truncate">
                                            {run.source.name}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-muted-foreground mt-0.5">
                                        <span class="font-mono">{run.source.domain}</span>
                                        <span>•</span>
                                        <span>{formatTimeAgo(run.started_at)}</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-muted-foreground font-mono">
                                        +{run.items_discovered} docs
                                    </span>

                                    {#if run.status === 'completed'}
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                            HTTP {run.http_status ?? 200}
                                        </span>
                                    {:else if run.status === 'running'}
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-primary/10 text-primary">
                                            <RefreshCw class="w-2.5 h-2.5 animate-spin" /> Ingesting
                                        </span>
                                    {:else}
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-destructive/10 text-destructive">
                                            {run.error_code ?? 'Failed'}
                                        </span>
                                    {/if}
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- Top Authority Distribution -->
            <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-foreground uppercase tracking-wider">Top Recruiting Authorities</span>
                    <Link href="/admin/institutions" class="text-xs text-primary hover:underline">Manage</Link>
                </div>

                <div class="flex flex-col gap-2">
                    {#each top_institutions as inst (inst.id)}
                        <div class="flex items-center justify-between text-xs py-1 border-b border-border/40 last:border-0">
                            <div class="flex items-center gap-2 truncate">
                                <Building2 class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                <span class="font-medium text-foreground truncate">{inst.name}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-muted-foreground">{inst.published_notices_count ?? 0} live</span>
                                {#if (inst.pending_notices_count ?? 0) > 0}
                                    <span class="px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-600 font-semibold text-[10px]">
                                        +{inst.pending_notices_count}
                                    </span>
                                {/if}
                            </div>
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>
