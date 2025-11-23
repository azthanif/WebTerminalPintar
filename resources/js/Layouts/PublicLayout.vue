<script setup>
import PublicNavbar from '@/Components/public/PublicNavbar.vue'
import PublicFooter from '@/Components/public/PublicFooter.vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Terminal Pintar',
  },
  showNavbar: {
    type: Boolean,
    default: true,
  },
  showFooter: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-50">
    <PublicNavbar v-if="props.showNavbar" />

    <main class="flex-1">
      <slot />
    </main>

    <PublicFooter v-if="props.showFooter" />
  </div>
</template>
