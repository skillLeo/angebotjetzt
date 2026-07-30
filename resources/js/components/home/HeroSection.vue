<script setup lang="ts">
import FloatChip from '@/components/marketing/FloatChip.vue';
import HandDrawnEllipse from '@/components/marketing/HandDrawnEllipse.vue';
import { router } from '@inertiajs/vue3';
import { CheckCircle2, MapPin, MousePointer2, Search } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    categories: Array<{ id: number; name: string; slug: string; icon: string; is_active: boolean }>;
    serviceTypes: Array<{ id: number; name: string; slug: string; categoryId: number }>;
    heroImage: string;
}>();


const category = ref<number | null>(null);
const service = ref<string>('');
const location = ref('');

const servicesForCategory = computed(() =>
    props.serviceTypes.filter((t) => t.categoryId === category.value),
);

watch(category, () => (service.value = ''));

function submit() {
    if (!service.value) return;
    router.get('/request', {
        service: service.value,
        plz: location.value || undefined,
    });
}
</script>

<template>
    <section class="relative overflow-hidden bg-sand-100 px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        <div
            class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-green-100/60 blur-3xl"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none absolute -bottom-32 -left-16 h-72 w-72 rounded-full bg-navy-100/50 blur-3xl"
            aria-hidden="true"
        />
        <div class="relative mx-auto max-w-7xl">
            <div class="grid items-center gap-8 lg:grid-cols-[55fr_45fr] lg:gap-6">
                <!-- Left column -->
                <div>
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }"
                    >
                        <p class="text-eyebrow mb-5 text-green-600">
                            {{ 'Anfragen. Vergleichen. Buchen.' }}
                        </p>
                        <h1 class="text-hero text-navy-700">
                            {{ 'Der richtige Anbieter' }}<br />
                            <span class="text-navy-700">{{ 'jetzt' }}&nbsp;</span><HandDrawnEllipse><span class="text-navy-700">{{ 'vergleichen' }}</span></HandDrawnEllipse>
                        </h1>
                        <p class="text-lead mt-6 max-w-xl text-ink-700">
                            {{ 'Beschreiben Sie, was Sie brauchen. Geprüfte Anbieter aus Ihrer Region senden Ihnen individuelle Angebote – Sie vergleichen und beauftragen online. Kostenlos und unverbindlich.' }}
                        </p>
                    </Motion>

                    <!-- Inline request bar -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.6, delay: 0.15, ease: [0.16, 1, 0.3, 1] }"
                    >
                        <form
                            class="mt-9 flex flex-col gap-2 rounded-panel bg-white p-2 shadow-lift"
                            @submit.prevent="submit"
                        >
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <div class="relative flex-1">
                                    <label for="hero-category" class="sr-only">{{ 'Kategorie' }}</label>
                                    <select
                                        id="hero-category"
                                        v-model="category"
                                        class="h-12 w-full appearance-none rounded-pill bg-transparent px-5 text-[15px] font-medium text-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                                    >
                                        <option :value="null">{{ 'Welche Kategorie?' }}</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id" :disabled="!cat.is_active">
                                            {{ cat.name }}{{ !cat.is_active ? ` (${'Demnächst'})` : '' }}
                                        </option>
                                    </select>
                                </div>
                                <div class="hidden h-7 w-px bg-ink-100 sm:block" />
                                <div class="relative flex-1">
                                    <label for="hero-service" class="sr-only">{{ 'Leistung' }}</label>
                                    <select
                                        id="hero-service"
                                        v-model="service"
                                        :disabled="!category"
                                        class="h-12 w-full appearance-none rounded-pill bg-transparent px-5 text-[15px] font-medium text-ink-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 disabled:cursor-not-allowed disabled:text-ink-300"
                                    >
                                        <option value="">{{ 'Welche Leistung?' }}</option>
                                        <option v-for="serviceType in servicesForCategory" :key="serviceType.id" :value="serviceType.slug">
                                            {{ serviceType.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 border-t border-ink-100 pt-2 sm:flex-row sm:items-center">
                                <div class="relative flex-1">
                                    <label for="hero-plz" class="sr-only">{{ 'PLZ oder Ort' }}</label>
                                    <MapPin
                                        class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-ink-300"
                                        :size="18"
                                        aria-hidden="true"
                                    />
                                    <input
                                        id="hero-plz"
                                        v-model="location"
                                        type="text"
                                        inputmode="numeric"
                                        :placeholder="'PLZ oder Ort'"
                                        class="h-12 w-full rounded-pill bg-transparent pr-4 pl-11 text-[15px] font-medium text-ink-700 placeholder:text-ink-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    :disabled="!service"
                                    class="flex h-12 items-center justify-center gap-2 rounded-pill bg-green-500 px-7 text-[15px] font-bold text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:bg-ink-300"
                                >
                                    <Search :size="18" aria-hidden="true" />
                                    {{ 'Angebote erhalten' }}
                                </button>
                            </div>
                        </form>
                        <p class="mt-3 pl-2 text-sm text-ink-500">
                            {{ 'Bereits über 8.000 Aufträge in ganz Deutschland vermittelt.' }}
                        </p>
                    </Motion>
                </div>

                <!-- Right column: photo with floating chips -->
                <div class="relative mx-auto w-full max-w-lg lg:max-w-none">
                    <Motion
                        :initial="{ opacity: 0, scale: 0.96 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ duration: 0.7, delay: 0.1, ease: [0.16, 1, 0.3, 1] }"
                    >
                        <img
                            :src="heroImage"
                            width="720"
                            height="600"
                            fetchpriority="high"
                            :alt="'Dienstleister bei der Ausführung eines Auftrags'"
                            class="aspect-[6/5] w-full rounded-panel object-cover shadow-lift"
                        />
                    </Motion>

                    <FloatChip :delay="0.5" class="top-6 -left-2 sm:-left-5">
                        <MapPin :size="16" class="text-green-500" aria-hidden="true" />
                        {{ 'Ort: Köln, 50667' }}
                    </FloatChip>
                    <FloatChip :delay="0.7" alt class="top-24 -right-2 sm:-right-4">
                        {{ 'Leistung: Unfallschaden' }}
                    </FloatChip>
                    <FloatChip :delay="0.9" class="bottom-20 -left-2 sm:-left-6">
                        <CheckCircle2 :size="16" class="text-green-500" aria-hidden="true" />
                        <span class="font-semibold text-green-700">{{ '3 Angebote erhalten' }}</span>
                    </FloatChip>
                    <FloatChip :delay="1.1" alt class="right-2 bottom-6 sm:-right-3">
                        <span class="text-ink-500">{{ 'ab' }}</span>
                        <span class="font-display text-lg font-extrabold text-navy-700">249&nbsp;€</span>
                    </FloatChip>

                    <MousePointer2
                        class="absolute -bottom-2 left-1/3 rotate-12 fill-navy-700 text-navy-700 drop-shadow-lg"
                        :size="40"
                        aria-hidden="true"
                    />
                </div>
            </div>
        </div>
    </section>
</template>
