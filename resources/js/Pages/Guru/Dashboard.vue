<template>
  <div class="space-y-8">
    <Head title="Dashboard Guru" />

    <section class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-600">Portal Guru</p>
        <h1 class="text-4xl font-bold text-slate-900">Dashboard Guru</h1>
        <p class="text-sm text-slate-500">Selamat datang, {{ teacherName }}. {{ heroSubtitle }}</p>
      </div>
      <button
        class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-700"
        @click="fetchSummary"
      >
        <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': isSyncingSummary }" />
        Sinkron Data
      </button>
    </section>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
      <div v-for="stat in highlightStats" :key="stat.id" class="rounded-3xl bg-white p-6 shadow-md">
        <div class="mb-4 flex items-start justify-between">
          <div>
            <p class="text-sm font-medium text-slate-600">{{ stat.label }}</p>
            <p class="mt-1 text-4xl font-bold text-emerald-600">{{ stat.value }}</p>
            <p class="mt-2 text-xs text-slate-400">{{ stat.meta }}</p>
          </div>
          <div class="text-4xl">{{ stat.icon }}</div>
        </div>
      </div>
    </section>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
      <div v-for="module in moduleCards" :key="module.id" class="rounded-3xl bg-white p-6 shadow-md transition hover:shadow-lg">
        <div class="mb-4 flex items-start gap-4">
          <div class="text-4xl">{{ module.icon }}</div>
          <div>
            <h3 class="text-lg font-bold text-slate-800">{{ module.title }}</h3>
            <p class="mt-1 text-xs text-slate-600">{{ module.description }}</p>
            <p class="mt-2 text-xs text-slate-400">{{ module.meta }}</p>
          </div>
        </div>
        <Link
          :href="module.href"
          class="block rounded-2xl bg-emerald-600 py-2 text-center text-sm font-semibold text-white transition hover:bg-emerald-700"
        >
          Buka Modul
        </Link>
      </div>
    </section>

    <section class="rounded-3xl bg-white p-6 shadow-md">
      <div class="mb-6 flex items-center gap-3">
        <div class="text-2xl">📊</div>
        <h2 class="text-xl font-bold text-slate-800">Aktivitas Terbaru</h2>
      </div>
      <div class="space-y-4">
        <div v-for="activity in activityStream" :key="activity.id" class="flex items-start gap-3 border-b pb-4 last:border-b-0">
          <div class="text-2xl">{{ activity.icon }}</div>
          <div class="flex-1">
            <p class="text-sm font-medium text-slate-800">{{ activity.description }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ activity.time }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      <section class="xl:col-span-2 rounded-3xl border border-slate-100 bg-white p-6 shadow-md">
        <header class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900">Kehadiran Siswa</h3>
            <p class="text-sm text-slate-500">Pantau status kehadiran terbaru dari seluruh sesi</p>
          </div>
          <div class="flex flex-wrap gap-3">
            <select
              v-model="attendanceFilters.status"
              class="rounded-2xl border border-slate-200 px-4 py-2 text-sm"
            >
              <option v-for="option in attendanceStatusOptions" :key="option" :value="option">{{ option }}</option>
            </select>
            <input
              v-model="attendanceFilters.search"
              type="text"
              placeholder="Cari siswa/topik"
              class="rounded-2xl border border-slate-200 px-4 py-2 text-sm"
            />
          </div>
        </header>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-100">
          <table class="min-w-full divide-y divide-slate-100">
            <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500">
              <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Siswa</th>
                <th class="px-4 py-3">Sesi</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Catatan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
              <tr v-for="(record, index) in attendanceRecords" :key="record.id">
                <td class="px-4 py-3 text-slate-400">{{ attendanceStartIndex + index }}</td>
                <td class="px-4 py-3">
                  <p class="font-medium text-slate-900">{{ record.student?.name ?? 'Tidak diketahui' }}</p>
                  <p class="text-xs text-slate-500">{{ record.session_time || '—' }}</p>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ record.session_topic || record.schedule?.topic || '—' }}</td>
                <td class="px-4 py-3 text-slate-500">{{ formatDate(record.attendance_date) }}</td>
                <td class="px-4 py-3">
                  <span :class="['inline-flex items-center px-3 py-1 rounded-full border text-xs font-medium', statusClass(record.status)]">
                    {{ record.status }}
                  </span>
                </td>
                <td class="px-4 py-3 text-slate-500">{{ record.notes || '—' }}</td>
              </tr>
              <tr v-if="!attendanceRecords.length && !isLoadingAttendance">
                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Belum ada data kehadiran.</td>
              </tr>
              <tr v-if="isLoadingAttendance">
                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Memuat data kehadiran...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4 flex items-center justify-between text-sm text-slate-500">
          <p>Menampilkan {{ attendanceRecords.length }} dari {{ attendancePagination.total }} entri</p>
          <div class="flex items-center gap-2">
            <button
              class="rounded-full border px-3 py-1"
              :disabled="attendancePagination.page === 1"
              @click="changeAttendancePage(-1)"
            >
              Sebelumnya
            </button>
            <span>Hal {{ attendancePagination.page }} / {{ attendancePagination.lastPage }}</span>
            <button
              class="rounded-full border px-3 py-1"
              :disabled="attendancePagination.page === attendancePagination.lastPage"
              @click="changeAttendancePage(1)"
            >
              Berikutnya
            </button>
          </div>
        </div>
      </section>

      <section class="flex flex-col rounded-3xl border border-slate-100 bg-white p-6 shadow-md">
        <div class="mb-4 flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-slate-900">Catatan Terbaru</h3>
            <p class="text-sm text-slate-500">Ringkasannya dapat dibagikan ke orang tua</p>
          </div>
          <Link :href="route('guru.attendance')" class="text-sm text-slate-600 hover:text-slate-900">Kelola</Link>
        </div>
        <ul class="flex-1 space-y-4">
          <li
            v-for="note in summary.recentNotes"
            :key="note.id"
            class="cursor-pointer rounded-2xl border border-slate-100 p-4 hover:border-slate-200"
            @click="openNoteModal(note)"
          >
            <p class="text-xs text-slate-400">{{ formatDate(note.recorded_at) }}</p>
            <p class="mt-1 font-semibold text-slate-900">{{ note.title }}</p>
            <p class="text-sm text-slate-500">{{ note.student ?? '—' }}</p>
          </li>
          <li v-if="!summary.recentNotes?.length" class="py-8 text-center text-slate-400">
            Belum ada catatan terbaru
          </li>
        </ul>
        <button
          class="mt-4 inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:text-slate-900"
          @click="navigate(route('guru.attendance'))"
        >
          <DocumentTextIcon class="h-4 w-4" />
          Tulis catatan baru
        </button>
      </section>
    </section>
  </div>

  <transition name="fade">
    <div
      v-if="showNoteModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-6 backdrop-blur-sm"
      role="dialog"
      aria-modal="true"
    >
      <div class="w-full max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-slate-400">{{ formatDate(activeNote?.recorded_at) }}</p>
            <h4 class="text-xl font-semibold text-slate-900">{{ activeNote?.title }}</h4>
            <p class="text-sm text-slate-500">{{ activeNote?.student ?? '—' }}</p>
          </div>
          <button class="text-slate-400 transition hover:text-slate-600" @click="closeNoteModal">✕</button>
        </div>
        <p class="leading-relaxed text-slate-600">{{ activeNote?.note }}</p>
        <div class="flex items-center gap-2 text-xs text-slate-400">
          <span class="rounded-full border bg-slate-50 px-3 py-1">{{ activeNote?.category }}</span>
          <span class="rounded-full border bg-slate-50 px-3 py-1">{{ activeNote?.sentiment ?? 'neutral' }}</span>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import GuruLayout from '@/Layouts/GuruLayout.vue'
import axios from 'axios'
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import { ArrowPathIcon, DocumentTextIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  summary: {
    type: Object,
    default: () => ({
      upcomingSchedules: [],
      attendanceStats: {},
      recentNotes: [],
    }),
  },
})

defineOptions({ layout: GuruLayout })

const page = usePage()
const teacherName = computed(() => page.props.auth?.user?.name ?? 'Guru Terminal Pintar')
const heroSubtitle = computed(() =>
  new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(new Date())
)

const formatDate = value => {
  if (!value) return '—'
  return new Date(value).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const summary = ref(JSON.parse(JSON.stringify(props.summary)))
const isSyncingSummary = ref(false)

const fetchSummary = async () => {
  isSyncingSummary.value = true
  try {
    const { data } = await axios.get(route('guru.api.dashboard.summary'))
    summary.value = data
  } finally {
    isSyncingSummary.value = false
  }
}

const highlightStats = computed(() => {
  const stats = summary.value.attendanceStats || {}
  return [
    {
      id: 'kehadiran',
      label: 'Kehadiran Tercatat',
      value: stats.Hadir ?? 0,
      meta: stats.Hadir ? `+${Math.min(stats.Hadir, 5)} sesi sukses minggu ini` : 'Belum ada data minggu ini',
      icon: '👩‍🏫',
    },
    {
      id: 'izin',
      label: 'Izin & Sakit',
      value: (stats.Izin ?? 0) + (stats.Sakit ?? 0),
      meta: 'Perlu tindak lanjut orang tua',
      icon: '🩺',
    },
    {
      id: 'jadwal',
      label: 'Jadwal Aktif',
      value: summary.value.upcomingSchedules?.length ?? 0,
      meta: '5 sesi terdekat otomatis terpantau',
      icon: '📅',
    },
  ]
})

const navigate = href => {
  window.location.href = href
}

const attendanceStatusOptions = ['Semua', 'Hadir', 'Sakit', 'Izin', 'Alpha']
const attendanceRecords = ref([])
const attendanceFilters = reactive({
  status: 'Semua',
  search: '',
})
const attendancePagination = reactive({ page: 1, perPage: 8, total: 0, lastPage: 1 })
const isLoadingAttendance = ref(false)

const moduleCards = computed(() => [
  {
    id: 'attendance',
    icon: '📝',
    title: 'Kehadiran & Catatan',
    description: 'Kelola status siswa dan catatan perkembangan',
    meta: `${attendancePagination.total || 0} entri kehadiran`,
    href: route('guru.attendance'),
  },
  {
    id: 'materials',
    icon: '📚',
    title: 'Kelola Materi',
    description: 'Unggah modul dan bahan ajar',
    meta: `${summary.value.upcomingSchedules?.length ?? 0} sesi terhubung`,
    href: route('guru.schedules'),
  },
  {
    id: 'reports',
    icon: '📈',
    title: 'Pemantauan Jadwal',
    description: 'Lihat detail jadwal & materi',
    meta: 'Selalu terbarui otomatis',
    href: route('guru.schedules'),
  },
])

const activityStream = computed(() => {
  const items = []

  summary.value.upcomingSchedules?.forEach(schedule => {
    items.push({
      id: `schedule-${schedule.id}`,
      icon: '📅',
      description: `${schedule.topic} • ${schedule.student ?? 'Kelas umum'}`,
      time: formatDate(schedule.start_time),
    })
  })

  summary.value.recentNotes?.forEach(note => {
    items.push({
      id: `note-${note.id}`,
      icon: '📝',
      description: `Catatan ${note.student ?? 'siswa'}: ${note.title}`,
      time: formatDate(note.recorded_at),
    })
  })

  if (!items.length) {
    return [
      {
        id: 'fallback-activity',
        icon: '✨',
        description: 'Belum ada aktivitas terbaru, tetap semangat mengajar!',
        time: heroSubtitle.value,
      },
    ]
  }

  return items.slice(0, 6)
})

const fetchAttendance = async () => {
  isLoadingAttendance.value = true
  try {
    const { data } = await axios.get(route('guru.api.attendance.index'), {
      params: {
        status: attendanceFilters.status,
        search: attendanceFilters.search,
        page: attendancePagination.page,
        per_page: attendancePagination.perPage,
      },
    })

    attendanceRecords.value = data.data
    attendancePagination.total = data.meta.total
    attendancePagination.lastPage = data.meta.last_page || 1
  } finally {
    isLoadingAttendance.value = false
  }
}

let searchTimer
watch(
  () => attendanceFilters.search,
  () => {
    clearTimeout(searchTimer)
    attendancePagination.page = 1
    searchTimer = setTimeout(fetchAttendance, 400)
  }
)

watch(
  () => attendanceFilters.status,
  () => {
    attendancePagination.page = 1
    fetchAttendance()
  }
)

const changeAttendancePage = step => {
  const target = attendancePagination.page + step
  if (target < 1 || target > attendancePagination.lastPage) return
  attendancePagination.page = target
  fetchAttendance()
}

const attendanceStartIndex = computed(
  () => (attendancePagination.page - 1) * attendancePagination.perPage + 1
)

const statusClass = status => {
  const map = {
    Hadir: 'bg-emerald-50 border-emerald-200 text-emerald-700',
    Izin: 'bg-amber-50 border-amber-200 text-amber-700',
    Sakit: 'bg-sky-50 border-sky-200 text-sky-700',
    Alpha: 'bg-rose-50 border-rose-200 text-rose-700',
  }
  return map[status] ?? 'bg-slate-50 border-slate-200 text-slate-600'
}

const showNoteModal = ref(false)
const activeNote = ref(null)
const openNoteModal = note => {
  activeNote.value = note
  showNoteModal.value = true
}
const closeNoteModal = () => {
  showNoteModal.value = false
  activeNote.value = null
}

onMounted(() => {
  fetchAttendance()
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
