<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ListChecks, Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    serviceType: { id: number; name: string };
    fields: Array<{ id: number; label: string; type: string; options: string[] | null; isRequired: boolean }>;
}>();

const typeLabels: Record<string, string> = {
    text: 'Text', number: 'Zahl', date: 'Datum', select: 'Auswahl', textarea: 'Mehrzeiliger Text', file: 'Datei-Upload',
};

const form = useForm({ label: '', type: 'text', options: '', is_required: false });

function submit() {
    form.post(`/admin/service-types/${props.serviceType.id}/fields`, {
        preserveScroll: true,
        onSuccess: () => form.reset('label', 'options', 'is_required'),
    });
}
function remove(id: number) {
    router.delete(`/admin/service-types/${props.serviceType.id}/fields/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head><title>{{ `Felder – ${serviceType.name}` }}</title></Head>

    <Link href="/admin/services" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> Zurück
    </Link>

    <div class="grid gap-6 lg:grid-cols-2">
        <PageCard :title="`Felder für „${serviceType.name}“`" :subtitle="'Diese Felder werden im Anfrage-Formular anstelle der Fahrzeugdaten angezeigt, sobald mindestens eines definiert ist.'">
            <div v-if="fields.length" class="divide-y divide-ink-100">
                <div v-for="f in fields" :key="f.id" class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-card bg-green-50 text-green-600"><ListChecks :size="17" aria-hidden="true" /></span>
                        <div>
                            <p class="font-semibold text-navy-700">
                                {{ f.label }}
                                <span v-if="f.isRequired" class="ml-1 text-xs font-bold text-green-600">*</span>
                            </p>
                            <p class="text-xs text-ink-500">{{ typeLabels[f.type] ?? f.type }}</p>
                        </div>
                    </div>
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-pill text-ink-500 transition hover:bg-red-50 hover:text-red-600" :aria-label="'Entfernen'" @click="remove(f.id)">
                        <Trash2 :size="17" aria-hidden="true" />
                    </button>
                </div>
            </div>
            <p v-else class="px-5 py-8 text-center text-sm text-ink-500">{{ 'Noch keine eigenen Felder – das Standard-Fahrzeugformular wird verwendet.' }}</p>
        </PageCard>

        <PageCard :title="'Feld hinzufügen'">
            <form class="space-y-5 p-6 sm:p-8" @submit.prevent="submit">
                <div>
                    <label for="label" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Bezeichnung' }}</label>
                    <input id="label" v-model="form.label" type="text" :placeholder="'z. B. Anzahl Zimmer'" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.label" class="mt-1 text-sm text-red-600">{{ form.errors.label }}</p>
                </div>

                <div>
                    <label for="type" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Feldtyp' }}</label>
                    <select id="type" v-model="form.type" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none">
                        <option v-for="(label, value) in typeLabels" :key="value" :value="value">{{ label }}</option>
                    </select>
                </div>

                <div v-if="form.type === 'select'">
                    <label for="options" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Auswahlmöglichkeiten (mit Komma getrennt)' }}</label>
                    <textarea
                        id="options"
                        v-model="form.options"
                        rows="3"
                        :placeholder="'z. B. Klein, Mittel, Groß'"
                        class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none"
                    />
                    <p v-if="form.errors.options" class="mt-1 text-sm text-red-600">{{ form.errors.options }}</p>
                </div>

                <label class="flex items-center gap-3 text-sm text-ink-700">
                    <input v-model="form.is_required" type="checkbox" class="h-5 w-5 accent-green-500" />
                    {{ 'Pflichtfeld' }}
                </label>

                <button type="submit" :disabled="form.processing" class="inline-flex w-full items-center justify-center gap-2 rounded-pill bg-green-500 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                    <Plus :size="18" aria-hidden="true" /> {{ 'Hinzufügen' }}
                </button>
            </form>
        </PageCard>
    </div>
</template>
