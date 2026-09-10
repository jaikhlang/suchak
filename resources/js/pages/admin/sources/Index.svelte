<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: "Dashboard", href: "/dashboard" },
            { title: "Sources & Crawlers", href: "/admin/sources" },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Link, router } from "@inertiajs/svelte";
    import Globe from "@lucide/svelte/icons/globe";
    import Play from "@lucide/svelte/icons/play";
    import Plus from "@lucide/svelte/icons/plus";
    import Search from "@lucide/svelte/icons/search";
    import ShieldCheck from "@lucide/svelte/icons/shield-check";
    import RefreshCw from "@lucide/svelte/icons/refresh-cw";
    import ExternalLink from "@lucide/svelte/icons/external-link";
    import Pencil from "@lucide/svelte/icons/pencil";
    import Eye from "@lucide/svelte/icons/eye";
    import Trash2 from "@lucide/svelte/icons/trash-2";

    interface InstitutionSummary {
        id: string;
        name: string;
        short_name: string | null;
        slug: string;
    }

    interface SourceItem {
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
        institution: InstitutionSummary | null;
        crawl_runs_count: number;
        artifacts_count: number;
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface PaginatedSources {
        data: SourceItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: PaginationLink[];
    }

    interface FilterOption {
        value: string;
        label: string;
    }

    interface Props {
        sources: PaginatedSources;
        filters: {
            search: string;
            status: string;
            trust_level: string;
        };
        sourceTypes: FilterOption[];
        trustLevels: FilterOption[];
    }

    let { sources, filters, sourceTypes, trustLevels }: Props = $props();

    let searchTerm = $state(filters.search ?? "");
    let selectedStatus = $state(filters.status ?? "");
    let selectedTrust = $state(filters.trust_level ?? "");
    let isTriggering = $state<string | null>(null);

    function handleFilter() {
        router.get(
            "/admin/sources",
            {
                search: searchTerm || undefined,
                status: selectedStatus || undefined,
                trust_level: selectedTrust || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    function triggerCrawl(sourceId: string) {
        isTriggering = sourceId;
        router.post(
            `/admin/sources/${sourceId}/crawl`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    isTriggering = null;
                },
            },
        );
    }

    function deleteSource(source: SourceItem) {
        if (
            confirm(
                `Are you sure you want to delete source "${source.name}"? All associated crawl runs and artifacts will also be removed.`,
            )
        ) {
            router.delete(`/admin/sources/${source.id}`);
        }
    }
</script>

<AppHead title="Sources & Crawlers" />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div
        class="flex flex-col gap-4 border-b pb-5 sm:flex-row sm:items-center sm:justify-between"
    >
        <div>
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary"
                >
                    Ingestion Layer
                </span>
                <span class="text-xs text-muted-foreground"
                    >Total: {sources.total}</span
                >
            </div>
            <h1
                class="text-2xl md:text-3xl font-bold tracking-tight text-foreground mt-1"
            >
                Official Sources & Crawlers
            </h1>
            <p class="text-sm text-muted-foreground mt-1">
                Manage government portal crawlers, scraping schedules, and PDF
                noticeboard monitors.
            </p>
        </div>

        <div>
            <Link
                href="/admin/sources/create"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all"
            >
                <Plus class="w-4 h-4" />
                Add New Source
            </Link>
        </div>
    </div>

    <!-- Filters Bar -->
    <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 p-4 rounded-xl border bg-card/60 shadow-xs"
    >
        <div class="relative sm:col-span-2">
            <Search
                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
            />
            <input
                type="text"
                bind:value={searchTerm}
                onkeydown={(e) => e.key === "Enter" && handleFilter()}
                placeholder="Search source name, domain or URL..."
                class="w-full rounded-lg border bg-background text-foreground placeholder:text-muted-foreground py-2 pl-9 pr-4 text-sm focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            />
        </div>

        <div>
            <select
                bind:value={selectedStatus}
                onchange={handleFilter}
                class="w-full rounded-lg border bg-background text-foreground py-2 pl-3 pr-8 text-sm focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="paused">Paused</option>
                <option value="failing">Failing</option>
                <option value="disabled">Disabled</option>
            </select>
        </div>

        <div>
            <select
                bind:value={selectedTrust}
                onchange={handleFilter}
                class="w-full rounded-lg border bg-background text-foreground py-2 pl-3 pr-8 text-sm focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Trust Levels</option>
                {#each trustLevels as tl}
                    <option value={tl.value}>{tl.label}</option>
                {/each}
            </select>
        </div>
    </div>

    <!-- Sources Table -->
    <div
        class="rounded-xl border bg-card text-card-foreground shadow-xs overflow-hidden"
    >
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-muted/50 border-b text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                >
                    <tr>
                        <th class="px-4 py-3">Source & Institution</th>
                        <th class="px-4 py-3">Method & Type</th>
                        <th class="px-4 py-3">Trust Level</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Discovered</th>
                        <th class="px-4 py-3">Last Crawled</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    {#if sources.data.length === 0}
                        <tr>
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <Globe
                                    class="mx-auto h-8 w-8 text-muted-foreground opacity-50 mb-2"
                                />
                                <div class="font-medium text-foreground text-sm">
                                    No crawling sources found
                                </div>
                                <div class="text-xs mt-1">
                                    Try clearing filters or adding a new source.
                                </div>
                            </td>
                        </tr>
                    {:else}
                        {#each sources.data as source (source.id)}
                            <tr class="hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="rounded-lg bg-primary/10 p-2 text-primary"
                                        >
                                            <Globe class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <Link
                                                href={`/admin/sources/${source.id}`}
                                                class="font-semibold text-foreground hover:text-primary transition-colors"
                                            >
                                                {source.name}
                                            </Link>
                                            <div
                                                class="flex items-center gap-2 text-xs text-muted-foreground"
                                            >
                                                <span>{source.domain}</span>
                                                <a
                                                    href={source.url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-muted-foreground hover:text-foreground transition-colors"
                                                >
                                                    <ExternalLink
                                                        class="h-3 w-3"
                                                    />
                                                </a>
                                            </div>
                                            {#if source.institution}
                                                <div class="mt-1">
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded bg-muted px-1.5 py-0.5 text-[11px] font-medium text-muted-foreground"
                                                    >
                                                        {source.institution
                                                            .short_name ??
                                                            source.institution
                                                            .name}
                                                    </span>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    <div class="text-foreground font-medium">
                                        {source.type.replace("_", " ")}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {source.crawl_method} (every {source.crawl_frequency_minutes}m)
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    {#if source.trust_level === "official_verified"}
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                                        >
                                            <ShieldCheck class="h-3.5 w-3.5" />
                                            Official
                                        </span>
                                    {:else}
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground"
                                        >
                                            {source.trust_level.replace(
                                                "_",
                                                " ",
                                            )}
                                        </span>
                                    {/if}
                                </td>

                                <td class="px-4 py-3.5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {source.status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : source.status === 'paused' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : source.status === 'failing' ? 'bg-destructive/10 text-destructive' : 'bg-muted text-muted-foreground'}"
                                    >
                                        {source.status}
                                    </span>
                                    {#if source.consecutive_failures > 0}
                                        <div
                                            class="text-[11px] text-destructive mt-0.5"
                                        >
                                            {source.consecutive_failures} fail(s)
                                        </div>
                                    {/if}
                                </td>

                                <td class="px-4 py-3.5 text-center">
                                    <div class="font-semibold text-foreground">
                                        {source.artifacts_count} artifacts
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {source.crawl_runs_count} runs
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 text-xs text-muted-foreground">
                                    {#if source.last_crawled_at}
                                        <div>
                                            {new Date(
                                                source.last_crawled_at,
                                            ).toLocaleString()}
                                        </div>
                                    {:else}
                                        <span class="opacity-50">Never</span>
                                    {/if}
                                </td>

                                <td class="px-4 py-3.5 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <button
                                            type="button"
                                            onclick={() =>
                                                triggerCrawl(source.id)}
                                            disabled={isTriggering ===
                                                source.id}
                                            title="Run Crawl Now"
                                            class="inline-flex items-center gap-1 rounded-md p-1.5 text-muted-foreground hover:bg-primary/10 hover:text-primary transition-colors disabled:opacity-50"
                                        >
                                            <Play
                                                class="h-4 w-4 {isTriggering ===
                                                source.id
                                                    ? 'animate-spin'
                                                    : ''}"
                                            />
                                        </button>

                                        <Link
                                            href={`/admin/sources/${source.id}`}
                                            title="View Details"
                                            class="rounded-md p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>

                                        <Link
                                            href={`/admin/sources/${source.id}/edit`}
                                            title="Edit"
                                            class="rounded-md p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>

                                        <button
                                            type="button"
                                            onclick={() => deleteSource(source)}
                                            title="Delete"
                                            class="rounded-md p-1.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive transition-colors"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    {/if}
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {#if sources.links.length > 3}
            <div
                class="flex items-center justify-between px-4 py-3 border-t text-xs text-muted-foreground"
            >
                <div>
                    Showing <span class="font-semibold text-foreground">{sources.data.length}</span> of <span class="font-semibold text-foreground">{sources.total}</span> sources
                </div>
                <div class="flex items-center gap-1">
                    {#each sources.links as link}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="px-2.5 py-1 rounded-md border text-xs font-medium transition-colors {link.active
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'bg-background hover:bg-muted text-foreground'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span class="px-2.5 py-1 rounded-md text-xs text-muted-foreground opacity-50 cursor-not-allowed">
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            </div>
        {/if}
    </div>
</div>
