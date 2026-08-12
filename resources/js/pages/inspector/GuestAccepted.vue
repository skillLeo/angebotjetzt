<script setup lang="ts">
import CenteredAuthShell from '@/components/auth/CenteredAuthShell.vue';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Mail, MapPin, Phone, UserPlus } from 'lucide-vue-next';

/**
 * Shown straight after an invited guest accepts a direct-accept request. The
 * contact details are unlocked here without an account, and the account claim
 * is offered afterwards — the same order the guest offer flow already uses.
 */
defineProps<{
    email: string;
    registerUrl: string;
    job: {
        number: string; service: string; vehicle: string;
        customer: { name: string; email: string; phone: string; strasse: string | null; plz: string; ort: string };
    };
}>();
</script>

<template>
    <Head><title>{{ 'Anfrage angenommen' }}</title></Head>

    <CenteredAuthShell
        :title="'Anfrage angenommen'"
        :description="`${job.vehicle} · ${job.service} · ${job.number}`"
        :icon="CheckCircle2"
    >
        <div class="space-y-5">
            <p class="rounded-card bg-green-50 p-3 text-sm font-semibold text-green-700">
                {{ 'Der Auftrag ist Ihnen verbindlich zugewiesen. Bitte kontaktieren Sie den Kunden, um den Termin abzustimmen.' }}
            </p>

            <div class="rounded-card border border-ink-300 p-4">
                <p class="font-display font-bold text-navy-700">{{ 'Kundendaten' }}</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-ink-500">{{ 'Name' }}</p>
                        <p class="font-semibold text-navy-700">{{ job.customer.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">{{ 'Adresse' }}</p>
                        <p class="flex items-center gap-1.5 font-semibold text-navy-700">
                            <MapPin :size="15" class="text-ink-300" aria-hidden="true" />
                            {{ job.customer.strasse ? job.customer.strasse + ', ' : '' }}{{ job.customer.plz }} {{ job.customer.ort }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">{{ 'Telefon' }}</p>
                        <a :href="`tel:${job.customer.phone}`" class="flex items-center gap-1.5 font-semibold text-green-600">
                            <Phone :size="15" aria-hidden="true" /> {{ job.customer.phone }}
                        </a>
                    </div>
                    <div>
                        <p class="text-sm text-ink-500">{{ 'E-Mail' }}</p>
                        <a :href="`mailto:${job.customer.email}`" class="flex items-center gap-1.5 font-semibold text-green-600">
                            <Mail :size="15" aria-hidden="true" /> {{ job.customer.email }}
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-sm leading-relaxed text-ink-500">
                {{ 'Das endgültige Honorar richtet sich nach der tatsächlich festgestellten Schadenhöhe. Nach Abschluss des Auftrags tragen Sie den berechneten Betrag in Ihrem Konto ein; die Provisionsrechnung wird daraus automatisch erstellt.' }}
            </p>

            <Link
                :href="registerUrl"
                class="flex w-full items-center justify-center gap-2 rounded-pill bg-navy-700 py-3.5 font-bold text-white transition hover:bg-navy-800"
            >
                <UserPlus :size="18" aria-hidden="true" />
                {{ 'Konto vervollständigen' }}
            </Link>
            <p class="text-center text-xs text-ink-500">
                {{ `Ihr Zugang wird für ${email} angelegt. Damit sehen Sie diesen Auftrag jederzeit wieder und erhalten weitere Anfragen.` }}
            </p>
        </div>
    </CenteredAuthShell>
</template>
