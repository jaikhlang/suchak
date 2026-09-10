<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'States & UTs', href: '/admin/states' },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { router } from '@inertiajs/svelte';
    import Building2 from '@lucide/svelte/icons/building-2';
    import Globe from '@lucide/svelte/icons/globe';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Search from '@lucide/svelte/icons/search';

    interface StateItem {
        id: string;
        name: string;
        iso_code: string;
        type: 'state' | 'union_territory';
        capital: string | null;
        is_active: boolean;
        institutions_count: number;
        districts_count: number;
    }

    interface Props {
        states: StateItem[];
        filters: {
            search: string | null;
            type: string | null;
        };
        stats: {
            total_states: number;
            total_uts: number;
        };
    }

    let { states, filters, stats }: Props = $props();

    let searchTerm = $state(filters.search ?? '');
    let selectedType = $state(filters.type ?? '');

    function handleFilter() {
        router.get(
            '/admin/states',
            {
                search: searchTerm || undefined,
                type: selectedType || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }

    function setType(type: string) {
        selectedType = type;
        handleFilter();
    }
</script>

<AppHead title="States & Union Territories" />

<div class="flex flex-col gap-6 p-4 md:p-8 max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                    Taxonomy Layer
                </span>
                <span class="text-xs text-muted-foreground font-mono">ISO 3166-2:IN</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-foreground mt-1">
                Indian States & Union Territories
            </h1>
            <p class="text-sm text-muted-foreground mt-1">
                Authoritative geographic jurisdiction matrix for Central and State Recruitment Boards.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="px-3.5 py-2 rounded-xl bg-card border shadow-xs text-center">
                <div class="text-xs font-medium text-muted-foreground uppercase tracking-wider">States</div>
                <div class="text-xl font-bold text-foreground">{stats.total_states}</div>
            </div>
            <div class="px-3.5 py-2 rounded-xl bg-card border shadow-xs text-center">
                <div class="text-xs font-medium text-muted-foreground uppercase tracking-wider">UTs</div>
                <div class="text-xl font-bold text-foreground">{stats.total_uts}</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Search Input -->
        <div class="relative w-full sm:w-80">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <input
                type="text"
                bind:value={searchTerm}
                oninput={handleFilter}
                placeholder="Search state, ISO, or capital..."
                class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
            />
        </div>

        <!-- Type Filter Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-muted/70 rounded-lg border w-full sm:w-auto justify-center">
            <button
                type="button"
                onclick={() => setType('')}
                class="px-3 py-1.5 text-xs font-medium rounded-md transition-all {selectedType === '' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'}"
            >
                All ({stats.total_states + stats.total_uts})
            </button>
            <button
                type="button"
                onclick={() => setType('state')}
                class="px-3 py-1.5 text-xs font-medium rounded-md transition-all {selectedType === 'state' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'}"
            >
                States ({stats.total_states})
            </button>
            <button
                type="button"
                onclick={() => setType('union_territory')}
                class="px-3 py-1.5 text-xs font-medium rounded-md transition-all {selectedType === 'union_territory' ? 'bg-background text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground'}"
            >
                Union Territories ({stats.total_uts})
            </button>
        </div>
    </div>

    <!-- States Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        {#each states as state (state.id)}
            <div class="group relative flex flex-col justify-between p-4 rounded-xl border bg-card text-card-foreground shadow-xs hover:shadow-md hover:border-primary/40 transition-all">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                            {state.iso_code}
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-medium {state.type === 'state' ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'}">
                            {state.type === 'state' ? 'State' : 'Union Territory'}
                        </span>
                    </div>

                    <h2 class="text-base font-semibold text-foreground mt-2 group-hover:text-primary transition-colors">
                        {state.name}
                    </h2>

                    {#if state.capital}
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground mt-1">
                            <MapPin class="w-3.5 h-3.5 shrink-0 opacity-70" />
                            <span>{state.capital}</span>
                        </div>
                    {/if}
                </div>

                <div class="flex items-center justify-between pt-3 mt-4 border-t text-xs text-muted-foreground">
                    <div class="flex items-center gap-1">
                        <Building2 class="w-3.5 h-3.5" />
                        <span>{state.institutions_count} {state.institutions_count === 1 ? 'Institution' : 'Institutions'}</span>
                    </div>
                    <span class="inline-block w-2 h-2 rounded-full {state.is_active ? 'bg-emerald-500' : 'bg-muted'}"></span>
                </div>
            </div>
        {:else}
            <div class="col-span-full flex flex-col items-center justify-center p-12 text-center rounded-xl border border-dashed bg-muted/20">
                <Globe class="w-10 h-10 text-muted-foreground mb-3 opacity-60" />
                <h3 class="text-base font-semibold text-foreground">No states or territories found</h3>
                <p class="text-xs text-muted-foreground mt-1">Try adjusting your search keywords or active filters.</p>
            </div>
        {/each}
    </div>
</div>
