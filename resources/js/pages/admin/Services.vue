<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ImagePlus, Pencil, Plus, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

type FlowMode = 'offer' | 'direct_accept' | 'external';

type ServiceTypeRow = { id: number; name: string; description: string | null; image: string | null; active: boolean; flowMode: FlowMode; externalUrl: string | null };

// Plain-language wording, shown in the admin so the behaviour of each mode is
// self-explanatory rather than something to ask about.
const FLOW_MODES: Array<{ value: FlowMode; label: string; hint: string }> = [
    { value: 'offer', label: 'Normal (Angebote einholen)', hint: 'Kunden stellen eine Anfrage, Anbieter senden Angebote, der Kunde vergleicht und beauftragt.' },
    { value: 'direct_accept', label: 'Direkt beauftragen (ohne Angebote)', hint: 'Kunden beantworten zwei Zusatzfragen und beauftragen direkt. Es gibt kein Preisfeld und keinen Angebotsvergleich.' },
    { value: 'external', label: 'Weiterleitung zum Partner', hint: 'Die Leistung ist sichtbar, aber nicht buchbar. Kunden werden zum Partner weitergeleitet.' },
];

function flowLabel(m: FlowMode) {
    return FLOW_MODES.find((f) => f.value === m)?.label ?? m;
}

defineProps<{
    categories: Array<{ id: number; name: string; slug: string; active: boolean; types: ServiceTypeRow[]; interest: number }>;
}>();

const processing = ref<number | null>(null);
function toggle(id: number) {
    processing.value = id;
    router.post(`/admin/categories/${id}/status`, {}, { preserveScroll: true, onFinish: () => (processing.value = null) });
}

const addingFor = ref<number | null>(null);
const editingId = ref<number | null>(null);

const addForm = useForm<{ name: string; description: string; photo: File | null }>({ name: '', description: '', photo: null });
const editForm = useForm<{ name: string; description: string; photo: File | null; flow_mode: FlowMode; external_url: string }>({ name: '', description: '', photo: null, flow_mode: 'offer', external_url: '' });

function startAdd(categoryId: number) {
    editingId.value = null;
    addingFor.value = categoryId;
    addForm.reset();
    addForm.clearErrors();
}
function cancelAdd() {
    addingFor.value = null;
}
function submitAdd(categoryId: number) {
    addForm.post(`/admin/categories/${categoryId}/service-types`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            addingFor.value = null;
            addForm.reset();
        },
    });
}

function startEdit(type: ServiceTypeRow) {
    addingFor.value = null;
    editingId.value = type.id;
    editForm.name = type.name;
    editForm.description = type.description ?? '';
    editForm.photo = null;
    editForm.flow_mode = type.flowMode ?? 'offer';
    editForm.external_url = type.externalUrl ?? '';
    editForm.clearErrors();
}
function cancelEdit() {
    editingId.value = null;
}
function submitEdit(id: number) {
    editForm.post(`/admin/service-types/${id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => (editingId.value = null),
    });
}

function remove(id: number) {
    router.delete(`/admin/service-types/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head><title>Dienstleistungen</title></Head>

    <PageCard title="Kategorien & Dienstleistungen" subtitle="Aktivieren Sie eine Kategorie, um sie auf der Startseite freizuschalten, und verwalten Sie die einzelnen Leistungen darin.">
        <div class="divide-y divide-ink-100">
            <div v-for="cat in categories" :key="cat.id" class="flex flex-col gap-4 px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="font-display font-bold text-navy-700">{{ cat.name }}</p>
                            <span class="rounded-pill px-2.5 py-0.5 text-xs font-bold" :class="cat.active ? 'bg-green-50 text-green-700' : 'bg-ink-100 text-ink-500'">
                                {{ cat.active ? 'Aktiv' : 'Demnächst' }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-ink-500">
                            {{ cat.types.length }} Leistungen<span v-if="cat.interest > 0"> · {{ cat.interest }} Interessenten</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        :disabled="processing === cat.id"
                        class="rounded-pill px-5 py-2 text-sm font-bold transition disabled:opacity-60"
                        :class="cat.active ? 'border border-ink-300 text-ink-700 hover:border-navy-700' : 'bg-green-500 text-white hover:bg-green-600'"
                        @click="toggle(cat.id)"
                    >
                        {{ cat.active ? 'Deaktivieren' : 'Aktivieren' }}
                    </button>
                </div>

                <ul v-if="cat.types.length" class="space-y-2">
                    <li v-for="t in cat.types" :key="t.id" class="rounded-card border border-ink-100 bg-sand-50/60 p-4">
                        <div v-if="editingId !== t.id" class="flex items-center gap-4">
                            <div class="h-14 w-20 shrink-0 overflow-hidden rounded-xs bg-ink-100">
                                <img v-if="t.image" :src="t.image" :alt="t.name" class="h-full w-full object-cover" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-navy-700">{{ t.name }}</p>
                                    <!-- Only flagged when it deviates from the normal flow, so the
                                         exceptions stand out instead of every row carrying a badge. -->
                                    <span
                                        v-if="t.flowMode && t.flowMode !== 'offer'"
                                        class="rounded-pill px-2.5 py-0.5 text-[11px] font-bold"
                                        :class="t.flowMode === 'external' ? 'bg-amber-100 text-amber-700' : 'bg-navy-50 text-navy-600'"
                                    >
                                        {{ t.flowMode === 'external' ? 'Weiterleitung zum Partner' : 'Direkt beauftragen' }}
                                    </span>
                                </div>
                                <p v-if="t.description" class="mt-0.5 truncate text-sm text-ink-500">{{ t.description }}</p>
                                <p v-if="t.flowMode === 'external' && t.externalUrl" class="mt-0.5 truncate text-xs text-ink-500">
                                    {{ t.externalUrl }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <Link :href="`/admin/service-types/${t.id}/fields`" class="rounded-pill px-3 py-1.5 text-xs font-semibold text-green-600 hover:bg-green-50">
                                    Felder
                                </Link>
                                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-pill text-ink-500 transition hover:bg-navy-50 hover:text-navy-700" aria-label="Bearbeiten" @click="startEdit(t)">
                                    <Pencil :size="16" aria-hidden="true" />
                                </button>
                                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-pill text-ink-500 transition hover:bg-red-50 hover:text-red-600" aria-label="Entfernen" @click="remove(t.id)">
                                    <Trash2 :size="16" aria-hidden="true" />
                                </button>
                            </div>
                        </div>

                        <form v-else class="space-y-3" @submit.prevent="submitEdit(t.id)">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-navy-700">Name</label>
                                    <input v-model="editForm.name" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none" />
                                    <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-600">{{ editForm.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-semibold text-navy-700">Foto (optional)</label>
                                    <label class="flex h-[38px] w-full cursor-pointer items-center gap-2 rounded-card border border-dashed border-ink-300 px-3 text-sm text-ink-500 hover:border-green-500">
                                        <ImagePlus :size="16" aria-hidden="true" />
                                        {{ editForm.photo ? editForm.photo.name : 'Datei wählen …' }}
                                        <input type="file" accept="image/*" class="hidden" @change="(e) => (editForm.photo = (e.target as HTMLInputElement).files?.[0] ?? null)" />
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-navy-700">Beschreibung</label>
                                <textarea v-model="editForm.description" rows="2" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none" />
                                <p v-if="editForm.errors.description" class="mt-1 text-xs text-red-600">{{ editForm.errors.description }}</p>

                                <div class="rounded-card bg-sand-50 p-3">
                                    <label class="block text-xs font-bold text-navy-700">{{ 'Ablauf dieser Leistung' }}</label>
                                    <select
                                        v-model="editForm.flow_mode"
                                        class="mt-1.5 w-full rounded-card border border-ink-300 bg-white px-3 py-2 text-sm focus:border-green-500 focus:outline-none"
                                    >
                                        <option v-for="m in FLOW_MODES" :key="m.value" :value="m.value">{{ m.label }}</option>
                                    </select>
                                    <p class="mt-1.5 text-xs text-ink-500">
                                        {{ FLOW_MODES.find((m) => m.value === editForm.flow_mode)?.hint }}
                                    </p>
                                    <p v-if="editForm.errors.flow_mode" class="mt-1 text-xs text-red-600">{{ editForm.errors.flow_mode }}</p>

                                    <div v-if="editForm.flow_mode === 'external'" class="mt-3">
                                        <label class="block text-xs font-bold text-navy-700">{{ 'Partner-URL' }}</label>
                                        <input
                                            v-model="editForm.external_url"
                                            type="url"
                                            placeholder="https://www.carspector.de"
                                            class="mt-1.5 w-full rounded-card border border-ink-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none"
                                        />
                                        <p class="mt-1 text-xs text-ink-500">
                                            {{ 'Dorthin werden Kunden geschickt, die diese Leistung auswählen.' }}
                                        </p>
                                        <p v-if="editForm.errors.external_url" class="mt-1 text-xs text-red-600">{{ editForm.errors.external_url }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" :disabled="editForm.processing" class="rounded-pill bg-green-500 px-4 py-2 text-sm font-bold text-white hover:bg-green-600 disabled:opacity-60">Speichern</button>
                                <button type="button" class="flex items-center gap-1 rounded-pill px-3 py-2 text-sm font-semibold text-ink-500 hover:text-navy-700" @click="cancelEdit">
                                    <X :size="14" aria-hidden="true" /> Abbrechen
                                </button>
                            </div>
                        </form>
                    </li>
                </ul>
                <p v-else class="rounded-card border border-dashed border-ink-200 px-4 py-6 text-center text-sm text-ink-500">Noch keine Leistungen in dieser Kategorie.</p>

                <form v-if="addingFor === cat.id" class="space-y-3 rounded-card border border-green-200 bg-green-50/40 p-4" @submit.prevent="submitAdd(cat.id)">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-navy-700">Name</label>
                            <input v-model="addForm.name" type="text" placeholder="z. B. Unfallschadengutachten" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none" />
                            <p v-if="addForm.errors.name" class="mt-1 text-xs text-red-600">{{ addForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-navy-700">Foto (optional)</label>
                            <label class="flex h-[38px] w-full cursor-pointer items-center gap-2 rounded-card border border-dashed border-ink-300 px-3 text-sm text-ink-500 hover:border-green-500">
                                <ImagePlus :size="16" aria-hidden="true" />
                                {{ addForm.photo ? addForm.photo.name : 'Datei wählen …' }}
                                <input type="file" accept="image/*" class="hidden" @change="(e) => (addForm.photo = (e.target as HTMLInputElement).files?.[0] ?? null)" />
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-navy-700">Beschreibung</label>
                        <textarea v-model="addForm.description" rows="2" placeholder="Kurze Beschreibung für die öffentliche Seite" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none" />
                        <p v-if="addForm.errors.description" class="mt-1 text-xs text-red-600">{{ addForm.errors.description }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" :disabled="addForm.processing" class="inline-flex items-center gap-2 rounded-pill bg-green-500 px-4 py-2 text-sm font-bold text-white hover:bg-green-600 disabled:opacity-60">
                            <Plus :size="16" aria-hidden="true" /> Leistung anlegen
                        </button>
                        <button type="button" class="flex items-center gap-1 rounded-pill px-3 py-2 text-sm font-semibold text-ink-500 hover:text-navy-700" @click="cancelAdd">
                            <X :size="14" aria-hidden="true" /> Abbrechen
                        </button>
                    </div>
                </form>
                <button
                    v-else
                    type="button"
                    class="inline-flex items-center gap-2 self-start rounded-pill border border-dashed border-ink-300 px-4 py-2 text-sm font-semibold text-ink-500 transition hover:border-green-500 hover:text-green-600"
                    @click="startAdd(cat.id)"
                >
                    <Plus :size="16" aria-hidden="true" /> Leistung hinzufügen
                </button>
            </div>
        </div>
    </PageCard>
</template>
