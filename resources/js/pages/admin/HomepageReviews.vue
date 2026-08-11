<script setup lang="ts">
import PhotoUpload from '@/components/admin/PhotoUpload.vue';
import PageCard from '@/components/dashboard/PageCard.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Star, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

type HomepageReview = {
    id: number;
    name: string;
    rating: number;
    comment: string;
    service: string | null;
    city: string | null;
    photo: string | null;
    sortOrder: number;
};

defineProps<{ reviews: HomepageReview[] }>();

const blank = {
    name: '',
    rating: 5,
    comment: '',
    service: '',
    city: '',
    sort_order: 0,
    photo: null as File | null,
};

const createForm = useForm({ ...blank });
const editingId = ref<number | null>(null);
const editForm = useForm({ ...blank });

function add() {
    createForm.post('/admin/homepage-reviews', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => createForm.reset(),
    });
}

function startEdit(rev: HomepageReview) {
    editingId.value = rev.id;
    editForm.clearErrors();
    editForm.name = rev.name;
    editForm.rating = rev.rating;
    editForm.comment = rev.comment;
    editForm.service = rev.service ?? '';
    editForm.city = rev.city ?? '';
    editForm.sort_order = rev.sortOrder;
    editForm.photo = null;
}

function saveEdit() {
    if (editingId.value === null) return;

    editForm.post(`/admin/homepage-reviews/${editingId.value}`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => (editingId.value = null),
    });
}

function remove(rev: HomepageReview) {
    if (!confirm(`Bewertung von „${rev.name}“ wirklich entfernen?`)) return;

    router.delete(`/admin/homepage-reviews/${rev.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head><title>Startseiten-Bewertungen</title></Head>

    <PageCard
        title="Startseiten-Bewertungen"
        subtitle="Diese Bewertungen erscheinen im Bewertungsbereich der Startseite, zusätzlich zu den Bewertungen aus abgeschlossenen Aufträgen."
    >
        <div class="px-5 py-6 sm:px-6">
        <form class="mb-8 rounded-card border border-ink-300 bg-sand-50 p-5" @submit.prevent="add">
            <p class="mb-4 font-display font-bold text-navy-700">Neue Bewertung</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Kundenname</span>
                    <input v-model="createForm.name" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                    <span v-if="createForm.errors.name" class="mt-1 block text-xs text-red-600">{{ createForm.errors.name }}</span>
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Bewertung</span>
                    <select v-model.number="createForm.rating" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm">
                        <option v-for="n in 5" :key="n" :value="n">{{ n }} von 5</option>
                    </select>
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Leistung (optional)</span>
                    <input v-model="createForm.service" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                </label>
                <label class="block">
                    <span class="mb-1 block text-xs font-bold text-ink-700">Stadt (optional)</span>
                    <input v-model="createForm.city" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                </label>
                <PhotoUpload
                    v-model="createForm.photo"
                    label="Foto (optional)"
                    shape="circle"
                    :error="createForm.errors.photo"
                    class="sm:col-span-2"
                />
            </div>
            <label class="mt-3 block">
                <span class="mb-1 block text-xs font-bold text-ink-700">Bewertungstext</span>
                <textarea v-model="createForm.comment" rows="3" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                <span v-if="createForm.errors.comment" class="mt-1 block text-xs text-red-600">{{ createForm.errors.comment }}</span>
            </label>
            <button
                type="submit"
                :disabled="createForm.processing"
                class="mt-4 rounded-pill bg-navy-700 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-navy-800 disabled:opacity-50"
            >
                Bewertung hinzufügen
            </button>
        </form>

        <div class="space-y-3">
            <div v-for="rev in reviews" :key="rev.id" class="rounded-card border border-ink-300 bg-white p-5">
                <form v-if="editingId === rev.id" @submit.prevent="saveEdit">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Kundenname</span>
                            <input v-model="editForm.name" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Bewertung</span>
                            <select v-model.number="editForm.rating" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm">
                                <option v-for="n in 5" :key="n" :value="n">{{ n }} von 5</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Leistung</span>
                            <input v-model="editForm.service" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-bold text-ink-700">Stadt</span>
                            <input v-model="editForm.city" type="text" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                        </label>
                        <PhotoUpload
                            v-model="editForm.photo"
                            label="Foto"
                            shape="circle"
                            :current="rev.photo"
                            :error="editForm.errors.photo"
                            class="sm:col-span-2"
                        />
                    </div>
                    <label class="mt-3 block">
                        <span class="mb-1 block text-xs font-bold text-ink-700">Bewertungstext</span>
                        <textarea v-model="editForm.comment" rows="3" class="w-full rounded-card border border-ink-300 px-3 py-2 text-sm" />
                    </label>
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
                    <div class="flex min-w-0 gap-4">
                        <img v-if="rev.photo" :src="rev.photo" :alt="rev.name" class="h-12 w-12 shrink-0 rounded-pill object-cover" />
                        <span v-else class="flex h-12 w-12 shrink-0 items-center justify-center rounded-pill bg-navy-700 font-display font-bold text-white">
                            {{ rev.name.charAt(0) }}
                        </span>
                        <div class="min-w-0">
                        <p class="flex items-center gap-2 font-display font-bold text-navy-700">
                            {{ rev.name }}
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-amber-500">
                                <Star :size="13" aria-hidden="true" />{{ rev.rating }}/5
                            </span>
                        </p>
                        <p class="mt-1 text-sm text-ink-700">{{ rev.comment }}</p>
                        <p v-if="rev.service || rev.city" class="mt-1 text-xs text-ink-500">
                            {{ rev.service }}<span v-if="rev.service && rev.city"> · </span>{{ rev.city }}
                        </p>
                        <button type="button" class="mt-2 text-xs font-bold text-navy-700 underline" @click="startEdit(rev)">
                            Bearbeiten
                        </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 rounded-pill p-1.5 text-ink-500 transition hover:bg-red-50 hover:text-red-600"
                        :aria-label="`Bewertung von ${rev.name} entfernen`"
                        @click="remove(rev)"
                    >
                        <Trash2 :size="16" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>

        <p v-if="!reviews.length" class="py-10 text-center text-ink-500">Noch keine Bewertungen angelegt.</p>
        </div>
    </PageCard>
</template>
