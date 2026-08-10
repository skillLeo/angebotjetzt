<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, Trash2, UploadCloud, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    settings: { commission_percent: number | string; stripe_configured: boolean; logoUrl: string | null };
}>();

const form = useForm({ commission_percent: props.settings.commission_percent });

function submit() {
    form.post('/admin/settings', {
        preserveScroll: true,
        onSuccess: () => toast.success('Einstellungen gespeichert.'),
    });
}

// --- Logo upload + live preview ---

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);
const selectedFile = ref<File | null>(null);
const looksTransparent = ref(true);
const checkingTransparency = ref(false);
const logoForm = useForm<{ logo: File | null }>({ logo: null });
const removing = ref(false);

/**
 * Reasonable-effort heuristic only, as scoped: rasters (PNG/WebP) are
 * flagged if all four corners are fully opaque, since that's exactly the
 * "flat background box" shape the previous logo incident produced. SVGs
 * are skipped — transparency there depends on markup, not pixels, and a
 * false positive would be more misleading than no check at all.
 */
function checkTransparency(file: File): Promise<boolean> {
    return new Promise((resolve) => {
        if (file.type === 'image/svg+xml') {
            resolve(true);
            return;
        }
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            const ctx = canvas.getContext('2d');
            if (!ctx || canvas.width === 0 || canvas.height === 0) {
                resolve(true);
                return;
            }
            ctx.drawImage(img, 0, 0);
            const corners: Array<[number, number]> = [
                [0, 0],
                [canvas.width - 1, 0],
                [0, canvas.height - 1],
                [canvas.width - 1, canvas.height - 1],
            ];
            try {
                const allOpaque = corners.every(([x, y]) => ctx.getImageData(x, y, 1, 1).data[3] >= 250);
                resolve(!allOpaque);
            } catch {
                resolve(true); // canvas tainted or unreadable — don't block on a check we can't run
            }
        };
        img.onerror = () => resolve(true);
        img.src = URL.createObjectURL(file);
    });
}

async function onFileSelected(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    if (!file) return;

    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    selectedFile.value = file;
    previewUrl.value = URL.createObjectURL(file);
    logoForm.logo = file;

    checkingTransparency.value = true;
    looksTransparent.value = await checkTransparency(file);
    checkingTransparency.value = false;
}

function clearSelection() {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
    previewUrl.value = null;
    selectedFile.value = null;
    logoForm.logo = null;
    if (fileInput.value) fileInput.value.value = '';
}

function saveLogo() {
    logoForm.post('/admin/settings/logo', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Logo gespeichert und überall aktualisiert.');
            clearSelection();
        },
    });
}

function removeLogo() {
    removing.value = true;
    router.delete('/admin/settings/logo', {
        preserveScroll: true,
        onSuccess: () => toast.success('Logo auf Standard zurückgesetzt.'),
        onFinish: () => (removing.value = false),
    });
}

const currentLogoUrl = computed(() => previewUrl.value ?? props.settings.logoUrl);
</script>

<template>
    <Head><title>Einstellungen</title></Head>

    <div class="grid gap-6 lg:grid-cols-2">
        <PageCard title="Provision">
            <form class="space-y-5 p-6 sm:p-8" @submit.prevent="submit">
                <div>
                    <label for="comm" class="mb-1.5 block text-sm font-semibold text-navy-700">Plattform-Provision (%)</label>
                    <input id="comm" v-model="form.commission_percent" type="number" step="0.1" min="0" max="50" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.commission_percent" class="mt-1 text-sm text-red-600">{{ form.errors.commission_percent }}</p>
                    <p class="mt-1 text-xs text-ink-500">Wird auf jede neue Buchung angewendet.</p>
                </div>
                <button type="submit" :disabled="form.processing" class="rounded-pill bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                    Speichern
                </button>
            </form>
        </PageCard>

        <PageCard title="Zahlungsanbindung">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-3 rounded-card px-4 py-3" :class="settings.stripe_configured ? 'bg-green-50' : 'bg-amber-50'">
                    <CheckCircle2 v-if="settings.stripe_configured" :size="20" class="text-green-600" aria-hidden="true" />
                    <XCircle v-else :size="20" class="text-amber-600" aria-hidden="true" />
                    <div>
                        <p class="text-sm font-semibold" :class="settings.stripe_configured ? 'text-green-800' : 'text-amber-800'">
                            Stripe {{ settings.stripe_configured ? 'konfiguriert' : 'nicht konfiguriert' }}
                        </p>
                        <p class="text-xs text-ink-500">STRIPE_SECRET und STRIPE_WEBHOOK_SECRET in der .env-Datei setzen.</p>
                    </div>
                </div>
            </div>
        </PageCard>

        <PageCard title="Logo" subtitle="Gilt für Header, Anmeldeseiten, alle Portale und E-Mails" class="lg:col-span-2">
            <div class="space-y-5 p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-3">
                    <input ref="fileInput" type="file" accept=".png,.svg,.webp,image/png,image/svg+xml,image/webp" class="hidden" @change="onFileSelected" />
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-pill border border-ink-300 px-5 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700"
                        @click="fileInput?.click()"
                    >
                        <UploadCloud :size="16" aria-hidden="true" /> Neues Logo wählen
                    </button>
                    <span class="text-xs text-ink-500">PNG, SVG oder WebP, max. 2&nbsp;MB.</span>
                </div>

                <div v-if="checkingTransparency" class="text-sm text-ink-500">Bild wird geprüft…</div>
                <div v-else-if="selectedFile && !looksTransparent" class="flex items-start gap-2.5 rounded-card bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <AlertTriangle :size="18" class="mt-0.5 shrink-0" aria-hidden="true" />
                    <span>
                        Dieses Bild scheint keinen transparenten Hintergrund zu haben — die Ecken sind vollständig deckend.
                        Das kann auf dunklem Hintergrund als sichtbarer Kasten erscheinen (siehe Vorschau unten). Sie können
                        trotzdem speichern, prüfen Sie aber zuerst die Vorschau.
                    </span>
                </div>

                <div v-if="currentLogoUrl">
                    <p class="mb-2 text-sm font-semibold text-navy-700">{{ selectedFile ? 'Live-Vorschau (noch nicht gespeichert)' : 'Aktuelles Logo' }}</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="flex h-24 items-center justify-center rounded-card border border-ink-100 bg-white px-6">
                            <BrandLogo :preview-src="currentLogoUrl" />
                        </div>
                        <div class="flex h-24 items-center justify-center rounded-card bg-navy-950 px-6">
                            <BrandLogo :preview-src="currentLogoUrl" inverted />
                        </div>
                    </div>
                    <p class="mt-1.5 grid grid-cols-2 gap-3 text-center text-xs text-ink-500">
                        <span>Heller Hintergrund (z. B. Header)</span>
                        <span>Dunkler Hintergrund (z. B. Portal-Menü)</span>
                    </p>
                </div>
                <p v-else class="text-sm text-ink-500">Kein individuelles Logo hochgeladen — das Standard-Logo wird verwendet.</p>

                <div class="flex flex-wrap items-center gap-3 border-t border-ink-100 pt-5">
                    <button
                        v-if="selectedFile"
                        type="button"
                        :disabled="logoForm.processing"
                        class="rounded-pill bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                        @click="saveLogo"
                    >
                        Logo speichern
                    </button>
                    <button v-if="selectedFile" type="button" class="text-sm font-semibold text-ink-500 hover:text-navy-700" @click="clearSelection">
                        Abbrechen
                    </button>
                    <button
                        v-if="!selectedFile && settings.logoUrl"
                        type="button"
                        :disabled="removing"
                        class="inline-flex items-center gap-1.5 rounded-pill border border-ink-300 px-5 py-2.5 text-sm font-bold text-red-600 transition hover:border-red-600 disabled:opacity-60"
                        @click="removeLogo"
                    >
                        <Trash2 :size="15" aria-hidden="true" /> Auf Standard-Logo zurücksetzen
                    </button>
                </div>
            </div>
        </PageCard>
    </div>
</template>
