<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatusBadge from '@/components/dashboard/StatusBadge.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();

const props = defineProps<{
    available: number;
    pendingPayout: number;
    history: Array<{ id: number; amount: number; status: string; requested: string; paid: string | null }>;
}>();

const maxPayout = computed(() => (props.available - props.pendingPayout) / 100);
const form = useForm({ amount: '', iban: '', bic: '', account_holder: '' });

function submit() {
    form.transform((d) => ({ ...d, amount: String(d.amount).replace(',', '.') })).post('/gutachter/wallet/auszahlung');
}
</script>

<template>
    <Head><title>{{ t('dashboard.inspectorPages.requestPayoutTitle') }}</title></Head>

    <Link href="/gutachter/wallet" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> {{ t('dashboard.inspectorPages.backToWallet') }}
    </Link>

    <div class="grid gap-6 lg:grid-cols-2">
        <PageCard :title="t('dashboard.inspectorPages.requestPayoutTitle')" :subtitle="t('dashboard.inspectorPages.availableAmount', { amount: formatEuro(available - pendingPayout) })">
            <form class="space-y-5 p-5 sm:p-6" @submit.prevent="submit">
                <div>
                    <label for="amount" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ t('dashboard.inspectorPages.amountLabel') }}</label>
                    <input id="amount" v-model="form.amount" type="text" inputmode="decimal" required :placeholder="t('dashboard.inspectorPages.amountPlaceholder', { amount: maxPayout.toFixed(2).replace('.', locale === 'de' ? ',' : '.') })" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                </div>
                <div>
                    <label for="holder" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ t('dashboard.inspectorPages.accountHolder') }}</label>
                    <input id="holder" v-model="form.account_holder" type="text" required class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.account_holder" class="mt-1 text-sm text-red-600">{{ form.errors.account_holder }}</p>
                </div>
                <div>
                    <label for="iban" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ t('dashboard.inspectorPages.iban') }}</label>
                    <input id="iban" v-model="form.iban" type="text" required placeholder="DE00 0000 0000 0000 0000 00" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] uppercase focus:border-green-500 focus:outline-none" />
                    <p v-if="form.errors.iban" class="mt-1 text-sm text-red-600">{{ form.errors.iban }}</p>
                </div>
                <div>
                    <label for="bic" class="mb-1.5 block text-sm font-semibold text-navy-700">{{ t('dashboard.inspectorPages.bic') }}</label>
                    <input id="bic" v-model="form.bic" type="text" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] uppercase focus:border-green-500 focus:outline-none" />
                </div>
                <button type="submit" :disabled="form.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                    {{ t('dashboard.inspectorPages.requestPayoutTitle') }}
                </button>
                <p class="text-xs text-ink-500">{{ t('dashboard.inspectorPages.payoutNote2') }}</p>
            </form>
        </PageCard>

        <PageCard :title="t('dashboard.inspectorPages.payoutHistory')">
            <div v-if="history.length" class="divide-y divide-ink-100">
                <div v-for="p in history" :key="p.id" class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                    <div>
                        <p class="font-display font-bold text-navy-700">{{ formatEuro(p.amount) }}</p>
                        <p class="text-xs text-ink-500">{{ t('dashboard.inspectorPages.requested', { date: p.requested }) }}<span v-if="p.paid"> · {{ t('dashboard.inspectorPages.paid', { date: p.paid }) }}</span></p>
                    </div>
                    <StatusBadge :status="p.status" />
                </div>
            </div>
            <p v-else class="px-5 py-8 text-center text-sm text-ink-500">{{ t('dashboard.inspectorPages.noPayoutsRequested') }}</p>
        </PageCard>
    </div>
</template>
