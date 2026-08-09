<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';


const props = defineProps<{
    request: { id: number; number: string; service: string; vehicle: string; ort: string };
    commissionPercent: number;
    competingOffers: number[];
}>();

const form = useForm({ price: '', estimated_date: '', message: '' });

const today = new Date();
const minDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

const priceCents = computed(() => {
    const val = parseFloat(String(form.price).replace(',', '.'));
    return isNaN(val) ? 0 : Math.round(val * 100);
});
const commissionCents = computed(() => Math.round((priceCents.value * props.commissionPercent) / 100));
const netCents = computed(() => priceCents.value - commissionCents.value);

function submit() {
    form.transform((d) => ({ ...d, price: String(d.price).replace(',', '.') }))
        .post(`/inspector/requests/${props.request.id}/offer`);
}
</script>

<template>
    <Head><title>{{ 'Angebot abgeben' }}</title></Head>

    <Link :href="`/inspector/requests/${request.id}`" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> {{ 'Zurück zur Anfrage' }}
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <PageCard :title="`Angebot für ${request.vehicle}`" :subtitle="`${request.service} · ${request.ort} · ${request.number}`">
                <form class="space-y-5 p-6 sm:p-8" @submit.prevent="submit">
                    <div>
                        <label for="price" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Ihr Preis (EUR, brutto) *' }}</label>
                        <div class="relative">
                            <input id="price" v-model="form.price" type="text" inputmode="decimal" required :placeholder="'z. B. 299,00'"
                                class="w-full rounded-card border border-ink-300 py-3 pr-10 pl-4 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500" />
                            <span class="absolute top-1/2 right-4 -translate-y-1/2 text-ink-500">€</span>
                        </div>
                        <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                    </div>

                    <div>
                        <label for="est" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Voraussichtlich fertig bis' }}</label>
                        <input id="est" v-model="form.estimated_date" type="date" :min="minDate" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                        <p v-if="form.errors.estimated_date" class="mt-1 text-sm text-red-600">{{ form.errors.estimated_date }}</p>
                    </div>

                    <div>
                        <label for="msg" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Nachricht an den Kunden (optional)' }}</label>
                        <textarea id="msg" v-model="form.message" rows="4" :placeholder="'z. B. Verfügbarkeit, Ablauf, Rückfragen …'"
                            class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500" />
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                        {{ 'Angebot verbindlich abgeben' }}
                    </button>
                </form>
            </PageCard>
        </div>

        <div class="space-y-6">
            <PageCard v-if="competingOffers.length" :title="'Andere Angebote für diese Anfrage'">
                <div class="space-y-2 p-5 text-sm sm:p-6">
                    <p class="text-ink-500">{{ `${competingOffers.length} weitere${competingOffers.length === 1 ? 's' : ''} Angebot${competingOffers.length === 1 ? '' : 'e'} bereits eingegangen:` }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(price, i) in competingOffers" :key="i" class="rounded-pill bg-sand-100 px-3 py-1 font-semibold text-navy-700">
                            {{ formatEuro(price) }}
                        </span>
                    </div>
                </div>
            </PageCard>

            <PageCard :title="'Verdienst-Vorschau'">
                <div class="space-y-3 p-5 text-sm sm:p-6">
                    <div class="flex justify-between">
                        <span class="text-ink-500">{{ 'Ihr Angebotspreis' }}</span>
                        <span class="font-bold text-navy-700">{{ formatEuro(priceCents) }}</span>
                    </div>
                    <div class="flex justify-between text-ink-500">
                        <span>{{ `Plattform-Provision (${commissionPercent} %)` }}</span>
                        <span>− {{ formatEuro(commissionCents) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-ink-100 pt-3">
                        <span class="font-semibold text-navy-700">{{ 'Ihre Auszahlung' }}</span>
                        <span class="font-display text-lg font-extrabold text-green-600">{{ formatEuro(netCents) }}</span>
                    </div>
                    <p class="pt-2 text-xs text-ink-500">
                        {{ 'Der Kunde zahlt den Angebotspreis direkt an Sie. Die Plattform-Provision stellen wir Ihnen nach Abschluss des Auftrags separat in Rechnung.' }}
                    </p>
                </div>
            </PageCard>
        </div>
    </div>
</template>
