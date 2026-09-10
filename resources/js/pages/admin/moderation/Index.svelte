<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: "Dashboard", href: "/dashboard" },
            { title: "Moderation Queue", href: "/admin/moderation" },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Link, router } from "@inertiajs/svelte";
    import AlertTriangle from "@lucide/svelte/icons/alert-triangle";
    import ArrowRight from "@lucide/svelte/icons/arrow-right";
    import Building2 from "@lucide/svelte/icons/building-2";
    import Calendar from "@lucide/svelte/icons/calendar";
    import CheckCircle2 from "@lucide/svelte/icons/check-circle-2";
    import Filter from "@lucide/svelte/icons/filter";
    import Search from "@lucide/svelte/icons/search";
    import ShieldAlert from "@lucide/svelte/icons/shield-alert";
    import ShieldCheck from "@lucide/svelte/icons/shield-check";
    import Sparkles from "@lucide/svelte/icons/sparkles";
    import Users from "@lucide/svelte/icons/users";

    interface InstitutionItem {
        id: string;
        name: string;
        short_name: string | null;
    }

    interface NoticeItem {
        id: string;
        title: string;
        slug: string;
        reference_number: string | null;
        status: string;
        confidence_score: number;
        total_vacancies: number;
        application_end_at: string | null;
        is_corrigendum: boolean;
        metadata: {
            hallucination_warning?: boolean;
            grounding_failures?: Array<{ field: string }>;
            rejection_reason?: string;
        };
        institution: InstitutionItem;
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface PaginatedNotices {
        data: NoticeItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: PaginationLink[];
    }

    interface QueueStats {
        total_pending: number;
        high_risk: number;
        standard: number;
        auto_eligible: number;
        total_approved: number;
    }

    interface Props {
        notices: PaginatedNotices;
        stats: QueueStats;
        filters: {
            status: string | null;
            confidence_tier: string | null;
            institution_id: string | null;
            search: string | null;
        };
        institutions: InstitutionItem[];
    }

    let { notices, stats, filters, institutions }: Props = $props();

    let searchTerm = $state(filters.search ?? "");
    let selectedStatus = $state(filters.status ?? "pending_review");
    let selectedTier = $state(filters.confidence_tier ?? "");
    let selectedInstitution = $state(filters.institution_id ?? "");

    function handleFilter() {
        router.get(
            "/admin/moderation",
            {
                search: searchTerm || undefined,
                status: selectedStatus || undefined,
                confidence_tier: selectedTier || undefined,
                institution_id: selectedInstitution || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }

    function resetFilters() {
        searchTerm = "";
        selectedStatus = "pending_review";
        selectedTier = "";
        selectedInstitution = "";
        handleFilter();
    }

    function formatConfidence(score: number): string {
        return Math.round(score * 100) + "%";
    }

    function formatDate(dateStr: string | null): string {
        if (!dateStr) return "Not specified";
        return new Date(dateStr).toLocaleDateString("en-IN", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }
</script>

<AppHead title="Moderation Workbench" />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                    Verification Engine
                </span>
                <span class="text-xs text-muted-foreground">
                    Pending Queue: {stats.total_pending}
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-foreground mt-1">
                Moderation Workbench
            </h1>
            <p class="text-sm text-muted-foreground mt-1">
                Verify AI-extracted recruitment notices against verbatim documents, inspect reservation quotas, and approve with zero hallucinations.
            </p>
        </div>

        {#if notices.data.length > 0}
            <div>
                <Link
                    href={`/admin/moderation/${notices.data[0].id}`}
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all"
                >
                    <Sparkles class="w-4 h-4" />
                    Speed-Run First Notice
                </Link>
            </div>
        {/if}
    </div>

    <!-- Queue Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- High Risk -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">High-Risk / Ungrounded</span>
                <span class="p-1.5 rounded-md bg-destructive/10 text-destructive">
                    <AlertTriangle class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground">{stats.high_risk}</div>
            <div class="text-xs text-muted-foreground mt-1">Score &lt; 75% or snippet missing</div>
        </div>

        <!-- Standard Queue -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Standard Review</span>
                <span class="p-1.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <Filter class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground">{stats.standard}</div>
            <div class="text-xs text-muted-foreground mt-1">Score 75% – 93%</div>
        </div>

        <!-- Auto-Eligible -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Auto-Publication Ready</span>
                <span class="p-1.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <ShieldCheck class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground">{stats.auto_eligible}</div>
            <div class="text-xs text-muted-foreground mt-1">Score &ge; 94% verified</div>
        </div>

        <!-- Total Approved -->
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Total Approved & Live</span>
                <span class="p-1.5 rounded-md bg-primary/10 text-primary">
                    <CheckCircle2 class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground">{stats.total_approved}</div>
            <div class="text-xs text-muted-foreground mt-1">Published to public catalog</div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 p-4 rounded-xl border bg-card/60 shadow-xs">
        <!-- Search -->
        <div class="relative lg:col-span-2">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <input
                type="text"
                bind:value={searchTerm}
                oninput={handleFilter}
                placeholder="Search title or Advt No..."
                class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            />
        </div>

        <!-- Confidence Tier Filter -->
        <div>
            <select
                bind:value={selectedTier}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Confidence Levels</option>
                <option value="high_risk">High Risk (&lt; 75%)</option>
                <option value="standard">Standard (75% – 93%)</option>
                <option value="auto_eligible">Auto-Eligible (&ge; 94%)</option>
            </select>
        </div>

        <!-- Status Filter -->
        <div>
            <select
                bind:value={selectedStatus}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="pending_review">Pending Review</option>
                <option value="extracted">Extracted (New)</option>
                <option value="approved">Approved</option>
                <option value="published">Published</option>
                <option value="rejected">Rejected</option>
                <option value="all">All Statuses</option>
            </select>
        </div>

        <!-- Institution Filter -->
        <div class="flex items-center gap-2">
            <select
                bind:value={selectedInstitution}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all truncate"
            >
                <option value="">All Authorities</option>
                {#each institutions as inst, i (`${inst.id}-${i}`)}
                    <option value={inst.id}>{inst.short_name ? `${inst.short_name} - ` : ""}{inst.name}</option>
                {/each}
            </select>

            {#if searchTerm || selectedTier || selectedStatus !== "pending_review" || selectedInstitution}
                <button
                    type="button"
                    onclick={resetFilters}
                    class="px-2.5 py-2 text-xs font-medium rounded-lg border bg-background hover:bg-muted/50 text-muted-foreground transition-all shrink-0"
                    title="Reset filters"
                >
                    Reset
                </button>
            {/if}
        </div>
    </div>

    <!-- Notice List -->
    {#if notices.data.length === 0}
        <div class="p-12 text-center border rounded-xl bg-card">
            <ShieldCheck class="w-12 h-12 text-muted-foreground/50 mx-auto mb-3" />
            <h3 class="text-base font-semibold text-foreground">Queue is clear!</h3>
            <p class="text-sm text-muted-foreground mt-1 max-w-md mx-auto">
                No recruitment notices currently match the active filter criteria. All discovered circulars have been verified.
            </p>
        </div>
    {:else}
        <div class="flex flex-col gap-3">
            {#each notices.data as notice, index (`${notice.id}-${index}`)}
                <div class="p-5 rounded-xl border bg-card text-card-foreground shadow-xs hover:border-primary/40 transition-all flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex flex-col gap-2 flex-1">
                        <!-- Badges Row -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Institution -->
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium bg-muted text-muted-foreground">
                                <Building2 class="w-3 h-3" />
                                {notice.institution.short_name || notice.institution.name}
                            </span>

                            <!-- Reference Number -->
                            {#if notice.reference_number}
                                <span class="text-xs font-mono px-2 py-0.5 rounded-md border text-muted-foreground">
                                    {notice.reference_number}
                                </span>
                            {/if}

                            <!-- Corrigendum Badge -->
                            {#if notice.is_corrigendum}
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                                    Corrigendum (शुद्धिपत्र)
                                </span>
                            {/if}

                            <!-- Hallucination Warning -->
                            {#if notice.metadata?.hallucination_warning}
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-md bg-destructive/10 text-destructive">
                                    <AlertTriangle class="w-3 h-3" />
                                    Ungrounded Assertions
                                </span>
                            {/if}

                            <!-- Confidence Score Badge -->
                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-md {notice.confidence_score >= 0.94 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (notice.confidence_score >= 0.75 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-destructive/10 text-destructive')}">
                                {formatConfidence(notice.confidence_score)} Confidence
                            </span>
                        </div>

                        <!-- Title -->
                        <h2 class="text-base md:text-lg font-semibold text-foreground tracking-tight">
                            {notice.title}
                        </h2>

                        <!-- Metrics Row -->
                        <div class="flex flex-wrap items-center gap-4 text-xs text-muted-foreground mt-1">
                            <span class="inline-flex items-center gap-1">
                                <Users class="w-3.5 h-3.5" />
                                Total Vacancies: <strong class="text-foreground">{notice.total_vacancies}</strong>
                            </span>

                            <span class="inline-flex items-center gap-1">
                                <Calendar class="w-3.5 h-3.5" />
                                Deadline: <strong class="text-foreground">{formatDate(notice.application_end_at)}</strong>
                            </span>

                            <span class="capitalize">
                                Status: <strong class="text-foreground">{notice.status.replace('_', ' ')}</strong>
                            </span>
                        </div>
                    </div>

                    <!-- Right Action Button -->
                    <div class="flex items-center gap-2 lg:self-center shrink-0">
                        <Link
                            href={`/admin/moderation/${notice.id}`}
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all w-full sm:w-auto"
                        >
                            Open Workbench
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            {/each}
        </div>

        <!-- Pagination -->
        {#if notices.last_page > 1}
            <div class="flex items-center justify-between border-t pt-4">
                <span class="text-xs text-muted-foreground">
                    Showing {notices.data.length} of {notices.total} notices
                </span>
                <div class="flex items-center gap-1">
                    {#each notices.links as link, i (`${link.label}-${i}`)}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all {link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-background text-foreground hover:bg-muted/50'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span class="px-3 py-1.5 text-xs font-medium text-muted-foreground/50">
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            </div>
        {/if}
    {/if}
</div>
