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
    { label: t('dashboard.inspector.navDashboard'), href: '/inspector', icon: LayoutDashboard, active: url.value === '/inspector' },
    { label: t('dashboard.inspector.navRequests'), href: '/inspector/requests', icon: Inbox, active: url.value.startsWith('/inspector/requests') },
    { label: t('dashboard.inspector.navOffers'), href: '/inspector/offers', icon: Tag, active: url.value.startsWith('/inspector/offers') },
    { label: t('dashboard.inspector.navBookings'), href: '/inspector/jobs', icon: Package, active: url.value.startsWith('/inspector/jobs') },
    { label: t('dashboard.inspector.navServiceArea'), href: '/inspector/service-areas', icon: MapPin, active: url.value.startsWith('/inspector/service-areas') },
    { label: t('dashboard.inspector.navWallet'), href: '/inspector/wallet', icon: Wallet, active: url.value.startsWith('/inspector/wallet') },
    { label: t('dashboard.inspector.navProfile'), href: '/inspector/profile', icon: User, active: url.value.startsWith('/inspector/profile') },
]);
</script>

<template>
    <DashboardShell
        :title="t('dashboard.inspector.title')"
        :nav="nav"
        logout-route="/inspector/logout"
        :user-name="inspector?.name ?? t('dashboard.inspector.role')"
        :user-role="t('dashboard.inspector.role')"
    >
        <slot />
    </DashboardShell>
</template>
