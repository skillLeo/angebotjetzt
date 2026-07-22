<script setup lang="ts">
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { usePage } from '@inertiajs/vue3';
import { FileText, Inbox, LayoutDashboard, MapPin, Package, Tag, User, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const url = computed(() => page.url);
const inspector = computed(() => (page.props.auth as { inspector?: { name?: string; company?: string } }).inspector);

const nav = computed(() => [
    { label: 'Dashboard', href: '/gutachter', icon: LayoutDashboard, active: url.value === '/gutachter' },
    { label: 'Anfragen', href: '/gutachter/anfragen', icon: Inbox, active: url.value.startsWith('/gutachter/anfragen') },
    { label: 'Meine Angebote', href: '/gutachter/angebote', icon: Tag, active: url.value.startsWith('/gutachter/angebote') },
    { label: 'Aufträge', href: '/gutachter/auftraege', icon: Package, active: url.value.startsWith('/gutachter/auftraege') },
    { label: 'Servicegebiet', href: '/gutachter/servicegebiet', icon: MapPin, active: url.value.startsWith('/gutachter/servicegebiet') },
    { label: 'Wallet', href: '/gutachter/wallet', icon: Wallet, active: url.value.startsWith('/gutachter/wallet') },
    { label: 'Profil', href: '/gutachter/profil', icon: User, active: url.value.startsWith('/gutachter/profil') },
]);
</script>

<template>
    <DashboardShell
        title="Gutachter-Portal"
        :nav="nav"
        logout-route="/gutachter/logout"
        :user-name="inspector?.name ?? 'Gutachter'"
        user-role="Gutachter"
    >
        <slot />
    </DashboardShell>
</template>
