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
    { label: t('dashboard.customer.navOverview'), href: '/konto', icon: LayoutDashboard, active: url.value === '/konto' },
    { label: t('dashboard.customer.navRequests'), href: '/konto/anfragen', icon: FileText, active: url.value.startsWith('/konto/anfragen') },
    { label: t('dashboard.customer.navBookings'), href: '/konto/auftraege', icon: Package, active: url.value.startsWith('/konto/auftraege') },
    { label: t('dashboard.customer.navPayments'), href: '/konto/zahlungen', icon: CreditCard, active: url.value.startsWith('/konto/zahlungen') },
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
