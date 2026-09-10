<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Notices & Corrigenda', href: '/admin/notices' },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router } from '@inertiajs/svelte';
    import AlertTriangle from '@lucide/svelte/icons/alert-triangle';
    import Building2 from '@lucide/svelte/icons/building-2';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import Eye from '@lucide/svelte/icons/eye';
    import FileText from '@lucide/svelte/icons/file-text';
    import Filter from '@lucide/svelte/icons/filter';
    import History from '@lucide/svelte/icons/history';
    import Search from '@lucide/svelte/icons/search';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Sparkles from '@lucide/svelte/icons/sparkles';

    interface InstitutionSummary {
        id: string;
        name: string;
        short_name: string | null;
        slug: string;
    }

    interface PostSummary {
        id: string;
        notice_id: string;
        slug: string;
        published_at: string | null;
    }

    interface NoticeItem {
        id: string;
        institution_id: string;
        title: string;
        slug: string;
        reference_number: string | null;
        notice_type: string;
        status: string;
        confidence_score: number;
        total_vacancies: number;
        is_corrigendum: boolean;
        application_end_at: string | null;
        published_at: string | null;
        first_seen_at: string;
        metadata: {
            hallucination_warning?: boolean;
            grounding_failures?: Array<{ field: string }>;
        };
        institution: InstitutionSummary;
        post: PostSummary | null;
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

    interface Props {
        notices: PaginatedNotices;
        stats: {
            total_all: number;
            published: number;
            pending: number;
            corrigenda: number;
        };
        filters: {
            status: string | null;
            type: string | null;
            institution_id: string | null;
            search: string | null;
        };
        institutions: InstitutionSummary[];
    }

    let { notices, stats, filters, institutions }: Props = $props();

    let searchTerm = $state(filters.search ?? '');
    let selectedStatus = $state(filters.status ?? '');
    let selectedType = $state(filters.type ?? '');
    let selectedInstitution = $state(filters.institution_id ?? '');

    function handleFilter() {
        router.get(
            '/admin/notices',
            {
                search: searchTerm || undefined,
                status: selectedStatus || undefined,
                type: selectedType || undefined,
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
        searchTerm = '';
        selectedStatus = '';
        selectedType = '';
        selectedInstitution = '';
        handleFilter();
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
</script>

<AppHead title="Notices & Corrigenda Catalog" />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                    Recruitment Records
                </span>
                <span class="text-xs text-muted-foreground font-mono">
                    Total: {notices.total} notices
                </span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-foreground mt-1">
                Notices & Corrigenda Directory
            </h1>
            <p class="text-sm text-muted-foreground mt-1">
                Complete registry of extracted recruitment gazettes, amendment circulars (शुद्धिपत्र), and editorial posts.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <Link
                href="/admin/moderation"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all"
            >
                <Sparkles class="w-4 h-4" />
                Moderation Queue
                {#if stats.pending > 0}
                    <span class="ml-1 px-1.5 py-0.5 text-xs font-semibold rounded-full bg-primary-foreground text-primary">
                        {stats.pending}
                    </span>
                {/if}
            </Link>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Total Ingested</span>
                <span class="p-1.5 rounded-md bg-primary/10 text-primary">
                    <FileText class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground">{stats.total_all}</div>
            <div class="text-xs text-muted-foreground mt-1">All gazettes in database</div>
        </div>

        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Published & Live</span>
                <span class="p-1.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <CheckCircle2 class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground">{stats.published}</div>
            <div class="text-xs text-muted-foreground mt-1">Candidate-facing on portal</div>
        </div>

        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Pending Moderation</span>
                <span class="p-1.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <Filter class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground">{stats.pending}</div>
            <div class="text-xs text-muted-foreground mt-1">Awaiting human review</div>
        </div>

        <div class="p-4 rounded-xl border bg-card text-card-foreground shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-muted-foreground">Corrigenda (शुद्धिपत्र)</span>
                <span class="p-1.5 rounded-md bg-primary/10 text-primary">
                    <History class="w-4 h-4" />
                </span>
            </div>
            <div class="text-2xl font-bold mt-2 text-foreground">{stats.corrigenda}</div>
            <div class="text-xs text-muted-foreground mt-1">Patched amendments</div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 p-4 rounded-xl border bg-card/60 shadow-xs">
        <div class="relative lg:col-span-2">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <input
                type="text"
                bind:value={searchTerm}
                oninput={handleFilter}
                placeholder="Search title, Advt No..."
                class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            />
        </div>

        <div>
            <select
                bind:value={selectedStatus}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Statuses</option>
                <option value="published">Published</option>
                <option value="approved">Approved</option>
                <option value="pending_review">Pending Review</option>
                <option value="rejected">Rejected</option>
                <option value="archived">Archived</option>
            </select>
        </div>

        <div>
            <select
                bind:value={selectedType}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Notice Types</option>
                <option value="recruitment">Recruitment Notification</option>
                <option value="corrigendum">Corrigendum (शुद्धिपत्र)</option>
                <option value="extension">Date Extension</option>
                <option value="exam_date">Exam Date</option>
                <option value="result">Result / Merit List</option>
            </select>
        </div>

        <div>
            <select
                bind:value={selectedInstitution}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Authorities</option>
                {#each institutions as inst}
                    <option value={inst.id}>{inst.name}</option>
                {/each}
            </select>
        </div>
    </div>

    <!-- Notices Table -->
    <div class="rounded-xl border bg-card text-card-foreground shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-muted/50 border-b text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">Title & Authority</th>
                        <th class="px-4 py-3">Advt No / Type</th>
                        <th class="px-4 py-3 text-center">Vacancies</th>
                        <th class="px-4 py-3">Deadline</th>
                        <th class="px-4 py-3">Confidence</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60">
                    {#if notices.data.length === 0}
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                                <FileText class="mx-auto h-8 w-8 opacity-40 mb-2" />
                                <div class="font-medium text-foreground text-sm">No notices match criteria</div>
                                <div class="text-xs mt-1">Try resetting filters to explore all recruitment documents.</div>
                                <button onclick={resetFilters} class="mt-3 px-3 py-1.5 text-xs font-medium rounded-lg border hover:bg-muted/50 transition-colors">
                                    Reset Filters
                                </button>
                            </td>
                        </tr>
                    {:else}
                        {#each notices.data as notice (notice.id)}
                            <tr class="hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3.5 max-w-xs sm:max-w-md">
                                    <div class="flex items-start gap-2.5">
                                        <div class="p-2 rounded-lg bg-primary/10 text-primary shrink-0 mt-0.5">
                                            <FileText class="w-4 h-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-foreground hover:text-primary transition-colors line-clamp-1">
                                                <Link href={`/admin/notices/${notice.id}`}>
                                                    {notice.title}
                                                </Link>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-muted-foreground mt-0.5">
                                                <span class="inline-flex items-center gap-1">
                                                    <Building2 class="w-3 h-3" />
                                                    {notice.institution.short_name ?? notice.institution.name}
                                                </span>
                                                {#if notice.is_corrigendum}
                                                    <span class="px-1.5 py-0.2 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 font-semibold text-[10px]">
                                                        शुद्धिपत्र
                                                    </span>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    <div class="font-mono text-xs text-foreground">
                                        {notice.reference_number ?? 'Not Specified'}
                                    </div>
                                    <div class="text-xs text-muted-foreground capitalize">
                                        {notice.notice_type.replace('_', ' ')}
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 text-center">
                                    <span class="font-semibold text-foreground">
                                        {notice.total_vacancies > 0 ? notice.total_vacancies.toLocaleString('en-IN') : 'TBD'}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 text-xs text-muted-foreground">
                                    {formatDate(notice.application_end_at)}
                                </td>

                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-semibold {notice.confidence_score >= 0.94 ? 'text-emerald-600 dark:text-emerald-400' : notice.confidence_score >= 0.75 ? 'text-amber-500' : 'text-destructive'}">
                                            {formatConfidence(notice.confidence_score)}
                                        </span>
                                        {#if notice.metadata?.hallucination_warning}
                                            <span title="Ungrounded assertions detected">
                                                <AlertTriangle class="w-3.5 h-3.5 text-destructive" />
                                            </span>
                                        {:else if notice.confidence_score >= 0.94}
                                            <span title="Verbatim Grounded">
                                                <ShieldCheck class="w-3.5 h-3.5 text-emerald-500" />
                                            </span>
                                        {/if}
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    {#if notice.status === 'published'}
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            <CheckCircle2 class="w-3 h-3" /> Published
                                        </span>
                                    {:else if notice.status === 'pending_review'}
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            Pending Review
                                        </span>
                                    {:else if notice.status === 'approved'}
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                                            Approved
                                        </span>
                                    {:else}
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-muted text-muted-foreground border">
                                            {notice.status}
                                        </span>
                                    {/if}
                                </td>

                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            href={`/admin/notices/${notice.id}`}
                                            title="View Notice Details & History"
                                            class="p-1.5 rounded-md hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>

                                        {#if notice.status === 'pending_review'}
                                            <Link
                                                href={`/admin/moderation/${notice.id}`}
                                                title="Moderate in Split-Screen Workbench"
                                                class="p-1.5 rounded-md hover:bg-primary/10 text-primary transition-colors"
                                            >
                                                <Sparkles class="w-4 h-4" />
                                            </Link>
                                        {/if}

                                        {#if notice.status === 'published' && notice.post}
                                            <a
                                                href={`/recruitment/${notice.institution.slug}/${notice.post.slug}`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                title="View Public Post"
                                                class="p-1.5 rounded-md hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                                            >
                                                <ExternalLink class="w-4 h-4" />
                                            </a>
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                        {/each}
                    {/if}
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {#if notices.last_page > 1}
            <div class="flex items-center justify-between px-4 py-3 border-t bg-muted/20 text-xs">
                <div class="text-muted-foreground">
                    Page {notices.current_page} of {notices.last_page} ({notices.total} total)
                </div>

                <div class="flex items-center gap-1">
                    {#each notices.links as link, index (link.label + '-' + index)}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="px-2.5 py-1 rounded-md border {link.active ? 'bg-primary text-primary-foreground font-semibold' : 'hover:bg-muted text-foreground'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span class="px-2.5 py-1 text-muted-foreground opacity-40">
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            </div>
        {/if}
    </div>
</div>
