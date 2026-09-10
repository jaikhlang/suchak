<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Notices & Corrigenda', href: '/admin/notices' },
            { title: 'Notice Audit', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router } from '@inertiajs/svelte';
    import AlertTriangle from '@lucide/svelte/icons/alert-triangle';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Building2 from '@lucide/svelte/icons/building-2';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import ExternalLink from '@lucide/svelte/icons/external-link';
    import FileText from '@lucide/svelte/icons/file-text';
    import History from '@lucide/svelte/icons/history';
    import RefreshCw from '@lucide/svelte/icons/refresh-cw';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Sparkles from '@lucide/svelte/icons/sparkles';
    import Users from '@lucide/svelte/icons/users';
    import Send from '@lucide/svelte/icons/send';
    import Bell from '@lucide/svelte/icons/bell';

    interface StateSummary {
        id: string;
        name: string;
        iso_code: string;
    }

    interface InstitutionSummary {
        id: string;
        name: string;
        short_name: string | null;
        slug: string;
        official_domain: string;
        state: StateSummary | null;
    }

    interface ReservationItem {
        id: string;
        category: string;
        quota_type: string;
        vacancies: number;
    }

    interface PositionItem {
        id: string;
        title: string;
        post_code: string | null;
        total_vacancies: number;
        employment_type: string;
        pay_level: string | null;
        pay_scale_text: string | null;
        reservations: ReservationItem[];
    }

    interface RevisionItem {
        id: string;
        version_number: number;
        change_reason: string | null;
        change_type: string;
        diff_data: Record<string, { old: any; new: any }>;
        created_at: string;
        actor: { id: string; name: string } | null;
    }

    interface EvidenceItem {
        id: string;
        field_name: string;
        extracted_value: string;
        verbatim_text_fragment: string;
        page_number: number;
        confidence_score: number;
        is_grounded: boolean;
    }

    interface NoticeDetail {
        id: string;
        institution_id: string;
        title: string;
        slug: string;
        reference_number: string | null;
        notice_type: string;
        status: string;
        confidence_score: number;
        total_vacancies: number;
        summary: string | null;
        is_corrigendum: boolean;
        parent_notice_id: string | null;
        published_at: string | null;
        application_start_at: string | null;
        application_end_at: string | null;
        fee_payment_end_at: string | null;
        correction_window_end_at: string | null;
        tentative_exam_date_text: string | null;
        exam_start_at: string | null;
        metadata: {
            hallucination_warning?: boolean;
            grounding_failures?: Array<{ field: string }>;
        };
        institution: InstitutionSummary;
        source: { id: string; name: string; url: string; domain: string; trust_level: string };
        source_artifact: { id: string; url: string; canonical_url: string; content_hash: string; mime_type: string; retrieved_at: string };
        positions: PositionItem[];
        evidence: EvidenceItem[];
        revisions: RevisionItem[];
        parent_notice: { id: string; title: string; slug: string; reference_number: string | null; status: string } | null;
        corrigenda: Array<{ id: string; title: string; slug: string; reference_number: string | null; status: string; published_at: string | null }>;
        post: { id: string; slug: string; published_at: string | null } | null;
    }

    interface Props {
        notice: NoticeDetail;
    }

    let { notice }: Props = $props();

    let isRepublishing = $state(false);

    function triggerRepublish() {
        isRepublishing = true;
        router.post(`/admin/notices/${notice.id}/republish`, {}, {
            preserveScroll: true,
            onFinish: () => {
                isRepublishing = false;
            },
        });
    }

    function formatConfidence(score: number): string {
        return Math.round(score * 100) + '%';
    }

    function formatDate(dateStr: string | null): string {
        if (!dateStr) return 'Not Specified';
        return new Date(dateStr).toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    let isDispatching = $state(false);

    function dispatchDistribution() {
        if (isDispatching) return;
        isDispatching = true;
        router.post(
            `/admin/notices/${notice.id}/dispatch-distribution`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    isDispatching = false;
                },
            }
        );
    }
</script>

<AppHead title={`Audit: ${notice.title}`} />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4 border-b pb-4">
        <div class="flex items-center gap-3">
            <Link
                href="/admin/notices"
                class="p-2 rounded-lg border hover:bg-muted/50 text-muted-foreground hover:text-foreground transition-colors"
                title="Back to Directory"
            >
                <ArrowLeft class="w-4 h-4" />
            </Link>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-primary/10 text-primary">
                        {notice.institution.short_name ?? notice.institution.name}
                    </span>
                    {#if notice.is_corrigendum}
                        <span class="text-xs font-medium px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                            शुद्धिपत्र Corrigendum
                        </span>
                    {/if}
                    <span class="text-xs text-muted-foreground font-mono">
                        Advt: {notice.reference_number ?? 'N/A'}
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-foreground mt-1">
                    {notice.title}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {#if notice.status === 'pending_review'}
                <Link
                    href={`/admin/moderation/${notice.id}`}
                    class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all"
                >
                    <Sparkles class="w-4 h-4" />
                    Open Split-Screen Workbench
                </Link>
            {/if}

            {#if notice.status === 'published' && notice.post}
                <button
                    onclick={triggerRepublish}
                    disabled={isRepublishing}
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border bg-card hover:bg-muted/50 transition-colors shadow-xs"
                >
                    <RefreshCw class="w-4 h-4 {isRepublishing ? 'animate-spin' : ''}" />
                    Re-render Post
                </button>

                <a
                    href={`/recruitment/${notice.institution.slug}/${notice.post.slug}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all"
                >
                    <ExternalLink class="w-4 h-4" />
                    View Public Post
                </a>
            {/if}
        </div>
    </div>

    <!-- Overview Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main 2-Cols: Recruitment Details & Positions -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <!-- Timeline & Status Card -->
            <div class="p-5 rounded-xl border bg-card text-card-foreground shadow-xs flex flex-col gap-4">
                <h2 class="text-base font-bold text-foreground flex items-center gap-2 border-b pb-2">
                    <FileText class="w-4 h-4 text-primary" /> Key Timeline & Recruitment Parameters
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-muted-foreground">Publication Date:</span>
                        <div class="font-semibold text-foreground text-sm mt-0.5">{formatDate(notice.published_at)}</div>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Application Start:</span>
                        <div class="font-semibold text-foreground text-sm mt-0.5">{formatDate(notice.application_start_at)}</div>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Application Deadline:</span>
                        <div class="font-semibold text-foreground text-sm mt-0.5">{formatDate(notice.application_end_at)}</div>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Fee Payment Closes:</span>
                        <div class="font-semibold text-foreground text-sm mt-0.5">{formatDate(notice.fee_payment_end_at)}</div>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Correction Window:</span>
                        <div class="font-semibold text-foreground text-sm mt-0.5">{formatDate(notice.correction_window_end_at)}</div>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Tentative Exam Date:</span>
                        <div class="font-semibold text-foreground text-sm mt-0.5">{notice.tentative_exam_date_text ?? 'Not announced'}</div>
                    </div>
                </div>
            </div>

            <!-- Positions & Vacancies Breakdown Card -->
            <div class="p-5 rounded-xl border bg-card text-card-foreground shadow-xs flex flex-col gap-4">
                <div class="flex items-center justify-between border-b pb-2">
                    <h2 class="text-base font-bold text-foreground flex items-center gap-2">
                        <Users class="w-4 h-4 text-primary" /> Positions & Reservation Matrix
                    </h2>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-primary/10 text-primary">
                        Total: {notice.total_vacancies.toLocaleString('en-IN')} Posts
                    </span>
                </div>

                <div class="flex flex-col gap-4">
                    {#each notice.positions as position (position.id)}
                        <div class="p-3.5 rounded-lg border bg-background/60 flex flex-col gap-2.5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-foreground text-sm">{position.title}</span>
                                    {#if position.post_code}
                                        <span class="ml-2 font-mono text-xs text-muted-foreground">(Code: {position.post_code})</span>
                                    {/if}
                                </div>
                                <span class="font-bold text-foreground text-sm">{position.total_vacancies} Posts</span>
                            </div>

                            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                                <span>Type: <strong class="text-foreground capitalize">{position.employment_type}</strong></span>
                                {#if position.pay_level}
                                    <span>Scale: <strong class="text-foreground">{position.pay_level} ({position.pay_scale_text})</strong></span>
                                {/if}
                            </div>

                            {#if position.reservations && position.reservations.length > 0}
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 pt-2 border-t border-border/40 text-center text-xs">
                                    {#each position.reservations as res (res.id)}
                                        <div class="p-1.5 rounded bg-muted/40 border">
                                            <div class="text-[10px] text-muted-foreground font-semibold">{res.category}</div>
                                            <div class="font-bold text-foreground">{res.vacancies}</div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    {/each}
                </div>
            </div>

            <!-- Corrigendum Revisions History (if any) -->
            {#if notice.revisions && notice.revisions.length > 0}
                <div class="p-5 rounded-xl border bg-card text-card-foreground shadow-xs flex flex-col gap-4">
                    <h2 class="text-base font-bold text-foreground flex items-center gap-2 border-b pb-2">
                        <History class="w-4 h-4 text-primary" /> Corrigendum Amendment Audit Trail
                    </h2>

                    <div class="flex flex-col gap-3">
                        {#each notice.revisions as rev (rev.id)}
                            <div class="p-3.5 rounded-lg border bg-background/60 flex flex-col gap-2 text-xs">
                                <div class="flex items-center justify-between font-medium">
                                    <span class="text-primary font-semibold">Revision #{rev.version_number}</span>
                                    <span class="text-muted-foreground">{formatDate(rev.created_at)}</span>
                                </div>
                                {#if rev.change_reason}
                                    <div class="text-foreground font-semibold">{rev.change_reason}</div>
                                {/if}

                                <div class="p-2.5 rounded bg-muted/50 border font-mono text-[11px] flex flex-col gap-1.5">
                                    <div class="text-muted-foreground font-semibold uppercase text-[10px]">Field Diffs:</div>
                                    {#each Object.entries(rev.diff_data) as [field, diff]}
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-foreground">{field}:</span>
                                            <span class="line-through text-destructive">{JSON.stringify(diff.old)}</span>
                                            <span>&rarr;</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{JSON.stringify(diff.new)}</span>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        {/each}
                    </div>
                </div>
            {/if}
        </div>

        <!-- Right 1-Col: Verification Evidence & Artifact Metadata -->
        <div class="flex flex-col gap-6">
            <!-- Confidence & Grounding Card -->
            <div class="p-5 rounded-xl border bg-card text-card-foreground shadow-xs flex flex-col gap-3">
                <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">AI Grounding & Confidence</span>

                <div class="flex items-center justify-between">
                    <span class="text-2xl font-bold {notice.confidence_score >= 0.94 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500'}">
                        {formatConfidence(notice.confidence_score)}
                    </span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full {notice.confidence_score >= 0.94 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600'} font-semibold">
                        {notice.confidence_score >= 0.94 ? 'Auto-Eligible' : 'Review Required'}
                    </span>
                </div>

                {#if notice.metadata?.hallucination_warning}
                    <div class="p-3 rounded-lg bg-destructive/10 border border-destructive/20 text-destructive text-xs flex items-start gap-2">
                        <AlertTriangle class="w-4 h-4 shrink-0 mt-0.5" />
                        <div>
                            <strong>Hallucination Warning:</strong> Some critical fields lacked verbatim character grounding in the gazette.
                        </div>
                    </div>
                {/if}
            </div>

            <!-- Source Gazette Artifact Info -->
            <div class="p-5 rounded-xl border bg-card text-card-foreground shadow-xs flex flex-col gap-3 text-xs">
                <span class="font-semibold text-muted-foreground uppercase tracking-wider">Source Artifact</span>

                <div>
                    <span class="text-muted-foreground">Domain:</span>
                    <div class="font-semibold text-foreground mt-0.5">{notice.source.domain}</div>
                </div>

                <div>
                    <span class="text-muted-foreground">Original URL:</span>
                    <div class="mt-0.5 truncate">
                        <a href={notice.source_artifact.url} target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-mono">
                            {notice.source_artifact.url}
                        </a>
                    </div>
                </div>

                <div>
                    <span class="text-muted-foreground">SHA-256 Content Hash:</span>
                    <div class="font-mono text-[11px] text-muted-foreground truncate mt-0.5">
                        {notice.source_artifact.content_hash}
                    </div>
                </div>

                <div>
                    <span class="text-muted-foreground">Retrieved At:</span>
                    <div class="font-semibold text-foreground mt-0.5">{formatDate(notice.source_artifact.retrieved_at)}</div>
                </div>
            </div>

            <!-- Outbound Distribution & Broadcast Gateway (Phase 10) -->
            <div class="p-5 rounded-xl border bg-card text-card-foreground shadow-xs flex flex-col gap-3 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                        <Send class="w-3.5 h-3.5 text-primary" /> Outbound Distribution
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                        Gateway Active
                    </span>
                </div>

                <p class="text-muted-foreground text-[11px] leading-relaxed">
                    Broadcast this verified recruitment notice to n8n webhook (Telegram bot channel, WhatsApp Community alerts) and active candidate subscribers.
                </p>

                <!-- Telegram Card Preview -->
                <div class="p-3 rounded-lg border bg-muted/40 font-mono text-[11px] space-y-1">
                    <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider font-sans">Telegram Channel Payload Preview:</div>
                    <div class="text-primary font-bold">📢 New Government Recruitment Verified</div>
                    <div>🏢 Authority: <strong>{notice.institution.name}</strong></div>
                    <div>📝 Notice: <strong>{notice.title}</strong></div>
                    <div>👥 Vacancies: <strong>{notice.total_vacancies.toLocaleString('en-IN')} Posts</strong></div>
                    <div>⏰ Deadline: <strong>{notice.application_end_at ? formatDate(notice.application_end_at) : 'Refer Gazette'}</strong></div>
                    <div class="text-muted-foreground text-[10px] pt-1">Signed with HMAC SHA-256</div>
                </div>

                <button
                    type="button"
                    onclick={dispatchDistribution}
                    disabled={isDispatching}
                    class="w-full mt-1 py-2 px-3 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <Send class="w-3.5 h-3.5" />
                    <span>{isDispatching ? 'Dispatching Broadcast...' : 'Dispatch Test Broadcast to n8n & Subscribers'}</span>
                </button>
            </div>

            <!-- Extracted Evidence Snippets -->
            <div class="p-5 rounded-xl border bg-card text-card-foreground shadow-xs flex flex-col gap-3 text-xs">
                <span class="font-semibold text-muted-foreground uppercase tracking-wider">Verbatim Grounding Snippets</span>

                <div class="flex flex-col gap-2.5 max-h-96 overflow-y-auto pr-1">
                    {#each notice.evidence as item (item.id)}
                        <div class="p-2.5 rounded-lg border bg-background/60 flex flex-col gap-1">
                            <div class="flex items-center justify-between font-semibold">
                                <span class="text-foreground">{item.field_name}</span>
                                <span class="text-[10px] text-muted-foreground font-mono">p. {item.page_number}</span>
                            </div>
                            <div class="text-muted-foreground italic text-[11px]">
                                "{item.verbatim_text_fragment}"
                            </div>
                        </div>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>
