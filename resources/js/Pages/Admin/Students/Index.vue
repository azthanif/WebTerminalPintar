<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

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
</script>

<template>
    <div>

        <Head title="Manajemen Siswa" />

        <div class="min-h-screen bg-gray-100">
            <!-- NAVBAR -->
            <nav class="bg-white shadow-md sticky top-0 z-40">
                <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                    <div class="text-2xl font-bold text-green-700">
                        <Link href="/admin/users">Terminal Pintar</Link>
                    </div>

                    <div class="hidden md:flex space-x-6 items-center text-sm">
                        <span class="text-gray-600">Dashboard</span>
                        <Link href="/admin/users" class="text-gray-600 hover:text-green-700">
                        Kelola Pengguna
                        </Link>
                        <span class="text-green-700 font-semibold border-b-2 border-green-700 pb-1">
                            Kelola Siswa
                        </span>
                         <Link href="/admin/berita" class="text-gray-600 hover:text-green-700">
                        Berita &amp; Dokumentasi
                        </Link>
                        <span class="text-gray-600">Perpustakaan</span>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                        </button>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gray-300" />
                            <div class="text-sm">
                                <div class="font-medium">Admin User</div>
                                <div class="text-gray-500">Administrator</div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="container mx-auto px-6 py-8">
                <!-- JUDUL -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Manajemen Siswa</h1>
                    <p class="text-gray-600 mt-2">
                        Kelola data siswa Terminal Pintar. Anda dapat menambah dan mengedit
                        data siswa
                    </p>
                </div>

                <!-- STAT CARD -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-md flex justify-between items-center">
                        <div>
                            <p class="text-gray-600 text-sm">Total Siswa</p>
                            <p class="text-3xl font-bold text-gray-800">
                                {{ stats.total }}
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.084-1.284-.24-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.084-1.284.24-1.857m0 0a5.002 5.002 0 019.52 0M12 10a3 3 0 110-6 3 3 0 010 6z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md flex justify-between items-center">
                        <div>
                            <p class="text-gray-600 text-sm">Siswa Aktif</p>
                            <p class="text-3xl font-bold text-gray-800">
                                {{ stats.aktif }}
                            </p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md flex justify-between items-center">
                        <div>
                            <p class="text-gray-600 text-sm">Siswa Nonaktif</p>
                            <p class="text-3xl font-bold text-gray-800">
                                {{ stats.nonaktif }}
                            </p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md flex justify-between items-center">
                        <div>
                            <p class="text-gray-600 text-sm">
                                Pendidikan Terakhir Terbanyak
                            </p>
                            <p class="text-2xl font-bold text-gray-800">
                                {{ stats.most_education }}
                            </p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- CARD DAFTAR SISWA -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Header + search -->
                    <div class="p-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <h2 class="text-xl font-bold text-gray-800">Daftar Siswa</h2>

                        <div
                            class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4 w-full md:w-auto">
                            <div class="relative w-full md:w-64">
                                <input v-model="searchQuery" type="text" placeholder="Cari ID, nama, atau pendidikan"
                                    class="border rounded-lg p-2 pl-10 w-full text-sm" @keyup.enter="searchData" />
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                            </div>

                            <Link :href="route('admin.students.create')"
                                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 w-full md:w-auto text-sm text-center">
                            + Tambah Siswa
                            </Link>
                        </div>
                    </div>

                    <!-- Tabel -->
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-max">
                            <thead class="bg-gray-50 border-b border-t border-gray-200">
                                <tr>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        ID
                                    </th>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        Nama Siswa
                                    </th>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        Pendidikan Terakhir
                                    </th>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        Orang Tua
                                    </th>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        Status
                                    </th>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="students.data.length === 0">
                                    <td colspan="6" class="p-4 text-center text-gray-500">
                                        Data tidak ditemukan.
                                    </td>
                                </tr>

                                <tr v-for="student in students.data" :key="student.id" class="hover:bg-gray-50">
                                    <td class="p-4 whitespace-nowrap">
                                        <span
                                            class="bg-green-100 text-green-700 text-xs font-medium px-2 py-1 rounded-full">
                                            {{ student.student_id }}
                                        </span>
                                    </td>
                                    <td class="p-4 whitespace-nowrap font-medium text-gray-800">
                                        {{ student.name }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-gray-600">
                                        {{ student.education_level }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-gray-600">
                                        {{ student.parent_name }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium" :class="student.status === 'Aktif'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700'
                                            ">
                                            {{ student.status }}
                                        </span>
                                    </td>
                                    <td class="p-4 whitespace-nowrap">
                                        <Link :href="route('admin.students.edit', student.id)"
                                            class="text-orange-500 hover:text-orange-700 mr-2 inline-block">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z">
                                            </path>
                                        </svg>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer pagination -->
                    <div
                        class="p-6 flex flex-col md:flex-row justify-between items-center border-t border-gray-200 bg-gray-50 gap-3">
                        <span class="text-sm text-gray-600">
                            {{ paginationInfo }}
                        </span>

                        <div class="flex space-x-2">
                            <button @click="goToPage(students.prev_page_url)" :disabled="!students.prev_page_url"
                                class="px-4 py-2 text-sm rounded-lg" :class="{
                                    'bg-white border text-gray-600 hover:bg-gray-100':
                                        students.prev_page_url,
                                    'bg-gray-200 text-gray-400 cursor-not-allowed':
                                        !students.prev_page_url,
                                }">
                                &larr; Sebelumnya
                            </button>
                            <button @click="goToPage(students.next_page_url)" :disabled="!students.next_page_url"
                                class="px-4 py-2 text-sm rounded-lg" :class="{
                                    'bg-white border text-gray-600 hover:bg-gray-100':
                                        students.next_page_url,
                                    'bg-gray-200 text-gray-400 cursor-not-allowed':
                                        !students.next_page_url,
                                }">
                                Berikutnya &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

</template>
