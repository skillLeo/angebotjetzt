<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { onClickOutside } from '@vueuse/core';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Car,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Flower2,
    Hammer,
    Menu,
    Monitor,
    Package,
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
const mobilePanel = ref<'main' | 'services'>('main');
const servicesRef = useTemplateRef('servicesRef');
onClickOutside(servicesRef, () => (servicesOpen.value = false));

const page = usePage();
type AuthState = { user?: unknown; inspector?: unknown; admin?: unknown } | undefined;
const accountLink = computed(() => {
    const auth = page.props.auth as AuthState;
    if (auth?.admin) return { href: '/admin', label: 'Mein Konto' };
    if (auth?.inspector) return { href: '/inspector', label: 'Mein Konto' };
    if (auth?.user) return { href: '/account', label: 'Mein Konto' };
    return { href: '/login', label: 'Anmelden' };
});

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
    car: Car,
    truck: Truck,
    hammer: Hammer,
    package: Package,
    'flower-2': Flower2,
    sparkles: Sparkles,
    monitor: Monitor,
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
        mobilePanel.value = 'main';
    }
});
</script>

<template>
    <header
        class="z-50 bg-white transition-shadow duration-300"
        :class="[reduced ? '' : 'sticky top-0', scrolled ? 'border-b border-ink-100 shadow-card' : '']"
    >
        <!-- Reduced (booking-flow) header: logo + trust message. A symmetric
             empty spacer column mirrors the logo's column so the message is
             centered against the full header width, not just the space left
             of the logo. -->
        <div
            v-if="reduced"
            class="mx-auto flex h-[92px] max-w-7xl flex-col items-center justify-center gap-2 px-4 py-2.5 sm:px-6 lg:grid lg:h-[72px] lg:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] lg:gap-4 lg:py-0 lg:px-8"
        >
            <Link href="/" aria-label="AngebotJetzt Startseite" class="flex items-center lg:justify-self-start">
                <BrandLogo />
            </Link>
            <div class="flex items-center justify-center gap-2 whitespace-nowrap text-sm font-semibold text-navy-700 sm:gap-2.5 sm:text-[15px]">
                <BadgeCheck :size="18" class="shrink-0 text-green-500" aria-hidden="true" />
                <span>{{ '8.000+ zufriedene Kunden in ganz Deutschland' }}</span>
            </div>
            <div aria-hidden="true" class="hidden lg:block" />
        </div>

        <div v-else class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:h-[72px] lg:px-8">
            <Link href="/" aria-label="AngebotJetzt Startseite" class="flex items-center">
                <BrandLogo />
            </Link>

            <nav class="hidden items-center gap-8 lg:flex" aria-label="Hauptnavigation">
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

            <div class="hidden items-center gap-4 lg:flex">
                <Link
                    :href="accountLink.href"
                    class="text-[15px] font-medium text-ink-700 transition-colors hover:text-navy-700"
                >
                    {{ accountLink.label }}
                </Link>
                <Link
                    href="/request/start"
                    class="rounded-pill bg-green-500 px-6 py-2.5 text-[15px] font-bold text-white shadow-card transition hover:bg-green-600"
                >
                    {{ 'Anfrage stellen' }}
                </Link>
            </div>

            <div class="flex items-center gap-2 lg:hidden">
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
                <!-- Two full-size panels slide horizontally between the top-level
                     menu and the services drill-down, like a native app's
                     navigation stack — never an inline accordion that pushes
                     the rest of the menu down. -->
                <div class="relative flex-1 overflow-hidden">
                    <nav
                        class="absolute inset-0 flex flex-col gap-1 overflow-y-auto px-6 pt-8 transition-transform duration-200 ease-out"
                        :class="mobilePanel === 'services' ? '-translate-x-full' : 'translate-x-0'"
                        aria-label="Mobile Navigation"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-card px-3 py-4 font-display text-2xl font-bold text-navy-700"
                            aria-haspopup="true"
                            @click="mobilePanel = 'services'"
                        >
                            {{ 'Dienstleistungen' }}
                            <ChevronRight :size="22" aria-hidden="true" />
                        </button>
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
                                :href="accountLink.href"
                                class="block rounded-card px-3 py-4 font-display text-2xl font-bold text-navy-700"
                                @click="mobileOpen = false"
                            >
                                {{ accountLink.label }}
                            </Link>
                        </Motion>
                    </nav>

                    <div
                        class="absolute inset-0 flex flex-col gap-1 overflow-y-auto px-6 pt-8 transition-transform duration-200 ease-out"
                        :class="mobilePanel === 'services' ? 'translate-x-0' : 'translate-x-full'"
                        aria-label="Dienstleistungen"
                    >
                        <button
                            type="button"
                            class="mb-4 flex items-center gap-1.5 rounded-card px-3 py-2 text-sm font-bold text-ink-500 transition-colors hover:text-navy-700"
                            @click="mobilePanel = 'main'"
                        >
                            <ChevronLeft :size="20" aria-hidden="true" />
                            {{ 'Zurück' }}
                        </button>
                        <p class="px-3 pb-2 font-display text-xl font-bold text-navy-700">{{ 'Dienstleistungen' }}</p>
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
                </div>
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
