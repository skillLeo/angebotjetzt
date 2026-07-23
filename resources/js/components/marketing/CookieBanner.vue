<script setup lang="ts">
import { onMounted, ref } from 'vue';


// GDPR consent banner with granular category toggles.
const visible = ref(false);
const showDetails = ref(false);
const consent = ref({ necessary: true, statistics: false, marketing: false });

const STORAGE_KEY = 'aj_cookie_consent';

onMounted(() => {
    if (!localStorage.getItem(STORAGE_KEY)) visible.value = true;
});

function save(all: boolean) {
    if (all) {
        consent.value = { necessary: true, statistics: true, marketing: true };
    }
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ ...consent.value, date: new Date().toISOString() }));
    visible.value = false;
}
</script>

<template>
    <Transition
        enter-active-class="transition duration-500 ease-out"
        enter-from-class="translate-y-8 opacity-0"
        leave-active-class="transition duration-300 ease-in"
        leave-to-class="translate-y-8 opacity-0"
    >
        <div
            v-if="visible"
            class="fixed inset-x-4 bottom-4 z-[60] mx-auto max-w-xl rounded-panel bg-white p-6 shadow-lift sm:inset-x-6"
            role="dialog"
            :aria-label="'Ihre Privatsphäre'"
        >
            <h2 class="font-display text-lg font-bold text-navy-700">{{ 'Ihre Privatsphäre' }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-ink-500">
                {{ 'Wir verwenden Cookies, um unsere Website zuverlässig zu betreiben und – mit Ihrer Zustimmung – die Nutzung zu analysieren. Details finden Sie in unserer' }}
                <a href="/cookie-policy" class="font-medium text-navy-700 underline">{{ 'Cookie-Richtlinie' }}</a>.
            </p>

            <div v-if="showDetails" class="mt-4 space-y-3">
                <label class="flex items-center justify-between gap-4 rounded-card bg-sand-50 px-4 py-3">
                    <span class="text-sm font-medium text-ink-700">{{ 'Notwendig' }}</span>
                    <input type="checkbox" checked disabled class="h-5 w-5 accent-green-500" />
                </label>
                <label class="flex items-center justify-between gap-4 rounded-card bg-sand-50 px-4 py-3">
                    <span class="text-sm font-medium text-ink-700">{{ 'Statistik' }}</span>
                    <input v-model="consent.statistics" type="checkbox" class="h-5 w-5 accent-green-500" />
                </label>
                <label class="flex items-center justify-between gap-4 rounded-card bg-sand-50 px-4 py-3">
                    <span class="text-sm font-medium text-ink-700">{{ 'Marketing' }}</span>
                    <input v-model="consent.marketing" type="checkbox" class="h-5 w-5 accent-green-500" />
                </label>
            </div>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                <button
                    type="button"
                    class="flex-1 rounded-pill bg-green-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-green-600"
                    @click="save(true)"
                >
                    {{ 'Alle akzeptieren' }}
                </button>
                <button
                    v-if="showDetails"
                    type="button"
                    class="flex-1 rounded-pill border border-ink-300 px-5 py-3 text-sm font-bold text-ink-700 transition hover:border-navy-700"
                    @click="save(false)"
                >
                    {{ 'Auswahl speichern' }}
                </button>
                <button
                    v-else
                    type="button"
                    class="flex-1 rounded-pill border border-ink-300 px-5 py-3 text-sm font-bold text-ink-700 transition hover:border-navy-700"
                    @click="showDetails = true"
                >
                    {{ 'Einstellungen' }}
                </button>
            </div>
        </div>
    </Transition>
</template>
