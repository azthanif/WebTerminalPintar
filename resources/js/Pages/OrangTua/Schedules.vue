<!-- <script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import ParentLayout from '@/Layouts/ParentLayout.vue'

const props = defineProps({
  student: Object,
  filters: {
    type: Object,
    default: () => ({}),
  },
  schedules: {
    type: Object,
    default: () => ({ data: [] }),
  },
  statusOptions: {
    type: Array,
    default: () => ['scheduled', 'completed', 'canceled'],
  },
})

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const perPage = ref(props.filters.per_page ?? 10)

const scheduleItems = computed(() => props.schedules?.data ?? [])
const pagination = computed(() => props.schedules?.meta ?? null)

let debounceId

watch([search, status, perPage], () => {
  clearTimeout(debounceId)
  debounceId = setTimeout(() => {
    router.get(route('orang-tua.schedules.index'), {
      search: search.value || undefined,
      status: status.value || undefined,
      per_page: perPage.value,
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

const statusLabel = (value) => ({
  scheduled: 'Terjadwal',
  completed: 'Selesai',
  canceled: 'Dibatalkan',
}[value] ?? value)

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
    <Head title="Jadwal Anak" />

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <h2 class="text-2xl font-bold text-slate-900">Jadwal Belajar {{ student.name }}</h2>
          <p class="text-sm text-slate-500">Pantau seluruh agenda pembelajaran dan materi</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
          <input
            v-model="search"
            type="text"
            placeholder="Cari mata pelajaran..."
            class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-200"
          />
          <select
            v-model="status"
            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-200"
          >
            <option value="">Semua Status</option>
            <option v-for="option in statusOptions" :key="option" :value="option">
              {{ statusLabel(option) }}
            </option>
          </select>
          <select
            v-model.number="perPage"
            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-200"
          >
            <option value="5">5 / halaman</option>
            <option value="10">10 / halaman</option>
            <option value="15">15 / halaman</option>
          </select>
        </div>
      </div>
    </section>

    <section class="space-y-4">
      <article
        v-for="schedule in scheduleItems"
        :key="schedule.id"
        class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
      >
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ schedule.subject }}</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ schedule.topic }}</h3>
            <p class="text-sm text-slate-500">{{ schedule.description }}</p>
          </div>
          <span class="inline-flex items-center rounded-full px-4 py-1 text-xs font-semibold uppercase tracking-[0.3em]"
            :class="{
              'bg-emerald-50 text-emerald-600': schedule.status === 'scheduled',
              'bg-slate-100 text-slate-600': schedule.status === 'completed',
              'bg-rose-50 text-rose-600': schedule.status === 'canceled',
            }">
            {{ statusLabel(schedule.status) }}
          </span>
        </div>
        <div class="mt-4 grid grid-cols-1 gap-4 text-sm text-slate-600 md:grid-cols-2">
          <p>Waktu: {{ schedule.start_time_readable }}</p>
          <p>Lokasi: {{ schedule.location ?? 'TBD' }}</p>
          <p>Guru: {{ schedule.teacher?.name ?? '-' }}</p>
          <p v-if="schedule.meeting_url">
            Tautan: <a :href="schedule.meeting_url" class="text-emerald-600 hover:underline" target="_blank">Buka Meeting</a>
          </p>
        </div>
        <div v-if="schedule.materials?.length" class="mt-4 rounded-2xl bg-slate-50 p-4">
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Materi</p>
          <ul class="mt-3 space-y-2 text-sm text-slate-600">
            <li v-for="material in schedule.materials" :key="material.id" class="flex items-center justify-between">
              <span>{{ material.title }}</span>
              <a
                v-if="material.download_url"
                :href="material.download_url"
                class="text-emerald-600 text-xs font-semibold"
                target="_blank"
              >
                Unduh
              </a>
            </li>
          </ul>
        </div>
      </article>

      <p v-if="!scheduleItems.length" class="text-center text-sm text-slate-500">Belum ada jadwal yang bisa ditampilkan.</p>
    </section>

    <nav v-if="pagination" class="flex items-center justify-between rounded-2xl border border-slate-100 bg-white p-4 text-sm">
      <div>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</div>
      <div class="space-x-2">
        <button
          class="rounded-full border border-slate-200 px-4 py-1"
          :disabled="!props.schedules?.links?.prev"
          @click="goToPage(props.schedules?.links?.prev)"
        >
          Sebelumnya
        </button>
        <button
          class="rounded-full border border-slate-200 px-4 py-1"
          :disabled="!props.schedules?.links?.next"
          @click="goToPage(props.schedules?.links?.next)"
        >
          Selanjutnya
        </button>
      </div>
    </nav>
  </div>
</template> -->

<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import ParentLayout from '@/Layouts/ParentLayout.vue'
import {
  ClockIcon,
  MapPinIcon,
  DocumentArrowUpIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  student: Object,
  filters: {
    type: Object,
    default: () => ({}),
  },
  schedules: {
    type: Object,
    default: () => ({ data: [] }),
  },
  statusOptions: {
    type: Array,
    default: () => ['scheduled', 'completed', 'canceled'],
  },
})

// State untuk Filter
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const startDate = ref(props.filters.start_date ?? '')
const endDate = ref(props.filters.end_date ?? '')

defineOptions({
  layout: ParentLayout,
})

// Logika Watcher untuk Filter Otomatis
let debounceId
watch([search, status, startDate, endDate], () => {
  clearTimeout(debounceId)
  debounceId = setTimeout(() => {
    router.get(
      route('orang-tua.schedules.index'),
      {
        search: search.value || undefined,
        status: status.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
      },
      {
        replace: true,
        preserveState: true,
        preserveScroll: true,
      }
    )
  }, 500)
})

const statusLabel = (value) => ({
  scheduled: 'Akan Datang',
  completed: 'Selesai',
  canceled: 'Dibatalkan',
}[value] ?? value)

const badgeClass = (status) => {
  const map = {
    'Akan Datang': 'bg-blue-50 text-blue-700',
    'Berlangsung': 'bg-emerald-50 text-emerald-700 animate-pulse',
    'Selesai': 'bg-gray-100 text-gray-700',
    'Dibatalkan': 'bg-red-50 text-red-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-700'
}

const pagination = computed(() => props.schedules?.meta ?? null)

const goToPage = (url) => {
  if (!url) return
  router.visit(url, { preserveScroll: true, preserveState: true })
}

const formatFullDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
}

const formatTimeRange = (start, end) => {
  if (!start) return 'Waktu belum ditentukan'
  const timeFormatter = new Intl.DateTimeFormat('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
  const startLabel = timeFormatter.format(new Date(start))
  const endLabel = end ? timeFormatter.format(new Date(end)) : '—'
  return `${startLabel} - ${endLabel}`
}

const openMaterial = (url) => {
  if (url) window.open(url, '_blank')
}
</script>

<template>
  <div class="space-y-8">
    <Head title="Jadwal Pelajaran" />

    <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-1">
          <p class="text-sm font-semibold text-[#78AE4E]">Jadwal Pelajaran</p>
          <h2 class="text-2xl font-bold text-gray-900">Agenda {{ student.name }}</h2>
          <p class="text-sm text-gray-500">Pantau kegiatan belajar mengajar secara real-time</p>
        </div>
        
        <div class="rounded-2xl border border-gray-100 px-4 py-3 min-w-[200px]">
          <p class="text-xs font-semibold text-gray-500">Total Jadwal</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ schedules.meta?.total ?? 0 }}</p>
        </div>
      </div>

      <div class="mt-6 grid gap-3 md:grid-cols-4">
        <div class="rounded-2xl border border-gray-100 px-4 py-3">
          <label class="text-xs font-semibold text-gray-500">Dari Tanggal</label>
          <input 
            v-model="startDate" 
            type="date" 
            class="mt-1 w-full border-none bg-transparent text-sm text-gray-800 focus:outline-none focus:ring-0 p-0"
          />
        </div>

        <div class="rounded-2xl border border-gray-100 px-4 py-3">
          <label class="text-xs font-semibold text-gray-500">Sampai Tanggal</label>
          <input 
            v-model="endDate" 
            type="date" 
            class="mt-1 w-full border-none bg-transparent text-sm text-gray-800 focus:outline-none focus:ring-0 p-0"
          />
        </div>

        <div class="rounded-2xl border border-gray-100 px-4 py-3">
          <label class="text-xs font-semibold text-gray-500">Status</label>
          <select
            v-model="status"
            class="mt-1 w-full border-none bg-transparent text-sm text-gray-800 focus:outline-none focus:ring-0 p-0"
          >
            <option value="">Semua Status</option>
            <option v-for="opt in statusOptions" :key="opt" :value="opt">
              {{ statusLabel(opt) }}
            </option>
          </select>
        </div>

        <div class="rounded-2xl border border-gray-100 px-4 py-3">
          <label class="text-xs font-semibold text-gray-500">Cari</label>
          <input
            v-model="search"
            type="search"
            placeholder="Topik / Mapel..."
            class="mt-1 w-full border-none bg-transparent text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-0 p-0"
          />
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
      <article
        v-for="schedule in (schedules.data ?? [])"
        :key="schedule.id"
        class="rounded-3xl border border-gray-100 bg-[#fdfcf9] p-5 shadow-sm transition hover:shadow-md"
      >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="flex-1 space-y-3">
            <div class="flex flex-wrap items-center gap-3">
              <p class="text-lg font-semibold text-gray-900">{{ formatFullDate(schedule.start_time) }}</p>
              <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(schedule.status)">
                {{ schedule.status }}
              </span>
            </div>

            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-600">
                        {{ schedule.subject }}
                    </span>
                </div>
                <p class="text-xl font-bold text-gray-900">{{ schedule.topic }}</p>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                    {{ schedule.description || 'Tidak ada deskripsi tambahan.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 pt-2">
              <div class="flex items-center gap-2">
                <ClockIcon class="h-5 w-5 text-[#78AE4E]" />
                <span>{{ formatTimeRange(schedule.start_time, schedule.end_time) }}</span>
              </div>
              <div class="flex items-center gap-2" v-if="schedule.location">
                <MapPinIcon class="h-5 w-5 text-[#78AE4E]" />
                <span>{{ schedule.location }}</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                </div>
                <span>{{ schedule.teacher?.name ?? 'Guru' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-5 space-y-3 pt-4 border-t border-gray-100" v-if="schedule.materials?.length">
          <h3 class="text-sm font-semibold text-gray-900">Materi Pembelajaran</h3>
          <div class="space-y-2">
            <button
              v-for="material in schedule.materials"
              :key="material.id"
              type="button"
              class="flex w-full items-center gap-3 rounded-2xl border border-gray-100 bg-white p-3 text-left hover:border-[#78AE4E] hover:bg-[#f1f8e9] transition group"
              @click="openMaterial(material.download_url)"
            >
              <div class="rounded-xl bg-[#eaf6df] p-2 text-[#78AE4E] group-hover:bg-white transition">
                <DocumentArrowUpIcon class="h-5 w-5" />
              </div>
              <div class="flex-1">
                <p class="font-semibold text-gray-900 text-sm">{{ material.title }}</p>
                <p class="text-xs text-gray-500">{{ material.description || 'Klik untuk mengunduh' }}</p>
              </div>
              <span class="text-xs font-semibold text-[#78AE4E]">Unduh</span>
            </button>
          </div>
        </div>
        
        <div v-else class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400 italic">Tidak ada materi terlampir untuk sesi ini.</p>
        </div>
      </article>

      <div v-if="!(schedules.data ?? []).length" class="flex flex-col items-center justify-center py-16 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p>Belum ada jadwal yang ditemukan.</p>
        <button v-if="status || search || startDate" @click="status=''; search=''; startDate=''; endDate=''" class="mt-2 text-sm text-[#78AE4E] hover:underline font-semibold">
            Reset filter
        </button>
      </div>
    </section>

    <nav v-if="pagination && pagination.total > pagination.per_page" class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 text-sm">
      <div class="text-gray-500">
        Menampilkan {{ pagination.from }}-{{ pagination.to }} dari {{ pagination.total }} jadwal
      </div>
      <div class="space-x-2">
        <button
          class="rounded-full border border-gray-200 px-4 py-1 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
          :disabled="!props.schedules?.links?.prev"
          @click="goToPage(props.schedules?.links?.prev)"
        >
          Sebelumnya
        </button>
        <button
          class="rounded-full border border-gray-200 px-4 py-1 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
          :disabled="!props.schedules?.links?.next"
          @click="goToPage(props.schedules?.links?.next)"
        >
          Selanjutnya
        </button>
      </div>
    </nav>
  </div>
</template>