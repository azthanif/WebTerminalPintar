<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

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

const searchQuery = ref(props.filters.search || '')

const paginationInfo = computed(() => {
    if (!props.berita || props.berita.total === 0) {
        return 'Tidak ada data berita'
    }
    return `Menampilkan ${props.berita.from} - ${props.berita.to} dari ${props.berita.total} berita`
})

const searchData = () => {
    router.get(
        route('admin.berita.index'),
        { search: searchQuery.value },
        { preserveState: true, replace: true },
    )
}

const goToPage = (url) => {
    if (!url) return
    router.get(url, {}, { preserveState: true, replace: true })
}

const deleteBerita = (id) => {
    if (!confirm('Apakah Anda yakin ingin menghapus berita ini?')) return

    router.delete(route('admin.berita.destroy', id), {
        preserveScroll: true,
    })
}

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
</script>

<template>
    <div>

        <Head title="Manajemen Berita" />

        <div class="min-h-screen bg-gray-100">
            <!-- NAVBAR -->
            <nav class="bg-white shadow-md sticky top-0 z-40">
                <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                    <div class="text-2xl font-bold text-green-700">
                        <Link href="/admin/users">Terminal Pintar</Link>
                    </div>

                    <div class="hidden md:flex space-x-6 items-center">
                        <span class="text-gray-600">Dashboard</span>
                        <Link href="/admin/users" class="text-gray-600 hover:text-green-700">
                        Kelola Pengguna
                        </Link>
                        <Link href="/admin/students" class="text-gray-600 hover:text-green-700">
                        Kelola Siswa
                        </Link>
                        <span class="text-green-700 font-semibold border-b-2 border-green-700 pb-1">
                            Berita &amp; Dokumentasi
                        </span>
                        <span class="text-gray-600 hover:text-green-700">Perpustakaan</span>
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
                <!-- HEADER -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">
                        Manajemen Berita dan Dokumentasi
                    </h1>
                    <p class="text-gray-600 mt-2">
                        Kelola data berita dan galeri. Anda dapat menambah dan mengedit data
                        berita dan galeri
                    </p>
                </div>

                <!-- STAT CARD -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-md flex justify-between items-center lg:col-span-1">
                        <div>
                            <p class="text-gray-600 text-sm">Total Berita/Artikel</p>
                            <p class="text-3xl font-bold text-gray-800">
                                {{ stats.total_berita }}
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-md flex justify-between items-center lg:col-span-1">
                        <div>
                            <p class="text-gray-600 text-sm">Foto</p>
                            <p class="text-3xl font-bold text-gray-800">
                                {{ stats.total_foto }}
                            </p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <svg class="w-6 h-6 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l-1.586-1.586a2 2 0 00-2.828 0L6 14m6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- CARD TABEL BERITA -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Header tabel -->
                    <div class="p-6 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <h2 class="text-xl font-bold text-gray-800">Daftar Artikel/Berita</h2>

                        <div
                            class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4 w-full md:w-auto">
                            <div class="relative w-full md:w-64">
                                <input v-model="searchQuery" type="text" placeholder="Cari berdasarkan ID atau judul"
                                    class="border rounded-lg p-2 pl-10 w-full" @keyup.enter="searchData" />
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                            </div>

                            <Link :href="route('admin.berita.create')"
                                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 w-full md:w-auto text-sm text-center">
                            + Tambah Berita
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
                                        Judul
                                    </th>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        Deskripsi
                                    </th>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        Tanggal Kegiatan
                                    </th>
                                    <th class="text-left text-sm font-semibold text-gray-600 p-4">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="berita.data.length === 0">
                                    <td colspan="5" class="p-4 text-center text-gray-500">
                                        Data tidak ditemukan.
                                    </td>
                                </tr>

                                <tr v-for="item in berita.data" :key="item.id" class="hover:bg-gray-50">
                                    <td class="p-4 whitespace-nowrap">
                                        <span
                                            class="bg-green-100 text-green-700 text-xs font-medium px-2 py-1 rounded-full">
                                            B{{ String(item.id).padStart(3, '0') }}
                                        </span>
                                    </td>
                                    <td class="p-4 whitespace-nowrap font-medium text-gray-800">
                                        {{ item.judul }}
                                    </td>
                                    <td class="p-4 text-gray-600 max-w-xs truncate">
                                        {{ truncate(item.konten, 70) }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap text-gray-600">
                                        {{ formatDate(item.created_at) }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Link :href="route('admin.berita.edit', item.id)"
                                                class="text-orange-500 hover:text-orange-700" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.536L16.732 3.732z">
                                                </path>
                                            </svg>
                                            </Link>
                                            <button class="text-red-500 hover:text-red-700" title="Hapus"
                                                @click="deleteBerita(item.id)">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- FOOTER PAGINATION -->
                    <div
                        class="p-6 flex flex-col md:flex-row justify-between items-center border-t border-gray-200 bg-gray-50">
                        <span class="text-sm text-gray-600 mb-4 md:mb-0">
                            {{ paginationInfo }}
                        </span>

                        <div class="flex space-x-2">
                            <button @click="goToPage(berita.prev_page_url)" :disabled="!berita.prev_page_url"
                                class="px-4 py-2 text-sm rounded-lg" :class="{
                                    'bg-white border text-gray-600 hover:bg-gray-100':
                                        berita.prev_page_url,
                                    'bg-gray-200 text-gray-400 cursor-not-allowed':
                                        !berita.prev_page_url,
                                }">
                                &larr; Sebelumnya
                            </button>
                            <button @click="goToPage(berita.next_page_url)" :disabled="!berita.next_page_url"
                                class="px-4 py-2 text-sm rounded-lg" :class="{
                                    'bg-white border text-gray-600 hover:bg-gray-100':
                                        berita.next_page_url,
                                    'bg-gray-200 text-gray-400 cursor-not-allowed':
                                        !berita.next_page_url,
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
