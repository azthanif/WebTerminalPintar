<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import ParentLayout from '@/Layouts/ParentLayout.vue'
import SummaryCard from '@/Components/Parent/SummaryCard.vue'
import { useParentDashboard } from '@/Composables/useParentDashboard'

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
</script>

<template>
  <div class="space-y-10">
    <Head title="Dashboard Orang Tua" />

    <section class="rounded-3xl bg-white/80 p-8 shadow-sm border border-slate-100">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Nama Anak</p>
          <h2 class="text-3xl font-bold text-slate-900">{{ student.name }}</h2>
          <p class="text-sm text-slate-500">{{ student.education_level }} · {{ student.school_name }}</p>
        </div>
        <div class="text-sm text-slate-500">
          <p>Status</p>
          <span class="mt-1 inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold"
            :class="student.status === 'active' ? 'border-emerald-200 text-emerald-600 bg-emerald-50' : 'border-slate-200 text-slate-500'">
            {{ student.status === 'active' ? 'Aktif' : 'Nonaktif' }}
          </span>
        </div>
      </div>
    </section>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
      <SummaryCard title="Persentase Kehadiran" subtitle="30 Hari Terakhir" :value="`${attendanceRate}%`" accent="emerald" />
      <SummaryCard title="Sesi Bulan Ini" subtitle="Total Pembelajaran" :value="sessionsThisMonth" accent="amber" />
      <SummaryCard title="Catatan Baru" subtitle="Feedback Guru" :value="notesThisMonth" accent="sky" />
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:col-span-2">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-slate-900">Catatan Terbaru</h3>
            <p class="text-sm text-slate-500">Umpan balik langsung dari guru</p>
          </div>
          <Link :href="route('orang-tua.notes.index')" class="text-sm font-semibold text-emerald-600 hover:text-emerald-500">
            Semua Catatan →
          </Link>
        </div>
        <div class="mt-6 space-y-5">
          <article v-for="note in noteFallback" :key="note.id" class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5">
            <h4 class="text-lg font-semibold text-slate-900">{{ note.title }}</h4>
            <p class="mt-1 text-xs text-slate-500">{{ note.recorded_at_readable }}</p>
            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ note.note }}</p>
            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.3em] text-emerald-500">
              {{ note.teacher?.name ?? 'Guru' }}
            </p>
          </article>
        </div>
      </div>

      <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
        <h3 class="text-xl font-bold text-slate-900">Jadwal Mendatang</h3>
        <p class="text-sm text-slate-500">Pantau pertemuan berikutnya</p>
        <div class="mt-6 space-y-4">
          <article v-for="schedule in scheduleFallback" :key="schedule.id" class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">{{ schedule.subject }}</p>
            <h4 class="mt-2 text-lg font-bold text-slate-900">{{ schedule.topic }}</h4>
            
            <p class="mt-1 text-xs text-slate-500 font-medium">
               {{ schedule.start_time ? formatDate(schedule.start_time) : '-' }} • 
               <span class="text-emerald-600">
                 {{ schedule.start_time ? formatTime(schedule.start_time) : '-' }}
               </span>
            </p>
            
            <p class="mt-2 text-sm text-slate-600">Lokasi: {{ schedule.location ?? '-' }}</p>
          </article>
        </div>
        
        <div v-if="nextSchedule" class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 text-sm text-emerald-700">
          Jadwal terdekat: {{ nextSchedule.topic }} • 
          {{ nextSchedule.start_time ? formatTime(nextSchedule.start_time) : '' }}
        </div>
        
        <Link :href="route('orang-tua.schedules.index')" class="mt-6 block text-center text-sm font-semibold text-emerald-600">
          Lihat Jadwal Lengkap →
        </Link>
      </div>
    </section>

    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-slate-900">Riwayat Kehadiran</h3>
        <span class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">5 Pertemuan Terakhir</span>
      </div>
      <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
        <article
          v-for="attendance in attendances"
          :key="attendance.id"
          class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4"
        >
          <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em]">
            <span>{{ attendance.attendance_date ? formatDate(attendance.attendance_date) : '-' }}</span>
            <span :class="attendance.status === 'present' ? 'text-emerald-500' : 'text-rose-500'">
              {{ attendance.status === 'present' ? 'HADIR' : attendance.status }}
            </span>
          </div>
          <p class="mt-2 text-lg font-bold text-slate-900">{{ attendance.session_topic ?? 'Pertemuan' }}</p>
          
          <p class="text-sm text-slate-600 mt-1">
             <span v-if="attendance.start_time_raw">
                {{ formatTime(attendance.start_time_raw) }} - {{ attendance.end_time_raw ? formatTime(attendance.end_time_raw) : 'Selesai' }}
             </span>
             <span v-else>
                {{ attendance.session_time }}
             </span>
          </p>
          
          <p class="mt-3 text-sm text-slate-500">{{ attendance.notes }}</p>
        </article>
      </div>
    </section>
  </div>
</template>