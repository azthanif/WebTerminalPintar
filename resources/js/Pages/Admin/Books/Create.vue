<script setup>
import Form from './Form.vue'

const props = defineProps({
    book: {
        type: Object,
        default: null,
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
