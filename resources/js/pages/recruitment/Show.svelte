<script lang="ts">
    import { Link, router, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import PublicNav from '@/components/PublicNav.svelte';
    import PublicFooter from '@/components/PublicFooter.svelte';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Calendar from '@lucide/svelte/icons/calendar';
    import Clock from '@lucide/svelte/icons/clock';
    import Users from '@lucide/svelte/icons/users';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import FileText from '@lucide/svelte/icons/file-text';
    import AlertTriangle from '@lucide/svelte/icons/alert-triangle';
    import AlertCircle from '@lucide/svelte/icons/alert-circle';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import History from '@lucide/svelte/icons/history';
    import Landmark from '@lucide/svelte/icons/landmark';
    import CreditCard from '@lucide/svelte/icons/credit-card';
    import GraduationCap from '@lucide/svelte/icons/graduation-cap';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Copy from '@lucide/svelte/icons/copy';
    import Check from '@lucide/svelte/icons/check';
    import Send from '@lucide/svelte/icons/send';
    import Bell from '@lucide/svelte/icons/bell';

    interface ReservationItem {
        category: string;
        quota_type: string;
        vacancies: number;
    }

    interface PositionItem {
        id: string;
        title: string;
        post_code: string | null;
        department: string | null;
        total_vacancies: number;
        employment_type: string;
        pay_level: string | null;
        pay_scale_text: string | null;
        reservations?: ReservationItem[];
    }

    interface EvidenceItem {
        id: string;
        field_name: string;
        extracted_value: string;
        page_number: number | null;
        verbatim_text_fragment: string;
        confidence_score: number;
    }

    interface RevisionItem {
        id: string;
        version_number: number;
        change_reason: string | null;
        change_type?: string;
        diff_data?: Record<string, { old: unknown; new: unknown }>;
        created_at: string;
        triggeringArtifact?: {
            title?: string;
            url?: string;
        } | null;
    }

    interface Props {
        post: {
            id: string;
            title: string;
            slug: string;
            excerpt: string;
            content_html: string;
            canonical_url: string;
            published_at: string;
        };
        notice: {
            id: string;
            title: string;
            reference_number: string | null;
            summary: string | null;
            total_vacancies: number;
            is_corrigendum: boolean;
            application_start_at: string | null;
            application_end_at: string | null;
            fee_payment_end_at: string | null;
            correction_window_end_at: string | null;
            tentative_exam_date_text: string | null;
            canonical_source_url: string;
            institution: {
                id: string;
                name: string;
                short_name: string | null;
                slug: string;
                official_domain: string;
                website_url: string;
                is_verified: boolean;
                state: {
                    id?: string;
                    name: string;
                    iso_code: string;
                } | null;
            };
            positions?: PositionItem[];
            applicationDetail?: {
                application_mode: string;
                apply_url: string | null;
                official_notification_pdf_url: string | null;
                general_fee: number | null;
                reserved_fee: number | null;
                female_fee: number | null;
                is_exempted_for_sc_st: boolean;
                is_exempted_for_female: boolean;
                is_exempted_for_pwbd: boolean;
            } | null;
            eligibilityRule?: {
                minimum_age: number | null;
                maximum_age: number | null;
                age_calculated_as_on: string | null;
                qualification_summary: string | null;
                nationality_text: string | null;
            } | null;
            evidence?: EvidenceItem[];
            revisions?: RevisionItem[];
            sourceArtifact?: {
                url: string;
                title: string;
                file_size_bytes: number;
            } | null;
        };
        schemaJson: Record<string, unknown>;
    }

    let { post, notice, schemaJson }: Props = $props();

    let evidenceExpanded = $state(false);
    let subscribeEmail = $state('');
    let isSubscribing = $state(false);
    let subscriptionSuccess = $state(false);
    let isCopied = $state(false);

    function formatDate(dateStr: string | null): string {
        if (!dateStr) return 'Refer Official Gazette';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }

    function formatNumber(num: number): string {
        return new Intl.NumberFormat('en-IN').format(num);
    }

    const inst = $derived(notice.institution);
    const appDetail = $derived(notice.applicationDetail);
    const eligibility = $derived(notice.eligibilityRule);

    const isExpired = $derived(
        notice.application_end_at ? new Date(notice.application_end_at) < new Date() : false
    );

    function copyLink() {
        if (typeof navigator !== 'undefined' && navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
            isCopied = true;
            setTimeout(() => {
                isCopied = false;
            }, 2500);
        }
    }

    function handleSubscribe(e: SubmitEvent) {
        e.preventDefault();
        if (!subscribeEmail) return;
        isSubscribing = true;
        router.post(
            '/subscriptions',
            {
                email: subscribeEmail,
                institution_id: inst.id,
                state_id: inst.state?.id || null,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    subscriptionSuccess = true;
                    subscribeEmail = '';
                },
                onFinish: () => {
                    isSubscribing = false;
                }
            }
        );
    }
</script>

<AppHead title={`${post.title} - ${inst.short_name || inst.name} | Suchak (सूचक)`} />

<!-- Google Jobs Schema.org JSON-LD structured data -->
<svelte:head>
    {@html `<script type="application/ld+json">${JSON.stringify(schemaJson)}</script>`}
</svelte:head>

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
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-muted-foreground">
                <Link href="/" class="hover:text-foreground transition-colors">Home</Link>
                <span>/</span>
                <Link href="/recruitment" class="hover:text-foreground transition-colors">Recruitment</Link>
                <span>/</span>
                <Link href={`/recruitment?institution_id=${inst.id}`} class="hover:text-foreground transition-colors">
                    {inst.short_name || inst.name}
                </Link>
                <span>/</span>
                <span class="text-foreground font-medium truncate max-w-xs">{post.title}</span>
            </nav>

            <!-- Corrigendum Amendment Alert Banner (If applicable) -->
            {#if notice.is_corrigendum || (notice.revisions && notice.revisions.length > 0)}
                <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-5 text-amber-900 dark:text-amber-200 shadow-xs">
                    <div class="flex items-start gap-3">
                        <AlertTriangle class="size-5 shrink-0 text-amber-600 dark:text-amber-400 mt-0.5" />
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm">Official Corrigendum / Amendment Notice ("शुद्धिपत्र")</span>
                                <span class="rounded bg-amber-500/20 px-2 py-0.5 text-[10px] font-bold uppercase">Amended</span>
                            </div>
                            <p class="text-xs leading-relaxed text-amber-800 dark:text-amber-300">
                                This recruitment notification has been officially updated via an authorized corrigendum circular.
                                Key dates, vacancy totals, or eligibility rules reflect the latest gazette revision.
                            </p>
                            {#if notice.revisions && notice.revisions.length > 0}
                                <div class="mt-2 text-xs space-y-1">
                                    {#each notice.revisions as rev}
                                        <div class="flex items-center gap-2 text-[11px] font-medium">
                                            <History class="size-3" />
                                            <span>Revision {rev.version_number} ({formatDate(rev.created_at)}): {rev.change_reason || 'Fields updated'}</span>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}

            <!-- Main Notice Header Card -->
            <div class="rounded-3xl border bg-card p-6 sm:p-8 shadow-xs space-y-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                    <div class="space-y-3 max-w-3xl">
                        <!-- Authority Badge & Status -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <div class="flex items-center gap-2 rounded-lg bg-muted px-2.5 py-1 text-xs font-semibold text-foreground">
                                <Landmark class="size-3.5 text-primary" />
                                <span>{inst.name}</span>
                            </div>

                            {#if inst.is_verified}
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <ShieldCheck class="size-3.5" />
                                    Official Authority
                                </span>
                            {/if}

                            {#if isExpired}
                                <span class="rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-600 dark:text-red-400">
                                    Applications Closed
                                </span>
                            {:else}
                                <span class="rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    Active Application Window
                                </span>
                            {/if}
                        </div>

                        <!-- Title -->
                        <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-foreground leading-tight">
                            {post.title}
                        </h1>

                        <!-- Advt Ref & Canonical -->
                        <div class="flex flex-wrap items-center gap-4 text-xs text-muted-foreground">
                            {#if notice.reference_number}
                                <div>
                                    <span class="font-medium">Gazette / Advt Ref:</span>
                                    <span class="font-mono font-bold text-foreground ml-1">{notice.reference_number}</span>
                                </div>
                            {/if}
                            <div>
                                <span class="font-medium">Published:</span>
                                <span class="text-foreground ml-1">{formatDate(post.published_at)}</span>
                            </div>
                            {#if inst.official_domain}
                                <a
                                    href={inst.website_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-primary hover:underline"
                                >
                                    <span>{inst.official_domain}</span>
                                    <ExternalLink class="size-3" />
                                </a>
                            {/if}
                        </div>
                    </div>

                    <!-- Direct Action Buttons -->
                    <div class="flex flex-col sm:flex-row md:flex-col gap-3 shrink-0">
                        {#if appDetail?.apply_url || notice.canonical_source_url}
                            <a
                                href={appDetail?.apply_url || notice.canonical_source_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-primary-foreground shadow-xs hover:bg-primary/90 transition-all text-center"
                            >
                                <span>Apply on Official Portal</span>
                                <ExternalLink class="size-4" />
                            </a>
                        {/if}

                        {#if notice.sourceArtifact?.url || notice.canonical_source_url}
                            <a
                                href={notice.sourceArtifact?.url || notice.canonical_source_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border bg-background px-5 py-2.5 text-xs font-semibold text-foreground shadow-2xs hover:bg-muted/40 transition-all text-center"
                            >
                                <FileText class="size-3.5" />
                                <span>Download Official PDF Gazette</span>
                            </a>
                        {/if}
                    </div>
                </div>

                <!-- Key Metrics Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 border-t pt-6">
                    <div class="p-3 rounded-xl bg-muted/30">
                        <span class="text-xs text-muted-foreground block">Total Openings</span>
                        <span class="text-lg sm:text-xl font-bold text-primary">
                            {formatNumber(notice.total_vacancies)} Posts
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-muted/30">
                        <span class="text-xs text-muted-foreground block">Application Deadline</span>
                        <span class="text-lg sm:text-xl font-bold text-foreground">
                            {formatDate(notice.application_end_at)}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-muted/30">
                        <span class="text-xs text-muted-foreground block">Application Mode</span>
                        <span class="text-lg sm:text-xl font-bold text-foreground">
                            {appDetail?.application_mode ? appDetail.application_mode.toUpperCase() : 'ONLINE'}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-muted/30">
                        <span class="text-xs text-muted-foreground block">Application Fee (General)</span>
                        <span class="text-lg sm:text-xl font-bold text-foreground">
                            {appDetail?.general_fee ? `₹${appDetail.general_fee}` : 'Free / Nil'}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detailed Content Layout: 2 Columns -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left 2 Cols: Main Vacancy & Quota Tables -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Official Corrigendum & Amendment History Alert -->
                    {#if notice.revisions && notice.revisions.length > 0}
                        <section class="rounded-2xl border border-amber-500/30 bg-amber-500/5 p-6 shadow-xs space-y-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-300">
                                        <History class="size-3.5" />
                                        <span>Official Corrigendum / Amendment Issued ("शुद्धिपत्र")</span>
                                    </div>
                                    <h2 class="text-base font-bold text-foreground">
                                        This recruitment notice has been officially modified by {inst.short_name || inst.name}
                                    </h2>
                                    <p class="text-xs text-muted-foreground">
                                        Key dates, vacancies, or requirements were amended via subsequent official circulars.
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-3 pt-1">
                                {#each notice.revisions as rev (rev.id)}
                                    <div class="rounded-xl border bg-card p-4 space-y-3 text-xs shadow-2xs">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b pb-2">
                                            <div class="font-bold text-foreground flex items-center gap-2">
                                                <span class="rounded bg-primary/10 text-primary px-2 py-0.5 font-mono text-[11px]">
                                                    Amendment #{rev.version_number}
                                                </span>
                                                <span>{rev.change_reason || 'Official Gazette Amendment'}</span>
                                            </div>
                                            <span class="text-muted-foreground font-medium">{formatDate(rev.created_at)}</span>
                                        </div>

                                        {#if rev.diff_data && Object.keys(rev.diff_data).length > 0}
                                            <div class="space-y-1.5">
                                                <span class="font-semibold text-muted-foreground block text-[11px]">
                                                    Audited Parameter Modifications:
                                                </span>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 font-mono text-[11px]">
                                                    {#each Object.entries(rev.diff_data) as [field, values]}
                                                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                                                            <span class="font-bold text-foreground uppercase block text-[10px] tracking-wider">
                                                                {field.replace(/_/g, ' ')}
                                                            </span>
                                                            <div class="flex items-center gap-2 mt-1">
                                                                <span class="line-through text-destructive">
                                                                    {String(values.old ?? 'None')}
                                                                </span>
                                                                <span class="text-muted-foreground font-sans">→</span>
                                                                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                                                    {String(values.new ?? 'None')}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    {/each}
                                                </div>
                                            </div>
                                        {/if}

                                        {#if rev.triggeringArtifact?.url}
                                            <div class="pt-1">
                                                <a
                                                    href={rev.triggeringArtifact.url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline"
                                                >
                                                    <FileText class="size-3.5" />
                                                    <span>Download Official Amendment Gazette</span>
                                                    <ExternalLink class="size-3" />
                                                </a>
                                            </div>
                                        {/if}
                                    </div>
                                {/each}
                            </div>
                        </section>
                    {/if}

                    <!-- Cadres & Positions Breakdown -->
                    {#if notice.positions && notice.positions.length > 0}
                        <section class="rounded-2xl border bg-card p-6 shadow-xs space-y-4">
                            <h2 class="text-lg font-bold tracking-tight text-foreground flex items-center gap-2">
                                <Users class="size-5 text-primary" />
                                <span>Position Cadres & Pay Scales</span>
                            </h2>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm border-collapse">
                                    <thead>
                                        <tr class="border-b bg-muted/40 text-xs font-semibold text-muted-foreground">
                                            <th class="p-3">Cadre / Post Title</th>
                                            <th class="p-3">Department</th>
                                            <th class="p-3 text-center">Vacancies</th>
                                            <th class="p-3">Pay Scale / Level</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y text-foreground">
                                        {#each notice.positions as pos (pos.id)}
                                            <tr>
                                                <td class="p-3 font-semibold">
                                                    <div>{pos.title}</div>
                                                    {#if pos.post_code}
                                                        <span class="text-[10px] font-mono text-muted-foreground">Code: {pos.post_code}</span>
                                                    {/if}
                                                </td>
                                                <td class="p-3 text-muted-foreground">{pos.department || 'Central Cadre'}</td>
                                                <td class="p-3 text-center font-bold text-primary">{pos.total_vacancies}</td>
                                                <td class="p-3 text-xs font-mono">{pos.pay_scale_text || pos.pay_level || 'As per Govt Norms'}</td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Indian Reservation Quota Matrix -->
                        <section class="rounded-2xl border bg-card p-6 shadow-xs space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold tracking-tight text-foreground flex items-center gap-2">
                                    <ShieldCheck class="size-5 text-emerald-500" />
                                    <span>Reservation Quota Breakdown Matrix</span>
                                </h2>
                                <span class="text-xs text-muted-foreground">Constitutional Quotas (UR/OBC/SC/ST/EWS)</span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm border-collapse">
                                    <thead>
                                        <tr class="border-b bg-muted/40 text-xs font-semibold text-muted-foreground">
                                            <th class="p-3">Post</th>
                                            <th class="p-3 text-center">UR</th>
                                            <th class="p-3 text-center">OBC</th>
                                            <th class="p-3 text-center">SC</th>
                                            <th class="p-3 text-center">ST</th>
                                            <th class="p-3 text-center">EWS</th>
                                            <th class="p-3 text-center font-bold">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y text-foreground">
                                        {#each notice.positions as pos (pos.id)}
                                            {@const res = pos.reservations || []}
                                            {@const ur = res.find(r => r.category === 'ur')?.vacancies ?? '-'}
                                            {@const obc = res.find(r => r.category === 'obc_ncl' || r.category === 'obc')?.vacancies ?? '-'}
                                            {@const sc = res.find(r => r.category === 'sc')?.vacancies ?? '-'}
                                            {@const st = res.find(r => r.category === 'st')?.vacancies ?? '-'}
                                            {@const ews = res.find(r => r.category === 'ews')?.vacancies ?? '-'}
                                            <tr>
                                                <td class="p-3 font-medium text-xs sm:text-sm">{pos.title}</td>
                                                <td class="p-3 text-center text-xs font-mono">{ur}</td>
                                                <td class="p-3 text-center text-xs font-mono">{obc}</td>
                                                <td class="p-3 text-center text-xs font-mono">{sc}</td>
                                                <td class="p-3 text-center text-xs font-mono">{st}</td>
                                                <td class="p-3 text-center text-xs font-mono">{ews}</td>
                                                <td class="p-3 text-center font-bold text-xs font-mono text-primary">{pos.total_vacancies}</td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    {/if}

                    <!-- Editorial HTML / Gazette Summary -->
                    <section class="rounded-2xl border bg-card p-6 shadow-xs space-y-4">
                        <h2 class="text-lg font-bold tracking-tight text-foreground">
                            Official Circular Summary & Guidelines
                        </h2>
                        <div class="prose dark:prose-invert max-w-none text-sm text-muted-foreground leading-relaxed">
                            {@html post.content_html}
                        </div>
                    </section>

                    <!-- Radical Evidence Grounding Accordion -->
                    {#if notice.evidence && notice.evidence.length > 0}
                        <section class="rounded-2xl border bg-card p-6 shadow-xs space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                                        <CheckCircle2 class="size-4 text-emerald-500" />
                                        <span>Audited Gazette Evidence Grounding</span>
                                    </h2>
                                    <p class="text-xs text-muted-foreground mt-0.5">
                                        Verbatim text fragments from the official PDF gazette verifying each extracted field.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onclick={() => (evidenceExpanded = !evidenceExpanded)}
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline"
                                >
                                    <span>{evidenceExpanded ? 'Collapse Evidence' : 'Inspect Evidence'}</span>
                                    <ChevronDown class={`size-3.5 transition-transform ${evidenceExpanded ? 'rotate-180' : ''}`} />
                                </button>
                            </div>

                            {#if evidenceExpanded}
                                <div class="space-y-3 pt-2">
                                    {#each notice.evidence as ev (ev.id)}
                                        <div class="rounded-xl border bg-muted/20 p-3.5 space-y-1.5 text-xs">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-foreground uppercase tracking-wide">
                                                    {ev.field_name.replace(/_/g, ' ')}
                                                </span>
                                                <div class="flex items-center gap-2">
                                                    {#if ev.page_number}
                                                        <span class="rounded bg-muted px-2 py-0.5 font-mono text-[10px] text-muted-foreground">
                                                            Gazette Page {ev.page_number}
                                                        </span>
                                                    {/if}
                                                    <span class="rounded bg-emerald-500/10 px-2 py-0.5 font-semibold text-[10px] text-emerald-600 dark:text-emerald-400">
                                                        {(ev.confidence_score * 100).toFixed(0)}% Confidence
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="font-mono text-primary font-bold">
                                                Extracted Value: {ev.extracted_value}
                                            </div>
                                            <div class="italic text-muted-foreground bg-card/60 p-2 rounded-lg border border-dashed">
                                                "{ev.verbatim_text_fragment}"
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </section>
                    {/if}
                </div>

                <!-- Right 1 Col: Key Dates, Eligibility & Fee Card -->
                <div class="space-y-6">
                    <!-- Important Dates Card -->
                    <div class="rounded-2xl border bg-card p-6 shadow-xs space-y-4">
                        <h3 class="text-sm font-bold tracking-wide text-foreground uppercase flex items-center gap-2">
                            <Calendar class="size-4 text-primary" />
                            <span>Important Dates</span>
                        </h3>
                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between pb-2 border-b">
                                <span class="text-muted-foreground">Applications Open:</span>
                                <span class="font-semibold text-foreground">{formatDate(notice.application_start_at)}</span>
                            </div>
                            <div class="flex items-center justify-between pb-2 border-b">
                                <span class="text-muted-foreground">Application Deadline:</span>
                                <span class="font-bold text-primary">{formatDate(notice.application_end_at)}</span>
                            </div>
                            {#if notice.fee_payment_end_at}
                                <div class="flex items-center justify-between pb-2 border-b">
                                    <span class="text-muted-foreground">Fee Payment Last Date:</span>
                                    <span class="font-semibold text-foreground">{formatDate(notice.fee_payment_end_at)}</span>
                                </div>
                            {/if}
                            {#if notice.correction_window_end_at}
                                <div class="flex items-center justify-between pb-2 border-b">
                                    <span class="text-muted-foreground">Correction Window Closes:</span>
                                    <span class="font-semibold text-foreground">{formatDate(notice.correction_window_end_at)}</span>
                                </div>
                            {/if}
                            {#if notice.tentative_exam_date_text}
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-muted-foreground">Tentative Exam:</span>
                                    <span class="font-semibold text-foreground">{notice.tentative_exam_date_text}</span>
                                </div>
                            {/if}
                        </div>
                    </div>

                    <!-- Eligibility Criteria Card -->
                    {#if eligibility}
                        <div class="rounded-2xl border bg-card p-6 shadow-xs space-y-4">
                            <h3 class="text-sm font-bold tracking-wide text-foreground uppercase flex items-center gap-2">
                                <GraduationCap class="size-4 text-primary" />
                                <span>Eligibility & Age Limit</span>
                            </h3>
                            <div class="space-y-3 text-xs">
                                {#if eligibility.minimum_age || eligibility.maximum_age}
                                    <div class="pb-2 border-b">
                                        <span class="text-muted-foreground block">Age Requirements:</span>
                                        <span class="font-semibold text-foreground">
                                            {eligibility.minimum_age ?? '18'} to {eligibility.maximum_age ?? '30'} Years
                                        </span>
                                        {#if eligibility.age_calculated_as_on}
                                            <span class="block text-[10px] text-muted-foreground mt-0.5">
                                                (Calculated as on {formatDate(eligibility.age_calculated_as_on)})
                                            </span>
                                        {/if}
                                    </div>
                                {/if}

                                {#if eligibility.qualification_summary}
                                    <div class="pb-2 border-b">
                                        <span class="text-muted-foreground block">Required Educational Qualification:</span>
                                        <p class="font-medium text-foreground mt-1 leading-relaxed">
                                            {eligibility.qualification_summary}
                                        </p>
                                    </div>
                                {/if}

                                {#if eligibility.nationality_text}
                                    <div>
                                        <span class="text-muted-foreground block">Nationality:</span>
                                        <span class="font-semibold text-foreground">{eligibility.nationality_text}</span>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    {/if}

                    <!-- Application Fee Card -->
                    {#if appDetail}
                        <div class="rounded-2xl border bg-card p-6 shadow-xs space-y-4">
                            <h3 class="text-sm font-bold tracking-wide text-foreground uppercase flex items-center gap-2">
                                <CreditCard class="size-4 text-primary" />
                                <span>Application Fee Details</span>
                            </h3>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between pb-2 border-b">
                                    <span class="text-muted-foreground">General / OBC Candidates:</span>
                                    <span class="font-bold text-foreground">
                                        {appDetail.general_fee ? `₹${appDetail.general_fee}` : 'Nil'}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between pb-2 border-b">
                                    <span class="text-muted-foreground">SC / ST Candidates:</span>
                                    <span class="font-semibold text-foreground">
                                        {appDetail.is_exempted_for_sc_st ? 'Exempted (₹0)' : (appDetail.reserved_fee ? `₹${appDetail.reserved_fee}` : 'Nil')}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-foreground">Female & PwBD:</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                        {appDetail.is_exempted_for_female ? 'Exempted (₹0)' : 'Refer Notification'}
                                    </span>
                                </div>
                            </div>
                        </div>
                    {/if}

                    <!-- Candidate Email Alert Card -->
                    <div class="rounded-2xl border bg-card p-6 shadow-xs space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded-lg bg-primary/10 text-primary">
                                <Bell class="size-4" />
                            </span>
                            <h3 class="text-sm font-bold tracking-wide text-foreground uppercase">
                                Stay Updated on This Exam
                            </h3>
                        </div>

                        <p class="text-xs text-muted-foreground leading-relaxed">
                            Receive instant verified email alerts for corrigenda, admit cards, key date extensions, and results directly from {inst.short_name || inst.name}.
                        </p>

                        {#if subscriptionSuccess}
                            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                                <CheckCircle2 class="size-4 shrink-0 text-emerald-500" />
                                <span>Subscribed! You will receive verified notifications for this authority.</span>
                            </div>
                        {:else}
                            <form onsubmit={handleSubscribe} class="space-y-2.5">
                                <input
                                    type="email"
                                    bind:value={subscribeEmail}
                                    placeholder="Enter your email address..."
                                    required
                                    class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                                />
                                <button
                                    type="submit"
                                    disabled={isSubscribing}
                                    class="w-full py-2 px-3 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all flex items-center justify-center gap-1.5 disabled:opacity-50"
                                >
                                    <Bell class="size-3.5" />
                                    <span>{isSubscribing ? 'Subscribing...' : 'Get Free Verified Alerts'}</span>
                                </button>
                            </form>
                        {/if}
                    </div>

                    <!-- Share & Verify Card -->
                    <div class="rounded-2xl border bg-card p-6 shadow-xs space-y-3">
                        <h3 class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                            Share Verified Gazette
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                onclick={copyLink}
                                class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg border bg-background hover:bg-muted/40 text-xs font-medium text-foreground transition-all"
                            >
                                {#if isCopied}
                                    <Check class="size-3.5 text-emerald-500" />
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Copied!</span>
                                {:else}
                                    <Copy class="size-3.5 text-muted-foreground" />
                                    <span>Copy Link</span>
                                {/if}
                            </button>

                            <a
                                href={`https://api.whatsapp.com/send?text=${encodeURIComponent(`📢 Government Recruitment: ${notice.title} (${notice.total_vacancies} Vacancies) - ${inst.name}. Check verified details on Suchak: ` + (typeof window !== 'undefined' ? window.location.href : ''))}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg border border-emerald-500/30 bg-emerald-500/5 hover:bg-emerald-500/10 text-xs font-medium text-emerald-700 dark:text-emerald-300 transition-all"
                            >
                                <Send class="size-3.5" />
                                <span>WhatsApp</span>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Navigation Back -->
                    <div class="pt-2">
                        <Link
                            href="/recruitment"
                            class="inline-flex items-center gap-2 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors"
                        >
                            <ArrowLeft class="size-3.5" />
                            <span>Back to Recruitment Directory</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <PublicFooter />
</div>
