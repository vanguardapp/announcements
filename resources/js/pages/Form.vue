<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { AppLayout, Button, Checkbox, Input, Textarea, useTranslations } from '@vanguard/ui';

const props = defineProps<{
    edit: boolean;
    announcement: { id: number; title: string; body: string } | null;
}>();

const { __ } = useTranslations();

const form = useForm({
    title: props.announcement?.title ?? '',
    body: props.announcement?.body ?? '',
    email_notification: false,
});

function submit(): void {
    if (props.edit && props.announcement) {
        form.put(route('announcements.update', props.announcement.id));

        return;
    }

    form.post(route('announcements.store'));
}

const heading = props.edit ? __('Update Announcement') : __('New Announcement');
</script>

<template>
    <AppLayout
        :title="heading"
        :heading="heading"
        :breadcrumbs="[
            { label: __('Announcements'), href: route('announcements.index') },
            { label: edit ? __('Update') : __('Create') },
        ]"
    >
        <form class="mx-auto max-w-2xl space-y-4" @submit.prevent="submit">
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <h6 class="border-b border-slate-100 px-6 py-4 font-medium">
                    {{ edit ? __('Update Announcement') : __('Create an Announcement') }}
                </h6>

                <div class="space-y-4 p-6">
                    <div>
                        <label for="title" class="mb-1 block text-slate-700">{{ __('Title') }}</label>
                        <Input
                            id="title"
                            v-model="form.title"
                            class="w-full"
                            :placeholder="__('What are you announcing?')"
                            :invalid="Boolean(form.errors.title)"
                        />
                        <small v-if="form.errors.title" class="text-red-600">
                            {{ form.errors.title }}
                        </small>
                    </div>

                    <div>
                        <label for="body" class="mb-1 block text-slate-700">{{ __('Body') }}</label>
                        <Textarea
                            id="body"
                            v-model="form.body"
                            class="w-full"
                            :rows="10"
                            :placeholder="__('Describe your announcement using markdown...')"
                            :invalid="Boolean(form.errors.body)"
                        />
                        <small v-if="form.errors.body" class="text-red-600">
                            {{ form.errors.body }}
                        </small>
                    </div>

                    <div v-if="!edit" class="flex items-start gap-2">
                        <Checkbox id="email_notification" v-model="form.email_notification" />
                        <label for="email_notification" class="cursor-pointer">
                            <span class="block text-slate-700">{{ __('E-Mail Notification') }}</span>
                            <small class="text-slate-500">
                                {{ __('Send email notification about the announcement to all users.') }}
                            </small>
                        </label>
                    </div>
                </div>
            </div>

            <Button
                type="submit"
                :label="edit ? __('Update Announcement') : __('Create Announcement')"
                :loading="form.processing"
            />
        </form>
    </AppLayout>
</template>
