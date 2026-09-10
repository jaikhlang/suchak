<script lang="ts">
    import { Link, router, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import PublicNav from '@/components/PublicNav.svelte';
    import PublicFooter from '@/components/PublicFooter.svelte';
    import Search from '@lucide/svelte/icons/search';
    import Filter from '@lucide/svelte/icons/filter';
    import X from '@lucide/svelte/icons/x';
    import Clock from '@lucide/svelte/icons/clock';
    import Briefcase from '@lucide/svelte/icons/briefcase';
    import Building2 from '@lucide/svelte/icons/building-2';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import AlertCircle from '@lucide/svelte/icons/alert-circle';
    import RotateCcw from '@lucide/svelte/icons/rotate-ccw';
    import Bell from '@lucide/svelte/icons/bell';

    interface StateItem {
        id: string;
        name: string;
        iso_code: string;
    }

    interface InstitutionItem {
        id: string;
        name: string;
        short_name: string | null;
    }

    interface InstitutionTypeOption {
        value: string;
        label: string;
    }

    interface PostItem {
        id: string;
        title: string;
        slug: string;
        excerpt: string;
        published_at: string;
        notice: {
            id: string;
            title: string;
            reference_number: string | null;
            total_vacancies: number;
            application_end_at: string | null;
            institution: {
                name: string;
                short_name: string | null;
                slug: string;
                is_verified: boolean;
                state: StateItem | null;
            };
            positions?: {
                id: string;
                title: string;
                total_vacancies: number;
                pay_level: string | null;
            }[];
        };
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface PaginatedData {
        data: PostItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: PaginationLink[];
    }

    interface Props {
        posts: PaginatedData;
        filters: {
            search: string | null;
            state_id: string | null;
            institution_id: string | null;
            institution_type: string | null;
            category: string | null;
            deadline: string | null;
        };
        states: StateItem[];
        institutions: InstitutionItem[];
        institutionTypes: InstitutionTypeOption[];
    }

    let { posts, filters, states, institutions, institutionTypes }: Props = $props();

    let searchTerm = $state(filters.search ?? '');
    let selectedState = $state(filters.state_id ?? '');
    let selectedInstitution = $state(filters.institution_id ?? '');
    let selectedType = $state(filters.institution_type ?? '');
    let selectedCategory = $state(filters.category ?? '');
    let selectedDeadline = $state(filters.deadline ?? 'active');

    let alertEmail = $state('');
    let isSubscribingAlert = $state(false);
    let alertSubscribed = $state(false);

    function handleAlertSubscribe(e: SubmitEvent) {
        e.preventDefault();
        if (!alertEmail) return;
        isSubscribingAlert = true;
        router.post(
            '/subscriptions',
            {
                email: alertEmail,
                state_id: selectedState || null,
                institution_id: selectedInstitution || null,
                reservation_category: selectedCategory || null,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    alertSubscribed = true;
                    alertEmail = '';
                },
                onFinish: () => {
                    isSubscribingAlert = false;
                }
            }
        );
    }

    function applyFilters() {
        router.get(
            '/recruitment',
            {
                search: searchTerm || undefined,
                state_id: selectedState || undefined,
                institution_id: selectedInstitution || undefined,
                institution_type: selectedType || undefined,
                category: selectedCategory || undefined,
                deadline: selectedDeadline || undefined,
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
        selectedState = '';
        selectedInstitution = '';
        selectedType = '';
        selectedCategory = '';
        selectedDeadline = 'active';
        router.get('/recruitment', {}, { preserveState: false });
    }

    function formatDate(dateStr: string | null): string {
        if (!dateStr) return 'Refer Official Gazette';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function formatNumber(num: number): string {
        return new Intl.NumberFormat('en-IN').format(num);
    }

    const categories = [
        { value: 'ur', label: 'Unreserved (UR)' },
        { value: 'obc_ncl', label: 'OBC (Non-Creamy Layer)' },
        { value: 'sc', label: 'Scheduled Caste (SC)' },
        { value: 'st', label: 'Scheduled Tribe (ST)' },
        { value: 'ews', label: 'Economically Weaker Section (EWS)' },
        { value: 'pwbd', label: 'Persons with Benchmark Disabilities (PwBD)' },
    ];
</script>

<AppHead title="Recruitment Catalog - Suchak (सूचक)" />

<div class="min-h-screen flex flex-col bg-background text-foreground">
    <PublicNav />

    {#if page.props.flash?.success}
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4 w-full">
            <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-500" />
                <span>{page.props.flash.success}</span>
            </div>
        </div>
    {/if}
    {#if page.props.flash?.error}
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-4 w-full">
            <div class="rounded-lg border border-destructive/20 bg-destructive/10 p-4 text-sm text-destructive flex items-center gap-2">
                <AlertCircle class="h-4 w-4 shrink-0" />
                <span>{page.props.flash.error}</span>
            </div>
        </div>
    {/if}

    <main class="flex-1 py-8 md:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-5">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                            Verified Directory
                        </span>
                        <span class="text-xs text-muted-foreground">Total: {posts.total} Notices</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground mt-1">
                        Government Recruitment Catalog
                    </h1>
                    <p class="text-sm text-muted-foreground mt-1">
                        Filter by recruiting authority, state jurisdiction, quota category, and active deadline status.
                    </p>
                </div>

                {#if searchTerm || selectedState || selectedInstitution || selectedType || selectedCategory || selectedDeadline !== 'active'}
                    <div>
                        <button
                            type="button"
                            onclick={resetFilters}
                            class="inline-flex items-center gap-1.5 rounded-lg border bg-card px-3 py-1.5 text-xs font-medium text-muted-foreground hover:text-foreground shadow-2xs hover:bg-muted/40 transition-all"
                        >
                            <RotateCcw class="size-3.5" />
                            <span>Reset All Filters</span>
                        </button>
                    </div>
                {/if}
            </div>

            <!-- Multi-Facet Filter Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 p-4 rounded-xl border bg-card shadow-xs">
                <!-- Search -->
                <div class="relative lg:col-span-2">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <input
                        type="text"
                        bind:value={searchTerm}
                        oninput={applyFilters}
                        placeholder="Search exam, position, advt..."
                        class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    />
                </div>

                <!-- State Filter -->
                <div>
                    <select
                        bind:value={selectedState}
                        onchange={applyFilters}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        <option value="">All States / All India</option>
                        {#each states as state (state.id)}
                            <option value={state.id}>{state.name}</option>
                        {/each}
                    </select>
                </div>

                <!-- Authority Type Filter -->
                <div>
                    <select
                        bind:value={selectedType}
                        onchange={applyFilters}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        <option value="">All Authority Types</option>
                        {#each institutionTypes as it (it.value)}
                            <option value={it.value}>{it.label}</option>
                        {/each}
                    </select>
                </div>

                <!-- Quota Category Filter -->
                <div>
                    <select
                        bind:value={selectedCategory}
                        onchange={applyFilters}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        <option value="">All Reservation Quotas</option>
                        {#each categories as cat (cat.value)}
                            <option value={cat.value}>{cat.label}</option>
                        {/each}
                    </select>
                </div>

                <!-- Deadline Filter -->
                <div>
                    <select
                        bind:value={selectedDeadline}
                        onchange={applyFilters}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        <option value="active">Active Deadlines Only</option>
                        <option value="all">All (Active & Past)</option>
                        <option value="expired">Expired Applications</option>
                    </select>
                </div>
            </div>

            <!-- Active Filter Badges -->
            {#if searchTerm || selectedState || selectedInstitution || selectedType || selectedCategory}
                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                    <span class="text-muted-foreground font-medium">Active filters:</span>
                    {#if searchTerm}
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-primary font-medium">
                            Search: "{searchTerm}"
                            <button type="button" onclick={() => { searchTerm = ''; applyFilters(); }} class="hover:opacity-75">
                                <X class="size-3" />
                            </button>
                        </span>
                    {/if}
                    {#if selectedState}
                        {@const stateName = states.find(s => s.id === selectedState)?.name}
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-primary font-medium">
                            State: {stateName}
                            <button type="button" onclick={() => { selectedState = ''; applyFilters(); }} class="hover:opacity-75">
                                <X class="size-3" />
                            </button>
                        </span>
                    {/if}
                    {#if selectedType}
                        {@const typeLabel = institutionTypes.find(t => t.value === selectedType)?.label}
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-primary font-medium">
                            Type: {typeLabel}
                            <button type="button" onclick={() => { selectedType = ''; applyFilters(); }} class="hover:opacity-75">
                                <X class="size-3" />
                            </button>
                        </span>
                    {/if}
                    {#if selectedCategory}
                        {@const catLabel = categories.find(c => c.value === selectedCategory)?.label}
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-primary font-medium">
                            Quota: {catLabel}
                            <button type="button" onclick={() => { selectedCategory = ''; applyFilters(); }} class="hover:opacity-75">
                                <X class="size-3" />
                            </button>
                        </span>
                    {/if}
                </div>
            {/if}

            <!-- Results Grid -->
            {#if posts.data.length === 0}
                <div class="rounded-2xl border bg-card p-12 text-center shadow-xs">
                    <Briefcase class="mx-auto size-12 text-muted-foreground opacity-50" />
                    <h3 class="mt-4 text-lg font-semibold text-foreground">No Recruitment Notices Found</h3>
                    <p class="mt-2 text-sm text-muted-foreground max-w-md mx-auto">
                        No published notices matched the selected filter criteria. Try clearing search keywords or switching to "All (Active & Past)".
                    </p>
                    <div class="mt-6">
                        <button
                            type="button"
                            onclick={resetFilters}
                            class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground shadow-xs hover:bg-primary/90 transition-all"
                        >
                            <RotateCcw class="size-3.5" />
                            <span>Reset Filters</span>
                        </button>
                    </div>
                </div>
            {:else}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {#each posts.data as post (post.id)}
                        {@const inst = post.notice.institution}
                        {@const notice = post.notice}
                        <div class="flex flex-col justify-between rounded-2xl border bg-card text-card-foreground p-6 shadow-xs hover:border-primary/50 hover:shadow-md transition-all">
                            <div>
                                <!-- Authority Header -->
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex size-8 items-center justify-center rounded-lg bg-muted text-foreground font-bold text-xs">
                                            {inst.short_name ? inst.short_name.slice(0, 4) : inst.name.slice(0, 4)}
                                        </div>
                                        <div>
                                            <span class="text-xs font-semibold text-foreground line-clamp-1">
                                                {inst.short_name || inst.name}
                                            </span>
                                            {#if inst.state}
                                                <span class="text-[10px] text-muted-foreground block">
                                                    {inst.state.name}
                                                </span>
                                            {/if}
                                        </div>
                                    </div>
                                    {#if inst.is_verified}
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                            <CheckCircle2 class="size-3" />
                                            Verified
                                        </span>
                                    {/if}
                                </div>

                                <!-- Post Title -->
                                <h2 class="text-base font-bold text-foreground line-clamp-2 hover:text-primary transition-colors">
                                    <Link href={`/recruitment/${inst.slug}/${post.slug}`}>
                                        {post.title}
                                    </Link>
                                </h2>

                                {#if notice.reference_number}
                                    <div class="mt-1 text-xs text-muted-foreground font-mono">
                                        Advt: {notice.reference_number}
                                    </div>
                                {/if}

                                <p class="mt-2.5 text-xs text-muted-foreground line-clamp-3 leading-relaxed">
                                    {post.excerpt}
                                </p>
                            </div>

                            <!-- Bottom Details & Action -->
                            <div class="mt-6 border-t pt-4 space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1.5 text-muted-foreground">
                                        <Clock class="size-3.5" />
                                        <span>Application Deadline:</span>
                                    </div>
                                    <span class="font-semibold text-foreground">
                                        {formatDate(notice.application_end_at)}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-muted-foreground">Total Openings:</span>
                                    <span class="rounded bg-primary/10 px-2 py-0.5 font-bold text-primary">
                                        {formatNumber(notice.total_vacancies)} Posts
                                    </span>
                                </div>

                                <Link
                                    href={`/recruitment/${inst.slug}/${post.slug}`}
                                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-primary/10 py-2 text-xs font-semibold text-primary hover:bg-primary hover:text-primary-foreground transition-all"
                                >
                                    <span>Inspect Notice & Evidence</span>
                                    <ArrowRight class="size-3.5" />
                                </Link>
                            </div>
                        </div>
                    {/each}
                </div>

                <!-- Pagination -->
                {#if posts.links.length > 3}
                    <div class="flex items-center justify-center gap-1.5 pt-6">
                        {#each posts.links as link}
                            {#if link.url}
                                <Link
                                    href={link.url}
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {link.active ? 'bg-primary text-primary-foreground' : 'border bg-card text-foreground hover:bg-muted/40'}"
                                >
                                    {@html link.label}
                                </Link>
                            {:else}
                                <span class="px-2 py-1.5 text-xs text-muted-foreground opacity-50">
                                    {@html link.label}
                                </span>
                            {/if}
                        {/each}
                    </div>
                {/if}

                <!-- Candidate Filter Alert Subscription Bar -->
                <div class="p-6 rounded-2xl border bg-card shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 rounded-xl bg-primary/10 text-primary shrink-0">
                            <Bell class="size-4" />
                        </span>
                        <div>
                            <div class="text-sm font-bold text-foreground">Get Verified Alerts for These Filters</div>
                            <div class="text-xs text-muted-foreground mt-0.5">Receive instant email circulars when new vacancies are gazetted matching your criteria.</div>
                        </div>
                    </div>

                    {#if alertSubscribed}
                        <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">
                            <CheckCircle2 class="size-4 text-emerald-500" />
                            <span>Subscribed to Filter Alerts!</span>
                        </div>
                    {:else}
                        <form onsubmit={handleAlertSubscribe} class="flex items-center gap-2 w-full sm:w-auto">
                            <input
                                type="email"
                                bind:value={alertEmail}
                                placeholder="Enter email address..."
                                required
                                class="px-3.5 py-2 text-xs rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 w-full sm:w-64"
                            />
                            <button
                                type="submit"
                                disabled={isSubscribingAlert}
                                class="px-5 py-2 text-xs font-semibold rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all shrink-0 disabled:opacity-50"
                            >
                                {isSubscribingAlert ? '...' : 'Notify Me'}
                            </button>
                        </form>
                    {/if}
                </div>
            {/if}
        </div>
    </main>

    <PublicFooter />
</div>
