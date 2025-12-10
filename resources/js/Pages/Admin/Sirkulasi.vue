<template>
  <div class="space-y-8">
    <Head title="Sirkulasi Perpustakaan - Admin" />

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <Link
          :href="route('admin.books.index')"
          class="flex items-center gap-2 text-sm font-medium text-[#78AE4E] transition hover:text-green-700"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0 7-7m-7 7h18" />
          </svg>
          Kembali ke Daftar Buku
        </Link>
        <h1 class="mt-3 text-4xl font-bold text-[#78AE4E]">Sirkulasi Perpustakaan</h1>
        <p class="text-sm text-gray-600">Kelola peminjaman dan pengembalian buku</p>
      </div>
      <div class="flex flex-wrap gap-3">
        <button
          type="button"
          @click="openPinjamModal"
          :disabled="!hasAvailableBooks"
          class="inline-flex items-center gap-2 rounded-lg px-5 py-3 font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="
            hasAvailableBooks
              ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500'
              : 'cursor-not-allowed bg-gray-300 focus:ring-0'
          "
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Catat Peminjaman
        </button>
        <button
          type="button"
          @click="openKembaliModal"
          :disabled="!hasBorrowedBooks"
          class="inline-flex items-center gap-2 rounded-lg px-5 py-3 font-medium text-white transition focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="
            hasBorrowedBooks
              ? 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
              : 'cursor-not-allowed bg-gray-300 focus:ring-0'
          "
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20V4m8 8H4" />
          </svg>
          Catat Pengembalian
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <div class="rounded-lg bg-white p-6 shadow">
        <p class="text-sm text-gray-600">Buku Tersedia</p>
        <p class="mt-2 text-3xl font-bold text-green-600">{{ stats.available ?? bukuTersedia.length }}</p>
      </div>
      <div class="rounded-lg bg-white p-6 shadow">
        <p class="text-sm text-gray-600">Sedang Dipinjam</p>
        <p class="mt-2 text-3xl font-bold text-blue-600">{{ stats.borrowed ?? bukuDipinjam.length }}</p>
      </div>
      <div class="rounded-lg bg-white p-6 shadow">
        <p class="text-sm text-gray-600">Total Pengguna</p>
        <p class="mt-2 text-3xl font-bold text-gray-700">{{ stats.totalUsers ?? users.length }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <div class="rounded-lg bg-white p-6 shadow-md">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">Peminjaman Buku</h2>
            <p class="mt-1 text-sm text-gray-600">Catat peminjaman baru melalui modal yang tersedia.</p>
          </div>
          <div class="text-3xl">📤</div>
        </div>
        <p class="mt-4 text-sm text-gray-600">
          Gunakan tombol “Catat Peminjaman” untuk memilih buku dan peminjam. Berikut beberapa buku yang saat ini tersedia:
        </p>
        <ul v-if="topBukuTersedia.length" class="mt-4 space-y-2">
          <li
            v-for="buku in topBukuTersedia"
            :key="buku.id"
            class="rounded-lg border border-gray-100 px-4 py-3"
          >
            <p class="text-sm font-semibold text-gray-900">{{ buku.judul }}</p>
            <p class="text-xs text-gray-500">{{ buku.kategori || 'Tanpa kategori' }}</p>
          </li>
        </ul>
        <p v-else class="mt-4 text-sm text-yellow-600">
          Tidak ada buku yang tersedia untuk dipinjam saat ini.
        </p>
      </div>
      <div class="rounded-lg bg-white p-6 shadow-md">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">Pengembalian Buku</h2>
            <p class="mt-1 text-sm text-gray-600">Tindak lanjuti pengembalian buku melalui modal pengembalian.</p>
          </div>
          <div class="text-3xl">📥</div>
        </div>
        <p class="mt-4 text-sm text-gray-600">
          Gunakan tombol “Catat Pengembalian” untuk mencari buku yang sedang dipinjam. Berikut beberapa peminjaman aktif:
        </p>
        <ul v-if="topBukuDipinjam.length" class="mt-4 space-y-2">
          <li
            v-for="buku in topBukuDipinjam"
            :key="buku.loan_id"
            class="rounded-lg border border-gray-100 px-4 py-3"
          >
            <p class="text-sm font-semibold text-gray-900">{{ buku.judul }}</p>
            <p class="text-xs text-gray-500">Peminjam: {{ getBorrowerName(buku) }}</p>
          </li>
        </ul>
        <p v-else class="mt-4 text-sm text-green-600">
          Tidak ada buku yang perlu dikembalikan saat ini.
        </p>
      </div>
    </div>

    <Modal
      :show="showPinjamModal"
      title="Catat Peminjaman Buku"
      variant="success"
      max-width="3xl"
      @close="closePinjamModal"
    >
      <template #description>
        Pilih buku yang tersedia serta peminjam untuk mencatat transaksi peminjaman baru.
      </template>

      <form @submit.prevent="submitPeminjaman" class="space-y-5">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <div>
            <label for="buku_pinjam" class="mb-2 block text-sm font-medium text-gray-700">
              Pilih Buku <span class="text-red-500">*</span>
            </label>
            <div class="relative" ref="bukuPinjamWrapper">
              <input
                id="buku_pinjam"
                v-model="searchBukuPinjam"
                type="text"
                placeholder="Cari judul buku..."
                class="w-full rounded-lg border px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                :class="{ 'border-red-500': formPinjam.errors.buku_id }"
                @focus="showBukuPinjamDropdown = true"
                autocomplete="off"
              />
              <div
                v-if="showBukuPinjamDropdown && filteredBukuTersedia.length > 0"
                class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border bg-white shadow-lg"
              >
                <button
                  v-for="buku in filteredBukuTersedia"
                  :key="buku.id"
                  type="button"
                  @click="selectBukuPinjam(buku)"
                  class="w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                >
                  <div class="font-medium text-gray-900">{{ buku.judul }}</div>
                  <div class="text-sm text-gray-500">
                    {{ buku.kategori ? `Kategori: ${buku.kategori}` : 'Tanpa Kategori' }}
                  </div>
                </button>
              </div>
              <div
                v-if="showBukuPinjamDropdown && searchBukuPinjam && filteredBukuTersedia.length === 0"
                class="absolute z-10 mt-1 w-full rounded-lg border bg-white p-3 text-center text-sm text-gray-500"
              >
                Tidak ada hasil yang cocok
              </div>
            </div>
            <p v-if="formPinjam.errors.buku_id" class="mt-1 text-sm text-red-600">{{ formPinjam.errors.buku_id }}</p>
            <p v-if="!hasAvailableBooks" class="mt-1 text-sm text-yellow-600">
              Tidak ada buku yang tersedia untuk dipinjam.
            </p>
          </div>

          <div>
            <label for="peminjam" class="mb-2 block text-sm font-medium text-gray-700">
              Peminjam <span class="text-red-500">*</span>
            </label>
            <p class="-mt-1 mb-2 text-xs text-gray-500">Pilih dari daftar atau ketik nama baru jika peminjam belum terdaftar.</p>
            <div class="relative" ref="peminjamWrapper">
              <input
                id="peminjam"
                v-model="searchPeminjam"
                type="text"
                placeholder="Cari atau tulis nama peminjam..."
                class="w-full rounded-lg border px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
                :class="{ 'border-red-500': formPinjam.errors.peminjam_id || formPinjam.errors.peminjam_nama }"
                @focus="showPeminjamDropdown = true"
                autocomplete="off"
              />
              <div
                v-if="showPeminjamDropdown && filteredUsers.length > 0"
                class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border bg-white shadow-lg"
              >
                <button
                  v-for="usr in filteredUsers"
                  :key="usr.id"
                  type="button"
                  @click="selectPeminjam(usr)"
                  class="w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                >
                  <div class="font-medium text-gray-900">{{ usr.nama }}</div>
                  <div class="text-sm text-gray-500">{{ usr.email }}</div>
                </button>
              </div>
              <div
                v-if="showPeminjamDropdown && searchPeminjam && filteredUsers.length === 0"
                class="absolute z-10 mt-1 w-full rounded-lg border bg-white p-3 text-center text-sm text-gray-500"
              >
                Tidak ada hasil yang cocok
              </div>
            </div>
            <p v-if="formPinjam.errors.peminjam_id || formPinjam.errors.peminjam_nama" class="mt-1 text-sm text-red-600">
              {{ formPinjam.errors.peminjam_id || formPinjam.errors.peminjam_nama }}
            </p>
          </div>

          <div v-if="!formPinjam.peminjam_id" class="md:col-span-2">
            <label for="peminjam_email" class="mb-2 block text-sm font-medium text-gray-700">
              Email Peminjam (opsional)
            </label>
            <input
              id="peminjam_email"
              v-model="formPinjam.peminjam_email"
              type="email"
              placeholder="Contoh: peminjam@domain.com"
              class="w-full rounded-lg border px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
              :class="{ 'border-red-500': formPinjam.errors.peminjam_email }"
              autocomplete="off"
            />
            <p v-if="formPinjam.errors.peminjam_email" class="mt-1 text-sm text-red-600">{{ formPinjam.errors.peminjam_email }}</p>
          </div>

          <div>
            <label for="tanggal_pinjam" class="mb-2 block text-sm font-medium text-gray-700">
              Tanggal Pinjam <span class="text-red-500">*</span>
            </label>
            <input
              id="tanggal_pinjam"
              v-model="formPinjam.tanggal_pinjam"
              type="date"
              class="w-full rounded-lg border px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-green-500"
              :class="{ 'border-red-500': formPinjam.errors.tanggal_pinjam }"
            />
            <p v-if="formPinjam.errors.tanggal_pinjam" class="mt-1 text-sm text-red-600">{{ formPinjam.errors.tanggal_pinjam }}</p>
          </div>
        </div>

        <div v-if="formPinjam.errors.error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600">
          {{ formPinjam.errors.error }}
        </div>

        <div class="flex justify-end gap-3 border-t pt-4">
          <button
            type="button"
            class="rounded-full border border-gray-300 px-5 py-2 text-gray-700 transition hover:bg-gray-50"
            @click="closePinjamModal"
            :disabled="formPinjam.processing"
          >
            Batal
          </button>
          <button
            type="submit"
            class="rounded-full bg-[#78AE4E] px-5 py-2 text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="formPinjam.processing || !hasAvailableBooks"
          >
            <span v-if="formPinjam.processing">Memproses...</span>
            <span v-else>Catat Peminjaman</span>
          </button>
        </div>
      </form>
    </Modal>

    <Modal
      :show="showKembaliModal"
      title="Catat Pengembalian Buku"
      variant="primary"
      max-width="3xl"
      @close="closeKembaliModal"
    >
      <template #description>
        Pilih buku yang sedang dipinjam untuk menyelesaikan proses pengembalian.
      </template>

      <form @submit.prevent="submitPengembalian" class="space-y-5">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <div>
            <label for="buku_kembali" class="mb-2 block text-sm font-medium text-gray-700">
              Pilih Buku yang Dikembalikan <span class="text-red-500">*</span>
            </label>
            <div class="relative" ref="bukuKembaliWrapper">
              <input
                id="buku_kembali"
                v-model="searchBuku"
                type="text"
                placeholder="Cari judul buku..."
                class="w-full rounded-lg border px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': formKembali.errors.peminjaman_id }"
                @focus="showBukuDropdown = true"
                autocomplete="off"
              />
              <div
                v-if="showBukuDropdown && filteredBuku.length > 0"
                class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border bg-white shadow-lg"
              >
                <button
                  v-for="buku in filteredBuku"
                  :key="buku.loan_id"
                  type="button"
                  @click="selectBuku(buku)"
                  class="w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                >
                  <div class="font-medium text-gray-900">{{ buku.judul }}</div>
                  <div class="text-sm text-gray-500">Peminjam: {{ getBorrowerName(buku) }}</div>
                </button>
              </div>
              <div
                v-if="showBukuDropdown && searchBuku && filteredBuku.length === 0"
                class="absolute z-10 mt-1 w-full rounded-lg border bg-white p-3 text-center text-sm text-gray-500"
              >
                Tidak ada hasil yang cocok
              </div>
            </div>
            <p v-if="formKembali.errors.peminjaman_id" class="mt-1 text-sm text-red-600">{{ formKembali.errors.peminjaman_id }}</p>
            <p v-if="!hasBorrowedBooks" class="mt-1 text-sm text-green-600">
              Tidak ada buku yang sedang dipinjam.
            </p>
          </div>

          <div>
            <label for="tanggal_kembali" class="mb-2 block text-sm font-medium text-gray-700">
              Tanggal Kembali <span class="text-red-500">*</span>
            </label>
            <input
              id="tanggal_kembali"
              v-model="formKembali.tanggal_kembali"
              type="date"
              class="w-full rounded-lg border px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500"
              :class="{ 'border-red-500': formKembali.errors.tanggal_kembali }"
            />
            <p v-if="formKembali.errors.tanggal_kembali" class="mt-1 text-sm text-red-600">{{ formKembali.errors.tanggal_kembali }}</p>
          </div>
        </div>

        <div v-if="formKembali.errors.error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600">
          {{ formKembali.errors.error }}
        </div>

        <div class="flex justify-end gap-3 border-t pt-4">
          <button
            type="button"
            class="rounded-full border border-gray-300 px-5 py-2 text-gray-700 transition hover:bg-gray-50"
            @click="closeKembaliModal"
            :disabled="formKembali.processing"
          >
            Batal
          </button>
          <button
            type="submit"
            class="rounded-full bg-blue-600 px-5 py-2 text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="formKembali.processing || !hasBorrowedBooks"
          >
            <span v-if="formKembali.processing">Memproses...</span>
            <span v-else>Catat Pengembalian</span>
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Modal from '@/Components/Modal.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout,
})

const props = defineProps({
  user: {
    type: Object,
    default: () => ({}),
  },
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
