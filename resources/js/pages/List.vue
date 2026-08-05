<script setup lang="ts">
import { AppLayout, Pagination, useTranslations } from '@vanguard/ui';
import AnnouncementCard from '../components/AnnouncementCard.vue';

defineProps<{
    announcements: {
        data: Array<{
            id: number;
            title: string;
            parsedBody: string;
            creator: string;
            avatar: string;
            shortDate: string;
            editUrl: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const { __ } = useTranslations();
</script>

<template>
    <AppLayout
        :title="__('Announcements')"
        :heading="__('Announcements')"
        :breadcrumbs="[{ label: __('Announcements') }]"
    >
        <div class="mx-auto max-w-2xl space-y-4">
            <AnnouncementCard
                v-for="announcement in announcements.data"
                :key="announcement.id"
                :announcement="announcement"
            />

            <div
                v-if="!announcements.data.length"
                class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
            >
                <p class="text-center text-lg">
                    {{ __('No new announcements at the moment.') }}
                </p>
            </div>

            <Pagination :links="announcements.links" />
        </div>
    </AppLayout>
</template>
