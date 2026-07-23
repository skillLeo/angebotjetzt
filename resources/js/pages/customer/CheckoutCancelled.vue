<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { XCircle } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{ requestId?: number }>();
</script>

<template>
    <Head><title>{{ t('dashboard.customerPages.paymentCancelledTitle') }}</title></Head>

    <div class="mx-auto max-w-xl py-10 text-center">
        <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-pill bg-ink-100 text-ink-500">
            <XCircle :size="42" aria-hidden="true" />
        </span>
        <h1 class="font-display mt-8 text-3xl font-extrabold text-navy-700">{{ t('dashboard.customerPages.paymentCancelledTitle') }}</h1>
        <p class="mt-3 text-ink-500">
            {{ t('dashboard.customerPages.paymentCancelledText') }}
        </p>
        <div class="mt-8">
            <Link href="/account/requests" class="rounded-pill bg-green-500 px-7 py-3.5 font-bold text-white transition hover:bg-green-600">
                {{ t('dashboard.customerPages.backToMyRequests') }}
            </Link>
        </div>
    </div>
</template>
