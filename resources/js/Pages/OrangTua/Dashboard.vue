<script setup>
import { computed } from 'vue'
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
// -----------------------------------------

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
             Dashboard <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-[var(--color-primary)]">Overview</span>
        </h1>
        <p class="mt-2 text-slate-500 font-medium text-lg">Pantau perkembangan ananda {{ student.name }}.</p>
      </div>
      
       <div class="flex items-center gap-3 p-1.5 bg-white rounded-2xl border border-slate-200 shadow-sm">
             <div class="p-2.5 bg-slate-50 rounded-xl text-[var(--color-primary)]">
                <ClockIcon class="h-6 w-6" />
             </div>
             <div class="pr-3">
                 <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hari ini</p>
                 <p class="text-sm font-bold text-slate-700">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
             </div>
        </div>
    </section>

    <!-- Profile Card (Premium Glass Effect) -->
    <section class="relative overflow-hidden rounded-[2.5rem] bg-white p-10 shadow-sm border border-slate-200 group hover:border-[var(--color-primary)]/30 transition-all">
       <div class="absolute top-0 right-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-[var(--color-primary-light)] opacity-20 blur-3xl group-hover:opacity-30 transition-opacity"></div>
       
      <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-6">
            <div class="h-24 w-24 rounded-full bg-slate-100 flex items-center justify-center border-4 border-white shadow-md ring-1 ring-slate-100">
                <UserIcon class="h-10 w-10 text-slate-400" />
            </div>
            <div>
              <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-1">Profil Siswa</p>
              <h2 class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ student.name }}</h2>
              <div class="mt-2 flex items-center gap-2 text-sm font-medium text-slate-500">
                 <AcademicCapIcon class="h-4 w-4" />
                 <span>{{ student.education_level }}</span>
                 <span>•</span>
                 <span>{{ student.school_name }}</span>
              </div>
            </div>
        </div>
        
        <div class="flex flex-col items-end">
          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status Akun</p>
          <span class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm font-bold shadow-sm transition-transform group-hover:scale-105"
            :class="student.status === 'active' ? 'border-emerald-200 text-emerald-700 bg-emerald-50' : 'border-slate-200 text-slate-500 bg-slate-50'">
            <CheckCircleIcon v-if="student.status === 'active'" class="h-4 w-4" />
            <XCircleIcon v-else class="h-4 w-4" />
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
                 <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ stat.label }}</p>
                 <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stat.value }}</p>
                 <p class="mt-1 text-xs font-medium text-slate-400">{{ stat.sub }}</p>
             </div>
             <div :class="[stat.bg, stat.color]" class="rounded-2xl p-4 shadow-sm ring-4 ring-slate-50 group-hover:ring-emerald-50 transition-all">
                 <component :is="stat.icon" class="h-8 w-8" />
             </div>
        </div>
    </section>

    <!-- Content Grid -->
    <section class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        
      <!-- Recent Notes -->
      <div class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm lg:col-span-2 flex flex-col">
        <div class="flex items-center justify-between mb-8">
          <div class="flex items-center gap-3">
             <div class="rounded-xl bg-slate-100 p-2 text-slate-600">
                 <DocumentTextIcon class="h-6 w-6" />
             </div>
             <div>
                <h3 class="text-xl font-bold text-slate-900">Catatan Terbaru</h3>
                <p class="text-sm text-slate-500 font-medium">Umpan balik & Laporan Guru</p>
             </div>
          </div>
          <Link :href="route('orang-tua.notes.index')" class="flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-2 text-sm font-bold text-emerald-600 transition hover:bg-emerald-50 hover:text-emerald-700">
            <span>Semua Catatan</span>
            <ArrowRightIcon class="h-4 w-4" />
          </Link>
        </div>
        
        <div class="space-y-4 flex-1">
          <article v-for="note in noteFallback" :key="note.id" class="rounded-[1.5rem] border border-slate-200 bg-slate-50/50 p-6 transition hover:bg-white hover:shadow-sm hover:border-slate-200">
            <div class="flex items-start justify-between">
                <div>
                    <h4 class="text-lg font-bold text-slate-900">{{ note.title }}</h4>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">{{ note.recorded_at_readable }}</p>
                </div>
                <span class="rounded-lg bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700 uppercase">Feedback</span>
            </div>
            
            <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ note.note }}</p>
            
            <div class="mt-4 flex items-center gap-2">
               <div class="h-6 w-6 rounded-full bg-white border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 shadow-sm">
                   {{ note.teacher?.name?.charAt(0) ?? 'G' }}
               </div>
               <p class="text-xs font-bold text-slate-500">
                  {{ note.teacher?.name ?? 'Guru' }}
               </p>
            </div>
          </article>
        </div>
      </div>

      <!-- Upcoming Schedule -->
      <div class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm flex flex-col">
        <div class="flex items-center gap-3 mb-6">
             <div class="rounded-xl bg-slate-100 p-2 text-slate-600">
                 <CalendarIcon class="h-6 w-6" />
             </div>
             <div>
                <h3 class="text-xl font-bold text-slate-900">Jadwal</h3>
                <p class="text-sm text-slate-500 font-medium">Kelas mendatang</p>
             </div>
        </div>

        <div class="flex-1 space-y-4">
          <article v-for="schedule in scheduleFallback" :key="schedule.id" 
                class="rounded-[1.5rem] border border-slate-200 bg-slate-50/50 p-5 group transition hover:bg-white hover:shadow-sm hover:border-slate-200">
            <div class="flex items-start justify-between">
                <span class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-bold text-blue-600 uppercase">{{ schedule.subject }}</span>
                <span v-if="schedule.status === 'scheduled'" class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
            </div>
            
            <h4 class="mt-3 text-base font-bold text-slate-900 leading-tight">{{ schedule.topic }}</h4>
            
            <div class="mt-3 flex items-center gap-2 text-xs font-medium text-slate-500">
                <CalendarIcon class="h-3.5 w-3.5" />
                <span>{{ schedule.start_time ? formatDate(schedule.start_time) : '-' }}</span>
            </div>
             <div class="mt-1 flex items-center gap-2 text-xs font-medium text-slate-500">
                <ClockIcon class="h-3.5 w-3.5" />
                <span class="text-emerald-600 font-bold">{{ schedule.start_time ? formatTime(schedule.start_time) : '-' }}</span>
            </div>
          </article>
        </div>
        
        <div v-if="nextSchedule" class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
           <div class="flex items-start gap-3">
               <div class="rounded-full bg-white p-1.5 text-emerald-600 shadow-sm border border-emerald-100">
                   <ClockIcon class="h-4 w-4" />
               </div>
               <div>
                   <p class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Segera Dimulai</p>
                   <p class="text-sm font-bold text-emerald-700 mt-1">{{ nextSchedule.topic }}</p>
                   <p class="text-xs text-emerald-600 mt-0.5">{{ nextSchedule.start_time ? formatTime(nextSchedule.start_time) : '' }}</p>
               </div>
           </div>
        </div>
        
        <Link :href="route('orang-tua.schedules.index')" class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300">
          <span>Lihat Jadwal Lengkap</span>
        </Link>
      </div>
    </section>

    <!-- Attendance History -->
    <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
      <div class="flex items-center justify-between mb-8">
         <div class="flex items-center gap-3">
             <div class="rounded-xl bg-slate-100 p-2 text-slate-600">
                 <CheckCircleIcon class="h-6 w-6" />
             </div>
             <div>
                <h3 class="text-xl font-bold text-slate-900">Riwayat Kehadiran</h3>
                <p class="text-sm text-slate-500 font-medium">5 Pertemuan Terakhir</p>
             </div>
         </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <article
          v-for="attendance in attendances"
          :key="attendance.id"
          class="rounded-[1.5rem] border border-slate-200 bg-slate-50/50 p-6 transition hover:bg-white hover:shadow-sm hover:border-slate-200"
        >
          <div class="flex items-center justify-between mb-4">
             <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                 <CalendarIcon class="h-3.5 w-3.5" />
                 <span>{{ attendance.attendance_date ? formatDate(attendance.attendance_date) : '-' }}</span>
             </div>
             <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide border shadow-sm"
                :class="attendance.status === 'present' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-700 border-rose-100'">
                {{ attendance.status === 'present' ? 'HADIR' : attendance.status }}
             </span>
          </div>
          
          <p class="text-base font-bold text-slate-900 line-clamp-2 min-h-[3rem]">{{ attendance.session_topic ?? 'Pertemuan' }}</p>
          
          <div class="mt-4 pt-4 border-t border-slate-200 flex items-center justify-between">
               <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5">
                   <ClockIcon class="h-3.5 w-3.5" />
                   <span v-if="attendance.start_time_raw">
                        {{ formatTime(attendance.start_time_raw) }} - {{ attendance.end_time_raw ? formatTime(attendance.end_time_raw) : 'Selesai' }}
                   </span>
                   <span v-else>
                        {{ attendance.session_time }}
                   </span>
               </p>
          </div>
          
          <p v-if="attendance.notes" class="mt-3 text-xs text-slate-500 italic bg-white p-2 rounded-lg border border-slate-200">
              "{{ attendance.notes }}"
          </p>
        </article>
      </div>
    </section>
  </div>
</template>