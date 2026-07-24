<script setup lang="ts">
import { errorToast, successToast } from '@/lib/swal';
import { type FlashPayload, type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage<SharedData>();
const { t } = useI18n();

function resolve(payload: FlashPayload): string {
    return typeof payload === 'string' ? payload : t(payload.key, payload.params ?? {});
}

watch(
    () => page.props.flash?.status,
    (status) => {
        if (status) successToast(resolve(status));
    },
);

watch(
    () => page.props.flash?.error,
    (error) => {
        if (error) errorToast(resolve(error));
    },
);
</script>

<template></template>
