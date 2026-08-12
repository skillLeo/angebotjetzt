<script setup lang="ts">
import { ExternalLink } from 'lucide-vue-next';

/**
 * Shown wherever a customer picks a service that a partner fulfils instead of
 * AngebotJetzt. Both the service page and the request wizard render this, so
 * the wording and the destination can never drift apart between entry points.
 */
withDefaults(
    defineProps<{
        url: string | null;
        /** Compact variant for use inside the wizard card. */
        inline?: boolean;
    }>(),
    { inline: false },
);
</script>

<template>
    <div :class="inline ? '' : 'rounded-panel border border-ink-100 bg-white p-8 shadow-card sm:p-10'">
        <p class="text-eyebrow text-green-600">{{ 'In Kooperation mit CarSpector' }}</p>
        <h2 :class="inline ? 'mt-2 font-display text-xl font-bold text-navy-700' : 'text-section mt-3 text-navy-700'">
            {{ 'Der Gebrauchtwagen-Check' }}
        </h2>

        <div class="mt-4 text-[15px] leading-relaxed text-ink-700">
            <p>
                Für professionelle Gebrauchtwagenchecks arbeiten wir mit Carspector zusammen. Dort können Sie Ihr
                Wunschfahrzeug unabhängig prüfen lassen und erhalten anschließend einen ausführlichen Zustandsbericht.
            </p>
        </div>

        <div class="mt-6">
            <a
                v-if="url"
                :href="url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 rounded-pill bg-green-500 px-7 py-3.5 text-[15px] font-bold text-white transition hover:bg-green-600"
            >
                {{ 'Jetzt bei CarSpector buchen' }}
                <ExternalLink :size="18" aria-hidden="true" />
            </a>
            <span
                v-else
                class="inline-flex cursor-not-allowed items-center gap-2 rounded-pill bg-ink-300 px-7 py-3.5 text-[15px] font-bold text-white"
                aria-disabled="true"
            >
                {{ 'Jetzt bei CarSpector buchen' }}
                <ExternalLink :size="18" aria-hidden="true" />
            </span>
            <p v-if="!url" class="mt-3 text-sm text-ink-500">
                {{ 'Der Link zum Partnerangebot wird in Kürze freigeschaltet.' }}
            </p>
        </div>
    </div>
</template>
