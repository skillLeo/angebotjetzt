<script setup lang="ts">
import CenteredAuthShell from '@/components/auth/CenteredAuthShell.vue';
import FormField from '@/components/forms/FormField.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { BadgeCheck, Car, Check, CheckCircle2, ClipboardCheck, MapPin, Shield, UserRound, Wrench } from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed, ref } from 'vue';

const props = defineProps<{
    profile: { birthday: string | null; tax_id: string | null; street: string | null; plz: string | null; city: string | null; phone: string | null };
    pendingRequestId: number | null;
    progress: { basicsDone: boolean; serviceAreaDone: boolean; detailsDone: boolean };
    serviceAreas: Array<{ id: number; type: string; city: string | null; from: string | null; to: string | null }>;
    details: { bio: string | null; qualifications: string | null; years_experience: number | null; avatar_key: string | null };
    serviceTypes: Array<{ id: number; name: string }>;
    selectedServiceTypeIds: number[];
    avatarOptions: Array<{ key: string; icon: string; color: string }>;
}>();

const avatarIcons: Record<string, Component> = { Shield, Wrench, BadgeCheck, Car, ClipboardCheck, UserRound };

function isoDateYearsAgo(years: number): string {
    const d = new Date();
    d.setFullYear(d.getFullYear() - years);
    return d.toISOString().slice(0, 10);
}
const maxBirthday = isoDateYearsAgo(18);
const minBirthday = isoDateYearsAgo(100);

const basicsForm = useForm({
    birthday: props.profile.birthday ?? '',
    tax_id: props.profile.tax_id ?? '',
    street: props.profile.street ?? '',
    plz: props.profile.plz ?? '',
    city: props.profile.city ?? '',
    phone: props.profile.phone ?? '',
});
function submitBasics() {
    basicsForm.post('/inspector/complete-profile');
}

const areaForm = useForm({ type: 'city', city_name: '', postal_from: '', postal_to: '' });
function submitArea() {
    areaForm.post('/inspector/service-areas', { preserveScroll: true, onSuccess: () => areaForm.reset('city_name', 'postal_from', 'postal_to') });
}
function deleteArea(id: number) {
    areaForm.delete(`/inspector/service-areas/${id}`, { preserveScroll: true });
}

const detailsForm = useForm({
    bio: props.details.bio ?? '',
    qualifications: props.details.qualifications ?? '',
    years_experience: props.details.years_experience ?? '',
    avatar_key: props.details.avatar_key ?? '',
    service_type_ids: [...props.selectedServiceTypeIds],
});
function toggleServiceType(id: number) {
    const idx = detailsForm.service_type_ids.indexOf(id);
    if (idx === -1) detailsForm.service_type_ids.push(id);
    else detailsForm.service_type_ids.splice(idx, 1);
}
function submitDetails() {
    detailsForm.post('/inspector/profile', { preserveScroll: true });
}

// The active accordion step: the first one still incomplete, or the last one
// once everything is done — chosen once per page load so completing a step
// visibly advances to the next without the user having to click anything.
const openStep = ref(!props.progress.basicsDone ? 1 : !props.progress.serviceAreaDone ? 2 : !props.progress.detailsDone ? 3 : 3);
const allDone = computed(() => props.progress.basicsDone && props.progress.serviceAreaDone && props.progress.detailsDone);

// An admin-invited registration remembers which request brought the provider
// here — once they're done with (or skipping the rest of) onboarding, send
// them straight back to it instead of the generic dashboard.
const exitHref = computed(() => (props.pendingRequestId ? `/inspector/requests/${props.pendingRequestId}` : '/inspector'));
</script>

<template>
    <Head><title>{{ 'Konto vervollständigen' }}</title></Head>

    <CenteredAuthShell
        :title="'Konto einrichten'"
        :description="'Vervollständigen Sie diese drei Schritte, damit Sie Anfragen erhalten können. Sie können jederzeit unterbrechen und später fortsetzen.'"
        :icon="ClipboardCheck"
    >
        <div class="mb-6 flex items-center gap-2">
            <div v-for="n in 3" :key="n" class="h-1.5 flex-1 rounded-pill" :class="(n === 1 && progress.basicsDone) || (n === 2 && progress.serviceAreaDone) || (n === 3 && progress.detailsDone) ? 'bg-green-500' : 'bg-ink-100'" />
        </div>

        <div v-if="allDone" class="mb-4 flex flex-col items-center gap-3 rounded-card border border-green-200 bg-green-50 p-5 text-center">
            <CheckCircle2 :size="28" class="text-green-600" aria-hidden="true" />
            <div>
                <p class="font-display font-bold text-navy-700">{{ 'Alles erledigt!' }}</p>
                <p class="mt-1 text-sm text-ink-500">{{ 'Ihr Profil ist vollständig. Sie können unten jederzeit noch etwas anpassen.' }}</p>
            </div>
            <Link :href="exitHref" class="rounded-pill bg-green-500 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-green-600">
                {{ pendingRequestId ? 'Zur Anfrage' : 'Zum Dashboard' }}
            </Link>
        </div>

        <div class="space-y-4">
            <!-- Step 1: Basics -->
            <div class="rounded-card border" :class="progress.basicsDone ? 'border-ink-100' : 'border-green-500'">
                <button type="button" class="flex w-full items-center gap-3 px-5 py-4 text-left" @click="openStep = 1">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-pill text-sm font-bold" :class="progress.basicsDone ? 'bg-green-50 text-green-600' : 'bg-navy-50 text-navy-700'">
                        <CheckCircle2 v-if="progress.basicsDone" :size="16" aria-hidden="true" />
                        <template v-else>1</template>
                    </span>
                    <span class="font-display font-bold text-navy-700">{{ 'Firmen- & Steuerdaten' }}</span>
                </button>
                <form v-if="openStep === 1" class="space-y-4 border-t border-ink-100 p-5" @submit.prevent="submitBasics">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormField v-model="basicsForm.birthday" :label="'Geburtsdatum'" type="date" required :min="minBirthday" :max="maxBirthday" :error="basicsForm.errors.birthday" />
                        <FormField v-model="basicsForm.phone" :label="'Telefon'" required :error="basicsForm.errors.phone" />
                    </div>
                    <FormField v-model="basicsForm.tax_id" :label="'Steuernummer / USt-IdNr.'" required :error="basicsForm.errors.tax_id" />
                    <FormField v-model="basicsForm.street" :label="'Straße & Hausnummer (Firmensitz)'" required :error="basicsForm.errors.street" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <FormField v-model="basicsForm.plz" :label="'PLZ'" inputmode="numeric" :maxlength="5" required :error="basicsForm.errors.plz" />
                        <FormField v-model="basicsForm.city" :label="'Stadt (Rechnungsadresse)'" required :error="basicsForm.errors.city" />
                    </div>
                    <button type="submit" :disabled="basicsForm.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                        {{ 'Speichern & weiter' }}
                    </button>
                </form>
            </div>

            <!-- Step 2: Service area -->
            <div class="rounded-card border" :class="[progress.serviceAreaDone ? 'border-ink-100' : 'border-green-500', !progress.basicsDone ? 'opacity-50' : '']">
                <button type="button" class="flex w-full items-center gap-3 px-5 py-4 text-left" :disabled="!progress.basicsDone" @click="openStep = 2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-pill text-sm font-bold" :class="progress.serviceAreaDone ? 'bg-green-50 text-green-600' : 'bg-navy-50 text-navy-700'">
                        <CheckCircle2 v-if="progress.serviceAreaDone" :size="16" aria-hidden="true" />
                        <template v-else>2</template>
                    </span>
                    <span class="font-display font-bold text-navy-700">{{ 'Servicegebiet' }}</span>
                </button>
                <div v-if="openStep === 2 && progress.basicsDone" class="space-y-4 border-t border-ink-100 p-5">
                    <div v-if="serviceAreas.length" class="space-y-2">
                        <div v-for="a in serviceAreas" :key="a.id" class="flex items-center justify-between rounded-card bg-sand-50 px-4 py-2.5 text-sm">
                            <span class="flex items-center gap-2 font-semibold text-navy-700">
                                <MapPin :size="14" class="text-green-500" aria-hidden="true" />
                                {{ a.type === 'city' ? a.city : `PLZ ${a.from} – ${a.to}` }}
                            </span>
                            <button type="button" class="text-xs font-bold text-red-600" @click="deleteArea(a.id)">{{ 'Entfernen' }}</button>
                        </div>
                    </div>
                    <form class="space-y-3" @submit.prevent="submitArea">
                        <div class="flex gap-2">
                            <label class="flex items-center gap-1.5 text-sm"><input v-model="areaForm.type" type="radio" value="city" class="accent-green-500" /> {{ 'Stadt' }}</label>
                            <label class="flex items-center gap-1.5 text-sm"><input v-model="areaForm.type" type="radio" value="postal_range" class="accent-green-500" /> {{ 'PLZ-Bereich' }}</label>
                        </div>
                        <FormField v-if="areaForm.type === 'city'" v-model="areaForm.city_name" :label="'Stadt'" placeholder="z. B. Köln" :error="areaForm.errors.city_name" />
                        <div v-else class="grid grid-cols-2 gap-3">
                            <FormField v-model="areaForm.postal_from" :label="'PLZ von'" inputmode="numeric" :maxlength="5" :error="areaForm.errors.postal_from" />
                            <FormField v-model="areaForm.postal_to" :label="'PLZ bis'" inputmode="numeric" :maxlength="5" :error="areaForm.errors.postal_to" />
                        </div>
                        <button type="submit" :disabled="areaForm.processing" class="w-full rounded-pill bg-green-500 py-3 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                            {{ 'Servicegebiet hinzufügen' }}
                        </button>
                    </form>
                    <button v-if="progress.serviceAreaDone" type="button" class="w-full rounded-pill border border-ink-300 py-3 text-sm font-bold text-navy-700 transition hover:border-navy-700" @click="openStep = 3">
                        {{ 'Weiter' }}
                    </button>
                </div>
            </div>

            <!-- Step 3: Profile details -->
            <div class="rounded-card border" :class="[progress.detailsDone ? 'border-ink-100' : 'border-green-500', !progress.basicsDone ? 'opacity-50' : '']">
                <button type="button" class="flex w-full items-center gap-3 px-5 py-4 text-left" :disabled="!progress.basicsDone" @click="openStep = 3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-pill text-sm font-bold" :class="progress.detailsDone ? 'bg-green-50 text-green-600' : 'bg-navy-50 text-navy-700'">
                        <CheckCircle2 v-if="progress.detailsDone" :size="16" aria-hidden="true" />
                        <template v-else>3</template>
                    </span>
                    <span class="font-display font-bold text-navy-700">{{ 'Profil & Qualifikationen' }}</span>
                </button>
                <form v-if="openStep === 3 && progress.basicsDone" class="space-y-5 border-t border-ink-100 p-5" @submit.prevent="submitDetails">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Über mich' }}</label>
                        <textarea v-model="detailsForm.bio" rows="3" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Qualifikationen & Zertifikate' }}</label>
                        <textarea v-model="detailsForm.qualifications" rows="2" class="w-full rounded-card border border-ink-300 px-4 py-3 text-[15px] focus:border-green-500 focus:outline-none" />
                    </div>
                    <FormField v-model="detailsForm.years_experience" :label="'Jahre Erfahrung'" type="number" :error="detailsForm.errors.years_experience" />
                    <div v-if="serviceTypes.length">
                        <label class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Angebotene Leistungen' }}</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="st in serviceTypes"
                                :key="st.id"
                                type="button"
                                class="rounded-pill border px-4 py-2 text-sm font-semibold transition"
                                :class="detailsForm.service_type_ids.includes(st.id) ? 'border-green-500 bg-green-50 text-green-700' : 'border-ink-300 text-ink-700 hover:border-navy-700'"
                                @click="toggleServiceType(st.id)"
                            >
                                {{ st.name }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-navy-700">{{ 'Profilsymbol' }}</label>
                        <div class="flex flex-wrap gap-3">
                            <button
                                v-for="opt in avatarOptions"
                                :key="opt.key"
                                type="button"
                                class="relative flex h-11 w-11 items-center justify-center rounded-full text-white transition"
                                :style="{ backgroundColor: opt.color }"
                                :class="detailsForm.avatar_key === opt.key ? 'ring-2 ring-navy-700 ring-offset-2' : 'opacity-80 hover:opacity-100'"
                                @click="detailsForm.avatar_key = opt.key"
                            >
                                <component :is="avatarIcons[opt.icon] ?? UserRound" :size="18" aria-hidden="true" />
                                <span v-if="detailsForm.avatar_key === opt.key" class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-white text-green-600 shadow-card">
                                    <Check :size="10" aria-hidden="true" />
                                </span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" :disabled="detailsForm.processing" class="w-full rounded-pill bg-green-500 py-3.5 font-bold text-white transition hover:bg-green-600 disabled:opacity-60">
                        {{ 'Speichern' }}
                    </button>
                </form>
            </div>
        </div>

        <div v-if="progress.basicsDone" class="mt-6 text-center">
            <Link :href="exitHref" class="text-sm font-semibold text-ink-500 hover:text-navy-700">
                {{ allDone ? (pendingRequestId ? 'Zur Anfrage' : 'Zum Dashboard') : 'Später fortsetzen' }}
            </Link>
        </div>
    </CenteredAuthShell>
</template>
