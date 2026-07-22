<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{ status: string }>();

// German labels + colour class per status across the whole platform.
const map: Record<string, { label: string; cls: string }> = {
    open: { label: 'Offen', cls: 'bg-navy-50 text-navy-600' },
    offers_received: { label: 'Angebote erhalten', cls: 'bg-green-50 text-green-700' },
    accepted: { label: 'Angenommen', cls: 'bg-green-50 text-green-700' },
    completed: { label: 'Abgeschlossen', cls: 'bg-navy-100 text-navy-700' },
    cancelled: { label: 'Storniert', cls: 'bg-ink-100 text-ink-700' },
    expired: { label: 'Abgelaufen', cls: 'bg-ink-100 text-ink-500' },
    unmatched: { label: 'Kein Gutachter', cls: 'bg-amber-100 text-amber-700' },
    rejected: { label: 'Abgelehnt', cls: 'bg-ink-100 text-ink-500' },
    withdrawn: { label: 'Zurückgezogen', cls: 'bg-ink-100 text-ink-500' },
    awaiting_payment: { label: 'Zahlung ausstehend', cls: 'bg-amber-100 text-amber-700' },
    paid: { label: 'Bezahlt', cls: 'bg-green-50 text-green-700' },
    in_progress: { label: 'In Bearbeitung', cls: 'bg-navy-50 text-navy-600' },
    completed_by_inspector: { label: 'Abgeschlossen (Gutachter)', cls: 'bg-green-50 text-green-700' },
    confirmed: { label: 'Bestätigt', cls: 'bg-green-100 text-green-700' },
    refunded: { label: 'Erstattet', cls: 'bg-ink-100 text-ink-500' },
    pending: { label: 'Ausstehend', cls: 'bg-amber-100 text-amber-700' },
    failed: { label: 'Fehlgeschlagen', cls: 'bg-red-100 text-red-700' },
};

const badge = computed(() => map[props.status] ?? { label: props.status, cls: 'bg-ink-100 text-ink-700' });
</script>

<template>
    <span class="inline-flex items-center rounded-pill px-2.5 py-1 text-xs font-bold whitespace-nowrap" :class="badge.cls">
        {{ badge.label }}
    </span>
</template>
