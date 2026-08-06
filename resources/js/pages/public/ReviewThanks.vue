<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';
</script>

<template>
    <Head><title>{{ 'Vielen Dank' }}</title></Head>

    <section class="flex min-h-[70vh] items-center bg-sand-50 px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-md text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-pill bg-green-500 text-white">
                <CheckCircle2 :size="32" aria-hidden="true" />
            </span>
            <h1 class="text-section mt-6 text-navy-700">{{ 'Vielen Dank für Ihr Feedback!' }}</h1>
            <p class="mt-3 text-ink-500">
                {{ 'Wir nehmen Ihre Rückmeldung ernst und schauen uns das genau an, damit wir uns verbessern können.' }}
            </p>
            <Link href="/" class="mt-8 inline-block rounded-pill bg-green-500 px-7 py-3.5 font-bold text-white transition hover:bg-green-600">
                {{ 'Zur Startseite' }}
            </Link>
        </div>
    </section>
</template>
