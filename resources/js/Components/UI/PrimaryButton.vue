<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <!-- Loading spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    
    <!-- Icon slot -->
    <component v-if="icon" :is="icon" :class="iconClasses" />
    
    <!-- Button text -->
    <span><slot /></span>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'button'
  },
  variant: {
    type: String,
    default: 'primary', // primary, secondary, tertiary, ghost
    validator: (val) => ['primary', 'secondary', 'tertiary', 'ghost'].includes(val)
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (val) => ['sm', 'md', 'lg'].includes(val)
  },
  disabled: Boolean,
  loading: Boolean,
  fullWidth: Boolean,
  icon: Object, // Heroicon component
})

const emit = defineEmits(['click'])

const buttonClasses = computed(() => {
  const base = 'inline-flex items-center justify-center font-semibold rounded-lg transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95'
  
  const variants = {
    primary: 'bg-[#84994F] text-white hover:bg-[#6B7D3F] focus:ring-[#84994F]/30 shadow-md hover:shadow-lg hover:-translate-y-0.5',
    secondary: 'bg-white text-[#84994F] border-2 border-[#84994F] hover:bg-[#84994F]/10 focus:ring-[#84994F]/30 shadow-sm hover:shadow-md',
    tertiary: 'bg-transparent text-[#84994F] hover:bg-[#84994F]/10 focus:ring-[#84994F]/20',
    ghost: 'bg-transparent text-slate-600 hover:bg-slate-100 focus:ring-slate-300'
  }
  
  const sizes = {
    sm: 'px-4 py-2 text-sm gap-1.5',
    md: 'px-6 py-2.5 text-base gap-2',
    lg: 'px-8 py-3 text-lg gap-2.5'
  }
  
  const width = props.fullWidth ? 'w-full' : ''
  
  return [base, variants[props.variant], sizes[props.size], width].filter(Boolean).join(' ')
})

const iconClasses = computed(() => {
  const sizes = {
    sm: 'h-4 w-4',
    md: 'h-5 w-5',
    lg: 'h-6 w-6'
  }
  
  return sizes[props.size]
})

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>
