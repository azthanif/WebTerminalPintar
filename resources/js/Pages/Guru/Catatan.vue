<template>
  <Head title="Catatan Guru" />

  <section class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
      <div>
        <p class="text-sm text-slate-500">Pantau umpan balik untuk orang tua</p>
        <h2 class="text-3xl font-bold text-slate-900">Catatan Guru</h2>
      </div>
      <div class="flex gap-3">
        <button class="px-4 py-2 rounded-full border" @click="fetchNotes()">Muat ulang</button>
        <button class="px-4 py-2 rounded-full bg-slate-900 text-white" @click="openForm()">
          Tambah Catatan
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <article
        v-for="note in notes"
        :key="note.id"
        class="bg-white border border-slate-100 rounded-3xl shadow-sm p-6 flex flex-col gap-3"
      >
        <div class="flex items-center gap-3">
          <span
            class="w-3 h-3 rounded-full"
            :style="{ backgroundColor: note.tag_color || '#c084fc' }"
          ></span>
          <p class="text-sm text-slate-500">{{ formatDate(note.recorded_at) }}</p>
          <span class="text-xs uppercase tracking-wide text-slate-400">{{ note.category }}</span>
        </div>
        <h3 class="text-xl font-semibold text-slate-900">{{ note.title }}</h3>
        <p class="text-sm text-slate-600">Untuk: {{ note.student?.name || '—' }}</p>
        <p class="text-slate-600">{{ note.note }}</p>
        <div class="flex gap-2 mt-auto">
          <button class="text-sm text-slate-500" @click="openForm(note)">Edit</button>
          <button class="text-sm text-rose-500" @click="confirmDelete(note)">Hapus</button>
        </div>
      </article>
      <p v-if="!notes.length && !isLoading" class="text-slate-400 py-20 text-center col-span-full">
        Belum ada catatan
      </p>
      <p v-if="isLoading" class="text-slate-500 py-20 text-center col-span-full">Memuat...</p>
    </div>
  </section>

  <div
    v-if="showForm"
    role="dialog"
    aria-modal="true"
    class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl p-8">
      <h3 class="text-xl font-semibold text-slate-900 mb-6">
        {{ form.id ? 'Perbarui Catatan' : 'Catatan Baru' }}
      </h3>
      <form class="space-y-4" @submit.prevent="saveNote">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <label class="text-sm text-slate-600 space-y-1">
            <span>Siswa</span>
            <select v-model="form.student_id" class="w-full rounded-2xl border px-3 py-2" required>
              <option disabled value="">Pilih siswa</option>
              <option v-for="student in students" :key="student.id" :value="student.id">
                {{ student.name }}
              </option>
            </select>
          </label>
          <label class="text-sm text-slate-600 space-y-1">
            <span>Jadwal</span>
            <select v-model="form.schedule_id" class="w-full rounded-2xl border px-3 py-2">
              <option :value="null">Tanpa jadwal</option>
              <option v-for="schedule in schedules" :key="schedule.id" :value="schedule.id">
                {{ schedule.topic }}
              </option>
            </select>
          </label>
        </div>
        <label class="text-sm text-slate-600 space-y-1">
          <span>Judul</span>
          <input v-model="form.title" type="text" class="w-full rounded-2xl border px-3 py-2" required />
        </label>
        <label class="text-sm text-slate-600 space-y-1">
          <span>Kategori</span>
          <select v-model="form.category" class="w-full rounded-2xl border px-3 py-2" required>
            <option value="academic">Akademik</option>
            <option value="behavior">Perilaku</option>
            <option value="communication">Komunikasi</option>
            <option value="general">Umum</option>
          </select>
        </label>
        <label class="text-sm text-slate-600 space-y-1">
          <span>Catatan</span>
          <textarea v-model="form.note" rows="4" class="w-full rounded-2xl border px-3 py-2" required></textarea>
        </label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <label class="text-sm text-slate-600 space-y-1">
            <span>Kode warna</span>
            <input v-model="form.tag_color" type="color" class="w-full rounded-2xl border px-3 py-2" />
          </label>
          <label class="text-sm text-slate-600 space-y-1">
            <span>Sentimen</span>
            <input v-model="form.sentiment" type="text" placeholder="positive" class="w-full rounded-2xl border px-3 py-2" />
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" v-model="form.is_flagged" />
            Tandai penting
          </label>
        </div>
        <label class="text-sm text-slate-600 space-y-1">
          <span>Tindak lanjut</span>
          <textarea v-model="form.follow_up_actions" rows="3" class="w-full rounded-2xl border px-3 py-2"></textarea>
        </label>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" class="px-4 py-2 rounded-full border" @click="closeForm">Batal</button>
          <button type="submit" class="px-4 py-2 rounded-full bg-slate-900 text-white">
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  <div
    v-if="showDelete"
    role="dialog"
    aria-modal="true"
    class="fixed inset-0 bg-black/30 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 text-center space-y-4">
      <p class="text-lg text-slate-800">Hapus catatan?</p>
      <p class="text-sm text-slate-500">Tindakan ini tidak dapat dibatalkan.</p>
      <div class="flex justify-center gap-3">
        <button class="px-4 py-2 rounded-full border" @click="showDelete = false">Batal</button>
        <button class="px-4 py-2 rounded-full bg-rose-600 text-white" @click="deleteNote">Hapus</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import GuruLayout from '@/Layouts/GuruLayout.vue'
import axios from 'axios'
import { reactive, ref } from 'vue'
import { route } from 'ziggy-js'

defineOptions({ layout: GuruLayout })

const props = defineProps({
  students: {
    type: Array,
    default: () => [],
  },
  schedules: {
    type: Array,
    default: () => [],
  },
})

const notes = ref([])
const isLoading = ref(false)
const pagination = reactive({ page: 1, lastPage: 1 })
const showForm = ref(false)
const showDelete = ref(false)
const targetNote = ref(null)

const form = reactive({
  id: null,
  student_id: '',
  schedule_id: null,
  title: '',
  category: 'academic',
  note: '',
  tag_color: '#f97316',
  sentiment: '',
  is_flagged: false,
  follow_up_actions: '',
})

const fetchNotes = async (page = 1) => {
  isLoading.value = true
  try {
    const { data } = await axios.get(route('guru.api.notes.index'), { params: { page } })
    notes.value = data.data
    pagination.page = data.current_page
    pagination.lastPage = data.last_page
  } finally {
    isLoading.value = false
  }
}

const openForm = note => {
  if (note) {
    Object.assign(form, {
      id: note.id,
      student_id: note.student_id,
      schedule_id: note.schedule_id,
      title: note.title,
      category: note.category,
      note: note.note,
      tag_color: note.tag_color || '#f97316',
      sentiment: note.sentiment || '',
      is_flagged: Boolean(note.is_flagged),
      follow_up_actions: note.follow_up_actions || '',
    })
  } else {
    Object.assign(form, {
      id: null,
      student_id: props.students[0]?.id ?? '',
      schedule_id: null,
      title: '',
      category: 'academic',
      note: '',
      tag_color: '#f97316',
      sentiment: '',
      is_flagged: false,
      follow_up_actions: '',
    })
  }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
}

const saveNote = async () => {
  const payload = { ...form }
  const url = form.id
    ? route('guru.api.notes.update', form.id)
    : route('guru.api.notes.store')
  const method = form.id ? 'put' : 'post'
  await axios[method](url, payload)
  showForm.value = false
  await fetchNotes(pagination.page)
}

const confirmDelete = note => {
  targetNote.value = note
  showDelete.value = true
}

const deleteNote = async () => {
  await axios.delete(route('guru.api.notes.destroy', targetNote.value.id))
  showDelete.value = false
  await fetchNotes(pagination.page)
}

const formatDate = value => {
  if (!value) return '-'
  return new Date(value).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'long',
    hour: '2-digit',
    minute: '2-digit',
  })
}

fetchNotes()
</script>
