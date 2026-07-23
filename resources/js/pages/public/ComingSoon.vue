<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Clock } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';

const { t } = useI18n();

const props = defineProps<{
    category: { name: string; slug: string; icon: string; description: string | null };
}>();

const form = useForm({ email: '' });

function submit() {
    form.post(`/demnaechst/${props.category.slug}/interesse`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toast.success(t('public.comingSoon.successToast'));
        },
    });
}
</script>

<template>
    <Head>
        <title>{{ category.name }} – {{ t('public.comingSoon.metaTitleSuffix') }}</title>
    </Head>

    <section class="flex min-h-[70vh] items-center bg-sand-100 px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <span class="inline-flex items-center gap-2 rounded-pill bg-navy-700 px-4 py-1.5 text-sm font-bold text-white">
                <Clock :size="15" aria-hidden="true" /> {{ t('public.comingSoon.badge') }}
            </span>
            <h1 class="text-hero mt-6 text-navy-700">{{ category.name }}</h1>
            <p class="text-lead mx-auto mt-5 max-w-lg text-ink-700">
                {{ t('public.comingSoon.description', { name: category.name }) }}
            </p>

            <form class="mx-auto mt-9 flex max-w-md flex-col gap-2 rounded-panel bg-white p-2 shadow-lift sm:flex-row" @submit.prevent="submit">
                <label :for="`notify-${category.slug}`" class="sr-only">{{ t('public.comingSoon.emailLabel') }}</label>
                <input
                    :id="`notify-${category.slug}`"
                    v-model="form.email"
                    type="email"
                    required
                    :placeholder="t('public.comingSoon.emailPlaceholder')"
                    class="h-12 flex-1 rounded-pill bg-transparent px-5 text-[15px] focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                />
                <button type="submit" :disabled="form.processing" class="h-12 rounded-pill bg-green-500 px-7 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                    {{ t('public.comingSoon.notifyMe') }}
                </button>
            </form>
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>

            <Link href="/" class="mt-10 inline-flex items-center gap-2 text-sm font-semibold text-navy-700 hover:text-green-600">
                <ArrowLeft :size="16" aria-hidden="true" /> {{ t('public.comingSoon.backHome') }}
            </Link>
        </div>
    </section>
</template>
