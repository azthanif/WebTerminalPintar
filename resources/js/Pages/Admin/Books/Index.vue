<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    MagnifyingGlassIcon, 
    PlusIcon, 
    BookOpenIcon, 
    BookmarkSquareIcon, 
    ArchiveBoxArrowDownIcon,
    WrenchScrewdriverIcon,
    PencilSquareIcon,
    TrashIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    DocumentTextIcon,
    BuildingLibraryIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    books: {
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
    statuses: {
        type: Object,
        required: true,
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

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

const paginationMeta = computed(() => resolvePaginationMeta(props.books))
const currentPage = computed(() => paginationMeta.value.current_page)
const lastPage = computed(() => paginationMeta.value.last_page)
const paginationInfo = computed(() => {
    const meta = paginationMeta.value
    if (!meta.total) {
        return 'Tidak ada data buku'
    }
    return `Menampilkan ${meta.from}-${meta.to} dari ${meta.total} buku`
})

const loadBooks = (page = 1) => {
    router.get(
        route('admin.books.index'),
        {
            page,
            search: search.value,
            status: status.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    )
}

const changeStatus = (value) => {
    status.value = value
    loadBooks(1)
}

let searchDebounceId
watch(search, () => {
    clearTimeout(searchDebounceId)
    searchDebounceId = setTimeout(() => loadBooks(1), 400)
})

const pages = computed(() => {
    if (!lastPage.value) return []
    const windowSize = Math.min(5, lastPage.value)
    const numbers = []
    let start = Math.max(1, currentPage.value - Math.floor(windowSize / 2))
    let end = Math.min(lastPage.value, start + windowSize - 1)

    if (end - start + 1 < windowSize) {
        start = Math.max(1, end - windowSize + 1)
    }

    for (let page = start; page <= end; page++) {
        numbers.push(page)
    }

    return numbers
})

const statusColor = (value) => {
    if (value === 'available') return 'bg-emerald-50 text-emerald-600 border-emerald-100'
    if (value === 'borrowed') return 'bg-amber-50 text-amber-600 border-amber-100'
    if (value === 'maintenance') return 'bg-sky-50 text-sky-600 border-sky-100'
    return 'bg-rose-50 text-rose-600 border-rose-100'
}

const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value || 0)

const borrowerLabel = (loan) => {
    if (!loan) {
        return 'Tidak ada peminjam'
    }

    const name = loan.borrower_display_name || loan.borrower?.name || 'Tidak diketahui'
    const total = loan.borrowed_books_total || 1
    return `${name} - (${total} buku)`
}

const borrowerEmail = (loan) => loan?.borrower_display_email || loan?.borrower?.email || ''

const showDeleteModal = ref(false)
const bookPendingDelete = ref(null)

const openDeleteModal = (book) => {
    bookPendingDelete.value = book
    showDeleteModal.value = true
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    bookPendingDelete.value = null
}

const confirmDelete = () => {
    if (!bookPendingDelete.value) return

    router.delete(route('admin.books.destroy', bookPendingDelete.value.id), {
        preserveScroll: true,
        onFinish: () => closeDeleteModal(),
    })
}
</script>

<template>
    <div class="space-y-8">
        <Head title="Manajemen Buku" />

        <!-- Premium Header Section -->
        <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative">
             <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-600 mb-2 border border-sky-100">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
                    </span>
                    <span>Katalog Perpustakaan</span>
                </div>
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                    Koleksi <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-indigo-600">Buku</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium text-lg">Kelola inventaris buku dan pantau sirkulasi peminjaman.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
                 <Link :href="route('admin.perpustakaan.sirkulasi')"
                    class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-white border border-slate-200 px-6 py-3 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300 hover:scale-105 active:scale-95">
                    <ArchiveBoxArrowDownIcon class="h-5 w-5 text-slate-500 group-hover:text-slate-700" />
                    <span>Sirkulasi</span>
                </Link>
                <Link :href="route('admin.books.create')"
                    class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--color-primary-light)] transition-all hover:bg-[var(--color-primary-hover)] hover:scale-105 active:scale-95">
                    <div class="rounded-lg bg-white/20 p-1">
                        <PlusIcon class="h-5 w-5 text-white" />
                    </div>
                    <span>Tambah Buku</span>
                </Link>
            </div>
        </section>

        <!-- Stats Grid (Modern Cards) -->
        <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-emerald-200">
                <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 blur-xl group-hover:bg-emerald-100 transition-colors"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Koleksi</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.total }}</p>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700">
                        <BookOpenIcon class="h-3 w-3" />
                         <span>Semua Kategori</span>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-sky-200">
                <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-sky-50 opacity-50 blur-xl group-hover:bg-sky-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Tersedia</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.available }}</p>
                      <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-2 py-1 text-[10px] font-bold text-sky-700">
                        <CheckCircleIcon class="h-3 w-3" />
                        <span>Siap Dipinjam</span>
                    </div>
                </div>
            </div>

             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-amber-200">
                 <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-amber-50 opacity-50 blur-xl group-hover:bg-amber-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Dipinjam</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.borrowed }}</p>
                     <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700">
                        <BookmarkSquareIcon class="h-3 w-3" />
                        <span>Sedang Keluar</span>
                    </div>
                </div>
            </div>

             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-rose-200">
                  <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-rose-50 opacity-50 blur-xl group-hover:bg-rose-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Maintenance</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.maintenance }}</p>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-700">
                        <WrenchScrewdriverIcon class="h-3 w-3" />
                        <span>Perlu Perbaikan</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Card -->
        <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
             <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
                <div>
                     <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <BuildingLibraryIcon class="h-6 w-6 text-slate-400" />
                        Daftar Buku
                     </h2>
                    <p class="text-sm text-slate-500 font-medium mt-1">Cari judul, kode, atau filter berdasarkan status.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                     <select v-model="status" @change="changeStatus($event.target.value)"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 focus:border-[var(--color-primary)] focus:bg-white focus:outline-none transition-colors cursor-pointer hover:bg-slate-100">
                        <option value="">Semua Status</option>
                        <option v-for="(label, value) in statuses" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <div class="relative w-full md:w-72">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <MagnifyingGlassIcon class="h-5 w-5" />
                        </div>
                        <input v-model="search" type="text" placeholder="Cari judul atau kode..."
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 py-3 text-sm font-medium text-slate-700 placeholder-slate-400 transition-all focus:border-[var(--color-primary)] focus:bg-white focus:ring-[var(--color-primary)]" />
                    </div>
                </div>
            </header>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                     <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Buku</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Kategori</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Peminjam</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Stok</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Sirkulasi</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="book in books.data" :key="book.id" class="group hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-bold text-slate-800 line-clamp-1" :title="book.title">{{ book.title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 border border-slate-200">{{ book.code }}</span>
                                        <span class="text-xs text-slate-500 truncate max-w-[150px]" :title="book.author">{{ book.author || 'Tanpa Penulis' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                    {{ book.category || 'Umum' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold border shadow-sm"
                                    :class="statusColor(book.status)">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ statuses[book.status] || 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="book.active_loans && book.active_loans.length" class="space-y-2">
                                     <div v-for="loan in book.active_loans" :key="loan.id" 
                                          class="flex items-center gap-2 rounded-xl bg-orange-50 border border-orange-100 p-2">
                                        <div class="h-6 w-6 rounded-full bg-orange-100 flex items-center justify-center text-xs font-bold text-orange-600 shrink-0">
                                            {{ (loan.borrower_display_name || loan.borrower?.name || '?').charAt(0) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-xs font-bold text-slate-700 truncate w-32" :title="borrowerLabel(loan)">{{ borrowerLabel(loan) }}</p>
                                        </div>
                                     </div>
                                </div>
                                <span v-else class="text-xs font-medium text-slate-400 italic">Tidak ada</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1">
                                    <span class="font-bold text-slate-700">{{ book.available_stock }}</span>
                                    <span class="text-slate-400">/</span>
                                    <span class="text-xs font-bold text-slate-500">{{ book.total_stock }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-50 text-slate-600 border border-slate-200">
                                    {{ formatNumber(book.loans_count) }}x
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="route('admin.books.edit', book.id)"
                                        class="group/btn inline-flex items-center justify-center h-8 w-8 rounded-full bg-amber-50 text-amber-600 transition-all hover:bg-amber-100 hover:scale-110 active:scale-95 border border-amber-100"
                                        title="Edit Buku">
                                        <PencilSquareIcon class="h-4 w-4" />
                                    </Link>
                                    <button @click="openDeleteModal(book)"
                                        class="group/btn inline-flex items-center justify-center h-8 w-8 rounded-full bg-rose-50 text-rose-600 transition-all hover:bg-rose-100 hover:text-rose-700 hover:scale-110 active:scale-95 border border-rose-100"
                                        title="Hapus Buku">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="books.data.length === 0">
                            <td colspan="7" class="px-6 py-16 text-center">
                                 <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 border border-slate-100">
                                        <BookOpenIcon class="h-8 w-8 text-slate-300" />
                                    </div>
                                    <p class="text-lg font-bold text-slate-700">Tidak ada buku ditemukan</p>
                                    <p class="text-sm text-slate-500 mt-1">Coba filter status atau gunakan kata kunci lain.</p>
                                    <button @click="search = ''; status = ''" class="mt-4 text-sm font-bold text-[var(--color-primary)] hover:underline">
                                        Reset Filter
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

             <!-- Footer / Pagination -->
            <div class="mt-8 flex flex-col gap-4 border-t border-slate-100 pt-6 text-sm lg:flex-row lg:items-center lg:justify-between">
                <p class="font-medium text-slate-500">{{ paginationInfo }}</p>
                <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-full border border-slate-200 w-fit">
                    <button @click="loadBooks(currentPage - 1)" :disabled="currentPage === 1"
                        class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="currentPage === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm'">
                        &larr; Prev
                    </button>
                    <div class="h-4 w-px bg-slate-200"></div>
                     <button v-for="page in pages" :key="page" @click="loadBooks(page)"
                        class="h-8 w-8 rounded-full text-xs font-bold flex items-center justify-center transition-all"
                        :class="page === currentPage
                            ? 'bg-[var(--color-primary)] text-white shadow-md'
                            : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)]'">
                        {{ page }}
                    </button>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <button @click="loadBooks(currentPage + 1)" :disabled="currentPage === lastPage"
                         class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="currentPage === lastPage ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm'">
                        Next &rarr;
                    </button>
                </div>
            </div>
        </section>

         <!-- Delete Modal (Premium) -->
        <transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="w-full max-w-md rounded-[2rem] bg-white shadow-2xl scale-100 transition-all border border-slate-200">
                    <div class="p-8 text-center">
                         <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-rose-600 mb-6 ring-8 ring-rose-50">
                            <ExclamationTriangleIcon class="h-8 w-8" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Hapus Buku?</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Apakah Anda yakin ingin menghapus buku <span class="font-bold text-slate-800">"{{ bookPendingDelete?.title }}"</span>? Tindakan ini tidak dapat dibatalkan.
                        </p>
                        
                        <div class="mt-6 space-y-3 rounded-2xl bg-slate-50 p-4 border border-slate-100 text-left">
                           <div class="flex items-center justify-between text-xs">
                               <span class="font-semibold text-slate-500 uppercase tracking-wider">Kode</span>
                               <span class="font-bold font-mono text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200">{{ bookPendingDelete?.code || '—' }}</span>
                           </div>
                           <div class="flex items-center justify-between text-xs">
                               <span class="font-semibold text-slate-500 uppercase tracking-wider">Status</span>
                               <span class="font-bold text-emerald-600" :class="{'text-amber-600': bookPendingDelete?.status === 'borrowed'}">{{ statuses[bookPendingDelete?.status] || '—' }}</span>
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
