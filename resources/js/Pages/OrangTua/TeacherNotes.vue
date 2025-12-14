<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import ParentLayout from '@/Layouts/ParentLayout.vue'
import {
  MagnifyingGlassIcon,
  BookmarkSquareIcon,
  ChatBubbleBottomCenterTextIcon,
  UserIcon,
  CalendarIcon,
  TagIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'

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

    <!-- Premium Header -->
    <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
      <div class="space-y-2">
         <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600 border border-emerald-100">
            <ChatBubbleBottomCenterTextIcon class="h-3 w-3" />
            <span>Laporan Perkembangan</span>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
             Catatan <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">Guru</span>
        </h1>
        <p class="text-slate-500 font-medium text-lg max-w-2xl">
            Rekomendasi dan hasil evaluasi pembelajaran untuk {{ student.name }}.
        </p>
      </div>

       <div class="flex items-center gap-4">
            <div class="relative w-full md:w-72">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-500">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </div>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Cari kata kunci..."
                    class="w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 py-3 text-sm font-bold text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all shadow-sm"
                />
            </div>
       </div>
    </section>

    <!-- Notes Grid -->
    <section class="grid grid-cols-1 gap-6">
      <article
        v-for="note in (notes.data ?? [])"
        :key="note.id"
        class="group relative flex flex-col gap-6 rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm transition-all hover:shadow-lg hover:border-emerald-200 hover:-translate-y-1"
      >
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
             <div class="space-y-3 flex-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                        <CalendarIcon class="h-3.5 w-3.5" />
                        {{ note.recorded_at_readable }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                        <TagIcon class="h-3.5 w-3.5" />
                        Catatan Akademik
                    </span>
                </div>
                <h3 class="text-2xl font-extrabold text-slate-900 group-hover:text-emerald-700 transition-colors">
                    {{ formatNoteTitle(note.title) }}
                </h3>
             </div>
             
             <div class="flex items-center gap-3 bg-slate-50 rounded-2xl p-3 border border-slate-100">
                 <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center text-emerald-600 shadow-sm border border-slate-100">
                     <UserIcon class="h-5 w-5" />
                 </div>
                 <div>
                     <p class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Ditulis Oleh</p>
                     <p class="text-sm font-bold text-slate-800">{{ note.teacher?.name ?? 'Guru Pengampu' }}</p>
                 </div>
             </div>
        </div>
        
        <div class="relative">
             <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-emerald-400 to-teal-200 rounded-full"></div>
             <p class="pl-6 text-base leading-relaxed text-slate-600 whitespace-pre-line font-medium">
                {{ note.note }}
             </p>
        </div>
      </article>

      <!-- Empty State -->
      <div v-if="!(notes.data ?? []).length" class="flex flex-col items-center justify-center py-20 text-center rounded-[2.5rem] border border-dashed border-slate-200 bg-slate-50/50">
        <div class="h-20 w-20 bg-white rounded-full flex items-center justify-center mb-6 shadow-sm border border-slate-100">
             <BookmarkSquareIcon class="h-10 w-10 text-slate-300" />
        </div>
        <h4 class="text-xl font-bold text-slate-800">Tidak ada catatan ditemukan</h4>
        <p class="text-slate-500 mt-2 max-w-md mx-auto">Kami tidak menemukan catatan guru yang sesuai dengan pencarian Anda.</p>
        <button 
          v-if="search"
          @click="search = ''"
            class="mt-6 text-sm font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-6 py-2.5 rounded-xl hover:bg-emerald-100 transition-colors"
        >
            Reset Pencarian
        </button>
      </div>
    </section>

    <!-- Pagination -->
    <nav v-if="pagination && pagination.total > pagination.per_page" class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="text-sm font-bold text-slate-500">
        Menampilkan <span class="text-slate-800">{{ pagination.from }}-{{ pagination.to }}</span> dari <span class="text-slate-800">{{ pagination.total }}</span> catatan
      </div>
      <div class="flex gap-2">
        <button
          class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-slate-600"
          :disabled="!props.notes?.links?.prev"
          @click="goToPage(props.notes?.links?.prev)"
        >
          <ChevronLeftIcon class="h-4 w-4" />
          Sebelumnya
        </button>
        <button
          class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-slate-600"
          :disabled="!props.notes?.links?.next"
          @click="goToPage(props.notes?.links?.next)"
        >
          Selanjutnya
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </nav>
  </div>
</template>