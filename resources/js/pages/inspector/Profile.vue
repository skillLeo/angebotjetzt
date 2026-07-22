<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import FormField from '@/components/forms/FormField.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

const props = defineProps<{
    profile: { name: string; company_name: string | null; email: string; phone: string | null; city: string | null; bio: string | null; qualifications: string | null; years_experience: number | null };
}>();

const form = useForm({
    name: props.profile.name,
    company_name: props.profile.company_name ?? '',
    phone: props.profile.phone ?? '',
    city: props.profile.city ?? '',
    bio: props.profile.bio ?? '',
    qualifications: props.profile.qualifications ?? '',
    years_experience: props.profile.years_experience ?? '',
});

function submit() {
    form.post('/gutachter/profil', {
        preserveScroll: true,
        onSuccess: () => toast.success('Profil aktualisiert.'),
    });
}
</script>

<template>
    <Head><title>Profil</title></Head>

    <PageCard title="Öffentliches Profil" subtitle="Diese Angaben sehen Kunden bei Ihren Angeboten.">
        <form class="space-y-5 p-5 sm:p-6" @submit.prevent="submit">
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField v-model="form.name" label="Name" required :error="form.errors.name" />
                <FormField v-model="form.company_name" label="Firma" :error="form.errors.company_name" />
                <FormField v-model="form.phone" label="Telefon" :error="form.errors.phone" />
                <FormField v-model="form.city" label="Stadt" :error="form.errors.city" />
                <FormField v-model="form.years_experience" label="Jahre Erfahrung" type="number" :error="form.errors.years_experience" />
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-navy-700">Über mich</label>
                <textarea v-model="form.bio" rows="4" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-navy-700">Qualifikationen & Zertifikate</label>
                <textarea v-model="form.qualifications" rows="3" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
            </div>
            <div class="rounded-card bg-sand-50 px-4 py-3 text-sm text-ink-500">E-Mail: {{ profile.email }} (Änderung über den Support)</div>
            <button type="submit" :disabled="form.processing" class="rounded-pill bg-green-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                Speichern
            </button>
        </form>
    </PageCard>
</template>
