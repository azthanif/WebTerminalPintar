<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
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

// Dynamic colors based on content type
const getTypeColor = (type) => {
    if (type === 'news') {
        return { bg: 'bg-rose-100', text: 'text-rose-700', border: 'border-rose-200', dot: 'bg-rose-500' }
    } else if (type === 'activity') {
        return { bg: 'bg-blue-100', text: 'text-blue-700', border: 'border-blue-200', dot: 'bg-blue-500' }
    } else if (type === 'gallery') {
        return { bg: 'bg-amber-100', text: 'text-amber-700', border: 'border-amber-200', dot: 'bg-amber-500' }
    }
    return { bg: 'bg-slate-100', text: 'text-slate-600', border: 'border-slate-200', dot: 'bg-slate-400' }
}

// Avatar gradient colors based on type
const getAvatarColor = (type) => {
    if (type === 'news') {
        return { bg: 'bg-gradient-to-br from-rose-400 to-rose-600', text: 'text-white', ring: 'ring-rose-300' }
    } else if (type === 'activity') {
        return { bg: 'bg-gradient-to-br from-blue-400 to-blue-600', text: 'text-white', ring: 'ring-blue-300' }
    } else if (type === 'gallery') {
        return { bg: 'bg-gradient-to-br from-amber-400 to-amber-600', text: 'text-white', ring: 'ring-amber-300' }
    }
    return { bg: 'bg-gradient-to-br from-slate-400 to-slate-500', text: 'text-white', ring: 'ring-slate-300' }
}

defineOptions({
    layout: AdminLayout,
})

const user = computed(() => page.props.auth?.user ?? null)
</script>

<template>
    <div class="space-y-8">
        <Head title="Manajemen Berita" />

        <!-- Page Header -->
        <PageHeader badgeLabel="Content Portal" badgeColor="red">
            <template #title>
                <p class="text-2xl font-bold text-slate-700 mt-0.5" style="font-family: 'Poppins', sans-serif;">
                    Selamat datang kembali, <span class="text-[var(--color-primary)]">{{ user?.name }}</span>!
                </p>
            </template>
        </PageHeader>

        <!-- Stats Cards -->
        <section class="grid gap-6 md:grid-cols-2">
             <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-gray-100 transition-all hover:shadow-md hover:-translate-y-1">
                 <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-rose-50 opacity-50 blur-xl group-hover:bg-rose-100 transition-colors"></div>
                 <div class="relative">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Artikel</p>
                        <div class="h-10 w-10 rounded-full bg-rose-100 flex items-center justify-center">
                            <NewspaperIcon class="h-5 w-5 text-rose-600" />
                        </div>
                    </div>
                    <p class="text-4xl font-extrabold text-slate-800">{{ stats.total_berita }}</p>
                    <p class="text-sm text-rose-600 font-medium mt-1">Berita & Kegiatan</p>
                 </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-gray-100 transition-all hover:shadow-md hover:-translate-y-1">
                 <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-amber-50 opacity-50 blur-xl group-hover:bg-amber-100 transition-colors"></div>
                 <div class="relative">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Galeri</p>
                        <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center">
                            <PhotoIcon class="h-5 w-5 text-amber-600" />
                        </div>
                    </div>
                    <p class="text-4xl font-extrabold text-slate-800">{{ stats.total_foto }}</p>
                    <p class="text-sm text-amber-600 font-medium mt-1">Dokumentasi Foto</p>
                </div>
            </div>
        </section>

        <!-- Content Card -->
        <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
            <!-- Table Toolbar (Standardized) -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <!-- Left: Title -->
                <div class="w-full md:w-1/4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <SpeakerWaveIcon class="h-6 w-6 text-slate-400" />
                        Daftar Publikasi
                    </h2>
                </div>

                <!-- Center: Search Bar -->
                <div class="w-full md:w-1/2 flex justify-center">
                    <div class="relative w-full max-w-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <MagnifyingGlassIcon class="h-5 w-5" />
                        </div>
                        <input v-model="searchQuery" type="text"
                            placeholder="Cari ID atau judul berita..."
                            class="w-full rounded-full border border-gray-300 bg-white pl-11 pr-4 py-2.5 text-sm font-medium text-slate-700 placeholder-slate-400 transition-all focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20 focus:outline-none" />
                    </div>
                </div>

                <!-- Right: Action Button -->
                <div class="w-full md:w-1/4 flex justify-end">
                    <Link :href="route('admin.berita.create')"
                        class="group inline-flex items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-bold text-white shadow-lg transition-all hover:-translate-y-1 active:scale-95"
                        style="background-color: #84994F; box-shadow: 0 4px 6px -1px rgba(132, 153, 79, 0.3);">
                        <PlusIcon class="h-4 w-4" />
                        <span>Tambah Konten</span>
                    </Link>
                </div>
            </div>

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
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 px-3 py-1.5 text-xs font-bold text-white shadow-md ring-2 ring-indigo-200 transition-all group-hover:scale-105 group-hover:shadow-lg">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white opacity-70"></span>
                                    #{{ String(item.id).padStart(3, '0') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold shadow-lg ring-2 transition-all group-hover:scale-110 group-hover:shadow-xl"
                                        :class="[getAvatarColor(item.type).bg, getAvatarColor(item.type).text, getAvatarColor(item.type).ring]">
                                        <NewspaperIcon v-if="item.type === 'news'" class="h-4 w-4" />
                                        <CalendarIcon v-else-if="item.type === 'activity'" class="h-4 w-4" />
                                        <PhotoIcon v-else class="h-4 w-4" />
                                        <div class="absolute inset-0 rounded-full bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 line-clamp-1" :title="item.judul">{{ item.judul }}</p>
                                        <p class="text-xs text-slate-500 line-clamp-1 mt-1">{{ truncate(item.konten, 60) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold border shadow-sm transition-all hover:scale-105"
                                    :class="[getTypeColor(item.type).bg, getTypeColor(item.type).text, getTypeColor(item.type).border]">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="getTypeColor(item.type).dot"></span>
                                    {{ formatType(item.type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600 font-medium">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="route('admin.berita.edit', item.id)"
                                        class="group/btn inline-flex items-center justify-center gap-1.5 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-amber-200 transition-all hover:from-amber-600 hover:to-amber-700 hover:-translate-y-0.5 hover:shadow-xl active:scale-95"
                                        title="Edit Konten">
                                        <PencilSquareIcon class="h-3.5 w-3.5" />
                                        <span>Edit</span>
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
