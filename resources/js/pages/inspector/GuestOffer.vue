<script setup lang="ts">
import CenteredAuthShell from '@/components/auth/CenteredAuthShell.vue';
import { formatEuro } from '@/lib/format';
import { Head, useForm } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    email: string;
    commissionPercent: number;
    request: { id: number; number: string; service: string; vehicle: string; ort: string };
}>();

const form = useForm({
    name: '',
    company_name: '',
    price: '',
    estimated_date: '',
    message: '',
    agb: false,
});

const today = new Date();
const minDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

const priceCents = computed(() => {
    const val = parseFloat(String(form.price).replace(',', '.'));
    return isNaN(val) ? 0 : Math.round(val * 100);
});
const commissionCents = computed(() => Math.round((priceCents.value * props.commissionPercent) / 100));

function submit() {
    form.transform((d) => ({ ...d, price: String(d.price).replace(',', '.') }))
        .post(`/inspector/invite/${props.request.id}/offer`);
}
</script>

<template>
    <Head><title>{{ 'Angebot abgeben' }}</title></Head>

    <CenteredAuthShell
        :title="'Angebot abgeben'"
        :description="`${request.vehicle} · ${request.service} · ${request.ort} · ${request.number}`"
        :icon="FileText"
    >
        <form class="space-y-5" @submit.prevent="submit">
            <p class="rounded-card bg-sand-50 p-3 text-sm text-ink-500">
                {{ `Sie geben dieses Angebot als ${email} ab. Ein Konto legen wir für Sie an, sobald Sie das Angebot absenden.` }}
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Ihr Name *' }}</label>
                    <input id="name" v-model="form.name" type="text" required class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="company" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Firma (optional)' }}</label>
                    <input id="company" v-model="form.company_name" type="text" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                </div>
            </div>

            <div>
                <label for="price" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Ihr Preis (EUR, brutto) *' }}</label>
                <div class="relative">
                    <input id="price" v-model="form.price" type="text" inputmode="decimal" required :placeholder="'z. B. 299,00'"
                        class="w-full rounded-card border border-ink-300 py-3 pr-10 pl-4 text-[15px] focus:border-green-500 focus:outline-none" />
                    <span class="absolute top-1/2 right-4 -translate-y-1/2 text-ink-500">€</span>
                </div>
                <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                <p v-if="priceCents > 0" class="mt-1.5 text-xs text-ink-500">
                    {{ `Plattform-Provision (${commissionPercent} %): ${formatEuro(commissionCents)}. Der Kunde zahlt den Angebotspreis direkt an Sie.` }}
                </p>
            </div>

            <div>
                <label for="est" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Voraussichtlich fertig bis' }}</label>
                <input id="est" v-model="form.estimated_date" type="date" :min="minDate" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                <p v-if="form.errors.estimated_date" class="mt-1 text-sm text-red-600">{{ form.errors.estimated_date }}</p>
            </div>

            <div>
                <label for="msg" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Nachricht an den Kunden (optional)' }}</label>
                <textarea id="msg" v-model="form.message" rows="3" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
            </div>

            <label class="flex items-start gap-3 text-sm text-ink-700">
                <input v-model="form.agb" type="checkbox" class="mt-0.5 h-5 w-5 accent-green-500" />
                <span>{{ 'Ich akzeptiere die' }} <a href="/terms" target="_blank" class="font-semibold text-green-600 underline">{{ 'AGB' }}</a> {{ 'für Dienstleister.' }}</span>
            </label>
            <p v-if="form.errors.agb" class="text-sm text-red-600">{{ form.errors.agb }}</p>

            <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                {{ 'Angebot verbindlich abgeben' }}
            </button>
        </form>
    </CenteredAuthShell>
</template>
