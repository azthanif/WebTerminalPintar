<script setup>
import Form from './Form.vue'

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
