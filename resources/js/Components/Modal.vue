<template>
  <Teleport to="body">
    <Transition leave-active-class="duration-200">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" scroll-region>
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="close"></div>

        <div class="relative z-10 mx-auto flex min-h-full items-start justify-center py-8">
          <div
            class="mb-6 flex max-h-[calc(100vh-4rem)] w-full flex-col overflow-hidden rounded-xl bg-white shadow-2xl transition-all sm:mx-auto"
            :class="maxWidthClass"
            :style="{ maxHeight: 'calc(100vh - 4rem)' }"
            @click.stop
          >
            <div :class="['px-6 py-4', headerClass]">
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h2 class="text-xl font-bold text-white">
                    {{ title }}
                  </h2>
                  <p v-if="$slots.description" :class="['mt-1 text-sm', descriptionClass]">
                    <slot name="description" />
                  </p>
                </div>
                <button
                  type="button"
                  class="rounded-full bg-white/10 p-1 text-white transition hover:bg-white/20"
                  @click="close"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="p-6 overflow-y-auto" :style="{ maxHeight: 'calc(100vh - 12rem)' }">
              <slot />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  maxWidth: {
    type: String,
    default: '2xl',
  },
  variant: {
    type: String,
    default: 'primary',
  },
})

const emit = defineEmits(['close'])

const close = () => emit('close')

const closeOnEscape = (event) => {
  if (event.key === 'Escape' && props.show) {
    close()
  }
}

onMounted(() => {
  document.addEventListener('keydown', closeOnEscape)
})

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape)
})

const maxWidthClass = computed(() => {
  const widths = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
  }

  return widths[props.maxWidth] || widths['2xl']
})

const headerClass = computed(() => {
  const variants = {
    primary: 'bg-[#78AE4E]',
    success: 'bg-[#78AE4E]',
    danger: 'bg-red-600',
    warning: 'bg-amber-500',
    info: 'bg-blue-600',
  }

  return variants[props.variant] || variants.primary
})

const descriptionClass = computed(() => {
  const colors = {
    primary: 'text-green-100',
    success: 'text-green-100',
    danger: 'text-red-100',
    warning: 'text-yellow-100',
    info: 'text-blue-100',
  }

  return colors[props.variant] || colors.primary
})
</script>
