<script setup>
const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
})

const emit = defineEmits(['change'])

const pages = computed(() => {
  const max = 5
  const pages = []
  let start = Math.max(1, props.currentPage - Math.floor(max / 2))
  let end = Math.min(props.lastPage, start + max - 1)

  if (end - start + 1 < max && props.lastPage >= max) {
    start = Math.max(1, end - max + 1)
  }

  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const go = (page) => {
  if (page >= 1 && page <= props.lastPage) {
    emit('change', page)
  }
}
</script>

<template>
  <div class="flex items-center gap-2">
    <button
      class="px-3 py-1 border rounded text-sm disabled:opacity-50"
      :disabled="currentPage === 1"
      @click="go(currentPage - 1)"
    >
      ← Sebelumnya
    </button>
    <button
      v-for="page in pages"
      :key="page"
      @click="go(page)"
      class="px-2 py-1 border rounded text-sm"
      :class="page === currentPage ? 'bg-green-500 text-white border-green-500' : 'hover:bg-gray-50'"
    >
      {{ page }}
    </button>
    <button
      class="px-3 py-1 border rounded text-sm disabled:opacity-50"
      :disabled="currentPage === lastPage"
      @click="go(currentPage + 1)"
    >
      Selanjutnya →
    </button>
  </div>
</template>
