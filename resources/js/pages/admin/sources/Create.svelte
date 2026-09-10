<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Sources', href: '/admin/sources' },
            { title: 'Create Source', href: '/admin/sources/create' },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Link, router } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Save from '@lucide/svelte/icons/save';

    interface InstitutionOption {
        id: string;
        name: string;
        short_name: string | null;
    }

    interface SelectOption {
        value: string;
        label: string;
    }

    interface Props {
        institutions: InstitutionOption[];
        sourceTypes: SelectOption[];
        crawlMethods: SelectOption[];
        trustLevels: SelectOption[];
        errors?: Record<string, string>;
    }

    let { institutions, sourceTypes, crawlMethods, trustLevels, errors = {} }: Props = $props();

    let form = $state({
        institution_id: '',
        name: '',
        type: 'recruitment_portal',
        url: '',
        canonical_url: '',
        crawl_method: 'http_static',
        crawl_frequency_minutes: 120,
        status: 'active',
        trust_level: 'official_verified',
        configuration: {},
    });

    let isSubmitting = $state(false);

    function handleSubmit(e: Event) {
        e.preventDefault();
        isSubmitting = true;

        router.post('/admin/sources', form, {
            onFinish: () => {
                isSubmitting = false;
            },
        });
    }
</script>

<AppHead title="Add New Source" />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-4xl mx-auto w-full">
    <div class="flex items-center gap-3 border-b pb-4">
        <Link
            href="/admin/sources"
            class="p-2 rounded-lg border hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
        >
            <ArrowLeft class="w-4 h-4" />
        </Link>
        <div>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-foreground">
                Register Official Source
            </h1>
            <p class="text-xs text-muted-foreground mt-0.5">
                Configure a new recruitment portal or circular board for automated crawling and verification.
            </p>
        </div>
    </div>

    <form onsubmit={handleSubmit} class="space-y-6">
        <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-xs space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Institution -->
                <div class="sm:col-span-2 space-y-1.5">
                    <label for="institution_id" class="text-xs font-medium text-foreground">
                        Authority / Institution <span class="text-destructive">*</span>
                    </label>
                    <select
                        id="institution_id"
                        bind:value={form.institution_id}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                        required
                    >
                        <option value="">Select an Authority...</option>
                        {#each institutions as inst}
                            <option value={inst.id}>{inst.name} {inst.short_name ? `(${inst.short_name})` : ''}</option>
                        {/each}
                    </select>
                    <InputError message={errors.institution_id} class="mt-1" />
                </div>

                <!-- Source Name -->
                <div class="sm:col-span-2 space-y-1.5">
                    <label for="name" class="text-xs font-medium text-foreground">
                        Source Name <span class="text-destructive">*</span>
                    </label>
                    <input
                        id="name"
                        type="text"
                        bind:value={form.name}
                        placeholder="e.g. UPSC Active Examinations Noticeboard"
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                        required
                    />
                    <InputError message={errors.name} class="mt-1" />
                </div>

                <!-- URL -->
                <div class="sm:col-span-2 space-y-1.5">
                    <label for="url" class="text-xs font-medium text-foreground">
                        Portal / Noticeboard URL <span class="text-destructive">*</span>
                    </label>
                    <input
                        id="url"
                        type="url"
                        bind:value={form.url}
                        placeholder="https://upsc.gov.in/examinations/active-exams"
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                        required
                    />
                    <p class="text-xs text-muted-foreground">
                        Must use HTTPS and will be validated against strict SSRF constraints (private IP and cloud metadata access blocked).
                    </p>
                    <InputError message={errors.url} class="mt-1" />
                </div>

                <!-- Source Type -->
                <div class="space-y-1.5">
                    <label for="type" class="text-xs font-medium text-foreground">
                        Source Type <span class="text-destructive">*</span>
                    </label>
                    <select
                        id="type"
                        bind:value={form.type}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        {#each sourceTypes as st}
                            <option value={st.value}>{st.label}</option>
                        {/each}
                    </select>
                    <InputError message={errors.type} class="mt-1" />
                </div>

                <!-- Crawl Method -->
                <div class="space-y-1.5">
                    <label for="crawl_method" class="text-xs font-medium text-foreground">
                        Crawl Engine <span class="text-destructive">*</span>
                    </label>
                    <select
                        id="crawl_method"
                        bind:value={form.crawl_method}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        {#each crawlMethods as cm}
                            <option value={cm.value}>{cm.label}</option>
                        {/each}
                    </select>
                    <InputError message={errors.crawl_method} class="mt-1" />
                </div>

                <!-- Frequency -->
                <div class="space-y-1.5">
                    <label for="crawl_frequency_minutes" class="text-xs font-medium text-foreground">
                        Crawl Frequency (Minutes) <span class="text-destructive">*</span>
                    </label>
                    <input
                        id="crawl_frequency_minutes"
                        type="number"
                        min="15"
                        max="10080"
                        bind:value={form.crawl_frequency_minutes}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                        required
                    />
                    <InputError message={errors.crawl_frequency_minutes} class="mt-1" />
                </div>

                <!-- Trust Level -->
                <div class="space-y-1.5">
                    <label for="trust_level" class="text-xs font-medium text-foreground">
                        Trust Level <span class="text-destructive">*</span>
                    </label>
                    <select
                        id="trust_level"
                        bind:value={form.trust_level}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        {#each trustLevels as tl}
                            <option value={tl.value}>{tl.label}</option>
                        {/each}
                    </select>
                    <InputError message={errors.trust_level} class="mt-1" />
                </div>

                <!-- Status -->
                <div class="space-y-1.5">
                    <label for="status" class="text-xs font-medium text-foreground">
                        Status <span class="text-destructive">*</span>
                    </label>
                    <select
                        id="status"
                        bind:value={form.status}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                    >
                        <option value="active">Active (Scheduled)</option>
                        <option value="paused">Paused</option>
                        <option value="disabled">Disabled</option>
                    </select>
                    <InputError message={errors.status} class="mt-1" />
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <Link
                href="/admin/sources"
                class="px-4 py-2 text-sm font-medium rounded-lg border hover:bg-muted transition-colors text-foreground"
            >
                Cancel
            </Link>
            <button
                type="submit"
                disabled={isSubmitting}
                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs disabled:opacity-50 transition-all"
            >
                <Save class="w-4 h-4" />
                {isSubmitting ? 'Saving...' : 'Register Source'}
            </button>
        </div>
    </form>
</div>
