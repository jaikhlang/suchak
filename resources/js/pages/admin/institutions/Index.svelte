<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: "Dashboard", href: "/dashboard" },
            { title: "Taxonomy", href: "/admin/states" },
            { title: "Institutions", href: "/admin/institutions" },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Link, router } from "@inertiajs/svelte";
    import Building2 from "@lucide/svelte/icons/building-2";
    import Edit from "@lucide/svelte/icons/pencil";
    import ExternalLink from "@lucide/svelte/icons/external-link";
    import Filter from "@lucide/svelte/icons/filter";
    import Plus from "@lucide/svelte/icons/plus";
    import Search from "@lucide/svelte/icons/search";
    import ShieldAlert from "@lucide/svelte/icons/shield-alert";
    import ShieldCheck from "@lucide/svelte/icons/shield-check";
    import Trash2 from "@lucide/svelte/icons/trash-2";

    interface AliasItem {
        id: string;
        alias: string;
        locale: string;
        is_primary: boolean;
    }

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
        institution_type: string;
        website_url: string;
        official_domain: string;
        is_verified: boolean;
        is_active: boolean;
        state: StateItem | null;
        aliases: AliasItem[];
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface PaginatedData {
        data: InstitutionItem[];
        current_page: number;
        last_page: number;
        total: number;
        links: PaginationLink[];
    }

    interface Props {
        institutions: PaginatedData;
        filters: {
            search: string | null;
            type: string | null;
            state_id: string | null;
            verified: string | null;
        };
        types: { value: string; label: string }[];
        states: StateItem[];
    }

    let { institutions, filters, types, states }: Props = $props();

    let searchTerm = $state(filters.search ?? "");
    let selectedType = $state(filters.type ?? "");
    let selectedState = $state(filters.state_id ?? "");
    let selectedVerified = $state(filters.verified ?? "");

    function handleFilter() {
        router.get(
            "/admin/institutions",
            {
                search: searchTerm || undefined,
                type: selectedType || undefined,
                state_id: selectedState || undefined,
                verified: selectedVerified || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    function resetFilters() {
        searchTerm = "";
        selectedType = "";
        selectedState = "";
        selectedVerified = "";
        handleFilter();
    }

    function deleteInstitution(id: string, name: string) {
        if (
            confirm(
                `Are you sure you want to delete "${name}"? This action cannot be undone.`,
            )
        ) {
            router.delete(`/admin/institutions/${id}`);
        }
    }
</script>

<AppHead title="Recruiting Institutions" />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
    <!-- Header -->
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b pb-5"
    >
        <div>
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary"
                >
                    Authority Directory
                </span>
                <span class="text-xs text-muted-foreground"
                    >Total: {institutions.total}</span
                >
            </div>
            <h1
                class="text-2xl md:text-3xl font-bold tracking-tight text-foreground mt-1"
            >
                Recruiting Authorities & Commissions
            </h1>
            <p class="text-sm text-muted-foreground mt-1">
                Authoritative Central, State, PSU, and Educational Recruitment
                Bodies.
            </p>
        </div>

        <div>
            <Link
                href="/admin/institutions/create"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all"
            >
                <Plus class="w-4 h-4" />
                Add Institution
            </Link>
        </div>
    </div>

    <!-- Search & Filters -->
    <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 p-4 rounded-xl border bg-card/60 shadow-xs"
    >
        <!-- Search -->
        <div class="relative lg:col-span-2">
            <Search
                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"
            />
            <input
                type="text"
                bind:value={searchTerm}
                oninput={handleFilter}
                placeholder="Search name, acronym, domain, alias..."
                class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            />
        </div>

        <!-- Type Filter -->
        <div>
            <select
                bind:value={selectedType}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Institution Types</option>
                {#each types as type}
                    <option value={type.value}>{type.label}</option>
                {/each}
            </select>
        </div>

        <!-- State Filter -->
        <div>
            <select
                bind:value={selectedState}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Jurisdictions (Central & State)</option>
                {#each states as state}
                    <option value={state.id}
                        >{state.name} ({state.iso_code})</option
                    >
                {/each}
            </select>
        </div>

        <!-- Verified Filter -->
        <div>
            <select
                bind:value={selectedVerified}
                onchange={handleFilter}
                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            >
                <option value="">All Verification Statuses</option>
                <option value="true">Verified Official Only</option>
                <option value="false">Unverified / Pending</option>
            </select>
        </div>
    </div>

    <!-- Data Table -->
    <div
        class="rounded-xl border bg-card text-card-foreground shadow-xs overflow-hidden"
    >
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-muted/50 border-b text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                >
                    <tr>
                        <th class="px-4 py-3">Authority / Acronym</th>
                        <th class="px-4 py-3">Type & Jurisdiction</th>
                        <th class="px-4 py-3">Official Domain</th>
                        <th class="px-4 py-3">Aliases (Entity Resolution)</th>
                        <th class="px-4 py-3">Trust Standing</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    {#each institutions.data as inst (inst.id)}
                        <tr class="hover:bg-muted/30 transition-colors">
                            <!-- Authority Name -->
                            <td class="px-4 py-3">
                                <div
                                    class="font-semibold text-foreground flex items-center gap-2"
                                >
                                    <span>{inst.name}</span>
                                    {#if inst.short_name}
                                        <span
                                            class="inline-flex px-1.5 py-0.2 rounded text-[11px] font-mono font-bold bg-muted text-muted-foreground"
                                        >
                                            {inst.short_name}
                                        </span>
                                    {/if}
                                </div>
                                <div
                                    class="text-xs font-mono text-muted-foreground mt-0.5"
                                >
                                    /{inst.slug}
                                </div>
                            </td>

                            <!-- Type & Jurisdiction -->
                            <td class="px-4 py-3">
                                <div
                                    class="text-xs font-medium text-foreground"
                                >
                                    {types.find(
                                        (t) =>
                                            t.value === inst.institution_type,
                                    )?.label ?? inst.institution_type}
                                </div>
                                <div
                                    class="text-[11px] text-muted-foreground mt-0.5"
                                >
                                    {inst.state
                                        ? `${inst.state.name} (${inst.state.iso_code})`
                                        : "National / Central Authority"}
                                </div>
                            </td>

                            <!-- Official Domain -->
                            <td class="px-4 py-3">
                                <a
                                    href={inst.website_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 text-xs font-mono text-primary hover:underline"
                                >
                                    <span>{inst.official_domain}</span>
                                    <ExternalLink class="w-3 h-3 shrink-0" />
                                </a>
                            </td>

                            <!-- Aliases -->
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    {#each inst.aliases.slice(0, 3) as alias}
                                        <span
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-muted text-muted-foreground border"
                                        >
                                            {alias.alias}
                                        </span>
                                    {/each}
                                    {#if inst.aliases.length > 3}
                                        <span
                                            class="text-[10px] text-muted-foreground font-medium self-center"
                                        >
                                            +{inst.aliases.length - 3} more
                                        </span>
                                    {/if}
                                </div>
                            </td>

                            <!-- Verification Status -->
                            <td class="px-4 py-3">
                                {#if inst.is_verified}
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                                    >
                                        <ShieldCheck class="w-3 h-3" />
                                        Verified
                                    </span>
                                {:else}
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        <ShieldAlert class="w-3 h-3" />
                                        Unverified
                                    </span>
                                {/if}
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <Link
                                        href={`/admin/institutions/${inst.id}/edit`}
                                        class="p-1.5 rounded-md hover:bg-muted text-muted-foreground hover:text-foreground transition-colors"
                                        title="Edit Institution"
                                    >
                                        <Edit class="w-4 h-4" />
                                    </Link>
                                    <button
                                        type="button"
                                        onclick={() =>
                                            deleteInstitution(
                                                inst.id,
                                                inst.name,
                                            )}
                                        class="p-1.5 rounded-md hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors"
                                        title="Delete Institution"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    {:else}
                        <tr>
                            <td
                                colspan="6"
                                class="px-4 py-12 text-center text-muted-foreground"
                            >
                                <Building2
                                    class="w-8 h-8 mx-auto mb-2 opacity-50"
                                />
                                <div
                                    class="font-medium text-foreground text-sm"
                                >
                                    No institutions found
                                </div>
                                <div class="text-xs mt-1">
                                    Try clearing filters or adding a new
                                    recruiting authority.
                                </div>
                                <button
                                    type="button"
                                    onclick={resetFilters}
                                    class="mt-3 inline-flex items-center gap-1 text-xs text-primary hover:underline font-medium"
                                >
                                    Reset all filters
                                </button>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {#if institutions.last_page > 1}
            <div
                class="flex items-center justify-between px-4 py-3 border-t text-xs text-muted-foreground"
            >
                <div>
                    Showing page <span class="font-semibold text-foreground"
                        >{institutions.current_page}</span
                    >
                    of
                    <span class="font-semibold text-foreground"
                        >{institutions.last_page}</span
                    >
                </div>
                <div class="flex items-center gap-1">
                    {#each institutions.links as link}
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
                            <span
                                class="px-2.5 py-1 rounded-md text-xs text-muted-foreground opacity-50 cursor-not-allowed"
                            >
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            </div>
        {/if}
    </div>
</div>
