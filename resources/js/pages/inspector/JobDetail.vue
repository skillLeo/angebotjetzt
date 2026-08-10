<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Check, Mail, MapPin, Phone } from 'lucide-vue-next';
import { ref } from 'vue';


const props = defineProps<{
    job: {
        id: number; number: string; service: string; vehicle: string; ort: string; price: number; net: number; status: string;
        customer: { name: string; email: string; phone: string; strasse: string | null; plz: string; ort: string };
        vehicle: { make: string; model: string; firstRegistration: string | null; mileage: number | null; vin: string | null };
        notes: string | null; photos: string[];
    };
}>();

const completing = ref(false);

function complete() {
    completing.value = true;
    router.post(`/inspector/jobs/${props.job.id}/complete`, {}, { onFinish: () => (completing.value = false) });
}
</script>

<template>
    <Head><title>{{ 'Auftrag' }} {{ job.number }}</title></Head>

    <Link href="/inspector/jobs" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> {{ 'Zurück zu Aufträgen' }}
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <PageCard :title="`${job.vehicle.make} ${job.vehicle.model}`" :subtitle="`${job.service} · ${job.number}`">
                <template #actions><StatusBadge :status="job.status" /></template>
                <p v-if="job.status === 'accepted'" class="mx-5 mt-5 rounded-card bg-green-50 p-3 text-sm font-semibold text-green-700 sm:mx-6 sm:mt-6">
                    {{ 'Bitte kontaktieren Sie den Kunden, um den Termin zu vereinbaren.' }}
                </p>
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div><p class="text-sm text-ink-500">{{ 'Erstzulassung' }}</p><p class="font-semibold text-navy-700">{{ job.vehicle.firstRegistration ?? '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ 'Kilometerstand' }}</p><p class="font-semibold text-navy-700">{{ job.vehicle.mileage ? job.vehicle.mileage.toLocaleString('de-DE') + ' km' : '–' }}</p></div>
                    <div><p class="text-sm text-ink-500">{{ 'FIN' }}</p><p class="font-semibold text-navy-700">{{ job.vehicle.vin ?? '–' }}</p></div>
                    <div v-if="job.notes" class="sm:col-span-2"><p class="text-sm text-ink-500">{{ 'Anmerkungen' }}</p><p class="text-navy-700">{{ job.notes }}</p></div>
                </div>
            </PageCard>

            <PageCard :title="'Kundendaten'">
                <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div><p class="text-sm text-ink-500">{{ 'Name' }}</p><p class="font-semibold text-navy-700">{{ job.customer.name }}</p></div>
                    <div>
                        <p class="text-sm text-ink-500">{{ 'Adresse' }}</p>
                        <p class="flex items-center gap-1.5 font-semibold text-navy-700">
                            <MapPin :size="15" class="text-ink-300" aria-hidden="true" />
                            {{ job.customer.strasse ? job.customer.strasse + ', ' : '' }}{{ job.customer.plz }} {{ job.customer.ort }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">{{ 'Telefon' }}</p>
                        <a :href="`tel:${job.customer.phone}`" class="flex items-center gap-1.5 font-semibold text-green-600"><Phone :size="15" aria-hidden="true" /> {{ job.customer.phone }}</a>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">{{ 'E-Mail' }}</p>
                        <a :href="`mailto:${job.customer.email}`" class="flex items-center gap-1.5 font-semibold text-green-600"><Mail :size="15" aria-hidden="true" /> {{ job.customer.email }}</a>
                    </div>
                </div>
            </PageCard>
        </div>

        <div>
            <PageCard :title="'Vergütung'">
                <div class="space-y-3 p-5 text-sm sm:p-6">
                    <div class="flex justify-between"><span class="text-ink-500">{{ 'Auftragswert' }}</span><span class="font-bold text-navy-700">{{ formatEuro(job.price) }}</span></div>
                    <div class="flex justify-between"><span class="text-ink-500">{{ 'Provision (Rechnung folgt separat)' }}</span><span class="font-bold text-navy-700">{{ formatEuro(job.price - job.net) }}</span></div>
                    <div class="flex justify-between border-t border-ink-100 pt-3"><span class="text-ink-500">{{ 'Ihr Anteil' }}</span><span class="font-display text-lg font-extrabold text-green-600">{{ formatEuro(job.net) }}</span></div>
                    <p class="border-t border-ink-100 pt-3 text-xs leading-relaxed text-ink-500">
                        {{ `Der Auftragswert von ${formatEuro(job.price)} ist direkt zwischen Ihnen und dem Kunden zu begleichen, außerhalb der Plattform.` }}
                    </p>
                    <button
                        v-if="['accepted', 'paid', 'in_progress'].includes(job.status)"
                        type="button"
                        :disabled="completing"
                        class="mt-3 inline-flex w-full items-start justify-center gap-2 rounded-pill bg-green-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                        @click="complete"
                    >
                        <Check :size="18" class="mt-0.5 shrink-0" aria-hidden="true" />
                        <span class="text-left">{{ 'Auftrag als abgeschlossen markieren' }}</span>
                    </button>
                    <p v-else-if="job.status === 'completed_by_inspector'" class="mt-3 rounded-card bg-green-50 p-3 text-center text-xs text-green-700">
                        {{ 'Als abgeschlossen markiert. Warte auf Bestätigung durch den Kunden bzw. die Plattform.' }}
                    </p>
                    <p v-else-if="job.status === 'confirmed'" class="mt-3 rounded-card bg-green-50 p-3 text-center text-xs text-green-700">
                        {{ 'Abgeschlossen und bestätigt.' }}
                    </p>
                </div>
            </PageCard>
        </div>
    </div>
</template>
