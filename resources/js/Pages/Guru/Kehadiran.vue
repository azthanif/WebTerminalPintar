<script setup>
import { Head } from '@inertiajs/vue3'
import GuruLayout from '@/Layouts/GuruLayout.vue'
import axios from 'axios'
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import {
  DocumentTextIcon,
  FunnelIcon,
  MagnifyingGlassIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: GuruLayout })

const perPage = 10
const attendanceStatusOptions = ['Semua', 'Hadir', 'Izin', 'Sakit', 'Alpha']
const attendanceInputStatuses = ['Hadir', 'Izin', 'Sakit', 'Alpha']

const attendanceFilters = reactive({
  query: '',
  status: 'Semua',
  page: 1,
})

const attendanceSummary = reactive({ Hadir: 0, Izin: 0, Sakit: 0, Alpha: 0 })
const attendanceRecords = ref([])
const pagination = reactive({ total: 0, lastPage: 1, currentPage: 1 })
const isLoadingAttendance = ref(false)
const successMessage = ref('')

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
  },
  {
    id: 'izin',
    label: 'Izin',
    value: attendanceSummary.Izin ?? 0,
    subtitle: 'Butuh persetujuan wali',
    badge: 'bg-blue-50 text-blue-700',
  },
  {
    id: 'sakit',
    label: 'Sakit',
    value: attendanceSummary.Sakit ?? 0,
    subtitle: 'Update status setelah pulih',
    badge: 'bg-amber-50 text-amber-700',
  },
  {
    id: 'alpha',
    label: 'Alpha',
    value: attendanceSummary.Alpha ?? 0,
    subtitle: 'Perlu tindak lanjut segera',
    badge: 'bg-rose-50 text-rose-700',
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
    Hadir: 'bg-[#eaf6df] text-[#3a5f2a]',
    Izin: 'bg-blue-100 text-blue-700',
    Sakit: 'bg-amber-100 text-amber-800',
    Alpha: 'bg-red-100 text-red-700',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}

const isRecordDirty = (record) => record.selectedStatus !== record.status

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
    status: record.status,
    selectedStatus: record.status,
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

const saveAttendance = async (record, overrides = {}) => {
  if (!record?.schedule_id || !record?.student_id) {
    return
  }
  record.saving = true
  try {
    const payload = {
      schedule_id: record.schedule_id,
      student_id: record.student_id,
      attendance_date: record.attendance_date,
      status: overrides.status ?? record.selectedStatus,
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
  if (!record?.id) return
  await axios.delete(route('guru.api.attendance.destroy', record.id))
  setSuccessMessage('Data kehadiran dihapus.')
  fetchAttendance()
}

const openNoteModal = (record) => {
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
</script>

<template>
  <div class="space-y-8">
    <Head title="Kehadiran" />

    <section class="space-y-6">
      <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
          <p class="text-sm font-semibold text-[#78AE4E]">Kehadiran</p>
          <h2 class="text-2xl font-bold text-gray-900">Rekapitulasi Kehadiran</h2>
          <p class="text-sm text-gray-500">Pantau kehadiran siswa dan tambahkan catatan perkembangan langsung dari tabel.</p>
        </div>
        <button class="rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600">Bahasa Indonesia</button>
      </header>

      <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <article
          v-for="card in attendanceSummaryCards"
          :key="card.id"
          class="flex items-center justify-between rounded-3xl border border-gray-100 bg-white p-4 shadow-sm"
        >
          <div>
            <p class="text-sm text-gray-500">{{ card.label }}</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ card.value }}</p>
            <p class="text-xs text-gray-400">{{ card.subtitle }}</p>
          </div>
          <span :class="['px-3 py-1 rounded-full text-sm font-semibold', card.badge]">{{ card.label }}</span>
        </article>
      </div>

      <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-sm font-semibold text-[#78AE4E]">Detail Kehadiran</p>
            <h3 class="text-xl font-bold text-gray-900">Akses detail catatan kehadiran</h3>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-2">
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
              <input
                v-model="attendanceFilters.query"
                type="text"
                placeholder="Cari nama siswa"
                class="bg-transparent text-sm text-gray-700 focus:outline-none"
              />
            </div>
            <div class="flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 py-2">
              <FunnelIcon class="h-5 w-5 text-gray-400" />
              <select v-model="attendanceFilters.status" class="bg-transparent text-sm text-gray-700 focus:outline-none">
                <option v-for="option in attendanceStatusOptions" :key="option" :value="option">
                  {{ option }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <div
          v-if="successMessage"
          class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"
        >
          {{ successMessage }}
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-100">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                <th class="px-6 py-3">No</th>
                <th class="px-6 py-3">Nama Siswa</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Catatan Perkembangan Siswa</th>
                <th class="px-6 py-3">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white text-sm text-gray-700">
              <tr v-if="isLoadingAttendance">
                <td colspan="5" class="px-6 py-6 text-center text-gray-400">Memuat data kehadiran...</td>
              </tr>
              <tr v-else-if="!attendanceRecords.length">
                <td colspan="5" class="px-6 py-6 text-center text-gray-400">Tidak ada data kehadiran</td>
              </tr>
              <tr v-else v-for="(record, index) in attendanceRecords" :key="record.id">
                <td class="px-6 py-4">{{ attendanceStartIndex + index }}</td>
                <td class="px-6 py-4">
                  <p class="font-semibold text-gray-900">{{ record.student?.name ?? 'Tidak diketahui' }}</p>
                  <p class="text-xs text-gray-500">{{ record.session_label }}</p>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <select
                      v-model="record.selectedStatus"
                      class="rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
                    >
                      <option v-for="option in attendanceInputStatuses" :key="option" :value="option">{{ option }}</option>
                    </select>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusBadgeClass(record.status)">
                      {{ record.status }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <button
                    class="flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-[#78AE4E] hover:text-[#78AE4E]"
                    @click="openNoteModal(record)"
                  >
                    <DocumentTextIcon class="h-5 w-5" />
                    Catatan Perkembangan Siswa
                  </button>
                  <p v-if="record.notes" class="mt-2 text-xs text-gray-500">{{ truncateNote(record.notes) }}</p>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <button
                      class="rounded-full bg-[#78AE4E] px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                      :disabled="record.saving || !isRecordDirty(record)"
                      @click="saveAttendance(record)"
                    >
                      {{ record.saving ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                    <button class="text-red-500 transition hover:text-red-600" title="Hapus" @click="removeAttendance(record)">
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4 flex flex-col gap-3 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between">
          <p>{{ attendanceRangeLabel }}</p>
          <div class="flex items-center gap-4 text-xs">
            <div class="flex items-center gap-1">
              <span class="h-3 w-3 rounded-full bg-[#eaf6df]"></span>
              <span>Hadir</span>
            </div>
            <div class="flex items-center gap-1">
              <span class="h-3 w-3 rounded-full bg-blue-100"></span>
              <span>Izin</span>
            </div>
            <div class="flex items-center gap-1">
              <span class="h-3 w-3 rounded-full bg-amber-100"></span>
              <span>Sakit</span>
            </div>
            <div class="flex items-center gap-1">
              <span class="h-3 w-3 rounded-full bg-red-100"></span>
              <span>Alpha</span>
            </div>
          </div>
        </div>

        <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
          <button
            class="rounded-full border px-4 py-2"
            :disabled="attendanceFilters.page === 1"
            @click="changeAttendancePage(-1)"
          >
            Sebelumnya
          </button>
          <span>Halaman {{ attendanceFilters.page }} / {{ pagination.lastPage }}</span>
          <button
            class="rounded-full border px-4 py-2"
            :disabled="attendanceFilters.page === pagination.lastPage"
            @click="changeAttendancePage(1)"
          >
            Berikutnya
          </button>
        </div>
      </article>
    </section>

    <div
      v-if="showNoteModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
      role="dialog"
      aria-modal="true"
    >
      <div class="w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-gray-100 bg-[#f7f5f2] px-6 py-4">
          <div>
            <p class="text-sm font-semibold text-gray-500">Catatan Perkembangan Siswa</p>
            <p class="text-lg font-bold text-gray-900">{{ activeNoteRecord?.student?.name ?? 'Tanpa nama' }}</p>
            <p class="text-xs text-gray-500">{{ activeNoteRecord?.session_label }}</p>
          </div>
          <button class="text-xl text-gray-400 transition hover:text-gray-600" @click="closeNoteModal">×</button>
        </header>

        <div class="space-y-4 px-6 py-6">
          <div>
            <label class="text-sm font-semibold text-gray-700">Deskripsi Singkat</label>
            <textarea
              v-model="noteForm.description"
              rows="4"
              class="mt-2 w-full rounded-2xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              placeholder="Tuliskan deskripsi perkembangan siswa"
            ></textarea>
          </div>
        </div>

        <footer class="flex items-center justify-end gap-3 border-t border-gray-100 bg-[#f7f5f2] px-6 py-4">
          <button class="rounded-full border border-gray-200 px-5 py-2 text-sm font-semibold text-gray-600" @click="closeNoteModal">
            Batal
          </button>
          <button class="rounded-full bg-[#78AE4E] px-6 py-2 text-sm font-semibold text-white" @click="saveNote">
            Simpan Catatan dan Status
          </button>
        </footer>
      </div>
    </div>
  </div>
</template>
