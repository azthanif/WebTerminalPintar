<template>
  <Teleport to="body">
    <Transition leave-active-class="duration-200">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0" scroll-region>
        <Transition
          enter-active-class="ease-out duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="ease-in duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div v-show="show" class="fixed inset-0 transform transition-all" @click="close">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" />
          </div>
        </Transition>

        <Transition
          enter-active-class="ease-out duration-300"
          enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          enter-to-class="opacity-100 translate-y-0 sm:scale-100"
          leave-active-class="ease-in duration-200"
          leave-from-class="opacity-100 translate-y-0 sm:scale-100"
          leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
          <div
            v-if="show"
            class="mb-6 flex min-h-[calc(100vh-4rem)] items-center justify-center sm:mx-auto w-full transform transition-all sm:min-h-full"
            :class="maxWidthClass"
          >
             <div class="relative w-full overflow-hidden rounded-[2.5rem] bg-white shadow-2xl ring-1 ring-black/5" @click.stop>
                <div :class="['px-8 py-6', headerClass]">
                  <div class="flex items-start justify-between gap-4">
                    <div>
                      <h2 class="text-xl font-extrabold text-white tracking-tight">
                        {{ title }}
                      </h2>
                      <p v-if="$slots.description" :class="['mt-1 text-sm font-medium', descriptionClass]">
                        <slot name="description" />
                      </p>
                    </div>
                    <button
                      type="button"
                      class="rounded-full bg-white/20 p-1.5 text-white transition hover:bg-white/30 hover:rotate-90 active:scale-95 backdrop-blur-md"
                      @click="close"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>

                <div class="p-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                  <slot />
                </div>
             </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue'

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

watch(
  () => props.show,
  (val) => {
      if (val) {
          document.body.style.overflow = 'hidden'
      } else {
          document.body.style.overflow = 'auto'
      }
  }
)

onMounted(() => {
  document.addEventListener('keydown', closeOnEscape)
})

onUnmounted(() => {
  document.removeEventListener('keydown', closeOnEscape)
  document.body.style.overflow = 'auto'
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
    primary: 'bg-[var(--color-primary)]',
    success: 'bg-emerald-500',
    danger: 'bg-rose-500',
    warning: 'bg-amber-500',
    info: 'bg-sky-500', 
  }

  return variants[props.variant] || variants.primary
})

const descriptionClass = computed(() => {
  const colors = {
    primary: 'text-white/80',
    success: 'text-emerald-100',
    danger: 'text-rose-100',
    warning: 'text-amber-100',
    info: 'text-sky-100',
  }

  return colors[props.variant] || colors.primary
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: #94a3b8;
}
</style>
