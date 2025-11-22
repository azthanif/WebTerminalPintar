<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    users: {
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

// search state (default dari filter server)
const search = ref(props.filters.search || '')

// current page & last page dari paginator Laravel
const currentPage = computed(() => props.users.current_page || 1)
const lastPage = computed(() => props.users.last_page || 1)

// hit API Inertia untuk ganti halaman / filter
const loadUsers = (page = 1) => {
    router.get(
        route('admin.users.index'),
        {
            page,
            search: search.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    )
}

const searchUsers = () => {
    loadUsers(1)
}

const nextPage = () => {
    if (props.users.next_page_url) {
        loadUsers(currentPage.value + 1)
    }
}

const previousPage = () => {
    if (props.users.prev_page_url) {
        loadUsers(currentPage.value - 1)
    }
}

const pages = computed(() => {
    if (!lastPage.value) return []
    const pageCount = Math.min(5, lastPage.value)
    const result = []
    let start = Math.max(1, currentPage.value - Math.floor(pageCount / 2))
    let end = Math.min(lastPage.value, start + pageCount - 1)

    if (end - start + 1 < pageCount) {
        start = Math.max(1, end - pageCount + 1)
    }

    for (let i = start; i <= end; i++) {
        result.push(i)
    }
    return result
})

const deleteUser = (id) => {
    if (!confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) return

    router.delete(route('admin.users.destroy', id), {
        preserveScroll: true,
    })
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('id-ID')
}

const formatTime = (time) => {
    if (!time) return '—'
    return new Date(time).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    })
}
</script>

<template>
    <div>

        <Head title="Manajemen Pengguna" />

        <div class="min-h-screen bg-gray-100">
            <!-- NAVBAR -->
            <nav class="bg-white shadow-md sticky top-0 z-40">
                <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                    <div class="text-2xl font-bold text-green-700">
                        <Link href="/admin/users">Terminal Pintar</Link>
                    </div>

                    <div class="hidden md:flex space-x-6 items-center">
                        <span class="text-gray-600 hover:text-green-700 cursor-pointer">
                            Dashboard
                        </span>
                        <span class="text-green-700 font-semibold border-b-2 border-green-700 pb-1">
                            Kelola Pengguna
                        </span>
                        <Link href="/admin/students" class="text-gray-600 hover:text-green-700">
                        Kelola Siswa
                        </Link>
                        <Link href="/admin/berita" class="text-gray-600 hover:text-green-700">
                        Berita &amp; Dokumentasi
                        </Link>
                        <span class="text-gray-600 hover:text-green-700 cursor-pointer">
                            Perpustakaan
                        </span>
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
                            <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                            <div class="text-sm">
                                <div class="font-medium">Admin User</div>
                                <div class="text-gray-500">Administrator</div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="container mx-auto px-6 py-8">
                <!-- HEADER + TOMBOL -->
                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-800 mb-2">
                            Manajemen Pengguna
                        </h1>
                        <p class="text-gray-600">
                            Kelola akun pengguna Terminal Pintar. Anda dapat menambah, mengedit,
                            dan menghapus akun pengguna
                        </p>
                    </div>
                    <Link :href="route('admin.users.create')"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center gap-2 text-sm">
                    <span>➕</span>
                    Tambah Pengguna
                    </Link>
                </div>

                <!-- STAT CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm mb-2">Total Pengguna</p>
                                <p class="text-3xl font-bold text-green-500">
                                    {{ stats.total || 0 }}
                                </p>
                            </div>
                            <span class="text-3xl text-green-400">👥</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm mb-2">Pengguna Aktif</p>
                                <p class="text-3xl font-bold text-yellow-500">
                                    {{ stats.aktif || 0 }}
                                </p>
                            </div>
                            <span class="text-3xl text-yellow-400">👤</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm mb-2">Admin &amp; Guru</p>
                                <p class="text-3xl font-bold text-red-500">
                                    {{ stats.admin_guru || 0 }}
                                </p>
                            </div>
                            <span class="text-3xl text-red-400">🛡️</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-600 text-sm mb-2">Orang Tua</p>
                                <p class="text-3xl font-bold text-blue-500">
                                    {{ stats.orang_tua || 0 }}
                                </p>
                            </div>
                            <span class="text-3xl text-blue-400">👨‍👩‍👧‍👦</span>
                        </div>
                    </div>
                </div>

                <!-- CARD DAFTAR PENGGUNA -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <!-- Header tabel + search -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-2">
                            <span class="text-green-500 text-xl">👥</span>
                            <h2 class="text-xl font-bold text-gray-800">Daftar Pengguna</h2>
                        </div>
                        <div class="relative w-full md:w-96">
                            <input v-model="search" @keyup.enter="searchUsers" type="text"
                                placeholder="Cari berdasarkan nama atau email"
                                class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500 text-sm" />
                            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                        </div>
                    </div>

                    <!-- Tabel -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Pengguna
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Kontak
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Peran
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Bergabung
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                        Login Terakhir
                                    </th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users.data" :key="user.id"
                                    class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <!-- Pengguna -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-semibold">
                                                {{ (user.nama || user.name).charAt(0).toUpperCase() }}
                                            </div>
                                            <span class="font-medium text-gray-800">
                                                {{ user.nama || user.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Kontak -->
                                    <td class="px-4 py-3">
                                        <div class="text-gray-600 text-sm">
                                            <div>{{ user.email }}</div>
                                            <div class="text-xs text-gray-400">
                                                {{ user.telepon || user.phone || '—' }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Peran -->
                                    <td class="px-4 py-3">
                                        <span v-if="user.role" :class="[
                                            'px-3 py-1 rounded-full text-xs font-semibold',
                                            user.role.nama_role === 'Admin'
                                                ? 'bg-red-100 text-red-700'
                                                : user.role.nama_role === 'Guru'
                                                    ? 'bg-yellow-100 text-yellow-700'
                                                    : user.role.nama_role === 'Orang Tua'
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-blue-100 text-blue-700',
                                        ]">
                                            {{ user.role.nama_role }}
                                        </span>
                                        <span v-else class="text-gray-400 text-xs">Tanpa Role</span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3">
                                        <span :class="[
                                            'px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1 w-fit',
                                            user.is_active
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700',
                                        ]">
                                            <span v-if="user.is_active">✓</span>
                                            <span v-else>✕</span>
                                            {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>

                                    <!-- Bergabung -->
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ formatDate(user.created_at) }}
                                    </td>

                                    <!-- Login terakhir -->
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ formatTime(user.last_login_at) }}
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2">
                                            <Link :href="route('admin.users.edit', user.id)"
                                                class="p-2 bg-yellow-100 text-yellow-600 rounded hover:bg-yellow-200 transition text-xs"
                                                title="Edit">
                                            ✎
                                            </Link>
                                            <button @click="deleteUser(user.id)"
                                                class="p-2 bg-red-100 text-red-600 rounded hover:bg-red-200 transition text-xs"
                                                title="Hapus">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="users.data.length === 0">
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                        Tidak ada data pengguna.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">
                            Menampilkan {{ users.from }}-{{ users.to }} dari
                            {{ users.total }} Pengguna
                        </p>
                        <div class="flex gap-2">
                            <button @click="previousPage" :disabled="!users.prev_page_url"
                                class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                                ← Sebelumnya
                            </button>
                            <div class="flex items-center gap-1">
                                <span v-for="page in pages" :key="page" @click="loadUsers(page)"
                                    class="px-2 py-1 border rounded cursor-pointer hover:bg-gray-50 text-sm" :class="{
                                        'bg-green-500 text-white border-green-500':
                                            page === currentPage,
                                    }">
                                    {{ page }}
                                </span>
                            </div>
                            <button @click="nextPage" :disabled="!users.next_page_url"
                                class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                                Selanjutnya →
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
::-webkit-scrollbar {
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
</style>



<!-- <div class="flex gap-1">
                            <Link v-for="link in props.users.links" :key="link?.label ?? link?.url ?? Math.random()"
                                :href="link?.url || '#'" v-html="link?.label ?? ''"
                                class="px-3 py-1 rounded-full border text-xs" :class="{
                                    'bg-emerald-600 text-white border-emerald-600': link?.active,
                                    'text-slate-500 border-slate-200 hover:bg-slate-100': !link?.active

                                }" :disabled="link ? !link.url : true" />
 </div> -->
