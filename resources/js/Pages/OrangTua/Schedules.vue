<!-- <script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed, onMounted, ref, watch } from 'vue'
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
import axios from 'axios'
import { computed, onMounted, ref, watch } from 'vue'
import ParentLayout from '@/Layouts/ParentLayout.vue'
import {
  ClockIcon,
  MapPinIcon,
  DocumentArrowUpIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
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
  calendar: {
    type: [Object, Array],
    default: () => ({ data: [] }),
  },
  calendarRange: {
    type: Object,
    default: () => ({ start_date: null, end_date: null }),
  },
})

defineOptions({
  layout: ParentLayout,
})

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? '')
const startDate = ref(props.filters.start_date ?? '')
const endDate = ref(props.filters.end_date ?? '')

const extractScheduleData = (payload) => {
  if (Array.isArray(payload)) {
    return payload
  }
  if (payload && Array.isArray(payload.data)) {
    return payload.data
  }
  return []
}

function parseDate(value) {
  if (!value) {
    return new Date()
  }
  if (value instanceof Date) {
    return new Date(value.getTime())
  }
  if (typeof value === 'string' && value.includes('T')) {
    return new Date(value)
  }
  if (typeof value === 'string') {
    const [year, month = 1, day = 1] = value.split('-').map(Number)
    return new Date(year, (month || 1) - 1, day || 1)
  }
  return new Date(value)
}

function formatDateKey(value) {
  const date = parseDate(value)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function startOfWeek(value) {
  const date = parseDate(value)
  const day = date.getDay()
  const diff = day === 0 ? -6 : 1 - day
  date.setDate(date.getDate() + diff)
  date.setHours(0, 0, 0, 0)
  return date
}

function endOfWeek(value) {
  const start = startOfWeek(value)
  const end = new Date(start)
  end.setDate(start.getDate() + 6)
  end.setHours(23, 59, 59, 999)
  return end
}

function computeRangeFromCursor(mode, cursor) {
  const base = parseDate(cursor)
  if (mode === 'day') {
    const key = formatDateKey(base)
    return { start_date: key, end_date: key }
  }
  if (mode === 'week') {
    const start = startOfWeek(base)
    const end = endOfWeek(base)
    return {
      start_date: formatDateKey(start),
      end_date: formatDateKey(end),
    }
  }
  const start = new Date(base.getFullYear(), base.getMonth(), 1)
  const end = new Date(base.getFullYear(), base.getMonth() + 1, 0)
  return {
    start_date: formatDateKey(start),
    end_date: formatDateKey(end),
  }
}

const todayKey = formatDateKey(new Date())
const initialRange = {
  start_date: props.calendarRange?.start_date ?? todayKey,
  end_date: props.calendarRange?.end_date ?? todayKey,
}

const calendarEvents = ref(extractScheduleData(props.calendar))
const calendarRangeState = ref(initialRange)
const calendarCursor = ref(initialRange.start_date)
const selectedDate = ref(initialRange.start_date)
const calendarMode = ref('week')
const calendarModeOptions = [
  { key: 'day', label: 'Hari' },
  { key: 'week', label: 'Minggu' },
  { key: 'month', label: 'Bulan' },
]
const calendarLoading = ref(false)
const calendarHasCustomRange = ref(false)
const calendarDetailActive = ref(true)

const statusDictionary = {
  scheduled: {
    label: 'Akan Datang',
    badge: 'bg-blue-50 text-blue-700',
    chip: 'bg-blue-50 text-blue-700',
  },
  completed: {
    label: 'Selesai',
    badge: 'bg-emerald-50 text-emerald-700',
    chip: 'bg-emerald-50 text-emerald-700',
  },
  canceled: {
    label: 'Dibatalkan',
    badge: 'bg-rose-50 text-rose-700',
    chip: 'bg-rose-50 text-rose-700',
  },
}

const statusLabel = (value) => statusDictionary[value]?.label ?? value
const badgeClass = (value) => statusDictionary[value]?.badge ?? 'bg-gray-100 text-gray-700'
const chipClass = (value) => statusDictionary[value]?.chip ?? 'bg-gray-100 text-gray-700'

const pagination = computed(() => props.schedules?.meta ?? null)
const scheduleItems = computed(() => props.schedules?.data ?? [])

const groupedSessions = computed(() => {
  const groups = {}
  const items = [...calendarEvents.value]
  items.sort((a, b) => new Date(a.start_time) - new Date(b.start_time))
  items.forEach((item) => {
    const key = formatDateKey(item.start_time ?? item.date ?? item.day)
    if (!groups[key]) {
      groups[key] = []
    }
    groups[key].push(item)
  })
  return groups
})

const calendarCells = computed(() => {
  if (!calendarRangeState.value.start_date || !calendarRangeState.value.end_date) return []
  const start = parseDate(calendarRangeState.value.start_date)
  const end = parseDate(calendarRangeState.value.end_date)
  const cells = []
  const pointer = new Date(start)
  while (pointer.getTime() <= end.getTime()) {
    const cellDate = new Date(pointer)
    const key = formatDateKey(cellDate)
    cells.push({
      key,
      label: cellDate.toLocaleDateString('id-ID', { weekday: 'short' }),
      number: cellDate.getDate(),
      isToday: key === todayKey,
      events: groupedSessions.value[key] ?? [],
    })
    pointer.setDate(pointer.getDate() + 1)
  }
  return cells
})

const calendarGridClass = computed(() => {
  if (calendarMode.value === 'day') {
    return 'grid grid-cols-1 gap-3'
  }
  return 'grid grid-cols-7 gap-3'
})

const calendarHeadline = computed(() => {
  const { start_date: startKey, end_date: endKey } = calendarRangeState.value
  if (!startKey || !endKey) return ''
  const startObj = parseDate(startKey)
  const endObj = parseDate(endKey)
  if (calendarMode.value === 'day') {
    return startObj.toLocaleDateString('id-ID', {
      weekday: 'long',
      day: '2-digit',
      month: 'long',
      year: 'numeric',
    })
  }
  if (calendarMode.value === 'week') {
    const startLabel = startObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
    const endLabel = endObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
    return `${startLabel} — ${endLabel}`
  }
  return startObj.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })
})

const selectedDayLabel = computed(() => {
  if (!selectedDate.value) return ''
  return parseDate(selectedDate.value).toLocaleDateString('id-ID', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
})

const selectedSessions = computed(() => groupedSessions.value[selectedDate.value] ?? [])

const showingCalendarContext = computed(() => calendarDetailActive.value)
const showingCalendarSessions = computed(() => calendarDetailActive.value && selectedSessions.value.length > 0)
const activeSessions = computed(() => (calendarDetailActive.value ? selectedSessions.value : scheduleItems.value))
const activeListLabel = computed(() => (calendarDetailActive.value ? selectedDayLabel.value : 'Daftar Jadwal'))
const activeListEyebrow = computed(() => (calendarDetailActive.value ? 'Tanggal Terpilih' : 'Daftar Jadwal'))
const emptyListText = computed(() => (
  calendarDetailActive.value
    ? 'Belum ada jadwal pada tanggal ini.'
    : 'Belum ada jadwal yang ditemukan.'
))

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
    refreshCalendar()
  }, 500)
})

watch(calendarCells, (cells) => {
  if (!calendarDetailActive.value || !cells.length) return
  const exists = cells.some((cell) => cell.key === selectedDate.value)
  if (!exists) {
    selectedDate.value = cells[0].key
  }
})

watch(
  () => [props.calendar, props.calendarRange],
  () => {
    hydrateFromServer()
  }
)

watch(
  () => props.student?.id,
  () => {
    calendarHasCustomRange.value = false
    calendarDetailActive.value = true
    hydrateFromServer(true)
    jumpToToday()
  }
)

const goToPage = (url) => {
  if (!url) return
  router.visit(url, { preserveScroll: true, preserveState: true })
}

const formatFullDate = (value) => {
  if (!value) return '-'
  return parseDate(value).toLocaleDateString('id-ID', {
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
  const startLabel = timeFormatter.format(parseDate(start))
  const endLabel = end ? timeFormatter.format(parseDate(end)) : '—'
  return `${startLabel} - ${endLabel}`
}

const openMaterial = (url) => {
  if (url) window.open(url, '_blank')
}

const hydrateFromServer = (force = false) => {
  if (!force && calendarHasCustomRange.value) {
    return
  }

  const range = {
    start_date: props.calendarRange?.start_date ?? todayKey,
    end_date: props.calendarRange?.end_date ?? todayKey,
  }

  calendarEvents.value = extractScheduleData(props.calendar)
  calendarRangeState.value = range
  calendarCursor.value = range.start_date
  if (calendarDetailActive.value) {
    selectedDate.value = range.start_date
  }
}

const setCalendarMode = (mode) => {
  if (calendarMode.value === mode) return
  calendarMode.value = mode
  calendarCursor.value = selectedDate.value
  refreshCalendar()
}

const navigateCalendar = (direction) => {
  const current = parseDate(calendarCursor.value)
  if (calendarMode.value === 'day') {
    current.setDate(current.getDate() + direction)
  } else if (calendarMode.value === 'week') {
    current.setDate(current.getDate() + direction * 7)
  } else {
    current.setMonth(current.getMonth() + direction)
  }
  calendarCursor.value = formatDateKey(current)
  refreshCalendar()
}

const jumpToToday = () => {
  calendarCursor.value = todayKey
  selectedDate.value = todayKey
  calendarDetailActive.value = true
  refreshCalendar()
}

const selectDate = (dateKey) => {
  selectedDate.value = dateKey
  calendarDetailActive.value = true
  if (calendarMode.value === 'day' && calendarCursor.value !== dateKey) {
    calendarCursor.value = dateKey
  }
}

const showAllSchedules = () => {
  calendarDetailActive.value = false
  selectedDate.value = null
}

const refreshCalendar = async () => {
  const range = computeRangeFromCursor(calendarMode.value, calendarCursor.value)
  calendarRangeState.value = range
  calendarLoading.value = true
  try {
    const response = await axios.get(route('orang-tua.schedules.calendar'), {
      params: {
        ...range,
        status: status.value || undefined,
        search: search.value || undefined,
      },
    })
    calendarEvents.value = extractScheduleData(response.data)
    calendarHasCustomRange.value = true
  } catch (error) {
    console.error('Gagal memuat kalender', error)
  } finally {
    calendarLoading.value = false
  }
}

onMounted(() => {
  jumpToToday()
})
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

      
    </section>

    <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <p class="text-sm font-semibold text-[#78AE4E]">Kalender Pembelajaran</p>
          <h3 class="text-2xl font-bold text-gray-900">Mode Hari, Minggu, atau Bulan</h3>
          <p class="text-sm text-gray-500">Klik tanggal untuk melihat seluruh sesi pada hari tersebut.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <button
            v-for="mode in calendarModeOptions"
            :key="mode.key"
            type="button"
            class="rounded-full border px-4 py-2 text-sm font-semibold transition"
            :class="calendarMode === mode.key
              ? 'border-[#78AE4E] bg-[#78AE4E]/10 text-[#78AE4E]'
              : 'border-gray-200 text-gray-500 hover:border-[#78AE4E] hover:text-[#78AE4E]'
            "
            @click="setCalendarMode(mode.key)"
          >
            {{ mode.label }}
          </button>
        </div>
      </div>

      <div class="mt-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-[#fdfcf9] px-4 py-3">
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="rounded-full border border-gray-200 p-2 text-gray-500 transition hover:border-[#78AE4E] hover:text-[#78AE4E]"
              @click="navigateCalendar(-1)"
            >
              <ChevronLeftIcon class="h-5 w-5" />
            </button>
            <p class="text-sm font-semibold text-gray-700">{{ calendarHeadline }}</p>
            <button
              type="button"
              class="rounded-full border border-gray-200 p-2 text-gray-500 transition hover:border-[#78AE4E] hover:text-[#78AE4E]"
              @click="navigateCalendar(1)"
            >
              <ChevronRightIcon class="h-5 w-5" />
            </button>
          </div>
          <button
            type="button"
            class="rounded-full border border-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 transition hover:border-[#78AE4E] hover:text-[#78AE4E]"
            @click="jumpToToday"
          >
            Hari Ini
          </button>
        </div>

        <div class="relative rounded-3xl border border-gray-100 bg-white p-4">
          <div
            v-if="calendarLoading"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-3xl bg-white/80 text-sm font-semibold text-gray-500"
          >
            Memuat kalender...
          </div>
          <div :class="calendarGridClass" class="gap-3">
            <button
              v-for="cell in calendarCells"
              :key="cell.key"
              type="button"
              class="flex flex-col rounded-2xl border bg-[#fdfcf9] p-3 text-left transition hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#78AE4E] focus-visible:ring-offset-2"
              :class="[
                cell.key === selectedDate ? 'border-[#78AE4E] shadow-sm' : 'border-gray-100 hover:border-[#78AE4E]/40',
                calendarMode === 'day' ? 'min-h-[160px]' : 'min-h-[140px]',
                cell.key === selectedDate ? 'mt-2 mb-2' : ''
              ]"
              @click="selectDate(cell.key)"
            >
              <div class="flex items-center justify-between text-gray-500">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wide">{{ cell.label }}</p>
                  <p class="text-2xl font-bold text-gray-900">{{ String(cell.number).padStart(2, '0') }}</p>
                </div>
                <span v-if="cell.isToday" class="rounded-full bg-[#78AE4E]/10 px-3 py-1 text-xs font-semibold text-[#78AE4E]">
                  Hari Ini
                </span>
              </div>
              <div class="mt-3 space-y-2">
                <p v-if="!cell.events.length" class="text-xs text-gray-400">Tidak ada sesi</p>
                <button
                  v-for="event in cell.events.slice(0, 3)"
                  :key="event.id"
                  type="button"
                  class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold transition"
                  :class="chipClass(event.status)"
                  @click.stop="selectDate(cell.key)"
                >
                  <span class="line-clamp-1 pr-2">{{ event.topic }}</span>
                  <span class="text-[10px] font-bold uppercase tracking-wide">
                    {{ formatTimeRange(event.start_time, event.end_time) }}
                  </span>
                </button>
                <p v-if="cell.events.length > 3" class="text-[10px] font-semibold text-gray-400">
                  + {{ cell.events.length - 3 }} sesi lainnya
                </p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
      <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gray-400">{{ activeListEyebrow }}</p>
          <h3 class="text-2xl font-bold text-gray-900">{{ activeListLabel || 'Daftar Jadwal' }}</h3>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            class="rounded-full border border-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 transition hover:border-[#78AE4E] hover:text-[#78AE4E]"
            @click="jumpToToday"
          >
            Lihat Hari Ini
          </button>
          <button
            v-if="showingCalendarContext"
            type="button"
            class="rounded-full border border-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 transition hover:border-[#78AE4E] hover:text-[#78AE4E]"
            @click="showAllSchedules"
          >
            Semua Jadwal
          </button>
        </div>
      </div>

      <template v-if="!showingCalendarContext || activeSessions.length">
        <article
          v-for="schedule in activeSessions"
          :key="schedule.id"
          class="rounded-3xl border border-gray-100 bg-[#fdfcf9] p-5 shadow-sm transition hover:shadow-md mb-4"
        >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="flex-1 space-y-3">
            <div class="flex flex-wrap items-center gap-3">
              <p class="text-lg font-semibold text-gray-900">{{ formatFullDate(schedule.start_time) }}</p>
              <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(schedule.status)">
                {{ statusLabel(schedule.status) }}
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
        
        <div v-else class="mt-4 border-t border-gray-100 pt-4">
            <p class="text-xs text-gray-400 italic">Tidak ada materi terlampir untuk sesi ini.</p>
        </div>
        </article>
      </template>

      <div v-if="showingCalendarContext && !activeSessions.length" class="flex flex-col items-center justify-center py-16 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p>{{ emptyListText }}</p>
      </div>

      <div v-if="!showingCalendarContext && !activeSessions.length" class="flex flex-col items-center justify-center py-16 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p>{{ emptyListText }}</p>
        <button v-if="status || search || startDate" @click="status=''; search=''; startDate=''; endDate=''" class="mt-2 text-sm text-[#78AE4E] hover:underline font-semibold">
            Reset filter
        </button>
      </div>
    </section>

    <nav v-if="!showingCalendarContext && pagination && pagination.total > pagination.per_page" class="flex items-center justify-between rounded-2xl border border-gray-100 bg-white p-4 text-sm">
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