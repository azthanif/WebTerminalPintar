<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    MagnifyingGlassIcon, 
    PlusIcon, 
    AcademicCapIcon, 
    UserIcon, 
    PencilSquareIcon, 
    TrashIcon, 
    CheckCircleIcon, 
    XCircleIcon,
    UsersIcon,
    TrophyIcon,
    UserMinusIcon
} from '@heroicons/vue/24/outline'

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
    <div class="space-y-8">

        <Head title="Manajemen Siswa" />

        <!-- Premium Header Section -->
        <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative">
             <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600 mb-2 border border-emerald-100">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Monitoring Siswa</span>
                </div>
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                    Data <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-[var(--color-primary)]">Siswa</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium text-lg">Kelola data siswa, status akademik, dan relasi orang tua.</p>
            </div>
            
             <Link :href="route('admin.students.create')"
                class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-[var(--color-primary)] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--color-primary-light)] transition-all hover:bg-[var(--color-primary-hover)] hover:scale-105 active:scale-95">
                <div class="rounded-lg bg-white/20 p-1">
                    <PlusIcon class="h-5 w-5 text-white" />
                </div>
                <span>Tambah Siswa</span>
            </Link>
        </section>

        <!-- Stats Grid (Modern Cards) -->
        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-emerald-200">
                <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 blur-xl group-hover:bg-emerald-100 transition-colors"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Siswa</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.total }}</p>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700">
                        <UsersIcon class="h-3 w-3" />
                         <span>Seluruh Angkatan</span>
                    </div>
                </div>
            </div>
            
             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-sky-200">
                <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-sky-50 opacity-50 blur-xl group-hover:bg-sky-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Siswa Aktif</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.aktif }}</p>
                      <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-2 py-1 text-[10px] font-bold text-sky-700">
                        <CheckCircleIcon class="h-3 w-3" />
                        <span>Mengikuti Program</span>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-amber-200">
                 <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-amber-50 opacity-50 blur-xl group-hover:bg-amber-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Nonaktif</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.nonaktif }}</p>
                     <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700">
                        <UserMinusIcon class="h-3 w-3" />
                        <span>Lulus / Cuti</span>
                    </div>
                </div>
            </div>

             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-rose-200">
                  <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-rose-50 opacity-50 blur-xl group-hover:bg-rose-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Dominasi</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-800 truncate" :title="stats.most_education">{{ stats.most_education }}</p>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-700">
                        <TrophyIcon class="h-3 w-3" />
                        <span>Jenjang Terbanyak</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Card -->
        <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
             <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-8">
                <div>
                     <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <AcademicCapIcon class="h-6 w-6 text-slate-400" />
                        Daftar Siswa
                     </h2>
                    <p class="text-sm text-slate-500 font-medium mt-1">Cari berdasarkan nama, ID, atau tingkat pendidikan.</p>
                </div>
                <div class="relative w-full md:w-80">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <MagnifyingGlassIcon class="h-5 w-5" />
                    </div>
                    <input v-model="searchQuery" type="text"
                         placeholder="Cari ID, nama, pendidikan"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 py-3 text-sm font-medium text-slate-700 placeholder-slate-400 transition-all focus:border-[var(--color-primary)] focus:bg-white focus:ring-[var(--color-primary)]" />
                </div>
            </header>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">ID Siswa</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Nama Lengkap</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Pendidikan</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Orang Tua</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="student in students.data" :key="student.id" class="group hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <span class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 border border-slate-200 group-hover:border-slate-300 transition-colors">
                                    {{ student.student_id }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                     <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">
                                        {{ student.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800">{{ student.name }}</p>
                                        <p class="text-xs text-slate-400 font-medium">{{ student.school_name || 'Sekolah belum diisi' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-slate-300"></span>
                                    <span class="font-medium text-slate-600">{{ student.education_level }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="student.parent_name" class="flex items-center gap-2">
                                    <UserIcon class="h-4 w-4 text-slate-400" />
                                    <span class="font-medium text-slate-700">{{ student.parent_name }}</span>
                                </div>
                                <span v-else class="text-xs text-slate-400 italic">Belum terhubung</span>
                            </td>
                            <td class="px-6 py-4">
                                 <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold border shadow-sm"
                                    :class="student.status === 'Aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200'">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="student.status === 'Aktif' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                    <span>{{ student.status }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <Link :href="route('admin.students.edit', student.id)"
                                    class="group/btn inline-flex items-center justify-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition-all hover:border-[var(--color-primary)] hover:text-[var(--color-primary)] hover:shadow-sm active:scale-95">
                                    <span>Kelola</span>
                                    <PencilSquareIcon class="h-3 w-3" />
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="students.data.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                 <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 border border-slate-100">
                                        <AcademicCapIcon class="h-8 w-8 text-slate-300" />
                                    </div>
                                    <p class="text-lg font-bold text-slate-700">Tidak ada siswa ditemukan</p>
                                    <p class="text-sm text-slate-500 mt-1">Coba cari dengan kata kunci lain.</p>
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
                    <button @click="loadStudents(currentPage - 1)" :disabled="currentPage === 1"
                        class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="currentPage === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm'">
                        &larr; Prev
                    </button>
                    <div class="h-4 w-px bg-slate-200"></div>
                     <button v-for="page in pages" :key="page" @click="loadStudents(page)"
                        class="h-8 w-8 rounded-full text-xs font-bold flex items-center justify-center transition-all"
                        :class="page === currentPage
                            ? 'bg-[var(--color-primary)] text-white shadow-md'
                            : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)]'">
                        {{ page }}
                    </button>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <button @click="loadStudents(currentPage + 1)" :disabled="currentPage === lastPage"
                         class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="currentPage === lastPage ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm'">
                        Next &rarr;
                    </button>
                </div>
            </div>
        </section>
    </div>

    <Notivue v-slot="notification">
        <Notification :item="notification" />
    </Notivue>
</template>
