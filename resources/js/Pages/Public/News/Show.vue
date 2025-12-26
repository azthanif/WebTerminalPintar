<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted } from 'vue'

// Intersection Observer refs
const headerRef = ref(null)
const headerVisible = ref(false)
const contentRef = ref(null)
const contentVisible = ref(false)
const galleryRef = ref(null)
const galleryVisible = ref(false)

let headerObserver = null
let contentObserver = null
let galleryObserver = null

// Stagger delay helper
const getStaggerDelay = (index) => `${index * 150}ms`

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

  if (contentRef.value) {
    contentObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            contentVisible.value = true
            contentObserver?.disconnect()
          }
        })
      },
      { threshold: 0.2 }
    )
    contentObserver.observe(contentRef.value)
  }

  if (galleryRef.value) {
    galleryObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            galleryVisible.value = true
            galleryObserver?.disconnect()
          }
        })
      },
      { threshold: 0.15 }
    )
    galleryObserver.observe(galleryRef.value)
  }
})

onUnmounted(() => {
  headerObserver?.disconnect()
  contentObserver?.disconnect()
  galleryObserver?.disconnect()
})

const props = defineProps({
  berita: {
    type: Object,
    required: true,
  },
})

const assets = {
  logo: '/images/logo-terminal-pintar.png',
  placeholder: '/images/dokumentasi.jpg',
}

const isMobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const formattedDate = computed(() => {
  if (!props.berita?.tanggal_publikasi) return ''
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(props.berita.tanggal_publikasi).toLocaleDateString('id-ID', options)
})

const heroImages = computed(() => {
  const gallery = props.berita?.gambar_header ?? props.berita?.gallery ?? []
  if (Array.isArray(gallery) && gallery.length) {
    return gallery.map((item) => item || assets.placeholder)
  }
  if (props.berita?.gambar_url) {
    return [props.berita.gambar_url]
  }
  return [assets.placeholder]
})

const subtitle = computed(() => props.berita?.sub_judul ?? props.berita?.deskripsi_singkat ?? '')
const contentHtml = computed(() => props.berita?.konten ?? '')
const isLoading = computed(() => !props.berita)

const handleImageError = (event) => {
  if (event?.target) {
    event.target.src = assets.placeholder
  }
}
</script>

<template>
  <PublicLayout :show-navbar="false" :show-footer="true">
    <Head :title="berita?.judul ?? 'Detail Berita'" />

    <div class="font-sans bg-gray-50 min-h-screen">
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

      <main class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 mb-20">
          <div v-if="isLoading" class="p-8 md:p-12 animate-pulse">
            <div class="h-6 bg-gray-200 rounded w-1/4 mb-4" />
            <div class="h-10 bg-gray-200 rounded w-3/4 mb-8" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
              <div class="h-64 bg-gray-200 rounded-xl" />
              <div class="h-64 bg-gray-200 rounded-xl" />
            </div>
            <div class="space-y-3">
              <div class="h-4 bg-gray-200 rounded w-full" />
              <div class="h-4 bg-gray-200 rounded w-full" />
              <div class="h-4 bg-gray-200 rounded w-5/6" />
            </div>
          </div>

          <article v-else class="relative">
            <!-- Breadcrumb -->
            <div class="px-5 sm:px-8 md:px-12 pt-6 sm:pt-8 pb-4">
              <nav class="flex items-center gap-2 text-sm text-slate-600">
                <Link :href="route('public.home')" class="hover:text-[#76B340] transition-colors">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                  </svg>
                </Link>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <Link :href="route('public.news.index')" class="hover:text-[#76B340] transition-colors">
                  Kegiatan
                </Link>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-medium text-[#76B340] truncate">{{ berita.judul?.substring(0, 40) }}...</span>
              </nav>
            </div>

            <!-- Article Header -->
            <div class="px-5 sm:px-8 md:px-12 pb-6 sm:pb-8">
              <!-- Category Badge -->
              <div class="inline-flex items-center gap-2 px-4 py-2 mb-6 text-xs font-bold tracking-wider uppercase bg-gradient-to-r from-[#76B340] to-emerald-600 text-white rounded-full shadow-lg">
                
                Dokumentasi Kegiatan
              </div>

              <!-- Title -->
              <h1 class="text-2xl sm:text-3xl md:text-5xl font-extrabold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent mb-5 sm:mb-6 leading-tight">
                {{ berita.judul }}
              </h1>

              <!-- Meta Info Row -->
              <div class="flex flex-wrap items-center gap-4 mb-8 pb-8 border-b-2 border-slate-100">
                <!-- Date -->
                <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-xl">
                  <svg class="w-5 h-5 text-[#76B340]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="text-sm font-medium text-slate-700">{{ formattedDate }}</span>
                </div>

                
              </div>

              <!-- Subtitle -->
              <p v-if="subtitle" class="text-lg sm:text-xl md:text-2xl text-slate-600 leading-relaxed mb-6 sm:mb-8 font-light">
                {{ subtitle }}
              </p>
            </div>

            <!-- Image Gallery -->
            <div class="px-5 sm:px-8 md:px-12 mb-8 sm:mb-12">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                  v-for="(gambar, index) in heroImages"
                  :key="index"
                  class="group relative overflow-hidden rounded-2xl shadow-xl h-72 md:h-96 cursor-pointer"
                  :class="heroImages.length === 1 ? 'md:col-span-2' : ''"
                >
                  <!-- Image -->
                  <img
                    :src="gambar"
                    alt="Foto Kegiatan"
                    class="w-full h-full object-cover transform transition-all duration-700 ease-in-out group-hover:scale-110 group-hover:rotate-1"
                    @error="handleImageError"
                  >
                  
                  <!-- Gradient Overlay -->
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500" />

                  <!-- Image Number Badge -->
                  <div class="absolute top-4 left-4 z-10">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm text-[#76B340] font-bold text-sm shadow-lg group-hover:scale-110 transition-transform duration-300">
                      {{ index + 1 }}
                    </span>
                  </div>

                  <!-- Zoom Icon -->
                  <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-500 transform scale-50 group-hover:scale-100">
                    <div class="w-16 h-16 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center shadow-2xl">
                      <svg class="w-8 h-8 text-[#76B340]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                      </svg>
                    </div>
                  </div>

                  <!-- Corner Decoration -->
                  <div class="absolute bottom-0 right-0 w-32 h-32 overflow-hidden rounded-br-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <div class="absolute -bottom-16 -right-16 w-32 h-32 bg-gradient-to-br from-[#76B340]/20 to-transparent rounded-full blur-2xl" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Content Section -->
            <div class="px-8 md:px-12 pb-12">
              <!-- Content Header -->
              <div class="flex items-center gap-3 mb-6">
                <div class="w-1 h-8 bg-gradient-to-b from-[#76B340] to-emerald-600 rounded-full"></div>
                <h3 class="text-2xl font-extrabold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">
                  Deskripsi Kegiatan
                </h3>
              </div>

              <!-- Article Content -->
              <div class="prose prose-lg max-w-none text-slate-600 prose-headings:text-slate-900 prose-headings:font-extrabold prose-a:text-[#76B340] prose-a:no-underline hover:prose-a:underline prose-strong:text-slate-900 prose-code:text-[#76B340] prose-code:bg-slate-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-img:rounded-xl prose-img:shadow-lg" 
                   v-html="contentHtml" 
              />
            </div>

            

            <!-- Back to List Button -->
            <div class="px-8 md:px-12 pb-12">
              <Link
                :href="route('public.news.index')"
                class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-[#76B340] to-emerald-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 group"
              >
                <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="font-bold">Kembali ke Daftar Kegiatan</span>
              </Link>
            </div>
          </article>
        </div>

      </main>
    </div>
  </PublicLayout>
</template>

<style scoped>
@keyframes pulse-slow {
  0%, 100% { opacity: 0.8; }
  50% { opacity: 1; }
}

.animate-pulse-slow {
  animation: pulse-slow 2s ease-in-out infinite;
}
</style>
