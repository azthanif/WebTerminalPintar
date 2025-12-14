<script setup>
import { computed } from 'vue'
import { ClockIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    title: {
        type: String,
        required: false
    },
    badgeLabel: {
        type: String,
        default: 'Admin Portal'
    },
    badgeColor: {
        type: String,
        default: 'gray',
        validator: (value) => ['green', 'orange', 'blue', 'red', 'gray', 'cyan', 'rose', 'amber'].includes(value)
    }
})

const badgeClasses = computed(() => {
    const colorMap = {
        green: 'bg-green-50 text-green-700 border-green-200',
        orange: 'bg-orange-50 text-orange-600 border-orange-200',
        blue: 'bg-blue-50 text-blue-700 border-blue-200',
        red: 'bg-red-50 text-red-700 border-red-200',
        cyan: 'bg-cyan-50 text-cyan-700 border-cyan-200',
        rose: 'bg-rose-50 text-rose-700 border-rose-200',
        amber: 'bg-amber-50 text-amber-700 border-amber-200',
        gray: 'bg-gray-50 text-gray-700 border-gray-300'
    }
    return colorMap[props.badgeColor] || colorMap.gray
})
</script>

<template>
    <!-- Page Header with Pill Toolbar -->
    <section class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <!-- Left: Page Title -->
        <div>
            <slot name="title">
                <h1 v-if="title" class="text-3xl font-extrabold text-slate-800 tracking-tight">{{ title }}</h1>
            </slot>
        </div>
        
        <!-- Right: Pill Toolbar -->
        <div class="flex items-center gap-3">
            <!-- Admin Portal Badge (Pill) -->
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold" :class="badgeClasses">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                </span>
                <span>{{ badgeLabel }}</span>
            </div>

            <!-- Date Widget (Pill) -->
            <div class="flex items-center gap-3 rounded-full border border-gray-300 bg-white px-4 py-2">
                <div class="text-[var(--color-primary)]">
                    <ClockIcon class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-700">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' }) }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
