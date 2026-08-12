<script setup lang="ts">
import PartnerHandoff from '@/components/marketing/PartnerHandoff.vue';
import WizardAccidentStep from '@/components/wizard/WizardAccidentStep.vue';
import WizardDynamicStep, { type WizardFieldDef } from '@/components/wizard/WizardDynamicStep.vue';
import WizardStep1 from '@/components/wizard/WizardStep1.vue';
import WizardStep2 from '@/components/wizard/WizardStep2.vue';
import WizardStep3 from '@/components/wizard/WizardStep3.vue';
import WizardStep4 from '@/components/wizard/WizardStep4.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Check } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<{
    serviceTypes: Array<{ id: number; name: string; slug: string; description: string; flowMode: string; externalUrl: string | null }>;
    serviceTypeFields: Record<number, WizardFieldDef[]>;
    preselected: string | null;
    partnerUrl: string | null;
}>();

const page = usePage();
const authUser = (page.props.auth as { user?: { name?: string; email?: string; phone?: string } }).user;
const isGuest = !authUser;

const DRAFT_KEY = 'aj_request_draft';
const step = ref(1);
const photos = ref<File[]>([]);

const form = useForm({
    service_type_id: null as number | null,
    vehicle_make: '',
    vehicle_model: '',
    first_registration: '',
    mileage: '' as string | number,
    vin: '',
    fuel_type: '',
    transmission: '',
    plz: '',
    ort: '',
    strasse: '',
    preferred_date: '',
    alternative_date: '',
    notes: '',
    contact_name: authUser?.name ?? '',
    contact_email: authUser?.email ?? '',
    contact_phone: authUser?.phone ?? '',
    agb: false as boolean,
    privacy: false as boolean,
    photos: [] as File[],
    answers: {} as Record<string, string | number | File | null>,
    accident_role: '' as string,
    has_lawyer: null as boolean | null,
});

// Kfz-Gutachten (and any other service with nothing defined yet) has zero
// custom fields, so this stays empty and step 2 keeps rendering the existing
// hardcoded vehicle-appraisal form exactly as before — only a service type an
// admin has actually configured fields for switches to the dynamic renderer.
const activeFields = computed(() => props.serviceTypeFields[form.service_type_id ?? -1] ?? []);

const activeType = computed(() => props.serviceTypes.find((t) => t.id === form.service_type_id) ?? null);

// Unfallschadengutachten inserts an extra questions step between choosing the
// service and entering vehicle details. Every other service keeps exactly the
// four steps it has always had.
const isDirectAccept = computed(() => activeType.value?.flowMode === 'direct_accept');

// Choosing a partner-fulfilled service stops the wizard here. The hand-off
// replaces every step, so there is no way through to the booking details
// whichever entry point selected it.
const isExternal = computed(() => activeType.value?.flowMode === 'external');
const partnerHandoffUrl = computed(() => activeType.value?.externalUrl ?? props.partnerUrl);

const stepKeys = computed(() => [
    'service',
    ...(isDirectAccept.value ? ['accident'] : []),
    'vehicle',
    'location',
    'contact',
]);

const stepLabels: Record<string, string> = {
    service: 'Leistung',
    accident: 'Unfall',
    vehicle: 'Fahrzeug',
    location: 'Ort & Termin',
    contact: 'Kontakt',
};

const steps = computed(() => stepKeys.value.map((k) => stepLabels[k]));
const currentKey = computed(() => stepKeys.value[step.value - 1] ?? 'contact');

// The dropdown fields in this wizard sit close to the sticky top bar on a
// page short enough to barely need scrolling. Closing one of those
// dropdowns triggers the browser's own "keep the focused control clear of
// the sticky header" scroll correction — real, and not something CSS
// scroll-margin fully cancels, but the smooth-scroll animation is what
// makes that correction look like a jarring page jump/blink. Landing
// instantly instead of gliding removes that, without touching smooth
// scrolling anywhere else on the site.
onMounted(() => document.documentElement.classList.add('scroll-auto-override'));
onUnmounted(() => document.documentElement.classList.remove('scroll-auto-override'));

onMounted(() => {
    const saved = localStorage.getItem(DRAFT_KEY);
    if (saved) {
        try {
            Object.assign(form, { ...JSON.parse(saved), photos: [] });
        } catch {
            /* ignore malformed draft */
        }
    }

    // A partner service is a dead end rather than a half-finished request, so
    // never restore one from the draft: otherwise every later visit to the
    // wizard reopens on the hand-off instead of the service picker.
    const restored = props.serviceTypes.find((t) => t.id === form.service_type_id);
    if (restored?.flowMode === 'external') {
        form.service_type_id = null;
    }

    if (props.preselected) {
        const match = props.serviceTypes.find((t) => t.slug === props.preselected);
        if (match) {
            form.service_type_id = match.id;
            // Already chosen on the homepage/category page — don't ask again.
            step.value = 2;
        }
    }
    const q = new URLSearchParams(window.location.search);
    if (q.get('plz')) form.plz = q.get('plz') as string;
});

watch(
    () => ({
        ...form.data(),
        photos: undefined,
        // Same reason as the restore guard above: a partner service must not
        // be written into the draft in the first place.
        service_type_id: isExternal.value ? null : form.service_type_id,
        // File objects (from a dynamic file-upload field) aren't valid draft
        // data either — same reasoning as excluding photos above.
        answers: Object.fromEntries(
            Object.entries(form.data().answers ?? {}).map(([k, v]) => [k, v instanceof File ? null : v]),
        ),
    }),
    (val) => localStorage.setItem(DRAFT_KEY, JSON.stringify(val)),
    { deep: true },
);

function next() {
    if (step.value < stepKeys.value.length) step.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function back() {
    if (step.value > 1) step.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function submit() {
    form.photos = photos.value;
    form.transform((data) => ({
        ...data,
        mileage: data.mileage === '' ? null : data.mileage,
        // Switching away from Unfallschadengutachten mid-wizard can leave
        // stale answers behind; only send them where they're actually asked.
        accident_role: isDirectAccept.value ? data.accident_role : null,
        has_lawyer: isDirectAccept.value ? data.has_lawyer : null,
    })).post('/request', {
        forceFormData: true,
        onSuccess: () => localStorage.removeItem(DRAFT_KEY),
    });
}
</script>

<template>
    <Head>
        <title>Anfrage stellen</title>
    </Head>

    <section class="bg-sand-50 px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <div class="mx-auto max-w-3xl">
            <!-- Progress: hidden once a partner service is chosen, there are no steps left to take. -->
            <ol v-if="!isExternal" class="mb-10 flex items-center justify-between">
                <li v-for="(label, i) in steps" :key="label" class="flex flex-1 items-center">
                    <div class="flex flex-col items-center">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-pill font-display text-sm font-bold transition"
                            :class="
                                step > i + 1
                                    ? 'bg-green-500 text-white'
                                    : step === i + 1
                                      ? 'bg-navy-700 text-white'
                                      : 'bg-white text-ink-500 ring-1 ring-ink-300'
                            "
                        >
                            <Check v-if="step > i + 1" :size="18" aria-hidden="true" />
                            <template v-else>{{ i + 1 }}</template>
                        </span>
                        <span class="mt-2 hidden text-xs font-semibold sm:block" :class="step >= i + 1 ? 'text-navy-700' : 'text-ink-500'">
                            {{ label }}
                        </span>
                    </div>
                    <div v-if="i < steps.length - 1" class="mx-2 h-0.5 flex-1" :class="step > i + 1 ? 'bg-green-500' : 'bg-ink-100'" />
                </li>
            </ol>

            <div class="rounded-panel border border-ink-100 bg-white p-6 shadow-card sm:p-8">
                <!-- Interception. Takes priority over every step, so selecting a
                     partner service can never continue into the booking details. -->
                <div v-if="isExternal">
                    <PartnerHandoff :url="partnerHandoffUrl" inline />
                    <button
                        type="button"
                        class="mt-6 inline-flex items-center gap-2 text-sm font-bold text-ink-500 transition hover:text-navy-700"
                        @click="form.service_type_id = null"
                    >
                        <ArrowLeft :size="16" aria-hidden="true" />
                        {{ 'Andere Leistung wählen' }}
                    </button>
                </div>

                <WizardStep1 v-else-if="currentKey === 'service'" :form="form" :service-types="serviceTypes" @next="next" />
                <WizardAccidentStep v-else-if="currentKey === 'accident'" :form="form" @next="next" @back="back" />
                <WizardStep2 v-else-if="currentKey === 'vehicle' && !activeFields.length" :form="form" v-model:photos="photos" @next="next" @back="back" />
                <WizardDynamicStep v-else-if="currentKey === 'vehicle'" :form="form" :fields="activeFields" @next="next" @back="back" />
                <WizardStep3 v-else-if="currentKey === 'location'" :form="form" @next="next" @back="back" />
                <WizardStep4 v-else :form="form" :service-types="serviceTypes" :is-guest="isGuest" @back="back" @submit="submit" />
            </div>
        </div>
    </section>
</template>
