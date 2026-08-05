<script setup lang="ts">
import { AppLayout, useTranslations } from '@vanguard/ui';
import AnnouncementCard from '../components/AnnouncementCard.vue';

const props = defineProps<{
    announcement: {
        id: number;
        title: string;
        parsedBody: string;
        creator: string;
        avatar: string;
        shortDate: string;
        editUrl: string;
    };
}>();

const { __ } = useTranslations();
</script>

<template>
    <AppLayout
        :title="`${__('Announcement')}:${props.announcement.title}`"
        :heading="__('Announcement Details')"
        :breadcrumbs="[
            { label: __('Announcements'), href: route('announcements.list') },
            { label: __('Announcement Details') },
        ]"
    >
        <div class="mx-auto max-w-2xl">
            <AnnouncementCard :announcement="announcement" />
        </div>
    </AppLayout>
</template>
