<script setup lang="ts">
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { usePage } from '@inertiajs/vue3';
import { FileText, Inbox, LayoutDashboard, MapPin, Package, Tag, User, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage();
const url = computed(() => page.url);
const inspector = computed(() => (page.props.auth as { inspector?: { name?: string; company?: string } }).inspector);

const nav = computed(() => [
    { label: t('dashboard.inspector.navDashboard'), href: '/gutachter', icon: LayoutDashboard, active: url.value === '/gutachter' },
    { label: t('dashboard.inspector.navRequests'), href: '/gutachter/anfragen', icon: Inbox, active: url.value.startsWith('/gutachter/anfragen') },
    { label: t('dashboard.inspector.navOffers'), href: '/gutachter/angebote', icon: Tag, active: url.value.startsWith('/gutachter/angebote') },
    { label: t('dashboard.inspector.navBookings'), href: '/gutachter/auftraege', icon: Package, active: url.value.startsWith('/gutachter/auftraege') },
    { label: t('dashboard.inspector.navServiceArea'), href: '/gutachter/servicegebiet', icon: MapPin, active: url.value.startsWith('/gutachter/servicegebiet') },
    { label: t('dashboard.inspector.navWallet'), href: '/gutachter/wallet', icon: Wallet, active: url.value.startsWith('/gutachter/wallet') },
    { label: t('dashboard.inspector.navProfile'), href: '/gutachter/profil', icon: User, active: url.value.startsWith('/gutachter/profil') },
]);
</script>

<template>
    <DashboardShell
        :title="t('dashboard.inspector.title')"
        :nav="nav"
        logout-route="/gutachter/logout"
        :user-name="inspector?.name ?? t('dashboard.inspector.role')"
        :user-role="t('dashboard.inspector.role')"
    >
        <slot />
    </DashboardShell>
</template>
