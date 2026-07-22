<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { XCircle } from 'lucide-vue-next';

defineProps<{ requestId?: number }>();
</script>

<template>
    <Head><title>Zahlung abgebrochen</title></Head>

    <div class="mx-auto max-w-xl py-10 text-center">
        <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-pill bg-ink-100 text-ink-500">
            <XCircle :size="42" aria-hidden="true" />
        </span>
        <h1 class="font-display mt-8 text-3xl font-extrabold text-navy-700">Zahlung abgebrochen</h1>
        <p class="mt-3 text-ink-500">
            Die Zahlung wurde nicht abgeschlossen. Ihr Angebot ist weiterhin verfügbar – Sie können den
            Vorgang jederzeit wiederholen.
        </p>
        <div class="mt-8">
            <Link href="/konto/anfragen" class="rounded-pill bg-green-500 px-7 py-3.5 font-bold text-white transition hover:bg-green-600">
                Zurück zu meinen Anfragen
            </Link>
        </div>
    </div>
</template>
