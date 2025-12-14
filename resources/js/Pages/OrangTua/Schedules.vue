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
  CalendarDaysIcon,
  ListBulletIcon,
  ArrowDownTrayIcon
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
    badge: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    chip: 'bg-emerald-50 text-emerald-700',
  },
  completed: {
    label: 'Selesai',
    badge: 'bg-slate-100 text-slate-600 border-slate-200',
    chip: 'bg-slate-100 text-slate-600',
  },
  canceled: {
    label: 'Dibatalkan',
    badge: 'bg-rose-50 text-rose-700 border-rose-200',
    chip: 'bg-rose-50 text-rose-700',
  },
}

const statusLabel = (value) => statusDictionary[value]?.label ?? value
const badgeClass = (value) => statusDictionary[value]?.badge ?? 'bg-slate-50 text-slate-600 border-slate-200'
const chipClass = (value) => statusDictionary[value]?.chip ?? 'bg-slate-50 text-slate-600'

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
      fullDay: cellDate.toLocaleDateString('id-ID', { weekday: 'long' }),
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
    return 'grid grid-cols-1 gap-4'
  }
  return 'grid grid-cols-2 lg:grid-cols-7 gap-4'
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
// const showingCalendarSessions = computed(() => calendarDetailActive.value && selectedSessions.value.length > 0)
const activeSessions = computed(() => (calendarDetailActive.value ? selectedSessions.value : scheduleItems.value))
const activeListLabel = computed(() => (calendarDetailActive.value ? selectedDayLabel.value : 'Daftar Jadwal'))
const activeListEyebrow = computed(() => (calendarDetailActive.value ? 'Agenda Tanggal' : 'Seluruh Jadwal'))
const emptyListText = computed(() => (
  calendarDetailActive.value
    ? 'Tidak ada kegiatan belajar pada tanggal ini.'
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

    <!-- Premium Header -->
    <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
      <div class="space-y-2">
         <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600 border border-emerald-100">
            <CalendarDaysIcon class="h-3 w-3" />
            <span>Agenda Akademik</span>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
             Jadwal <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">Belajar</span>
        </h1>
        <p class="text-slate-500 font-medium text-lg max-w-2xl">
            Pantau kegiatan dan agenda {{ student.name }} secara real-time.
        </p>
      </div>

       <div class="flex items-center gap-4">
           <div class="rounded-[2rem] border border-slate-200 bg-white px-6 py-4 shadow-sm flex items-center gap-4">
                <div class="h-12 w-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <ListBulletIcon class="h-6 w-6" />
                </div>
                <div>
                   <p class="text-xs font-bold uppercase text-slate-400">Total Sesi</p>
                   <p class="text-2xl font-extrabold text-slate-800">{{ schedules.meta?.total ?? 0 }}</p>
                </div>
           </div>
       </div>
    </section>

    <!-- Calendar Section -->
    <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
      <!-- Calendar Controls -->
      <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between mb-8">
        <div>
          <h3 class="text-xl font-bold text-slate-900">Kalender Pembelajaran</h3>
          <p class="text-sm font-medium text-slate-500 mt-1">Pilih tampilan hari, minggu, atau bulan.</p>
        </div>
        <div class="flex bg-slate-50 rounded-2xl p-1.5 border border-slate-100">
          <button
            v-for="mode in calendarModeOptions"
            :key="mode.key"
            type="button"
            class="rounded-xl px-5 py-2 text-sm font-bold transition-all"
            :class="calendarMode === mode.key
              ? 'bg-white text-emerald-600 shadow-sm ring-1 ring-slate-100'
              : 'text-slate-500 hover:text-emerald-600 hover:bg-slate-100'
            "
            @click="setCalendarMode(mode.key)"
          >
            {{ mode.label }}
          </button>
        </div>
      </div>

      <!-- Calendar Navigation -->
      <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-[#f8fafc] px-4 py-3">
          <div class="flex items-center gap-3">
            <button
              type="button"
              class="group h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 transition hover:border-emerald-200 hover:text-emerald-600 hover:shadow-sm"
              @click="navigateCalendar(-1)"
            >
              <ChevronLeftIcon class="h-5 w-5 group-hover:-translate-x-0.5 transition-transform" />
            </button>
             <button
              type="button"
              class="group h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 transition hover:border-emerald-200 hover:text-emerald-600 hover:shadow-sm"
              @click="navigateCalendar(1)"
            >
              <ChevronRightIcon class="h-5 w-5 group-hover:translate-x-0.5 transition-transform" />
            </button>
            <p class="text-base font-bold text-slate-700 ml-2 capitalize">{{ calendarHeadline }}</p>
          </div>
          <button
            type="button"
            class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-2 text-xs font-bold uppercase tracking-wide text-emerald-600 transition hover:bg-emerald-100 hover:scale-105 active:scale-95"
            @click="jumpToToday"
          >
            Kembali ke Hari Ini
          </button>
        </div>

        <!-- Calendar Grid -->
        <div class="relative min-h-[300px]">
          <div
            v-if="calendarLoading"
            class="absolute inset-0 z-10 flex items-center justify-center rounded-[2rem] bg-white/60 backdrop-blur-sm"
          >
            <div class="flex items-center gap-2 text-emerald-600 font-bold bg-white px-4 py-2 rounded-full shadow-lg border border-emerald-100">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memuat Jadwal...</span>
            </div>
          </div>

          <div :class="calendarGridClass">
            <button
              v-for="cell in calendarCells"
              :key="cell.key"
              type="button"
              class="group flex flex-col rounded-2xl p-4 text-left transition-all outline-none ring-offset-2 focus-visible:ring-2 focus-visible:ring-emerald-400"
              :class="[
                cell.key === selectedDate 
                    ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 scale-[1.02]' 
                    : 'bg-slate-50 text-slate-600 hover:bg-white hover:shadow-md hover:scale-[1.01] border border-transparent hover:border-emerald-100',
                 calendarMode === 'day' ? 'min-h-[120px]' : 'min-h-[140px]'
              ]"
              @click="selectDate(cell.key)"
            >
              <div class="flex items-center justify-between mb-3">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wider opacity-80" :class="cell.key === selectedDate ? 'text-emerald-100' : 'text-slate-400'">{{ cell.label }}</p>
                  <p class="text-2xl font-extrabold" :class="cell.key === selectedDate ? 'text-white' : 'text-slate-800'">{{ String(cell.number).padStart(2, '0') }}</p>
                </div>
                <span v-if="cell.isToday" class="rounded-lg px-2 py-1 text-[10px] font-bold uppercase tracking-wide" :class="cell.key === selectedDate ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">
                  Hari Ini
                </span>
              </div>
              
              <div class="flex-1 space-y-1.5">
                <p v-if="!cell.events.length" class="text-xs italic mt-2 opacity-60">Tidak ada sesi</p>
                <div
                  v-for="event in cell.events.slice(0, 3)"
                  :key="event.id"
                  class="w-full rounded-lg px-2.5 py-1.5 text-xs font-bold flex items-center justify-between shadow-sm"
                  :class="cell.key === selectedDate ? 'bg-white/20 text-white border border-white/10' : 'bg-white border border-slate-100 text-slate-700'"
                >
                  <span class="truncate mr-2">{{ event.topic }}</span>
                  <span class="opacity-80 text-[10px]">{{ formatTimeRange(event.start_time).split(' - ')[0] }}</span>
                </div>
                <p v-if="cell.events.length > 3" class="text-[10px] font-bold text-center mt-1" :class="cell.key === selectedDate ? 'text-emerald-100' : 'text-emerald-600'">
                  + {{ cell.events.length - 3 }} Lainnya
                </p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Detailed List Section -->
    <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm relative overflow-hidden">
      <div class="absolute top-0 right-0 p-12 opacity-5 pointer-events-none">
          <ListBulletIcon class="w-64 h-64 text-slate-900" />
      </div>

      <div class="relative z-10 mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-6">
        <div>
          <span class="inline-block rounded-lg bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-emerald-600 mb-2">
            {{ activeListEyebrow }}
          </span>
          <h3 class="text-2xl font-extrabold text-slate-900">{{ activeListLabel }}</h3>
        </div>
        <div class="flex items-center gap-2">
           <button
            v-if="showingCalendarContext"
            type="button"
            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-xs font-bold uppercase tracking-wide text-slate-600 shadow-sm transition-all hover:bg-slate-50 hover:text-emerald-600"
            @click="showAllSchedules"
          >
            Tampilkan Semua
          </button>
        </div>
      </div>

      <div v-if="activeSessions.length" class="space-y-4 relative z-10">
        <article
          v-for="schedule in activeSessions"
          :key="schedule.id"
          class="group rounded-[2rem] border border-slate-200 bg-slate-50/50 p-6 transition-all hover:bg-white hover:border-emerald-200 hover:shadow-lg hover:-translate-y-1"
        >
          <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex-1 space-y-4">
               <!-- Header Line -->
              <div class="flex flex-wrap items-center gap-3">
                 <div class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-1.5 border border-slate-200 shadow-sm">
                    <CalendarDaysIcon class="h-4 w-4 text-emerald-500" />
                    <p class="text-sm font-bold text-slate-700">{{ formatFullDate(schedule.start_time) }}</p>
                 </div>
                <span class="rounded-lg px-3 py-1.5 text-xs font-bold border shadow-sm" :class="badgeClass(schedule.status)">
                  {{ statusLabel(schedule.status) }}
                </span>
                 <span class="rounded-lg bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600">
                    {{ schedule.subject }}
                </span>
              </div>

               <!-- Title & Desc -->
              <div>
                <h4 class="text-xl font-extrabold text-slate-900 group-hover:text-emerald-600 transition-colors">{{ schedule.topic }}</h4>
                <p class="text-sm font-medium text-slate-500 mt-2 leading-relaxed max-w-3xl">
                  {{ schedule.description || 'Tidak ada deskripsi tambahan untuk sesi ini.' }}
                </p>
              </div>

              <!-- Meta Info -->
              <div class="flex flex-wrap items-center gap-6 text-sm font-semibold text-slate-600 pt-2">
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-100">
                  <ClockIcon class="h-5 w-5 text-emerald-500" />
                  <span>{{ formatTimeRange(schedule.start_time, schedule.end_time) }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-100" v-if="schedule.location">
                  <MapPinIcon class="h-5 w-5 text-emerald-500" />
                  <span>{{ schedule.location }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-100">
                   <div class="h-5 w-5 rounded-full bg-slate-200 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-slate-500">G</span>
                   </div>
                  <span>{{ schedule.teacher?.name ?? 'Guru Pengampu' }}</span>
                </div>
              </div>
            </div>

            <!-- Materials -->
            <div v-if="schedule.materials?.length" class="w-full lg:w-72 bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                 <h5 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
                    <DocumentArrowUpIcon class="h-4 w-4" />
                    Materi ({{ schedule.materials.length }})
                 </h5>
                 <div class="space-y-2">
                    <button
                        v-for="material in schedule.materials"
                        :key="material.id"
                        type="button"
                        class="w-full flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-2.5 text-left hover:border-emerald-200 hover:bg-emerald-50 transition-all group/file"
                        @click="openMaterial(material.download_url)"
                    >
                        <div class="h-8 w-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-emerald-500 shadow-sm group-hover/file:scale-110 transition-transform">
                            <ArrowDownTrayIcon class="h-4 w-4" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-700 truncate group-hover/file:text-emerald-700">{{ material.title }}</p>
                            <p class="text-[10px] text-slate-500 truncate">Unduh File</p>
                        </div>
                    </button>
                 </div>
            </div>
            <div v-else class="hidden lg:block w-72 rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center text-xs text-slate-400 font-medium">
                Tidak ada materi
            </div>
          </div>
        </article>
      </div>

       <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-20 text-center relative z-10">
        <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-slate-100">
             <CalendarDaysIcon class="h-10 w-10 text-slate-300" />
        </div>
        <h4 class="text-xl font-bold text-slate-800">{{ emptyListText }}</h4>
        <p class="text-slate-500 mt-2 max-w-md mx-auto">Tidak ada jadwal yang ditemukan untuk filter yang dipilih. Coba pilih tanggal lain atau reset filter pencarian.</p>
        <button 
            v-if="status || search || startDate" 
            @click="status=''; search=''; startDate=''; endDate=''" 
            class="mt-6 inline-flex items-center justify-center rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-200 hover:bg-slate-900 active:scale-95 transition-all"
        >
            Reset Filter
        </button>
      </div>

      <!-- Pagination -->
      <nav v-if="!showingCalendarContext && pagination && pagination.total > pagination.per_page" class="mt-8 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-6 py-4 relative z-10">
        <div class="text-sm font-bold text-slate-500">
          Menampilkan <span class="text-slate-800">{{ pagination.from }}-{{ pagination.to }}</span> dari <span class="text-slate-800">{{ pagination.total }}</span> jadwal
        </div>
        <div class="flex gap-2">
          <button
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-slate-600"
            :disabled="!props.schedules?.links?.prev"
            @click="goToPage(props.schedules?.links?.prev)"
          >
            Sebelumnya
          </button>
          <button
            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition-all hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:text-slate-600"
            :disabled="!props.schedules?.links?.next"
            @click="goToPage(props.schedules?.links?.next)"
          >
            Selanjutnya
          </button>
        </div>
      </nav>
    </section>
  </div>
</template>