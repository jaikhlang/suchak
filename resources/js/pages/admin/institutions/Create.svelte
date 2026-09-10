<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Taxonomy', href: '/admin/states' },
            { title: 'Institutions', href: '/admin/institutions' },
            { title: 'Add Institution', href: '/admin/institutions/create' },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Globe from '@lucide/svelte/icons/globe';
    import Plus from '@lucide/svelte/icons/plus';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Trash2 from '@lucide/svelte/icons/trash-2';

    interface StateItem {
        id: string;
        name: string;
        iso_code: string;
    }

    interface ParentItem {
        id: string;
        name: string;
        short_name: string | null;
    }

    interface Props {
        states: StateItem[];
        parents: ParentItem[];
        types: { value: string; label: string }[];
        errors?: Record<string, string>;
    }

    let { states, parents, types, errors = {} }: Props = $props();

    let form = $state({
        name: '',
        short_name: '',
        slug: '',
        institution_type: 'central_gov',
        parent_id: '',
        state_id: '',
        website_url: '',
        official_domain: '',
        is_verified: true,
        is_active: true,
        aliases: [
            { alias: '', locale: 'en', is_primary: true },
            { alias: '', locale: 'hi', is_primary: false },
        ],
    });

    let isSubmitting = $state(false);

    function autoSlug() {
        if (!form.slug || form.slug === '') {
            form.slug = form.name
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    }

    function autoDomain() {
        if (form.website_url && (!form.official_domain || form.official_domain === '')) {
            try {
                const url = new URL(form.website_url.startsWith('http') ? form.website_url : `https://${form.website_url}`);
                form.official_domain = url.hostname.replace(/^www\./, '').toLowerCase();
            } catch {
                // Ignore parse errors while user is typing
            }
        }
    }

    function addAlias() {
        form.aliases = [...form.aliases, { alias: '', locale: 'hi', is_primary: false }];
    }

    function removeAlias(index: number) {
        form.aliases = form.aliases.filter((_, i) => i !== index);
    }

    function handleSubmit(e: Event) {
        e.preventDefault();
        isSubmitting = true;

        const payload = {
            ...form,
            parent_id: form.parent_id || null,
            state_id: form.state_id || null,
            aliases: form.aliases.filter(a => a.alias.trim() !== ''),
        };

        router.post('/admin/institutions', payload, {
            onFinish: () => {
                isSubmitting = false;
            },
        });
    }
</script>

<AppHead title="Add Recruiting Institution" />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-4xl mx-auto w-full">
    <!-- Header -->
    <div class="flex items-center justify-between border-b pb-4">
        <div class="flex items-center gap-3">
            <Link
                href="/admin/institutions"
                class="p-2 rounded-lg border hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
            >
                <ArrowLeft class="w-4 h-4" />
            </Link>
            <div>
                <h1 class="text-xl md:text-2xl font-bold tracking-tight text-foreground">
                    Add New Recruiting Authority
                </h1>
                <p class="text-xs text-muted-foreground mt-0.5">
                    Register an authoritative government commission, public board, or testing agency.
                </p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <form onsubmit={handleSubmit} class="space-y-6">
        <!-- Basic Info Card -->
        <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-xs space-y-4">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                <Building2 class="w-4 h-4 text-primary" />
                Core Authority Identity
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Name -->
                <div class="md:col-span-2 space-y-1.5">
                    <label for="name" class="text-xs font-medium text-foreground">Official Full Name *</label>
                    <input
                        id="name"
                        type="text"
                        bind:value={form.name}
                        onblur={autoSlug}
                        placeholder="e.g. Union Public Service Commission"
                        required
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                    />
                    {#if errors.name}
                        <p class="text-xs text-destructive">{errors.name}</p>
                    {/if}
                </div>

                <!-- Short Name / Acronym -->
                <div class="space-y-1.5">
                    <label for="short_name" class="text-xs font-medium text-foreground">Acronym / Short Name</label>
                    <input
                        id="short_name"
                        type="text"
                        bind:value={form.short_name}
                        placeholder="e.g. UPSC"
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 font-mono uppercase"
                    />
                    {#if errors.short_name}
                        <p class="text-xs text-destructive">{errors.short_name}</p>
                    {/if}
                </div>

                <!-- Slug -->
                <div class="space-y-1.5">
                    <label for="slug" class="text-xs font-medium text-foreground">URL Slug *</label>
                    <input
                        id="slug"
                        type="text"
                        bind:value={form.slug}
                        placeholder="e.g. upsc"
                        required
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 font-mono text-xs"
                    />
                    {#if errors.slug}
                        <p class="text-xs text-destructive">{errors.slug}</p>
                    {/if}
                </div>

                <!-- Institution Type -->
                <div class="space-y-1.5">
                    <label for="institution_type" class="text-xs font-medium text-foreground">Authority Type *</label>
                    <select
                        id="institution_type"
                        bind:value={form.institution_type}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                    >
                        {#each types as type}
                            <option value={type.value}>{type.label}</option>
                        {/each}
                    </select>
                    {#if errors.institution_type}
                        <p class="text-xs text-destructive">{errors.institution_type}</p>
                    {/if}
                </div>

                <!-- Jurisdiction State -->
                <div class="space-y-1.5">
                    <label for="state_id" class="text-xs font-medium text-foreground">Jurisdiction (State / UT)</label>
                    <select
                        id="state_id"
                        bind:value={form.state_id}
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                    >
                        <option value="">National / Central Authority</option>
                        {#each states as state}
                            <option value={state.id}>{state.name} ({state.iso_code})</option>
                        {/each}
                    </select>
                    {#if errors.state_id}
                        <p class="text-xs text-destructive">{errors.state_id}</p>
                    {/if}
                </div>
            </div>
        </div>

        <!-- Web & Domain Integrity Card -->
        <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-xs space-y-4">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                <Globe class="w-4 h-4 text-primary" />
                Web & Official Domain Whitelist
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Website URL -->
                <div class="space-y-1.5">
                    <label for="website_url" class="text-xs font-medium text-foreground">Official Website URL *</label>
                    <input
                        id="website_url"
                        type="url"
                        bind:value={form.website_url}
                        onblur={autoDomain}
                        placeholder="https://upsc.gov.in"
                        required
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 font-mono text-xs"
                    />
                    {#if errors.website_url}
                        <p class="text-xs text-destructive">{errors.website_url}</p>
                    {/if}
                </div>

                <!-- Official Domain -->
                <div class="space-y-1.5">
                    <label for="official_domain" class="text-xs font-medium text-foreground">Authoritative Domain *</label>
                    <input
                        id="official_domain"
                        type="text"
                        bind:value={form.official_domain}
                        placeholder="upsc.gov.in"
                        required
                        class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 font-mono text-xs"
                    />
                    {#if errors.official_domain}
                        <p class="text-xs text-destructive">{errors.official_domain}</p>
                    {/if}
                    <p class="text-[11px] text-muted-foreground">Must match authoritative TLD whitelist (e.g. .gov.in, .nic.in).</p>
                </div>
            </div>

            <!-- Verification & Active Switches -->
            <div class="flex flex-wrap items-center gap-6 pt-2 border-t">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        bind:checked={form.is_verified}
                        class="rounded border-input text-primary focus:ring-primary w-4 h-4"
                    />
                    <span class="text-xs font-medium text-foreground flex items-center gap-1">
                        <ShieldCheck class="w-3.5 h-3.5 text-emerald-600" />
                        Verified Official Institution
                    </span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        bind:checked={form.is_active}
                        class="rounded border-input text-primary focus:ring-primary w-4 h-4"
                    />
                    <span class="text-xs font-medium text-foreground">Active for Ingestion</span>
                </label>
            </div>
        </div>

        <!-- Multilingual Aliases Card -->
        <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">
                        Multilingual Aliases & Entity Resolution
                    </h2>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Add Hindi, Devanagari, and alternative names used in gazette circulars.
                    </p>
                </div>
                <button
                    type="button"
                    onclick={addAlias}
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border hover:bg-muted transition-colors"
                >
                    <Plus class="w-3.5 h-3.5" />
                    Add Alias
                </button>
            </div>

            <div class="space-y-2.5">
                {#each form.aliases as alias, index}
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            bind:value={alias.alias}
                            placeholder="e.g. संघ लोक सेवा आयोग"
                            class="flex-1 px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40"
                        />
                        <select
                            bind:value={alias.locale}
                            class="w-24 px-2 py-2 text-xs rounded-lg border bg-background text-foreground focus:outline-hidden"
                        >
                            <option value="en">en (English)</option>
                            <option value="hi">hi (Hindi)</option>
                            <option value="mr">mr (Marathi)</option>
                            <option value="ta">ta (Tamil)</option>
                            <option value="te">te (Telugu)</option>
                            <option value="bn">bn (Bengali)</option>
                        </select>
                        <button
                            type="button"
                            onclick={() => removeAlias(index)}
                            class="p-2 rounded-lg border hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                {/each}
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <Link
                href="/admin/institutions"
                class="px-4 py-2 text-sm font-medium rounded-lg border hover:bg-muted transition-colors"
            >
                Cancel
            </Link>
            <button
                type="submit"
                disabled={isSubmitting}
                class="px-5 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs disabled:opacity-50 transition-all"
            >
                {isSubmitting ? 'Saving...' : 'Create Institution'}
            </button>
        </div>
    </form>
</div>
