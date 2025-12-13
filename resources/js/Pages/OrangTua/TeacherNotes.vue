<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import ParentLayout from '@/Layouts/ParentLayout.vue'

const props = defineProps({
  student: Object,
  filters: {
    type: Object,
    default: () => ({}),
  },
  notes: {
    type: Object,
    default: () => ({ data: [] }),
  },
})

// State untuk filter
const search = ref(props.filters.search ?? '')

defineOptions({
  layout: ParentLayout,
})

// Logika Debounce untuk pencarian otomatis saat mengetik
let debounceId
watch(search, () => {
  clearTimeout(debounceId)
  debounceId = setTimeout(() => {
    router.get(
      route('orang-tua.notes.index'),
      {
        search: search.value || undefined,
      },
      {
        replace: true, // Jangan buat history browser baru
        preserveState: true, // Jaga state komponen
        preserveScroll: true, // Jangan scroll ke atas
      }
    )
  }, 300)
})

const pagination = computed(() => props.notes?.meta ?? null)

const goToPage = (url) => {
  if (!url) return
  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
  })
}

const formatNoteTitle = (title) => {
  if (!title) {
    return '-'
  }

  const [primary, secondary] = title.split('·').map((part) => part.trim())

  if (!secondary) {
    return primary
  }

  const uniqueSubjects = Array.from(
    new Set(
      secondary
        .split('-')
        .map((part) => part.trim())
        .filter(Boolean)
    )
  )

  return uniqueSubjects.length
    ? `${primary} · ${uniqueSubjects.join(' - ')}`
    : primary
}
</script>

<template>
  <div class="space-y-8">
    <Head title="Catatan Guru" />

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900">Catatan Guru Untuk {{ student.name }}</h2>
          <p class="text-sm text-slate-500">Dokumentasi perkembangan dan rekomendasi pembelajaran</p>
        </div>
        
        <div class="flex flex-col gap-3 sm:flex-row w-full md:w-auto">
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Cari kata kunci..."
              class="w-full rounded-2xl border border-slate-200 pl-10 pr-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-200 transition"
            />
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          
        </div>
      </div>
    </section>

    <section class="space-y-5">
      <article
        v-for="note in (notes.data ?? [])"
        :key="note.id"
        class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm hover:border-emerald-100 transition"
      >
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ note.recorded_at_readable }}</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ formatNoteTitle(note.title) }}</h3>
          </div>
        </div>
        
        <p class="mt-4 text-sm leading-relaxed text-slate-600 whitespace-pre-line">{{ note.note }}</p>
        
        <div class="mt-4 flex items-center gap-2">
            <div class="h-6 w-6 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-600">
                {{ (note.teacher?.name ?? 'G').charAt(0) }}
            </div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">{{ note.teacher?.name ?? 'Guru' }}</p>
        </div>
      </article>

      <div v-if="!(notes.data ?? []).length" class="flex flex-col items-center justify-center py-12 text-slate-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm">Tidak ada catatan yang ditemukan.</p>
        <button 
          v-if="search"
          @click="search = ''"
            class="mt-2 text-sm text-emerald-600 hover:underline"
        >
            Reset filter
        </button>
      </div>
    </section>

    <nav v-if="pagination && pagination.total > pagination.per_page" class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-4 text-sm">
      <div class="text-slate-500">
        Menampilkan {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} catatan
      </div>
      <div class="space-x-2">
        <button
          class="rounded-full border border-slate-200 px-4 py-1 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
          :disabled="!props.notes?.links?.prev"
          @click="goToPage(props.notes?.links?.prev)"
        >
          Sebelumnya
        </button>
        <button
          class="rounded-full border border-slate-200 px-4 py-1 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
          :disabled="!props.notes?.links?.next"
          @click="goToPage(props.notes?.links?.next)"
        >
          Selanjutnya
        </button>
      </div>
    </nav>
  </div>
</template>