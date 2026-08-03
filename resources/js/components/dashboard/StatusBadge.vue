<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ status: string }>();

const clsMap: Record<string, string> = {
    open: 'bg-navy-50 text-navy-600',
    offers_received: 'bg-green-50 text-green-700',
    accepted: 'bg-green-50 text-green-700',
    completed: 'bg-navy-100 text-navy-700',
    cancelled: 'bg-ink-100 text-ink-700',
    expired: 'bg-ink-100 text-ink-500',
    unmatched: 'bg-amber-100 text-amber-700',
    rejected: 'bg-ink-100 text-ink-500',
    withdrawn: 'bg-ink-100 text-ink-500',
    awaiting_payment: 'bg-amber-100 text-amber-700',
    paid: 'bg-green-50 text-green-700',
    in_progress: 'bg-navy-50 text-navy-600',
    completed_by_inspector: 'bg-green-50 text-green-700',
    confirmed: 'bg-green-100 text-green-700',
    refunded: 'bg-ink-100 text-ink-500',
    pending: 'bg-amber-100 text-amber-700',
    failed: 'bg-red-100 text-red-700',
};

const labelMap: Record<string, string> = {
    open: 'Offen',
    offers_received: 'Angebote erhalten',
    accepted: 'Angenommen',
    completed: 'Abgeschlossen',
    cancelled: 'Storniert',
    expired: 'Abgelaufen',
    unmatched: 'Kein Anbieter',
    rejected: 'Abgelehnt',
    withdrawn: 'Zurückgezogen',
    awaiting_payment: 'Zahlung ausstehend',
    paid: 'Bezahlt',
    in_progress: 'In Bearbeitung',
    completed_by_inspector: 'Abgeschlossen (Anbieter)',
    confirmed: 'Bestätigt',
    refunded: 'Erstattet',
    pending: 'Ausstehend',
    failed: 'Fehlgeschlagen',
};

const badge = computed(() => ({
    label: labelMap[props.status] ?? props.status,
    cls: clsMap[props.status] ?? 'bg-ink-100 text-ink-700',
}));
</script>

<template>
    <span class="inline-flex items-center rounded-pill px-2.5 py-1 text-xs font-bold whitespace-nowrap" :class="badge.cls">
        {{ badge.label }}
    </span>
</template>
