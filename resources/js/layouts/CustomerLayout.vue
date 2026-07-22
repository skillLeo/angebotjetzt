<script setup lang="ts">
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { usePage } from '@inertiajs/vue3';
import { CreditCard, FileText, LayoutDashboard, Package, Settings } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const url = computed(() => page.url);
const user = computed(() => (page.props.auth as { user?: { name?: string } }).user);

const nav = computed(() => [
    { label: 'Übersicht', href: '/konto', icon: LayoutDashboard, active: url.value === '/konto' },
    { label: 'Meine Anfragen', href: '/konto/anfragen', icon: FileText, active: url.value.startsWith('/konto/anfragen') },
    { label: 'Meine Aufträge', href: '/konto/auftraege', icon: Package, active: url.value.startsWith('/konto/auftraege') },
    { label: 'Zahlungen', href: '/konto/zahlungen', icon: CreditCard, active: url.value.startsWith('/konto/zahlungen') },
    { label: 'Einstellungen', href: '/settings/profile', icon: Settings, active: url.value.startsWith('/settings') },
]);
</script>

<template>
    <DashboardShell
        title="Mein Konto"
        :nav="nav"
        logout-route="/logout"
        :user-name="user?.name ?? 'Kunde'"
        user-role="Kunde"
    >
        <slot />
    </DashboardShell>
</template>
