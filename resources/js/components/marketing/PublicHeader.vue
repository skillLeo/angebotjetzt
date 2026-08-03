<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { onClickOutside } from '@vueuse/core';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BadgeCheck,
    ChevronDown,
    ClipboardCheck,
    Flower2,
    Hammer,
    Menu,
    Monitor,
    PaintbrushVertical,
    PartyPopper,
    Shield,
    Sparkles,
    Truck,
    X,
} from 'lucide-vue-next';
import { mobileNavOpen as mobileOpen } from '@/composables/useMobileNav';
import { Motion } from 'motion-v';
import type { Component } from 'vue';
import { computed, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';

withDefaults(defineProps<{ reduced?: boolean }>(), { reduced: false });

const scrolled = ref(false);
const servicesOpen = ref(false);
const mobileServicesOpen = ref(false);
const servicesRef = useTemplateRef('servicesRef');
onClickOutside(servicesRef, () => (servicesOpen.value = false));

const page = usePage();
const isLoggedIn = computed(() => Boolean((page.props.auth as { user?: unknown } | undefined)?.user));

type NavCategory = { id: number; name: string; slug: string; icon: string; is_active: boolean };
const navCategories = computed(() => {
    const cats = (page.props.navCategories as NavCategory[] | undefined) ?? [];
    // Kfz-Gutachten is the only live category today and always leads the list,
    // regardless of its sort_order in the database.
    return [...cats].sort((a, b) => {
        if (a.slug === 'kfz-gutachten') return -1;
        if (b.slug === 'kfz-gutachten') return 1;
        return 0;
    });
});

const categoryIcons: Record<string, Component> = {
    truck: Truck,
    hammer: Hammer,
    'flower-2': Flower2,
    sparkles: Sparkles,
    paintbrush: PaintbrushVertical,
    'clipboard-check': ClipboardCheck,
    monitor: Monitor,
    'party-popper': PartyPopper,
    shield: Shield,
};

const navItems = computed(() => [
    { label: 'So funktioniert\'s', href: '/how-it-works' },
    { label: 'Für Dienstleister', href: '/for-inspectors' },
    { label: 'FAQ', href: '/faq' },
    { label: 'Über uns', href: '/about' },
]);

function onScroll() {
    scrolled.value = window.scrollY > 40;
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

// Locking scroll via `overflow: hidden` on an ancestor breaks the header's
// `position: sticky` (a well-known CSS interaction) — if the page was already
// scrolled down, the header un-sticks and scrolls away behind the open menu.
// Freezing the body in place with a negative offset instead avoids that
// entirely, and restores the exact scroll position on close.
let savedScrollY = 0;
watch(mobileOpen, (open) => {
    if (open) {
        savedScrollY = window.scrollY;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${savedScrollY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
    } else {
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        window.scrollTo(0, savedScrollY);
        mobileServicesOpen.value = false;
    }
});
</script>

<template>
    <header
        class="sticky top-0 z-50 bg-white transition-shadow duration-300"
        :class="scrolled ? 'border-b border-ink-100 shadow-card' : ''"
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:h-[72px] lg:px-8">
            <Link href="/" aria-label="AngebotJetzt Startseite">
                <BrandLogo />
            </Link>

            <div v-if="reduced" class="flex flex-1 items-center justify-center gap-2 text-sm font-semibold text-navy-700 sm:gap-2.5 sm:text-[15px]">
                <BadgeCheck :size="18" class="shrink-0 text-green-500" aria-hidden="true" />
                <span>{{ '8.000+ zufriedene Kunden in ganz Deutschland' }}</span>
            </div>

            <nav v-if="!reduced" class="hidden items-center gap-8 lg:flex" aria-label="Hauptnavigation">
                <div ref="servicesRef" class="relative">
                    <button
                        type="button"
                        class="flex items-center gap-1 text-[15px] font-medium text-ink-700 transition-colors hover:text-navy-700"
                        :aria-expanded="servicesOpen"
                        aria-haspopup="true"
                        @click="servicesOpen = !servicesOpen"
                    >
                        {{ 'Dienstleistungen' }}
                        <ChevronDown :size="16" class="transition-transform" :class="servicesOpen ? 'rotate-180' : ''" aria-hidden="true" />
                    </button>
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-2 scale-95"
                        leave-active-class="transition duration-150 ease-in"
                        leave-to-class="opacity-0 -translate-y-2 scale-95"
                    >
                        <div
                            v-if="servicesOpen"
                            class="absolute left-0 mt-3 w-80 origin-top-left overflow-hidden rounded-card border border-ink-100 bg-white p-3 shadow-lift"
                        >
                            <p class="px-3 pb-2 text-eyebrow text-ink-500">{{ 'Unsere Kategorien' }}</p>
                            <div class="flex flex-col gap-1">
                                <template v-for="cat in navCategories" :key="cat.id">
                                    <Link
                                        v-if="cat.is_active"
                                        href="/vehicle-reports"
                                        class="group flex items-center gap-3 rounded-xs px-3 py-3 text-[15px] font-medium text-ink-700 transition-colors hover:bg-sand-50 hover:text-navy-700"
                                        @click="servicesOpen = false"
                                    >
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xs bg-green-50 text-green-600 transition group-hover:scale-105">
                                            <component :is="categoryIcons[cat.icon] ?? Sparkles" :size="18" aria-hidden="true" />
                                        </span>
                                        <span class="flex-1">{{ cat.name }}</span>
                                    </Link>
                                    <span
                                        v-else
                                        class="flex cursor-not-allowed items-center gap-3 rounded-xs px-3 py-3 text-[15px] font-medium text-ink-300 select-none"
                                        aria-disabled="true"
                                    >
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xs bg-sand-50 text-ink-300">
                                            <component :is="categoryIcons[cat.icon] ?? Sparkles" :size="18" aria-hidden="true" />
                                        </span>
                                        <span class="flex-1">{{ cat.name }}</span>
                                        <span class="rounded-pill bg-sand-100 px-2 py-0.5 text-xs font-bold text-ink-400">{{ 'Demnächst' }}</span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </Transition>
                </div>
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="text-[15px] font-medium text-ink-700 transition-colors hover:text-navy-700"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div v-if="!reduced" class="hidden items-center gap-4 lg:flex">
                <Link
                    :href="isLoggedIn ? '/account' : '/login'"
                    class="text-[15px] font-medium text-ink-700 transition-colors hover:text-navy-700"
                >
                    {{ isLoggedIn ? 'Mein Konto' : 'Anmelden' }}
                </Link>
                <Link
                    href="/request/start"
                    class="rounded-pill bg-green-500 px-6 py-2.5 text-[15px] font-bold text-white shadow-card transition hover:bg-green-600"
                >
                    {{ 'Anfrage stellen' }}
                </Link>
            </div>

            <div v-if="!reduced" class="flex items-center gap-2 lg:hidden">
                <button
                    type="button"
                    class="flex h-11 w-11 items-center justify-center rounded-pill text-navy-700"
                    :aria-expanded="mobileOpen"
                    :aria-label="'Menü öffnen'"
                    @click="mobileOpen = !mobileOpen"
                >
                    <Menu v-if="!mobileOpen" :size="26" aria-hidden="true" />
                    <X v-else :size="26" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Full-screen mobile overlay -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileOpen && !reduced" class="fixed inset-0 top-16 z-40 flex flex-col bg-white lg:hidden">
                <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-6 pt-8" aria-label="Mobile Navigation">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-card px-3 py-3 font-display text-xl font-bold text-navy-700"
                        :aria-expanded="mobileServicesOpen"
                        aria-haspopup="true"
                        @click="mobileServicesOpen = !mobileServicesOpen"
                    >
                        {{ 'Dienstleistungen' }}
                        <ChevronDown
                            :size="22"
                            class="transition-transform duration-200"
                            :class="mobileServicesOpen ? 'rotate-180' : ''"
                            aria-hidden="true"
                        />
                    </button>
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out overflow-hidden"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-[640px]"
                        leave-active-class="transition-all duration-150 ease-in overflow-hidden"
                        leave-from-class="opacity-100 max-h-[640px]"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div v-if="mobileServicesOpen" class="flex flex-col gap-1 pb-1">
                            <template v-for="cat in navCategories" :key="cat.id">
                                <Link
                                    v-if="cat.is_active"
                                    href="/vehicle-reports"
                                    class="flex items-center gap-3 rounded-card px-3 py-3 pl-4 font-display text-lg font-bold text-navy-700"
                                    @click="mobileOpen = false"
                                >
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xs bg-green-50 text-green-600">
                                        <component :is="categoryIcons[cat.icon] ?? Sparkles" :size="18" aria-hidden="true" />
                                    </span>
                                    {{ cat.name }}
                                </Link>
                                <span
                                    v-else
                                    class="flex cursor-not-allowed items-center gap-3 rounded-card px-3 py-3 pl-4 font-display text-lg font-bold text-ink-300 select-none"
                                    aria-disabled="true"
                                >
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xs bg-sand-50 text-ink-300">
                                        <component :is="categoryIcons[cat.icon] ?? Sparkles" :size="18" aria-hidden="true" />
                                    </span>
                                    <span class="flex-1">{{ cat.name }}</span>
                                    <span class="rounded-pill bg-sand-100 px-2.5 py-1 text-xs font-bold text-ink-400">{{ 'Demnächst' }}</span>
                                </span>
                            </template>
                        </div>
                    </Transition>
                    <div class="my-3 border-t border-ink-100" />
                    <Motion
                        v-for="(item, i) in navItems"
                        :key="item.href"
                        :initial="{ opacity: 0, x: -16 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ delay: 0.06 * i, duration: 0.4, ease: [0.16, 1, 0.3, 1] }"
                    >
                        <Link
                            :href="item.href"
                            class="block rounded-card px-3 py-4 font-display text-2xl font-bold text-navy-700"
                            @click="mobileOpen = false"
                        >
                            {{ item.label }}
                        </Link>
                    </Motion>
                    <Motion
                        :initial="{ opacity: 0, x: -16 }"
                        :animate="{ opacity: 1, x: 0 }"
                        :transition="{ delay: 0.28, duration: 0.4 }"
                    >
                        <Link
                            :href="isLoggedIn ? '/account' : '/login'"
                            class="block rounded-card px-3 py-4 font-display text-2xl font-bold text-ink-500"
                            @click="mobileOpen = false"
                        >
                            {{ isLoggedIn ? 'Mein Konto' : 'Anmelden' }}
                        </Link>
                    </Motion>
                </nav>
                <div class="border-t border-ink-100 p-6">
                    <Link
                        href="/request/start"
                        class="block w-full rounded-pill bg-green-500 py-4 text-center text-base font-bold text-white"
                        @click="mobileOpen = false"
                    >
                        {{ 'Jetzt kostenlos Angebote erhalten' }}
                    </Link>
                </div>
            </div>
        </Transition>
    </header>
</template>
