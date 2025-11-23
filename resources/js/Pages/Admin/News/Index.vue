<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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
defineOptions({
    layout: AdminLayout,
})
</script>

<template>
    <div class="space-y-10">

        <Head title="Manajemen Berita" />

        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Konten</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900">Berita &amp; Dokumentasi</h1>
                    <p class="text-sm text-slate-500">Publikasikan aktivitas dan galeri terbaru.</p>
                </div>
                <Link :href="route('admin.berita.create')"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-600">
                + Tambah Berita
                </Link>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2">
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total Artikel</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.total_berita }}</p>
                <p class="text-xs text-emerald-500">Berita dan dokumentasi</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Jumlah Foto</p>
                <p class="mt-3 text-4xl font-semibold text-slate-900">{{ stats.total_foto }}</p>
                <p class="text-xs text-amber-500">Unggahan bergambar</p>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">Daftar Artikel / Berita</h2>
                    <p class="text-sm text-slate-500">Filter berdasarkan ID atau judul berita.</p>
                </div>
                <div class="relative w-full md:w-80">
                    <span class="pointer-events-none absolute left-3 top-0 flex h-full items-center text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 3.5a5.5 5.5 0 013.962 9.337l3.85 3.85a.75.75 0 11-1.06 1.06l-3.85-3.85A5.5 5.5 0 119 3.5zm0 1.5a4 4 0 100 8 4 4 0 000-8z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input v-model="searchQuery" type="text" placeholder="Cari berdasarkan ID atau judul"
                        @keyup.enter="searchData"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-10 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-100">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Judul</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        <tr v-for="item in berita.data" :key="item.id" class="hover:bg-slate-50/70">
                            <td class="px-4 py-4">
                                <span
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">
                                    B{{ String(item.id).padStart(3, '0') }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-900">{{ item.judul }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ truncate(item.konten, 80) }}
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <Link :href="route('admin.berita.edit', item.id)"
                                        class="inline-flex items-center rounded-full border border-amber-100 px-3 py-1 text-xs font-semibold text-amber-600 transition hover:bg-amber-50">
                                    Edit
                                    </Link>
                                    <button @click="deleteBerita(item.id)"
                                        class="inline-flex items-center rounded-full border border-rose-100 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="berita.data.length === 0">
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-6 text-sm text-slate-500 md:flex-row md:items-center md:justify-between">
                <span>{{ paginationInfo }}</span>
                <div class="flex items-center gap-2">
                    <button @click="goToPage(berita.prev_page_url)" :disabled="!berita.prev_page_url"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold"
                        :class="berita.prev_page_url ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'">
                        Sebelumnya
                    </button>
                    <button @click="goToPage(berita.next_page_url)" :disabled="!berita.next_page_url"
                        class="rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold"
                        :class="berita.next_page_url ? 'text-slate-700 hover:bg-slate-50' : 'cursor-not-allowed text-slate-300'">
                        Berikutnya
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
