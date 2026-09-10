<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import Building2 from '@lucide/svelte/icons/building-2';
    import FileText from '@lucide/svelte/icons/file-text';
    import FolderGit2 from '@lucide/svelte/icons/folder-git-2';
    import Globe from '@lucide/svelte/icons/globe';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: 'Moderation Queue',
            href: '/admin/moderation',
            icon: ShieldCheck,
        },
        {
            title: 'Notices & Corrigenda',
            href: '/admin/notices',
            icon: FileText,
        },
        {
            title: 'Sources & Crawlers',
            href: '/admin/sources',
            icon: Globe,
        },
        {
            title: 'Institutions',
            href: '/admin/institutions',
            icon: Building2,
        },
        {
            title: 'States & UTs',
            href: '/admin/states',
            icon: MapPin,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/svelte-starter-kit',
            icon: FolderGit2,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits#svelte',
            icon: BookOpen,
        },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
