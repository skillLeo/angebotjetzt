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
    { label: t('dashboard.admin.navRequests'), href: '/admin/anfragen', icon: FileText, active: url.value.startsWith('/admin/anfragen') },
    { label: t('dashboard.admin.navOffers'), href: '/admin/angebote', icon: Tag, active: url.value.startsWith('/admin/angebote') },
    { label: t('dashboard.admin.navBookings'), href: '/admin/auftraege', icon: Package, active: url.value.startsWith('/admin/auftraege') },
    { label: t('dashboard.admin.navPayments'), href: '/admin/zahlungen', icon: Banknote, active: url.value.startsWith('/admin/zahlungen') },
    { label: t('dashboard.admin.navCommissions'), href: '/admin/provisionen', icon: Percent, active: url.value.startsWith('/admin/provisionen') },
    { label: t('dashboard.admin.navInspectors'), href: '/admin/gutachter', icon: Users, active: url.value.startsWith('/admin/gutachter') },
    { label: t('dashboard.admin.navWallets'), href: '/admin/wallets', icon: Wallet, active: url.value.startsWith('/admin/wallets') },
    { label: t('dashboard.admin.navPayouts'), href: '/admin/auszahlungen', icon: Banknote, active: url.value.startsWith('/admin/auszahlungen') },
    { label: t('dashboard.admin.navCustomers'), href: '/admin/kunden', icon: Users, active: url.value.startsWith('/admin/kunden') },
    { label: t('dashboard.admin.navServices'), href: '/admin/dienstleistungen', icon: Tag, active: url.value.startsWith('/admin/dienstleistungen') },
    { label: t('dashboard.admin.navSettings'), href: '/admin/einstellungen', icon: Settings, active: url.value.startsWith('/admin/einstellungen') },
    { label: t('dashboard.admin.navLogs'), href: '/admin/protokolle', icon: ScrollText, active: url.value.startsWith('/admin/protokolle') },
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
