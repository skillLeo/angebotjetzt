<script setup lang="ts">
import BrandLogo from '@/components/marketing/BrandLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { avatarIcons } from '@/lib/avatars';
import { onClickOutside } from '@vueuse/core';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, BellOff, LogOut, Menu, X } from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed, ref, useTemplateRef } from 'vue';

const props = defineProps<{
    title: string;
    nav: Array<{ label: string; href: string; icon: Component; active: boolean }>;
    logoutRoute: string;
    userName: string;
    userRole: string;
    userAvatar?: { icon: string; color: string } | null;
}>();

const sidebarOpen = ref(false);
const notifOpen = ref(false);
const notifRef = useTemplateRef('notifRef');
onClickOutside(notifRef, () => (notifOpen.value = false));

const page = usePage();
const notifications = computed(() => (page.props.notifications as { count: number; items: Array<{ id: number; title: string; body: string | null; link: string | null; read: boolean; date: string }> }) ?? { count: 0, items: [] });

function logout() {
    router.post(props.logoutRoute);
}

function markAllRead() {
    router.post('/notifications/read-all', {}, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <div class="min-h-screen bg-sand-50">
        <!-- Sidebar (desktop) -->
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col bg-navy-950 lg:flex">
            <div class="flex h-16 items-center border-b border-navy-800 px-6">
                <Link href="/">
                    <BrandLogo inverted />
                </Link>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" :aria-label="'Portal-Navigation'">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="group flex items-center gap-3 rounded-card px-3.5 py-2.5 text-sm font-semibold transition-all duration-150"
                    :class="
                        item.active
                            ? 'bg-green-500 text-white shadow-[0_2px_10px_rgba(62,174,43,0.35)]'
                            : 'text-navy-100 hover:translate-x-0.5 hover:bg-navy-800 hover:text-white'
                    "
                >
                    <component :is="item.icon" :size="19" aria-hidden="true" class="shrink-0 transition-transform group-hover:scale-110" />
                    {{ item.label }}
                </Link>
            </nav>
            <div class="border-t border-navy-800 p-3">
                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-card px-3.5 py-2.5 text-sm font-medium text-navy-100 transition hover:bg-navy-800 hover:text-white"
                    @click="logout"
                >
                    <LogOut :size="19" aria-hidden="true" />
                    {{ 'Abmelden' }}
                </button>
            </div>
        </aside>

        <!-- Mobile sidebar -->
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150"
            leave-to-class="opacity-0"
        >
            <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
                <div class="absolute inset-0 bg-navy-950/60" @click="sidebarOpen = false" />
                <aside class="absolute inset-y-0 left-0 flex w-72 flex-col bg-navy-950">
                    <div class="flex h-16 items-center justify-between border-b border-navy-800 px-6">
                        <BrandLogo inverted />
                        <button type="button" :aria-label="'Menü schließen'" @click="sidebarOpen = false">
                            <X :size="24" class="text-white" aria-hidden="true" />
                        </button>
                    </div>
                    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
                        <Link
                            v-for="item in nav"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-card px-3.5 py-2.5 text-sm font-semibold transition"
                            :class="item.active ? 'bg-green-500 text-white shadow-[0_2px_10px_rgba(62,174,43,0.35)]' : 'text-navy-100 hover:bg-navy-800 hover:text-white'"
                            @click="sidebarOpen = false"
                        >
                            <component :is="item.icon" :size="19" aria-hidden="true" class="shrink-0" />
                            {{ item.label }}
                        </Link>
                    </nav>
                    <div class="border-t border-navy-800 p-3">
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-card px-3.5 py-2.5 text-sm font-medium text-navy-100 hover:bg-navy-800"
                            @click="logout"
                        >
                            <LogOut :size="19" aria-hidden="true" /> {{ 'Abmelden' }}
                        </button>
                    </div>
                </aside>
            </div>
        </Transition>

        <div class="lg:pl-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-ink-100 bg-white/90 px-4 backdrop-blur-sm sm:px-6">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-pill text-navy-700 lg:hidden"
                        :aria-label="'Menü öffnen'"
                        @click="sidebarOpen = true"
                    >
                        <Menu :size="24" aria-hidden="true" />
                    </button>
                    <h1 class="font-display text-lg font-bold text-navy-700">{{ title }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <div ref="notifRef" class="relative">
                        <button
                            type="button"
                            class="relative flex h-10 w-10 items-center justify-center rounded-pill text-ink-700 transition hover:bg-sand-100"
                            :aria-label="'Benachrichtigungen'"
                            @click="notifOpen = !notifOpen"
                        >
                            <Bell :size="20" aria-hidden="true" />
                            <span
                                v-if="notifications.count > 0"
                                class="absolute top-1.5 right-1.5 flex h-4 min-w-4 items-center justify-center rounded-pill bg-green-500 px-1 text-[10px] font-bold text-white ring-2 ring-white"
                            >
                                {{ notifications.count }}
                            </span>
                        </button>
                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="opacity-0 -translate-y-1 scale-95"
                            leave-active-class="transition duration-100 ease-in"
                            leave-to-class="opacity-0 -translate-y-1 scale-95"
                        >
                            <div
                                v-if="notifOpen"
                                class="absolute right-0 mt-2 w-80 origin-top-right overflow-hidden rounded-card border border-ink-100 bg-white shadow-lift"
                            >
                                <div class="flex items-center justify-between border-b border-ink-100 px-4 py-3">
                                    <p class="font-display text-sm font-bold text-navy-700">{{ 'Benachrichtigungen' }}</p>
                                    <button
                                        v-if="notifications.count > 0"
                                        type="button"
                                        class="text-xs font-semibold text-green-600 transition hover:text-green-700"
                                        @click="markAllRead"
                                    >
                                        {{ 'Alle als gelesen markieren' }}
                                    </button>
                                </div>
                                <div v-if="notifications.items.length" class="max-h-96 overflow-y-auto">
                                    <Link
                                        v-for="n in notifications.items"
                                        :key="n.id"
                                        :href="n.link ?? '#'"
                                        class="block border-b border-ink-100 px-4 py-3 transition hover:bg-sand-50"
                                        :class="!n.read ? 'bg-green-50/40' : ''"
                                        @click="notifOpen = false"
                                    >
                                        <p class="text-sm font-semibold text-navy-700">{{ n.title }}</p>
                                        <p v-if="n.body" class="mt-0.5 text-xs text-ink-500">{{ n.body }}</p>
                                        <p class="mt-1 text-xs text-ink-300">{{ n.date }}</p>
                                    </Link>
                                </div>
                                <div v-else class="flex flex-col items-center gap-2 px-4 py-8 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-pill bg-sand-100 text-ink-500">
                                        <BellOff :size="18" aria-hidden="true" />
                                    </span>
                                    <p class="text-sm text-ink-500">{{ 'Keine Benachrichtigungen' }}</p>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <div class="hidden items-center gap-3 sm:flex">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-navy-700">{{ userName }}</p>
                            <p class="text-xs text-ink-500">{{ userRole }}</p>
                        </div>
                        <span
                            v-if="userAvatar"
                            class="flex h-9 w-9 items-center justify-center rounded-pill text-white ring-2 ring-navy-50"
                            :style="{ backgroundColor: userAvatar.color }"
                        >
                            <component :is="avatarIcons[userAvatar.icon] ?? undefined" :size="16" aria-hidden="true" />
                        </span>
                        <span
                            v-else
                            class="flex h-9 w-9 items-center justify-center rounded-pill bg-navy-700 font-display text-sm font-bold text-white ring-2 ring-navy-50"
                        >
                            {{ userName.charAt(0) }}
                        </span>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>

        <Toaster position="top-center" rich-colors />
    </div>
</template>
