<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    BookOpenIcon, 
    ArrowLeftIcon, 
    HashtagIcon, 
    UserIcon, 
    TagIcon, 
    InformationCircleIcon, 
    CalendarIcon,
    DocumentTextIcon,
    Bars3BottomLeftIcon,
    ArchiveBoxIcon,
    CheckCircleIcon,
    ShieldCheckIcon,
    PencilSquareIcon,
    PencilIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    book: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    statuses: {
        type: Object,
        default: () => ({}),
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

const isEdit = computed(() => !!props.book)

const form = useForm({
    code: props.book?.code ?? '',
    title: props.book?.title ?? '',
    author: props.book?.author ?? '',
    category: props.book?.category ?? props.categories[0] ?? 'Umum',
    status: props.book?.status ?? 'available',
    published_year: props.book?.published_year ?? new Date().getFullYear(),
    total_pages: props.book?.total_pages ?? 0,
    total_stock: props.book?.total_stock ?? 1,
    available_stock: props.book?.available_stock ?? 1,
    description: props.book?.description ?? '',
})

const ensureValidStatus = () => {
    const statusOptions = props.statuses || {}
    if (!statusOptions || Object.keys(statusOptions).length === 0) {
        return
    }

    if (!Object.prototype.hasOwnProperty.call(statusOptions, form.status)) {
        const fallback = Object.keys(statusOptions)[0] ?? ''
        form.status = fallback
    }
}

ensureValidStatus()

watch(
    () => form.total_stock,
    (value) => {
        if (!value) return
        if (form.available_stock > value) {
            form.available_stock = value
        }
    },
)

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.books.update', props.book.id))
    } else {
        form.post(route('admin.books.store'))
    }
}
</script>

<template>
    <div class="space-y-8">
        <Head :title="isEdit ? 'Edit Buku' : 'Tambah Buku'" />

        <!-- Header Section -->
        <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
            <div>
                 <Link :href="route('admin.books.index')" class="group inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-sky-600 transition-colors mb-4">
                    <div class="rounded-full bg-slate-100 p-1 group-hover:bg-sky-100 transition-colors">
                        <ArrowLeftIcon class="h-4 w-4" />
                    </div>
                    <span>Kembali ke Katalog</span>
                </Link>
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                    {{ isEdit ? 'Edit' : 'Tambah' }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-indigo-600">Buku Koleksi</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium text-lg max-w-2xl">
                    {{ isEdit ? 'Perbarui informasi detail buku dalam koleksi perpustakaan.' : 'Daftarkan judul buku baru ke dalam inventaris perpustakaan.' }}
                </p>
            </div>
        </section>

        <section class="rounded-[2.5rem] border border-slate-200 bg-white shadow-sm overflow-hidden">
             <!-- Form Header -->
            <div class="px-8 pt-8 pb-4 border-b border-slate-100 bg-sky-50/30">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <PencilIcon class="h-6 w-6 text-sky-600" />
                    Detail & Spesifikasi Buku
                </h2>
            </div>
            
            <form @submit.prevent="submit" class="p-8 space-y-8">
                <!-- Info Section -->
                 <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4 flex items-start gap-3">
                    <InformationCircleIcon class="h-6 w-6 text-sky-600 shrink-0 mt-0.5" />
                    <div>
                         <p class="text-sm font-bold text-sky-800">Tips Pengisian</p>
                         <p class="text-xs text-sky-600 mt-1 leading-relaxed">
                             Gunakan <strong>Kode Buku</strong> yang unik, isi <strong>deskripsi</strong> singkat untuk membantu pencarian, dan sesuaikan <strong>Stok Tersedia</strong> dengan kondisi real di rak.
                         </p>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-2">
                    <!-- Left Column: Primary Information -->
                    <div class="space-y-6">
                         <div class="flex items-center gap-2 mb-2">
                            <DocumentTextIcon class="h-5 w-5 text-sky-600" />
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Informasi Utama</h3>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Kode Buku <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                        <HashtagIcon class="h-5 w-5" />
                                    </div>
                                    <input v-model="form.code" type="text" placeholder="BK-001"
                                        class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all bg-slate-50 hover:bg-white"
                                        :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.code }" />
                                </div>
                                <p v-if="form.errors.code" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.code }}</p>
                            </div>

                            <div class="sm:col-span-1">
                                <label class="block text-sm font-bold text-slate-700 mb-2">Tahun Terbit</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                        <CalendarIcon class="h-5 w-5" />
                                    </div>
                                    <input v-model.number="form.published_year" type="number"
                                        class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all bg-slate-50 hover:bg-white"
                                        :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.published_year }" />
                                </div>
                                <p v-if="form.errors.published_year" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.published_year }}</p>
                            </div>
                        </div>

                        <div>
                             <label class="block text-sm font-bold text-slate-700 mb-2">Judul Buku <span class="text-rose-500">*</span></label>
                             <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <BookOpenIcon class="h-5 w-5" />
                                </div>
                                <input v-model="form.title" type="text" placeholder="Masukkan judul buku lengkap"
                                    class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all bg-slate-50 hover:bg-white"
                                    :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.title }" />
                            </div>
                            <p v-if="form.errors.title" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.title }}</p>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                             <div>
                                 <label class="block text-sm font-bold text-slate-700 mb-2">Penulis</label>
                                 <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                        <UserIcon class="h-5 w-5" />
                                    </div>
                                    <input v-model="form.author" type="text" placeholder="Nama penulis"
                                        class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all bg-slate-50 hover:bg-white"
                                        :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.author }" />
                                </div>
                                <p v-if="form.errors.author" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.author }}</p>
                            </div>

                             <div>
                                 <label class="block text-sm font-bold text-slate-700 mb-2">Kategori</label>
                                 <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                        <TagIcon class="h-5 w-5" />
                                    </div>
                                    <select v-model="form.category"
                                        class="w-full appearance-none rounded-2xl border border-slate-200 pl-11 pr-10 py-3 text-sm font-bold focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all bg-slate-50 hover:bg-white cursor-pointer"
                                        :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.category }">
                                        <option v-for="category in categories" :key="category" :value="category">
                                            {{ category }}
                                        </option>
                                    </select>
                                     <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <p v-if="form.errors.category" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.category }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi & Sinopsis</label>
                            <div class="relative">
                                 <div class="pointer-events-none absolute top-3 left-0 flex items-start pl-4 text-slate-400">
                                    <Bars3BottomLeftIcon class="h-5 w-5" />
                                </div>
                                <textarea v-model="form.description" rows="5" placeholder="Tuliskan sinopsis singkat atau catatan buku..."
                                    class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium leading-relaxed focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all bg-slate-50 hover:bg-white resize-none"
                                    :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.description }"></textarea>
                            </div>
                            <p v-if="form.errors.description" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.description }}</p>
                        </div>
                    </div>

                    <!-- Right Column: Status & Inventory -->
                    <div class="space-y-6">
                        <div class="rounded-[2.5rem] border border-slate-100 bg-slate-50 p-7 space-y-8">
                            <div>
                                <div class="flex items-center gap-2 mb-4">
                                    <ArchiveBoxIcon class="h-5 w-5 text-indigo-600" />
                                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Status & Inventaris</h3>
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Status Koleksi</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <label v-for="(label, value) in statuses" :key="value" 
                                                class="cursor-pointer group relative flex items-center gap-3 rounded-2xl border p-4 transition-all"
                                                :class="form.status === value ? 'bg-white border-sky-300 ring-4 ring-sky-100 shadow-sm' : 'bg-white/50 border-slate-200 hover:bg-white hover:border-slate-300'">
                                                <input type="radio" :value="value" v-model="form.status" class="sr-only" />
                                                <div class="h-5 w-5 rounded-full border border-slate-300 flex items-center justify-center bg-white shadow-inner"
                                                    :class="{'border-sky-500': form.status === value}">
                                                    <div v-if="form.status === value" class="h-2.5 w-2.5 rounded-full bg-sky-500"></div>
                                                </div>
                                                <span class="text-sm font-bold" :class="form.status === value ? 'text-sky-700' : 'text-slate-600'">{{ label }}</span>
                                            </label>
                                        </div>
                                        <p v-if="form.errors.status" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.status }}</p>
                                    </div>

                                    <div class="grid gap-6 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2 font-mono">JUMLAH HALAMAN</label>
                                            <div class="relative">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                                    <DocumentTextIcon class="h-5 w-5" />
                                                </div>
                                                <input v-model.number="form.total_pages" type="number" min="0"
                                                    class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-bold focus:border-sky-500 focus:bg-white focus:outline-none transition-all bg-white" />
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2 font-mono">TOTAL STOK</label>
                                            <div class="relative">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                                    <ArchiveBoxIcon class="h-5 w-5" />
                                                </div>
                                                <input v-model.number="form.total_stock" type="number" min="1"
                                                    class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-bold focus:border-sky-500 focus:bg-white focus:outline-none transition-all bg-white" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm">
                                        <div class="flex items-center justify-between mb-4">
                                            <label class="text-sm font-bold text-slate-700 font-mono uppercase">Stok Tersedia Saat Ini</label>
                                            <CheckCircleIcon class="h-5 w-5 text-emerald-500" />
                                        </div>
                                        <div class="relative">
                                            <input v-model.number="form.available_stock" type="number" min="0" :max="form.total_stock"
                                                class="w-full rounded-2xl border-2 border-slate-100 bg-slate-50 px-6 py-4 text-3xl font-black text-slate-800 text-center focus:border-emerald-500 focus:bg-white focus:outline-none transition-all" />
                                        </div>
                                        <div class="mt-4 flex items-center gap-2 text-xs font-bold text-slate-400 justify-center">
                                            <InformationCircleIcon class="h-4 w-4" />
                                            <span>Maksimal: {{ form.total_stock }} buku</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <Link :href="route('admin.books.index')"
                        class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-800 active:scale-95">
                    Batal
                    </Link>
                    <button type="submit"
                        class="group inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--color-primary)] px-8 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--color-primary-light)] transition hover:bg-[var(--color-primary-hover)] hover:scale-105 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
                        :disabled="form.processing">
                         <div v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
                        <span>{{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Simpan Buku' }}</span>
                    </button>
                </div>
            </form>
        </section>
    </div>
    <Notivue v-slot="notification">
        <Notification :item="notification" />
    </Notivue>
</template>
