<script setup lang="ts">
/**
 * WYSIWYG editor for the legal pages, built on TipTap.
 *
 * The package is bundled through Vite like any other dependency — nothing is
 * fetched from a CDN at runtime, because the site's CSP blocks external
 * scripts.
 *
 * The toolbar deliberately offers only what the legal pages actually use:
 * section headings (h2/h3), paragraphs, bold/italic, lists and links. The
 * rendered page styles those tags itself (see LegalPage.vue), so nothing here
 * writes inline styles or classes that could fight the site's design.
 */
import Link from '@tiptap/extension-link';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { Bold, Heading2, Heading3, Italic, Link as LinkIcon, List, ListOrdered, Pilcrow, Redo2, Undo2, Unlink } from 'lucide-vue-next';
import { onBeforeUnmount, watch } from 'vue';

const props = defineProps<{ modelValue: string }>();
const emit = defineEmits<{ 'update:modelValue': [value: string] }>();

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: { levels: [2, 3] },
            link: false,
        }),
        Link.configure({ openOnClick: false, autolink: false }),
    ],
    editorProps: {
        attributes: { class: 'legal-editor-surface focus:outline-none' },
    },
    onUpdate: ({ editor: instance }) => emit('update:modelValue', instance.getHTML()),
});

// Only reset the document when the incoming value differs from what the
// editor already holds, otherwise every keystroke would round-trip and drop
// the caret to the start.
watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.getHTML()) {
            editor.value.commands.setContent(value, { emitUpdate: false });
        }
    },
);

onBeforeUnmount(() => editor.value?.destroy());

function setLink() {
    const previous = editor.value?.getAttributes('link').href ?? '';
    const url = window.prompt('Ziel-URL (leer lassen zum Entfernen):', previous);
    if (url === null) return;

    if (url === '') {
        editor.value?.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }

    editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
}

const buttonClass = (active: boolean) =>
    [
        'flex h-9 w-9 items-center justify-center rounded-card border transition',
        active ? 'border-green-500 bg-green-50 text-green-700' : 'border-transparent text-ink-500 hover:bg-sand-50 hover:text-navy-700',
    ].join(' ');
</script>

<template>
    <div class="overflow-hidden rounded-card border border-ink-300 focus-within:border-green-500">
        <div v-if="editor" class="flex flex-wrap items-center gap-1 border-b border-ink-100 bg-sand-50 px-2 py-1.5">
            <button type="button" :class="buttonClass(editor.isActive('paragraph'))" title="Absatz" @click="editor.chain().focus().setParagraph().run()">
                <Pilcrow :size="16" aria-hidden="true" />
            </button>
            <button type="button" :class="buttonClass(editor.isActive('heading', { level: 2 }))" title="Abschnitts-Überschrift" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()">
                <Heading2 :size="16" aria-hidden="true" />
            </button>
            <button type="button" :class="buttonClass(editor.isActive('heading', { level: 3 }))" title="Unter-Überschrift" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()">
                <Heading3 :size="16" aria-hidden="true" />
            </button>
            <span class="mx-1 h-5 w-px bg-ink-100" />
            <button type="button" :class="buttonClass(editor.isActive('bold'))" title="Fett" @click="editor.chain().focus().toggleBold().run()">
                <Bold :size="16" aria-hidden="true" />
            </button>
            <button type="button" :class="buttonClass(editor.isActive('italic'))" title="Kursiv" @click="editor.chain().focus().toggleItalic().run()">
                <Italic :size="16" aria-hidden="true" />
            </button>
            <span class="mx-1 h-5 w-px bg-ink-100" />
            <button type="button" :class="buttonClass(editor.isActive('bulletList'))" title="Aufzählung" @click="editor.chain().focus().toggleBulletList().run()">
                <List :size="16" aria-hidden="true" />
            </button>
            <button type="button" :class="buttonClass(editor.isActive('orderedList'))" title="Nummerierte Liste" @click="editor.chain().focus().toggleOrderedList().run()">
                <ListOrdered :size="16" aria-hidden="true" />
            </button>
            <span class="mx-1 h-5 w-px bg-ink-100" />
            <button type="button" :class="buttonClass(editor.isActive('link'))" title="Link setzen" @click="setLink">
                <LinkIcon :size="16" aria-hidden="true" />
            </button>
            <button type="button" :class="buttonClass(false)" title="Link entfernen" @click="editor.chain().focus().unsetLink().run()">
                <Unlink :size="16" aria-hidden="true" />
            </button>
            <span class="mx-1 h-5 w-px bg-ink-100" />
            <button type="button" :class="buttonClass(false)" title="Rückgängig" @click="editor.chain().focus().undo().run()">
                <Undo2 :size="16" aria-hidden="true" />
            </button>
            <button type="button" :class="buttonClass(false)" title="Wiederherstellen" @click="editor.chain().focus().redo().run()">
                <Redo2 :size="16" aria-hidden="true" />
            </button>
        </div>

        <EditorContent :editor="editor" class="legal-editor" />
    </div>
</template>

<style scoped>
.legal-editor :deep(.legal-editor-surface) {
    min-height: 22rem;
    max-height: 34rem;
    overflow-y: auto;
    padding: 1.25rem 1.5rem;
    font-size: 15px;
    line-height: 1.7;
    color: var(--color-ink-700);
}
.legal-editor :deep(h2) {
    font-family: var(--font-display);
    font-size: 20px;
    font-weight: 700;
    color: var(--color-navy-700);
    margin-top: 1.5rem;
    margin-bottom: 0.4rem;
}
.legal-editor :deep(h3) {
    font-weight: 700;
    color: var(--color-navy-700);
    margin-top: 1rem;
}
.legal-editor :deep(p) {
    margin-bottom: 0.75rem;
}
.legal-editor :deep(a) {
    color: var(--color-green-600);
    text-decoration: underline;
}
.legal-editor :deep(ul) {
    list-style: disc;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}
.legal-editor :deep(ol) {
    list-style: decimal;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
}
</style>
