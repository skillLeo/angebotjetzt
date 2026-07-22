<script setup lang="ts">
import Reveal from '@/components/marketing/Reveal.vue';
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, ShieldCheck, Clock, Euro } from 'lucide-vue-next';

defineProps<{
    serviceType: { name: string; slug: string; description: string; image: string | null };
    others: Array<{ id: number; name: string; slug: string; description: string; image: string | null }>;
}>();

const benefits = [
    { icon: ShieldCheck, title: 'Geprüfte Sachverständige', text: 'Unabhängige, oft öffentlich bestellte und vereidigte Gutachter mit langjähriger Erfahrung.' },
    { icon: Euro, title: 'Preise vergleichen', text: 'Mehrere individuelle Angebote – Sie entscheiden, welches am besten passt.' },
    { icon: Clock, title: 'Schnelle Rückmeldung', text: 'Erste Angebote in der Regel innerhalb weniger Stunden nach Ihrer Anfrage.' },
];
</script>

<template>
    <Head>
        <title>{{ serviceType.name }} – Gutachter vergleichen</title>
        <meta name="description" :content="serviceType.description" />
    </Head>

    <section class="bg-white px-4 pt-6 sm:px-6 lg:px-8">
        <nav class="mx-auto max-w-7xl text-sm text-ink-500" aria-label="Brotkrümel">
            <Link href="/" class="hover:text-navy-700">Startseite</Link>
            <span class="mx-2">/</span>
            <Link href="/kfz-gutachten" class="hover:text-navy-700">Kfz-Gutachten</Link>
            <span class="mx-2">/</span>
            <span class="text-navy-700">{{ serviceType.name }}</span>
        </nav>
    </section>

    <section class="bg-white px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <div class="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-2">
            <div>
                <p class="text-eyebrow mb-4 text-green-600">Kfz-Gutachten</p>
                <h1 class="text-hero text-navy-700">{{ serviceType.name }}</h1>
                <p class="text-lead mt-6 text-ink-700">{{ serviceType.description }}</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <Link
                        :href="`/anfrage?service=${serviceType.slug}`"
                        class="rounded-pill bg-green-500 px-7 py-3.5 text-center text-[15px] font-bold text-white transition hover:bg-green-600"
                    >
                        Jetzt kostenlos Angebote erhalten
                    </Link>
                    <Link
                        href="/so-funktionierts"
                        class="rounded-pill border border-ink-300 px-7 py-3.5 text-center text-[15px] font-bold text-navy-700 transition hover:border-navy-700"
                    >
                        So funktioniert's
                    </Link>
                </div>
            </div>
            <div v-if="serviceType.image" class="overflow-hidden rounded-panel shadow-lift">
                <img
                    :src="serviceType.image"
                    width="640"
                    height="440"
                    :alt="`${serviceType.name} durch einen Kfz-Sachverständigen`"
                    class="aspect-[16/11] w-full object-cover"
                />
            </div>
        </div>
    </section>

    <section class="bg-sand-50 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-5 md:grid-cols-3">
                <Reveal v-for="(b, i) in benefits" :key="i" :delay="i * 0.08">
                    <div class="h-full rounded-card border border-ink-100 bg-white p-7">
                        <span class="flex h-12 w-12 items-center justify-center rounded-card bg-green-50 text-green-600">
                            <component :is="b.icon" :size="24" aria-hidden="true" />
                        </span>
                        <h3 class="mt-5 font-display text-lg font-bold text-navy-700">{{ b.title }}</h3>
                        <p class="mt-2 text-[15px] leading-relaxed text-ink-500">{{ b.text }}</p>
                    </div>
                </Reveal>
            </div>
        </div>
    </section>

    <section class="bg-white py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <SectionHeading line1="Weitere" line2="Kfz-Gutachten" />
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="other in others"
                    :key="other.id"
                    :href="`/kfz-gutachten/${other.slug}`"
                    class="group flex items-center justify-between gap-4 rounded-card border border-ink-100 bg-white p-5 transition hover:border-green-500 hover:shadow-card"
                >
                    <div>
                        <p class="font-display font-bold text-navy-700">{{ other.name }}</p>
                    </div>
                    <ArrowRight :size="20" class="shrink-0 text-green-500 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                </Link>
            </div>
        </div>
    </section>

    <section class="bg-navy-900 py-16 lg:py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
            <h2 class="text-section text-white">Bereit für Ihr <span class="text-green-400">{{ serviceType.name }}</span>?</h2>
            <p class="text-lead mx-auto mt-5 max-w-xl text-navy-100">
                Stellen Sie jetzt kostenlos Ihre Anfrage und erhalten Sie unverbindliche Angebote aus Ihrer Region.
            </p>
            <Link
                :href="`/anfrage?service=${serviceType.slug}`"
                class="mt-8 inline-flex items-center gap-2 rounded-pill bg-green-500 px-8 py-4 text-base font-bold text-white transition hover:bg-green-600"
            >
                <CheckCircle2 :size="20" aria-hidden="true" /> Anfrage stellen
            </Link>
        </div>
    </section>
</template>
