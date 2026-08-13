<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { FileText, List } from 'lucide-vue-next';
import { nextTick, onMounted, ref, useTemplateRef } from 'vue';

/**
 * `body` carries admin-authored HTML from the legal-content screen. It is
 * sanitised server-side before it is stored (see RichTextSanitizer) and can
 * only be written by an authenticated administrator. Pages that pass their
 * markup through the default slot instead leave it unset.
 */
defineProps<{ title: string; updated?: string; body?: string }>();

const proseRef = useTemplateRef('proseRef');
const toc = ref<Array<{ id: string; text: string }>>([]);
const activeId = ref('');

function slugify(text: string, i: number): string {
    const base = text
        .toLowerCase()
        .replace(/[§]/g, '')
        .trim()
        .replace(/[^a-z0-9äöüß\s-]/gi, '')
        .replace(/\s+/g, '-');
    return base || `section-${i}`;
}

onMounted(async () => {
    await nextTick();
    if (!proseRef.value) return;
    const headings = Array.from(proseRef.value.querySelectorAll('h2'));
    toc.value = headings.map((h, i) => {
        const id = slugify(h.textContent ?? '', i);
        h.id = id;
        return { id, text: h.textContent ?? '' };
    });
    if (toc.value.length) activeId.value = toc.value[0].id;

    if (typeof IntersectionObserver === 'undefined') return;
    const observer = new IntersectionObserver(
        (entries) => {
            const visible = entries.find((e) => e.isIntersecting);
            if (visible) activeId.value = (visible.target as HTMLElement).id;
        },
        { rootMargin: '-15% 0px -70% 0px' },
    );
    headings.forEach((h) => observer.observe(h));
});
</script>

<template>
    <Head>
        <title>{{ title }}</title>
    </Head>

    <section class="bg-white px-4 pt-4 pb-8 sm:px-6 lg:pb-14">
        <div class="relative mx-auto max-w-5xl overflow-hidden rounded-panel bg-navy-900 px-6 py-14 sm:px-12 lg:py-16">
            <div class="pointer-events-none absolute -top-20 -right-20 h-64 w-64 rounded-full bg-green-500/20 blur-3xl" aria-hidden="true" />
            <div class="pointer-events-none absolute -bottom-24 -left-16 h-64 w-64 rounded-full bg-navy-500/30 blur-3xl" aria-hidden="true" />
            <div class="relative">
                <span class="flex h-12 w-12 items-center justify-center rounded-card bg-white/10 text-green-400">
                    <FileText :size="22" aria-hidden="true" />
                </span>
                <h1 class="mt-6 font-display text-3xl font-extrabold text-white lg:text-4xl">{{ title }}</h1>
                <p v-if="updated" class="mt-3 text-sm text-navy-100">Stand: {{ updated }}</p>
            </div>
        </div>
    </section>

    <section class="bg-white px-4 pb-20 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-5xl gap-10 lg:grid-cols-[220px_1fr]">
            <!-- Sticky table of contents -->
            <aside v-if="toc.length" class="hidden lg:block">
                <div class="sticky top-24">
                    <p class="text-eyebrow mb-3 flex items-center gap-1.5 text-ink-500"><List :size="13" aria-hidden="true" /> Inhalt</p>
                    <nav class="space-y-1 border-l border-ink-100 pl-4">
                        <a
                            v-for="item in toc"
                            :key="item.id"
                            :href="`#${item.id}`"
                            class="-ml-px block border-l-2 py-1 pl-4 text-sm transition"
                            :class="activeId === item.id ? 'border-green-500 font-semibold text-navy-700' : 'border-transparent text-ink-500 hover:text-navy-700'"
                        >
                            {{ item.text }}
                        </a>
                    </nav>
                </div>
            </aside>

            <div v-if="body" ref="proseRef" class="legal-prose max-w-3xl space-y-5 text-[15px] leading-relaxed text-ink-700" v-html="body" />
            <div v-else ref="proseRef" class="legal-prose max-w-3xl space-y-5 text-[15px] leading-relaxed text-ink-700">
                <slot />
            </div>
        </div>
    </section>
</template>

<style scoped>
.legal-prose :deep(h2) {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    color: var(--color-navy-700);
    margin-top: 2.75rem;
    padding-top: 1.5rem;
    margin-bottom: 0.5rem;
    border-top: 1px solid var(--color-ink-100);
    scroll-margin-top: 6rem;
}
.legal-prose :deep(h2:first-child) {
    margin-top: 0;
    padding-top: 0;
    border-top: none;
}
.legal-prose :deep(h3) {
    font-weight: 700;
    color: var(--color-navy-700);
    margin-top: 1.25rem;
}
.legal-prose :deep(a) {
    color: var(--color-green-600);
    text-decoration: underline;
}
.legal-prose :deep(ul) {
    list-style: disc;
    padding-left: 1.5rem;
}
</style>
