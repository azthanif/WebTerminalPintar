<script setup>
import { Head, router, useForm, Link, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { PlusIcon, MagnifyingGlassIcon, CheckCircleIcon, BookOpenIcon } from '@heroicons/vue/24/outline'

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

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)

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
    if (value === 'returned') return 'bg-gray-100 text-gray-600 border border-gray-200'
    if (value === 'overdue') return 'bg-red-100 text-red-700 border border-red-200 animate-pulse'
    return 'bg-blue-100 text-blue-700 border border-blue-200'
}

// Get education level from borrower for avatar color
const getEducationLevel = (borrower) => {
    return borrower?.student?.education_level || borrower?.education_level || 'Umum'
}

// Avatar gradient colors based on education level (same as Students page)
const getAvatarColor = (educationLevel) => {
    if (!educationLevel) return { bg: 'bg-gradient-to-br from-slate-400 to-slate-500', text: 'text-white', ring: 'ring-slate-300' }
    
    const level = educationLevel.toLowerCase()
    
    if (level.includes('sd') || level.includes('mi')) {
        return { bg: 'bg-gradient-to-br from-blue-400 to-blue-600', text: 'text-white', ring: 'ring-blue-300' }
    } else if (level.includes('smp') || level.includes('mts')) {
        return { bg: 'bg-gradient-to-br from-purple-400 to-purple-600', text: 'text-white', ring: 'ring-purple-300' }
    } else if (level.includes('sma') || level.includes('smk') || level.includes('ma')) {
        return { bg: 'bg-gradient-to-br from-green-400 to-green-600', text: 'text-white', ring: 'ring-green-300' }
    } else if (level.includes('kuliah') || level.includes('universitas') || level.includes('perguruan')) {
        return { bg: 'bg-gradient-to-br from-amber-400 to-amber-600', text: 'text-white', ring: 'ring-amber-300' }
    } else {
        return { bg: 'bg-gradient-to-br from-indigo-400 to-indigo-600', text: 'text-white', ring: 'ring-indigo-300' }
    }
}
</script>

<template>
    <div class="space-y-8">
        <Head title="Sirkulasi Peminjaman" />

        <!-- Page Header -->
        <PageHeader badgeLabel="Circulation Desk" badgeColor="cyan">
            <template #title>
                <p class="text-2xl font-bold text-slate-700 mt-0.5" style="font-family: 'Poppins', sans-serif;">
                    Selamat datang kembali, <span class="text-[var(--color-primary)]">{{ user?.name }}</span>!
                </p>
            </template>
        </PageHeader>

        <!-- Stats Cards -->
        <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
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
            <div class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm lg:col-span-2">
                <!-- Table Toolbar (Standardized) -->
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                    <!-- Left: Title -->
                    <div class="w-full md:w-1/4">
                        <h2 class="text-xl font-bold text-slate-800">Riwayat Peminjaman</h2>
                    </div>

                    <!-- Center: Search & Filter -->
                    <div class="w-full md:w-1/2 flex justify-center gap-3">
                        <select v-model="status" @change="loadLoans(1)"
                            class="rounded-full border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20 focus:outline-none transition-all cursor-pointer hover:bg-slate-50">
                            <option value="">Semua status</option>
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                        <div class="relative flex-1 max-w-md">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <MagnifyingGlassIcon class="h-5 w-5" />
                            </div>
                            <input v-model="search" type="text" @keyup.enter="loadLoans(1)"
                                placeholder="Cari buku / peminjam"
                                class="w-full rounded-full border border-gray-300 bg-white pl-11 pr-4 py-2.5 text-sm font-medium text-slate-700 placeholder-slate-400 transition-all focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20 focus:outline-none" />
                        </div>
                    </div>

                    <!-- Right: Action Button -->
                    <div class="w-full md:w-1/4 flex justify-end">
                        <button type="button" onclick="document.getElementById('loan-form').scrollIntoView({behavior: 'smooth'})"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-green-600 text-white px-6 py-2.5 text-sm font-bold shadow-lg transition-all hover:bg-green-700 hover:-translate-y-1 active:scale-95">
                            <PlusIcon class="h-4 w-4" />
                            <span>Catat Peminjaman</span>
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Peminjam</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Buku</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-400">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="loan in loans.data" :key="loan.id" class="group hover:bg-cyan-50/30 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-cyan-500 to-blue-500 px-3 py-1.5 text-xs font-bold text-white shadow-md ring-2 ring-cyan-200 transition-all group-hover:scale-105 group-hover:shadow-lg">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white opacity-70"></span>
                                        #{{ String(loan.id).padStart(3, '0') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold shadow-lg ring-2 transition-all group-hover:scale-110 group-hover:shadow-xl"
                                            :class="[getAvatarColor(getEducationLevel(loan.borrower)).bg, getAvatarColor(getEducationLevel(loan.borrower)).text, getAvatarColor(getEducationLevel(loan.borrower)).ring]">
                                            {{ (loan.borrower?.name || '?').charAt(0).toUpperCase() }}
                                            <div class="absolute inset-0 rounded-full bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ loan.borrower?.name }}</p>
                                            <p class="text-xs text-slate-500">{{ loan.borrower?.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-md ring-2 ring-amber-300">
                                            <BookOpenIcon class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 line-clamp-1" :title="loan.book?.title">{{ loan.book?.title }}</p>
                                            <p class="text-xs text-slate-500">{{ loan.book?.code }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    <p>Pinjam: {{ formatDate(loan.borrowed_at) }}</p>
                                    <p :class="loan.status === 'overdue' ? 'text-red-600 font-bold' : ''">Kembali: {{ formatDate(loan.due_at) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold shadow-sm"
                                        :class="statusClass(loan.status)">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        {{ statuses[loan.status] || 'Dipinjam' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button v-if="loan.status === 'borrowed'" @click="markReturned(loan.id)"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-green-50 text-green-600 transition-all hover:bg-green-100 hover:scale-110 active:scale-95 border border-green-100"
                                            title="Kembalikan">
                                            <CheckCircleIcon class="h-4 w-4" />
                                        </button>
                                        <span v-else class="text-xs text-slate-400">—</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="loans.data.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 border border-slate-100">
                                            <BookOpenIcon class="h-8 w-8 text-slate-300" />
                                        </div>
                                        <p class="text-lg font-bold text-slate-700">Belum ada data peminjaman</p>
                                        <p class="text-sm text-slate-500 mt-1">Catat peminjaman baru untuk memulai.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex flex-col gap-4 border-t border-slate-100 pt-6 text-sm lg:flex-row lg:items-center lg:justify-between">
                    <p class="font-medium text-slate-500">
                        Menampilkan {{ loans.from || 0 }}-{{ loans.to || 0 }} dari {{ loans.total || 0 }} peminjaman
                    </p>
                    <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-full border border-slate-200 w-fit">
                        <button @click="loadLoans(currentPage - 1)" :disabled="!loans.prev_page_url"
                            class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                            :class="loans.prev_page_url ? 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm' : 'text-slate-300 cursor-not-allowed'">
                            ← Prev
                        </button>
                        <div class="h-4 w-px bg-slate-200"></div>
                        <button v-for="page in pages" :key="page" @click="loadLoans(page)"
                            class="h-8 w-8 rounded-full text-xs font-bold flex items-center justify-center transition-all"
                            :class="page === currentPage ? 'bg-[var(--color-primary)] text-white shadow-md' : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)]'">
                            {{ page }}
                        </button>
                        <div class="h-4 w-px bg-slate-200"></div>
                        <button @click="loadLoans(currentPage + 1)" :disabled="!loans.next_page_url"
                            class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                            :class="loans.next_page_url ? 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm' : 'text-slate-300 cursor-not-allowed'">
                            Next →
                        </button>
                    </div>
                </div>
            </div>

            <div id="loan-form" class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
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
