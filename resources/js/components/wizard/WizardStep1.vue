<script setup lang="ts">
import WizardNav from '@/components/wizard/WizardNav.vue';
import { CheckCircle2 } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    form: { service_type_id: number | null };
    serviceTypes: Array<{ id: number; name: string; slug: string; description: string }>;
}>();
const emit = defineEmits<{ next: [] }>();

const error = ref('');

function proceed() {
    if (!props.form.service_type_id) {
        error.value = 'Bitte wählen Sie eine Leistung aus.';
        return;
    }
    error.value = '';
    emit('next');
}
</script>

<template>
    <div>
        <h2 class="font-display text-2xl font-bold text-navy-700">Welches Gutachten benötigen Sie?</h2>
        <p class="mt-1 text-ink-500">Wählen Sie die passende Leistung aus.</p>

        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <button
                v-for="type in serviceTypes"
                :key="type.id"
                type="button"
                class="relative rounded-card border-2 p-5 text-left transition"
                :class="form.service_type_id === type.id ? 'border-green-500 bg-green-50' : 'border-ink-100 hover:border-ink-300'"
                @click="form.service_type_id = type.id"
            >
                <CheckCircle2
                    v-if="form.service_type_id === type.id"
                    class="absolute top-4 right-4 text-green-500"
                    :size="20"
                    aria-hidden="true"
                />
                <p class="pr-6 font-display font-bold text-navy-700">{{ type.name }}</p>
                <p class="mt-1.5 text-sm leading-relaxed text-ink-500">{{ type.description }}</p>
            </button>
        </div>
        <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>

        <WizardNav :show-back="false" @next="proceed" />
    </div>
</template>
