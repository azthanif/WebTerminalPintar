<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import NewsCard from '@/Components/public/NewsCard.vue'
import Pagination from '@/Components/public/Pagination.vue'
import { Head, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  berita: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const items = computed(() => props.berita.data ?? [])
const currentPage = ref(props.berita.current_page ?? 1)
const lastPage = ref(props.berita.last_page ?? 1)

const goToPage = (page) => {
  router.get(
    route('public.news.index'),
    { page, ...props.filters },
    { preserveScroll: true, preserveState: true },
  )
}

</script>

<template>
  <PublicLayout>
    <Head title="Berita & Dokumentasi" />

    <section class="container mx-auto px-6 py-10">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">
            Berita & Dokumentasi
          </h1>
          <p class="text-gray-600 mt-2 text-sm">
            Lihat informasi kegiatan terbaru di Terminal Pintar.
          </p>
        </div>
      </div>

      <div v-if="items.length === 0" class="text-gray-500 text-sm">
        Belum ada berita yang dipublikasikan.
      </div>

      <div v-else class="grid md:grid-cols-3 gap-6 mb-8">
        <NewsCard
          v-for="item in items"
          :key="item.id"
          :berita="item"
        />
      </div>

      <div v-if="lastPage > 1" class="flex justify-center">
        <Pagination
          :current-page="currentPage"
          :last-page="lastPage"
          @change="goToPage"
        />
      </div>
    </section>
  </PublicLayout>
</template>
