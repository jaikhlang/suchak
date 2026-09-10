<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import Rss from '@lucide/svelte/icons/rss';
    import Search from '@lucide/svelte/icons/search';
    import Menu from '@lucide/svelte/icons/menu';
    import X from '@lucide/svelte/icons/x';
    import Building2 from '@lucide/svelte/icons/building-2';
    import FileText from '@lucide/svelte/icons/file-text';
    import Bell from '@lucide/svelte/icons/bell';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import SubscribeModal from '@/components/SubscribeModal.svelte';

    const auth = $derived(page.props.auth);
    let mobileMenuOpen = $state(false);
    let subscribeModalOpen = $state(false);
</script>

<header class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur-md supports-backdrop-filter:bg-background/60">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Logo & Branding -->
        <div class="flex items-center gap-6">
            <Link href="/" class="flex items-center gap-3 transition-opacity hover:opacity-90">
                <div class="flex size-9 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-xs">
                    <AppLogoIcon class="size-5 fill-current" />
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-lg font-bold tracking-tight text-foreground">Suchak</span>
                        <span class="rounded bg-primary/10 px-1.5 py-0.5 text-xs font-semibold text-primary">सूचक</span>
                    </div>
                    <span class="hidden text-[10px] font-medium tracking-wide text-muted-foreground uppercase sm:inline-block">
                        Verified Indian Recruitment Discovery
                    </span>
                </div>
            </Link>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
            <Link
                href="/recruitment"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-foreground/80 hover:bg-muted/60 hover:text-foreground transition-all"
            >
                <Search class="size-4 text-muted-foreground" />
                <span>Browse Notices</span>
            </Link>

            <Link
                href="/recruitment?deadline=active"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-foreground/80 hover:bg-muted/60 hover:text-foreground transition-all"
            >
                <FileText class="size-4 text-muted-foreground" />
                <span>Active Deadlines</span>
            </Link>

            <a
                href="/feeds/latest.xml"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-foreground/80 hover:bg-muted/60 hover:text-foreground transition-all"
                title="RSS 2.0 Feed"
            >
                <Rss class="size-4 text-amber-500" />
                <span>RSS Feed</span>
            <button
                type="button"
                onclick={() => (subscribeModalOpen = true)}
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-foreground/80 hover:bg-muted/60 hover:text-foreground transition-all cursor-pointer"
            >
                <Bell class="size-4 text-primary" />
                <span>Get Alerts</span>
            </button>
        </nav>

        <!-- Right: Auth actions -->
        <div class="hidden md:flex items-center gap-3">
            <button
                type="button"
                onclick={() => (subscribeModalOpen = true)}
                class="inline-flex items-center gap-1.5 rounded-lg border border-primary/30 bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary shadow-2xs hover:bg-primary/20 transition-all cursor-pointer"
            >
                <Bell class="size-3.5" />
                <span>Job Alerts</span>
            </button>

            {#if auth?.user}
                {#if auth.user.role === 'admin' || auth.user.role === 'moderator'}
                    <Link
                        href="/admin/moderation"
                        class="inline-flex items-center gap-1.5 rounded-lg border bg-card px-3 py-1.5 text-xs font-semibold text-foreground shadow-xs hover:bg-muted/40 transition-all"
                    >
                        <ShieldCheck class="size-3.5 text-emerald-500" />
                        <span>Moderation Queue</span>
                    </Link>
                {/if}
                <Link
                    href="/dashboard"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3.5 py-1.5 text-xs font-semibold text-primary-foreground shadow-xs hover:bg-primary/90 transition-all"
                >
                    Dashboard
                </Link>
            {:else}
                <Link
                    href="/login"
                    class="inline-flex items-center rounded-lg border bg-card px-3.5 py-1.5 text-xs font-semibold text-foreground shadow-xs hover:bg-muted/40 transition-all"
                >
                    Officer Sign In
                </Link>
            {/if}
        </div>

        <!-- Mobile Menu Toggle Button -->
        <button
            type="button"
            onclick={() => (mobileMenuOpen = !mobileMenuOpen)}
            class="inline-flex md:hidden items-center justify-center rounded-lg p-2 text-muted-foreground hover:bg-muted/60 hover:text-foreground"
            aria-label="Toggle navigation menu"
        >
            {#if mobileMenuOpen}
                <X class="size-5" />
            {:else}
                <Menu class="size-5" />
            {/if}
        </button>
    </div>

    <!-- Mobile Dropdown Menu -->
    {#if mobileMenuOpen}
        <div class="border-b bg-card px-4 py-4 md:hidden shadow-xs space-y-2">
            <Link
                href="/recruitment"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-foreground hover:bg-muted/60"
                onclick={() => (mobileMenuOpen = false)}
            >
                <Search class="size-4 text-muted-foreground" />
                Browse All Notices
            </Link>
            <Link
                href="/recruitment?deadline=active"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-foreground hover:bg-muted/60"
                onclick={() => (mobileMenuOpen = false)}
            >
                <FileText class="size-4 text-muted-foreground" />
                Active Deadlines
            </Link>
            <a
                href="/feeds/latest.xml"
                target="_blank"
                rel="noopener noreferrer"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-foreground hover:bg-muted/60"
            >
                <Rss class="size-4 text-amber-500" />
                RSS 2.0 Notification Feed
            </a>
            <div class="border-t pt-3 mt-2">
                {#if auth?.user}
                    <Link
                        href="/dashboard"
                        class="flex w-full items-center justify-center rounded-lg bg-primary py-2 text-sm font-semibold text-primary-foreground"
                    >
                        Go to Dashboard
                    </Link>
                {:else}
                    <Link
                        href="/login"
                        class="flex w-full items-center justify-center rounded-lg border bg-background py-2 text-sm font-semibold text-foreground"
                    >
                        Officer / Admin Sign In
                    </Link>
                {/if}
            </div>
        </div>
    {/if}
</header>

<SubscribeModal isOpen={subscribeModalOpen} onClose={() => (subscribeModalOpen = false)} />
