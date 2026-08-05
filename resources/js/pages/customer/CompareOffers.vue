<script setup lang="ts">
import StarRating from '@/components/marketing/StarRating.vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, BadgeCheck, CalendarClock, Check } from 'lucide-vue-next';
import { ref } from 'vue';


const props = defineProps<{
    request: { id: number; number: string; service: string; vehicle: string; ort: string };
    offers: Array<{
        id: number; price: number; message: string | null; estimatedDate: string | null; status: string; editedAt: string | null;
        inspector: { name: string; company: string | null; city: string | null; verified: boolean; experience: number | null; rating: number | null; reviews: number | null };
    }>;
}>();

const accepting = ref<number | null>(null);
const lowest = props.offers.length ? Math.min(...props.offers.map((o) => o.price)) : 0;

const confirmDialogOpen = ref(false);
const pendingOffer = ref<(typeof props.offers)[number] | null>(null);

function openConfirm(offer: (typeof props.offers)[number]) {
    pendingOffer.value = offer;
    confirmDialogOpen.value = true;
}

function confirmAccept() {
    if (!pendingOffer.value) return;
    const offerId = pendingOffer.value.id;
    confirmDialogOpen.value = false;
    accepting.value = offerId;
    router.post(`/account/offers/${offerId}/accept`, {}, {
        onFinish: () => (accepting.value = null),
    });
}
</script>

<template>
    <Head><title>{{ 'Angebote vergleichen' }}</title></Head>

    <Link :href="`/account/requests/${request.id}`" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> {{ 'Zurück' }}
    </Link>

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-navy-700">{{ `${offers.length} Angebote für Ihre Anfrage` }}</h1>
        <p class="text-ink-500">{{ request.vehicle }} · {{ request.service }} · {{ request.ort }}</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <article
            v-for="o in offers"
            :key="o.id"
            class="flex flex-col rounded-card border bg-white p-6 shadow-card"
            :class="o.price === lowest ? 'border-green-500 ring-1 ring-green-500' : 'border-ink-100'"
        >
            <div v-if="o.price === lowest" class="mb-3 w-fit rounded-pill bg-green-500 px-3 py-1 text-xs font-bold text-white">
                {{ 'Günstigstes Angebot' }}
            </div>
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-pill bg-navy-700 font-display text-lg font-bold text-white">
                    {{ o.inspector.name.charAt(0) }}
                </span>
                <div>
                    <p class="font-display font-bold text-navy-700">{{ o.inspector.name }}</p>
                    <p class="text-sm text-ink-500">{{ o.inspector.city }}</p>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span v-if="o.inspector.verified" class="inline-flex items-center gap-1 rounded-pill bg-green-50 px-2.5 py-0.5 text-xs font-bold text-green-700">
                    <BadgeCheck :size="13" aria-hidden="true" /> {{ 'Geprüft' }}
                </span>
                <span v-if="o.inspector.rating" class="inline-flex items-center gap-1 text-xs text-ink-500">
                    <StarRating :rating="o.inspector.rating" :size="13" /> {{ o.inspector.rating.toFixed(1).replace('.', ',') }} ({{ o.inspector.reviews }})
                </span>
            </div>

            <p class="mt-5 font-display text-3xl font-extrabold text-navy-700">{{ formatEuro(o.price) }}</p>

            <p v-if="o.estimatedDate" class="mt-2 flex items-center gap-1.5 text-sm text-ink-500">
                <CalendarClock :size="15" aria-hidden="true" /> {{ `Fertig bis ${o.estimatedDate}` }}
            </p>
            <p v-if="o.message" class="mt-3 flex-1 rounded-card bg-sand-50 p-3 text-sm leading-relaxed text-ink-700">
                „{{ o.message }}"
            </p>
            <div v-else class="flex-1" />
            <p v-if="o.editedAt" class="mt-2 text-xs text-ink-500">{{ 'Bearbeitet am' }} {{ o.editedAt }}</p>

            <button
                type="button"
                :disabled="accepting !== null || o.status !== 'open'"
                class="mt-5 inline-flex items-center justify-center gap-2 rounded-pill bg-green-500 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                @click="openConfirm(o)"
            >
                <Check :size="18" aria-hidden="true" />
                {{ accepting === o.id ? 'Wird bestätigt …' : 'Angebot annehmen' }}
            </button>
        </article>
    </div>

    <p v-if="!offers.length" class="rounded-card border border-ink-100 bg-white py-12 text-center text-ink-500">
        {{ 'Für diese Anfrage liegen noch keine Angebote vor.' }}
    </p>

    <Dialog v-model:open="confirmDialogOpen">
        <DialogContent v-if="pendingOffer">
            <DialogHeader>
                <DialogTitle>{{ 'Angebot verbindlich annehmen?' }}</DialogTitle>
                <DialogDescription class="space-y-3 pt-2 text-left">
                    <span class="block">
                        {{ `Mit der Annahme des Angebots von ${pendingOffer.inspector.name} über ${formatEuro(pendingOffer.price)} schließen Sie eine verbindliche Vereinbarung direkt mit diesem Gutachter ab.` }}
                    </span>
                    <span class="block">
                        {{ 'Die Zahlung für den Auftrag erfolgt direkt zwischen Ihnen und dem Gutachter, außerhalb der Plattform. AngebotJetzt ist an dieser Zahlung nicht beteiligt.' }}
                    </span>
                    <span class="block">
                        {{ 'Nach der Bestätigung werden die Kontaktdaten beider Seiten freigeschaltet und alle anderen Angebote für diese Anfrage automatisch abgelehnt.' }}
                    </span>
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <button
                    type="button"
                    class="rounded-pill border border-ink-300 px-6 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700"
                    @click="confirmDialogOpen = false"
                >
                    {{ 'Abbrechen' }}
                </button>
                <button
                    type="button"
                    class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-green-600"
                    @click="confirmAccept"
                >
                    {{ 'Verbindlich annehmen' }}
                </button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
