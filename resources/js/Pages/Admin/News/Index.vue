<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    MagnifyingGlassIcon, 
    PlusIcon, 
    NewspaperIcon, 
    PhotoIcon, 
    CalendarIcon,
    PencilSquareIcon,
    TrashIcon,
    ExclamationTriangleIcon,
    SpeakerWaveIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    berita: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
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

const resolvePaginationMeta = (paginator) => {
    if (!paginator) {
        return { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 }
    }

    const source = paginator.meta ?? paginator

    return {
        current_page: source.current_page ?? 1,
        last_page: source.last_page ?? 1,
        from: source.from ?? 0,
        to: source.to ?? 0,
        total: source.total ?? 0,
    }
}

const searchQuery = ref(props.filters.search || '')

const paginationMeta = computed(() => resolvePaginationMeta(props.berita))

const paginationInfo = computed(() => {
    const meta = paginationMeta.value
    if (!meta.total) {
        return 'Tidak ada data berita'
    }
    return `Menampilkan ${meta.from} - ${meta.to} dari ${meta.total} berita`
})

const currentPage = computed(() => paginationMeta.value.current_page)
const lastPage = computed(() => paginationMeta.value.last_page)

const pages = computed(() => {
    if (!lastPage.value) return []

    const limit = Math.min(5, lastPage.value)
    const numbers = []
    let start = Math.max(1, currentPage.value - Math.floor(limit / 2))
    let end = Math.min(lastPage.value, start + limit - 1)

    if (end - start + 1 < limit) {
        start = Math.max(1, end - limit + 1)
    }

    for (let page = start; page <= end; page++) {
        numbers.push(page)
    }

    return numbers
})

const loadBerita = (page = 1) => {
    router.get(
        route('admin.berita.index'),
        {
            page,
            search: searchQuery.value || undefined,
        },
        { preserveState: true, replace: true },
    )
}

let searchDebounceId
watch(searchQuery, () => {
    clearTimeout(searchDebounceId)
    searchDebounceId = setTimeout(() => loadBerita(1), 400)
})

const formatDate = (dateString) => {
    if (!dateString) return 'N/A'
    const d = new Date(dateString)
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    })
}

const truncate = (text, length) => {
    if (!text) return ''
    return text.length > length ? text.substring(0, length) + '...' : text
}

const formatType = (type) => {
    if (type === 'activity') return 'Kegiatan'
    if (type === 'gallery') return 'Galeri'
    return 'Berita'
}

const showDeleteModal = ref(false)
const beritaPendingDelete = ref(null)

const openDeleteModal = (item) => {
    beritaPendingDelete.value = item
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    beritaPendingDelete.value = null
}

const confirmDelete = () => {
    if (!beritaPendingDelete.value) return

    router.delete(route('admin.berita.destroy', beritaPendingDelete.value.id), {
        preserveScroll: true,
        onFinish: () => closeDeleteModal(),
    })
}

const isNewsType = computed(() => beritaPendingDelete.value?.type === 'news')

const modalTitle = computed(() => {
    const type = beritaPendingDelete.value?.type
    if (type === 'activity') return 'Hapus Kegiatan'
    if (type === 'gallery') return 'Hapus Dokumentasi'
    return 'Hapus Berita'
})

const modalMessage = computed(() => {
    const type = beritaPendingDelete.value?.type
    if (type === 'news') return 'Apakah anda yakin untuk menghapus berita kegiatan ini?'
    if (type === 'activity') return 'Apakah anda yakin untuk menghapus kegiatan ini?'
    if (type === 'gallery') return 'Apakah anda yakin untuk menghapus dokumentasi ini?'
    return 'Apakah anda yakin untuk menghapus konten ini?'
})

const modalInfoDate = computed(() => {
    if (!beritaPendingDelete.value) return ''
    return formatDate(beritaPendingDelete.value.event_date || beritaPendingDelete.value.created_at)
})
defineOptions({
    layout: AdminLayout,
})
</script>

<template>
    <div class="space-y-8">
        <Head title="Manajemen Berita" />

        <!-- Premium Header -->
        <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative">
            <div>
                 <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-600 mb-2 border border-orange-100">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                    </span>
                    <span>Pusat Informasi</span>
                </div>
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                    Berita & <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">Dokumentasi</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium text-lg">Kelola artikel berita, pengumuman kegiatan, dan galeri foto.</p>
            </div>
             <Link :href="route('admin.berita.create')"
                class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--color-primary-light)] transition-all hover:bg-[var(--color-primary-hover)] hover:scale-105 active:scale-95">
                <div class="rounded-lg bg-white/20 p-1">
                    <PlusIcon class="h-5 w-5 text-white" />
                </div>
                <span>Tambah Konten</span>
            </Link>
        </section>

        <!-- Stats Cards -->
        <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
             <div class="relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 group transition-all hover:shadow-md hover:-translate-y-1 hover:border-emerald-200">
                 <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 blur-xl group-hover:bg-emerald-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Artikel</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.total_berita }}</p>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700">
                        <NewspaperIcon class="h-3 w-3" />
                        <span>Berita & Kegiatan</span>
                    </div>
                 </div>
            </div>

            <div class="relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 group transition-all hover:shadow-md hover:-translate-y-1 hover:border-amber-200">
                 <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-amber-50 opacity-50 blur-xl group-hover:bg-amber-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Galeri</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.total_foto }}</p>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700">
                        <PhotoIcon class="h-3 w-3" />
                         <span>Dokumentasi Foto</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Card -->
        <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
             <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                         <SpeakerWaveIcon class="h-6 w-6 text-slate-400" />
                        Daftar Publikasi
                    </h2>
                     <p class="text-sm text-slate-500 font-medium mt-1">Gunakan pencarian untuk menemukan artikel lama.</p>
                </div>
                <div class="relative w-full md:w-80">
                     <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <MagnifyingGlassIcon class="h-5 w-5" />
                    </div>
                    <input v-model="searchQuery" type="text" placeholder="Cari ID atau judul berita..."
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 py-3 text-sm font-medium text-slate-700 placeholder-slate-400 transition-all focus:border-[var(--color-primary)] focus:bg-white focus:ring-[var(--color-primary)]" />
                </div>
            </header>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Judul</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Tipe</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Tanggal</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="item in berita.data" :key="item.id" class="group hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded border border-slate-200">
                                    #{{ String(item.id).padStart(3, '0') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-slate-800 line-clamp-1" :title="item.judul">{{ item.judul }}</p>
                                    <p class="text-xs text-slate-500 line-clamp-1 mt-1">{{ truncate(item.konten, 60) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                 <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold border shadow-sm"
                                    :class="item.type === 'activity'
                                        ? 'bg-indigo-50 text-indigo-600 border-indigo-100'
                                        : item.type === 'gallery'
                                            ? 'bg-amber-50 text-amber-600 border-amber-100'
                                            : 'bg-emerald-50 text-emerald-600 border-emerald-100'">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ formatType(item.type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-medium">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="route('admin.berita.edit', item.id)"
                                        class="group/btn inline-flex items-center justify-center h-8 w-8 rounded-full bg-amber-50 text-amber-600 transition-all hover:bg-amber-100 hover:scale-110 active:scale-95 border border-amber-100"
                                        title="Edit Konten">
                                        <PencilSquareIcon class="h-4 w-4" />
                                    </Link>
                                    <button @click="openDeleteModal(item)"
                                        class="group/btn inline-flex items-center justify-center h-8 w-8 rounded-full bg-rose-50 text-rose-600 transition-all hover:bg-rose-100 hover:text-rose-700 hover:scale-110 active:scale-95 border border-rose-100"
                                        title="Hapus Konten">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="berita.data.length === 0">
                            <td colspan="5" class="px-6 py-16 text-center">
                                 <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 border border-slate-100">
                                        <NewspaperIcon class="h-8 w-8 text-slate-300" />
                                    </div>
                                    <p class="text-lg font-bold text-slate-700">Belum ada konten</p>
                                    <p class="text-sm text-slate-500 mt-1">Mulailah dengan menambahkan berita atau galeri baru.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex flex-col gap-4 border-t border-slate-100 pt-6 text-sm lg:flex-row lg:items-center lg:justify-between">
                <p class="font-medium text-slate-500">{{ paginationInfo }}</p>
                <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-full border border-slate-200 w-fit">
                    <button @click="loadBerita(currentPage - 1)" :disabled="currentPage === 1"
                        class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="currentPage === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm'">
                        &larr; Prev
                    </button>
                    <div class="h-4 w-px bg-slate-200"></div>
                     <button v-for="page in pages" :key="page" @click="loadBerita(page)"
                        class="h-8 w-8 rounded-full text-xs font-bold flex items-center justify-center transition-all"
                        :class="page === currentPage
                            ? 'bg-[var(--color-primary)] text-white shadow-md'
                            : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)]'">
                        {{ page }}
                    </button>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <button @click="loadBerita(currentPage + 1)" :disabled="currentPage === lastPage"
                         class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="currentPage === lastPage ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm'">
                        Next &rarr;
                    </button>
                </div>
            </div>
        </section>

        <!-- Delete Modal -->
        <transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="w-full max-w-md rounded-[2rem] bg-white shadow-2xl scale-100 transition-all border border-slate-200">
                    <div class="p-8 text-center">
                         <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-rose-600 mb-6 ring-8 ring-rose-50">
                            <ExclamationTriangleIcon class="h-8 w-8" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">{{ modalTitle }}</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">{{ modalMessage }}</p>

                         <div v-if="isNewsType" class="mt-6 space-y-3 rounded-2xl bg-slate-50 p-4 border border-slate-100 text-left">
                           <div class="flex items-start gap-3">
                               <NewspaperIcon class="h-5 w-5 text-slate-400 mt-0.5 shrink-0" />
                               <div>
                                   <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Judul Berita</p>
                                   <p class="text-sm font-semibold text-slate-800 line-clamp-2">{{ beritaPendingDelete?.judul || '—' }}</p>
                               </div>
                           </div>
                           <div class="flex items-center gap-3">
                               <CalendarIcon class="h-5 w-5 text-slate-400 shrink-0" />
                               <div>
                                   <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Tanggal</p>
                                   <p class="text-sm font-semibold text-slate-800">{{ modalInfoDate }}</p>
                               </div>
                           </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="closeDeleteModal"
                                class="flex-1 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 active:scale-95">
                                Batal
                            </button>
                            <button type="button" @click="confirmDelete"
                                class="flex-1 rounded-xl bg-rose-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-700 active:scale-95">
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>

        <Notivue v-slot="notification">
            <Notification :item="notification" />
        </Notivue>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.98);
}
</style>
