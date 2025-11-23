<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  berita: {
    type: Object,
    required: true,
  },
})

const formattedDate = computed(() => {
  if (!props.berita?.tanggal_publikasi) return ''
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(props.berita.tanggal_publikasi).toLocaleDateString('id-ID', options)
})
</script>

<template>
  <PublicLayout>
    <Head :title="berita.judul" />

    <section class="container mx-auto px-6 py-8">
      <Link
        :href="route('public.news.index')"
        class="text-sm text-green-700 hover:underline"
      >
        ← Kembali ke daftar berita
      </Link>

      <article class="mt-4 bg-white rounded-2xl shadow-sm overflow-hidden">
        <div v-if="berita.gambar_url" class="h-64 w-full overflow-hidden">
          <img
            :src="berita.gambar_url"
            :alt="berita.judul"
            class="w-full h-full object-cover"
          />
        </div>

        <div class="p-6 md:p-8">
          <p class="text-sm text-gray-400 mb-2">
            {{ formattedDate }}
          </p>
          <h1 class="text-3xl font-bold text-gray-800 mb-4">
            {{ berita.judul }}
          </h1>

          <p v-if="berita.deskripsi_singkat" class="text-gray-600 mb-4 italic">
            {{ berita.deskripsi_singkat }}
          </p>

          <div
            class="prose max-w-none"
            v-html="berita.konten"
          />
        </div>
      </article>
    </section>
  </PublicLayout>
</template>
