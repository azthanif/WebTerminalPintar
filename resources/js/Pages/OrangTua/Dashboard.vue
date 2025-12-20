<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import ParentLayout from '@/Layouts/ParentLayout.vue'
import { useParentDashboard } from '@/Composables/useParentDashboard'
import { 
  CheckCircleIcon, 
  XCircleIcon, 
  UserIcon, 
  AcademicCapIcon, 
  CalendarIcon, 
  ClockIcon, 
  ArrowRightIcon, 
  DocumentTextIcon 
} from '@heroicons/vue/24/outline'

const props = defineProps({
  student: { type: Object, required: true },
  summary: { type: Object, default: () => ({}) },
  notes: { type: Array, default: () => [] },
  schedules: { type: Array, default: () => [] },
  attendances: { type: Array, default: () => [] },
})

const { attendanceRate, sessionsThisMonth, notesThisMonth, nextSchedule } = useParentDashboard(props.summary)

defineOptions({ layout: ParentLayout })

// --- FUNGSI FORMAT WAKTU (CLIENT SIDE) ---
const formatTime = (isoString) => {
  if (!isoString) return '-'
  return new Date(isoString).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatDate = (isoString) => {
  if (!isoString) return '-'
  return new Date(isoString).toLocaleDateString('id-ID', {
    weekday: 'long', 
    day: 'numeric', 
    month: 'long',
    year: 'numeric'
  })
}

// --- REAL-TIME STATUS LOGIC ---
const currentTime = ref(new Date())
let timeUpdateInterval = null

onMounted(() => {
  timeUpdateInterval = setInterval(() => {
    currentTime.value = new Date()
  }, 1000)
})

onBeforeUnmount(() => {
  if (timeUpdateInterval) clearInterval(timeUpdateInterval)
})

const computeScheduleStatus = (schedule) => {
  if (!schedule || !schedule.start_time) return 'Akan Datang'
  
  const now = currentTime.value
  const start = new Date(schedule.start_time)
  const end = schedule.end_time ? new Date(schedule.end_time) : null
  
  if (now < start) return 'Akan Datang'
  if (end && now >= start && now <= end) return 'Berlangsung'
  if (!end && now >= start) return 'Berlangsung' // Fallback jika tidak ada end_time
  return 'Selesai'
}

const statusBadgeClass = (status) => {
  switch (status) {
    case 'Berlangsung': return 'bg-emerald-100 text-emerald-700 border-emerald-200'
    case 'Selesai': return 'bg-slate-100 text-slate-700 border-slate-200'
    default: return 'bg-amber-100 text-amber-700 border-amber-200'
  }
}
// -----------------------------

const noteFallback = computed(() => props.notes.length ? props.notes : [
  {
    id: 'fallback-note',
    title: 'Belum ada catatan',
    note: 'Belum ada catatan guru bulan ini.',
    recorded_at_readable: '-',
    teacher: { name: '-' },
  },
])

const scheduleFallback = computed(() => props.schedules.length ? props.schedules : [
  {
    id: 'fallback-schedule',
    subject: 'Belum ada jadwal',
    topic: 'Jadwal mendatang akan tampil di sini',
    start_time: null, // Ubah dari readable ke raw
    location: '-',
    status: 'scheduled',
  },
])

const stats = computed(() => [
    {
        label: 'Kehadiran',
        value: `${attendanceRate.value}%`,
        sub: '30 Hari Terakhir',
        icon: CheckCircleIcon,
        bg: 'bg-emerald-50',
        color: 'text-emerald-600',
    },
    {
        label: 'Sesi Bulan Ini',
        value: sessionsThisMonth.value,
        sub: 'Total Pembelajaran',
        icon: AcademicCapIcon,
        bg: 'bg-amber-50',
        color: 'text-amber-600',
    },
    {
        label: 'Catatan Baru',
        value: notesThisMonth.value,
        sub: 'Feedback Guru',
        icon: DocumentTextIcon,
        bg: 'bg-sky-50',
        color: 'text-sky-600',
    },
])
</script>

<template>
  <div class="space-y-10">
    <Head title="Dashboard Orang Tua" />

    <!-- Header Section (Premium Upgrade) -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative">
      <div>
        <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600 mb-2 border border-emerald-100">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span>Portal Orang Tua</span>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
             Dashboard <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-[#84994F]">Overview</span>
        </h1>
        <p class="mt-2 text-slate-500 font-medium text-lg">Pantau perkembangan ananda {{ student.name }}.</p>
      </div>
      
       <div class="flex items-center gap-3 p-1.5 bg-white rounded-2xl border border-slate-200 shadow-sm">
             <div class="p-2.5 bg-slate-50 rounded-xl text-[var(--color-primary)]">
                <ClockIcon class="h-6 w-6" />
             </div>
             <div class="pr-3">
                 <p class="text-xs font-bold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-[#84994F] uppercase tracking-wider">Hari ini</p>
                 <p class="text-sm font-bold text-slate-700">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
             </div>
        </div>
    </section>

    <!-- Profile Card (Compact) -->
    <section class="relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 group hover:border-[var(--color-primary)]/30 transition-all">
       <div class="absolute top-0 right-0 -mt-6 -mr-6 h-32 w-32 rounded-full bg-[var(--color-primary-light)] opacity-20 blur-2xl group-hover:opacity-30 transition-opacity"></div>
       
      <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-full bg-slate-100 flex items-center justify-center border-2 border-white shadow-md ring-1 ring-slate-100">
                <UserIcon class="h-7 w-7 text-slate-400" />
            </div>
            <div>
              <p class="text-[10px] font-bold uppercase tracking-widest text-[#84994F]/70 mb-0.5">Profil Siswa</p>
              <h2 class="text-2xl font-extrabold bg-gradient-to-r from-[#84994F] to-[#6b7a3f] bg-clip-text text-transparent tracking-tight">{{ student.name }}</h2>
              <div class="mt-1 flex items-center gap-2 text-xs font-medium text-slate-500">
                 <AcademicCapIcon class="h-3.5 w-3.5 text-[#84994F]" />
                 <span class="font-semibold">{{ student.education_level }}</span>
                 <span class="text-[#84994F]">•</span>
                 <span class="font-semibold">{{ student.school_name }}</span>
              </div>
            </div>
        </div>
        
        <div class="flex flex-col items-end">
          <p class="text-[10px] font-bold bg-gradient-to-r from-emerald-600 to-[#84994F] bg-clip-text text-transparent uppercase tracking-wider mb-1.5">Status Akun</p>
          <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold shadow-sm transition-transform group-hover:scale-105"
            :class="student.status === 'active' ? 'border-emerald-200 text-emerald-700 bg-emerald-50' : 'border-slate-200 text-slate-500 bg-slate-50'">
            <CheckCircleIcon v-if="student.status === 'active'" class="h-3.5 w-3.5" />
            <XCircleIcon v-else class="h-3.5 w-3.5" />
            {{ student.status === 'active' ? 'Siswa Aktif' : 'Nonaktif' }}
          </span>
        </div>
      </div>
    </section>

    <!-- Stats Grid -->
    <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div v-for="stat in stats" :key="stat.label" 
             class="group flex items-center justify-between rounded-[2rem] bg-white p-8 shadow-sm border border-slate-200 transition-all hover:shadow-lg hover:-translate-y-1 hover:border-emerald-200">
             <div>
                 <p class="text-xs font-bold uppercase tracking-wider bg-gradient-to-r from-[#84994F] to-[#6b7a3f] bg-clip-text text-transparent">{{ stat.label }}</p>
                 <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stat.value }}</p>
                 <p class="mt-1 text-xs font-medium text-[#84994F]/60">{{ stat.sub }}</p>
             </div>
             <div :class="[stat.bg, stat.color]" class="rounded-2xl p-4 shadow-sm ring-4 ring-slate-50 group-hover:ring-emerald-50 transition-all">
                 <component :is="stat.icon" class="h-8 w-8" />
             </div>
        </div>
    </section>

    <!-- Content Grid (Restructured: Left = Notes, Right = Schedule + Attendance) -->
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        
      <!-- Recent Notes (Left Column) -->
      <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-2.5 group/header">
             <div class="rounded-lg bg-gradient-to-br from-[#84994F]/20 to-[#6b7a3f]/20 border border-[#84994F]/30 p-1.5 text-[#84994F] group-hover/header:scale-110 group-hover/header:shadow-md transition-all">
                 <DocumentTextIcon class="h-5 w-5" />
             </div>
             <div>
                <h3 class="text-lg font-bold bg-gradient-to-r from-slate-900 to-[#84994F] bg-clip-text text-transparent group-hover/header:from-[#84994F] group-hover/header:to-[#6b7a3f] transition-all">Catatan Terbaru</h3>
                <p class="text-xs text-[#84994F]/60 font-medium">Umpan balik Guru</p>
             </div>
          </div>
          <Link :href="route('orang-tua.notes.index')" class="flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-bold text-emerald-600 transition hover:bg-emerald-50 hover:text-emerald-700">
            <span>Semua</span>
            <ArrowRightIcon class="h-3.5 w-3.5" />
          </Link>
        </div>
        
        <div class="space-y-3 flex-1">
          <article v-for="note in noteFallback" :key="note.id" class="group/note rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition-all hover:bg-white hover:shadow-md hover:border-[#84994F]/30 hover:-translate-y-0.5 cursor-pointer">
            <div class="flex items-start justify-between">
                <div>
                    <h4 class="text-sm font-bold text-slate-900 group-hover/note:text-[#84994F] transition-colors">{{ note.title }}</h4>
                    <p class="text-[10px] font-bold text-slate-400 group-hover/note:text-[#84994F]/70 uppercase tracking-wider mt-0.5 transition-colors">{{ note.recorded_at_readable }}</p>
                </div>
                <span class="rounded-md bg-emerald-100 px-1.5 py-0.5 text-[9px] font-bold text-emerald-700 uppercase group-hover/note:bg-[#84994F] group-hover/note:text-white transition-all group-hover/note:scale-105">Feedback</span>
            </div>
            
            <p class="mt-3 text-xs leading-relaxed text-slate-600 line-clamp-2">{{ note.note }}</p>
            
            <div class="mt-3 flex items-center gap-1.5">
               <div class="h-5 w-5 rounded-full bg-white border border-slate-200 flex items-center justify-center text-[9px] font-bold text-slate-500 shadow-sm">
                   {{ note.teacher?.name?.charAt(0) ?? 'G' }}
               </div>
               <p class="text-[10px] font-bold text-[#84994F] group-hover/note:text-[#6b7a3f] transition-colors">
                  {{ note.teacher?.name ?? 'Guru' }}
               </p>
            </div>
          </article>
        </div>
      </div>

      <!-- Right Column: Schedule + Attendance -->
      <div class="space-y-6">
        <!-- Upcoming Schedule (Top Right) -->
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
          <div class="flex items-center gap-2.5 mb-4 group/header">
               <div class="rounded-lg bg-gradient-to-br from-[#84994F]/20 to-[#6b7a3f]/20 border border-[#84994F]/30 p-1.5 text-[#84994F] group-hover/header:scale-110 group-hover/header:shadow-md transition-all">
                   <CalendarIcon class="h-5 w-5" />
               </div>
               <div>
                  <h3 class="text-lg font-bold bg-gradient-to-r from-slate-900 to-[#84994F] bg-clip-text text-transparent group-hover/header:from-[#84994F] group-hover/header:to-[#6b7a3f] transition-all">Jadwal</h3>
                  <p class="text-xs text-[#84994F]/60 font-medium">Kelas mendatang</p>
               </div>
          </div>

          <div class="space-y-3">
            <article v-for="schedule in scheduleFallback.slice(0, 2)" :key="schedule.id" 
                  class="group/schedule rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition-all hover:bg-white hover:shadow-md hover:border-[#84994F]/30 hover:-translate-y-0.5 cursor-pointer">
              <div class="flex items-start justify-between">
                  <span class="rounded-md bg-blue-50 px-1.5 py-0.5 text-[9px] font-bold text-blue-600 uppercase group-hover/schedule:bg-[#84994F] group-hover/schedule:text-white transition-all group-hover/schedule:scale-105">{{ schedule.subject }}</span>
                  <div class="flex items-center gap-2">
                      <span v-if="computeScheduleStatus(schedule) === 'Berlangsung'" class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                      </span>
                      <span v-else-if="computeScheduleStatus(schedule) === 'Akan Datang'" class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                      <span v-else class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                  </div>
              </div>
              
              <h4 class="mt-2 text-sm font-bold text-slate-900 leading-tight line-clamp-1 group-hover/schedule:text-[#84994F] transition-colors">{{ schedule.topic }}</h4>
              
              <div class="mt-2 flex items-center justify-between">
                  <div class="flex items-center gap-1.5 text-[10px] font-medium text-slate-500">
                    <ClockIcon class="h-3 w-3 group-hover/schedule:text-[#84994F] transition-colors" />
                    <span class="text-slate-600 font-bold group-hover/schedule:text-[#84994F] transition-colors">{{ schedule.start_time ? formatTime(schedule.start_time) : '-' }}</span>
                  </div>
                  <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border uppercase tracking-wider" :class="statusBadgeClass(computeScheduleStatus(schedule))">
                      {{ computeScheduleStatus(schedule) }}
                  </span>
              </div>
            </article>
          </div>
        
          <Link :href="route('orang-tua.schedules.index')" class="mt-4 flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300">
            <span>Lihat Semua</span>
            <ArrowRightIcon class="h-3.5 w-3.5" />
          </Link>
        </div>

        <!-- Attendance History (Bottom Right) -->
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center gap-2.5 mb-4 group/header">
               <div class="rounded-lg bg-gradient-to-br from-[#84994F]/20 to-[#6b7a3f]/20 border border-[#84994F]/30 p-1.5 text-[#84994F] group-hover/header:scale-110 group-hover/header:shadow-md transition-all">
                   <CheckCircleIcon class="h-5 w-5" />
               </div>
               <div>
                  <h3 class="text-lg font-bold bg-gradient-to-r from-slate-900 to-[#84994F] bg-clip-text text-transparent group-hover/header:from-[#84994F] group-hover/header:to-[#6b7a3f] transition-all">Riwayat Kehadiran</h3>
                  <p class="text-xs text-[#84994F]/60 font-medium">Terakhir</p>
               </div>
          </div>

          <div class="space-y-3">
            <article
              v-for="attendance in attendances.slice(0, 3)"
              :key="attendance.id"
              class="group/attendance rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition-all hover:bg-white hover:shadow-md hover:border-[#84994F]/30 hover:-translate-y-0.5 cursor-pointer"
            >
              <div class="flex items-center justify-between mb-2">
                 <span class="rounded-md px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide border shadow-sm"
                    :class="{
                      'bg-emerald-50 text-emerald-700 border-emerald-100': attendance.status === 'Hadir',
                      'bg-blue-50 text-blue-700 border-blue-100': attendance.status === 'Izin',
                      'bg-amber-50 text-amber-700 border-amber-100': attendance.status === 'Sakit',
                      'bg-rose-50 text-rose-700 border-rose-100': attendance.status === 'Alpha',
                    }">
                    {{ attendance.status }}
                 </span>
                 <div class="flex items-center gap-1 text-[10px] font-medium text-slate-500">
                     <ClockIcon class="h-3 w-3" />
                     <span v-if="attendance.start_time_raw">
                          {{ formatTime(attendance.start_time_raw) }}
                     </span>
                     <span v-else>
                          {{ attendance.session_time }}
                     </span>
                 </div>
              </div>
              
              <p class="text-sm font-bold text-slate-900 line-clamp-1 group-hover/attendance:text-[#84994F] transition-colors">{{ attendance.session_topic ?? 'Pertemuan' }}</p>
              
              <p v-if="attendance.notes" class="mt-2 text-[10px] text-slate-500 italic bg-white p-2 rounded-md border border-slate-200 line-clamp-2">
                  "{{ attendance.notes }}"
              </p>
            </article>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>