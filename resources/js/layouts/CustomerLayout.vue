<script setup lang="ts">
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { usePage } from '@inertiajs/vue3';
import { CreditCard, FileText, LayoutDashboard, Package, Settings } from 'lucide-vue-next';
import { computed } from 'vue';


const page = usePage();
const url = computed(() => page.url);
const user = computed(() => (page.props.auth as { user?: { name?: string } }).user);

const nav = computed(() => [
    { label: 'Übersicht', href: '/account', icon: LayoutDashboard, active: url.value === '/account' },
    { label: 'Meine Anfragen', href: '/account/requests', icon: FileText, active: url.value.startsWith('/account/requests') },
    { label: 'Meine Aufträge', href: '/account/bookings', icon: Package, active: url.value.startsWith('/account/bookings') },
    { label: 'Zahlungen', href: '/account/payments', icon: CreditCard, active: url.value.startsWith('/account/payments') },
    { label: 'Einstellungen', href: '/settings/profile', icon: Settings, active: url.value.startsWith('/settings') },
]);
</script>

<template>
    <DashboardShell
        :title="'Mein Konto'"
        :nav="nav"
        logout-route="/logout"
        :user-name="user?.name ?? 'Kunde'"
        :user-role="'Kunde'"
    >
        <slot />
    </DashboardShell>
</template>
