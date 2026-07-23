<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';
import { Motion } from 'motion-v';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{ sessionId: string | null }>();
</script>

<template>
    <Head><title>{{ t('dashboard.customerPages.paymentSuccessTitle') }}</title></Head>

    <div class="mx-auto max-w-xl py-10 text-center">
        <Motion :initial="{ scale: 0.6, opacity: 0 }" :animate="{ scale: 1, opacity: 1 }" :transition="{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }">
            <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-pill bg-green-500 text-white">
                <CheckCircle2 :size="42" aria-hidden="true" />
            </span>
        </Motion>
        <h1 class="font-display mt-8 text-3xl font-extrabold text-navy-700">{{ t('dashboard.customerPages.paymentSuccessHeading') }}</h1>
        <p class="mt-3 text-ink-500">
            {{ t('dashboard.customerPages.paymentSuccessText') }}
        </p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <Link href="/account/bookings" class="rounded-pill bg-green-500 px-7 py-3.5 font-bold text-white transition hover:bg-green-600">
                {{ t('dashboard.customerPages.toMyBookings') }}
            </Link>
            <Link href="/account" class="rounded-pill border border-ink-300 px-7 py-3.5 font-bold text-navy-700 transition hover:border-navy-700">
                {{ t('dashboard.customerPages.toOverview') }}
            </Link>
        </div>
    </div>
</template>
