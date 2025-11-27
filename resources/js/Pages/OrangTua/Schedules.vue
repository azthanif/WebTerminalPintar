<script setup>
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
</template>
