<script setup lang="ts">
import PageCard from '@/components/dashboard/PageCard.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { formatEuro } from '@/lib/format';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, BadgeCheck, CheckCircle2, Clock, MapPin, Package, Star, Wallet, XCircle } from 'lucide-vue-next';

const props = defineProps<{
    inspector: {
        id: number; name: string; company: string | null; email: string; phone: string | null; city: string | null;
        street: string | null; plz: string | null; category: string | null; birthday: string | null; taxId: string | null; bio: string | null;
        active: boolean; approved: boolean; verified: boolean; emailVerified: boolean; profileComplete: boolean;
        since: string | null; imported: string | null;
        jobs: number; offers: number; reviews: number; rating: number | null;
        wallet: { available: number; pending: number; lifetime: number };
        areas: Array<{ id: number; type: string; city: string | null; from: number | null; to: number | null }>;
    };
}>();

function toggle() {
    router.post(`/admin/inspectors/${props.inspector.id}/status`, {}, { preserveScroll: true });
}
function approve() {
    router.post(`/admin/inspectors/${props.inspector.id}/approve`, {}, { preserveScroll: true });
}
function toggleVerified() {
    router.post(`/admin/inspectors/${props.inspector.id}/verified`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head><title>{{ inspector.name }}</title></Head>

    <Link href="/admin/inspectors" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-ink-500 hover:text-navy-700">
        <ArrowLeft :size="16" aria-hidden="true" /> Zurück
    </Link>

    <div v-if="!inspector.approved" class="mb-6 rounded-card border border-amber-200 bg-amber-50 px-5 py-4">
        <div class="flex items-center gap-3">
            <Clock :size="20" class="shrink-0 text-amber-600" aria-hidden="true" />
            <p class="flex-1 text-sm font-semibold text-amber-800">Dieser Gutachter wartet auf Freischaltung und erhält noch keine Anfragen.</p>
            <button type="button" class="rounded-pill bg-green-500 px-5 py-2 text-sm font-bold text-white transition hover:bg-green-600" @click="approve">
                Freischalten
            </button>
        </div>
        <div class="mt-3 flex flex-wrap gap-4 pl-8 text-xs font-semibold">
            <span class="inline-flex items-center gap-1.5" :class="inspector.emailVerified ? 'text-green-700' : 'text-amber-800'">
                <component :is="inspector.emailVerified ? CheckCircle2 : XCircle" :size="14" aria-hidden="true" /> E-Mail bestätigt
            </span>
            <span class="inline-flex items-center gap-1.5" :class="inspector.profileComplete ? 'text-green-700' : 'text-amber-800'">
                <component :is="inspector.profileComplete ? CheckCircle2 : XCircle" :size="14" aria-hidden="true" /> Profil vervollständigt
            </span>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-navy-700">{{ inspector.name }}</h1>
            <p class="text-ink-500">{{ inspector.company }} · {{ inspector.city }} · {{ inspector.email }}</p>
            <p v-if="inspector.imported" class="mt-1 text-xs text-ink-500">Importiert von: {{ inspector.imported }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-pill px-5 py-2.5 text-sm font-bold transition"
                :class="inspector.verified ? 'bg-navy-50 text-navy-700 hover:bg-navy-100' : 'bg-white text-ink-700 ring-1 ring-ink-300 hover:bg-sand-50'"
                @click="toggleVerified"
            >
                <BadgeCheck :size="17" aria-hidden="true" /> {{ inspector.verified ? 'Geprüft-Status entfernen' : 'Als geprüft markieren' }}
            </button>
            <button type="button" class="rounded-pill px-6 py-2.5 text-sm font-bold text-white transition" :class="inspector.active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'" @click="toggle">
                {{ inspector.active ? 'Deaktivieren' : 'Aktivieren' }}
            </button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Aufträge" :value="inspector.jobs" :icon="Package" />
        <StatCard label="Angebote" :value="inspector.offers" :icon="Package" />
        <StatCard label="Bewertung" :value="inspector.rating ? `${inspector.rating} (${inspector.reviews})` : '–'" :icon="Star" />
        <StatCard label="Guthaben" :value="formatEuro(inspector.wallet.available)" :icon="Wallet" accent />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <PageCard title="Profil">
            <div class="space-y-3 p-6 sm:p-8">
                <div><p class="text-sm text-ink-500">Kategorie</p><p class="font-semibold text-navy-700">{{ inspector.category ?? '–' }}</p></div>
                <div><p class="text-sm text-ink-500">Telefon</p><p class="font-semibold text-navy-700">{{ inspector.phone ?? '–' }}</p></div>
                <div><p class="text-sm text-ink-500">Firmensitz</p><p class="font-semibold text-navy-700">{{ inspector.street ? `${inspector.street}, ${inspector.plz} ${inspector.city}` : '–' }}</p></div>
                <div><p class="text-sm text-ink-500">Geburtsdatum</p><p class="font-semibold text-navy-700">{{ inspector.birthday ?? '–' }}</p></div>
                <div><p class="text-sm text-ink-500">Steuernummer</p><p class="font-semibold text-navy-700">{{ inspector.taxId ?? '–' }}</p></div>
                <div><p class="text-sm text-ink-500">Dabei seit</p><p class="font-semibold text-navy-700">{{ inspector.since ?? '–' }}</p></div>
                <div v-if="inspector.bio"><p class="text-sm text-ink-500">Über</p><p class="text-navy-700">{{ inspector.bio }}</p></div>
            </div>
        </PageCard>

        <PageCard title="Servicegebiete">
            <div v-if="inspector.areas.length" class="divide-y divide-ink-100">
                <div v-for="a in inspector.areas" :key="a.id" class="flex items-center gap-3 px-5 py-3 sm:px-6">
                    <MapPin :size="16" class="text-green-500" aria-hidden="true" />
                    <span class="text-sm font-semibold text-navy-700">{{ a.type === 'city' ? a.city : `PLZ ${a.from} – ${a.to}` }}</span>
                </div>
            </div>
            <p v-else class="px-5 py-6 text-center text-sm text-ink-500">Keine Servicegebiete.</p>
        </PageCard>
    </div>
</template>
