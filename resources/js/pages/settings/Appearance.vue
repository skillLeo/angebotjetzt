<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

const { t } = useI18n();

watchEffect(() => {
    setLayoutProps({
        breadcrumbs: [{ title: t('dashboard.settingsPage.appearanceSettings'), href: edit() }],
    });
});
</script>

<template>
    <Head :title="t('dashboard.settingsPage.appearanceSettings')" />

    <h1 class="sr-only">{{ t('dashboard.settingsPage.appearanceSettings') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('dashboard.settingsPage.appearanceSettings')"
            :description="t('dashboard.settingsPage.appearanceDesc')"
        />
        <AppearanceTabs />
    </div>
</template>
