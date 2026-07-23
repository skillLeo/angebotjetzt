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
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage();
const url = computed(() => page.url);
const admin = computed(() => (page.props.auth as { admin?: { name?: string } }).admin);

const nav = computed(() => [
    { label: t('dashboard.admin.navDashboard'), href: '/admin', icon: LayoutDashboard, active: url.value === '/admin' },
    { label: t('dashboard.admin.navRequests'), href: '/admin/requests', icon: FileText, active: url.value.startsWith('/admin/requests') },
    { label: t('dashboard.admin.navOffers'), href: '/admin/offers', icon: Tag, active: url.value.startsWith('/admin/offers') },
    { label: t('dashboard.admin.navBookings'), href: '/admin/bookings', icon: Package, active: url.value.startsWith('/admin/bookings') },
    { label: t('dashboard.admin.navPayments'), href: '/admin/payments', icon: Banknote, active: url.value.startsWith('/admin/payments') },
    { label: t('dashboard.admin.navCommissions'), href: '/admin/commissions', icon: Percent, active: url.value.startsWith('/admin/commissions') },
    { label: t('dashboard.admin.navInspectors'), href: '/admin/inspectors', icon: Users, active: url.value.startsWith('/admin/inspectors') },
    { label: t('dashboard.admin.navWallets'), href: '/admin/wallets', icon: Wallet, active: url.value.startsWith('/admin/wallets') },
    { label: t('dashboard.admin.navPayouts'), href: '/admin/payouts', icon: Banknote, active: url.value.startsWith('/admin/payouts') },
    { label: t('dashboard.admin.navCustomers'), href: '/admin/customers', icon: Users, active: url.value.startsWith('/admin/customers') },
    { label: t('dashboard.admin.navServices'), href: '/admin/services', icon: Tag, active: url.value.startsWith('/admin/services') },
    { label: t('dashboard.admin.navSettings'), href: '/admin/settings', icon: Settings, active: url.value.startsWith('/admin/settings') },
    { label: t('dashboard.admin.navLogs'), href: '/admin/logs', icon: ScrollText, active: url.value.startsWith('/admin/logs') },
]);
</script>

<template>
    <DashboardShell
        :title="t('dashboard.admin.title')"
        :nav="nav"
        logout-route="/admin/logout"
        :user-name="admin?.name ?? t('dashboard.admin.role')"
        :user-role="t('dashboard.admin.role')"
    >
        <slot />
    </DashboardShell>
</template>
