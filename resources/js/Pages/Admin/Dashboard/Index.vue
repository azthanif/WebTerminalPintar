<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recentNews: {
        type: Array,
        default: () => [],
    },
    recentStudents: {
        type: Array,
        default: () => [],
    },
    growth: {
        type: Object,
        default: () => ({ labels: [], users: [], students: [] }),
    },
})

defineOptions({
    layout: AdminLayout,
})

const summaryStats = computed(() => ({
    totalSiswa: props.stats?.students?.total ?? 0,
    totalBerita: props.stats?.news?.total ?? 0,
    koleksiBuku: props.stats?.books?.total ?? 0,
    totalUsers: props.stats?.users?.total ?? 0,
    kategoriBuku: props.stats?.books?.categories ?? 0,
}))

const moduleLinks = computed(() => ([
    {
        id: 'students',
        icon: '👥',
        title: 'Kelola Siswa',
        description: 'Kelola data siswa, kelas, dan status keaktifan',
        meta: `${summaryStats.value.totalSiswa} siswa aktif`,
        href: route('admin.students.index'),
    },
    {
        id: 'users',
        icon: '🧑',
        title: 'Kelola Pengguna',
        description: 'Kelola akun admin, guru, dan orang tua',
        meta: `${summaryStats.value.totalUsers} pengguna`,
        href: route('admin.users.index'),
    },
    {
        id: 'news',
        icon: '📰',
        title: 'Kelola Berita & Dokumentasi',
        description: 'Kelola konten berita dan galeri kegiatan',
        meta: `${summaryStats.value.totalBerita} artikel`,
        href: route('admin.berita.index'),
    },
    {
        id: 'library',
        icon: '📚',
        title: 'Kelola Perpustakaan',
        description: 'Kelola koleksi buku dan materi pembelajaran',
        meta: `${summaryStats.value.koleksiBuku} buku`,
        href: route('admin.books.index'),
    },
]))

const fallbackActivities = [
    { id: 'fallback-1', icon: '👤', deskripsi: '5 siswa baru mendaftar hari ini', waktu: '2 jam yang lalu' },
    { id: 'fallback-2', icon: '📰', deskripsi: 'Artikel "Workshop Robotika" telah dipublish', waktu: '4 jam yang lalu' },
    { id: 'fallback-3', icon: '🧑‍🏫', deskripsi: '2 guru baru bergabung', waktu: '1 hari yang lalu' },
    { id: 'fallback-4', icon: '📚', deskripsi: '15 buku baru ditambahkan', waktu: '3 hari yang lalu' },
]

const daftarAktivitas = computed(() => {
    const activities = []

    props.recentStudents.forEach((student) => {
        activities.push({
            id: `student-${student.id}`,
            icon: '👤',
            deskripsi: `${student.name} bergabung sebagai siswa baru`,
            waktu: student.joined_at || 'Baru saja',
        })
    })

    props.recentNews.forEach((news) => {
        activities.push({
            id: `news-${news.id}`,
            icon: '📰',
            deskripsi: `Artikel "${news.title}" ${news.isPublished ? 'diterbitkan' : 'disimpan sebagai draf'}`,
            waktu: news.activity_at || news.event_date,
        })
    })

    return activities.length ? activities.slice(0, 6) : fallbackActivities
})
</script>

<template>
    <div class="space-y-8">
        <Head title="Dashboard Admin" />

        <section>
            <h1 class="mb-2 text-4xl font-bold text-emerald-600">Dashboard Admin</h1>
            <p class="text-sm text-slate-500">
                Selamat datang di panel administrasi Terminal Pintar. Kelola semua aspek sistem pembelajaran dengan mudah dan efisien.
            </p>
        </section>

        <section class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="rounded-3xl bg-white p-6 shadow-md">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total Siswa</p>
                        <p class="mt-1 text-5xl font-bold text-emerald-600">{{ summaryStats.totalSiswa }}</p>
                        <p class="mt-2 text-xs text-slate-400">+{{ summaryStats.totalSiswa ? 25 : 0 }} bulan ini</p>
                    </div>
                    <div class="text-4xl">👥</div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-md">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Artikel Terbit</p>
                        <p class="mt-1 text-5xl font-bold text-emerald-600">{{ summaryStats.totalBerita }}</p>
                        <p class="mt-2 text-xs text-slate-400">+{{ summaryStats.totalBerita ? 5 : 0 }} minggu ini</p>
                    </div>
                    <div class="text-4xl">📄</div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-md">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Koleksi Buku</p>
                        <p class="mt-1 text-5xl font-bold text-emerald-600">{{ summaryStats.koleksiBuku }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ summaryStats.kategoriBuku }} kategori</p>
                    </div>
                    <div class="text-4xl">📚</div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div v-for="module in moduleLinks" :key="module.id" class="rounded-3xl bg-white p-6 shadow-md transition hover:shadow-lg">
                <div class="mb-4 flex items-start gap-4">
                    <div class="text-4xl">{{ module.icon }}</div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ module.title }}</h3>
                        <p class="mt-1 text-xs text-slate-600">{{ module.description }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ module.meta }}</p>
                    </div>
                </div>
                <Link :href="module.href"
                    class="block rounded-2xl bg-emerald-600 py-2 text-center text-sm font-semibold text-white transition hover:bg-emerald-700">
                Akses Modul
                </Link>
            </div>
        </section>

        <section class="rounded-3xl bg-white p-6 shadow-md">
            <div class="mb-6 flex items-center gap-3">
                <div class="text-2xl">📊</div>
                <h2 class="text-xl font-bold text-slate-800">Aktivitas Terbaru</h2>
            </div>
            <div class="space-y-4">
                <div v-for="aktivitas in daftarAktivitas" :key="aktivitas.id" class="flex items-start gap-3 border-b pb-4 last:border-b-0">
                    <div class="text-2xl">{{ aktivitas.icon }}</div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-800">{{ aktivitas.deskripsi }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ aktivitas.waktu }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
