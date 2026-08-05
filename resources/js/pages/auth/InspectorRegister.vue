<script setup lang="ts">
import SplitAuthShell from '@/components/auth/SplitAuthShell.vue';
import FormField from '@/components/forms/FormField.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { UserPlus } from 'lucide-vue-next';

const props = defineProps<{
    categories: Array<{ id: number; name: string }>;
    prefill: { email: string | null; requestId: string | null; serviceCategoryId: number | null };
}>();

const form = useForm({
    service_category_id: props.prefill.serviceCategoryId ? String(props.prefill.serviceCategoryId) : '',
    company_name: '',
    name: '',
    email: props.prefill.email ?? '',
    password: '',
    password_confirmation: '',
    agb: false,
    request_id: props.prefill.requestId ?? '',
});

function submit() {
    form.post('/inspector/register', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <Head><title>{{ 'Als Anbieter registrieren' }}</title></Head>

    <SplitAuthShell
        :quote="'Endlich passende Aufträge ohne Kaltakquise – ich bestimme meine Preise selbst.'"
        quote-author="Sabine Krüger, Kfz-Sachverständige"
        :title="'Als Anbieter registrieren'"
        :description="'Erhalten Sie passende Anfragen aus Ihrer Region automatisch.'"
        :icon="UserPlus"
    >
        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Kategorie' }} <span class="text-green-600">*</span></label>
                <Select v-model="form.service_category_id">
                    <SelectTrigger class="h-12 w-full rounded-card border-ink-300 px-4 py-3 text-[15px] focus-visible:border-green-500">
                        <SelectValue :placeholder="'Welche Kategorie?'" />
                    </SelectTrigger>
                    <SelectContent class="max-w-[calc(100vw-2rem)]">
                        <SelectItem v-for="cat in categories" :key="cat.id" :value="String(cat.id)">{{ cat.name }}</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.service_category_id" class="mt-1 text-sm text-red-600">{{ form.errors.service_category_id }}</p>
            </div>
            <FormField v-model="form.company_name" :label="'Firma (optional)'" :error="form.errors.company_name" />
            <FormField v-model="form.name" :label="'Name'" required :error="form.errors.name" />
            <FormField v-model="form.email" :label="'E-Mail'" type="email" required :error="form.errors.email" />
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.password" :label="'Passwort'" type="password" required :error="form.errors.password" />
                <FormField v-model="form.password_confirmation" :label="'Passwort bestätigen'" type="password" required />
            </div>
            <label class="flex items-start gap-3 text-sm text-ink-700">
                <input v-model="form.agb" type="checkbox" class="mt-0.5 h-5 w-5 accent-green-500" />
                <span>{{ 'Ich akzeptiere die' }} <Link href="/terms" class="font-semibold text-green-600 underline" target="_blank">{{ 'AGB' }}</Link> {{ 'und' }} <Link href="/privacy" class="font-semibold text-green-600 underline" target="_blank">{{ 'Datenschutzerklärung' }}</Link>.</span>
            </label>
            <p v-if="form.errors.agb" class="text-sm text-red-600">{{ form.errors.agb }}</p>

            <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                {{ 'Konto erstellen' }}
            </button>
            <p class="text-center text-sm text-ink-500">
                {{ 'Bereits registriert?' }} <Link href="/login" class="font-semibold text-green-600">{{ 'Zum Login' }}</Link>
            </p>
        </form>
    </SplitAuthShell>
</template>
