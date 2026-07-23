<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import type { LucideIcon } from 'lucide-vue-next';

const { title = '', description = '', icon } = defineProps<{
    title?: string;
    description?: string;
    icon?: LucideIcon;
}>();
</script>

<template>
    <AuthLayout :title="title" :description="description" :icon="icon">
        <slot />
    </AuthLayout>
</template>
