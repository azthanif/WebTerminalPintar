<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

const currentPage = computed(() => props.books.current_page || 1)
const lastPage = computed(() => props.books.last_page || 1)

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

const searchBooks = () => loadBooks(1)

const changeStatus = (value) => {
    status.value = value
    loadBooks(1)
}

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
    if (value === 'available') return 'bg-emerald-50 text-emerald-600'
    if (value === 'borrowed') return 'bg-amber-50 text-amber-600'
    if (value === 'maintenance') return 'bg-sky-50 text-sky-600'
    return 'bg-rose-50 text-rose-600'
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
    <div class="space-y-10">
        <Head title="Manajemen Buku" />

        <section class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-slate-400">Perpustakaan</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Koleksi Buku</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Pantau persediaan buku dan riwayat peminjaman di Terminal Pintar.
                </p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <Link :href="route('admin.perpustakaan.sirkulasi')"
                    class="inline-flex items-center justify-center rounded-2xl bg-sky-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-200 transition hover:bg-sky-600">
                    Sirkulasi Peminjaman dan Pengembalian Buku
                </Link>
                <Link :href="route('admin.books.create')"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-600">
                + Tambah Buku
                </Link>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Koleksi</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.total }}</p>
                <p class="text-xs text-emerald-500">Semua kategori buku</p>
            </div>
            <div class="rounded-3xl border border-sky-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Tersedia</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.available }}</p>
                <p class="text-xs text-sky-500">Siap dipinjam</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Sedang Dipinjam</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.borrowed }}</p>
                <p class="text-xs text-amber-500">Siswa / Guru</p>
            </div>
            <div class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Perawatan</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.maintenance }}</p>
                <p class="text-xs text-rose-500">Perlu perhatian</p>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Daftar Buku</h2>
                    <p class="text-sm text-slate-500">Gunakan pencarian untuk menemukan buku tertentu</p>
                </div>
                <div class="flex w-full flex-col gap-3 sm:flex-row md:w-auto">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute left-3 top-0 flex h-full items-center text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M9 3.5a5.5 5.5 0 013.962 9.337l3.85 3.85a.75.75 0 11-1.06 1.06l-3.85-3.85A5.5 5.5 0 119 3.5zm0 1.5a4 4 0 100 8 4 4 0 000-8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                        <input v-model="search" type="text" @keyup.enter="searchBooks" placeholder="Cari judul / kode"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-10 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                    </div>
                    <select v-model="status" @change="changeStatus($event.target.value)"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:bg-white focus:outline-none">
                        <option value="">Semua status</option>
                        <option v-for="(label, value) in statuses" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Buku</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Peminjam</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3">Dipinjam</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        <tr v-for="book in books.data" :key="book.id" class="hover:bg-slate-50/50">
                            <td class="px-4 py-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ book.title }}</p>
                                    <p class="text-xs text-slate-500">Kode: {{ book.code }}</p>
                                    <p class="text-xs text-slate-500">Penulis: {{ book.author || '-' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                {{ book.category || 'Umum' }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="statusColor(book.status)">
                                    {{ statuses[book.status] || 'Tidak diketahui' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">
                                <div v-if="book.active_loans && book.active_loans.length">
                                    <div
                                        v-for="loan in book.active_loans"
                                        :key="loan.id"
                                        class="mb-3 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2 last:mb-0"
                                    >
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ borrowerLabel(loan) }}
                                        </p>
                                        <p v-if="borrowerEmail(loan)" class="text-xs text-slate-500">
                                            {{ borrowerEmail(loan) }}
                                        </p>
                                        <p v-else class="text-xs italic text-slate-400">Email belum tersedia</p>
                                    </div>
                                </div>
                                <p v-else class="text-xs italic text-slate-400">Tidak ada peminjaman aktif</p>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ book.available_stock }}/{{ book.total_stock }}
                                </div>
                                <p class="text-xs text-slate-500">tersedia / total</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ formatNumber(book.loans_count) }} kali
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="route('admin.books.edit', book.id)"
                                        class="inline-flex items-center rounded-full border border-amber-100 px-3 py-1 text-xs font-semibold text-amber-600 transition hover:bg-amber-50">
                                    Edit
                                    </Link>
                                    <button @click="openDeleteModal(book)"
                                        class="inline-flex items-center rounded-full border border-rose-100 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="books.data.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">
                                Belum ada data buku.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col gap-4 border-t border-slate-100 pt-6 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
                <p>
                    Menampilkan {{ books.from || 0 }}-{{ books.to || 0 }} dari {{ books.total || 0 }} buku
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="loadBooks(currentPage - 1)" :disabled="!books.prev_page_url"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition"
                        :class="books.prev_page_url ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'">
                        Sebelumnya
                    </button>
                    <button v-for="page in pages" :key="page" @click="loadBooks(page)"
                        class="rounded-full px-3 py-1 text-xs font-semibold"
                        :class="page === currentPage
                            ? 'bg-emerald-500 text-white'
                            : 'text-slate-600 hover:bg-slate-50'">
                        {{ page }}
                    </button>
                    <button @click="loadBooks(currentPage + 1)" :disabled="!books.next_page_url"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition"
                        :class="books.next_page_url ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </section>

        <transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4">
                <div class="w-full max-w-lg rounded-[36px] bg-[#f8f8fb] p-6 text-slate-700 shadow-2xl">
                    <div class="flex items-center gap-3 rounded-3xl bg-white px-5 py-4 shadow-sm">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-6 w-6 text-emerald-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7V5a2 2 0 012-2h8a2 2 0 012 2v2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-widest text-emerald-400">Perpustakaan</p>
                            <p class="text-lg font-semibold text-emerald-600">Perpustakaan Terminal Pintar</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-3xl bg-white p-6 shadow-inner">
                        <h3 class="text-center text-xl font-semibold text-rose-600">Hapus Buku</h3>
                        <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-center text-rose-600">
                            <p class="text-sm font-semibold">⚠️ Peringatan</p>
                            <p class="mt-2 text-sm">Apakah Anda yakin ingin menghapus buku ini? Tindakan ini tidak dapat dibatalkan.</p>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm">
                            <p class="text-base font-semibold text-slate-800">Informasi Buku</p>
                            <div class="mt-4 grid grid-cols-2 gap-y-2 text-slate-700">
                                <span class="font-medium">Kode</span>
                                <span>: {{ bookPendingDelete?.code || '-' }}</span>
                                <span class="font-medium">Judul</span>
                                <span>: {{ bookPendingDelete?.title }}</span>
                                <span class="font-medium">Kategori</span>
                                <span>: {{ bookPendingDelete?.category || 'Umum' }}</span>
                                <span class="font-medium">Status</span>
                                <span>: {{ statuses[bookPendingDelete?.status] || 'Tidak diketahui' }}</span>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <button type="button" @click="closeDeleteModal"
                                class="rounded-2xl border border-slate-200 px-6 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                                Batal
                            </button>
                            <button type="button" @click="confirmDelete"
                                class="rounded-2xl bg-rose-600 px-6 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-700">
                                Hapus Buku
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
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
