<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  berita: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <article class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden flex flex-col">
    <div v-if="berita.gambar_url" class="h-40 w-full overflow-hidden">
      <img
        :src="berita.gambar_url"
        :alt="berita.judul"
        class="w-full h-full object-cover"
      />
    </div>
    <div class="p-4 flex-1 flex flex-col">
      <p class="text-xs text-gray-400 mb-2">
        {{ new Date(berita.tanggal_publikasi).toLocaleDateString('id-ID') }}
      </p>
      <h3 class="font-semibold text-gray-800 mb-2 line-clamp-2">
        {{ berita.judul }}
      </h3>
      <p class="text-sm text-gray-600 flex-1 line-clamp-3">
        {{ berita.deskripsi ?? berita.deskripsi_singkat }}
      </p>
      <div class="mt-3">
        <Link
          :href="route('public.news.show', berita.slug)"
          class="text-sm text-green-700 font-medium hover:underline"
        >
          Baca Selengkapnya →
        </Link>
      </div>
    </div>
  </article>
</template>
