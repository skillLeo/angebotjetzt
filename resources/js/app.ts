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

// Browser translation tools (Google Translate etc.) re-parent text nodes into
// <font> wrappers Vue doesn't know about. Vue's patch then throws on
// removeChild/insertBefore and the page silently stops updating — e.g. a form
// submit succeeds server-side but the UI never leaves the page. Tolerating the
// two calls that throw keeps the app functional while translated.
if (typeof Node === 'function' && Node.prototype) {
    const originalRemoveChild = Node.prototype.removeChild;
    Node.prototype.removeChild = function <T extends Node>(this: Node, child: T): T {
        if (child.parentNode !== this) {
            return child;
        }
        return originalRemoveChild.call(this, child) as T;
    };
    const originalInsertBefore = Node.prototype.insertBefore;
    Node.prototype.insertBefore = function <T extends Node>(this: Node, newNode: T, referenceNode: Node | null): T {
        if (referenceNode && referenceNode.parentNode !== this) {
            return originalInsertBefore.call(this, newNode, null) as T;
        }
        return originalInsertBefore.call(this, newNode, referenceNode) as T;
    };
}

const appName = import.meta.env.VITE_APP_NAME || 'AngebotJetzt';

createInertiaApp({
    title: (title) => (title ? `${title} – ${appName}` : `${appName} – Anfragen. Vergleichen. Beauftragen.`),
    layout: (name) => {
        switch (true) {
            case name === 'Home' || name.startsWith('public/') || name.startsWith('wizard/'):
                return PublicLayout;
            case name === 'auth/AdminLogin' || name === 'auth/InspectorRegister':
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
