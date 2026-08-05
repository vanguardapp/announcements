<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Menu, useTranslations } from '@vanguard/ui';
import { ref } from 'vue';

const props = defineProps<{
    announcements: Array<{
        id: number;
        title: string;
        url: string;
        creator: string;
        avatar: string;
        createdAt: string;
    }>;
    /**
     * Whether the newest announcement postdates the viewer's last read, which
     * is what puts the dot on the bell.
     */
    unread: boolean;
    listUrl: string;
    readUrl: string;
}>();

const { __ } = useTranslations();

const seen = ref(!props.unread);

/** Opening the dropdown marks everything as read, as the Blade version did. */
function markRead(open: boolean): void {
    if (!open || seen.value) {
        return;
    }

    seen.value = true;

    router.post(props.readUrl, {}, { preserveScroll: true, preserveState: true, only: [] });
}
</script>

<template>
    <Menu @update:open="markRead">
        <template #trigger>
            <button
                type="button"
                class="relative cursor-pointer px-3 text-slate-500 hover:text-slate-700"
                :aria-label="__('Announcements')"
            >
                <span
                    v-if="!seen"
                    class="absolute top-0 right-2 size-2 rounded-full bg-red-500"
                    aria-hidden="true"
                />
                <i class="fas fa-bullhorn" aria-hidden="true" />
            </button>
        </template>

        <div class="max-h-[350px] w-[380px] overflow-y-auto">
            <div class="bg-white p-6 text-center">
                <h5 class="text-slate-500">{{ __('Announcements') }}</h5>

                <a
                    v-if="announcements.length"
                    :href="listUrl"
                    class="text-primary-600 hover:underline"
                >
                    {{ __('View All') }}
                </a>
            </div>

            <div class="bg-lighter">
                <a
                    v-for="announcement in announcements"
                    :key="announcement.id"
                    :href="announcement.url"
                    class="flex items-start gap-3 px-6 py-4 hover:bg-white"
                >
                    <img
                        :src="announcement.avatar"
                        :alt="announcement.creator"
                        width="50"
                        height="50"
                        class="size-[50px] shrink-0 rounded-full object-cover"
                    />

                    <div class="w-full">
                        <div class="flex items-start justify-between gap-2">
                            <span class="font-semibold">{{ announcement.creator }}</span>
                            <span class="shrink-0 text-slate-500">{{ announcement.createdAt }}</span>
                        </div>

                        <span class="text-primary-600">{{ announcement.title }}</span>
                    </div>
                </a>

                <p v-if="!announcements.length" class="p-6 text-center text-slate-500">
                    {{ __('No new announcements at the moment.') }}
                </p>
            </div>
        </div>
    </Menu>
</template>
