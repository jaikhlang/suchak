<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: "Dashboard", href: "/dashboard" },
            { title: "Moderation Queue", href: "/admin/moderation" },
            { title: "Verification Workbench", href: "#" },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Link, router } from "@inertiajs/svelte";
    import AlertTriangle from "@lucide/svelte/icons/alert-triangle";
    import ArrowLeft from "@lucide/svelte/icons/arrow-left";
    import ArrowRight from "@lucide/svelte/icons/arrow-right";
    import Building2 from "@lucide/svelte/icons/building-2";
    import Calendar from "@lucide/svelte/icons/calendar";
    import Check from "@lucide/svelte/icons/check";
    import CheckCircle2 from "@lucide/svelte/icons/check-circle-2";
    import ChevronDown from "@lucide/svelte/icons/chevron-down";
    import ExternalLink from "@lucide/svelte/icons/external-link";
    import Eye from "@lucide/svelte/icons/eye";
    import FileText from "@lucide/svelte/icons/file-text";
    import Info from "@lucide/svelte/icons/info";
    import Keyboard from "@lucide/svelte/icons/keyboard";
    import Pencil from "@lucide/svelte/icons/pencil";
    import ShieldAlert from "@lucide/svelte/icons/shield-alert";
    import ShieldCheck from "@lucide/svelte/icons/shield-check";
    import Users from "@lucide/svelte/icons/users";
    import X from "@lucide/svelte/icons/x";
    import { onMount } from "svelte";

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
        department: string | null;
        total_vacancies: number;
        employment_type: string;
        pay_level: string | null;
        pay_scale_text: string | null;
        salary_min: number | null;
        salary_max: number | null;
        reservations: ReservationItem[];
    }

    interface EvidenceItem {
        id: string;
        field_name: string;
        extracted_value: string;
        page_number: number | null;
        verbatim_text_fragment: string;
        char_start_offset: number | null;
        char_end_offset: number | null;
        confidence_score: number;
        is_verified_by_human: boolean;
    }

    interface ApplicationDetailItem {
        id: string;
        application_mode: string;
        apply_url: string | null;
        official_notification_pdf_url: string | null;
        general_fee: number;
        reserved_fee: number;
        female_fee: number;
        is_exempted_for_sc_st: boolean;
        is_exempted_for_female: boolean;
        is_exempted_for_pwbd: boolean;
        offline_postal_address: string | null;
        postal_pincode: string | null;
        instructions: string | null;
    }

    interface EligibilityRuleItem {
        id: string;
        minimum_age: number | null;
        maximum_age: number | null;
        age_calculated_as_on: string | null;
        age_relaxation_json: Record<string, number>;
        qualification_summary: string | null;
        experience_text: string | null;
        nationality_text: string;
        raw_eligibility_text: string | null;
    }

    interface ArtifactExtractionItem {
        id: string;
        method: string;
        clean_text: string | null;
        raw_text: string | null;
        page_count: number | null;
        confidence_score: number | null;
        processor_name: string;
    }

    interface SourceArtifactItem {
        id: string;
        url: string;
        canonical_url: string;
        content_hash: string;
        storage_path: string;
        file_size_bytes: number;
        extractions: ArtifactExtractionItem[];
    }

    interface NoticeItem {
        id: string;
        title: string;
        notice_type: string;
        slug: string;
        reference_number: string | null;
        summary: string | null;
        status: string;
        confidence_score: number;
        is_corrigendum: boolean;
        published_at: string | null;
        application_start_at: string | null;
        application_end_at: string | null;
        fee_payment_end_at: string | null;
        correction_window_end_at: string | null;
        tentative_exam_date_text: string | null;
        exam_start_at: string | null;
        exam_end_at: string | null;
        canonical_source_url: string;
        total_vacancies: number;
        metadata: {
            hallucination_warning?: boolean;
            grounding_failures?: Array<{ field: string; claimed_value?: string; missing_snippet?: string }>;
            rejection_reason?: string;
        };
        institution: {
            id: string;
            name: string;
            short_name: string | null;
            official_domain: string;
            is_verified: boolean;
        };
        source: {
            id: string;
            name: string;
            trust_level: string;
        };
        source_artifact: SourceArtifactItem | null;
        positions: PositionItem[];
        evidence: EvidenceItem[];
        application_detail: ApplicationDetailItem | null;
        eligibility_rule: EligibilityRuleItem | null;
    }

    interface Props {
        notice: NoticeItem;
        primaryExtraction: ArtifactExtractionItem | null;
        nextPendingNotice: { id: string; title: string; slug: string } | null;
        candidateParentNotices?: Array<{
            id: string;
            title: string;
            reference_number: string | null;
            total_vacancies: number;
            application_end_at: string | null;
            status: string;
        }>;
    }

    let { notice, primaryExtraction, nextPendingNotice, candidateParentNotices = [] }: Props = $props();

    // Active State
    let activeEvidenceIndex = $state<number>(0);
    let isEditMode = $state<boolean>(false);
    let isRejectModalOpen = $state<boolean>(false);
    let isCorrigendumModalOpen = $state<boolean>(false);
    let isMergeModalOpen = $state<boolean>(false);
    let isSubmitting = $state<boolean>(false);
    let documentViewMode = $state<"ocr" | "raw">("ocr");

    // Edit Form State
    let editForm = $state({
        title: notice.title,
        reference_number: notice.reference_number ?? "",
        summary: notice.summary ?? "",
        application_start_at: notice.application_start_at ? notice.application_start_at.substring(0, 10) : "",
        application_end_at: notice.application_end_at ? notice.application_end_at.substring(0, 10) : "",
        fee_payment_end_at: notice.fee_payment_end_at ? notice.fee_payment_end_at.substring(0, 10) : "",
        tentative_exam_date_text: notice.tentative_exam_date_text ?? "",
        total_vacancies: notice.total_vacancies,
        reason: "",
    });

    // Rejection Form State
    let rejectionReason = $state("duplicate");
    let rejectionNotes = $state("");

    // Corrigendum Form State
    let corrigendumForm = $state({
        parent_notice_id: candidateParentNotices.length > 0 ? candidateParentNotices[0].id : "",
        reason: `Corrigendum amendment to ${notice.reference_number || notice.title}`,
        application_end_at: notice.application_end_at ? notice.application_end_at.substring(0, 10) : "",
        fee_payment_end_at: notice.fee_payment_end_at ? notice.fee_payment_end_at.substring(0, 10) : "",
        correction_window_end_at: notice.correction_window_end_at ? notice.correction_window_end_at.substring(0, 10) : "",
        tentative_exam_date_text: notice.tentative_exam_date_text ?? "",
        total_vacancies: notice.total_vacancies,
    });

    // Merge Duplicate Form State
    let mergeForm = $state({
        canonical_notice_id: candidateParentNotices.length > 0 ? candidateParentNotices[0].id : "",
        notes: "",
    });

    // Active Evidence Item
    let currentEvidence = $derived(
        notice.evidence.length > 0 && activeEvidenceIndex < notice.evidence.length
            ? notice.evidence[activeEvidenceIndex]
            : null
    );

    // Document text for left pane
    let documentText = $derived(
        documentViewMode === "ocr"
            ? (primaryExtraction?.clean_text || primaryExtraction?.raw_text || "No OCR text extracted for this document.")
            : (primaryExtraction?.raw_text || primaryExtraction?.clean_text || "No raw text available.")
    );

    // Verification check for reservations math
    let reservationCalculations = $derived(() => {
        let declaredTotal = notice.total_vacancies;
        let sumTotal = notice.positions.reduce((acc, pos) => acc + pos.total_vacancies, 0);
        return {
            declaredTotal,
            sumTotal,
            isMatch: declaredTotal === sumTotal,
        };
    });

    function selectEvidence(index: number) {
        activeEvidenceIndex = index;
        scrollToEvidenceSnippet();
    }

    function selectEvidenceByField(fieldName: string) {
        const idx = notice.evidence.findIndex((e) => e.field_name === fieldName);
        if (idx !== -1) {
            selectEvidence(idx);
        }
    }

    function scrollToEvidenceSnippet() {
        const snippet = currentEvidence?.verbatim_text_fragment;
        if (!snippet) return;

        const container = document.getElementById("document-text-container");
        const el = document.getElementById("active-evidence-highlight");
        if (container && el) {
            el.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }

    // Keyboard Shortcuts
    function handleKeydown(e: KeyboardEvent) {
        // Skip if typing in an input or textarea
        const activeTag = document.activeElement?.tagName.toLowerCase();
        if (activeTag === "input" || activeTag === "textarea" || activeTag === "select") {
            return;
        }

        if (e.key === "a" || e.key === "A") {
            e.preventDefault();
            approveNotice();
        } else if (e.key === "r" || e.key === "R") {
            e.preventDefault();
            isRejectModalOpen = true;
        } else if (e.key === "c" || e.key === "C") {
            e.preventDefault();
            isCorrigendumModalOpen = true;
        } else if (e.key === "m" || e.key === "M") {
            e.preventDefault();
            isMergeModalOpen = true;
        } else if (e.key === "e" || e.key === "E") {
            e.preventDefault();
            isEditMode = !isEditMode;
        } else if (e.key === "j" || e.key === "J") {
            e.preventDefault();
            if (notice.evidence.length > 0) {
                activeEvidenceIndex = (activeEvidenceIndex + 1) % notice.evidence.length;
                scrollToEvidenceSnippet();
            }
        } else if (e.key === "k" || e.key === "K") {
            e.preventDefault();
            if (notice.evidence.length > 0) {
                activeEvidenceIndex = (activeEvidenceIndex - 1 + notice.evidence.length) % notice.evidence.length;
                scrollToEvidenceSnippet();
            }
        }
    }

    onMount(() => {
        window.addEventListener("keydown", handleKeydown);
        return () => {
            window.removeEventListener("keydown", handleKeydown);
        };
    });

    function approveNotice() {
        if (isSubmitting) return;
        isSubmitting = true;
        router.post(
            `/admin/moderation/${notice.id}/approve`,
            { publish_post: true },
            {
                onFinish: () => {
                    isSubmitting = false;
                },
            }
        );
    }

    function submitRejection() {
        if (isSubmitting) return;
        isSubmitting = true;
        router.post(
            `/admin/moderation/${notice.id}/reject`,
            {
                reason: rejectionReason,
                notes: rejectionNotes,
            },
            {
                onFinish: () => {
                    isSubmitting = false;
                    isRejectModalOpen = false;
                },
            }
        );
    }

    function submitCorrigendum() {
        if (isSubmitting || !corrigendumForm.parent_notice_id) return;
        isSubmitting = true;
        router.post(
            `/admin/moderation/${notice.id}/corrigendum`,
            corrigendumForm,
            {
                onSuccess: () => {
                    isCorrigendumModalOpen = false;
                },
                onFinish: () => {
                    isSubmitting = false;
                },
            }
        );
    }

    function submitMerge() {
        if (isSubmitting || !mergeForm.canonical_notice_id) return;
        isSubmitting = true;
        router.post(
            `/admin/moderation/${notice.id}/merge-duplicate`,
            mergeForm,
            {
                onSuccess: () => {
                    isMergeModalOpen = false;
                },
                onFinish: () => {
                    isSubmitting = false;
                },
            }
        );
    }

    function saveManualEdit() {
        if (isSubmitting) return;
        isSubmitting = true;
        router.put(
            `/admin/moderation/${notice.id}`,
            editForm,
            {
                onSuccess: () => {
                    isEditMode = false;
                },
                onFinish: () => {
                    isSubmitting = false;
                },
            }
        );
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

    function isFieldGrounded(fieldName: string): boolean {
        const ev = notice.evidence.find((e) => e.field_name === fieldName);
        if (!ev) return false;
        return ev.confidence_score >= 0.80;
    }

    function getEvidenceForField(fieldName: string): EvidenceItem | undefined {
        return notice.evidence.find((e) => e.field_name === fieldName);
    }
</script>

<AppHead title={`Review: ${notice.title}`} />

<!-- Main Container (Full height split-screen) -->
<div class="flex flex-col h-[calc(100vh-4rem)] w-full overflow-hidden bg-background text-foreground">
    <!-- Top Action Bar -->
    <header class="h-16 border-b px-4 lg:px-6 flex items-center justify-between gap-4 bg-card shrink-0 shadow-xs">
        <div class="flex items-center gap-3 min-w-0">
            <Link
                href="/admin/moderation"
                class="p-2 rounded-lg border hover:bg-muted/50 text-muted-foreground transition-all shrink-0"
                title="Back to Queue"
            >
                <ArrowLeft class="w-4 h-4" />
            </Link>

            <div class="truncate">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-primary/10 text-primary truncate">
                        {notice.institution.short_name || notice.institution.name}
                    </span>
                    <span class="text-xs font-mono text-muted-foreground hidden sm:inline">
                        {notice.reference_number || "No Ref No."}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-md font-semibold {notice.confidence_score >= 0.94 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : (notice.confidence_score >= 0.75 ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-destructive/10 text-destructive')}">
                        {formatConfidence(notice.confidence_score)} Confidence
                    </span>
                </div>
                <h1 class="text-sm font-bold text-foreground truncate max-w-xl">
                    {notice.title}
                </h1>
            </div>
        </div>

        <!-- Hotkey Indicators & Main Actions -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Speed-Run Hotkey Pill -->
            <div class="hidden xl:flex items-center gap-1.5 px-2.5 py-1 rounded-md border text-xs text-muted-foreground bg-muted/30">
                <Keyboard class="w-3.5 h-3.5" />
                <span>Keys:</span>
                <kbd class="px-1 rounded border bg-background font-mono font-semibold text-foreground">A</kbd> Approve
                <kbd class="px-1 rounded border bg-background font-mono font-semibold text-foreground">R</kbd> Reject
                <kbd class="px-1 rounded border bg-background font-mono font-semibold text-foreground">E</kbd> Edit
                <kbd class="px-1 rounded border bg-background font-mono font-semibold text-foreground">C</kbd> Corrigendum
                <kbd class="px-1 rounded border bg-background font-mono font-semibold text-foreground">M</kbd> Merge
                <kbd class="px-1 rounded border bg-background font-mono font-semibold text-foreground">J/K</kbd> Evidence
            </div>

            <!-- Corrigendum Button -->
            <button
                type="button"
                onclick={() => (isCorrigendumModalOpen = true)}
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-amber-500/30 text-amber-600 dark:text-amber-400 bg-background hover:bg-amber-500/10 transition-all"
                title="Mark as Corrigendum / Amendment and patch parent notice (Hotkey: C)"
            >
                Corrigendum (C)
            </button>

            <!-- Merge Duplicate Button -->
            <button
                type="button"
                onclick={() => (isMergeModalOpen = true)}
                class="px-3 py-1.5 text-xs font-medium rounded-lg border bg-background hover:bg-muted/50 text-foreground transition-all"
                title="Merge as duplicate into existing notice (Hotkey: M)"
            >
                Merge (M)
            </button>

            <!-- Edit Toggle -->
            <button
                type="button"
                onclick={() => (isEditMode = !isEditMode)}
                class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-all {isEditMode ? 'bg-primary text-primary-foreground border-primary' : 'bg-background hover:bg-muted/50 text-foreground'}"
            >
                <Pencil class="w-3.5 h-3.5 inline mr-1" />
                {isEditMode ? "Cancel Edit" : "Edit (E)"}
            </button>

            <!-- Reject Button -->
            <button
                type="button"
                onclick={() => (isRejectModalOpen = true)}
                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-destructive/30 text-destructive hover:bg-destructive/10 transition-all"
            >
                <X class="w-3.5 h-3.5 inline mr-1" />
                Reject (R)
            </button>

            <!-- Approve Button -->
            <button
                type="button"
                onclick={approveNotice}
                disabled={isSubmitting}
                class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all flex items-center gap-1.5"
            >
                <Check class="w-3.5 h-3.5" />
                Approve & Publish (A)
            </button>
        </div>
    </header>

    <!-- Split-Screen Body -->
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x overflow-hidden">
        <!-- ========================================== -->
        <!-- LEFT PANE: Document & Evidence Viewer     -->
        <!-- ========================================== -->
        <div class="flex flex-col h-full overflow-hidden bg-muted/20">
            <!-- Left Pane Sub-header -->
            <div class="h-11 px-4 border-b flex items-center justify-between bg-card/60 text-xs shrink-0">
                <div class="flex items-center gap-2">
                    <FileText class="w-4 h-4 text-muted-foreground" />
                    <span class="font-semibold text-foreground">Source Document & OCR Layer</span>
                    {#if primaryExtraction?.page_count}
                        <span class="text-muted-foreground">({primaryExtraction.page_count} Pages)</span>
                    {/if}
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex items-center rounded-md border p-0.5 bg-background">
                        <button
                            type="button"
                            onclick={() => (documentViewMode = "ocr")}
                            class="px-2 py-0.5 text-xs rounded {documentViewMode === 'ocr' ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:text-foreground'}"
                        >
                            Clean OCR
                        </button>
                        <button
                            type="button"
                            onclick={() => (documentViewMode = "raw")}
                            class="px-2 py-0.5 text-xs rounded {documentViewMode === 'raw' ? 'bg-primary text-primary-foreground font-medium' : 'text-muted-foreground hover:text-foreground'}"
                        >
                            Raw Text
                        </button>
                    </div>

                    {#if notice.source_artifact?.url}
                        <a
                            href={notice.source_artifact.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            class="p-1 rounded hover:bg-muted text-muted-foreground hover:text-foreground"
                            title="Open original official link"
                        >
                            <ExternalLink class="w-3.5 h-3.5" />
                        </a>
                    {/if}
                </div>
            </div>

            <!-- Active Evidence Banner -->
            {#if currentEvidence}
                <div class="px-4 py-2 bg-amber-500/10 border-b flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 truncate">
                        <span class="font-semibold text-amber-700 dark:text-amber-300 uppercase tracking-wider text-[11px]">
                            Focus: {currentEvidence.field_name.replace(/_/g, " ")}
                        </span>
                        <span class="text-muted-foreground truncate italic">
                            "{currentEvidence.verbatim_text_fragment}"
                        </span>
                    </div>

                    <div class="flex items-center gap-1 shrink-0 text-muted-foreground">
                        <span>Page {currentEvidence.page_number ?? 1}</span>
                        <span class="font-mono">({activeEvidenceIndex + 1}/{notice.evidence.length})</span>
                    </div>
                </div>
            {/if}

            <!-- Document Text Scroller -->
            <div id="document-text-container" class="flex-1 p-6 overflow-y-auto font-mono text-xs leading-relaxed select-text whitespace-pre-wrap text-foreground/90">
                {#if currentEvidence && documentText.includes(currentEvidence.verbatim_text_fragment)}
                    {@const parts = documentText.split(currentEvidence.verbatim_text_fragment)}
                    {parts[0]}<mark
                        id="active-evidence-highlight"
                        class="bg-amber-300 text-amber-950 dark:bg-amber-500 dark:text-black font-semibold px-1 py-0.5 rounded border-2 border-amber-500 animate-pulse shadow-xs"
                    >{currentEvidence.verbatim_text_fragment}</mark>{parts.slice(1).join(currentEvidence.verbatim_text_fragment)}
                {:else}
                    {documentText}
                {/if}
            </div>

            <!-- Left Pane Footer -->
            <div class="h-10 px-4 border-t bg-card/60 flex items-center justify-between text-xs text-muted-foreground shrink-0">
                <span>Processor: {primaryExtraction?.processor_name || "Docling & PaddleOCR"}</span>
                <span>SHA-256: <code class="font-mono text-[10px]">{notice.source_artifact?.content_hash.substring(0, 16) || "N/A"}...</code></span>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT PANE: Structured Inspector & Editor  -->
        <!-- ========================================== -->
        <div class="flex flex-col h-full overflow-y-auto p-4 md:p-6 gap-6 bg-card">
            <!-- Hallucination Alert Banner (if ungrounded) -->
            {#if notice.metadata?.hallucination_warning}
                <div class="p-4 rounded-xl border border-destructive/30 bg-destructive/10 text-destructive flex items-start gap-3">
                    <AlertTriangle class="w-5 h-5 shrink-0 mt-0.5" />
                    <div>
                        <h4 class="text-sm font-semibold">Potential Hallucination / Ungrounded Assertions Detected</h4>
                        <p class="text-xs mt-1 text-destructive/90">
                            The extraction model asserted values that could not be matched verbatim in the raw artifact text. Please manually verify or edit the highlighted fields before approving.
                        </p>
                        {#if notice.metadata.grounding_failures && notice.metadata.grounding_failures.length > 0}
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                {#each notice.metadata.grounding_failures as failure, i (`failure-${i}`)}
                                    <span class="px-2 py-0.5 rounded bg-destructive/20 text-xs font-mono">
                                        {failure.field}
                                    </span>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}

            <!-- 1. Basic Metadata & Reference Card -->
            <section class="p-4 rounded-xl border bg-card shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider flex items-center gap-1.5">
                        <Building2 class="w-4 h-4 text-muted-foreground" />
                        Notice Information
                    </h3>

                    {#if isFieldGrounded("title")}
                        <button
                            type="button"
                            onclick={() => selectEvidenceByField("title")}
                            class="text-[11px] font-medium px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 transition-all cursor-pointer"
                        >
                            ✓ Grounded
                        </button>
                    {/if}
                </div>

                {#if isEditMode}
                    <div class="space-y-3">
                        <div>
                            <label for="edit-title" class="text-xs font-medium text-muted-foreground block mb-1">Notice Title</label>
                            <input
                                id="edit-title"
                                type="text"
                                bind:value={editForm.title}
                                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="edit-ref" class="text-xs font-medium text-muted-foreground block mb-1">Advt / Reference No.</label>
                                <input
                                    id="edit-ref"
                                    type="text"
                                    bind:value={editForm.reference_number}
                                    class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                                />
                            </div>
                            <div>
                                <label for="edit-vacancies" class="text-xs font-medium text-muted-foreground block mb-1">Total Vacancies</label>
                                <input
                                    id="edit-vacancies"
                                    type="number"
                                    bind:value={editForm.total_vacancies}
                                    class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                                />
                            </div>
                        </div>

                        <div>
                            <label for="edit-summary" class="text-xs font-medium text-muted-foreground block mb-1">Summary</label>
                            <textarea
                                id="edit-summary"
                                rows={2}
                                bind:value={editForm.summary}
                                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                            ></textarea>
                        </div>
                    </div>
                {:else}
                    <div class="space-y-2">
                        <div class="text-base font-semibold text-foreground">{notice.title}</div>
                        <div class="grid grid-cols-2 gap-4 text-xs text-muted-foreground pt-1">
                            <div>
                                <span class="block font-medium">Reference No:</span>
                                <span class="font-mono text-foreground">{notice.reference_number || "None"}</span>
                            </div>
                            <div>
                                <span class="block font-medium">Notice Type:</span>
                                <span class="capitalize text-foreground">{notice.notice_type.replace(/_/g, " ")}</span>
                            </div>
                        </div>
                        {#if notice.summary}
                            <p class="text-xs text-muted-foreground pt-2 border-t mt-2">
                                {notice.summary}
                            </p>
                        {/if}
                    </div>
                {/if}
            </section>

            <!-- 2. Timeline & Critical Deadlines Card -->
            <section class="p-4 rounded-xl border bg-card shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider flex items-center gap-1.5">
                        <Calendar class="w-4 h-4 text-muted-foreground" />
                        Important Dates
                    </h3>

                    {#if isFieldGrounded("application_end_at")}
                        <button
                            type="button"
                            onclick={() => selectEvidenceByField("application_end_at")}
                            class="text-[11px] font-medium px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 transition-all cursor-pointer"
                        >
                            ✓ Grounded
                        </button>
                    {:else}
                        <button
                            type="button"
                            onclick={() => selectEvidenceByField("application_end_at")}
                            class="text-[11px] font-medium px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 transition-all cursor-pointer"
                        >
                            ⚠ Ungrounded Deadline
                        </button>
                    {/if}
                </div>

                {#if isEditMode}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="edit-app-start" class="text-xs font-medium text-muted-foreground block mb-1">Application Start</label>
                            <input
                                id="edit-app-start"
                                type="date"
                                bind:value={editForm.application_start_at}
                                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                            />
                        </div>
                        <div>
                            <label for="edit-app-end" class="text-xs font-medium text-muted-foreground block mb-1">Application End</label>
                            <input
                                id="edit-app-end"
                                type="date"
                                bind:value={editForm.application_end_at}
                                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                            />
                        </div>
                        <div>
                            <label for="edit-fee-end" class="text-xs font-medium text-muted-foreground block mb-1">Fee Payment End</label>
                            <input
                                id="edit-fee-end"
                                type="date"
                                bind:value={editForm.fee_payment_end_at}
                                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                            />
                        </div>
                        <div>
                            <label for="edit-exam-text" class="text-xs font-medium text-muted-foreground block mb-1">Exam Date Text</label>
                            <input
                                id="edit-exam-text"
                                type="text"
                                bind:value={editForm.tentative_exam_date_text}
                                placeholder="e.g. November 2026"
                                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                            />
                        </div>
                    </div>
                {:else}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">Start Date</span>
                            <span class="font-semibold text-foreground text-sm">{formatDate(notice.application_start_at)}</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">Closing Deadline</span>
                            <span class="font-semibold text-foreground text-sm text-primary">{formatDate(notice.application_end_at)}</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">Fee Payment</span>
                            <span class="font-semibold text-foreground text-sm">{formatDate(notice.fee_payment_end_at)}</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">Exam Schedule</span>
                            <span class="font-semibold text-foreground text-sm">{notice.tentative_exam_date_text || "TBA"}</span>
                        </div>
                    </div>
                {/if}
            </section>

            <!-- 3. Positions & Indian Reservation Matrix Card -->
            <section class="p-4 rounded-xl border bg-card shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider flex items-center gap-1.5">
                            <Users class="w-4 h-4 text-muted-foreground" />
                            Cadres & Indian Quota Matrix
                        </h3>
                        <span class="text-xs text-muted-foreground">
                            Declared Total: {notice.total_vacancies} Posts
                        </span>
                    </div>

                    <!-- Math verification status -->
                    {#if reservationCalculations().isMatch}
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <CheckCircle2 class="w-3 h-3" />
                            Math Verified ({reservationCalculations().sumTotal})
                        </span>
                    {:else}
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded bg-destructive/10 text-destructive">
                            <AlertTriangle class="w-3 h-3" />
                            Discrepancy (Sum: {reservationCalculations().sumTotal} vs Declared: {reservationCalculations().declaredTotal})
                        </span>
                    {/if}
                </div>

                <div class="space-y-3">
                    {#each notice.positions as position, pIndex (`${position.id}-${pIndex}`)}
                        <div class="p-3 rounded-lg border bg-muted/20">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-semibold text-sm text-foreground">{position.title}</span>
                                    {#if position.post_code}
                                        <span class="ml-2 text-xs font-mono px-1.5 py-0.2 rounded border bg-background text-muted-foreground">
                                            Code: {position.post_code}
                                        </span>
                                    {/if}
                                </div>
                                <span class="font-bold text-sm text-foreground">
                                    {position.total_vacancies} Posts
                                </span>
                            </div>

                            <div class="text-xs text-muted-foreground mt-1">
                                Pay: {position.pay_scale_text || position.pay_level || "As per rules"}
                            </div>

                            <!-- Reservation Breakdown Badges -->
                            {#if position.reservations.length > 0}
                                <div class="flex flex-wrap gap-1.5 mt-2 pt-2 border-t">
                                    {#each position.reservations as res, rIndex (`${res.id}-${rIndex}`)}
                                        <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded border bg-card">
                                            <strong class="text-foreground mr-1">{res.category}:</strong> {res.vacancies}
                                        </span>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    {/each}
                </div>
            </section>

            <!-- 4. Eligibility & Qualifications Card -->
            {#if notice.eligibility_rule}
                <section class="p-4 rounded-xl border bg-card shadow-xs">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <ShieldCheck class="w-4 h-4 text-muted-foreground" />
                        Eligibility & Age Criteria
                    </h3>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">Age Range</span>
                            <span class="font-semibold text-foreground text-sm">
                                {notice.eligibility_rule.minimum_age ?? "—"} to {notice.eligibility_rule.maximum_age ?? "—"} Years
                            </span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">Nationality</span>
                            <span class="font-semibold text-foreground text-sm">
                                {notice.eligibility_rule.nationality_text}
                            </span>
                        </div>
                    </div>

                    {#if notice.eligibility_rule.qualification_summary}
                        <div class="mt-3 text-xs">
                            <span class="font-medium text-muted-foreground block mb-0.5">Qualifications:</span>
                            <p class="text-foreground">{notice.eligibility_rule.qualification_summary}</p>
                        </div>
                    {/if}
                </section>
            {/if}

            <!-- 5. Application Details & Fees Card -->
            {#if notice.application_detail}
                <section class="p-4 rounded-xl border bg-card shadow-xs">
                    <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <Info class="w-4 h-4 text-muted-foreground" />
                        Application Mode & Fees
                    </h3>

                    <div class="grid grid-cols-3 gap-3 text-xs">
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">Mode</span>
                            <span class="font-semibold text-foreground text-sm capitalize">
                                {notice.application_detail.application_mode.replace(/_/g, " ")}
                            </span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">General Fee</span>
                            <span class="font-semibold text-foreground text-sm">
                                {notice.application_detail.general_fee ? `₹${notice.application_detail.general_fee}` : "Free"}
                            </span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-muted/30 border">
                            <span class="text-muted-foreground block text-[10px]">Reserved / SC / ST</span>
                            <span class="font-semibold text-foreground text-sm">
                                {notice.application_detail.reserved_fee ? `₹${notice.application_detail.reserved_fee}` : "Exempted"}
                            </span>
                        </div>
                    </div>
                </section>
            {/if}

            <!-- Save manual edit button if in edit mode -->
            {#if isEditMode}
                <div class="sticky bottom-0 p-4 rounded-xl border bg-card/95 backdrop-blur-md shadow-md flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <input
                            type="text"
                            bind:value={editForm.reason}
                            placeholder="State reason for manual revision (e.g. Corrected date per page 3)..."
                            class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground"
                        />
                    </div>
                    <button
                        type="button"
                        onclick={saveManualEdit}
                        disabled={isSubmitting}
                        class="px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all shrink-0"
                    >
                        Save Revision & Diff
                    </button>
                </div>
            {/if}
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
{#if isRejectModalOpen}
    <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card text-card-foreground rounded-xl border shadow-lg max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-foreground">Reject Recruitment Notice</h3>
                <button
                    type="button"
                    onclick={() => (isRejectModalOpen = false)}
                    class="p-1 rounded hover:bg-muted text-muted-foreground hover:text-foreground"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <p class="text-xs text-muted-foreground">
                Rejection removes this notice from public publication. Please specify the reason for the audit log.
            </p>

            <div class="space-y-3">
                <div>
                    <label for="reject-reason-select" class="text-xs font-medium text-muted-foreground block mb-1">Primary Reason</label>
                    <select
                        id="reject-reason-select"
                        bind:value={rejectionReason}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                    >
                        <option value="duplicate">Duplicate Notice / Already Published</option>
                        <option value="tender_only">Tender / Auction (Not Recruitment)</option>
                        <option value="invalid_scan">Unreadable / Corrupted Scan</option>
                        <option value="unverified_source">Unverified Third-Party Circular</option>
                        <option value="other">Other / Manual Discard</option>
                    </select>
                </div>

                <div>
                    <label for="reject-notes-textarea" class="text-xs font-medium text-muted-foreground block mb-1">Moderator Audit Notes (Optional)</label>
                    <textarea
                        id="reject-notes-textarea"
                        rows={3}
                        bind:value={rejectionNotes}
                        placeholder="Additional details explaining the rejection..."
                        class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                    ></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    onclick={() => (isRejectModalOpen = false)}
                    class="px-4 py-2 text-xs font-medium rounded-lg border bg-background hover:bg-muted/50 text-muted-foreground transition-all"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    onclick={submitRejection}
                    disabled={isSubmitting}
                    class="px-4 py-2 text-xs font-semibold rounded-lg bg-destructive text-destructive-foreground hover:bg-destructive/90 shadow-xs transition-all"
                >
                    Confirm Rejection
                </button>
            </div>
        </div>
    </div>
{/if}

<!-- Corrigendum / Amendment Patch Modal -->
{#if isCorrigendumModalOpen}
    <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card text-card-foreground rounded-xl border shadow-lg max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <FileText class="w-4 h-4" />
                    </span>
                    <h3 class="text-base font-bold text-foreground">Apply Corrigendum / Amendment Patch</h3>
                </div>
                <button
                    type="button"
                    onclick={() => (isCorrigendumModalOpen = false)}
                    class="p-1 rounded hover:bg-muted text-muted-foreground hover:text-foreground"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <p class="text-xs text-muted-foreground leading-relaxed">
                Designate this circular as an official Corrigendum / Addendum ("शुद्धिपत्र") to update an existing recruitment notice. A new revision audit record will be logged on the parent notice.
            </p>

            <div class="space-y-4 pt-1">
                <!-- Target Parent Notice -->
                <div>
                    <label for="corrigendum-parent-select" class="text-xs font-semibold text-foreground block mb-1">
                        Select Parent Recruitment Notice <span class="text-destructive">*</span>
                    </label>
                    {#if candidateParentNotices.length > 0}
                        <select
                            id="corrigendum-parent-select"
                            bind:value={corrigendumForm.parent_notice_id}
                            class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                        >
                            {#each candidateParentNotices as parent (parent.id)}
                                <option value={parent.id}>
                                    {parent.reference_number ? `[${parent.reference_number}] ` : ''}{parent.title} ({parent.total_vacancies} posts)
                                </option>
                            {/each}
                        </select>
                    {:else}
                        <input
                            id="corrigendum-parent-select"
                            type="text"
                            bind:value={corrigendumForm.parent_notice_id}
                            placeholder="Enter Parent Notice ULID..."
                            class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 font-mono"
                        />
                    {/if}
                </div>

                <!-- Reason / Context -->
                <div>
                    <label for="corrigendum-reason-input" class="text-xs font-semibold text-foreground block mb-1">
                        Amendment Reason / Gazette Reference
                    </label>
                    <input
                        id="corrigendum-reason-input"
                        type="text"
                        bind:value={corrigendumForm.reason}
                        placeholder="e.g. Corrigendum-1: Extended application deadline and revised vacancy matrix"
                        class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                    />
                </div>

                <!-- Patched Fields Grid -->
                <div class="rounded-lg border bg-muted/20 p-3 space-y-3">
                    <span class="text-xs font-bold text-foreground block">
                        Fields to Patch on Parent Notice:
                    </span>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label for="corr-deadline" class="text-[11px] font-medium text-muted-foreground block mb-1">
                                New Application Deadline
                            </label>
                            <input
                                id="corr-deadline"
                                type="date"
                                bind:value={corrigendumForm.application_end_at}
                                class="w-full px-2.5 py-1.5 text-xs rounded-md border bg-background text-foreground focus:outline-hidden"
                            />
                        </div>

                        <div>
                            <label for="corr-vacancies" class="text-[11px] font-medium text-muted-foreground block mb-1">
                                Revised Total Vacancies
                            </label>
                            <input
                                id="corr-vacancies"
                                type="number"
                                bind:value={corrigendumForm.total_vacancies}
                                min="0"
                                class="w-full px-2.5 py-1.5 text-xs rounded-md border bg-background text-foreground focus:outline-hidden"
                            />
                        </div>

                        <div>
                            <label for="corr-fee-deadline" class="text-[11px] font-medium text-muted-foreground block mb-1">
                                New Fee Payment End Date
                            </label>
                            <input
                                id="corr-fee-deadline"
                                type="date"
                                bind:value={corrigendumForm.fee_payment_end_at}
                                class="w-full px-2.5 py-1.5 text-xs rounded-md border bg-background text-foreground focus:outline-hidden"
                            />
                        </div>

                        <div>
                            <label for="corr-correction-window" class="text-[11px] font-medium text-muted-foreground block mb-1">
                                Correction Window Closes
                            </label>
                            <input
                                id="corr-correction-window"
                                type="date"
                                bind:value={corrigendumForm.correction_window_end_at}
                                class="w-full px-2.5 py-1.5 text-xs rounded-md border bg-background text-foreground focus:outline-hidden"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="corr-exam-text" class="text-[11px] font-medium text-muted-foreground block mb-1">
                            Revised Tentative Exam Date Text
                        </label>
                        <input
                            id="corr-exam-text"
                            type="text"
                            bind:value={corrigendumForm.tentative_exam_date_text}
                            placeholder="e.g. Rescheduled to 15-20 December 2026"
                            class="w-full px-2.5 py-1.5 text-xs rounded-md border bg-background text-foreground focus:outline-hidden"
                        />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    onclick={() => (isCorrigendumModalOpen = false)}
                    class="px-4 py-2 text-xs font-medium rounded-lg border bg-background hover:bg-muted/50 text-muted-foreground transition-all"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    onclick={submitCorrigendum}
                    disabled={isSubmitting || !corrigendumForm.parent_notice_id}
                    class="px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all disabled:opacity-50"
                >
                    Apply Corrigendum Patch
                </button>
            </div>
        </div>
    </div>
{/if}

<!-- Merge Duplicate Modal -->
{#if isMergeModalOpen}
    <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-card text-card-foreground rounded-xl border shadow-lg max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-foreground">Merge Duplicate Notice</h3>
                <button
                    type="button"
                    onclick={() => (isMergeModalOpen = false)}
                    class="p-1 rounded hover:bg-muted text-muted-foreground hover:text-foreground"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <p class="text-xs text-muted-foreground leading-relaxed">
                Select the canonical master notice. This notice will be marked as a duplicate and referenced to the master for auditing.
            </p>

            <div class="space-y-3">
                <div>
                    <label for="canonical-notice-select" class="text-xs font-semibold text-foreground block mb-1">
                        Canonical Master Notice <span class="text-destructive">*</span>
                    </label>
                    {#if candidateParentNotices.length > 0}
                        <select
                            id="canonical-notice-select"
                            bind:value={mergeForm.canonical_notice_id}
                            class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                        >
                            {#each candidateParentNotices as parent (parent.id)}
                                <option value={parent.id}>
                                    {parent.reference_number ? `[${parent.reference_number}] ` : ''}{parent.title} ({parent.total_vacancies} posts)
                                </option>
                            {/each}
                        </select>
                    {:else}
                        <input
                            id="canonical-notice-select"
                            type="text"
                            bind:value={mergeForm.canonical_notice_id}
                            placeholder="Enter Master Notice ULID..."
                            class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 font-mono"
                        />
                    {/if}
                </div>

                <div>
                    <label for="merge-notes" class="text-xs font-medium text-muted-foreground block mb-1">
                        Merge Reason & Reference Notes
                    </label>
                    <textarea
                        id="merge-notes"
                        rows={3}
                        bind:value={mergeForm.notes}
                        placeholder="e.g. Duplicate publication found via employment newspaper crawl..."
                        class="w-full px-3 py-2 text-xs rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                    ></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    onclick={() => (isMergeModalOpen = false)}
                    class="px-4 py-2 text-xs font-medium rounded-lg border bg-background hover:bg-muted/50 text-muted-foreground transition-all"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    onclick={submitMerge}
                    disabled={isSubmitting || !mergeForm.canonical_notice_id}
                    class="px-4 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all disabled:opacity-50"
                >
                    Confirm Merge
                </button>
            </div>
        </div>
    </div>
{/if}
