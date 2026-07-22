<script setup lang="ts">
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { usePage } from '@inertiajs/vue3';
import {
    Banknote,
    FileText,
    LayoutDashboard,
    Package,
    Percent,
    ScrollText,
    Settings,
    Tag,
    Users,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const url = computed(() => page.url);
const admin = computed(() => (page.props.auth as { admin?: { name?: string } }).admin);

const nav = computed(() => [
    { label: 'Dashboard', href: '/admin', icon: LayoutDashboard, active: url.value === '/admin' },
    { label: 'Anfragen', href: '/admin/anfragen', icon: FileText, active: url.value.startsWith('/admin/anfragen') },
    { label: 'Angebote', href: '/admin/angebote', icon: Tag, active: url.value.startsWith('/admin/angebote') },
    { label: 'Aufträge', href: '/admin/auftraege', icon: Package, active: url.value.startsWith('/admin/auftraege') },
    { label: 'Zahlungen', href: '/admin/zahlungen', icon: Banknote, active: url.value.startsWith('/admin/zahlungen') },
    { label: 'Provisionen', href: '/admin/provisionen', icon: Percent, active: url.value.startsWith('/admin/provisionen') },
    { label: 'Gutachter', href: '/admin/gutachter', icon: Users, active: url.value.startsWith('/admin/gutachter') },
    { label: 'Wallets', href: '/admin/wallets', icon: Wallet, active: url.value.startsWith('/admin/wallets') },
    { label: 'Auszahlungen', href: '/admin/auszahlungen', icon: Banknote, active: url.value.startsWith('/admin/auszahlungen') },
    { label: 'Kunden', href: '/admin/kunden', icon: Users, active: url.value.startsWith('/admin/kunden') },
    { label: 'Dienstleistungen', href: '/admin/dienstleistungen', icon: Tag, active: url.value.startsWith('/admin/dienstleistungen') },
    { label: 'Einstellungen', href: '/admin/einstellungen', icon: Settings, active: url.value.startsWith('/admin/einstellungen') },
    { label: 'Protokolle', href: '/admin/protokolle', icon: ScrollText, active: url.value.startsWith('/admin/protokolle') },
]);
</script>

<template>
    <DashboardShell
        title="Admin-Panel"
        :nav="nav"
        logout-route="/admin/logout"
        :user-name="admin?.name ?? 'Admin'"
        user-role="Administrator"
    >
        <slot />
    </DashboardShell>
</template>
