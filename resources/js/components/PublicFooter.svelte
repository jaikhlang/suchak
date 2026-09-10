<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import FileCode from '@lucide/svelte/icons/file-code';
    import Rss from '@lucide/svelte/icons/rss';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
</script>

<footer class="border-t bg-card text-card-foreground">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
            <!-- Brand & Mission -->
            <div class="md:col-span-2 space-y-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-xs">
                        <AppLogoIcon class="size-4 fill-current" />
                    </div>
                    <span class="text-lg font-bold tracking-tight text-foreground">Project Suchak (सूचक)</span>
                </div>
                <p class="text-sm text-muted-foreground leading-relaxed max-w-md">
                    Authoritative, open Indian government recruitment discovery platform.
                    Zero hallucination tolerance: every vacancy count, quota breakdown, and deadline is backed by verbatim gazette evidence fragments.
                </p>
                <div class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                    <ShieldCheck class="size-4" />
                    <span>Evidence-grounded parsing from official .gov.in and .nic.in sources.</span>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-sm font-semibold tracking-wide text-foreground uppercase">Navigation</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li>
                        <Link href="/recruitment" class="text-muted-foreground hover:text-foreground transition-colors">
                            All Recruitment Notices
                        </Link>
                    </li>
                    <li>
                        <Link href="/recruitment?deadline=active" class="text-muted-foreground hover:text-foreground transition-colors">
                            Active Application Deadlines
                        </Link>
                    </li>
                    <li>
                        <Link href="/recruitment?institution_type=central_commission" class="text-muted-foreground hover:text-foreground transition-colors">
                            Central Commissions (UPSC, SSC)
                        </Link>
                    </li>
                    <li>
                        <Link href="/recruitment?institution_type=state_psc" class="text-muted-foreground hover:text-foreground transition-colors">
                            State PSC Notifications
                        </Link>
                    </li>
                </ul>
            </div>

            <!-- Technical Feeds & Transparency -->
            <div>
                <h3 class="text-sm font-semibold tracking-wide text-foreground uppercase">Feeds & Machine Index</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li>
                        <a href="/feeds/latest.xml" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-muted-foreground hover:text-foreground transition-colors">
                            <Rss class="size-3.5 text-amber-500" />
                            <span>RSS 2.0 XML Feed</span>
                        </a>
                    </li>
                    <li>
                        <a href="/sitemap.xml" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-muted-foreground hover:text-foreground transition-colors">
                            <FileCode class="size-3.5 text-blue-500" />
                            <span>Googlebot XML Sitemap</span>
                        </a>
                    </li>
                    <li>
                        <Link href="/login" class="text-muted-foreground hover:text-foreground transition-colors">
                            Officer Moderation Portal
                        </Link>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-8 border-t pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
            <p>© {new Date().getFullYear()} Project Suchak (सूचक). Public Service Recruitment Information System.</p>
            <p>Non-commercial public service initiative for Indian aspirants.</p>
        </div>
    </div>
</footer>
