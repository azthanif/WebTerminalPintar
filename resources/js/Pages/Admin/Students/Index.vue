<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    students: { type: Object, required: true },
    stats: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})

const searchQuery = ref(props.filters.search || '')

const paginationInfo = computed(() => {
    if (!props.students || props.students.total === 0) {
        return 'Tidak ada data siswa'
    }
    return `Menampilkan ${props.students.from} - ${props.students.to} dari ${props.students.total} siswa`
})

const searchData = () => {
    router.get(
        route('admin.students.index'),
        { search: searchQuery.value },
        { preserveState: true, replace: true },
    )
}

const goToPage = (url) => {
    if (!url) return
    router.get(url, {}, { preserveState: true, replace: true })
}

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
                    <input v-model="searchQuery" type="text" @keyup.enter="searchData"
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

            <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-6 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <span>{{ paginationInfo }}</span>
                <div class="flex items-center gap-2">
                    <button @click="goToPage(students.prev_page_url)" :disabled="!students.prev_page_url"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold"
                        :class="students.prev_page_url ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'">
                        Sebelumnya
                    </button>
                    <button @click="goToPage(students.next_page_url)" :disabled="!students.next_page_url"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold"
                        :class="students.next_page_url ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'">
                        Berikutnya
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
