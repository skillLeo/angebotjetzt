<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const scrolled = ref(false);
const mobileOpen = ref(false);

const page = usePage();
const isLoggedIn = computed(() => Boolean((page.props.auth as { user?: unknown } | undefined)?.user));

const navItems = computed(() => [
    { label: t('nav.kfzGutachten'), href: '/vehicle-reports' },
    { label: t('nav.howItWorks'), href: '/how-it-works' },
    { label: t('nav.forInspectors'), href: '/for-inspectors' },
    { label: t('nav.prices'), href: '/pricing' },
]);

function onScroll() {
    scrolled.value = window.scrollY > 40;
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

watch(mobileOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
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

            <nav class="hidden items-center gap-8 lg:flex" aria-label="Hauptnavigation">
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
                    :href="isLoggedIn ? '/account' : '/login'"
                    class="text-[15px] font-medium text-ink-700 transition-colors hover:text-navy-700"
                >
                    {{ isLoggedIn ? t('nav.myAccount') : t('nav.login') }}
                </Link>
                <Link
                    href="/request"
                    class="rounded-pill bg-green-500 px-6 py-2.5 text-[15px] font-bold text-white shadow-card transition hover:bg-green-600"
                >
                    {{ t('nav.requestNow') }}
                </Link>
            </div>

            <div class="flex items-center gap-2 lg:hidden">
                <button
                    type="button"
                    class="flex h-11 w-11 items-center justify-center rounded-pill text-navy-700"
                    :aria-expanded="mobileOpen"
                    :aria-label="t('nav.menuOpen')"
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
            <div v-if="mobileOpen" class="fixed inset-0 top-16 z-40 flex flex-col bg-white lg:hidden">
                <nav class="flex flex-1 flex-col gap-1 overflow-y-auto px-6 pt-8" aria-label="Mobile Navigation">
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
                            {{ isLoggedIn ? t('nav.myAccount') : t('nav.login') }}
                        </Link>
                    </Motion>
                </nav>
                <div class="border-t border-ink-100 p-6">
                    <Link
                        href="/request"
                        class="block w-full rounded-pill bg-green-500 py-4 text-center text-base font-bold text-white"
                        @click="mobileOpen = false"
                    >
                        {{ t('nav.mobileCtaLine') }}
                    </Link>
                </div>
            </div>
        </Transition>
    </header>
</template>
