<template>
  <div
    ref="cardRef"
    class="group relative rounded-2xl bg-white p-6 border border-slate-200 shadow-sm transition-all duration-300 cursor-pointer"
    :class="[
      isVisible && 'opacity-100 translate-y-0',
      !isVisible && 'opacity-0 translate-y-10',
      'hover:shadow-lg hover:-translate-y-1 hover:border-[#84994F]/30'
    ]"
  >
    <!-- Icon -->
    <div class="mb-4 inline-flex items-center justify-center rounded-xl bg-gradient-to-br from-[#84994F]/20 to-[#6B7D3F]/20 border border-[#84994F]/20 p-3 transition-transform duration-300 group-hover:scale-110 group-hover:shadow-md">
      <component :is="icon" class="h-8 w-8 text-[#84994F]" />
    </div>
    
    <!-- Title -->
    <h3 class="text-xl font-bold text-slate-900 group-hover:text-[#84994F] transition-colors duration-300 leading-tight">
      {{ title }}
    </h3>
    
    <!-- Description -->
    <p class="mt-2 text-slate-600 leading-relaxed">
      {{ description }}
    </p>
    
    <!-- Optional link -->
    <a
      v-if="link"
      :href="link"
      class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-[#84994F] opacity-0 group-hover:opacity-100 transition-opacity duration-300"
      @click.prevent="$emit('link-click', link)"
    >
      {{ linkText }}
      <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </a>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useScrollAnimation } from '@/Composables/useAnimations'

const props = defineProps({
  icon: {
    type: Object,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  link: String,
  linkText: {
    type: String,
    default: 'Learn more'
  },
  animationDelay: {
    type: Number,
    default: 0
  }
})

defineEmits(['link-click'])

const { target: cardRef, isVisible } = useScrollAnimation({
  threshold: 0.2,
  once: true
})
</script>
