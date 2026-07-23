<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';


watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [{ title: 'Erscheinungsbild-Einstellungen', href: edit() }],
    });
});
</script>

<template>
    <Head :title="'Erscheinungsbild-Einstellungen'" />

    <h1 class="sr-only">{{ 'Erscheinungsbild-Einstellungen' }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="'Erscheinungsbild-Einstellungen'"
            :description="'Passen Sie das Erscheinungsbild Ihres Kontos an'"
        />
        <AppearanceTabs />
    </div>
</template>
