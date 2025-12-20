<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Form from './Form.vue'

defineOptions({
    layout: AdminLayout,
})

const props = defineProps({
    book: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    statuses: {
        type: Object,
        default: () => ({}),
    },
})
</script>

<template>
    <Form v-bind="props" />
</template>
