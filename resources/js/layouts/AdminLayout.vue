<script setup lang="ts">
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { usePage } from '@inertiajs/vue3';
import {
    Banknote,
    FileText,
    LayoutDashboard,
    Package,
    Percent,
    Receipt,
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
    { label: 'Anfragen', href: '/admin/requests', icon: FileText, active: url.value.startsWith('/admin/requests') },
    { label: 'Angebote', href: '/admin/offers', icon: Tag, active: url.value.startsWith('/admin/offers') },
    { label: 'Aufträge', href: '/admin/bookings', icon: Package, active: url.value.startsWith('/admin/bookings') },
    { label: 'Zahlungen', href: '/admin/payments', icon: Banknote, active: url.value.startsWith('/admin/payments') },
    { label: 'Provisionen', href: '/admin/commissions', icon: Percent, active: url.value.startsWith('/admin/commissions') },
    { label: 'Rechnungen', href: '/admin/invoices', icon: Receipt, active: url.value.startsWith('/admin/invoices') },
    { label: 'Gutachter', href: '/admin/inspectors', icon: Users, active: url.value.startsWith('/admin/inspectors') },
    { label: 'Wallets', href: '/admin/wallets', icon: Wallet, active: url.value.startsWith('/admin/wallets') },
    { label: 'Auszahlungen', href: '/admin/payouts', icon: Banknote, active: url.value.startsWith('/admin/payouts') },
    { label: 'Kunden', href: '/admin/customers', icon: Users, active: url.value.startsWith('/admin/customers') },
    { label: 'Dienstleistungen', href: '/admin/services', icon: Tag, active: url.value.startsWith('/admin/services') },
    { label: 'Einstellungen', href: '/admin/settings', icon: Settings, active: url.value.startsWith('/admin/settings') },
    { label: 'Protokolle', href: '/admin/logs', icon: ScrollText, active: url.value.startsWith('/admin/logs') },
]);
</script>

<template>
    <DashboardShell
        :title="'Admin-Panel'"
        :nav="nav"
        logout-route="/admin/logout"
        :user-name="admin?.name ?? 'Administrator'"
        :user-role="'Administrator'"
    >
        <slot />
    </DashboardShell>
</template>
