<template>
  <div class="space-y-8">
    <Head title="Catatan Guru" />

    <!-- Premium Header -->
    <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
      <div>
         <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-600 mb-2 border border-orange-100">
            <BookmarkSquareIcon class="h-3 w-3" />
            <span>Jurnal Guru</span>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
             Catatan & <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">Evaluasi</span>
        </h1>
        <p class="mt-2 text-slate-500 font-medium text-lg max-w-2xl">
            Catat perkembangan perilaku dan akademik siswa secara detail.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3">
           <button
            class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-white border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-orange-600 active:scale-95 transition-all"
            @click="fetchNotes()"
          >
             <ArrowPathIcon class="h-5 w-5 group-hover:rotate-180 transition-transform duration-500" />
            <span>Muat Ulang</span>
          </button>
           <button
            class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-200 transition-all hover:bg-orange-600 hover:scale-105 active:scale-95"
            @click="openForm()"
          >
            <div class="rounded-lg bg-white/20 p-1 group-hover:bg-white/30 transition-colors">
                <PlusIcon class="h-5 w-5 text-white" />
            </div>
            <span>Catatan Baru</span>
          </button>
      </div>
    </section>

    <!-- Notes Grid -->
    <section>
        <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
             <div v-for="n in 3" :key="n" class="h-64 animate-pulse rounded-[2.5rem] bg-slate-100"></div>
        </div>

        <div v-else-if="!notes.length" class="rounded-[2.5rem] border border-dashed border-slate-300 bg-slate-50/50 p-12 text-center">
            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-white shadow-sm mb-4">
                 <ClipboardDocumentListIcon class="h-8 w-8 text-slate-300" />
            </div>
            <p class="text-slate-500 font-medium text-lg">Belum ada catatan dibuat.</p>
            <p class="text-slate-400 text-sm mt-1">Mulai dengan mencatat perkembangan siswa.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <article
                v-for="note in notes"
                :key="note.id"
                class="group relative flex flex-col rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-orange-200 hover:-translate-y-1"
            >
                <div class="flex items-start justify-between mb-4">
                     <div class="flex items-center gap-2">
                         <span class="w-2.5 h-2.5 rounded-full ring-2 ring-white shadow-sm" :style="{ backgroundColor: note.tag_color || '#c084fc' }"></span>
                         <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ note.category }}</span>
                     </div>
                     <span class="text-xs font-medium text-slate-400 bg-slate-50 px-2 py-1 rounded-lg">{{ formatDate(note.recorded_at) }}</span>
                </div>

                <div class="mb-4">
                     <h3 class="text-xl font-bold text-slate-800 line-clamp-1 group-hover:text-orange-600 transition-colors">{{ note.title }}</h3>
                     <p class="text-sm font-medium text-slate-500 mt-1 flex items-center gap-1.5">
                         <UserIcon class="h-3.5 w-3.5" />
                         {{ note.student?.name || 'Siswa tidak diketahui' }}
                     </p>
                </div>

                <div class="flex-1 mb-6">
                    <p class="text-sm text-slate-600 leading-relaxed line-clamp-3 bg-slate-50 p-3 rounded-2xl border border-slate-100 italic">
                        "{{ note.note }}"
                    </p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-50 mt-auto">
                     <span v-if="note.is_flagged" class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded-md uppercase tracking-wide">
                        <FlagIcon class="h-3 w-3" /> Penting
                     </span>
                     <span v-else></span>

                     <div class="flex gap-2">
                          <button
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors"
                            @click="openForm(note)"
                            title="Edit"
                        >
                             <PencilSquareIcon class="h-5 w-5" />
                        </button>
                        <button
                            class="p-2 rounded-xl text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                            @click="confirmDelete(note)"
                            title="Hapus"
                        >
                             <TrashIcon class="h-5 w-5" />
                        </button>
                     </div>
                </div>
            </article>
        </div>
    </section>

    <!-- Modal Form (Add/Edit) -->
    <Modal
      :show="showForm"
      :title="form.id ? 'Perbarui Catatan' : 'Catatan Baru'"
      variant="warning"
      max-width="2xl"
      @close="closeForm"
    >
      <template #description>
         {{ form.id ? 'Edit detail catatan yang sudah ada.' : 'Tambahkan catatan evaluasi atau perkembangan siswa.' }}
      </template>

      <form class="space-y-6" @submit.prevent="saveNote">
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                 <label class="block text-sm font-bold text-slate-700 mb-1.5">Siswa</label>
                 <div class="relative">
                    <select v-model="form.student_id" required class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 outline-none transition-all">
                        <option disabled value="">Pilih Siswa</option>
                        <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <UserIcon class="h-4 w-4" />
                    </div>
                 </div>
            </div>
            <div>
                 <label class="block text-sm font-bold text-slate-700 mb-1.5">Jadwal Referensi</label>
                 <div class="relative">
                    <select v-model="form.schedule_id" class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 outline-none transition-all">
                        <option :value="null">Umum / Tanpa Jadwal</option>
                        <option v-for="schedule in schedules" :key="schedule.id" :value="schedule.id">{{ schedule.topic }}</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                        <CalendarIcon class="h-4 w-4" />
                    </div>
                 </div>
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
             <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Judul Catatan</label>
                <input v-model="form.title" type="text" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none" placeholder="Ringkasan singkat" />
             </div>
             <div>
                 <label class="block text-sm font-bold text-slate-700 mb-1.5">Kategori</label>
                 <select v-model="form.category" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-100 outline-none">
                    <option value="academic">Akademik</option>
                    <option value="behavior">Perilaku</option>
                    <option value="communication">Komunikasi</option>
                    <option value="general">Umum</option>
                 </select>
             </div>
         </div>

         <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">Detail Catatan</label>
            <textarea v-model="form.note" rows="4" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none resize-none" placeholder="Tuliskan pengamatan anda..."></textarea>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
             <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Warna Label</label>
                <input v-model="form.tag_color" type="color" class="w-full h-10 rounded-lg border border-slate-200 cursor-pointer" />
             </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5">Sentimen</label>
                <input v-model="form.sentiment" type="text" placeholder="Positif/Negatif" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-orange-500 focus:outline-none" />
             </div>
              <div class="flex items-center">
                <label class="flex items-center gap-3 cursor-pointer group">
                  <div class="relative flex items-center">
                     <input type="checkbox" v-model="form.is_flagged" class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:bg-rose-500 checked:border-rose-500" />
                     <CheckIcon class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 h-3.5 w-3.5 text-white opacity-0 peer-checked:opacity-100" />
                  </div>
                  <span class="text-sm font-bold text-slate-600 group-hover:text-rose-600 transition-colors">Tandai Penting</span>
                </label>
             </div>
         </div>

         <div>
            <label class="block text-sm font-bold text-slate-700 mb-1.5">Rencana Tindak Lanjut</label>
            <textarea v-model="form.follow_up_actions" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none resize-none" placeholder="Langkah apa yang akan diambil?"></textarea>
         </div>

         <div class="flex justify-end gap-3 pt-2">
            <button type="button" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="closeForm">Batal</button>
            <button type="submit" class="rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-200 hover:bg-slate-900 active:scale-95 transition-transform">Simpan Catatan</button>
         </div>
      </form>
    </Modal>

    <!-- Modal Delete -->
    <Modal :show="showDelete" title="Hapus Catatan" variant="danger" max-width="sm" @close="showDelete = false">
        <template #description>Konfirmasi penghapusan permanen.</template>
        <div class="space-y-6">
            <div class="bg-rose-50 p-4 rounded-xl border border-rose-100 text-rose-800 font-bold text-sm text-center">
                Apakah Anda yakin ingin menghapus catatan ini? <br>Data yang dihapus tidak dapat dipulihkan.
            </div>
            <div class="flex justify-center gap-3">
                <button class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="showDelete = false">Batalkan</button>
                <button class="rounded-xl bg-rose-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-200 hover:bg-rose-700 active:scale-95 transition-transform" @click="deleteNote">Ya, Hapus</button>
            </div>
        </div>
    </Modal>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import GuruLayout from '@/Layouts/GuruLayout.vue'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'
import { reactive, ref } from 'vue'
import { route } from 'ziggy-js'
import {
  BookmarkSquareIcon,
  PlusIcon,
  ArrowPathIcon,
  ClipboardDocumentListIcon,
  UserIcon,
  CalendarIcon,
  TagIcon,
  FlagIcon,
  PencilSquareIcon,
  TrashIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

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
