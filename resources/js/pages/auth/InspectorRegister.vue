<script setup lang="ts">
import SplitAuthShell from '@/components/auth/SplitAuthShell.vue';
import FormField from '@/components/forms/FormField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const form = useForm({
    company_name: '', name: '', email: '', phone: '', city: '',
    password: '', password_confirmation: '', plz_from: '', plz_to: '', agb: false,
});

function submit() {
    form.post('/registrieren/gutachter', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <Head><title>{{ t('auth.inspectorRegister.headTitle') }}</title></Head>

    <SplitAuthShell
        :quote="t('auth.inspectorRegister.quote')"
        quote-author="Sabine Krüger, Kfz-Sachverständige"
    >
        <h1 class="font-display text-3xl font-extrabold text-navy-700">{{ t('auth.inspectorRegister.heading') }}</h1>
        <p class="mt-2 text-ink-500">{{ t('auth.inspectorRegister.subtitle') }}</p>

        <form class="mt-8 space-y-4" @submit.prevent="submit">
            <FormField v-model="form.company_name" :label="t('auth.inspectorRegister.company')" :error="form.errors.company_name" />
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.name" :label="t('auth.inspectorRegister.name')" required :error="form.errors.name" />
                <FormField v-model="form.phone" :label="t('auth.inspectorRegister.phone')" required :error="form.errors.phone" />
            </div>
            <FormField v-model="form.email" :label="t('auth.inspectorRegister.email')" type="email" required :error="form.errors.email" />
            <FormField v-model="form.city" :label="t('auth.inspectorRegister.city')" required :error="form.errors.city" />
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.plz_from" :label="t('auth.inspectorRegister.plzFrom')" inputmode="numeric" maxlength="5" :error="form.errors.plz_from" />
                <FormField v-model="form.plz_to" :label="t('auth.inspectorRegister.plzTo')" inputmode="numeric" maxlength="5" :error="form.errors.plz_to" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField v-model="form.password" :label="t('auth.inspectorRegister.password')" type="password" required :error="form.errors.password" />
                <FormField v-model="form.password_confirmation" :label="t('auth.inspectorRegister.confirmPassword')" type="password" required />
            </div>
            <label class="flex items-start gap-3 text-sm text-ink-700">
                <input v-model="form.agb" type="checkbox" class="mt-0.5 h-5 w-5 accent-green-500" />
                <span>{{ t('auth.inspectorRegister.acceptPrefix') }} <Link href="/agb" class="font-semibold text-green-600 underline" target="_blank">{{ t('auth.inspectorRegister.terms') }}</Link> {{ t('auth.inspectorRegister.and') }} <Link href="/datenschutz" class="font-semibold text-green-600 underline" target="_blank">{{ t('auth.inspectorRegister.privacy') }}</Link>.</span>
            </label>
            <p v-if="form.errors.agb" class="text-sm text-red-600">{{ form.errors.agb }}</p>

            <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                {{ t('auth.inspectorRegister.submit') }}
            </button>
            <p class="text-center text-sm text-ink-500">
                {{ t('auth.inspectorRegister.alreadyRegistered') }} <Link href="/gutachter/login" class="font-semibold text-green-600">{{ t('auth.inspectorRegister.toLogin') }}</Link>
            </p>
        </form>
    </SplitAuthShell>
</template>
