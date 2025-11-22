<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  news: {
    type: Object,
    default: null,
  },
})

const isEdit = computed(() => !!props.news)

const form = useForm({
  judul: props.news?.judul ?? '',
  konten: props.news?.konten ?? '',
  event_date: props.news?.event_date ?? '',
  type: props.news?.type ?? 'news',
})

const submit = () => {
  if (isEdit.value) {
    form.put(route('admin.berita.update', props.news.id))
  } else {
    form.post(route('admin.berita.store'))
  }
}
</script>

<template>
  <Head :title="isEdit ? 'Edit Berita' : 'Tambah Berita'" />

  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div
      class="w-full max-w-2xl bg-white rounded-xl shadow-md overflow-hidden border border-gray-200"
    >
      <!-- Header -->
      <div class="bg-green-600 px-5 py-3 flex items-center justify-between">
        <div>
          <h1 class="text-sm font-semibold text-white">
            {{ isEdit ? 'Edit Berita' : 'Tambah Berita Baru' }}
          </h1>
          <p class="text-[11px] text-white/80">
            Isi judul, konten, dan tanggal kegiatan untuk berita ini.
          </p>
        </div>
        <Link
          :href="route('admin.berita.index')"
          class="text-white/80 text-lg leading-none hover:text-white"
        >
          ×
        </Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="px-5 py-4 space-y-4">
        <!-- Judul -->
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">
            Judul
          </label>
          <input
            v-model="form.judul"
            type="text"
            class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
          />
          <div v-if="form.errors.judul" class="text-[11px] text-red-600 mt-1">
            {{ form.errors.judul }}
          </div>
        </div>

        <!-- Tipe & Tanggal -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
              Tipe Konten
            </label>
            <select
              v-model="form.type"
              class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
            >
              <option value="news">Berita</option>
              <option value="activity">Kegiatan</option>
              <option value="gallery">Galeri</option>
            </select>
            <div v-if="form.errors.type" class="text-[11px] text-red-600 mt-1">
              {{ form.errors.type }}
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
              Tanggal Kegiatan (opsional)
            </label>
            <input
              v-model="form.event_date"
              type="date"
              class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
            />
            <div
              v-if="form.errors.event_date"
              class="text-[11px] text-red-600 mt-1"
            >
              {{ form.errors.event_date }}
            </div>
          </div>
        </div>

        <!-- Konten -->
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">
            Konten Berita
          </label>
          <textarea
            v-model="form.konten"
            rows="8"
            class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
          />
          <div v-if="form.errors.konten" class="text-[11px] text-red-600 mt-1">
            {{ form.errors.konten }}
          </div>
        </div>

        <!-- Tombol -->
        <div class="pt-2 flex justify-end gap-3">
          <Link
            :href="route('admin.berita.index')"
            class="px-4 py-1.5 rounded-full border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50"
          >
            Batal
          </Link>
          <button
            type="submit"
            class="px-5 py-1.5 rounded-full bg-green-600 text-xs font-semibold text-white hover:bg-green-700 disabled:opacity-50"
            :disabled="form.processing"
          >
            {{ isEdit ? 'Simpan Perubahan' : 'Simpan Berita' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
