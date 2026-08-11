<script setup lang="ts">
import PhotoUpload from '@/components/admin/PhotoUpload.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Trash2, Wrench } from 'lucide-vue-next';
import { ref } from 'vue';

type Partner = {
    id: number;
    name: string;
    city: string | null;
    reviewsCount: number;
    rating: number | null;
    jobsCount: number;
    memberSince: string | null;
    photo: string | null;
    sortOrder: number;
};

defineProps<{ partners: Partner[] }>();

const blank = {
    name: '',
    city: '',
    reviews_count: 0,
    rating: 5,
    jobs_count: 0,
    member_since: '',
    sort_order: 0,
    photo: null as File | null,
};

const createForm = useForm({ ...blank });
const editingId = ref<number | null>(null);
const editForm = useForm({ ...blank });

function add() {
    createForm.post('/admin/homepage-partners', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => createForm.reset(),
    });
}

function startEdit(p: Partner) {
    editingId.value = p.id;
    editForm.clearErrors();
    editForm.name = p.name;
    editForm.city = p.city ?? '';
    editForm.reviews_count = p.reviewsCount;
    editForm.rating = p.rating ?? 5;
    editForm.jobs_count = p.jobsCount;
    editForm.member_since = p.memberSince ?? '';
    editForm.sort_order = p.sortOrder;
    editForm.photo = null;
}

function saveEdit() {
    if (editingId.value === null) return;

    editForm.post(`/admin/homepage-partners/${editingId.value}`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => (editingId.value = null),
    });
}

function remove(p: Partner) {
    if (!confirm(`Partner „${p.name}“ wirklich entfernen?`)) return;

    router.delete(`/admin/homepage-partners/${p.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head><title>Startseiten-Partner</title></Head>

    <PageCard
        title="Startseiten-Partner"
        subtitle="Diese Partner erscheinen im Anbieterbereich der Startseite, zusätzlich zu den registrierten geprüften Anbietern."
    >
        <div class="px-5 py-6 sm:px-6">
        <form class="mb-8 rounded-card border border-ink-300 bg-sand-50 p-5" @submit.prevent="add">
            <p class="mb-4 font-display font-bold text-navy-700">Neuer Partner</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Name</span>
                    <input v-model="createForm.name" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                    <span v-if="createForm.errors.name" class="mt-1 block text-xs text-red-600">{{ createForm.errors.name }}</span>
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Standort</span>
                    <input v-model="createForm.city" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Dabei seit</span>
                    <input v-model="createForm.member_since" type="text" placeholder="Januar 2026" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Anzahl Bewertungen</span>
                    <input v-model.number="createForm.reviews_count" type="number" min="0" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Durchschnittsbewertung</span>
                    <input v-model.number="createForm.rating" type="number" min="1" max="5" step="0.1" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Abgeschlossene Aufträge</span>
                    <input v-model.number="createForm.jobs_count" type="number" min="0" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                </label>
                <PhotoUpload
                    v-model="createForm.photo"
                    label="Foto / Logo (optional)"
                    shape="square"
                    :error="createForm.errors.photo"
                    class="sm:col-span-2"
                />
            </div>
            <button
                type="submit"
                :disabled="createForm.processing"
                class="mt-4 rounded-pill bg-navy-700 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-navy-800 disabled:opacity-50"
            >
                Partner hinzufügen
            </button>
        </form>

        <div class="space-y-3">
            <div v-for="p in partners" :key="p.id" class="rounded-card border border-ink-300 bg-white p-5">
                <form v-if="editingId === p.id" @submit.prevent="saveEdit">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Name</span>
                            <input v-model="editForm.name" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Standort</span>
                            <input v-model="editForm.city" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Dabei seit</span>
                            <input v-model="editForm.member_since" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Anzahl Bewertungen</span>
                            <input v-model.number="editForm.reviews_count" type="number" min="0" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Durchschnittsbewertung</span>
                            <input v-model.number="editForm.rating" type="number" min="1" max="5" step="0.1" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Abgeschlossene Aufträge</span>
                            <input v-model.number="editForm.jobs_count" type="number" min="0" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <PhotoUpload
                            v-model="editForm.photo"
                            label="Foto / Logo"
                            shape="square"
                            :current="p.photo"
                            :error="editForm.errors.photo"
                            class="sm:col-span-2"
                        />
                    </div>
                    <div class="mt-3 flex gap-2">
                        <button type="submit" :disabled="editForm.processing" class="rounded-pill bg-navy-700 px-5 py-2 text-xs font-bold text-white disabled:opacity-50">
                            Speichern
                        </button>
                        <button type="button" class="rounded-pill border border-ink-300 px-5 py-2 text-xs font-bold text-ink-700" @click="editingId = null">
                            Abbrechen
                        </button>
                    </div>
                </form>

                <div v-else class="flex items-start justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <img v-if="p.photo" :src="p.photo" :alt="p.name" class="h-14 w-14 shrink-0 rounded-pill object-cover" />
                        <span v-else class="flex h-14 w-14 shrink-0 items-center justify-center rounded-pill bg-sand-100 text-ink-500">
                            <Wrench :size="22" aria-hidden="true" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-display font-bold text-navy-700">{{ p.name }}</p>
                            <p class="mt-0.5 text-xs text-ink-500">
                                {{ p.city ?? '—' }} · {{ p.reviewsCount }} Bewertungen · {{ p.jobsCount }} Aufträge ·
                                {{ (p.rating ?? 5).toFixed(1).replace('.', ',') }}
                            </p>
                            <p v-if="p.memberSince" class="text-xs text-ink-500">Dabei seit {{ p.memberSince }}</p>
                            <button type="button" class="mt-2 text-xs font-bold text-navy-700 underline" @click="startEdit(p)">
                                Bearbeiten
                            </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-pill p-1.5 text-ink-500 transition hover:bg-red-50 hover:text-red-600"
                        :aria-label="`${p.name} entfernen`"
                        @click="remove(p)"
                    >
                        <Trash2 :size="16" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>

        <p v-if="!partners.length" class="py-10 text-center text-ink-500">Noch keine Partner angelegt.</p>
        </div>
    </PageCard>
</template>
