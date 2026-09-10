<script lang="ts">
    import { Link, router, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import PublicNav from '@/components/PublicNav.svelte';
    import PublicFooter from '@/components/PublicFooter.svelte';
    import Search from '@lucide/svelte/icons/search';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Users from '@lucide/svelte/icons/users';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Calendar from '@lucide/svelte/icons/calendar';
    import ArrowRight from '@lucide/svelte/icons/arrow-right';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Clock from '@lucide/svelte/icons/clock';
    import Briefcase from '@lucide/svelte/icons/briefcase';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import AlertCircle from '@lucide/svelte/icons/alert-circle';
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
        slug: string;
        official_domain: string;
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

    interface Props {
        featuredPosts: PostItem[];
        stats: {
            total_vacancies: number;
            active_notices: number;
            total_institutions: number;
        };
        states: StateItem[];
        topInstitutions: InstitutionItem[];
    }

    let { featuredPosts, stats, states, topInstitutions }: Props = $props();

    let searchInput = $state('');
    let subEmail = $state('');
    let subState = $state('');
    let subInstitution = $state('');
    let subCategory = $state('');
    let isSubmittingSub = $state(false);
    let subSuccess = $state(false);

    function handleSearch(e: SubmitEvent) {
        e.preventDefault();
        if (searchInput.trim()) {
            router.get('/recruitment', { search: searchInput.trim() });
        } else {
            router.get('/recruitment');
        }
    }

    function submitSubscription(e: SubmitEvent) {
        e.preventDefault();
        if (!subEmail) return;
        isSubmittingSub = true;
        router.post(
            '/subscriptions',
            {
                email: subEmail,
                state_id: subState || null,
                institution_id: subInstitution || null,
                reservation_category: subCategory || null,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    subSuccess = true;
                    subEmail = '';
                },
                onFinish: () => {
                    isSubmittingSub = false;
                },
            }
        );
    }

    function formatDate(dateStr: string | null): string {
        if (!dateStr) return 'Refer Official Gazette';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function formatNumber(num: number): string {
        return new Intl.NumberFormat('en-IN').format(num);
    }
</script>

<AppHead title="Suchak (सूचक) - Official Indian Government Recruitment Discovery" />

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

    <!-- Hero Section -->
    <section class="relative border-b bg-linear-to-b from-card/80 to-background pt-12 pb-16 md:pt-20 md:pb-24 overflow-hidden">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <!-- Pill Badge -->
            <div class="inline-flex items-center gap-2 rounded-full border bg-background/80 px-3.5 py-1.5 text-xs font-semibold text-foreground shadow-2xs backdrop-blur-xs mb-6">
                <span class="flex size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Evidence-Grounded Recruitment Intelligence</span>
                <span class="text-muted-foreground">|</span>
                <span class="text-primary font-bold">100% Verified</span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-5xl md:text-6xl max-w-4xl mx-auto leading-tight sm:leading-tight md:leading-tight">
                Authoritative Indian Government <span class="text-primary">Recruitment Notices</span>
            </h1>

            <p class="mt-4 text-base sm:text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
                Directly parsed and grounded against official Central and State gazettes.
                Zero unverified rumors, accurate reservation quotas, and strict deadline alerts.
            </p>

            <!-- Search Form -->
            <form onsubmit={handleSearch} class="mt-8 max-w-2xl mx-auto">
                <div class="relative flex items-center shadow-md rounded-2xl border bg-card focus-within:ring-2 focus-within:ring-primary/50 transition-all">
                    <Search class="absolute left-4 size-5 text-muted-foreground pointer-events-none" />
                    <input
                        type="text"
                        bind:value={searchInput}
                        placeholder="Search exam, position (e.g. UPSC ESE, SSC CGL, Engineer, Assistant)..."
                        class="w-full rounded-2xl bg-transparent py-4 pl-12 pr-28 text-sm sm:text-base text-foreground placeholder:text-muted-foreground focus:outline-hidden"
                    />
                    <button
                        type="submit"
                        class="absolute right-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-xs hover:bg-primary/90 transition-all"
                    >
                        Search
                    </button>
                </div>
            </form>

            <!-- Quick Filter Tags -->
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs text-muted-foreground">
                <span class="font-medium">Trending:</span>
                <Link href="/recruitment?search=UPSC" class="rounded-full border bg-card px-2.5 py-1 text-foreground hover:border-primary/50 transition-colors">
                    UPSC Exams
                </Link>
                <Link href="/recruitment?search=SSC" class="rounded-full border bg-card px-2.5 py-1 text-foreground hover:border-primary/50 transition-colors">
                    Staff Selection (SSC)
                </Link>
                <Link href="/recruitment?search=Engineer" class="rounded-full border bg-card px-2.5 py-1 text-foreground hover:border-primary/50 transition-colors">
                    Engineering Cadres
                </Link>
                <Link href="/recruitment?deadline=active" class="rounded-full border bg-card px-2.5 py-1 text-foreground hover:border-primary/50 transition-colors">
                    Closing This Month
                </Link>
            </div>
        </div>
    </section>

    <!-- Metrics Strip -->
    <section class="border-b bg-card/40 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div class="rounded-xl border bg-card p-5 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <Users class="size-5" />
                        </div>
                        <div>
                            <span class="text-2xl font-bold tracking-tight text-foreground">{formatNumber(stats.total_vacancies)}</span>
                            <span class="block text-xs font-medium text-muted-foreground uppercase">Active Vacancies</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-5 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <Briefcase class="size-5" />
                        </div>
                        <div>
                            <span class="text-2xl font-bold tracking-tight text-foreground">{formatNumber(stats.active_notices)}</span>
                            <span class="block text-xs font-medium text-muted-foreground uppercase">Open Recruitment Notices</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-5 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            <Building2 class="size-5" />
                        </div>
                        <div>
                            <span class="text-2xl font-bold tracking-tight text-foreground">{formatNumber(stats.total_institutions)}</span>
                            <span class="block text-xs font-medium text-muted-foreground uppercase">Verified Commissions</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-5 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <ShieldCheck class="size-5" />
                        </div>
                        <div>
                            <span class="text-2xl font-bold tracking-tight text-foreground">100%</span>
                            <span class="block text-xs font-medium text-muted-foreground uppercase">Evidence Grounded</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured & Active Notifications Section -->
    <section class="py-12 md:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-b pb-4 mb-8">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                            <Sparkles class="size-3" />
                            Verified Gazette Notices
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-foreground mt-1">
                        Latest Recruitment Notifications
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">
                        Verified government circulars with audited vacancy numbers and official application links.
                    </p>
                </div>
                <div>
                    <Link
                        href="/recruitment"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline"
                    >
                        <span>View All {formatNumber(stats.active_notices)} Notices</span>
                        <ArrowRight class="size-4" />
                    </Link>
                </div>
            </div>

            {#if featuredPosts.length === 0}
                <div class="rounded-2xl border bg-card p-12 text-center shadow-xs">
                    <Briefcase class="mx-auto size-12 text-muted-foreground opacity-50" />
                    <h3 class="mt-4 text-lg font-semibold text-foreground">No Published Notices Yet</h3>
                    <p class="mt-2 text-sm text-muted-foreground max-w-md mx-auto">
                        Our ingestion crawler is constantly indexing official state gazettes and commissions. Check back shortly.
                    </p>
                </div>
            {:else}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {#each featuredPosts as post (post.id)}
                        {@const inst = post.notice.institution}
                        {@const notice = post.notice}
                        <div class="flex flex-col justify-between rounded-2xl border bg-card text-card-foreground p-6 shadow-xs hover:border-primary/50 hover:shadow-md transition-all">
                            <div>
                                <!-- Top Authority Bar -->
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
                                <h3 class="text-base font-bold text-foreground line-clamp-2 hover:text-primary transition-colors">
                                    <Link href={`/recruitment/${inst.slug}/${post.slug}`}>
                                        {post.title}
                                    </Link>
                                </h3>

                                {#if notice.reference_number}
                                    <div class="mt-1 text-xs text-muted-foreground font-mono">
                                        Advt: {notice.reference_number}
                                    </div>
                                {/if}

                                <p class="mt-2.5 text-xs text-muted-foreground line-clamp-3 leading-relaxed">
                                    {post.excerpt}
                                </p>
                            </div>

                            <!-- Bottom Metrics & Action -->
                            <div class="mt-6 border-t pt-4 space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1.5 text-muted-foreground">
                                        <Clock class="size-3.5" />
                                        <span>Deadline:</span>
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
                                    <span>View Notice & Apply</span>
                                    <ArrowRight class="size-3.5" />
                                </Link>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </section>

    <!-- Top Monitored Authorities Grid -->
    {#if topInstitutions.length > 0}
        <section class="border-t bg-card/30 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-8">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">
                        Official Recruiting Authorities Monitored
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-muted-foreground">
                        We actively monitor and ingest direct gazette circulars from these certified constitutional and statutory bodies.
                    </p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    {#each topInstitutions as inst (inst.id)}
                        <Link
                            href={`/recruitment?institution_id=${inst.id}`}
                            class="flex flex-col items-center justify-center rounded-xl border bg-card p-4 text-center shadow-xs hover:border-primary/50 hover:shadow-xs transition-all"
                        >
                            <div class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary font-bold text-sm mb-2">
                                {inst.short_name || inst.name.slice(0, 3)}
                            </div>
                            <span class="text-xs font-bold text-foreground line-clamp-1">{inst.short_name || inst.name}</span>
                            <span class="text-[10px] text-muted-foreground line-clamp-1 mt-0.5">{inst.official_domain}</span>
                        </Link>
                    {/each}
                </div>
            </div>
        </section>
    {/if}

    <!-- States & Territories Directory -->
    {#if states.length > 0}
        <section class="border-t py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold tracking-tight text-foreground">
                            Browse Opportunities by State & Union Territory
                        </h2>
                        <p class="text-xs text-muted-foreground mt-0.5">
                            Filter State Public Service Commission (PSC) and departmental gazettes.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    {#each states.slice(0, 16) as state (state.id)}
                        <Link
                            href={`/recruitment?state_id=${state.id}`}
                            class="inline-flex items-center gap-1.5 rounded-lg border bg-card px-3 py-1.5 text-xs font-medium text-foreground shadow-2xs hover:border-primary/50 hover:bg-muted/40 transition-colors"
                        >
                            <span>{state.name}</span>
                            <span class="text-[10px] text-muted-foreground font-mono">{state.iso_code}</span>
                        </Link>
                    {/each}
                    <Link
                        href="/recruitment"
                        class="inline-flex items-center rounded-lg border border-dashed bg-card/60 px-3 py-1.5 text-xs font-medium text-primary hover:bg-muted/40 transition-colors"
                    >
                        More States →
                    </Link>
                </div>
            </div>
        </section>
    {/if}

    <!-- Candidate Free Verified Alerts Section -->
    <section class="border-t py-14 bg-muted/20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border bg-card p-8 sm:p-12 shadow-xs space-y-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary mb-3">
                        <Bell class="size-3.5" />
                        <span>Real-Time Gazette Alerts</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground">
                        Never Miss a Government Recruitment Deadline
                    </h2>
                    <p class="text-sm text-muted-foreground mt-2 leading-relaxed">
                        Subscribe to verified email notifications matching your State, recruiting authority, and reservation category (UR, OBC-NCL, SC, ST, EWS). 100% spam-free, strictly authoritative circulars.
                    </p>
                </div>

                {#if subSuccess}
                    <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-6 flex items-center gap-3 text-emerald-700 dark:text-emerald-300">
                        <CheckCircle2 class="size-6 shrink-0 text-emerald-500" />
                        <div>
                            <div class="font-bold text-sm">Successfully Subscribed to Project Suchak!</div>
                            <div class="text-xs mt-0.5">You will receive verified recruitment alerts and corrigendum circulars as soon as they are gazetted.</div>
                        </div>
                    </div>
                {:else}
                    <form onsubmit={submitSubscription} class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Email -->
                            <div class="space-y-1.5 lg:col-span-1">
                                <label for="sub-email" class="text-xs font-medium text-foreground">Your Email Address *</label>
                                <input
                                    id="sub-email"
                                    type="email"
                                    bind:value={subEmail}
                                    placeholder="e.g. aspirant@nic.in"
                                    required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                                />
                            </div>

                            <!-- State -->
                            <div class="space-y-1.5">
                                <label for="sub-state" class="text-xs font-medium text-foreground">State / Territory</label>
                                <select
                                    id="sub-state"
                                    bind:value={subState}
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                                >
                                    <option value="">All India / Central</option>
                                    {#each states as state (state.id)}
                                        <option value={state.id}>{state.name}</option>
                                    {/each}
                                </select>
                            </div>

                            <!-- Authority -->
                            <div class="space-y-1.5">
                                <label for="sub-inst" class="text-xs font-medium text-foreground">Recruiting Authority</label>
                                <select
                                    id="sub-inst"
                                    bind:value={subInstitution}
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                                >
                                    <option value="">All Authorities</option>
                                    {#each topInstitutions as inst (inst.id)}
                                        <option value={inst.id}>{inst.short_name || inst.name}</option>
                                    {/each}
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="space-y-1.5">
                                <label for="sub-cat" class="text-xs font-medium text-foreground">Reservation Category</label>
                                <select
                                    id="sub-cat"
                                    bind:value={subCategory}
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                                >
                                    <option value="">All Categories</option>
                                    <option value="UR">Unreserved (UR)</option>
                                    <option value="OBC_NCL">OBC (Non-Creamy Layer)</option>
                                    <option value="SC">Scheduled Caste (SC)</option>
                                    <option value="ST">Scheduled Tribe (ST)</option>
                                    <option value="EWS">Economically Weaker Section (EWS)</option>
                                    <option value="PWBD">Persons with Disabilities (PwBD)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4 pt-1">
                            <span class="text-xs text-muted-foreground hidden sm:inline">
                                Unsubscribe anytime with 1 click. Zero promotional marketing.
                            </span>
                            <button
                                type="submit"
                                disabled={isSubmittingSub}
                                class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-primary-foreground shadow-xs hover:bg-primary/90 transition-all disabled:opacity-50"
                            >
                                <Bell class="size-3.5" />
                                <span>{isSubmittingSub ? 'Subscribing...' : 'Subscribe to Free Alerts'}</span>
                            </button>
                        </div>
                    </form>
                {/if}
            </div>
        </div>
    </section>

    <!-- Zero-Hallucination Architecture Banner -->
    <section class="border-t bg-card py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border bg-muted/20 p-8 sm:p-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-1.5 rounded-md bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                        <CheckCircle2 class="size-3.5" />
                        Radical Evidence Transparency
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">
                        Why Project Suchak (सूचक)?
                    </h2>
                    <p class="text-sm text-muted-foreground leading-relaxed">
                        Recruitment rumors cause widespread anxiety among millions of Indian aspirants. Suchak guarantees that every vacancy count, reservation category quota (UR, OBC-NCL, SC, ST, EWS), age limit, and application deadline is linked to an exact verbatim sentence in the original gazette PDF.
                    </p>
                </div>
                <div class="shrink-0">
                    <Link
                        href="/recruitment"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-xs hover:bg-primary/90 transition-all"
                    >
                        <span>Start Exploring Verified Notices</span>
                        <ArrowRight class="size-4" />
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <PublicFooter />
</div>
