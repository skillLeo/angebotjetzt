<script setup lang="ts">
import RichTextEditor from '@/components/admin/RichTextEditor.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ExternalLink } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

type Field = { key: string; label: string; type: string; value: string };
type LegalPage = { key: string; label: string; url: string; fields: Field[] };

const props = defineProps<{ pages: LegalPage[] }>();

function fieldValue(page: LegalPage, suffix: string): string {
    return page.fields.find((f) => f.key.endsWith(`.${suffix}`))?.value ?? '';
}

function fieldLabel(page: LegalPage, suffix: string): string {
    return page.fields.find((f) => f.key.endsWith(`.${suffix}`))?.label ?? '';
}

// One independent form per page, so saving the AGB never touches the
// Datenschutzerklärung.
const forms = Object.fromEntries(
    props.pages.map((page) => [
        page.key,
        useForm({
            title: fieldValue(page, 'title'),
            updated: fieldValue(page, 'updated'),
            body: fieldValue(page, 'body'),
        }),
    ]),
);

function submit(page: LegalPage) {
    forms[page.key].post(`/admin/legal-content/${page.key}`, {
        preserveScroll: true,
        onSuccess: () => toast.success(`${page.label} gespeichert.`),
    });
}
</script>

<template>
    <Head><title>Rechtliche Seiten</title></Head>

    <div class="space-y-6">
        <div class="rounded-card border border-ink-100 bg-white px-6 py-5 shadow-card">
            <h1 class="font-display text-lg font-bold text-navy-700">Rechtliche Seiten</h1>
            <p class="mt-0.5 text-sm text-ink-500">
                Impressum, Datenschutz, AGB und Cookie-Richtlinie. Änderungen sind sofort nach dem Speichern
                auf der jeweiligen Seite sichtbar. Das Inhaltsverzeichnis am Seitenrand wird automatisch aus den
                Abschnitts-Überschriften erzeugt.
            </p>
        </div>

        <PageCard v-for="page in pages" :key="page.key" :title="page.label">
            <template #actions>
                <a
                    :href="page.url"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-ink-500 transition hover:text-navy-700"
                >
                    <ExternalLink :size="14" aria-hidden="true" /> Seite ansehen
                </a>
            </template>

            <form class="space-y-5 p-6 sm:p-8" @submit.prevent="submit(page)">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label :for="`${page.key}-title`" class="mb-1.5 block text-sm font-semibold text-navy-700">
                            {{ fieldLabel(page, 'title') }}
                        </label>
                        <input
                            :id="`${page.key}-title`"
                            v-model="forms[page.key].title"
                            type="text"
                            class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none"
                        />
                        <p v-if="forms[page.key].errors.title" class="mt-1 text-sm text-red-600">
                            {{ forms[page.key].errors.title }}
                        </p>
                    </div>
                    <div>
                        <label :for="`${page.key}-updated`" class="mb-1.5 block text-sm font-semibold text-navy-700">
                            {{ fieldLabel(page, 'updated') }}
                        </label>
                        <input
                            :id="`${page.key}-updated`"
                            v-model="forms[page.key].updated"
                            type="text"
                            class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none"
                        />
                        <p v-if="forms[page.key].errors.updated" class="mt-1 text-sm text-red-600">
                            {{ forms[page.key].errors.updated }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-navy-700">Inhalt</label>
                    <RichTextEditor v-model="forms[page.key].body" />
                    <p v-if="forms[page.key].errors.body" class="mt-1 text-sm text-red-600">
                        {{ forms[page.key].errors.body }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="forms[page.key].processing"
                    class="rounded-pill bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                >
                    Speichern
                </button>
            </form>
        </PageCard>
    </div>
</template>
