<script setup>
import { Head } from '@inertiajs/vue3'
import GuruLayout from '@/Layouts/GuruLayout.vue'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import {
  DocumentTextIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  TrashIcon,
  ClipboardDocumentCheckIcon,
  CheckCircleIcon,
  ClockIcon,
  XCircleIcon,
  ExclamationCircleIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

defineOptions({ layout: GuruLayout })

const props = defineProps({
  initialSubject: {
    type: String,
    default: null,
  },
  initialScheduleDate: {
    type: String,
    default: null,
  },
})

const perPage = 10
const attendanceStatusOptions = ['Semua', 'Hadir', 'Izin', 'Sakit', 'Alpha']
const attendanceInputStatuses = ['Hadir', 'Izin', 'Sakit', 'Alpha']

const normalizedInitialSubject =
  typeof props.initialSubject === 'string' && props.initialSubject.trim().length ? props.initialSubject.trim() : 'Semua'
const normalizedScheduleDate = props.initialScheduleDate ?? new Date().toISOString().slice(0, 10)

const attendanceFilters = reactive({
  query: '',
  status: 'Semua',
  subject: normalizedInitialSubject,
  page: 1,
  scheduleDate: normalizedScheduleDate,
})

const attendanceSummary = reactive({ Hadir: 0, Izin: 0, Sakit: 0, Alpha: 0 })
const attendanceRecords = ref([])
const pagination = reactive({ total: 0, lastPage: 1, currentPage: 1 })
const isLoadingAttendance = ref(false)
const successMessage = ref('')
const subjectOptions = ref([])

const scheduleLock = reactive({
  isLocked: false,
  secondsRemaining: 0,
  startsAt: null,
  label: '',
})
let countdownTimer = null

const showNoteModal = ref(false)
const activeNoteRecord = ref(null)
const noteForm = reactive({ description: '' })

const attendanceSummaryCards = computed(() => [
  {
    id: 'hadir',
    label: 'Hadir',
    value: attendanceSummary.Hadir ?? 0,
    subtitle: 'Siswa hadir tepat waktu',
    badge: 'bg-emerald-50 text-emerald-700',
    icon: CheckCircleIcon,
    bgGradient: 'bg-gradient-to-br from-emerald-500 to-teal-400',
    shadowColor: 'shadow-emerald-200',
    textColor: 'text-white'
  },
  {
    id: 'izin',
    label: 'Izin',
    value: attendanceSummary.Izin ?? 0,
    subtitle: 'Butuh persetujuan wali',
    badge: 'bg-blue-50 text-blue-700',
    icon: ClipboardDocumentCheckIcon,
    bgGradient: 'bg-gradient-to-br from-blue-500 to-indigo-400',
    shadowColor: 'shadow-blue-200',
    textColor: 'text-white'
  },
  {
    id: 'sakit',
    label: 'Sakit',
    value: attendanceSummary.Sakit ?? 0,
    subtitle: 'Update status setelah pulih',
    badge: 'bg-amber-50 text-amber-700',
    icon: ExclamationCircleIcon,
    bgGradient: 'bg-gradient-to-br from-amber-400 to-orange-400',
    shadowColor: 'shadow-amber-200',
    textColor: 'text-white'
  },
  {
    id: 'alpha',
    label: 'Alpha',
    value: attendanceSummary.Alpha ?? 0,
    subtitle: 'Perlu tindak lanjut segera',
    badge: 'bg-rose-50 text-rose-700',
    icon: XCircleIcon,
    bgGradient: 'bg-gradient-to-br from-rose-500 to-pink-500',
    shadowColor: 'shadow-rose-200',
    textColor: 'text-white'
  },
])

const attendanceStartIndex = computed(() => (attendanceFilters.page - 1) * perPage + 1)

const attendanceRangeLabel = computed(() => {
  if (!pagination.total) {
    return 'Belum ada data kehadiran'
  }
  const start = attendanceStartIndex.value
  const end = Math.min(attendanceFilters.page * perPage, pagination.total)
  return `Menampilkan ${start}-${end} dari ${pagination.total} entri`
})

const statusBadgeClass = (status) => {
  const map = {
    Hadir: 'bg-emerald-50 text-emerald-700 border-emerald-100',
    Izin: 'bg-blue-50 text-blue-700 border-blue-100',
    Sakit: 'bg-amber-50 text-amber-700 border-amber-100',
    Alpha: 'bg-rose-50 text-rose-700 border-rose-100',
  }
  return map[status] ?? 'bg-slate-100 text-slate-500 border-slate-200'
}

const normalizeStatusValue = (value) => value ?? ''

const isRecordDirty = (record) => normalizeStatusValue(record.draftStatus) !== normalizeStatusValue(record.status)

const truncateNote = (text, limit = 80) => {
  if (!text) return ''
  return text.length > limit ? `${text.slice(0, limit)}…` : text
}

const setSuccessMessage = (message) => {
  successMessage.value = message
  if (!message) return
  setTimeout(() => {
    successMessage.value = ''
  }, 3000)
}

const clearCountdownTimer = () => {
  if (countdownTimer) {
    clearInterval(countdownTimer)
    countdownTimer = null
  }
}

const startCountdownTimer = () => {
  clearCountdownTimer()
  if (scheduleLock.secondsRemaining <= 0) return
  countdownTimer = setInterval(() => {
    if (scheduleLock.secondsRemaining <= 1) {
      scheduleLock.secondsRemaining = 0
      scheduleLock.isLocked = false
      clearCountdownTimer()
      fetchAttendance()
    } else {
      scheduleLock.secondsRemaining -= 1
    }
  }, 1000)
}

const applyScheduleWindow = (windowData) => {
  if (!windowData) {
    clearCountdownTimer()
    scheduleLock.isLocked = false
    scheduleLock.secondsRemaining = 0
    scheduleLock.startsAt = null
    scheduleLock.label = ''
    return
  }

  scheduleLock.startsAt = windowData.starts_at ?? null
  const rawSeconds = Number(windowData.seconds_until ?? 0)
  scheduleLock.secondsRemaining = Math.max(0, Math.floor(isNaN(rawSeconds) ? 0 : rawSeconds))
  scheduleLock.isLocked = scheduleLock.secondsRemaining > 0
  scheduleLock.label = windowData.schedule_label ?? attendanceFilters.subject

  if (scheduleLock.isLocked) {
    startCountdownTimer()
  } else {
    clearCountdownTimer()
  }
}

const formatCountdown = (seconds) => {
  const total = Math.max(0, Number(seconds) || 0)
  const hours = Math.floor(total / 3600)
  const minutes = Math.floor((total % 3600) / 60)
  const secs = total % 60
  const segments = []
  if (hours > 0) {
    segments.push(String(hours).padStart(2, '0'))
  }
  segments.push(String(minutes).padStart(2, '0'))
  segments.push(String(secs).padStart(2, '0'))
  return segments.join(':')
}

const isScheduleLocked = computed(() => scheduleLock.isLocked)
const scheduleCountdownLabel = computed(() => formatCountdown(scheduleLock.secondsRemaining))
const scheduleStartTimeLabel = computed(() => {
  if (!scheduleLock.startsAt) return ''
  return new Date(scheduleLock.startsAt).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
})

const normalizeRecord = (record) => {
  const sessionSubject = record.session_subject ?? record.schedule?.subject ?? ''
  const sessionTopic = record.session_topic ?? record.schedule?.topic ?? ''
  const sessionLabel = [sessionSubject, sessionTopic].filter(Boolean).join(' - ') || 'Sesi tanpa judul'

  return {
    id: record.id,
    student: record.student,
    schedule: record.schedule,
    schedule_id: record.schedule_id ?? record.schedule?.id,
    student_id: record.student_id ?? record.student?.id,
    attendance_date: record.attendance_date,
    status: record.status ?? null,
    draftStatus: record.status ?? '',
    notes: record.notes ?? '',
    noteDraft: record.notes ?? '',
    session_label: sessionLabel,
    session_time: record.session_time ?? record.schedule?.start_time,
    saving: false,
  }
}

const fetchAttendance = async () => {
  isLoadingAttendance.value = true
  try {
    const { data } = await axios.get(route('guru.api.attendance.index'), {
      params: {
        search: attendanceFilters.query,
        status: attendanceFilters.status,
        subject: attendanceFilters.subject === 'Semua' ? undefined : attendanceFilters.subject,
        schedule_date: attendanceFilters.scheduleDate,
        page: attendanceFilters.page,
        per_page: perPage,
      },
    })

    attendanceRecords.value = data.data.map(normalizeRecord)
    pagination.total = data.meta.total || 0
    pagination.lastPage = data.meta.last_page || 1
    pagination.currentPage = data.meta.current_page || attendanceFilters.page

    const summary = data.summary || {}
    attendanceSummary.Hadir = summary.Hadir ?? 0
    attendanceSummary.Izin = summary.Izin ?? 0
    attendanceSummary.Sakit = summary.Sakit ?? 0
    attendanceSummary.Alpha = summary.Alpha ?? 0

    subjectOptions.value = data.subjects ?? []
    if (data.schedule_date) {
      attendanceFilters.scheduleDate = data.schedule_date
    }
    if (attendanceFilters.subject !== 'Semua' && !subjectOptions.value.includes(attendanceFilters.subject)) {
      attendanceFilters.subject = 'Semua'
    }
    applyScheduleWindow(data.schedule_window ?? null)
  } finally {
    isLoadingAttendance.value = false
  }
}

const changeAttendancePage = (direction) => {
  const next = attendanceFilters.page + direction
  if (next < 1 || next > pagination.lastPage) return
  attendanceFilters.page = next
  fetchAttendance()
}

let searchTimer
watch(
  () => attendanceFilters.query,
  () => {
    clearTimeout(searchTimer)
    attendanceFilters.page = 1
    searchTimer = setTimeout(fetchAttendance, 350)
  }
)

watch(
  () => attendanceFilters.status,
  () => {
    attendanceFilters.page = 1
    fetchAttendance()
  }
)

watch(
  () => attendanceFilters.subject,
  () => {
    attendanceFilters.page = 1
    applyScheduleWindow(null)
    fetchAttendance()
  }
)

const saveAttendance = async (record, overrides = {}) => {
  if (isScheduleLocked.value) return
  if (!record?.schedule_id || !record?.student_id) {
    return
  }
  const nextStatus = overrides.status ?? record.draftStatus ?? ''
  if (!nextStatus) {
    setSuccessMessage('Pilih status kehadiran terlebih dahulu.')
    return
  }
  record.saving = true
  try {
    const payload = {
      schedule_id: record.schedule_id,
      student_id: record.student_id,
      attendance_date: record.attendance_date,
      status: nextStatus,
      notes: overrides.notes ?? record.noteDraft ?? record.notes ?? '',
    }

    const { data } = await axios.post(route('guru.api.attendance.store'), payload)
    Object.assign(record, normalizeRecord(data))
    setSuccessMessage('Kehadiran berhasil diperbarui.')
    await fetchAttendance()
  } finally {
    record.saving = false
  }
}

const removeAttendance = async (record) => {
  if (isScheduleLocked.value || !record?.id) return
  await axios.delete(route('guru.api.attendance.destroy', record.id))
  setSuccessMessage('Data kehadiran dihapus.')
  fetchAttendance()
}

const openNoteModal = (record) => {
  if (isScheduleLocked.value) return
  activeNoteRecord.value = record
  noteForm.description = record.noteDraft ?? record.notes ?? ''
  showNoteModal.value = true
}

const closeNoteModal = () => {
  showNoteModal.value = false
  activeNoteRecord.value = null
  noteForm.description = ''
}

const saveNote = async () => {
  if (!activeNoteRecord.value) return
  await saveAttendance(activeNoteRecord.value, { notes: noteForm.description })
  closeNoteModal()
}

onMounted(() => {
  fetchAttendance()
})
onBeforeUnmount(() => {
  clearTimeout(searchTimer)
  clearCountdownTimer()
})
</script>

<template>
  <div class="space-y-8">
    <Head title="Kehadiran" />

    <!-- Header Section -->
    <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
      <div>
         <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-600 mb-2 border border-orange-100 shadow-sm">
            <ClockIcon class="h-3 w-3" />
            <span>Manajemen Presensi</span>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
             Rekapitulasi <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">Kehadiran</span>
        </h1>
        <p class="mt-2 text-slate-500 font-medium text-lg max-w-2xl">
            Pantau kehadiran siswa secara realtime dan kelola catatan perkembangan kelas.
        </p>
      </div>
      
      <!-- Filter Mapel (More Colorful) -->
      <div class="min-w-[200px] group">
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 group-hover:text-orange-500 transition-colors">Filter Mapel</label>
        <div class="relative">
             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <DocumentTextIcon class="h-5 w-5 group-hover:text-orange-400" />
            </div>
             <select
                v-model="attendanceFilters.subject"
                class="w-full appearance-none rounded-2xl border border-slate-200 bg-white pl-10 pr-10 py-3 text-sm font-bold text-slate-700 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all cursor-pointer hover:border-orange-200"
            >
                <option value="Semua">Semua Pelajaran</option>
                <option v-for="subject in subjectOptions" :key="subject" :value="subject">
                {{ subject }}
                </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
      </div>
    </section>

    <!-- Summary Cards (Colorful Gradient) -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="card in attendanceSummaryCards" :key="card.id" 
             :class="['group relative overflow-hidden rounded-[2rem] p-6 shadow-md border-0 transition-all hover:scale-[1.02]', card.bgGradient, card.shadowColor]">
           
           <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

           <div class="flex items-start justify-between relative z-10">
                <div>
                   <p class="text-xs font-bold uppercase tracking-widest text-white/80">{{ card.label }}</p>
                   <p class="mt-2 text-4xl font-extrabold text-white drop-shadow-sm">{{ card.value }}</p>
                </div>
                 <div class="rounded-2xl p-2.5 bg-white/20 backdrop-blur-sm border border-white/20 shadow-sm">
                     <component :is="card.icon" class="h-6 w-6 text-white" />
                 </div>
           </div>
           <p class="mt-4 text-xs font-medium text-white/90 flex items-center gap-1.5 relative z-10">
               <span class="inline-block h-1.5 w-1.5 rounded-full bg-white"></span>
               {{ card.subtitle }}
           </p>
        </div>
    </div>

    <!-- Main Content Card -->
    <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
        <div class="mb-8 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div>
                 <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <UserGroupIcon class="h-6 w-6 text-orange-500" />
                    Daftar Siswa
                </h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Kelola status kehadiran dan catatan perkembangan.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                 <div class="relative w-full sm:w-64 group">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-hover:text-orange-400 transition-colors">
                        <MagnifyingGlassIcon class="h-5 w-5" />
                    </div>
                    <input
                        v-model="attendanceFilters.query"
                        type="text"
                        placeholder="Cari nama siswa..."
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all hover:bg-white hover:shadow-sm"
                    />
                </div>
                 <div class="relative w-full sm:w-48 group">
                     <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-hover:text-orange-400 transition-colors">
                        <FunnelIcon class="h-5 w-5" />
                    </div>
                    <select 
                        v-model="attendanceFilters.status" 
                        class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-10 py-2.5 text-sm font-bold text-slate-700 focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all cursor-pointer hover:bg-white hover:shadow-sm"
                    >
                        <option v-for="option in attendanceStatusOptions" :key="option" :value="option">
                        {{ option }}
                        </option>
                    </select>
                     <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <div v-if="successMessage" class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-100 p-4 flex items-center gap-3 shadow-sm">
             <CheckCircleIcon class="h-6 w-6 text-emerald-500" />
            <p class="text-sm font-bold text-emerald-700">{{ successMessage }}</p>
        </div>

        <div v-if="isScheduleLocked" class="mb-6 rounded-2xl bg-amber-50 border border-amber-100 p-4 flex items-start gap-3 shadow-sm">
             <ClockIcon class="h-6 w-6 text-amber-500 mt-0.5" />
             <div>
                 <p class="font-bold text-amber-800 text-sm">Presensi Terjadwal</p>
                 <p class="mt-1 text-xs text-amber-700 font-medium">
                    Sesi akan dimulai dalam <span class="text-amber-900 font-bold bg-amber-100 px-1.5 py-0.5 rounded">{{ scheduleCountdownLabel }}</span>
                    <span v-if="scheduleStartTimeLabel"> (Pukul {{ scheduleStartTimeLabel }})</span>
                 </p>
             </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
             <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-gradient-to-r from-orange-50 to-amber-50">
                    <tr>
                        <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">No</th>
                        <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Siswa</th>
                        <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Status Kehadiran</th>
                        <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider text-slate-600">Catatan</th>
                        <th class="px-6 py-5 text-right text-xs font-bold uppercase tracking-wider text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr v-if="isLoadingAttendance">
                         <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Memuat data...</td>
                    </tr>
                    <tr v-else-if="!attendanceRecords.length">
                        <td colspan="5" class="px-6 py-16 text-center">
                             <div class="flex flex-col items-center justify-center">
                                <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                    <UserGroupIcon class="h-8 w-8 text-slate-300" />
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">Tidak ada data siswa</h3>
                                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan filter pencarian.</p>
                            </div>
                        </td>
                    </tr>
                    <tr v-else v-for="(record, index) in attendanceRecords" :key="record.id" class="group hover:bg-orange-50/20 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-400">
                            {{ attendanceStartIndex + index }}
                        </td>
                         <td class="px-6 py-4">
                            <div>
                                <p class="font-bold text-slate-800 group-hover:text-orange-700 transition-colors">{{ record.student?.name ?? '—' }}</p>
                                <p class="text-xs font-medium text-slate-400 mt-0.5">{{ record.session_label }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                 <div class="relative">
                                    <select
                                        v-model="record.draftStatus"
                                        :disabled="isScheduleLocked"
                                        class="appearance-none rounded-xl border border-slate-200 bg-white pl-3 pr-8 py-1.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-orange-100 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400 transition-shadow cursor-pointer hover:border-orange-300 shadow-sm"
                                        :class="record.draftStatus ? 'text-slate-700' : 'text-slate-400'"
                                    >
                                        <option disabled value="">Pilih Status</option>
                                        <option v-for="option in attendanceInputStatuses" :key="option" :value="option">{{ option }}</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-slate-400">
                                         <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                 </div>
                                <span v-if="record.draftStatus" :class="['rounded-full px-2.5 py-1 text-[10px] font-bold border shadow-sm', statusBadgeClass(record.draftStatus)]">
                                    {{ record.draftStatus }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                             <div class="flex items-start gap-2">
                                <button
                                    class="group/note flex items-center justify-center h-8 w-8 rounded-full bg-slate-50 text-slate-400 hover:bg-orange-100 hover:text-orange-600 transition-all border border-slate-200 hover:border-orange-200"
                                    :disabled="isScheduleLocked"
                                    @click="openNoteModal(record)"
                                    title="Edit Catatan"
                                >
                                    <DocumentTextIcon class="h-4 w-4" />
                                </button>
                                <p v-if="record.notes" class="text-xs text-slate-600 font-medium bg-slate-50 px-3 py-2 rounded-xl border border-slate-100 max-w-[200px] leading-relaxed cursor-help" :title="record.notes">
                                    {{ truncateNote(record.notes) }}
                                </p>
                                <p v-else class="text-xs text-slate-300 italic py-2">Tidak ada catatan</p>
                             </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                             <div class="flex items-center justify-end gap-2">
                                <button
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-3 py-1.5 text-xs font-bold text-white shadow-sm shadow-emerald-200 transition-all hover:bg-emerald-600 hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                                    :disabled="isScheduleLocked || record.saving || !isRecordDirty(record)"
                                    @click="saveAttendance(record)"
                                >
                                    <span v-if="record.saving" class="h-3 w-3 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                    {{ record.saving ? 'Saving' : 'Simpan' }}
                                </button>
                                <button
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-600 transition-all border border-rose-100 hover:scale-110 active:scale-95"
                                    :disabled="isScheduleLocked"
                                    title="Hapus Data"
                                    @click="removeAttendance(record)"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                             </div>
                        </td>
                    </tr>
                </tbody>
             </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6 flex flex-col items-center gap-4 border-t border-slate-100 pt-6 sm:flex-row sm:justify-between">
            <span class="text-sm font-bold text-slate-400">{{ attendanceRangeLabel }}</span>
             <div class="flex items-center gap-2">
                <button
                    class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="attendanceFilters.page === 1"
                    @click="changeAttendancePage(-1)"
                >
                    Sebelumnya
                </button>
                 <span class="text-xs font-bold text-slate-800 bg-slate-100 px-3 py-2 rounded-lg">Hal. {{ attendanceFilters.page }}</span>
                <button
                    class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
                     :disabled="attendanceFilters.page === pagination.lastPage"
                    @click="changeAttendancePage(1)"
                >
                    Berikutnya
                </button>
            </div>
        </div>
    </section>

    <!-- Note Modal -->
    <Modal
      :show="showNoteModal"
      title="Catatan Siswa"
      variant="warning"
      max-width="lg"
      @close="closeNoteModal"
    >
      <template #description>
        Tambahkan catatan perkembangan untuk <strong>{{ activeNoteRecord?.student?.name }}</strong>.
      </template>

      <div class="space-y-6">
        <div>
           <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Perkembangan</label>
           <textarea
              v-model="noteForm.description"
              rows="6"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all bg-slate-50 focus:bg-white resize-none"
              placeholder="Tuliskan detail perilaku, prestasi, atau hal yang perlu diperhatikan..."
            ></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 active:scale-95"
            @click="closeNoteModal"
          >
            Batal
          </button>
          <button
            class="rounded-xl bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-200 transition hover:bg-orange-600 active:scale-95"
            @click="saveNote"
          >
            Simpan Catatan
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>
