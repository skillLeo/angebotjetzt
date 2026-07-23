import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import InspectorLayout from '@/layouts/InspectorLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'AngebotJetzt';

createInertiaApp({
    title: (title) => (title ? `${title} – ${appName}` : `${appName} – Anfragen. Vergleichen. Beauftragen.`),
    layout: (name) => {
        switch (true) {
            case name === 'Home' || name.startsWith('public/') || name.startsWith('wizard/'):
                return PublicLayout;
            case name === 'auth/InspectorLogin' || name === 'auth/AdminLogin' || name === 'auth/InspectorRegister':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [CustomerLayout, SettingsLayout];
            case name.startsWith('customer/'):
                return CustomerLayout;
            case name.startsWith('inspector/'):
                return InspectorLayout;
            case name.startsWith('admin/'):
                return AdminLayout;
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#3EAE2B',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
