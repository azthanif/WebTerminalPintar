<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    loans: {
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
    books: {
        type: Array,
        default: () => [],
    },
    borrowers: {
        type: Array,
        default: () => [],
    },
    statuses: {
        type: Object,
        required: true,
    },
})

defineOptions({
    layout: AdminLayout,
})

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

const loanForm = useForm({
    book_id: '',
    user_id: '',
    borrowed_at: '',
    due_at: '',
    notes: '',
})

onMounted(() => {
    loanForm.borrowed_at = new Date().toISOString().slice(0, 10)
})

const currentPage = computed(() => props.loans.current_page || 1)
const lastPage = computed(() => props.loans.last_page || 1)

const loadLoans = (page = 1) => {
    router.get(
        route('admin.loans.index'),
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

const submitLoan = () => {
    loanForm.post(route('admin.loans.store'), {
        onSuccess: () => {
            loanForm.reset('book_id', 'user_id', 'notes')
            loanForm.borrowed_at = new Date().toISOString().slice(0, 10)
        },
    })
}

const markReturned = (loanId) => {
    router.put(route('admin.loans.update', loanId), {
        status: 'returned',
        returned_at: new Date().toISOString().slice(0, 10),
    }, {
        preserveScroll: true,
    })
}

const markOverdue = (loanId) => {
    router.put(route('admin.loans.update', loanId), {
        status: 'overdue',
    }, {
        preserveScroll: true,
    })
}

const formatDate = (value) => {
    if (!value) return '—'
    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    })
}

const statusClass = (value) => {
    if (value === 'returned') return 'bg-emerald-50 text-emerald-600'
    if (value === 'overdue') return 'bg-rose-50 text-rose-600'
    return 'bg-amber-50 text-amber-600'
}
</script>

<template>
    <div class="space-y-10">
        <Head title="Sirkulasi Buku" />

        <section class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-slate-400">Perpustakaan</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Sirkulasi Peminjaman</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Catat peminjaman baru dan pantau status pengembalian.
                </p>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Sedang Dipinjam</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.active }}</p>
                <p class="text-xs text-emerald-500">Belum dikembalikan</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Terlambat</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.overdue }}</p>
                <p class="text-xs text-amber-500">Segera tindak lanjuti</p>
            </div>
            <div class="rounded-3xl border border-sky-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Selesai</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.returned }}</p>
                <p class="text-xs text-sky-500">Sudah dikembalikan</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Riwayat Peminjaman</h2>
                        <p class="text-sm text-slate-500">Filter untuk melihat status tertentu</p>
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
                            <input v-model="search" type="text" @keyup.enter="loadLoans(1)" placeholder="Cari buku / peminjam"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-10 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                        </div>
                        <select v-model="status" @change="loadLoans(1)"
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
                                <th class="px-4 py-3">Peminjam</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 bg-white">
                            <tr v-for="loan in loans.data" :key="loan.id" class="hover:bg-slate-50/50">
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900">{{ loan.book?.title }}</p>
                                    <p class="text-xs text-slate-500">Kode: {{ loan.book?.code }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900">{{ loan.borrower?.name }}</p>
                                    <p class="text-xs text-slate-500">{{ loan.borrower?.email }}</p>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    <p>Pinjam: {{ formatDate(loan.borrowed_at) }}</p>
                                    <p>Jatuh tempo: {{ formatDate(loan.due_at) }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="statusClass(loan.status)">
                                        {{ statuses[loan.status] || 'Dipinjam' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button v-if="loan.status === 'borrowed'" @click="markReturned(loan.id)"
                                            class="rounded-full border border-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-600 hover:bg-emerald-50">
                                            Selesai
                                        </button>
                                        <button v-if="loan.status === 'borrowed'" @click="markOverdue(loan.id)"
                                            class="rounded-full border border-amber-100 px-3 py-1 text-xs font-semibold text-amber-600 hover:bg-amber-50">
                                            Tandai Telat
                                        </button>
                                        <span v-else class="text-xs text-slate-400">—</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="loans.data.length === 0">
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">
                                    Belum ada data peminjaman.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex flex-col gap-4 border-t border-slate-100 pt-6 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
                    <p>
                        Menampilkan {{ loans.from || 0 }}-{{ loans.to || 0 }} dari {{ loans.total || 0 }} peminjaman
                    </p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button @click="loadLoans(currentPage - 1)" :disabled="!loans.prev_page_url"
                            class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition"
                            :class="loans.prev_page_url ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'">
                            Sebelumnya
                        </button>
                        <button v-for="page in pages" :key="page" @click="loadLoans(page)"
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="page === currentPage
                                ? 'bg-emerald-500 text-white'
                                : 'text-slate-600 hover:bg-slate-50'">
                            {{ page }}
                        </button>
                        <button @click="loadLoans(currentPage + 1)" :disabled="!loans.next_page_url"
                            class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition"
                            :class="loans.next_page_url ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'">
                            Selanjutnya
                        </button>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-slate-900">Peminjaman Baru</h2>
                <p class="text-sm text-slate-500">Isi formulir untuk mencatat peminjaman buku.</p>

                <form @submit.prevent="submitLoan" class="mt-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Buku</label>
                        <select v-model="loanForm.book_id"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none">
                            <option value="">Pilih buku tersedia</option>
                            <option v-for="book in books" :key="book.id" :value="book.id">
                                {{ book.title }} ({{ book.code }})
                            </option>
                        </select>
                        <p v-if="loanForm.errors.book_id" class="mt-1 text-xs text-rose-500">{{ loanForm.errors.book_id }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Peminjam</label>
                        <select v-model="loanForm.user_id"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none">
                            <option value="">Pilih pengguna</option>
                            <option v-for="borrower in borrowers" :key="borrower.id" :value="borrower.id">
                                {{ borrower.name }} - {{ borrower.email }}
                            </option>
                        </select>
                        <p v-if="loanForm.errors.user_id" class="mt-1 text-xs text-rose-500">{{ loanForm.errors.user_id }}</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Pinjam</label>
                            <input v-model="loanForm.borrowed_at" type="date"
                                class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                            <p v-if="loanForm.errors.borrowed_at" class="mt-1 text-xs text-rose-500">{{ loanForm.errors.borrowed_at }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Jatuh Tempo</label>
                            <input v-model="loanForm.due_at" type="date"
                                class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                            <p v-if="loanForm.errors.due_at" class="mt-1 text-xs text-rose-500">{{ loanForm.errors.due_at }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan</label>
                        <textarea v-model="loanForm.notes" rows="4"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none"></textarea>
                        <p v-if="loanForm.errors.notes" class="mt-1 text-xs text-rose-500">{{ loanForm.errors.notes }}</p>
                    </div>

                    <button type="submit"
                        class="w-full rounded-2xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-600 disabled:opacity-60"
                        :disabled="loanForm.processing">
                        Simpan Peminjaman
                    </button>
                </form>
            </div>
        </section>
    </div>
</template>
