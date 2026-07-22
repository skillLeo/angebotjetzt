<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, UploadCloud, XCircle } from 'lucide-vue-next';

defineProps<{
    report?: Array<{ row: number; data: Record<string, string>; errors: string[]; ok: boolean }>;
}>();

const form = useForm({ file: null as File | null });

function upload(e: Event) {
    form.file = (e.target as HTMLInputElement).files?.[0] ?? null;
    if (form.file) {
        form.post('/admin/gutachter/import/vorschau', { forceFormData: true, preserveScroll: true });
    }
}

function confirmImport() {
    router.post('/admin/gutachter/import');
}
</script>

<template>
    <Head><title>Gutachter importieren</title></Head>

    <Link href="/admin/gutachter" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> Zurück
    </Link>

    <PageCard title="Gutachter per CSV importieren" subtitle="Spalten: name; company; email; phone; city; plz_von; plz_bis (Semikolon oder Komma getrennt)">
        <div class="p-5 sm:p-6">
            <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-card border-2 border-dashed border-ink-300 px-6 py-10 text-center transition hover:border-green-500 hover:bg-green-50">
                <UploadCloud :size="30" class="text-ink-500" aria-hidden="true" />
                <span class="text-sm font-medium text-ink-700">CSV-Datei auswählen</span>
                <input type="file" accept=".csv,text/csv" class="hidden" @change="upload" />
            </label>
            <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</p>
        </div>

        <div v-if="report" class="border-t border-ink-100 p-5 sm:p-6">
            <div class="mb-4 flex items-center justify-between">
                <p class="font-display font-bold text-navy-700">
                    Validierung: {{ report.filter((r) => r.ok).length }} gültig, {{ report.filter((r) => !r.ok).length }} fehlerhaft
                </p>
                <button
                    v-if="report.some((r) => r.ok)"
                    type="button"
                    class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-green-600"
                    @click="confirmImport"
                >
                    {{ report.filter((r) => r.ok).length }} Gutachter importieren
                </button>
            </div>
            <div class="space-y-2">
                <div v-for="r in report" :key="r.row" class="flex items-start gap-3 rounded-card px-4 py-3" :class="r.ok ? 'bg-green-50' : 'bg-red-50'">
                    <CheckCircle2 v-if="r.ok" :size="18" class="mt-0.5 shrink-0 text-green-600" aria-hidden="true" />
                    <XCircle v-else :size="18" class="mt-0.5 shrink-0 text-red-600" aria-hidden="true" />
                    <div class="min-w-0 text-sm">
                        <p class="font-semibold text-navy-700">Zeile {{ r.row }}: {{ r.data.name || '(kein Name)' }} · {{ r.data.email }}</p>
                        <p v-if="!r.ok" class="text-red-600">{{ r.errors.join(', ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </PageCard>
</template>
