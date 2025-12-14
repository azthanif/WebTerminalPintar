<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    NewspaperIcon, 
    ArrowLeftIcon, 
    PencilSquareIcon, 
    PhotoIcon, 
    CalendarIcon, 
    DocumentTextIcon, 
    TagIcon, 
    CloudArrowUpIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  news: {
    type: Object,
    default: null,
  },
})

defineOptions({
  layout: AdminLayout,
})

const page = usePage()
const FLASH_NOTIFICATION_DURATION = 4000

const pushFlashNotification = (type, message) => {
  if (!message) {
    return
  }

  const payload = {
    title: type === 'success' ? 'Berhasil' : 'Terjadi Kesalahan',
    message,
    duration: FLASH_NOTIFICATION_DURATION,
  }

  if (type === 'success') {
    push.success(payload)
  } else {
    push.error(payload)
  }
}

watch(
  () => ({
    success: page.props.flash?.success ?? null,
    error: page.props.flash?.error ?? null,
  }),
  ({ success, error }) => {
    if (success) {
      pushFlashNotification('success', success)
    }

    if (error) {
      pushFlashNotification('error', error)
    }
  },
  { immediate: true },
)

const isEdit = computed(() => !!props.news)

const existingCoverUrl = computed(() => props.news?.cover_url ?? null)
const existingSecondaryCoverUrl = computed(() => props.news?.cover_secondary_url ?? null)

const form = useForm({
  judul: props.news?.judul ?? '',
  sub_judul: props.news?.sub_judul ?? '',
  konten: props.news?.konten ?? '',
  event_date: props.news?.event_date ?? '',
  type: props.news?.type ?? 'news',
  cover_image: null,
  cover_image_secondary: null,
})

const submit = () => {
  const options = {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => form.reset('cover_image', 'cover_image_secondary'),
  }

  if (isEdit.value) {
    form
      .transform((data) => ({
        ...data,
        _method: 'put',
      }))
      .post(route('admin.berita.update', props.news.id), options)
  } else {
    form.transform((data) => data).post(route('admin.berita.store'), options)
  }
}

const handleFileChange = (event, field) => {
  const file = event.target.files?.[0]
  form[field] = file ?? null
}
</script>

<template>
  <div class="space-y-8">
    <Head :title="isEdit ? 'Edit Berita' : 'Tambah Berita'" />

    <!-- Header Section -->
    <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
      <div>
         <Link :href="route('admin.berita.index')" class="group inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-orange-600 transition-colors mb-4">
            <div class="rounded-full bg-slate-100 p-1 group-hover:bg-orange-100 transition-colors">
                <ArrowLeftIcon class="h-4 w-4" />
            </div>
            <span>Kembali ke Berita</span>
        </Link>
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
            {{ isEdit ? 'Edit' : 'Tambah' }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">Konten</span>
        </h1>
        <p class="mt-2 text-slate-500 font-medium text-lg max-w-2xl">
            {{ isEdit ? 'Perbarui informasi artikel atau dokumentasi.' : 'Publikasikan informasi baru untuk siswa dan orang tua.' }}
        </p>
      </div>
    </section>

    <!-- Main Content -->
    <section class="rounded-[2.5rem] border border-slate-200 bg-white shadow-sm overflow-hidden">
       <!-- Form Header -->
        <div class="px-8 pt-8 pb-4 border-b border-slate-100 bg-orange-50/30">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <PencilSquareIcon class="h-6 w-6 text-orange-600" />
                Detail Konten
            </h2>
        </div>

      <form @submit.prevent="submit" class="p-8 space-y-8">
        <!-- Info Box -->
        <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4 flex items-start gap-3">
            <InformationCircleIcon class="h-6 w-6 text-orange-600 shrink-0 mt-0.5" />
            <div>
                 <p class="text-sm font-bold text-orange-800">Tips Penulisan</p>
                 <p class="text-xs text-orange-600 mt-1 leading-relaxed">
                     Buat judul yang <strong>menarik</strong> dan singkat. Gunakan paragraf pendek agar mudah dibaca di perangkat mobile.
                 </p>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
           <!-- Left Column: Content Details -->
            <div class="space-y-6">
                 <div>
                     <label class="block text-sm font-bold text-slate-700 mb-2">Judul Utama <span class="text-rose-500">*</span></label>
                     <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <NewspaperIcon class="h-5 w-5" />
                        </div>
                        <input v-model="form.judul" type="text" placeholder="Contoh: Perayaan Hari Kartini"
                            class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all bg-slate-50 hover:bg-white"
                            :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.judul }" />
                    </div>
                    <p v-if="form.errors.judul" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.judul }}</p>
                </div>

                <div>
                     <label class="block text-sm font-bold text-slate-700 mb-2">Sub Judul / Ringkasan</label>
                     <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <DocumentTextIcon class="h-5 w-5" />
                        </div>
                        <input v-model="form.sub_judul" type="text" placeholder="Ringkasan singkat yang menarik..."
                            class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all bg-slate-50 hover:bg-white"
                            :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.sub_judul }" />
                    </div>
                    <p v-if="form.errors.sub_judul" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.sub_judul }}</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                  <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tipe Konten</label>
                    <div class="relative">
                         <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <TagIcon class="h-5 w-5" />
                        </div>
                        <select v-model="form.type"
                          class="w-full appearance-none rounded-2xl border border-slate-200 pl-11 pr-10 py-3 text-sm font-bold focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all bg-slate-50 hover:bg-white cursor-pointer"
                          :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.type }">
                          <option value="news">Berita</option>
                          <option value="activity">Kegiatan</option>
                          <option value="gallery">Galeri Foto</option>
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <p v-if="form.errors.type" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.type }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal (Opsional)</label>
                     <div class="relative">
                         <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <CalendarIcon class="h-5 w-5" />
                        </div>
                        <input v-model="form.event_date" type="date"
                          class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all bg-slate-50 hover:bg-white"
                          :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.event_date }" />
                    </div>
                    <p v-if="form.errors.event_date" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.event_date }}</p>
                  </div>
                </div>

                 <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Konten Lengkap <span class="text-rose-500">*</span></label>
                    <textarea v-model="form.konten" rows="8" placeholder="Tuliskan isi berita atau deskripsi kegiatan di sini..."
                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium leading-relaxed focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all bg-slate-50 hover:bg-white resize-y"
                        :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.konten }"></textarea>
                    <p v-if="form.errors.konten" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.konten }}</p>
                </div>
            </div>

            <!-- Right Column: Media Uploads -->
            <div class="space-y-6">
                <div class="rounded-[2rem] border border-slate-100 bg-slate-50/50 p-6 space-y-6">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <PhotoIcon class="h-5 w-5 text-orange-600" />
                        Media & Dokumentasi
                    </h3>

                    <div>
                      <label class="block text-sm font-bold text-slate-700 mb-2">Sampul Utama</label>
                      <label class="group relative flex flex-col items-center justify-center w-full h-40 rounded-2xl border-2 border-dashed border-slate-300 bg-white hover:bg-orange-50 hover:border-orange-300 transition-all cursor-pointer overflow-hidden">
                         <div v-if="existingCoverUrl && !form.cover_image" class="absolute inset-0 flex items-center justify-center bg-slate-100">
                             <img :src="existingCoverUrl" class="h-full w-full object-cover" />
                             <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                 <span class="text-white font-bold text-sm">Ganti Gambar</span>
                             </div>
                         </div>
                         <div v-else class="flex flex-col items-center justify-center pt-5 pb-6">
                            <CloudArrowUpIcon class="w-8 h-8 text-slate-400 mb-2 group-hover:text-orange-500 transition-colors" />
                            <p class="mb-1 text-sm text-slate-500 font-medium"><span class="font-bold text-orange-600">Klik upload</span> atau drag folder</p>
                            <p class="text-xs text-slate-400">JPG, PNG (Max 8MB)</p>
                             <p v-if="form.cover_image" class="mt-2 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                                {{ form.cover_image.name }}
                            </p>
                        </div>
                        <input type="file" accept=".jpg,.jpeg,.png" @change="(e) => handleFileChange(e, 'cover_image')" class="hidden" />
                      </label>
                       <p v-if="form.errors.cover_image" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.cover_image }}</p>
                    </div>

                    <div>
                      <label class="block text-sm font-bold text-slate-700 mb-2">Foto Tambahan (Opsional)</label>
                       <label class="group relative flex flex-col items-center justify-center w-full h-40 rounded-2xl border-2 border-dashed border-slate-300 bg-white hover:bg-orange-50 hover:border-orange-300 transition-all cursor-pointer overflow-hidden">
                         <div v-if="existingSecondaryCoverUrl && !form.cover_image_secondary" class="absolute inset-0 flex items-center justify-center bg-slate-100">
                             <img :src="existingSecondaryCoverUrl" class="h-full w-full object-cover" />
                             <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                 <span class="text-white font-bold text-sm">Ganti Gambar</span>
                             </div>
                         </div>
                        <div v-else class="flex flex-col items-center justify-center pt-5 pb-6">
                            <CloudArrowUpIcon class="w-8 h-8 text-slate-400 mb-2 group-hover:text-orange-500 transition-colors" />
                            <p class="mb-1 text-sm text-slate-500 font-medium"><span class="font-bold text-orange-600">Klik upload</span> atau drag folder</p>
                            <p class="text-xs text-slate-400">JPG, PNG (Max 8MB)</p>
                            <p v-if="form.cover_image_secondary" class="mt-2 text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-lg">
                                {{ form.cover_image_secondary.name }}
                            </p>
                        </div>
                        <input type="file" accept=".jpg,.jpeg,.png" @change="(e) => handleFileChange(e, 'cover_image_secondary')" class="hidden" />
                      </label>
                       <p v-if="form.errors.cover_image_secondary" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.cover_image_secondary }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
          <Link :href="route('admin.berita.index')"
            class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-800 active:scale-95">
            Batal
          </Link>
          <button type="submit"
            class="group inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--color-primary)] px-8 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--color-primary-light)] transition hover:bg-[var(--color-primary-hover)] hover:scale-105 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
            :disabled="form.processing">
             <div v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
            <span>{{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Publikasikan Berita' }}</span>
          </button>
        </div>
      </form>
    </section>

    <Notivue v-slot="notification">
      <Notification :item="notification" />
    </Notivue>
  </div>
</template>
