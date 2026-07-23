<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import StarRating from '@/components/marketing/StarRating.vue';
import { Link } from '@inertiajs/vue3';
import { Facebook, Instagram, Linkedin } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const columns = computed(() => [
    {
        title: t('footer.about'),
        links: [
            { label: t('footer.aboutUs'), href: '/about' },
            { label: t('footer.howItWorks'), href: '/how-it-works' },
            { label: t('footer.reviews'), href: '/reviews' },
            { label: t('footer.prices'), href: '/pricing' },
            { label: t('footer.contact'), href: '/contact' },
        ],
    },
    {
        title: t('footer.services'),
        links: [
            { label: t('footer.accidentReport'), href: '/vehicle-reports/unfallschadengutachten' },
            { label: t('footer.vehicleValuation'), href: '/vehicle-reports/fahrzeugbewertung' },
            { label: t('footer.usedCarCheck'), href: '/vehicle-reports/gebrauchtwagencheck' },
            { label: t('footer.depreciationReport'), href: '/vehicle-reports/wertminderung-restwert' },
            { label: t('footer.allServices'), href: '/vehicle-reports' },
        ],
    },
    {
        title: t('footer.forInspectors'),
        links: [
            { label: t('footer.becomeInspector'), href: '/for-inspectors' },
            { label: t('footer.inspectorLogin'), href: '/inspector/login' },
            { label: t('footer.faq'), href: '/faq' },
        ],
    },
    {
        title: t('footer.legal'),
        links: [
            { label: t('footer.imprint'), href: '/imprint' },
            { label: t('footer.privacy'), href: '/privacy' },
            { label: t('footer.terms'), href: '/terms' },
            { label: t('footer.cookiePolicy'), href: '/cookie-policy' },
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
                        <p class="mt-0.5 text-sm text-navy-100">{{ t('footer.basedOnReviews') }}</p>
                    </div>
                </div>

                <div class="text-sm leading-relaxed text-navy-100 md:text-right">
                    {{ t('footer.addressLine') }}<br />
                    © {{ new Date().getFullYear() }} AngebotJetzt. {{ t('footer.copyright') }}
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <div class="flex gap-4">
                    <a
                        href="#"
                        class="flex h-10 w-10 items-center justify-center rounded-pill bg-navy-800 text-white transition hover:bg-green-500"
                        :aria-label="t('footer.facebookLabel')"
                    >
                        <Facebook :size="18" aria-hidden="true" />
                    </a>
                    <a
                        href="#"
                        class="flex h-10 w-10 items-center justify-center rounded-pill bg-navy-800 text-white transition hover:bg-green-500"
                        :aria-label="t('footer.instagramLabel')"
                    >
                        <Instagram :size="18" aria-hidden="true" />
                    </a>
                    <a
                        href="#"
                        class="flex h-10 w-10 items-center justify-center rounded-pill bg-navy-800 text-white transition hover:bg-green-500"
                        :aria-label="t('footer.linkedinLabel')"
                    >
                        <Linkedin :size="18" aria-hidden="true" />
                    </a>
                </div>
            </div>
        </div>
    </footer>
</template>
