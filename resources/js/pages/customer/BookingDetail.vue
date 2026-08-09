<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Check, Mail, Phone } from 'lucide-vue-next';
import { ref } from 'vue';


const props = defineProps<{
    booking: {
        id: number; number: string; service: string; vehicle: string; inspector: string; inspectorCompany: string | null;
        city: string | null; price: number; status: string; date: string;
        inspectorPhone: string | null; inspectorEmail: string | null; message: string | null;
    };
}>();

const confirming = ref(false);

function confirmCompletion() {
    confirming.value = true;
    router.post(`/account/bookings/${props.booking.id}/confirm`, {}, {
        preserveScroll: true,
        onFinish: () => (confirming.value = false),
    });
}
</script>

<template>
    <Head><title>{{ 'Auftrag' }} {{ booking.number }}</title></Head>

    <Link href="/account/bookings" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> {{ 'Zurück zu Aufträgen' }}
    </Link>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <PageCard :title="`${booking.vehicle} · ${booking.service}`" :subtitle="booking.number">
                <template #actions><StatusBadge :status="booking.status" /></template>
                <div class="p-6 sm:p-8">
                    <p v-if="booking.status === 'accepted'" class="mb-5 rounded-card bg-green-50 p-3 text-sm font-semibold text-green-700">
                        {{ 'Der Dienstleister wird sich in Kürze bei Ihnen melden.' }}
                    </p>
                    <p class="text-sm text-ink-500">{{ 'Ihr Dienstleister' }}</p>
                    <p class="font-display text-lg font-bold text-navy-700">{{ booking.inspector }}</p>
                    <p v-if="booking.inspectorCompany" class="text-sm text-ink-500">{{ booking.inspectorCompany }}, {{ booking.city }}</p>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <a v-if="booking.inspectorPhone" :href="`tel:${booking.inspectorPhone}`" class="inline-flex items-center gap-2 rounded-pill border border-ink-300 px-5 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700">
                            <Phone :size="16" aria-hidden="true" /> {{ booking.inspectorPhone }}
                        </a>
                        <a v-if="booking.inspectorEmail" :href="`mailto:${booking.inspectorEmail}`" class="inline-flex items-center gap-2 rounded-pill border border-ink-300 px-5 py-2.5 text-sm font-bold text-navy-700 transition hover:border-navy-700">
                            <Mail :size="16" aria-hidden="true" /> {{ 'E-Mail' }}
                        </a>
                    </div>

                    <div v-if="booking.message" class="mt-5 rounded-card bg-sand-50 p-4 text-sm leading-relaxed text-ink-700">
                        „{{ booking.message }}"
                    </div>
                </div>
            </PageCard>

            <PageCard v-if="booking.status === 'completed_by_inspector'" :title="'Auftrag abschließen'">
                <div class="p-6 sm:p-8">
                    <p class="mb-4 text-sm text-ink-500">
                        {{ 'Der Dienstleister hat diesen Auftrag als abgeschlossen markiert. Bitte bestätigen Sie den Abschluss — im Anschluss erhalten Sie eine E-Mail, um Ihre Erfahrung zu bewerten.' }}
                    </p>
                    <button
                        type="button"
                        :disabled="confirming"
                        class="inline-flex items-center justify-center gap-2 rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-green-600 disabled:opacity-60"
                        @click="confirmCompletion"
                    >
                        <Check :size="16" aria-hidden="true" /> {{ 'Auftrag als abgeschlossen bestätigen' }}
                    </button>
                </div>
            </PageCard>
        </div>

        <div>
            <PageCard :title="'Auftragsübersicht'">
                <div class="space-y-3 p-5 text-sm sm:p-6">
                    <div class="flex justify-between"><span class="text-ink-500">{{ 'Auftragswert' }}</span><span class="font-bold text-navy-700">{{ formatEuro(booking.price) }}</span></div>
                    <p class="border-t border-ink-100 pt-3 text-xs leading-relaxed text-ink-500">
                        {{ 'Die Zahlung für diesen Auftrag erfolgt direkt zwischen Ihnen und dem Dienstleister, außerhalb der Plattform.' }}
                    </p>
                </div>
            </PageCard>
        </div>
    </div>
</template>
