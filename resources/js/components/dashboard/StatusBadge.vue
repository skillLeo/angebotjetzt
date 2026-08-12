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
    // Amber, not green: this one is still waiting on someone.
    completed_by_inspector: 'bg-amber-100 text-amber-700',
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
    // Names the party being waited on, so this in-between state is not read
    // as "finished" in the admin list.
    completed_by_inspector: 'Wartet auf Kundenbestätigung',
    confirmed: 'Bestätigt & abgeschlossen',
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
