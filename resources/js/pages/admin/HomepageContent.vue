<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ExternalLink, RotateCcw } from 'lucide-vue-next';
import { reactive } from 'vue';
import { toast } from 'vue-sonner';

type Field = { key: string; label: string; type: string; hint: string | null; value: string };
type Section = { key: string; label: string; hint: string | null; fields: Field[] };

const props = defineProps<{ sections: Section[] }>();

// One form holding every field, so the admin can edit several sections and
// save once. Each section also has its own save button that posts the same
// form — the server only writes the keys it is given.
const values: Record<string, string> = {};
for (const section of props.sections) {
    for (const field of section.fields) values[field.key] = field.value;
}

const form = useForm({ values });

// What the fields held when the page loaded, so "zurücksetzen" restores the
// last saved state rather than the registry default.
const saved = reactive<Record<string, string>>({ ...values });

function submit() {
    form.post('/admin/homepage-content', {
        preserveScroll: true,
        onSuccess: () => {
            for (const key of Object.keys(form.values)) saved[key] = form.values[key];
            toast.success('Startseiten-Inhalte gespeichert.');
        },
    });
}

function resetSection(section: Section) {
    for (const field of section.fields) form.values[field.key] = saved[field.key];
}

function isDirty(section: Section) {
    return section.fields.some((f) => form.values[f.key] !== saved[f.key]);
}

// Laravel reports per-field failures as "values.<key>", which is not a
// statically known property of the form's error shape.
function errorFor(key: string): string | undefined {
    return (form.errors as unknown as Record<string, string | undefined>)[`values.${key}`];
}
</script>

<template>
    <Head><title>Startseite</title></Head>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-card border border-ink-100 bg-white px-6 py-5 shadow-card">
            <div>
                <h1 class="font-display text-lg font-bold text-navy-700">Texte der Startseite</h1>
                <p class="mt-0.5 text-sm text-ink-500">
                    Änderungen sind sofort nach dem Speichern auf der Startseite sichtbar.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="/"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 rounded-pill border border-ink-300 px-5 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700"
                >
                    <ExternalLink :size="15" aria-hidden="true" /> Startseite ansehen
                </a>
                <button
                    type="button"
                    :disabled="form.processing"
                    class="rounded-pill bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                    @click="submit"
                >
                    Alle Änderungen speichern
                </button>
            </div>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <PageCard v-for="section in sections" :key="section.key" :title="section.label" :subtitle="section.hint ?? undefined">
                <template #actions>
                    <button
                        v-if="isDirty(section)"
                        type="button"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 transition hover:text-navy-700"
                        @click="resetSection(section)"
                    >
                        <RotateCcw :size="14" aria-hidden="true" /> Änderungen verwerfen
                    </button>
                </template>

                <div class="grid gap-5 p-6 sm:p-8 lg:grid-cols-2">
                    <div
                        v-for="field in section.fields"
                        :key="field.key"
                        :class="field.type === 'textarea' ? 'lg:col-span-2' : ''"
                    >
                        <label :for="field.key" class="mb-1.5 block text-sm font-semibold text-navy-700">
                            {{ field.label }}
                        </label>
                        <textarea
                            v-if="field.type === 'textarea'"
                            :id="field.key"
                            v-model="form.values[field.key]"
                            rows="3"
                            class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] leading-relaxed focus:border-green-500 focus:outline-none"
                        />
                        <input
                            v-else
                            :id="field.key"
                            v-model="form.values[field.key]"
                            type="text"
                            class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none"
                        />
                        <p v-if="field.hint" class="mt-1 text-xs text-ink-500">{{ field.hint }}</p>
                        <p v-if="errorFor(field.key)" class="mt-1 text-sm text-red-600">{{ errorFor(field.key) }}</p>
                    </div>

                    <div class="lg:col-span-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-pill bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                        >
                            Speichern
                        </button>
                    </div>
                </div>
            </PageCard>
        </form>
    </div>
</template>
