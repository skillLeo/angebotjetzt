<script setup lang="ts">
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { usePage } from '@inertiajs/vue3';
import { CreditCard, FileText, LayoutDashboard, Package, Settings } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage();
const url = computed(() => page.url);
const user = computed(() => (page.props.auth as { user?: { name?: string } }).user);

const nav = computed(() => [
    { label: t('dashboard.customer.navOverview'), href: '/account', icon: LayoutDashboard, active: url.value === '/account' },
    { label: t('dashboard.customer.navRequests'), href: '/account/requests', icon: FileText, active: url.value.startsWith('/account/requests') },
    { label: t('dashboard.customer.navBookings'), href: '/account/bookings', icon: Package, active: url.value.startsWith('/account/bookings') },
    { label: t('dashboard.customer.navPayments'), href: '/account/payments', icon: CreditCard, active: url.value.startsWith('/account/payments') },
    { label: t('dashboard.customer.navSettings'), href: '/settings/profile', icon: Settings, active: url.value.startsWith('/settings') },
]);
</script>

<template>
    <DashboardShell
        :title="t('dashboard.customer.title')"
        :nav="nav"
        logout-route="/logout"
        :user-name="user?.name ?? t('dashboard.customer.role')"
        :user-role="t('dashboard.customer.role')"
    >
        <slot />
    </DashboardShell>
</template>
