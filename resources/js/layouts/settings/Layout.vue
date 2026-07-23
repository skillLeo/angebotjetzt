<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShieldCheck, UserRound } from 'lucide-vue-next';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';


const sidebarNavItems = computed<NavItem[]>(() => [
    {
        title: 'Profil',
        href: editProfile(),
        icon: UserRound,
    },
    {
        title: 'Sicherheit',
        href: editSecurity(),
        icon: ShieldCheck,
    },
]);

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <h1 class="font-display text-2xl font-bold text-navy-700">{{ 'Einstellungen' }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ 'Profil- und Kontoeinstellungen verwalten' }}</p>
        </div>

        <nav class="mb-6 flex flex-wrap gap-2" aria-label="Settings">
            <Link
                v-for="item in sidebarNavItems"
                :key="toUrl(item.href)"
                :href="item.href"
                class="inline-flex items-center gap-2 rounded-pill px-4 py-2 text-sm font-bold transition"
                :class="
                    isCurrentOrParentUrl(item.href)
                        ? 'bg-navy-700 text-white'
                        : 'border border-ink-100 bg-white text-ink-700 hover:border-navy-500 hover:text-navy-700'
                "
            >
                <component :is="item.icon" :size="16" aria-hidden="true" />
                {{ item.title }}
            </Link>
        </nav>

        <div class="rounded-card border border-ink-100 bg-white p-6 shadow-card sm:p-8">
            <slot />
        </div>
    </div>
</template>
