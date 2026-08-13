<script setup lang="ts">
import LegalPage from '@/components/marketing/LegalPage.vue';

defineProps<{ content: { title: string; updated: string; body: string } }>();
</script>

<template>
    <LegalPage :title="content.title" :updated="content.updated || undefined" :body="content.body" />
</template>
