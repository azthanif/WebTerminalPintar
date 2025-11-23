<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  berita: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const isMobileMenuOpen = ref(false)
const isLoading = ref(false)

const assets = {
  logo: '/images/logo-terminal-pintar.png',
  placeholder: '/images/dokumentasi.jpg',
}

const items = computed(() => props.berita?.data ?? [])
const currentPage = computed(() => props.berita?.current_page ?? 1)
const lastPage = computed(() => props.berita?.last_page ?? 1)
const totalItems = computed(() => props.berita?.total ?? 0)
const perPage = computed(() => props.berita?.per_page ?? items.value.length)

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const formatTanggal = (dateString) => {
  if (!dateString) return ''
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

const paginationLabel = computed(() => {
  if (totalItems.value === 0) return 'Tidak ada berita ditemukan.'
  const start = (currentPage.value - 1) * perPage.value + 1
  const end = Math.min(currentPage.value * perPage.value, totalItems.value)
  return `Menampilkan ${start}-${end} dari ${totalItems.value} Berita`
})

const paginationPages = computed(() => {
  const pages = []
  const maxPagesToShow = 5
  let startPage = Math.max(1, currentPage.value - Math.floor(maxPagesToShow / 2))
  let endPage = Math.min(lastPage.value, startPage + maxPagesToShow - 1)

  if (endPage - startPage + 1 < maxPagesToShow && lastPage.value >= maxPagesToShow) {
    startPage = Math.max(1, endPage - maxPagesToShow + 1)
  }

  for (let page = startPage; page <= endPage; page += 1) {
    pages.push(page)
  }
  return pages
})

const goToPage = (page) => {
  if (page < 1 || page > lastPage.value || page === currentPage.value) return

  window.scrollTo({ top: 0, behavior: 'smooth' })
  router.get(
    route('public.news.index'),
    { page, ...props.filters },
    {
      preserveState: true,
      onStart: () => { isLoading.value = true },
      onFinish: () => { isLoading.value = false },
    },
  )
}

const handleImageError = (event) => {
  if (event?.target) {
    event.target.src = assets.placeholder
  }
}
</script>

<template>
  <PublicLayout :show-navbar="false">
    <Head title="Berita & Dokumentasi" />

    <div class="berita-list-page font-sans bg-gray-50 min-h-screen">
      <header class="bg-white border-b border-gray-200 py-4 sticky top-0 z-40 shadow-sm transition-all duration-300">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center">
            <Link :href="route('public.home')" class="flex items-center group">
              <img
                :src="assets.logo"
                alt="Logo Terminal Pintar"
                class="h-10 w-10 mr-3 rounded-full transition-transform duration-500 group-hover:rotate-12 group-hover:scale-110"
              >
              <span class="text-2xl font-bold text-[#76B340] tracking-tight transition-colors duration-300 group-hover:text-[#5a8a30]">
                Terminal Pintar
              </span>
            </Link>

            <nav class="hidden lg:flex items-center space-x-8">
              <Link
                :href="route('public.home')"
                class="relative group text-gray-600 font-medium transition-colors hover:text-[#76B340]"
              >
                Beranda
                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#76B340] transition-all duration-300 group-hover:w-full" />
              </Link>
              <Link
                :href="route('public.news.index')"
                class="relative group text-[#76B340] font-bold transition-colors"
              >
                Kegiatan
                <span class="absolute -bottom-1 left-0 w-full h-0.5 bg-[#76B340]" />
              </Link>
              <Link
                :href="route('login')"
                class="bg-[#76B340] text-white px-6 py-2 rounded-full text-sm font-medium transform transition-all duration-300 hover:bg-[#659936] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-green-200 active:scale-95"
              >
                Login
              </Link>
            </nav>

            <div class="flex lg:hidden items-center">
              <button type="button" @click="toggleMobileMenu" class="text-[#76B340] hover:text-[#659936] focus:outline-none p-2 transition-colors">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div
          v-show="isMobileMenuOpen"
          class="lg:hidden border-t border-gray-100 bg-white shadow-xl absolute w-full left-0 z-30 transform transition-all duration-300 ease-in-out"
        >
          <div class="px-4 pt-4 pb-6 space-y-3">
            <Link
              :href="route('public.home')"
              class="group flex items-center px-4 py-3 rounded-xl text-base font-medium text-gray-600 transition-all duration-300 hover:bg-green-50 hover:text-[#76B340] hover:translate-x-2 hover:shadow-sm"
              @click="isMobileMenuOpen = false"
            >
              <span class="w-1.5 h-5 bg-[#76B340] rounded-full mr-3 opacity-0 transform -translate-x-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0" />
              Beranda
            </Link>

            <Link
              :href="route('public.news.index')"
              class="group flex items-center px-4 py-3 rounded-xl text-base font-bold text-[#76B340] bg-green-50/50 transition-all duration-300 hover:bg-green-50 hover:translate-x-2 hover:shadow-sm"
              @click="isMobileMenuOpen = false"
            >
              <span class="w-1.5 h-5 bg-[#76B340] rounded-full mr-3" />
              Kegiatan
            </Link>

            <div class="pt-4 px-1">
              <Link
                :href="route('login')"
                class="block w-full text-center bg-[#76B340] text-white px-4 py-3 rounded-xl text-base font-bold shadow-md transition-all duration-300 hover:bg-[#659936] hover:shadow-xl hover:-translate-y-1 active:scale-95"
                @click="isMobileMenuOpen = false"
              >
                Login
              </Link>
            </div>
          </div>
        </div>
      </header>

      <section class="relative border-b border-gray-100 pt-12 pb-8 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-3xl md:text-4xl font-extrabold mb-4 text-[#76B340] tracking-tight">
            Daftar Dokumentasi & Berita
          </h1>
          <p class="text-lg font-light text-gray-500 max-w-2xl mx-auto">
            Jelajahi informasi terbaru, kegiatan, dan dokumentasi seputar Terminal Pintar.
          </p>
        </div>
      </section>

      <section class="px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto pb-16">
        <div v-if="isLoading" class="space-y-6">
          <div
            v-for="n in 7"
            :key="n"
            class="bg-white rounded-xl border border-gray-100 p-4 flex items-center animate-pulse"
          >
            <div class="w-28 h-28 bg-gray-200 rounded-lg shrink-0" />
            <div class="ml-6 grow space-y-3">
              <div class="h-6 bg-gray-200 rounded w-3/4" />
              <div class="h-4 bg-gray-200 rounded w-1/2" />
              <div class="h-8 bg-gray-200 rounded w-32 mt-2" />
            </div>
          </div>
        </div>

        <div v-else class="space-y-6">
          <div
            v-for="item in items"
            :key="item.id"
            class="group relative bg-white rounded-xl border border-gray-100 p-4 md:p-5 flex flex-col md:flex-row items-start md:items-center transition-all duration-300 ease-out hover:shadow-xl hover:shadow-green-50 hover:-translate-y-1 hover:border-green-100"
          >
            <div class="w-full md:w-32 h-48 md:h-28 shrink-0 overflow-hidden rounded-lg relative mb-4 md:mb-0">
              <img
                :src="item.gambar_url || assets.placeholder"
                :alt="item.judul"
                class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110"
                @error="handleImageError"
              >
              <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity duration-300" />
            </div>

            <div class="md:ml-6 grow">
              <Link :href="route('public.news.show', item.slug)">
                <h3 class="font-bold text-xl text-gray-800 mb-2 transition-colors duration-300 group-hover:text-[#76B340]">
                  {{ item.judul }}
                </h3>
              </Link>

              <div class="flex items-center text-sm text-gray-500 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatTanggal(item.tanggal_publikasi) }}
                <span class="mx-2 text-gray-300">|</span>
                <span class="text-xs bg-gray-100 px-2 py-0.5 rounded-full text-gray-600">
                  {{ item.waktu_lalu ?? '' }}
                </span>
              </div>

              <Link
                :href="route('public.news.show', item.slug)"
                class="inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold transition-all duration-300 text-[#76B340] bg-green-50 border border-[#76B340] group-hover:bg-[#76B340] group-hover:text-white group-hover:shadow-md"
              >
                Baca Selengkapnya
              </Link>
            </div>
          </div>

          <p v-if="!items.length" class="text-center text-gray-500 text-sm">
            Belum ada berita yang dipublikasikan.
          </p>
        </div>

        <div v-if="!isLoading && lastPage > 1" class="mt-12 flex flex-col items-center space-y-4">
          <div class="flex justify-center items-center space-x-2">
            <button
              type="button"
              @click="goToPage(currentPage - 1)
              "
              :disabled="currentPage === 1"
              class="w-10 h-10 rounded-full flex items-center justify-center text-gray-600 bg-white border border-gray-200 shadow-sm transition-all hover:bg-gray-50 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <button
              v-for="page in paginationPages"
              :key="page"
              type="button"
              @click="goToPage(page)"
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium border transition-all duration-200',
                page === currentPage
                  ? 'bg-[#76B340] text-white border-[#76B340] shadow-md scale-110'
                  : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:border-green-200 hover:text-[#76B340]'
              ]"
            >
              {{ page }}
            </button>

            <button
              type="button"
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === lastPage"
              class="w-10 h-10 rounded-full flex items-center justify-center text-gray-600 bg-white border border-gray-200 shadow-sm transition-all hover:bg-gray-50 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <p class="text-sm text-gray-400 font-medium">
            {{ paginationLabel }}
          </p>
        </div>
      </section>
    </div>
  </PublicLayout>
</template>
