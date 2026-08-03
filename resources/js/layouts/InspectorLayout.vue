<script setup lang="ts">
import DashboardShell from '@/components/dashboard/DashboardShell.vue';
import { usePage } from '@inertiajs/vue3';
import { FileText, Inbox, LayoutDashboard, MapPin, Package, Receipt, Tag, User, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';


const page = usePage();
const url = computed(() => page.url);
const inspector = computed(() => (page.props.auth as { inspector?: { name?: string; company?: string } }).inspector);

const nav = computed(() => [
    { label: 'Dashboard', href: '/inspector', icon: LayoutDashboard, active: url.value === '/inspector' },
    { label: 'Anfragen', href: '/inspector/requests', icon: Inbox, active: url.value.startsWith('/inspector/requests') },
    { label: 'Meine Angebote', href: '/inspector/offers', icon: Tag, active: url.value.startsWith('/inspector/offers') },
    { label: 'Aufträge', href: '/inspector/jobs', icon: Package, active: url.value.startsWith('/inspector/jobs') },
    { label: 'Servicegebiet', href: '/inspector/service-areas', icon: MapPin, active: url.value.startsWith('/inspector/service-areas') },
    { label: 'Rechnungen', href: '/inspector/invoices', icon: Receipt, active: url.value.startsWith('/inspector/invoices') },
    { label: 'Wallet', href: '/inspector/wallet', icon: Wallet, active: url.value.startsWith('/inspector/wallet') },
    { label: 'Profil', href: '/inspector/profile', icon: User, active: url.value.startsWith('/inspector/profile') },
]);
</script>

<template>
    <DashboardShell
        :title="'Gutachter-Portal'"
        :nav="nav"
        logout-route="/inspector/logout"
        :user-name="inspector?.name ?? 'Gutachter'"
        :user-role="'Gutachter'"
    >
        <slot />
    </DashboardShell>
</template>
