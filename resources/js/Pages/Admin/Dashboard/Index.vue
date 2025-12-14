<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    UserGroupIcon, 
    NewspaperIcon, 
    BookOpenIcon, 
    UsersIcon, 
    ArrowRightIcon, 
    ClockIcon,
    ChartBarIcon
} from '@heroicons/vue/24/outline'

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
        icon: UserGroupIcon,
        title: 'Kelola Siswa',
        description: 'Kelola data siswa, kelas, dan status keaktifan.',
        meta: `${summaryStats.value.totalSiswa} Siswa Aktif`,
        href: route('admin.students.index'),
        color: 'text-blue-600',
        bg: 'bg-blue-50',
    },
    {
        id: 'users',
        icon: UsersIcon,
        title: 'Kelola Pengguna',
        description: 'Kelola akun admin, guru, dan orang tua.',
        meta: `${summaryStats.value.totalUsers} Pengguna`,
        href: route('admin.users.index'),
        color: 'text-purple-600',
        bg: 'bg-purple-50',
    },
    {
        id: 'news',
        icon: NewspaperIcon,
        title: 'Berita & Dokumentasi',
        description: 'Kelola konten berita dan galeri kegiatan sekolah.',
        meta: `${summaryStats.value.totalBerita} Artikel`,
        href: route('admin.berita.index'),
        color: 'text-orange-600',
        bg: 'bg-orange-50',
    },
    {
        id: 'library',
        icon: BookOpenIcon,
        title: 'Perpustakaan',
        description: 'Kelola koleksi buku dan materi pembelajaran.',
        meta: `${summaryStats.value.koleksiBuku} Buku`,
        href: route('admin.books.index'),
        color: 'text-emerald-600',
        bg: 'bg-emerald-50',
    },
]))

const fallbackActivities = [
    { id: 'fallback-1', icon: UserGroupIcon, deskripsi: '5 siswa baru mendaftar hari ini', waktu: '2 jam yang lalu', color: 'text-blue-500', bg: 'bg-blue-100' },
    { id: 'fallback-2', icon: NewspaperIcon, deskripsi: 'Artikel "Workshop Robotika" telah dipublish', waktu: '4 jam yang lalu', color: 'text-orange-500', bg: 'bg-orange-100' },
    { id: 'fallback-3', icon: UsersIcon, deskripsi: '2 guru baru bergabung', waktu: '1 hari yang lalu', color: 'text-purple-500', bg: 'bg-purple-100' },
    { id: 'fallback-4', icon: BookOpenIcon, deskripsi: '15 buku baru ditambahkan', waktu: '3 hari yang lalu', color: 'text-emerald-500', bg: 'bg-emerald-100' },
]

const daftarAktivitas = computed(() => {
    const activities = []

    props.recentStudents.forEach((student) => {
        activities.push({
            id: `student-${student.id}`,
            icon: UserGroupIcon,
            deskripsi: `${student.name} bergabung sebagai siswa baru`,
            waktu: student.joined_at || 'Baru saja',
            color: 'text-blue-600',
            bg: 'bg-blue-100',
        })
    })

    props.recentNews.forEach((news) => {
        activities.push({
            id: `news-${news.id}`,
            icon: NewspaperIcon,
            deskripsi: `Artikel "${news.title}" ${news.isPublished ? 'diterbitkan' : 'disimpan sebagai draf'}`,
            waktu: news.activity_at || news.event_date,
            color: 'text-orange-600',
            bg: 'bg-orange-100',
        })
    })

    return activities.length ? activities.slice(0, 6) : fallbackActivities
})
</script>

<template>
    <div class="space-y-10">
        <Head title="Dashboard Admin" />

        <!-- Header Section (Upgraded) -->
        <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 relative">
            <div>
                 <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-600 mb-2 border border-orange-100">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                    </span>
                    <span>Admin Portal</span>
                </div>
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                    Dashboard <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-primary)] to-emerald-600">Overview</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium text-lg">Selamat datang kembali, Administrator Terminal Pintar.</p>
            </div>
            
            <div class="flex items-center gap-3 p-1.5 bg-white rounded-2xl border border-slate-200 shadow-sm">
                 <div class="p-2.5 bg-slate-50 rounded-xl text-[var(--color-primary)]">
                    <ClockIcon class="h-6 w-6" />
                 </div>
                 <div class="pr-3">
                     <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hari ini</p>
                     <p class="text-sm font-bold text-slate-700">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                 </div>
            </div>
        </section>

        <!-- Stats Grid (Modern Cards with Thicker Border) -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Card 1 -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-8 shadow-sm border border-slate-200 transition-all hover:shadow-lg hover:-translate-y-1 hover:border-[var(--color-primary-light)]">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-[var(--color-primary-light)] opacity-20 blur-2xl group-hover:opacity-40 transition-opacity"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-slate-400">Total Siswa</p>
                        <h3 class="mt-2 text-4xl font-extrabold text-slate-800">{{ summaryStats.totalSiswa }}</h3>
                        <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                            <span>+{{ summaryStats.totalSiswa ? 12 : 0 }}%</span>
                            <span class="font-medium text-emerald-600/70">bulan ini</span>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-[var(--color-primary-light)] p-3 text-[var(--color-primary)] ring-4 ring-slate-50">
                        <UserGroupIcon class="h-8 w-8" />
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-8 shadow-sm border border-slate-200 transition-all hover:shadow-lg hover:-translate-y-1 hover:border-orange-200">
                 <div class="absolute top-0 right-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-orange-100 opacity-20 blur-2xl group-hover:opacity-40 transition-opacity"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-slate-400">Artikel & Berita</p>
                        <h3 class="mt-2 text-4xl font-extrabold text-slate-800">{{ summaryStats.totalBerita }}</h3>
                         <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-bold text-orange-700">
                            <span>+{{ summaryStats.totalBerita ? 3 : 0 }}</span>
                            <span class="font-medium text-orange-600/70">minggu ini</span>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-orange-100 p-3 text-orange-600 ring-4 ring-slate-50">
                        <NewspaperIcon class="h-8 w-8" />
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white p-8 shadow-sm border border-slate-200 transition-all hover:shadow-lg hover:-translate-y-1 hover:border-purple-200">
                 <div class="absolute top-0 right-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-purple-100 opacity-20 blur-2xl group-hover:opacity-40 transition-opacity"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-slate-400">Total Koleksi</p>
                        <h3 class="mt-2 text-4xl font-extrabold text-slate-800">{{ summaryStats.koleksiBuku }}</h3>
                         <div class="mt-3 inline-flex items-center gap-1 rounded-full bg-slate-50 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                            <span>{{ summaryStats.kategoriBuku }}</span>
                            <span class="font-medium text-slate-500">Kategori Buku</span>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-purple-100 p-3 text-purple-600 ring-4 ring-slate-50">
                        <BookOpenIcon class="h-8 w-8" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Modules Grid -->
        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="module in moduleLinks" :key="module.id" 
                class="group flex flex-col justify-between rounded-[2.5rem] bg-white p-8 shadow-sm border border-slate-200 transition-all hover:shadow-xl hover:-translate-y-2 cursor-pointer relative overflow-hidden hover:border-[var(--color-primary)]/30">
                
                <div class="absolute top-0 right-0 -mr-8 -mt-8 h-40 w-40 rounded-full opacity-5 bg-current transition-transform group-hover:scale-150" :class="module.color"></div>

                <div>
                    <div :class="[module.bg, module.color]" class="inline-flex rounded-2xl p-4 shadow-sm ring-4 ring-slate-50 transition-all group-hover:ring-[var(--color-primary)]/20">
                        <component :is="module.icon" class="h-8 w-8" />
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-slate-800">{{ module.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 font-medium">{{ module.description }}</p>
                </div>

                <div class="mt-8">
                     <p class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ module.meta }}</p>
                    <Link :href="module.href"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-50 py-3.5 text-sm font-bold text-slate-700 transition-all group-hover:bg-[var(--color-primary)] group-hover:text-white group-hover:shadow-lg">
                        <span>Akses Modul</span>
                        <ArrowRightIcon class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Activity Feed -->
        <section class="rounded-[2.5rem] bg-white p-10 shadow-sm border border-slate-200">
            <div class="mb-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-slate-100 p-2.5 text-slate-600">
                        <ChartBarIcon class="h-6 w-6" />
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">Aktivitas Terbaru</h2>
                </div>
                <button class="text-sm font-bold text-[var(--color-primary)] hover:underline decoration-2 underline-offset-4">Lihat Semua</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                <div v-for="(aktivitas, index) in daftarAktivitas" :key="aktivitas.id" 
                    class="flex items-start gap-4 p-4 rounded-2xl transition hover:bg-slate-50 border border-transparent hover:border-slate-100"
                    :class="index < 2 ? 'bg-slate-50/50' : ''">
                    <div :class="[aktivitas.bg, aktivitas.color]" class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full shadow-sm ring-2 ring-white">
                        <component :is="aktivitas.icon" class="h-5 w-5" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-800 leading-snug">{{ aktivitas.deskripsi }}</p>
                        <div class="mt-1.5 flex items-center gap-2 text-xs text-slate-400 font-medium">
                            <ClockIcon class="h-3.5 w-3.5" />
                            <span>{{ aktivitas.waktu }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
