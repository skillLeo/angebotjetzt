<script setup lang="ts">
import SectionHeading from '@/components/marketing/SectionHeading.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, router } from '@inertiajs/vue3';
import { MapPin, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    categories: Array<{ id: number; name: string; slug: string; icon: string; is_active: boolean }>;
    serviceTypes: Array<{ id: number; name: string; slug: string; categoryId: number }>;
}>();

const category = ref<string | undefined>(undefined);
const service = ref<string | undefined>(undefined);
const location = ref('');

const servicesForCategory = computed(() =>
    props.serviceTypes.filter((t) => String(t.categoryId) === category.value),
);

watch(category, () => (service.value = undefined));

function submit() {
    if (!service.value) return;
    router.get('/request', {
        service: service.value,
        plz: location.value || undefined,
    });
}
</script>

<template>
    <Head>
        <title>Anfrage stellen</title>
    </Head>

    <section class="bg-sand-50 px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <div class="mx-auto max-w-2xl">
            <SectionHeading
                centered
                :eyebrow="'Anfrage stellen'"
                :line1="'Was brauchen Sie'"
                :line2="'gerade?'"
            />
            <p class="mt-4 text-center text-lead text-ink-700">
                {{ 'Wählen Sie zunächst eine Kategorie, dann die passende Leistung – wir verbinden Sie mit geprüften Anbietern aus Ihrer Region.' }}
            </p>

            <form
                class="mt-10 flex flex-col gap-2 rounded-panel bg-white p-2 shadow-lift"
                @submit.prevent="submit"
            >
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <label for="start-category" class="sr-only">{{ 'Kategorie' }}</label>
                        <Select v-model="category">
                            <SelectTrigger id="start-category" class="h-12 w-full rounded-pill border-none bg-transparent px-5 text-[15px] font-medium text-ink-700 shadow-none focus-visible:ring-2 focus-visible:ring-green-500">
                                <SelectValue :placeholder="'Welche Kategorie?'" />
                            </SelectTrigger>
                            <SelectContent class="max-w-[calc(100vw-2rem)]">
                                <SelectItem v-for="cat in categories" :key="cat.id" :value="String(cat.id)" :disabled="!cat.is_active">
                                    {{ cat.name }}{{ !cat.is_active ? ` (${'Demnächst'})` : '' }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="hidden h-7 w-px bg-ink-100 sm:block" />
                    <div class="relative flex-1">
                        <label for="start-service" class="sr-only">{{ 'Leistung' }}</label>
                        <Select v-model="service" :disabled="!category">
                            <SelectTrigger id="start-service" class="h-12 w-full rounded-pill border-none bg-transparent px-5 text-[15px] font-medium text-ink-700 shadow-none focus-visible:ring-2 focus-visible:ring-green-500 disabled:cursor-not-allowed disabled:text-ink-300">
                                <SelectValue :placeholder="'Welche Leistung?'" />
                            </SelectTrigger>
                            <SelectContent class="max-w-[calc(100vw-2rem)]">
                                <SelectItem v-for="serviceType in servicesForCategory" :key="serviceType.id" :value="serviceType.slug">
                                    {{ serviceType.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <div class="flex flex-col gap-2 border-t border-ink-100 pt-2 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <label for="start-plz" class="sr-only">{{ 'PLZ oder Ort' }}</label>
                        <MapPin
                            class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-ink-300"
                            :size="18"
                            aria-hidden="true"
                        />
                        <input
                            id="start-plz"
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
            <p class="mt-4 text-center text-sm text-ink-500">
                {{ 'Kostenlos & unverbindlich. Bereits über 8.000 Aufträge in ganz Deutschland vermittelt.' }}
            </p>
        </div>
    </section>
</template>
