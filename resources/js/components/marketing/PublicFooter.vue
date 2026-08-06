<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import StarRating from '@/components/marketing/StarRating.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Facebook, Instagram, Linkedin } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
type NavCategory = { id: number; name: string; slug: string; is_active: boolean };
const categoryLinks = computed(() => {
    const cats = (page.props.navCategories as NavCategory[] | undefined) ?? [];
    return [...cats]
        .sort((a, b) => {
            if (a.slug === 'kfz-gutachten') return -1;
            if (b.slug === 'kfz-gutachten') return 1;
            return 0;
        })
        .map((cat) => ({
            label: cat.name,
            href: cat.is_active ? '/vehicle-reports' : `/coming-soon/${cat.slug}`,
        }));
});

const columns = computed(() => [
    {
        title: 'Über AngebotJetzt',
        links: [
            { label: 'Über uns', href: '/about' },
            { label: 'So funktioniert\'s', href: '/how-it-works' },
            { label: 'Bewertungen', href: '/reviews' },
            { label: 'Preise', href: '/pricing' },
            { label: 'Kontakt', href: '/contact' },
        ],
    },
    {
        title: 'Dienstleistungen',
        links: categoryLinks.value,
    },
    {
        title: 'Für Dienstleister',
        links: [
            { label: 'Anbieter werden', href: '/for-inspectors' },
            { label: 'Anbieter-Login', href: '/login' },
            { label: 'Häufige Fragen', href: '/faq' },
        ],
    },
    {
        title: 'Rechtliches',
        links: [
            { label: 'Impressum', href: '/imprint' },
            { label: 'Datenschutz', href: '/privacy' },
            { label: 'AGB', href: '/terms' },
            { label: 'Cookie-Richtlinie', href: '/cookie-policy' },
        ],
    },
]);
</script>

<template>
    <footer class="bg-navy-950 text-navy-100">
        <div class="mx-auto max-w-7xl px-4 pt-16 pb-10 sm:px-6 lg:px-8">
            <div class="mb-14">
                <BrandLogo inverted tagline />
            </div>

            <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-4">
                <nav v-for="col in columns" :key="col.title" class="min-w-0" :aria-label="col.title">
                    <h3 class="text-eyebrow mb-5 text-white">{{ col.title }}</h3>
                    <ul class="space-y-3">
                        <li v-for="link in col.links" :key="link.href">
                            <Link
                                :href="link.href"
                                class="text-[15px] break-words text-navy-100 transition-colors hover:text-green-400"
                            >
                                {{ link.label }}
                            </Link>
                        </li>
                    </ul>
                </nav>
            </div>

            <div
                class="mt-16 flex flex-col gap-8 border-t border-navy-800 pt-8 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-3">
                    <span class="font-display text-3xl font-extrabold text-white">4,9</span>
                    <div>
                        <StarRating :rating="5" :size="15" />
                        <p class="mt-0.5 text-sm text-navy-100">{{ 'basierend auf Kundenbewertungen' }}</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-navy-100 md:text-right">
                    {{ 'AngebotJetzt · Musterstraße 1, 10115 Berlin' }}<br />
                    © {{ new Date().getFullYear() }} AngebotJetzt. {{ 'Alle Rechte vorbehalten.' }}
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <div class="flex gap-4">
                    <a
                        href="#"
                        class="flex h-10 w-10 items-center justify-center rounded-pill bg-navy-800 text-white transition hover:bg-green-500"
                        :aria-label="'AngebotJetzt auf Facebook'"
                    >
                        <Facebook :size="18" aria-hidden="true" />
                    </a>
                    <a
                        href="#"
                        class="flex h-10 w-10 items-center justify-center rounded-pill bg-navy-800 text-white transition hover:bg-green-500"
                        :aria-label="'AngebotJetzt auf Instagram'"
                    >
                        <Instagram :size="18" aria-hidden="true" />
                    </a>
                    <a
                        href="#"
                        class="flex h-10 w-10 items-center justify-center rounded-pill bg-navy-800 text-white transition hover:bg-green-500"
                        :aria-label="'AngebotJetzt auf LinkedIn'"
                    >
                        <Linkedin :size="18" aria-hidden="true" />
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
