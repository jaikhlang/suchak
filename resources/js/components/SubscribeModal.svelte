<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import Bell from '@lucide/svelte/icons/bell';
    import X from '@lucide/svelte/icons/x';
    import CheckCircle2 from '@lucide/svelte/icons/check-circle-2';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';

    interface Props {
        isOpen: boolean;
        onClose: () => void;
        defaultCategory?: string;
        defaultStateId?: string;
    }

    let { isOpen, onClose, defaultCategory = '', defaultStateId = '' }: Props = $props();

    let email = $state('');
    let category = $state(defaultCategory);
    let stateId = $state(defaultStateId);
    let isSubmitting = $state(false);
    let isSuccess = $state(false);

    function handleSubmit(e: SubmitEvent) {
        e.preventDefault();
        if (!email) return;

        isSubmitting = true;
        router.post(
            '/subscriptions',
            {
                email,
                reservation_category: category || null,
                state_id: stateId || null,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    isSuccess = true;
                    setTimeout(() => {
                        isSuccess = false;
                        onClose();
                    }, 2500);
                },
                onFinish: () => {
                    isSubmitting = false;
                },
            }
        );
    }
</script>

{#if isOpen}
    <!-- Modal Backdrop -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-background/80 backdrop-blur-xs">
        <div class="relative w-full max-w-md rounded-2xl border bg-card p-6 text-card-foreground shadow-lg animate-in fade-in zoom-in-95 duration-150">
            <!-- Close Button -->
            <button
                type="button"
                onclick={onClose}
                class="absolute right-4 top-4 rounded-lg p-1 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                aria-label="Close"
            >
                <X class="size-4" />
            </button>

            {#if isSuccess}
                <div class="py-6 text-center space-y-3">
                    <div class="flex size-12 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 mx-auto">
                        <CheckCircle2 class="size-6" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground">Alerts Activated!</h3>
                    <p class="text-xs text-muted-foreground max-w-xs mx-auto">
                        You will receive instant, evidence-grounded notifications directly to <span class="font-semibold text-foreground">{email}</span> whenever matching government gazettes are published.
                    </p>
                </div>
            {:else}
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <Bell class="size-4" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-foreground">Subscribe to Job Alerts</h3>
                            <p class="text-xs text-muted-foreground">Zero spam. Only verified official gazette notices.</p>
                        </div>
                    </div>

                    <form onsubmit={handleSubmit} class="space-y-3.5 pt-2">
                        <div>
                            <label for="sub_email" class="block text-xs font-semibold text-foreground mb-1">
                                Email Address *
                            </label>
                            <input
                                id="sub_email"
                                type="email"
                                bind:value={email}
                                placeholder="name@example.com"
                                required
                                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground placeholder:text-muted-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                            />
                        </div>

                        <div>
                            <label for="sub_cat" class="block text-xs font-semibold text-foreground mb-1">
                                Quota Category (Optional)
                            </label>
                            <select
                                id="sub_cat"
                                bind:value={category}
                                class="w-full px-3 py-2 text-sm rounded-lg border bg-background text-foreground focus:outline-hidden focus:ring-2 focus:ring-primary/40 transition-all"
                            >
                                <option value="">All Categories</option>
                                <option value="UR">Unreserved (UR / General)</option>
                                <option value="OBC_NCL">OBC (Non-Creamy Layer)</option>
                                <option value="SC">Scheduled Caste (SC)</option>
                                <option value="ST">Scheduled Tribe (ST)</option>
                                <option value="EWS">Economically Weaker Section (EWS)</option>
                                <option value="PWBD">PwBD / Divyangjan</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2 text-[11px] text-muted-foreground pt-1">
                            <ShieldCheck class="size-3.5 text-emerald-500 shrink-0" />
                            <span>Unsubscribe anytime with 1 click. Verbatim grounded data only.</span>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3">
                            <button
                                type="button"
                                onclick={onClose}
                                class="px-4 py-2 text-xs font-semibold rounded-lg border hover:bg-muted/40 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={isSubmitting}
                                class="px-5 py-2 text-xs font-semibold rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs transition-all disabled:opacity-50"
                            >
                                {isSubmitting ? 'Subscribing...' : 'Get Instant Alerts'}
                            </button>
                        </div>
                    </form>
                </div>
            {/if}
        </div>
    </div>
{/if}
