<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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

const paginationMeta = computed(() => props.users?.meta ?? {})

// current page & last page dari paginator Laravel
const currentPage = computed(() => paginationMeta.value.current_page || 1)
const lastPage = computed(() => paginationMeta.value.last_page || 1)

const paginationInfo = computed(() => {
    const meta = paginationMeta.value
    const from = meta.from ?? 0
    const to = meta.to ?? 0
    const total = meta.total ?? 0
    return `Menampilkan ${from}-${to} dari ${total} pengguna · ${PER_PAGE_LIMIT} data per halaman`
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
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    })
}
</script>

<template>
    <div class="space-y-10">
        <Head title="Kelola Pengguna" />

        <section class="flex flex-col gap-4 rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-slate-400">Pengguna</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">Manajemen Pengguna</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola akun admin, guru, dan orang tua di Terminal Pintar.
                </p>
            </div>
            <Link :href="route('admin.users.create')"
                class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-600">
            + Tambah Pengguna
            </Link>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total Pengguna</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.total || 0 }}</p>
                <p class="text-xs text-emerald-500">Semua akun terdaftar</p>
            </div>
            <div class="rounded-3xl border border-sky-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pengguna Aktif</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.aktif || 0 }}</p>
                <p class="text-xs text-sky-500">Sudah terverifikasi</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Admin &amp; Guru</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.admin_guru || 0 }}</p>
                <p class="text-xs text-amber-500">Memiliki hak akses tinggi</p>
            </div>
            <div class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Orang Tua</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.orang_tua || 0 }}</p>
                <p class="text-xs text-rose-500">Pemantau perkembangan</p>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Daftar Pengguna</h2>
                    <p class="text-sm text-slate-500">Cari dan kelola akun pengguna</p>
                </div>
                <div class="flex w-full flex-col gap-3 md:w-auto md:flex-row md:items-center">
                    <div class="relative flex-1 md:w-72">
                        <span class="pointer-events-none absolute left-3 top-0 flex h-full items-center text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M9 3.5a5.5 5.5 0 013.962 9.337l3.85 3.85a.75.75 0 11-1.06 1.06l-3.85-3.85A5.5 5.5 0 119 3.5zm0 1.5a4 4 0 100 8 4 4 0 000-8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                        <input v-model="search" type="text" placeholder="Cari nama atau email"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-10 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                    </div>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Pengguna</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Bergabung</th>
                            <th class="px-4 py-3">Login Terakhir</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            :class="[
                                'hover:bg-slate-50/50 transition-colors',
                                isCurrentUser(user) ? 'bg-emerald-50/60 ring-1 ring-emerald-100' : '',
                            ]"
                        >
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-sm font-semibold text-emerald-600">
                                        {{ (user.nama || user.name).charAt(0).toUpperCase() }}
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-slate-900">{{ user.nama || user.name }}</p>
                                            <span
                                                v-if="isCurrentUser(user)"
                                                class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-emerald-700"
                                            >
                                                Akun Aktif
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500">ID: {{ user.id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-medium text-slate-700">{{ user.email }}</p>
                                <p class="text-xs text-slate-500">{{ user.telepon || user.phone || '—' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                                    :class="user.role
                                        ? 'bg-emerald-50 text-emerald-600'
                                        : 'bg-slate-100 text-slate-500'">
                                    {{ user.role?.nama_role || 'Belum diset' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="user.is_active
                                        ? 'bg-emerald-50 text-emerald-600'
                                        : 'bg-rose-50 text-rose-600'">
                                    <span>{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ formatDateTime(user.last_login_at) }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="route('admin.users.edit', user.id)"
                                        class="inline-flex items-center rounded-full border border-amber-100 px-3 py-1 text-xs font-semibold text-amber-600 transition hover:bg-amber-50">
                                    Edit
                                    </Link>
                                    <button
                                        @click="attemptDelete(user)"
                                        :disabled="isCurrentUser(user)"
                                        class="inline-flex items-center rounded-full border border-rose-100 px-3 py-1 text-xs font-semibold transition"
                                        :class="isCurrentUser(user)
                                            ? 'cursor-not-allowed border-slate-100 text-slate-300'
                                            : 'text-rose-600 hover:bg-rose-50'"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">
                                Belum ada data pengguna.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col gap-4 border-t border-slate-100 pt-6 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
                <p>{{ paginationInfo }}</p>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="loadUsers(currentPage - 1)" :disabled="currentPage === 1"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition"
                        :class="currentPage === 1 ? 'cursor-not-allowed text-slate-300' : 'text-slate-700 hover:bg-slate-50'">
                        Sebelumnya
                    </button>
                    <button v-for="page in pages" :key="page" @click="loadUsers(page)"
                        class="rounded-full px-3 py-1 text-xs font-semibold"
                        :class="page === currentPage
                            ? 'bg-emerald-500 text-white'
                            : 'text-slate-600 hover:bg-slate-50'">
                        {{ page }}
                    </button>
                    <button @click="loadUsers(currentPage + 1)" :disabled="currentPage === lastPage"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold transition"
                        :class="currentPage === lastPage ? 'cursor-not-allowed text-slate-300' : 'text-slate-700 hover:bg-slate-50'">
                        Selanjutnya
                    </button>
                </div>
            </div>
        </section>

        <transition name="fade">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                <div class="w-full max-w-md rounded-3xl bg-white shadow-2xl">
                    <div class="rounded-t-3xl bg-rose-600 p-6 text-white">
                        <p class="text-sm font-semibold uppercase tracking-widest">Hapus Data Pengguna</p>
                        <p class="mt-1 text-xs text-rose-100">Hapus data pengguna sesuai beserta kredensial yang dimilikinya</p>
                    </div>
                    <div class="space-y-6 px-6 pb-6 pt-8">
                        <div class="rounded-2xl border-2 border-rose-100 bg-rose-50 p-4 text-center text-rose-600">
                            <p class="text-sm font-semibold">Apakah anda yakin untuk menghapus akun pengguna ini?</p>
                        </div>
                        <div class="space-y-3 rounded-2xl border border-rose-100 p-4 text-sm">
                            <p class="font-semibold text-slate-800">Informasi Pengguna</p>
                            <p class="text-slate-600">nama: <span class="font-medium text-slate-900">{{ userPendingDelete?.nama || userPendingDelete?.name }}</span></p>
                            <p class="text-slate-600">peran: <span class="font-medium text-slate-900">{{ userPendingDelete?.role?.nama_role || 'Belum diset' }}</span></p>
                            <p class="text-slate-600">status: <span class="font-medium text-slate-900">{{ userPendingDelete?.is_active ? 'aktif' : 'nonaktif' }}</span></p>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="closeDeleteModal"
                                class="flex-1 rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="button" @click="deleteUser"
                                class="flex-1 rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-700">
                                Hapus
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



<!-- <div class="flex gap-1">
                            <Link v-for="link in props.users.links" :key="link?.label ?? link?.url ?? Math.random()"
                                :href="link?.url || '#'" v-html="link?.label ?? ''"
                                class="px-3 py-1 rounded-full border text-xs" :class="{
                                    'bg-emerald-600 text-white border-emerald-600': link?.active,
                                    'text-slate-500 border-slate-200 hover:bg-slate-100': !link?.active

                                }" :disabled="link ? !link.url : true" />
 </div> -->
