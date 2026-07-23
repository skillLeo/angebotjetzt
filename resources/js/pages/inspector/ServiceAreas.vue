<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { MapPin, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    areas: Array<{ id: number; type: string; city: string | null; from: string | null; to: string | null }>;
}>();

const type = ref<'city' | 'postal_range'>('city');
const form = useForm({ type: 'city', city_name: '', postal_from: '', postal_to: '' });

function submit() {
    form.type = type.value;
    form.post('/inspector/service-areas', {
        preserveScroll: true,
        onSuccess: () => form.reset('city_name', 'postal_from', 'postal_to'),
    });
}
function remove(id: number) {
    router.delete(`/inspector/service-areas/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head><title>{{ t('dashboard.inspectorPages.serviceAreaTitle') }}</title></Head>

    <div class="grid gap-6 lg:grid-cols-2">
        <PageCard :title="t('dashboard.inspectorPages.myServiceArea')" :subtitle="t('dashboard.inspectorPages.myServiceAreaSubtitle')">
            <div v-if="areas.length" class="divide-y divide-ink-100">
                <div v-for="a in areas" :key="a.id" class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-card bg-green-50 text-green-600"><MapPin :size="17" aria-hidden="true" /></span>
                        <div>
                            <p class="font-semibold text-navy-700">{{ a.type === 'city' ? a.city : t('dashboard.inspectorPages.plzRange', { from: a.from, to: a.to }) }}</p>
                            <p class="text-xs text-ink-500">{{ a.type === 'city' ? t('dashboard.inspectorPages.city') : t('dashboard.inspectorPages.postalRange') }}</p>
                        </div>
                    </div>
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-pill text-ink-500 transition hover:bg-red-50 hover:text-red-600" :aria-label="t('dashboard.inspectorPages.remove')" @click="remove(a.id)">
                        <Trash2 :size="17" aria-hidden="true" />
                    </button>
                </div>
            </div>
            <p v-else class="px-5 py-8 text-center text-sm text-ink-500">{{ t('dashboard.inspectorPages.noServiceAreasYet') }}</p>
        </PageCard>

        <PageCard :title="t('dashboard.inspectorPages.addServiceArea')">
            <form class="space-y-5 p-5 sm:p-6" @submit.prevent="submit">
                <div class="flex gap-2">
                    <button type="button" class="flex-1 rounded-pill py-2.5 text-sm font-bold transition" :class="type === 'city' ? 'bg-navy-700 text-white' : 'bg-sand-50 text-ink-700'" @click="type = 'city'">{{ t('dashboard.inspectorPages.city') }}</button>
                    <button type="button" class="flex-1 rounded-pill py-2.5 text-sm font-bold transition" :class="type === 'postal_range' ? 'bg-navy-700 text-white' : 'bg-sand-50 text-ink-700'" @click="type = 'postal_range'">{{ t('dashboard.inspectorPages.postalRange') }}</button>
                </div>

                <div v-if="type === 'city'">
                    <label for="city" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ t('dashboard.inspectorPages.cityLabel') }}</label>
                    <input id="city" v-model="form.city_name" type="text" :placeholder="t('dashboard.inspectorPages.cityPlaceholder')" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.city_name" class="mt-1 text-sm text-red-600">{{ form.errors.city_name }}</p>
                </div>
                <div v-else class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="from" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ t('dashboard.inspectorPages.plzFrom') }}</label>
                        <input id="from" v-model="form.postal_from" type="text" inputmode="numeric" maxlength="5" placeholder="50000" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    </div>
                    <div>
                        <label for="to" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ t('dashboard.inspectorPages.plzTo') }}</label>
                        <input id="to" v-model="form.postal_to" type="text" inputmode="numeric" maxlength="5" placeholder="51999" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    </div>
                    <p v-if="form.errors.postal_to" class="col-span-2 text-sm text-red-600">{{ form.errors.postal_to }}</p>
                </div>

                <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center gap-2 rounded-pill bg-green-500 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                    <Plus :size="18" aria-hidden="true" /> {{ t('dashboard.inspectorPages.add') }}
                </button>
            </form>
        </PageCard>
    </div>
</template>
