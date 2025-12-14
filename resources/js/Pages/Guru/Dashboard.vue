<template>
  <div class="space-y-10">
    <Head title="Dashboard Guru" />

    <!-- Header Section (Premium Upgrade) -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative">
      <div>
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600 mb-2 border border-emerald-100 shadow-sm">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span>Portal Guru</span>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
             Dashboard <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">Overview</span>
        </h1>
        <p class="mt-2 text-slate-500 font-medium text-lg">Selamat datang, <span class="text-emerald-700 font-bold">{{ teacherName }}</span>. {{ heroSubtitle }}</p>
      </div>
      
      <button
        class="group inline-flex items-center gap-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition-all hover:shadow-xl hover:-translate-y-1 active:scale-95 border border-emerald-500"
        @click="fetchSummary"
      >
        <ArrowPathIcon class="h-5 w-5 transition-transform group-hover:rotate-180 text-emerald-100" :class="{ 'animate-spin': isSyncingSummary }" />
        <span>Sinkron Data</span>
      </button>
    </section>

    <!-- Stats Grid (Fresh & Elegant Colors) -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div v-for="stat in highlightStats" :key="stat.id" 
           class="group relative overflow-hidden rounded-[2.5rem] p-8 shadow-sm border transition-all hover:shadow-lg hover:-translate-y-1"
           :class="[stat.cardBg, stat.cardBorder]">
        
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-32 w-32 rounded-full opacity-20 blur-2xl transition-opacity group-hover:opacity-30" :class="stat.bg"></div>
        
        <div class="relative flex items-start justify-between">
          <div>
            <p class="text-sm font-bold uppercase tracking-wider opacity-70" :class="stat.textLabel">{{ stat.label }}</p>
            <h3 class="mt-2 text-4xl font-extrabold text-slate-800">{{ stat.value }}</h3>
            <div class="mt-3 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset shadow-sm" :class="[stat.tagBg, stat.tagColor, 'ring-black/5']">
                <span class="font-medium">{{ stat.meta }}</span>
            </div>
          </div>
          <div class="rounded-2xl p-3 ring-4 ring-white/50 backdrop-blur-sm transition-all group-hover:scale-110 shadow-sm" :class="[stat.bg, stat.color]">
            <component :is="stat.icon" class="h-8 w-8" />
          </div>
        </div>
      </div>
    </section>

    <!-- Main Modules Grid -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="module in moduleCards" :key="module.id" 
           class="group flex flex-col justify-between rounded-[2.5rem] bg-white p-8 shadow-sm border border-slate-100 transition-all hover:shadow-xl hover:-translate-y-2 cursor-pointer relative overflow-hidden hover:border-slate-200">
        
        <!-- Subtle Pattern Background -->
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none" 
            :style="{ backgroundImage: 'radial-gradient(' + module.dotColor + ' 1px, transparent 1px)', backgroundSize: '20px 20px' }">
        </div>

        <div class="absolute top-0 right-0 -mr-8 -mt-8 h-40 w-40 rounded-full opacity-5 bg-current transition-transform group-hover:scale-150" :class="module.color"></div>
        
        <div class="relative z-10">
           <div :class="[module.bg, module.color]" class="inline-flex rounded-2xl p-4 shadow-sm ring-4 ring-slate-50 group-hover:ring-black/5 transition-all group-hover:scale-110">
             <component :is="module.icon" class="h-8 w-8" />
           </div>
           
           <h3 class="mt-6 text-xl font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">{{ module.title }}</h3>
           <p class="mt-2 text-sm leading-relaxed text-slate-500 font-medium group-hover:text-slate-600">{{ module.description }}</p>
        </div>

        <div class="mt-8 relative z-10">
           <p class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ module.meta }}</p>
            <Link
              :href="module.href"
              class="flex w-full items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-bold transition-all shadow-sm border"
              :class="[
                  'bg-slate-50 text-slate-700 border-slate-200',
                  'group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600 group-hover:shadow-emerald-200'
              ]"
            >
              <span>Akses Modul</span>
              <ArrowRightIcon class="h-4 w-4 transition-transform group-hover:translate-x-1" />
            </Link>
        </div>
      </div>
    </section>

    <!-- Activity & Lists Section -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
      <!-- Activity Feed (More Colorful) -->
      <div class="lg:col-span-1 rounded-[2.5rem] bg-white p-8 shadow-sm border border-slate-200 flex flex-col">
        <div class="mb-6 flex items-center gap-3">
            <div class="rounded-xl bg-orange-100 p-2.5 text-orange-600">
                <ChartBarIcon class="h-6 w-6" />
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Aktivitas</h2>
                <p class="text-xs font-semibold text-slate-400">Linimasa kegiatan terkini</p>
            </div>
        </div>
        <div class="space-y-4 flex-1">
            <div v-for="(activity, index) in activityStream" :key="activity.id" 
                 class="group flex items-start gap-4 p-4 rounded-2xl transition-all border border-transparent hover:border-slate-100 hover:shadow-sm"
                 :class="[activity.bgClass, 'hover:bg-white']">
                 
                <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm transition-transform group-hover:scale-110" 
                    :class="[activity.iconBg, activity.iconColor]">
                    <component :is="activity.icon" class="h-5 w-5" />
                </div>
                
                <div class="flex-1">
                    <p class="text-sm font-bold text-slate-800 leading-snug group-hover:text-emerald-700 transition-colors">{{ activity.description }}</p>
                    <div class="mt-1.5 flex items-center gap-1.5 text-xs font-medium" :class="activity.timeColor">
                        <ClockIcon class="h-3.5 w-3.5" />
                        <span>{{ activity.time }}</span>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <!-- Attendance Table (More Vibrant) -->
      <div class="lg:col-span-2 space-y-8">
          <!-- Table Section -->
          <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm flex flex-col h-full">
            <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
              <div class="flex items-center gap-3">
                 <div class="rounded-xl bg-blue-100 p-2.5 text-blue-600">
                    <ClipboardDocumentCheckIcon class="h-6 w-6" />
                 </div>
                 <div>
                    <h3 class="text-xl font-bold text-slate-800">Kehadiran Siswa</h3>
                    <p class="text-sm text-slate-500 font-medium">Pantau status kehadiran sesi.</p>
                 </div>
              </div>
              
              <div class="flex flex-wrap gap-2">
                <div class="relative">
                    <select
                      v-model="attendanceFilters.status"
                      class="appearance-none rounded-xl border border-slate-200 bg-slate-50 pl-4 pr-10 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 transition-all cursor-pointer hover:bg-white hover:border-emerald-200"
                    >
                      <option v-for="option in attendanceStatusOptions" :key="option" :value="option">{{ option }}</option>
                    </select>
                    <!-- Chevron Down Icon manually positioned if needed, or rely on browser default mostly hidden by appearance-none + styling -->
                </div>
                
                <div class="relative w-full md:w-56">
                    <input
                      v-model="attendanceFilters.search"
                      type="text"
                      placeholder="Cari siswa..."
                      class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 transition-all hover:bg-white hover:border-emerald-200"
                    />
                </div>
              </div>
            </header>
    
            <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm flex-1">
              <table class="min-w-full">
                <thead class="bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                  <tr>
                    <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Siswa</th>
                    <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Topik & Sesi</th>
                    <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm bg-white">
                  <tr v-for="(record, index) in attendanceRecords" :key="record.id" 
                      class="group transition-all duration-200 hover:bg-emerald-50/30">
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-4">
                          <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-100 to-white border border-indigo-100 flex items-center justify-center text-indigo-600 font-extrabold text-sm shadow-sm group-hover:scale-110 transition-transform">
                              {{ record.student?.name?.charAt(0) ?? '?' }}
                          </div>
                          <div>
                             <div class="font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">{{ record.student?.name ?? 'Tidak diketahui' }}</div>
                             <div class="text-[10px] uppercase font-bold text-slate-400 mt-0.5 tracking-wide">{{ record.student?.nis ?? 'NIS --' }}</div>
                          </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-700">{{ record.session_topic || record.schedule?.topic || '—' }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 bg-slate-100 text-[10px] font-bold text-slate-500">
                                <CalendarIcon class="h-3 w-3" />
                                {{ formatDate(record.attendance_date).split(',')[1] }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 bg-slate-100 text-[10px] font-bold text-slate-500">
                                <ClockIcon class="h-3 w-3" />
                                {{ record.session_time || '—' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="['inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold border shadow-sm transition-transform group-hover:scale-105', statusClass(record.status)]">
                        <span class="h-1.5 w-1.5 rounded-full" :class="statusDotClass(record.status)"></span>
                        {{ record.status }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="!attendanceRecords.length && !isLoadingAttendance">
                    <td colspan="3" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-slate-400">
                             <ClipboardDocumentCheckIcon class="h-12 w-12 text-slate-200 mb-3" />
                             <p class="text-sm font-medium italic">Belum ada data kehadiran yang tercatat.</p>
                        </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-6">
                <span class="text-xs font-bold text-slate-400">
                    Halaman {{ attendancePagination.page }} dari {{ attendancePagination.lastPage }}
                </span>
                <div class="flex gap-2">
                   <button 
                      :disabled="attendancePagination.page === 1" 
                      @click="changeAttendancePage(-1)"
                      class="flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 disabled:opacity-50 transition-colors"
                   >
                       <ArrowRightIcon class="h-3 w-3 rotate-180" />
                       Sebelumnnya
                   </button>
                   <button 
                      :disabled="attendancePagination.page === attendancePagination.lastPage" 
                      @click="changeAttendancePage(1)"
                      class="flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 disabled:opacity-50 transition-colors"
                   >
                       Berikutnya
                       <ArrowRightIcon class="h-3 w-3" />
                   </button>
                </div>
            </div>
          </section>
      </div>
    </section>
  </div>

  <transition name="fade">
    <!-- Note Modal (Unchanged logic, just ensure consistency implies no changes needed if previously fine, but context implied focusing on dashboard main page) -->
    <div
      v-if="showNoteModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-6 backdrop-blur-md"
      role="dialog"
      aria-modal="true"
    >
        <!-- ... existing modal code ... -->
       <div class="w-full max-w-xl space-y-4 rounded-[2rem] bg-white p-8 shadow-2xl scale-100 transition-all border border-slate-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ formatDate(activeNote?.recorded_at) }}</p>
            <h4 class="mt-1 text-2xl font-bold text-slate-800">{{ activeNote?.title }}</h4>
            <p class="text-sm text-slate-500 font-medium">Siswa: {{ activeNote?.student ?? '—' }}</p>
          </div>
          <button class="rounded-full bg-slate-100 p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600" @click="closeNoteModal">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
          </button>
        </div>
        <div class="rounded-2xl bg-slate-50 p-6 text-slate-600 leading-relaxed text-sm font-medium border border-slate-100">
            {{ activeNote?.note }}
        </div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
          <span class="rounded-lg bg-slate-100 px-3 py-1.5 border border-slate-200">{{ activeNote?.category }}</span>
          <span class="rounded-lg bg-slate-100 px-3 py-1.5 border border-slate-200">{{ activeNote?.sentiment ?? 'neutral' }}</span>
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
import { 
    ArrowPathIcon, 
    DocumentTextIcon, 
    UserGroupIcon, 
    AcademicCapIcon, 
    CalendarIcon, 
    ClipboardDocumentCheckIcon, 
    ChatBubbleBottomCenterTextIcon, 
    ChartBarIcon, 
    ClockIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline'

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
const teacherName = computed(() => page.props.auth?.user?.name ?? 'Guru')
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
       meta: stats.Hadir ? `+${Math.min(stats.Hadir, 5)} sesi minggu ini` : 'Belum ada data',
      icon: ClipboardDocumentCheckIcon,
      color: 'text-emerald-600',
      bg: 'bg-emerald-100',
      tagColor: 'text-emerald-800',
      tagBg: 'bg-emerald-200/50',
      cardBg: 'bg-emerald-50/30',
      cardBorder: 'border-emerald-100',
      textLabel: 'text-emerald-900',
    },
    {
      id: 'izin',
      label: 'Izin & Sakit',
      value: (stats.Izin ?? 0) + (stats.Sakit ?? 0),
       meta: 'Perlu tindak lanjut',
      icon: UserGroupIcon,
      color: 'text-amber-600',
      bg: 'bg-amber-100',
      tagColor: 'text-amber-800',
      tagBg: 'bg-amber-200/50',
      cardBg: 'bg-amber-50/30',
      cardBorder: 'border-amber-100',
      textLabel: 'text-amber-900',
    },
    {
      id: 'jadwal',
      label: 'Jadwal Aktif',
      value: summary.value.upcomingSchedules?.length ?? 0,
      meta: 'Sesi kelas terdekat',
      icon: CalendarIcon,
      color: 'text-blue-600',
      bg: 'bg-blue-100',
      tagColor: 'text-blue-800',
      tagBg: 'bg-blue-200/50',
      cardBg: 'bg-blue-50/30',
      cardBorder: 'border-blue-100',
       textLabel: 'text-blue-900',
    },
  ]
})

const attendanceStatusOptions = ['Semua', 'Hadir', 'Sakit', 'Izin', 'Alpha']
const attendanceRecords = ref([])
const attendanceFilters = reactive({
  status: 'Semua',
  search: '',
})
const attendancePagination = reactive({ page: 1, perPage: 6, total: 0, lastPage: 1 }) 
const isLoadingAttendance = ref(false)

const moduleCards = computed(() => [
  {
    id: 'attendance',
    icon: ChatBubbleBottomCenterTextIcon,
    title: 'Kehadiran & Catatan',
    description: 'Kelola status siswa dan catatan perkembangan.',
    meta: `${attendancePagination.total || 0} entri kehadiran`,
    href: route('guru.attendance'),
    color: 'text-blue-600',
    bg: 'bg-blue-50',
    dotColor: '#e0f2fe' // blue-100
  },
  {
    id: 'materials',
    icon: AcademicCapIcon,
    title: 'Kelola Materi',
    description: 'Unggah modul dan bahan ajar kelas.',
    meta: `${summary.value.upcomingSchedules?.length ?? 0} sesi terhubung`,
    href: route('guru.schedules'),
    color: 'text-emerald-600',
    bg: 'bg-emerald-50',
    dotColor: '#d1fae5' // emerald-100
  },
  {
    id: 'reports',
    icon: ChartBarIcon,
    title: 'Pemantauan Jadwal',
    description: 'Lihat detail jadwal & materi pembelajaran.',
     meta: 'Tersinkronisasi otomatis',
    href: route('guru.schedules'),
    color: 'text-purple-600',
    bg: 'bg-purple-50',
    dotColor: '#f3e8ff' // purple-100
  },
])

const activityStream = computed(() => {
  const items = []

  summary.value.upcomingSchedules?.forEach(schedule => {
    items.push({
      id: `schedule-${schedule.id}`,
      icon: CalendarIcon,
      description: `${schedule.topic} • ${schedule.student ?? 'Kelas umum'}`,
      time: formatDate(schedule.start_time),
      bgClass: 'bg-blue-50/40',
      iconBg: 'bg-blue-100',
      iconColor: 'text-blue-600',
      timeColor: 'text-blue-400'
    })
  })

  summary.value.recentNotes?.forEach(note => {
    items.push({
      id: `note-${note.id}`,
      icon: DocumentTextIcon,
      description: `Catatan ${note.student ?? 'siswa'}: ${note.title}`,
      time: formatDate(note.recorded_at),
      bgClass: 'bg-orange-50/40',
      iconBg: 'bg-orange-100',
      iconColor: 'text-orange-600',
       timeColor: 'text-orange-400'
    })
  })

  // Fill with placeholder if empty
  if (!items.length) {
     return [
       {
        id: 'fallback',
        icon: UserGroupIcon,
        description: 'Belum ada aktivitas terbaru.',
        time: 'Hari ini',
        bgClass: 'bg-slate-50',
        iconBg: 'bg-slate-100',
        iconColor: 'text-slate-500', 
        timeColor: 'text-slate-400'
       }
     ]
  }

  return items.slice(0, 5)
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

const statusClass = status => {
  const map = {
    Hadir: 'bg-emerald-50 text-emerald-700 border-emerald-100',
    Izin: 'bg-amber-50 text-amber-700 border-amber-100',
    Sakit: 'bg-sky-50 text-sky-700 border-sky-100',
    Alpha: 'bg-rose-50 text-rose-700 border-rose-100',
  }
  return map[status] ?? 'bg-slate-50 text-slate-600 border-slate-100'
}

const statusDotClass = status => {
    const map = {
    Hadir: 'bg-emerald-500',
    Izin: 'bg-amber-500',
    Sakit: 'bg-sky-500',
    Alpha: 'bg-rose-500',
  }
  return map[status] ?? 'bg-slate-400'
}

const showNoteModal = ref(false)
const activeNote = ref(null)
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
