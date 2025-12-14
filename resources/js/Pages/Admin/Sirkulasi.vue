<template>
  <div class="space-y-8">
    <Head title="Manajemen Sirkulasi - Admin" />

    <!-- Page Header -->
    <PageHeader badgeLabel="Circulation Desk" badgeColor="cyan">
        <template #title>
            <p class="text-2xl font-bold text-slate-700 mt-0.5" style="font-family: 'Poppins', sans-serif;">
                Selamat datang kembali, <span class="text-[var(--color-primary)]">{{ user?.name }}</span>!
            </p>
        </template>
    </PageHeader>

    <!-- Back Link -->
    <div class="-mt-4">
      <Link
        :href="route('admin.books.index')"
        class="group inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-cyan-600 transition-colors"
      >
        <div class="rounded-full bg-slate-100 p-1 group-hover:bg-cyan-100 transition-colors">
          <ArrowLeftIcon class="h-4 w-4" />
        </div>
        <span>Kembali ke Katalog</span>
      </Link>
    </div>

    <!-- Stats Cards -->
    <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
      <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-cyan-50 opacity-50 blur-xl group-hover:bg-cyan-100 transition-colors"></div>
        <div class="relative">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Buku Tersedia</p>
            <div class="h-10 w-10 rounded-full bg-cyan-100 flex items-center justify-center">
              <CheckCircleIcon class="h-5 w-5 text-cyan-600" />
            </div>
          </div>
          <p class="text-4xl font-extrabold text-slate-800">{{ stats.available ?? bukuTersedia.length }}</p>
          <p class="text-sm text-cyan-600 font-medium mt-1">Siap dipinjam</p>
        </div>
      </div>
      <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-orange-50 opacity-50 blur-xl group-hover:bg-orange-100 transition-colors"></div>
        <div class="relative">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sedang Dipinjam</p>
            <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
              <ClockIcon class="h-5 w-5 text-orange-600" />
            </div>
          </div>
          <p class="text-4xl font-extrabold text-slate-800">{{ stats.borrowed ?? bukuDipinjam.length }}</p>
          <p class="text-sm text-orange-600 font-medium mt-1">Belum kembali</p>
        </div>
      </div>
      <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-blue-50 opacity-50 blur-xl group-hover:bg-blue-100 transition-colors"></div>
        <div class="relative">
          <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Peminjam</p>
            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
              <UserGroupIcon class="h-5 w-5 text-blue-600" />
            </div>
          </div>
          <p class="text-4xl font-extrabold text-slate-800">{{ stats.totalUsers ?? users.length }}</p>
          <p class="text-sm text-blue-600 font-medium mt-1">Siswa & Guru</p>
        </div>
      </div>
    </section>

    <!-- Main Content Grid -->
    <section class="grid gap-6 lg:grid-cols-2">
      <!-- Card Peminjaman (Available Books) -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="bg-cyan-50 px-6 py-4 border-b border-cyan-100 flex flex-wrap justify-between items-center gap-3">
          <div>
            <h2 class="text-lg font-bold text-cyan-900 flex items-center gap-2">
              <ArrowUpTrayIcon class="h-5 w-5" />
              Buku Tersedia
            </h2>
            <p class="text-sm text-cyan-700 mt-0.5">Buku populer yang siap dipinjam</p>
          </div>
          <button
            type="button"
            @click="openPinjamModal"
            :disabled="!hasAvailableBooks"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-green-600 text-white px-4 py-1.5 text-sm font-bold shadow-sm transition-all hover:bg-green-700 hover:-translate-y-0.5 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <ArrowUpTrayIcon class="h-4 w-4" />
            <span class="hidden sm:inline">Catat Peminjaman</span>
          </button>
        </div>
        
        <div v-if="topBukuTersedia.length" class="p-4 space-y-2">
          <div v-for="buku in topBukuTersedia" :key="buku.id" 
            class="group flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-3 transition-all hover:bg-white hover:border-cyan-200 hover:shadow-sm">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-slate-800 group-hover:text-cyan-700 transition-colors truncate">{{ buku.judul }}</p>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-xs font-mono bg-white border border-slate-200 rounded px-1.5 py-0.5 text-slate-500">{{ buku.code }}</span>
                <span class="text-xs text-slate-500 truncate">{{ buku.kategori || 'Umum' }}</span>
              </div>
            </div>
            <div class="h-8 w-8 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-600 opacity-0 group-hover:opacity-100 transition-opacity shrink-0 ml-2">
              <PlusIcon class="h-4 w-4" />
            </div>
          </div>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-12 px-4 text-center">
          <ExclamationTriangleIcon class="h-12 w-12 text-amber-400 mb-3" />
          <p class="text-sm font-bold text-amber-700">Stok Kosong</p>
          <p class="text-xs text-amber-600 mt-1">Tidak ada buku tersedia saat ini.</p>
        </div>
      </div>

      <!-- Card Pengembalian (Borrowed Books) -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="bg-orange-50 px-6 py-4 border-b border-orange-100 flex flex-wrap justify-between items-center gap-3">
          <div>
            <h2 class="text-lg font-bold text-orange-900 flex items-center gap-2">
              <ArrowDownTrayIcon class="h-5 w-5" />
              Sedang Dipinjam
            </h2>
            <p class="text-sm text-orange-700 mt-0.5">Daftar buku yang sedang berada di luar</p>
          </div>
          <button
            type="button"
            @click="openKembaliModal"
            :disabled="!hasBorrowedBooks"
            class="inline-flex items-center justify-center gap-2 rounded-full bg-white text-cyan-700 border border-cyan-600 px-4 py-1.5 text-sm font-bold shadow-sm transition-all hover:bg-cyan-50 hover:-translate-y-0.5 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <ArrowDownTrayIcon class="h-4 w-4" />
            <span class="hidden sm:inline">Catat Pengembalian</span>
          </button>
        </div>

        <div v-if="topBukuDipinjam.length" class="p-4 space-y-2">
          <div v-for="buku in topBukuDipinjam" :key="buku.loan_id"
            class="group flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-3 transition-all hover:bg-white hover:border-orange-200 hover:shadow-sm">
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-slate-800 group-hover:text-orange-700 transition-colors truncate">{{ buku.judul }}</p>
              <div class="flex items-center gap-2 mt-1">
                <UserIcon class="h-3 w-3 text-slate-400 shrink-0" />
                <span class="text-xs text-slate-500 font-medium truncate">{{ getBorrowerName(buku) }}</span>
              </div>
            </div>
            <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 opacity-0 group-hover:opacity-100 transition-opacity shrink-0 ml-2">
              <CheckCircleIcon class="h-4 w-4" />
            </div>
          </div>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-12 px-4 text-center">
          <CheckCircleIcon class="h-12 w-12 text-emerald-400 mb-3" />
          <p class="text-sm font-bold text-emerald-700">Semua Tertib</p>
          <p class="text-xs text-emerald-600 mt-1">Tidak ada buku yang dipinjam saat ini.</p>
        </div>
      </div>
    </section>

    <!-- Modal Peminjaman -->
    <Modal
      :show="showPinjamModal"
      title="Catat Peminjaman"
      variant="success"
      max-width="2xl"
      @close="closePinjamModal"
    >
      <template #description>
        Pilih buku dan peminjam untuk mencatat transaksi baru.
      </template>

      <form @submit.prevent="submitPeminjaman" class="space-y-6">
        <div class="space-y-5">
           <!-- Pilih Buku -->
          <div>
            <label for="buku_pinjam" class="mb-2 block text-sm font-bold text-slate-700">
              Pilih Buku <span class="text-rose-500">*</span>
            </label>
            <div class="relative" ref="bukuPinjamWrapper">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </div>
              <input
                id="buku_pinjam"
                v-model="searchBukuPinjam"
                type="text"
                placeholder="Cari judul buku..."
                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-slate-50"
                :class="{ 'border-rose-400 ring-4 ring-rose-50': formPinjam.errors.buku_id }"
                @focus="showBukuPinjamDropdown = true"
                autocomplete="off"
              />
              <div
                v-if="showBukuPinjamDropdown && filteredBukuTersedia.length > 0"
                class="absolute z-10 mt-2 max-h-60 w-full overflow-y-auto rounded-2xl border border-slate-100 bg-white shadow-xl"
              >
                <button
                  v-for="buku in filteredBukuTersedia"
                  :key="buku.id"
                  type="button"
                  @click="selectBukuPinjam(buku)"
                  class="group flex w-full flex-col px-4 py-3 text-left transition hover:bg-emerald-50 border-b border-slate-50 last:border-0"
                >
                  <span class="text-sm font-bold text-slate-800 group-hover:text-emerald-700">{{ buku.judul }}</span>
                  <span class="text-xs text-slate-500">{{ buku.kategori || 'Tanpa Kategori' }}</span>
                </button>
              </div>
              <div
                v-if="showBukuPinjamDropdown && searchBukuPinjam && filteredBukuTersedia.length === 0"
                class="absolute z-10 mt-2 w-full rounded-2xl border border-slate-100 bg-white p-4 text-center text-sm font-medium text-slate-500 shadow-xl"
              >
                Tidak ada buku ditemukan.
              </div>
            </div>
            <p v-if="formPinjam.errors.buku_id" class="mt-1 text-xs font-bold text-rose-500">{{ formPinjam.errors.buku_id }}</p>
          </div>

           <!-- Pilih Peminjam -->
          <div>
            <label for="peminjam" class="mb-2 block text-sm font-bold text-slate-700">
              Data Peminjam <span class="text-rose-500">*</span>
            </label>
            <div class="relative" ref="peminjamWrapper">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <UserIcon class="h-5 w-5" />
                </div>
              <input
                id="peminjam"
                v-model="searchPeminjam"
                type="text"
                placeholder="Cari atau ketik nama peminjam..."
                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-slate-50"
                :class="{ 'border-rose-400 ring-4 ring-rose-50': formPinjam.errors.peminjam_id || formPinjam.errors.peminjam_nama }"
                @focus="showPeminjamDropdown = true"
                autocomplete="off"
              />
              <div
                v-if="showPeminjamDropdown && filteredUsers.length > 0"
                class="absolute z-10 mt-2 max-h-60 w-full overflow-y-auto rounded-2xl border border-slate-100 bg-white shadow-xl"
              >
                <button
                  v-for="usr in filteredUsers"
                  :key="usr.id"
                  type="button"
                  @click="selectPeminjam(usr)"
                  class="group flex w-full flex-col px-4 py-3 text-left transition hover:bg-emerald-50 border-b border-slate-50 last:border-0"
                >
                  <span class="text-sm font-bold text-slate-800 group-hover:text-emerald-700">{{ usr.nama }}</span>
                  <span class="text-xs text-slate-500">{{ usr.email }}</span>
                </button>
              </div>
            </div>
             <p class="mt-1.5 text-xs text-slate-400">Pilih dari daftar atau ketik nama baru untuk tamu.</p>
            <p v-if="formPinjam.errors.peminjam_id || formPinjam.errors.peminjam_nama" class="mt-1 text-xs font-bold text-rose-500">
              {{ formPinjam.errors.peminjam_id || formPinjam.errors.peminjam_nama }}
            </p>
          </div>

          <div v-if="!formPinjam.peminjam_id" class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
            <label for="peminjam_email" class="mb-2 block text-sm font-bold text-slate-700">
              Email Peminjam (Opsional)
            </label>
            <input
              id="peminjam_email"
              v-model="formPinjam.peminjam_email"
              type="email"
              placeholder="email@peminjam.com"
              class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
              :class="{ 'border-rose-400': formPinjam.errors.peminjam_email }"
              autocomplete="off"
            />
          </div>

          <div>
            <label for="tanggal_pinjam" class="mb-2 block text-sm font-bold text-slate-700">
              Tanggal Pinjam <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                 <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <CalendarIcon class="h-5 w-5" />
                </div>
                <input
                id="tanggal_pinjam"
                v-model="formPinjam.tanggal_pinjam"
                type="date"
                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-slate-50"
                :class="{ 'border-rose-400': formPinjam.errors.tanggal_pinjam }"
                />
            </div>
             <p v-if="formPinjam.errors.tanggal_pinjam" class="mt-1 text-xs font-bold text-rose-500">{{ formPinjam.errors.tanggal_pinjam }}</p>
          </div>
        </div>

        <div v-if="formPinjam.errors.error" class="rounded-xl border border-rose-200 bg-rose-50 p-4 flex items-center gap-2 text-sm text-rose-600 font-bold">
            <ExclamationTriangleIcon class="h-5 w-5 shrink-0" />
            {{ formPinjam.errors.error }}
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 active:scale-95"
            @click="closePinjamModal"
            :disabled="formPinjam.processing"
          >
            Batal
          </button>
          <button
            type="submit"
            class="rounded-xl bg-emerald-500 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-200 transition hover:bg-emerald-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="formPinjam.processing || !hasAvailableBooks"
          >
            <span v-if="formPinjam.processing">Menyimpan...</span>
            <span v-else>Simpan Peminjaman</span>
          </button>
        </div>
      </form>
    </Modal>

    <!-- Modal Pengembalian -->
    <Modal
      :show="showKembaliModal"
      title="Catat Pengembalian"
      variant="info"
      max-width="2xl"
      @close="closeKembaliModal"
    >
      <template #description>
        Cari buku yang ingin dikembalikan dari daftar peminjaman.
      </template>

      <form @submit.prevent="submitPengembalian" class="space-y-6">
        <div class="space-y-5">
          <div>
            <label for="buku_kembali" class="mb-2 block text-sm font-bold text-slate-700">
              Cari Buku / Peminjam <span class="text-rose-500">*</span>
            </label>
            <div class="relative" ref="bukuKembaliWrapper">
                 <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </div>
              <input
                id="buku_kembali"
                v-model="searchBuku"
                type="text"
                placeholder="Ketikan judul buku atau nama peminjam..."
                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all bg-slate-50"
                :class="{ 'border-rose-400 ring-4 ring-rose-50': formKembali.errors.peminjaman_id }"
                @focus="showBukuDropdown = true"
                autocomplete="off"
              />
              <div
                v-if="showBukuDropdown && filteredBuku.length > 0"
                class="absolute z-10 mt-2 max-h-60 w-full overflow-y-auto rounded-2xl border border-slate-100 bg-white shadow-xl"
              >
                <button
                  v-for="buku in filteredBuku"
                  :key="buku.loan_id"
                  type="button"
                  @click="selectBuku(buku)"
                  class="group flex w-full flex-col px-4 py-3 text-left transition hover:bg-sky-50 border-b border-slate-50 last:border-0"
                >
                  <span class="text-sm font-bold text-slate-800 group-hover:text-sky-700">{{ buku.judul }}</span>
                  <div class="flex items-center gap-1.5 mt-0.5">
                      <UserIcon class="h-3 w-3 text-slate-400" />
                      <span class="text-xs text-slate-500">Peminjam: {{ getBorrowerName(buku) }}</span>
                  </div>
                </button>
              </div>
              <div
                v-if="showBukuDropdown && searchBuku && filteredBuku.length === 0"
                class="absolute z-10 mt-2 w-full rounded-2xl border border-slate-100 bg-white p-4 text-center text-sm font-medium text-slate-500 shadow-xl"
              >
                Tidak ditemukan data peminjaman yang cocok.
              </div>
            </div>
            <p v-if="formKembali.errors.peminjaman_id" class="mt-1 text-xs font-bold text-rose-500">{{ formKembali.errors.peminjaman_id }}</p>
          </div>

          <div>
            <label for="tanggal_kembali" class="mb-2 block text-sm font-bold text-slate-700">
              Tanggal Kembali <span class="text-rose-500">*</span>
            </label>
             <div class="relative">
                 <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <CalendarIcon class="h-5 w-5" />
                </div>
                <input
                id="tanggal_kembali"
                v-model="formKembali.tanggal_kembali"
                type="date"
                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all bg-slate-50"
                :class="{ 'border-rose-400': formKembali.errors.tanggal_kembali }"
                />
            </div>
             <p v-if="formKembali.errors.tanggal_kembali" class="mt-1 text-x font-bold text-rose-500">{{ formKembali.errors.tanggal_kembali }}</p>
          </div>
        </div>

        <div v-if="formKembali.errors.error" class="rounded-xl border border-rose-200 bg-rose-50 p-4 flex items-center gap-2 text-sm text-rose-600 font-bold">
            <ExclamationTriangleIcon class="h-5 w-5 shrink-0" />
            {{ formKembali.errors.error }}
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 active:scale-95"
            @click="closeKembaliModal"
            :disabled="formKembali.processing"
          >
            Batal
          </button>
          <button
            type="submit"
            class="rounded-xl bg-sky-500 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-sky-200 transition hover:bg-sky-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="formKembali.processing || !hasBorrowedBooks"
          >
            <span v-if="formKembali.processing">Memproses...</span>
            <span v-else>Simpan Pengembalian</span>
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Modal from '@/Components/Modal.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import {
    ArrowLeftIcon,
    ArrowUpTrayIcon,
    ArrowDownTrayIcon,
    CheckCircleIcon,
    ClockIcon,
    UserGroupIcon,
    PlusIcon,
    ExclamationTriangleIcon,
    UserIcon,
    MagnifyingGlassIcon,
    CalendarIcon
} from '@heroicons/vue/24/outline'

defineOptions({
  layout: AdminLayout,
})

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)

const props = defineProps({
  bukuTersedia: {
    type: Array,
    default: () => [],
  },
  bukuDipinjam: {
    type: Array,
    default: () => [],
  },
  users: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
})

const getToday = () => new Date().toISOString().split('T')[0]

const showPinjamModal = ref(false)
const showKembaliModal = ref(false)

const searchBukuPinjam = ref('')
const showBukuPinjamDropdown = ref(false)

const searchPeminjam = ref('')
const showPeminjamDropdown = ref(false)

const searchBuku = ref('')
const showBukuDropdown = ref(false)

const bukuPinjamWrapper = ref(null)
const peminjamWrapper = ref(null)
const bukuKembaliWrapper = ref(null)
const selectedPeminjamName = ref('')

const hasAvailableBooks = computed(() => (props.bukuTersedia || []).length > 0)
const hasBorrowedBooks = computed(() => (props.bukuDipinjam || []).length > 0)
const topBukuTersedia = computed(() => (props.bukuTersedia || []).slice(0, 5))
const topBukuDipinjam = computed(() => (props.bukuDipinjam || []).slice(0, 5))

const filteredBukuTersedia = computed(() => {
  if (!searchBukuPinjam.value) {
    return props.bukuTersedia || []
  }
  const search = searchBukuPinjam.value.toLowerCase()
  return (props.bukuTersedia || []).filter(
    (buku) =>
      buku.judul?.toLowerCase().includes(search) || (buku.kategori || '').toLowerCase().includes(search),
  )
})

const filteredUsers = computed(() => {
  if (!searchPeminjam.value) {
    return props.users || []
  }
  const search = searchPeminjam.value.toLowerCase()
  return (props.users || []).filter(
    (user) => user.nama?.toLowerCase().includes(search) || user.email?.toLowerCase().includes(search),
  )
})

const filteredBuku = computed(() => {
  if (!searchBuku.value) {
    return props.bukuDipinjam || []
  }
  const search = searchBuku.value.toLowerCase()
  return (props.bukuDipinjam || []).filter((buku) => {
    const borrower = getBorrowerName(buku) || ''
    return buku.judul?.toLowerCase().includes(search) || borrower.toLowerCase().includes(search)
  })
})

const formPinjam = useForm({
  buku_id: '',
  peminjam_id: '',
  peminjam_nama: '',
  peminjam_email: '',
  tanggal_pinjam: getToday(),
})

const formKembali = useForm({
  peminjaman_id: '',
  tanggal_kembali: getToday(),
})

const resetPinjamForm = () => {
  formPinjam.reset()
  formPinjam.clearErrors()
  formPinjam.tanggal_pinjam = getToday()
  formPinjam.peminjam_nama = ''
  formPinjam.peminjam_email = ''
  searchBukuPinjam.value = ''
  searchPeminjam.value = ''
  showBukuPinjamDropdown.value = false
  showPeminjamDropdown.value = false
  selectedPeminjamName.value = ''
}

const resetPengembalianForm = () => {
  formKembali.reset()
  formKembali.clearErrors()
  formKembali.tanggal_kembali = getToday()
  searchBuku.value = ''
  showBukuDropdown.value = false
}

const openPinjamModal = () => {
  resetPinjamForm()
  showPinjamModal.value = true
}

const closePinjamModal = () => {
  showPinjamModal.value = false
  resetPinjamForm()
}

const openKembaliModal = () => {
  resetPengembalianForm()
  showKembaliModal.value = true
}

const closeKembaliModal = () => {
  showKembaliModal.value = false
  resetPengembalianForm()
}

const selectBukuPinjam = (buku) => {
  formPinjam.buku_id = buku.id
  searchBukuPinjam.value = buku.judul
  showBukuPinjamDropdown.value = false
}

const selectPeminjam = (user) => {
  formPinjam.peminjam_id = user.id
  formPinjam.peminjam_nama = user.nama
  formPinjam.peminjam_email = user.email || ''
  selectedPeminjamName.value = user.nama
  searchPeminjam.value = user.nama
  showPeminjamDropdown.value = false
}

const selectBuku = (buku) => {
  const activeLoan = buku.peminjaman?.[0]
  formKembali.peminjaman_id = activeLoan?.id || ''
  searchBuku.value = buku.judul
  showBukuDropdown.value = false
}

const getBorrowerName = (buku) =>
  buku.peminjaman?.[0]?.display_name || buku.peminjaman?.[0]?.peminjam?.nama || 'Tidak diketahui'

watch(searchPeminjam, (value) => {
  formPinjam.peminjam_nama = value

  if (!value) {
    if (!formPinjam.peminjam_id) {
      formPinjam.peminjam_email = ''
    }
    if (selectedPeminjamName.value) {
      formPinjam.peminjam_id = ''
      selectedPeminjamName.value = ''
    }
    return
  }

  if (selectedPeminjamName.value && value === selectedPeminjamName.value) {
    return
  }

  if (formPinjam.peminjam_id) {
    formPinjam.peminjam_id = ''
    formPinjam.peminjam_email = ''
    selectedPeminjamName.value = ''
  }
})

const handleClickOutside = (event) => {
  if (bukuPinjamWrapper.value && !bukuPinjamWrapper.value.contains(event.target)) {
    showBukuPinjamDropdown.value = false
  }

  if (peminjamWrapper.value && !peminjamWrapper.value.contains(event.target)) {
    showPeminjamDropdown.value = false
  }

  if (bukuKembaliWrapper.value && !bukuKembaliWrapper.value.contains(event.target)) {
    showBukuDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

const submitPeminjaman = () => {
  formPinjam.post(route('admin.perpustakaan.pinjam'), {
    preserveScroll: true,
    onSuccess: closePinjamModal,
  })
}

const submitPengembalian = () => {
  formKembali.post(route('admin.perpustakaan.kembalikan'), {
    preserveScroll: true,
    onSuccess: closeKembaliModal,
  })
}
</script>
