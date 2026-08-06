<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    AppLayout,
    Button,
    Pagination,
    Tooltip,
    useConfirmDelete,
    usePermissions,
    useTranslations,
} from '@vanguard/ui';

interface AnnouncementRow {
    id: number;
    title: string;
    creator: string;
    creatorAvatar: string;
    creatorUrl: string;
    createdAt: string;
    showUrl: string;
    editUrl: string;
    deleteUrl: string;
}

defineProps<{
    announcements: {
        data: AnnouncementRow[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

const { __ } = useTranslations();
const { can } = usePermissions();
const { confirmDelete } = useConfirmDelete();

function remove(announcement: AnnouncementRow): void {
    confirmDelete({
        url: announcement.deleteUrl,
        message: __('Are you sure that you want to delete this announcement?'),
    });
}
</script>

<template>
    <AppLayout
        :title="__('Announcements')"
        :heading="__('Announcements')"
        :breadcrumbs="[{ label: __('Announcements') }]"
    >
        <div class="mb-4 flex justify-end">
            <Link :href="route('announcements.create')">
                <Button :label="__('Create Announcement')" icon="fas fa-plus" rounded />
            </Link>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-2xl text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="w-14 px-4 py-3" />
                            <th class="px-4 py-3 font-medium">{{ __('Creator') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Title') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Created At') }}</th>
                            <th class="px-4 py-3 text-center font-medium">{{ __('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="!announcements.data.length">
                            <td colspan="5" class="px-4 py-3">
                                <em class="text-slate-500">{{ __('No announcements found.') }}</em>
                            </td>
                        </tr>

                        <tr
                            v-for="announcement in announcements.data"
                            :key="announcement.id"
                            class="border-b border-slate-50 odd:bg-slate-50/60"
                        >
                            <td class="px-4 py-3">
                                <Link :href="announcement.creatorUrl">
                                    <img
                                        :src="announcement.creatorAvatar"
                                        :alt="announcement.creator"
                                        width="40"
                                        height="40"
                                        class="size-10 rounded-full object-cover"
                                    />
                                </Link>
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    v-if="can('users.manage')"
                                    :href="announcement.creatorUrl"
                                    class="text-primary-600 hover:underline"
                                >
                                    {{ announcement.creator }}
                                </Link>
                                <span v-else>{{ announcement.creator }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="announcement.showUrl"
                                    class="text-primary-600 hover:underline"
                                >
                                    {{ announcement.title }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">{{ announcement.createdAt }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <Tooltip :text="__('Edit Announcement')">
                                    <Link :href="announcement.editUrl">
                                        <Button
                                            icon="fas fa-edit"
                                            text
                                            rounded
                                            severity="secondary"
                                            :aria-label="__('Edit Announcement')"
                                        />
                                    </Link>
                                </Tooltip>

                                <Tooltip :text="__('Delete Announcement')">
                                    <Button
                                        icon="fas fa-trash"
                                        text
                                        rounded
                                        severity="secondary"
                                        :aria-label="__('Delete Announcement')"
                                        @click="remove(announcement)"
                                    />
                                </Tooltip>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="announcements.links" />
        </div>
    </AppLayout>
</template>
