<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import { MagnifyingGlassIcon, PlusIcon, UserIcon, TrashIcon, PencilSquareIcon, ShieldCheckIcon, UserGroupIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline'

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
    currentUserId: {
        type: Number,
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

const PER_PAGE_LIMIT = 10
const search = ref(props.filters.search || '')

const user = computed(() => page.props.auth?.user ?? null)
const currentUserId = computed(() => page.props.auth?.user?.id)

const paginationMeta = computed(() => props.users?.meta ?? {})

// current page & last page dari paginator Laravel
const currentPage = computed(() => paginationMeta.value.current_page || 1)
const lastPage = computed(() => paginationMeta.value.last_page || 1)

const paginationInfo = computed(() => {
    const meta = paginationMeta.value
    const from = meta.from ?? 0
    const to = meta.to ?? 0
    const total = meta.total ?? 0
    return `Menampilkan ${from}-${to} dari ${total} pengguna`
})

// hit API Inertia untuk ganti halaman / filter
const loadUsers = (page = 1) => {
    router.get(
        route('admin.users.index'),
        {
            page,
            search: search.value,
            per_page: PER_PAGE_LIMIT,
        },
        {
            preserveState: true,
            replace: true,
        },
    )
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

let searchDebounceId
watch(search, () => {
    clearTimeout(searchDebounceId)
    searchDebounceId = setTimeout(() => loadUsers(1), 400)
})

const showDeleteModal = ref(false)
const userPendingDelete = ref(null)

const openDeleteModal = (user) => {
    userPendingDelete.value = user
    showDeleteModal.value = true
}

const isCurrentUser = (user) => {
    if (!user) return false
    return user.id === props.currentUserId
}

const attemptDelete = (user) => {
    if (isCurrentUser(user)) {
        pushFlashNotification('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.')
        return
    }

    openDeleteModal(user)
}

const closeDeleteModal = () => {
    showDeleteModal.value = false
    userPendingDelete.value = null
}

const deleteUser = () => {
    if (!userPendingDelete.value) return

    if (isCurrentUser(userPendingDelete.value)) {
        pushFlashNotification('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.')
        closeDeleteModal()
        return
    }

    router.delete(route('admin.users.destroy', userPendingDelete.value.id), {
        preserveScroll: true,
        onFinish: () => closeDeleteModal(),
    })
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        timeZone: 'Asia/Jakarta',
    })
}

const formatDateTime = (value) => {
    if (!value) {
        return 'Belum pernah login'
    }

    return new Date(value).toLocaleString('id-ID', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    })
}

// Dynamic role colors
const getRoleColor = (roleName) => {
    if (!roleName) return 'bg-slate-100 text-slate-600 border-slate-200'
    
    const name = roleName.toLowerCase()
    
    if (name.includes('admin')) {
        return 'bg-purple-100 text-purple-700 border-purple-200'
    } else if (name.includes('guru') || name.includes('teacher')) {
        return 'bg-blue-100 text-blue-700 border-blue-200'
    } else if (name.includes('orang tua') || name.includes('parent')) {
        return 'bg-amber-100 text-amber-700 border-amber-200'
    } else if (name.includes('siswa') || name.includes('student')) {
        return 'bg-green-100 text-green-700 border-green-200'
    } else {
        return 'bg-indigo-100 text-indigo-700 border-indigo-200'
    }
}

// Dynamic avatar colors based on role (matching role badge colors)
const getAvatarColor = (roleName) => {
    if (!roleName) return { bg: 'bg-gradient-to-br from-slate-400 to-slate-500', text: 'text-white', ring: 'ring-slate-300' }
    
    const name = roleName.toLowerCase()
    
    if (name.includes('admin')) {
        return { bg: 'bg-gradient-to-br from-purple-400 to-purple-600', text: 'text-white', ring: 'ring-purple-300' }
    } else if (name.includes('guru') || name.includes('teacher')) {
        return { bg: 'bg-gradient-to-br from-blue-400 to-blue-600', text: 'text-white', ring: 'ring-blue-300' }
    } else if (name.includes('orang tua') || name.includes('parent')) {
        return { bg: 'bg-gradient-to-br from-amber-400 to-amber-600', text: 'text-white', ring: 'ring-amber-300' }
    } else if (name.includes('siswa') || name.includes('student')) {
        return { bg: 'bg-gradient-to-br from-green-400 to-green-600', text: 'text-white', ring: 'ring-green-300' }
    } else {
        return { bg: 'bg-gradient-to-br from-indigo-400 to-indigo-600', text: 'text-white', ring: 'ring-indigo-300' }
    }
}
</script>

<template>
    <div class="space-y-8">
        <Head title="Kelola Pengguna" />

        <!-- Page Header -->
        <PageHeader badgeLabel="Admin Panel" badgeColor="blue">
            <template #title>
                <p class="text-2xl font-bold text-slate-700 mt-0.5" style="font-family: 'Poppins', sans-serif;">
                    Selamat datang kembali, <span class="text-[var(--color-primary)]">{{ user?.name }}</span>!
                </p>
            </template>
        </PageHeader>

        <!-- Stats Grid (Modern Cards) -->
        <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-emerald-200">
                <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 blur-xl group-hover:bg-emerald-100 transition-colors"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Pengguna</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.total || 0 }}</p>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700">
                        <UserGroupIcon class="h-3 w-3" />
                         <span>Semua Akun</span>
                    </div>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-sky-200">
                <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-sky-50 opacity-50 blur-xl group-hover:bg-sky-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Pengguna Aktif</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.aktif || 0 }}</p>
                      <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-2 py-1 text-[10px] font-bold text-sky-700">
                        <ShieldCheckIcon class="h-3 w-3" />
                        <span>Terverifikasi</span>
                    </div>
                </div>
            </div>

             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-amber-200">
                 <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-amber-50 opacity-50 blur-xl group-hover:bg-amber-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Admin & Guru</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.admin_guru || 0 }}</p>
                     <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700">
                        <UserIcon class="h-3 w-3" />
                        <span>Akses Tinggi</span>
                    </div>
                </div>
            </div>

             <div class="group relative overflow-hidden rounded-[2rem] bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1 hover:border-rose-200">
                  <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-rose-50 opacity-50 blur-xl group-hover:bg-rose-100 transition-colors"></div>
                 <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Orang Tua</p>
                    <p class="mt-2 text-4xl font-extrabold text-slate-800">{{ stats.orang_tua || 0 }}</p>
                    <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-700">
                        <UserIcon class="h-3 w-3" />
                        <span>Wali Murid</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Card -->
        <section class="rounded-[2.5rem] border border-slate-200 bg-white p-8 shadow-sm">
            <!-- Table Toolbar (Standardized) -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <!-- Left: Title -->
                <div class="w-full md:w-1/4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <UserGroupIcon class="h-6 w-6 text-slate-400" />
                        Daftar Pengguna
                    </h2>
                </div>

                <!-- Center: Search Bar -->
                <div class="w-full md:w-1/2 flex justify-center">
                    <div class="relative w-full max-w-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <MagnifyingGlassIcon class="h-5 w-5" />
                        </div>
                        <input v-model="search" type="text"
                            placeholder="Cari nama atau email..."
                            class="w-full rounded-full border border-gray-300 bg-white pl-11 pr-4 py-2.5 text-sm font-medium text-slate-700 placeholder-slate-400 transition-all focus:border-[var(--color-primary)] focus:ring-2 focus:ring-[var(--color-primary)]/20 focus:outline-none" />
                    </div>
                </div>

                <!-- Right: Action Button -->
                <div class="w-full md:w-1/4 flex justify-end">
                    <Link :href="route('admin.users.create')"
                        class="group inline-flex items-center justify-center gap-2 rounded-full bg-[var(--color-primary)] px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-[var(--color-primary)]/30 transition-all hover:bg-[var(--color-primary-hover)] hover:-translate-y-0.5 hover:shadow-xl active:scale-95">
                        <PlusIcon class="h-4 w-4" />
                        <span>Tambah Pengguna</span>
                    </Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Pengguna</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Kontak</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-400">Login Terakhir</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="group hover:bg-slate-50/80 transition-colors"
                            :class="{'bg-[var(--color-primary-lighter)]/30 hover:bg-[var(--color-primary-lighter)]/50': isCurrentUser(user)}"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold shadow-lg ring-2 transition-all group-hover:scale-110 group-hover:shadow-xl"
                                        :class="[getAvatarColor(user.role?.nama_role).bg, getAvatarColor(user.role?.nama_role).text, getAvatarColor(user.role?.nama_role).ring]">
                                        {{ (user.nama || user.name).charAt(0).toUpperCase() }}
                                        <div class="absolute inset-0 rounded-full bg-white opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-800">{{ user.nama || user.name }}</span>
                                             <span v-if="isCurrentUser(user)" class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-700">You</span>
                                        </div>
                                        <p class="text-xs font-medium text-slate-400">ID: #{{ user.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-700">{{ user.email }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ user.telepon || user.phone || '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold border shadow-sm transition-all hover:scale-105"
                                    :class="getRoleColor(user.role?.nama_role)">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                                    {{ user.role?.nama_role || 'Belum set role' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold border-2 shadow-md transition-all hover:scale-105"
                                    :class="user.is_active 
                                        ? 'bg-gradient-to-r from-emerald-50 to-green-50 text-emerald-700 border-emerald-300' 
                                        : 'bg-gradient-to-r from-rose-50 to-red-50 text-rose-700 border-rose-300'">
                                    <span class="relative flex h-2 w-2">
                                        <span v-if="user.is_active" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2" 
                                            :class="user.is_active ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    </span>
                                    <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-600">{{ formatDateTime(user.last_login_at) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="route('admin.users.edit', user.id)"
                                        class="group/btn inline-flex items-center justify-center gap-1.5 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-amber-200 transition-all hover:from-amber-600 hover:to-amber-700 hover:-translate-y-0.5 hover:shadow-xl active:scale-95"
                                        title="Edit Pengguna">
                                        <PencilSquareIcon class="h-3.5 w-3.5" />
                                        <span>Edit</span>
                                    </Link>
                                    <button
                                        @click="attemptDelete(user)"
                                        :disabled="isCurrentUser(user)"
                                        class="group/btn inline-flex items-center justify-center h-8 w-8 rounded-full transition-all border"
                                        :class="isCurrentUser(user)
                                            ? 'cursor-not-allowed bg-slate-100 text-slate-300 border-slate-200'
                                            : 'bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100 hover:text-rose-700 hover:scale-110 active:scale-95'"
                                        title="Hapus Pengguna">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                                        <UserGroupIcon class="h-8 w-8 text-slate-300" />
                                    </div>
                                    <p class="text-lg font-bold text-slate-700">Tidak ada pengguna ditemukan</p>
                                    <p class="text-sm text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian Anda.</p>
                                    <button @click="search = ''" class="mt-4 text-sm font-bold text-[var(--color-primary)] hover:underline">
                                        Reset Pencarian
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
                    <button @click="loadUsers(currentPage - 1)" :disabled="currentPage === 1"
                        class="rounded-full px-4 py-2 text-xs font-bold transition-all"
                        :class="currentPage === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)] hover:shadow-sm'">
                        &larr; Prev
                    </button>
                    <div class="h-4 w-px bg-slate-200"></div>
                     <button v-for="page in pages" :key="page" @click="loadUsers(page)"
                        class="h-8 w-8 rounded-full text-xs font-bold flex items-center justify-center transition-all"
                        :class="page === currentPage
                            ? 'bg-[var(--color-primary)] text-white shadow-md'
                            : 'text-slate-600 hover:bg-white hover:text-[var(--color-primary)]'">
                        {{ page }}
                    </button>
                    <div class="h-4 w-px bg-slate-200"></div>
                    <button @click="loadUsers(currentPage + 1)" :disabled="currentPage === lastPage"
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
                            <TrashIcon class="h-8 w-8" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">Hapus Pengguna?</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Apakah Anda yakin ingin menghapus akun <span class="font-bold text-slate-800">"{{ userPendingDelete?.nama || userPendingDelete?.name }}"</span>? Tindakan ini tidak dapat dibatalkan.
                        </p>
                        
                        <div class="mt-6 space-y-3 rounded-2xl bg-slate-50 p-4 border border-slate-100 text-left">
                           <div class="flex items-center justify-between text-xs">
                               <span class="font-semibold text-slate-500 uppercase tracking-wider">Role</span>
                               <span class="font-bold text-slate-700">{{ userPendingDelete?.role?.nama_role || '—' }}</span>
                           </div>
                           <div class="flex items-center justify-between text-xs">
                               <span class="font-semibold text-slate-500 uppercase tracking-wider">Status</span>
                               <span class="font-bold text-emerald-600" :class="{'text-rose-600': !userPendingDelete?.is_active}">{{ userPendingDelete?.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                           </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="closeDeleteModal"
                                class="flex-1 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 active:scale-95">
                                Batal
                            </button>
                            <button type="button" @click="deleteUser"
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
