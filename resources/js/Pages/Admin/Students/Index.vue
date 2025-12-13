<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    students: { type: Object, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
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

const paginationMeta = computed(() => resolvePaginationMeta(props.students))

const paginationInfo = computed(() => {
    const meta = paginationMeta.value
    if (!meta.total) {
        return 'Tidak ada data siswa'
    }
    return `Menampilkan ${meta.from} - ${meta.to} dari ${meta.total} siswa`
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

const loadStudents = (page = 1) => {
    router.get(
        route('admin.students.index'),
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
    searchDebounceId = setTimeout(() => loadStudents(1), 400)
})

defineOptions({
    layout: AdminLayout,
})
</script>

<template>
    <div class="space-y-10">

        <Head title="Manajemen Siswa" />

        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Siswa</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900">Manajemen Siswa</h1>
                    <p class="text-sm text-slate-500">Pantau perkembangan siswa Terminal Pintar.</p>
                </div>
                <Link :href="route('admin.students.create')"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-600">
                + Tambah Siswa
                </Link>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total Siswa</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.total }}</p>
                <p class="text-xs text-emerald-500">Termasuk seluruh angkatan</p>
            </div>
            <div class="rounded-3xl border border-sky-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Siswa Aktif</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.aktif }}</p>
                <p class="text-xs text-sky-500">Sedang mengikuti program</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Siswa Nonaktif</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.nonaktif }}</p>
                <p class="text-xs text-amber-500">Lulus / cuti</p>
            </div>
            <div class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Pendidikan Terbanyak</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900">{{ stats.most_education }}</p>
                <p class="text-xs text-rose-500">Dominasi jenjang terakhir</p>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Daftar Siswa</h2>
                    <p class="text-sm text-slate-500">Filter berdasarkan ID, nama, atau pendidikan.</p>
                </div>

                <div class="relative w-full md:w-80">
                    <span class="pointer-events-none absolute left-3 top-0 flex h-full items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 3.5a5.5 5.5 0 013.962 9.337l3.85 3.85a.75.75 0 11-1.06 1.06l-3.85-3.85A5.5 5.5 0 119 3.5zm0 1.5a4 4 0 100 8 4 4 0 000-8z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input v-model="searchQuery" type="text"
                        placeholder="Cari ID, nama, pendidikan"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-10 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-4 py-3">ID Siswa</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Pendidikan</th>
                            <th class="px-4 py-3">Orang Tua</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        <tr v-for="student in students.data" :key="student.id" class="hover:bg-slate-50/70">
                            <td class="px-4 py-4">
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">
                                    {{ student.student_id }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-900">{{ student.name }}</p>
                                <p class="text-xs text-slate-500">{{ student.school_name || 'Sekolah belum diisi' }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ student.education_level }}
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ student.parent_name || '—' }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="student.status === 'Aktif'
                                        ? 'bg-emerald-50 text-emerald-600'
                                        : 'bg-slate-100 text-slate-500'">
                                    {{ student.status }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <Link :href="route('admin.students.edit', student.id)"
                                    class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                Kelola
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="students.data.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col gap-4 border-t border-slate-100 pt-6 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
                <span>{{ paginationInfo }}</span>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="loadStudents(currentPage - 1)" :disabled="currentPage === 1"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition"
                        :class="currentPage === 1 ? 'cursor-not-allowed text-slate-300' : 'text-slate-700 hover:bg-slate-50'">
                        Sebelumnya
                    </button>
                    <button v-for="pageNumber in pages" :key="`students-page-${pageNumber}`" @click="loadStudents(pageNumber)"
                        class="rounded-full px-3 py-1 text-xs font-semibold"
                        :class="pageNumber === currentPage ? 'bg-emerald-500 text-white' : 'text-slate-600 hover:bg-slate-50'">
                        {{ pageNumber }}
                    </button>
                    <button @click="loadStudents(currentPage + 1)" :disabled="currentPage === lastPage"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition"
                        :class="currentPage === lastPage ? 'cursor-not-allowed text-slate-300' : 'text-slate-700 hover:bg-slate-50'">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </section>
    </div>

    <Notivue v-slot="notification">
        <Notification :item="notification" />
    </Notivue>
</template>
