<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, Send, Upload } from 'lucide-vue-next';
import { computed } from 'vue';

type Row = { line: number; email: string; errors: string[]; ok: boolean };

const props = defineProps<{
    invitations: Array<{ id: number; email: string; sentAt: string | null; registered: boolean }>;
    stats: { total: number; queued: number; batchSize: number };
    report?: Row[];
}>();

const uploadForm = useForm({ file: null as File | null });

const okCount = computed(() => (props.report ?? []).filter((r) => r.ok).length);
const badCount = computed(() => (props.report ?? []).filter((r) => !r.ok).length);

function upload() {
    uploadForm.post('/admin/inspectors/invites/preview', { forceFormData: true, preserveScroll: true });
}

function send() {
    if (!confirm(`${okCount.value} Einladung(en) jetzt versenden?`)) return;
    router.post('/admin/inspectors/invites', {}, { preserveScroll: true });
}
</script>

<template>
    <Head><title>Dienstleister einladen</title></Head>

    <PageCard
        title="Dienstleister per CSV einladen"
        subtitle="Laden Sie eine CSV mit E-Mail-Adressen hoch (eine pro Zeile). Bereits registrierte oder bereits eingeladene Adressen werden automatisch übersprungen."
    >
        <div class="px-5 py-6 sm:px-6">
            <!-- Upload -->
            <form class="rounded-card border border-ink-300 bg-sand-50 p-5" @submit.prevent="upload">
                <label for="invite-csv" class="mb-1.5 block text-xs font-bold text-ink-700">CSV-Datei</label>
                <input
                    id="invite-csv"
                    type="file"
                    accept=".csv,text/csv,text/plain"
                    class="w-full text-sm"
                    @input="uploadForm.file = ($event.target as HTMLInputElement).files?.[0] ?? null"
                />
                <p v-if="uploadForm.errors.file" class="mt-1.5 text-xs font-semibold text-red-600">{{ uploadForm.errors.file }}</p>
                <p class="mt-2 text-xs text-ink-500">
                    Eine E-Mail-Adresse pro Zeile. Eine Kopfzeile „email“ wird automatisch erkannt und übersprungen.
                </p>
                <button
                    type="submit"
                    :disabled="uploadForm.processing || !uploadForm.file"
                    class="mt-4 inline-flex items-center gap-2 rounded-pill bg-navy-700 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-navy-800 disabled:opacity-50"
                >
                    <Upload :size="16" aria-hidden="true" />
                    {{ uploadForm.processing ? 'Wird geprüft…' : 'Datei prüfen' }}
                </button>
            </form>

            <!-- Per-row validation report -->
            <div v-if="report" class="mt-8">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-pill bg-green-50 px-3 py-1 text-sm font-bold text-green-700">
                        {{ okCount }} gültig
                    </span>
                    <span v-if="badCount" class="rounded-pill bg-red-50 px-3 py-1 text-sm font-bold text-red-600">
                        {{ badCount }} übersprungen
                    </span>
                </div>

                <div class="mt-4 overflow-hidden rounded-card border border-ink-300">
                    <table class="w-full text-sm">
                        <thead class="bg-sand-50">
                            <tr class="text-left">
                                <th class="px-4 py-2.5 text-xs font-bold tracking-wide text-ink-500 uppercase">Zeile</th>
                                <th class="px-4 py-2.5 text-xs font-bold tracking-wide text-ink-500 uppercase">E-Mail</th>
                                <th class="px-4 py-2.5 text-xs font-bold tracking-wide text-ink-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in report" :key="row.line" class="border-t border-ink-100">
                                <td class="px-4 py-2.5 text-ink-500">{{ row.line }}</td>
                                <td class="px-4 py-2.5 break-all text-navy-700">{{ row.email }}</td>
                                <td class="px-4 py-2.5">
                                    <span v-if="row.ok" class="inline-flex items-center gap-1.5 font-semibold text-green-700">
                                        <CheckCircle2 :size="15" aria-hidden="true" /> Wird eingeladen
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 font-semibold text-red-600">
                                        <AlertTriangle :size="15" aria-hidden="true" /> {{ row.errors.join(', ') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button
                    v-if="okCount"
                    type="button"
                    class="mt-5 inline-flex items-center gap-2 rounded-pill bg-green-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-green-600"
                    @click="send"
                >
                    <Send :size="16" aria-hidden="true" />
                    {{ okCount }} Einladung(en) versenden
                </button>
                <p v-if="okCount > stats.batchSize" class="mt-2 text-xs text-ink-500">
                    Es werden zunächst {{ stats.batchSize }} verschickt, der Rest folgt automatisch alle paar Minuten.
                </p>
            </div>

            <!-- Log -->
            <div class="mt-10">
                <p class="font-display font-bold text-navy-700">
                    Versendete Einladungen
                    <span class="ml-2 text-sm font-normal text-ink-500">
                        {{ stats.total }} gesamt<span v-if="stats.queued">, {{ stats.queued }} in Warteschlange</span>
                    </span>
                </p>

                <div v-if="invitations.length" class="mt-3 overflow-hidden rounded-card border border-ink-300">
                    <table class="w-full text-sm">
                        <thead class="bg-sand-50">
                            <tr class="text-left">
                                <th class="px-4 py-2.5 text-xs font-bold tracking-wide text-ink-500 uppercase">E-Mail</th>
                                <th class="px-4 py-2.5 text-xs font-bold tracking-wide text-ink-500 uppercase">Versendet</th>
                                <th class="px-4 py-2.5 text-xs font-bold tracking-wide text-ink-500 uppercase">Registriert</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="inv in invitations" :key="inv.id" class="border-t border-ink-100">
                                <td class="px-4 py-2.5 break-all text-navy-700">{{ inv.email }}</td>
                                <td class="px-4 py-2.5 text-ink-500">{{ inv.sentAt ?? 'In Warteschlange' }}</td>
                                <td class="px-4 py-2.5">
                                    <span v-if="inv.registered" class="font-semibold text-green-700">Ja</span>
                                    <span v-else class="text-ink-500">Noch nicht</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="mt-3 text-sm text-ink-500">Noch keine Einladungen versendet.</p>
            </div>
        </div>
    </PageCard>
</template>
