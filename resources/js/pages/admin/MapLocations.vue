<script setup lang="ts">
import MapPicker from '@/components/admin/MapPicker.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Check, MapPin, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Location = {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    isManual: boolean;
    isCovered: boolean;
    providers: number;
};

const props = defineProps<{ locations: Location[] }>();

const coveredCount = computed(() => props.locations.filter((l) => l.isCovered || l.providers > 0).length);

function toggleCovered(loc: Location) {
    router.post(`/admin/map-locations/${loc.id}/covered`, {}, { preserveScroll: true });
}

function bulk(covered: boolean) {
    const msg = covered
        ? 'Alle Standorte grün anzeigen?'
        : 'Bei allen Standorten die Markierung entfernen?';
    if (!confirm(msg)) return;

    router.post('/admin/map-locations/bulk', { covered }, { preserveScroll: true });
}

const createForm = useForm({ name: '', latitude: null as number | null, longitude: null as number | null });
const editingId = ref<number | null>(null);
const editForm = useForm({ name: '' });

const picked = computed(() =>
    createForm.latitude !== null && createForm.longitude !== null
        ? { lat: createForm.latitude, lng: createForm.longitude }
        : null,
);
const lookingUp = ref(false);

// Dropping a pin fills the name in from the map, but the admin stays free to
// overwrite it before saving.
async function onPick(point: { lat: number; lng: number }) {
    createForm.clearErrors();
    createForm.latitude = point.lat;
    createForm.longitude = point.lng;
    lookingUp.value = true;

    try {
        const res = await fetch('/admin/map-locations/reverse-geocode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify(point),
        });

        if (res.ok) {
            const { name } = await res.json();
            if (name) createForm.name = name;
        }
    } catch {
        // Leaving the name blank is fine; the admin can simply type it.
    } finally {
        lookingUp.value = false;
    }
}

function clearPick() {
    createForm.latitude = null;
    createForm.longitude = null;
}

function add() {
    createForm.post('/admin/map-locations', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
}

function startEdit(loc: Location) {
    editingId.value = loc.id;
    editForm.clearErrors();
    editForm.name = loc.name;
}

function saveEdit() {
    if (editingId.value === null) return;

    editForm.post(`/admin/map-locations/${editingId.value}`, {
        preserveScroll: true,
        onSuccess: () => (editingId.value = null),
    });
}

function remove(loc: Location) {
    if (!confirm(`„${loc.name}“ wirklich von der Karte entfernen?`)) return;

    router.delete(`/admin/map-locations/${loc.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head><title>Kartenstandorte</title></Head>

    <PageCard
        title="Kartenstandorte"
        :subtitle="`Angehakte Standorte erscheinen grün auf der öffentlichen Karte. ${coveredCount} von ${locations.length} grün.`"
    >
        <template #actions>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="rounded-pill bg-green-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-green-700"
                    @click="bulk(true)"
                >
                    Alle auswählen
                </button>
                <button
                    type="button"
                    class="rounded-pill border border-ink-300 px-4 py-2 text-sm font-bold text-ink-700 transition hover:bg-sand-50"
                    @click="bulk(false)"
                >
                    Alle abwählen
                </button>
            </div>
        </template>

        <div class="px-5 py-6 sm:px-6">
        <div class="mb-8">
            <MapPicker :locations="locations" :picked="picked" @pick="onPick" />

            <form class="mt-4 flex flex-wrap items-start gap-3" @submit.prevent="add">
                <div class="min-w-[240px] flex-1">
                    <input
                        v-model="createForm.name"
                        type="text"
                        placeholder="Stadt eintippen oder oben auf die Karte klicken"
                        class="w-full rounded-pill border border-ink-300 px-4 py-2.5 text-sm"
                    />
                    <p v-if="lookingUp" class="mt-2 px-4 text-sm text-ink-500">Ortsname wird ermittelt…</p>
                    <p v-else-if="picked" class="mt-2 flex items-center gap-2 px-4 text-sm text-green-700">
                        Punkt gesetzt: {{ picked.lat }}, {{ picked.lng }}
                        <button type="button" class="underline" @click="clearPick">zurücksetzen</button>
                    </p>
                    <p v-if="createForm.errors.name" class="mt-2 px-4 text-sm text-red-600">{{ createForm.errors.name }}</p>
                </div>
                <button
                    type="submit"
                    :disabled="createForm.processing || !createForm.name.trim()"
                    class="rounded-pill bg-navy-700 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-navy-800 disabled:opacity-50"
                >
                    {{ createForm.processing ? 'Wird gespeichert…' : 'Hinzufügen' }}
                </button>
            </form>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="loc in locations"
                :key="loc.id"
                class="rounded-card border p-4"
                :class="loc.isCovered || loc.providers > 0 ? 'border-green-500 bg-green-50' : 'border-ink-300 bg-white'"
            >
                <form v-if="editingId === loc.id" @submit.prevent="saveEdit">
                    <input
                        v-model="editForm.name"
                        type="text"
                        class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm"
                    />
                    <p v-if="editForm.errors.name" class="mt-2 text-sm text-red-600">{{ editForm.errors.name }}</p>
                    <div class="mt-3 flex gap-2">
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="rounded-pill bg-navy-700 px-4 py-1.5 text-xs font-bold text-white disabled:opacity-50"
                        >
                            Speichern
                        </button>
                        <button
                            type="button"
                            class="rounded-pill border border-ink-300 px-4 py-1.5 text-xs font-bold text-ink-700"
                            @click="editingId = null"
                        >
                            Abbrechen
                        </button>
                    </div>
                </form>

                <template v-else>
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex min-w-0 gap-3">
                            <button
                                type="button"
                                class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded border-2 transition"
                                :class="loc.isCovered ? 'border-green-600 bg-green-600 text-white' : 'border-ink-300 bg-white hover:border-ink-500'"
                                :aria-pressed="loc.isCovered"
                                :aria-label="`${loc.name} ${loc.isCovered ? 'abwählen' : 'grün anzeigen'}`"
                                @click="toggleCovered(loc)"
                            >
                                <Check v-if="loc.isCovered" :size="15" :stroke-width="3" aria-hidden="true" />
                            </button>
                            <div class="min-w-0">
                                <p class="flex items-center gap-1.5 font-display font-bold text-navy-700">
                                    <MapPin :size="15" aria-hidden="true" />
                                    <span class="truncate">{{ loc.name }}</span>
                                </p>
                                <p class="mt-1 text-xs text-ink-500">
                                    {{ loc.providers > 0 ? `${loc.providers} geprüfte Anbieter` : 'Keine geprüften Anbieter' }}
                                </p>
                                <p class="mt-0.5 text-xs text-ink-500">
                                    {{ loc.providers > 0 ? 'Grün durch Anbieter' : loc.isCovered ? 'Grün (manuell)' : 'Nicht grün' }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 rounded-pill p-1.5 text-ink-500 transition hover:bg-red-50 hover:text-red-600"
                            :aria-label="`${loc.name} entfernen`"
                            @click="remove(loc)"
                        >
                            <Trash2 :size="16" aria-hidden="true" />
                        </button>
                    </div>
                    <button
                        type="button"
                        class="mt-3 text-xs font-bold text-navy-700 underline"
                        @click="startEdit(loc)"
                    >
                        Bearbeiten
                    </button>
                </template>
            </div>
        </div>

        <p v-if="!locations.length" class="py-10 text-center text-ink-500">Noch keine Standorte angelegt.</p>
        </div>
    </PageCard>
</template>
