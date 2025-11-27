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
  categoryOptions: {
    type: Array,
    default: () => ['behavior', 'academic', 'communication', 'general'],
  },
})

const search = ref(props.filters.search ?? '')
const category = ref(props.filters.category ?? '')

let debounceId

watch([search, category], () => {
  clearTimeout(debounceId)
  debounceId = setTimeout(() => {
    router.get(route('orang-tua.notes.index'), {
      search: search.value || undefined,
      category: category.value || undefined,
    }, {
      replace: true,
      preserveState: true,
      preserveScroll: true,
    })
  }, 300)
})

defineOptions({
  layout: ParentLayout,
})

const categoryLabel = (value) => ({
  behavior: 'Perilaku',
  academic: 'Akademik',
  communication: 'Komunikasi',
  general: 'Umum',
}[value] ?? value)

const pagination = computed(() => props.notes?.meta ?? null)

const goToPage = (url) => {
  if (!url) {
    return
  }

  router.visit(url, {
    preserveScroll: true,
    preserveState: true,
  })
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
        <div class="flex flex-col gap-2 sm:flex-row">
          <input
            v-model="search"
            type="text"
            placeholder="Cari catatan..."
            class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-200"
          />
          <select
            v-model="category"
            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-200"
          >
            <option value="">Semua Kategori</option>
            <option v-for="option in categoryOptions" :key="option" :value="option">
              {{ categoryLabel(option) }}
            </option>
          </select>
        </div>
      </div>
    </section>

    <section class="space-y-5">
      <article
        v-for="note in (notes.data ?? [])"
        :key="note.id"
        class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
      >
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ note.recorded_at_readable }}</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ note.title }}</h3>
          </div>
          <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">
            {{ categoryLabel(note.category) }}
          </span>
        </div>
        <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ note.note }}</p>
        <p class="mt-4 text-xs font-semibold uppercase tracking-[0.4em] text-emerald-500">{{ note.teacher?.name ?? 'Guru' }}</p>
      </article>

      <p v-if="!(notes.data ?? []).length" class="text-center text-sm text-slate-500">Belum ada catatan guru.</p>
    </section>

    <nav v-if="pagination" class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-4 text-sm">
      <div>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</div>
      <div class="space-x-2">
        <button
          class="rounded-full border border-slate-200 px-4 py-1"
          :disabled="!props.notes?.links?.prev"
          @click="goToPage(props.notes?.links?.prev)"
        >
          Sebelumnya
        </button>
        <button
          class="rounded-full border border-slate-200 px-4 py-1"
          :disabled="!props.notes?.links?.next"
          @click="goToPage(props.notes?.links?.next)"
        >
          Selanjutnya
        </button>
      </div>
    </nav>
  </div>
</template>
