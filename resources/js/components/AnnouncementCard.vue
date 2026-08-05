<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button, usePermissions, useTranslations } from '@vanguard/ui';

defineProps<{
    announcement: {
        id: number;
        title: string;
        /** Markdown rendered to HTML server side by the model accessor. */
        parsedBody: string;
        creator: string;
        avatar: string;
        shortDate: string;
        editUrl: string;
    };
}>();

const { __ } = useTranslations();
const { can } = usePermissions();
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
        <div class="p-6">
            <h4 class="mb-3 text-xl font-medium text-slate-800">{{ announcement.title }}</h4>

            <!-- eslint-disable-next-line vue/no-v-html -- markdown rendered server side -->
            <div class="prose-vanguard" v-html="announcement.parsedBody" />
        </div>

        <div class="bg-lighter flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <img
                    :src="announcement.avatar"
                    :alt="announcement.creator"
                    width="40"
                    height="40"
                    class="size-10 rounded-full object-cover"
                />

                <div>
                    <div class="leading-tight">{{ announcement.creator }}</div>
                    <div class="text-slate-500">{{ announcement.shortDate }}</div>
                </div>
            </div>

            <Link v-if="can('announcements.manage')" :href="announcement.editUrl">
                <Button :label="__('Edit')" severity="secondary" size="small" />
            </Link>
        </div>
    </div>
</template>

<style>
/*
 * Announcement bodies are user authored markdown, so the elements it produces
 * cannot carry utility classes. Scoped to this class rather than applied
 * globally, which would leak into the host application.
 */
.prose-vanguard :is(h1, h2, h3, h4, h5, h6) {
    font-weight: 600;
    margin-block: 0.75em 0.4em;
}
.prose-vanguard p {
    margin-block: 0.5em;
}
.prose-vanguard a {
    color: var(--color-primary-600);
    text-decoration: underline;
}
.prose-vanguard ul,
.prose-vanguard ol {
    margin-block: 0.5em;
    padding-inline-start: 1.5em;
    list-style: revert;
}
.prose-vanguard code {
    background: var(--color-slate-100);
    border-radius: 0.25rem;
    padding: 0.1em 0.3em;
}
.prose-vanguard pre {
    background: var(--color-slate-100);
    border-radius: 0.375rem;
    overflow-x: auto;
    padding: 0.75rem;
}
.prose-vanguard table {
    border-collapse: collapse;
    width: 100%;
}
.prose-vanguard :is(th, td) {
    border: 1px solid var(--color-slate-200);
    padding: 0.375rem 0.5rem;
}
.prose-vanguard blockquote {
    border-inline-start: 3px solid var(--color-slate-200);
    color: var(--color-slate-500);
    padding-inline-start: 0.75rem;
}
</style>
