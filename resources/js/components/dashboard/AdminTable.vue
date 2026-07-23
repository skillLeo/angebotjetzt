<script setup lang="ts">
// Responsive table: real table >= md, stacked cards below (never horizontal scroll).
import { Inbox } from 'lucide-vue-next';
import EmptyState from '@/components/dashboard/EmptyState.vue';

defineProps<{
    columns: Array<{ key: string; label: string; align?: 'right' | 'center' }>;
    rows: Array<Record<string, unknown>>;
    rowKey?: string;
}>();
</script>

<template>
    <div>
        <!-- Desktop table -->
        <table v-if="rows.length" class="hidden w-full md:table">
            <thead>
                <tr class="border-b border-ink-100 text-left">
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        class="px-4 py-3 text-xs font-bold tracking-wide text-ink-500 uppercase"
                        :class="{ 'text-right': col.align === 'right', 'text-center': col.align === 'center' }"
                    >
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, i) in rows" :key="rowKey ? String(row[rowKey]) : i" class="border-b border-ink-100 transition-colors hover:bg-sand-50">
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        class="px-4 py-3.5 text-sm text-navy-700"
                        :class="{ 'text-right': col.align === 'right', 'text-center': col.align === 'center' }"
                    >
                        <slot :name="col.key" :row="row" :value="row[col.key]">{{ row[col.key] }}</slot>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Mobile cards -->
        <div v-if="rows.length" class="divide-y divide-ink-100 md:hidden">
            <div v-for="(row, i) in rows" :key="rowKey ? String(row[rowKey]) : i" class="space-y-2 px-4 py-4 transition-colors active:bg-sand-50">
                <div v-for="col in columns" :key="col.key" class="flex items-start justify-between gap-3">
                    <span class="text-xs font-bold tracking-wide text-ink-500 uppercase">{{ col.label }}</span>
                    <span class="text-right text-sm text-navy-700">
                        <slot :name="col.key" :row="row" :value="row[col.key]">{{ row[col.key] }}</slot>
                    </span>
                </div>
            </div>
        </div>

        <EmptyState v-if="!rows.length" :icon="Inbox" title="Keine Einträge vorhanden" />
    </div>
</template>
