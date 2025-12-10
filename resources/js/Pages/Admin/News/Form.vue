<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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
  <div class="space-y-10">
    <Head :title="isEdit ? 'Edit Berita' : 'Tambah Berita'" />

    <section>
      <Link :href="route('admin.berita.index')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
        ← Kembali ke Daftar Berita
      </Link>
      <h1 class="mt-3 text-3xl font-bold text-emerald-600">
        {{ isEdit ? 'Edit Konten Berita' : 'Tambah Berita & Dokumentasi' }}
      </h1>
      <p class="mt-1 text-sm text-slate-500">
        {{ isEdit
          ? 'Perbarui artikel agar informasi selalu relevan bagi orang tua dan siswa.'
          : 'Publikasikan aktivitas terbaru lengkap dengan judul, konten, dan detail acara.' }}
      </p>
    </section>

    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
      <form @submit.prevent="submit" class="space-y-6 p-8">
        <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4 text-sm text-sky-700">
          <strong>Tips:</strong> Buat judul singkat, gunakan paragraf singkat pada konten, dan isi tanggal jika ini agenda tertentu.
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Judul Konten<span class="text-rose-500">*</span></label>
          <input v-model="form.judul" type="text" placeholder="Contoh: Kegiatan Literasi Bersama Orang Tua"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            :class="{ 'border-rose-400': form.errors.judul }" />
          <p v-if="form.errors.judul" class="mt-1 text-xs text-rose-500">{{ form.errors.judul }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Sub Judul Konten</label>
          <input v-model="form.sub_judul" type="text" placeholder="Ringkasan singkat yang menarik perhatian"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            :class="{ 'border-rose-400': form.errors.sub_judul }" />
          <p class="mt-1 text-xs text-slate-500">Sub judul ini akan tampil sebagai highlight di detail berita.</p>
          <p v-if="form.errors.sub_judul" class="mt-1 text-xs text-rose-500">{{ form.errors.sub_judul }}</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Tipe Konten</label>
            <select v-model="form.type"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              :class="{ 'border-rose-400': form.errors.type }">
              <option value="news">Berita</option>
              <option value="activity">Kegiatan</option>
              <option value="gallery">Galeri</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Gunakan "Galeri" jika ingin menyorot foto/video.</p>
            <p v-if="form.errors.type" class="mt-1 text-xs text-rose-500">{{ form.errors.type }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Tanggal Kegiatan (opsional)</label>
            <input v-model="form.event_date" type="date"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              :class="{ 'border-rose-400': form.errors.event_date }" />
            <p class="mt-1 text-xs text-slate-500">Isi jika artikel merujuk pada acara tertentu.</p>
            <p v-if="form.errors.event_date" class="mt-1 text-xs text-rose-500">{{ form.errors.event_date }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Deskripsi<span class="text-rose-500">*</span></label>
          <textarea v-model="form.konten" rows="8" placeholder="Ceritakan jalannya kegiatan, dokumentasi, atau informasi penting lain..."
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-relaxed focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            :class="{ 'border-rose-400': form.errors.konten }"></textarea>
          <p class="mt-1 text-xs text-slate-500">Gunakan enter untuk paragraf baru agar mudah dibaca.</p>
          <p v-if="form.errors.konten" class="mt-1 text-xs text-rose-500">{{ form.errors.konten }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Sampul / Foto Utama</label>
          <input type="file" accept=".jpg,.jpeg,.png" @change="(e) => handleFileChange(e, 'cover_image')"
            class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 px-4 py-2.5 text-sm text-slate-600 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            :class="{ 'border-rose-400': form.errors.cover_image }" />
          <p class="mt-1 text-xs text-slate-500">Format yang didukung: .jpg, .jpeg, .png (maks. 8MB). Tidak ada pratinjau otomatis.</p>
          <p v-if="existingCoverUrl" class="mt-1 text-xs text-slate-500">
            File saat ini: <a :href="existingCoverUrl" target="_blank" class="font-semibold text-emerald-600">Lihat gambar</a>
          </p>
          <p v-if="form.errors.cover_image" class="mt-1 text-xs text-rose-500">{{ form.errors.cover_image }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Foto Tambahan</label>
          <input type="file" accept=".jpg,.jpeg,.png" @change="(e) => handleFileChange(e, 'cover_image_secondary')"
            class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 px-4 py-2.5 text-sm text-slate-600 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            :class="{ 'border-rose-400': form.errors.cover_image_secondary }" />
          <p class="mt-1 text-xs text-slate-500">Opsional: foto kedua untuk galeri di detail berita.</p>
          <p v-if="existingSecondaryCoverUrl" class="mt-1 text-xs text-slate-500">
            File saat ini: <a :href="existingSecondaryCoverUrl" target="_blank" class="font-semibold text-emerald-600">Lihat gambar</a>
          </p>
          <p v-if="form.errors.cover_image_secondary" class="mt-1 text-xs text-rose-500">{{ form.errors.cover_image_secondary }}</p>
        </div>

        <div class="flex justify-end gap-3">
          <Link :href="route('admin.berita.index')"
            class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
            Batal
          </Link>
          <button type="submit"
            class="rounded-2xl bg-emerald-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-600 disabled:opacity-60"
            :disabled="form.processing">
            {{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Publikasikan Berita' }}
          </button>
        </div>
      </form>
    </section>

    <Notivue v-slot="notification">
      <Notification :item="notification" />
    </Notivue>
  </div>
</template>
