<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
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

// Intersection Observer refs
const headerRef = ref(null)
const headerVisible = ref(false)
const listRef = ref(null)
const listVisible = ref(false)

let headerObserver = null
let listObserver = null

// Stagger delay helper
const getStaggerDelay = (index) => `${index * 100}ms`

onMounted(() => {
  if (headerRef.value) {
    headerObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            headerVisible.value = true
            headerObserver?.disconnect()
          }
        })
      },
      { threshold: 0.1 }
    )
    headerObserver.observe(headerRef.value)
  }

  if (listRef.value) {
    listObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            listVisible.value = true
            listObserver?.disconnect()
          }
        })
      },
      { threshold: 0.15 }
    )
    listObserver.observe(listRef.value)
  }
})

onUnmounted(() => {
  headerObserver?.disconnect()
  listObserver?.disconnect()
})

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
                class="h-14 w-auto object-contain transition-transform duration-500 group-hover:rotate-12 group-hover:scale-110"
              >
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

      <section class="relative pt-12 md:pt-16 pb-10 md:pb-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-50 via-white to-emerald-50/20 overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div class="absolute top-0 right-1/4 w-96 h-96 bg-[#76B340]/10 rounded-full blur-3xl animate-pulse"></div>
          <div class="absolute bottom-0 left-1/4 w-80 h-80 bg-emerald-200/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <div class="max-w-5xl mx-auto relative z-10">
          <!-- Breadcrumb -->
          <nav class="flex items-center gap-2 text-sm text-slate-600 mb-8">
            <Link :href="route('public.home')" class="hover:text-[#76B340] transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
              </svg>
            </Link>
            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-medium text-[#76B340]">Dokumentasi & Berita</span>
          </nav>

          <!-- Header Content -->
          <div class="text-center">
            <!-- Icon Badge -->
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-[#76B340] to-emerald-600 shadow-lg mb-6 animate-bounce" style="animation-duration: 2s;">
              <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
              </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 tracking-tight">
              <span class="bg-gradient-to-r from-[#76B340] via-emerald-600 to-[#76B340] bg-clip-text text-transparent">
                Dokumentasi & Berita
              </span>
            </h1>

            <!-- Subtitle -->
            <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto mb-8 leading-relaxed">
              Jelajahi informasi terbaru, kegiatan, dan dokumentasi seputar <span class="font-semibold text-[#76B340]">Terminal Pintar</span>
            </p>

            <!-- Decorative Divider -->
            <div class="flex items-center justify-center gap-2 mb-8">
              <div class="h-1 w-20 bg-gradient-to-r from-transparent via-[#76B340] to-transparent rounded-full"></div>
              <div class="h-2 w-2 rounded-full bg-[#76B340]"></div>
              <div class="h-1 w-20 bg-gradient-to-r from-transparent via-[#76B340] to-transparent rounded-full"></div>
            </div>

            
          </div>
        </div>
      </section>

      <section class="px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto pb-8 pt-8">
        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-6">
          <div
            v-for="n in 7"
            :key="n"
            class="bg-white rounded-2xl border-2 border-gray-100 p-6 flex flex-col md:flex-row gap-6 animate-pulse"
          >
            <div class="w-full md:w-40 h-56 md:h-32 bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl shrink-0" />
            <div class="grow space-y-4">
              <div class="h-7 bg-gradient-to-r from-gray-200 to-gray-300 rounded-lg w-3/4" />
              <div class="h-5 bg-gradient-to-r from-gray-200 to-gray-300 rounded w-1/2" />
              <div class="h-10 bg-gradient-to-r from-gray-200 to-gray-300 rounded-full w-40 mt-4" />
            </div>
          </div>
        </div>

        <!-- News List -->
        <div v-else class="space-y-6">
          <article
            v-for="(item, index) in items"
            :key="item.id"
            class="group relative bg-white rounded-2xl border-2 border-slate-100 p-6 flex flex-col md:flex-row gap-6 transition-all duration-500 hover:shadow-2xl hover:shadow-emerald-100/50 hover:-translate-y-2 hover:border-[#76B340]/30"
            :style="{ transitionDelay: `${index * 50}ms` }"
          >
            <!-- Gradient Overlay on Hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-[#76B340]/5 to-emerald-50/30 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

            <!-- Image Container -->
            <div class="relative w-full md:w-44 h-56 md:h-36 shrink-0 overflow-hidden rounded-xl">
              <!-- Category Badge -->
              <div class="absolute top-3 left-3 z-10">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-white/95 backdrop-blur-sm text-[#76B340] shadow-lg group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                  </svg>
                  Kegiatan
                </span>
              </div>

              <!-- Image -->
              <img
                :src="item.gambar_url || assets.placeholder"
                :alt="item.judul"
                class="w-full h-full object-cover transform transition-all duration-700 group-hover:scale-110 group-hover:rotate-1"
                @error="handleImageError"
              >
              
              <!-- Gradient Overlay -->
              <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

              <!-- Play Icon Overlay -->
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-500 transform scale-50 group-hover:scale-100">
                <div class="w-14 h-14 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow-2xl">
                  <svg class="w-6 h-6 text-[#76B340] ml-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                  </svg>
                </div>
              </div>

              <!-- Corner Decoration -->
              <div class="absolute bottom-0 right-0 w-20 h-20 overflow-hidden rounded-br-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <div class="absolute -bottom-10 -right-10 w-20 h-20 bg-gradient-to-br from-[#76B340]/20 to-transparent rounded-full blur-xl" />
              </div>
            </div>

            <!-- Content -->
            <div class="grow relative z-10 flex flex-col">
              <!-- Title -->
              <Link :href="route('public.news.show', item.slug)" class="group/title">
                <h3 class="font-extrabold text-xl md:text-2xl mb-3 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent group-hover:from-[#76B340] group-hover:to-emerald-600 transition-all duration-500 line-clamp-2">
                  {{ item.judul }}
                </h3>
              </Link>

              <!-- Meta Information -->
              <div class="flex flex-wrap items-center gap-3 mb-4 text-sm text-slate-500">
                <!-- Date -->
                <div class="flex items-center gap-1.5 group/meta">
                  <svg class="w-4 h-4 group-hover/meta:text-[#76B340] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="font-medium">{{ formatTanggal(item.tanggal_publikasi) }}</span>
                </div>
              </div>

              <!-- CTA Button -->
              <div class="mt-auto">
                <Link
                  :href="route('public.news.show', item.slug)"
                  class="group/btn relative inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold overflow-hidden transition-all duration-300 border-2 border-[#76B340] text-[#76B340] hover:text-white hover:shadow-lg hover:scale-105"
                >
                  <!-- Animated Background -->
                  <span class="absolute inset-0 bg-gradient-to-r from-[#76B340] to-emerald-600 transform -translate-x-full group-hover/btn:translate-x-0 transition-transform duration-300 ease-out" />
                  
                  <!-- Button Content -->
                  <span class="relative flex items-center gap-2">
                    Baca Selengkapnya
                    
                  </span>
                </Link>
              </div>
            </div>

            <!-- Decorative Corner Element -->
            <div class="absolute top-0 right-0 w-24 h-24 overflow-hidden rounded-tr-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
              <div class="absolute -top-12 -right-12 w-24 h-24 bg-gradient-to-br from-[#76B340]/10 to-transparent rounded-full blur-xl" />
            </div>
          </article>

          <!-- Empty State -->
          <div v-if="!items.length" class="text-center py-16">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 mb-4">
              <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <p class="text-lg font-semibold text-slate-600 mb-2">Belum Ada Berita</p>
            <p class="text-sm text-slate-500">Belum ada berita yang dipublikasikan saat ini.</p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="!isLoading && lastPage > 1" class="mt-16 flex flex-col items-center space-y-6">
          <!-- Page Numbers -->
          <div class="flex items-center gap-2">
            <!-- Previous Button -->
            <button
              type="button"
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="group w-11 h-11 rounded-xl flex items-center justify-center text-slate-600 bg-white border-2 border-slate-200 shadow-sm transition-all hover:bg-[#76B340] hover:border-[#76B340] hover:text-white hover:shadow-lg hover:-translate-x-1 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-slate-200 disabled:hover:text-slate-600 disabled:hover:translate-x-0"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <!-- Page Buttons -->
            <button
              v-for="page in paginationPages"
              :key="page"
              type="button"
              @click="goToPage(page)"
              :class="[
                'w-11 h-11 rounded-xl flex items-center justify-center text-sm font-bold border-2 transition-all duration-300',
                page === currentPage
                  ? 'bg-gradient-to-br from-[#76B340] to-emerald-600 text-white border-[#76B340] shadow-lg shadow-emerald-200 scale-110'
                  : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-[#76B340] hover:text-[#76B340] hover:scale-105'
              ]"
            >
              {{ page }}
            </button>

            <!-- Next Button -->
            <button
              type="button"
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === lastPage"
              class="group w-11 h-11 rounded-xl flex items-center justify-center text-slate-600 bg-white border-2 border-slate-200 shadow-sm transition-all hover:bg-[#76B340] hover:border-[#76B340] hover:text-white hover:shadow-lg hover:translate-x-1 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white disabled:hover:border-slate-200 disabled:hover:text-slate-600 disabled:hover:translate-x-0"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <!-- Pagination Label -->
          <div class="flex items-center gap-3 text-sm">
            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-slate-100">
              <svg class="w-4 h-4 text-[#76B340]" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
              </svg>
              <span class="font-bold text-slate-700">{{ paginationLabel }}</span>
            </div>
          </div>
        </div>

      </section>
    </div>
  </PublicLayout>
</template>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0px) translateX(0px); }
  33% { transform: translateY(-25px) translateX(15px); }
  66% { transform: translateY(-12px) translateX(-12px); }
}

@keyframes float-delayed {
  0%, 100% { transform: translateY(0px) translateX(0px) scale(1); }
  33% { transform: translateY(20px) translateX(-20px) scale(1.05); }
  66% { transform: translateY(8px) translateX(15px) scale(0.95); }
}

@keyframes text-shimmer {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 0.6; }
}

.animate-float {
  animation: float 10s ease-in-out infinite;
}

.animate-float-delayed {
  animation: float-delayed 12s ease-in-out infinite;
  animation-delay: 1.5s;
}

.animate-text-shimmer {
  animation: text-shimmer 3s ease-in-out infinite;
}

.animate-pulse-slow {
  animation: pulse-slow 4s ease-in-out infinite;
}
</style>
