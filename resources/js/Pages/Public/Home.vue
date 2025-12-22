<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import PrimaryButton from '@/Components/UI/PrimaryButton.vue'

const props = defineProps({
  kegiatan: {
    type: Array,
    default: () => [],
  },
  heroStats: {
    type: Object,
    default: () => ({
      students_count: 0,
      news_count: 0,
    }),
  },
})

const isMobileMenuOpen = ref(false)
const isLoading = ref(false)

// Intersection Observer refs
const heroRef = ref(null)
const heroVisible = ref(false)
const activitiesRef = ref(null)
const activitiesVisible = ref(false)
// Alias for template compatibility
const featuresRef = activitiesRef
const featuresVisible = activitiesVisible
const aboutRef = ref(null)
const aboutVisible = ref(false)
const teamRef = ref(null)
const teamVisible = ref(false)
const ctaRef = ref(null)
const ctaVisible = ref(false)

let heroObserver = null
let activitiesObserver = null
let aboutObserver = null
let teamObserver = null
let ctaObserver = null

// Stagger delay helper
const getStaggerDelay = (index) => `${index * 100}ms`

onMounted(() => {
  // Hero section observer
  if (heroRef.value) {
    heroObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          heroVisible.value = entry.isIntersecting
        })
      },
      { threshold: 0.1 }
    )
    heroObserver.observe(heroRef.value)
  }

  // Activities section observer
  if (activitiesRef.value) {
    activitiesObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          activitiesVisible.value = entry.isIntersecting
        })
      },
      { threshold: 0.15 }
    )
    activitiesObserver.observe(activitiesRef.value)
  }

  // About section observer
  if (aboutRef.value) {
    aboutObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          aboutVisible.value = entry.isIntersecting
        })
      },
      { threshold: 0.15 }
    )
    aboutObserver.observe(aboutRef.value)
  }

  // Team section observer
  if (teamRef.value) {
    teamObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          teamVisible.value = entry.isIntersecting
        })
      },
      { threshold: 0.15 }
    )
    teamObserver.observe(teamRef.value)
  }

  // CTA section observer
  if (ctaRef.value) {
    ctaObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          ctaVisible.value = entry.isIntersecting
        })
      },
      { threshold: 0.15 }
    )
    ctaObserver.observe(ctaRef.value)
  }
})

onUnmounted(() => {
  heroObserver?.disconnect()
  activitiesObserver?.disconnect()
  aboutObserver?.disconnect()
  teamObserver?.disconnect()
  ctaObserver?.disconnect()
})

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const formatTanggal = (dateString) => {
  if (!dateString) return 'Tanggal belum tersedia'
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

const assets = {
  logo: '/images/logo-terminal-pintar.png',
  hero: '/images/hero.jpg',
  pengurus: '/images/pengurus.jpg',
  donasi: '/images/donasi.jpg',
  relawan: '/images/relawan.jpg',
}

const placeholderImage = '/images/dokumentasi.jpg'

const handleImageError = (event) => {
  if (event?.target) {
    event.target.src = placeholderImage
  }
}
</script>

<template>
  <PublicLayout :show-navbar="false" :show-footer="false">
    <Head title="Terminal Pintar - Beranda" />

    <div class="landing-page font-sans text-gray-800">
      <header class="bg-white border-b border-gray-200 py-4 sticky top-0 z-50 shadow-sm transition-all duration-300">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center">
            <a href="#" class="flex items-center group shrink-0" @click.prevent="closeMobileMenu">
              <img
                :src="assets.logo"
                alt="Logo Terminal Pintar"
                class="h-10 sm:h-12 md:h-14 w-auto object-contain transition-transform duration-500 group-hover:rotate-12 group-hover:scale-105 mr-2 sm:mr-3"
              >
              <span class="text-lg sm:text-xl md:text-2xl font-bold text-[#76B340] tracking-tight transition-colors duration-300 group-hover:text-[#5a8a30]">
                Terminal Pintar
              </span>
            </a>

            <nav class="hidden lg:flex items-center space-x-8">
              <a
                href="#kegiatan"
                class="relative group text-gray-600 font-medium transition-colors hover:text-[#76B340]"
              >
                Kegiatan
                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#76B340] transition-all duration-300 group-hover:w-full" />
              </a>
              <a
                href="#tentang"
                class="relative group text-gray-600 font-medium transition-colors hover:text-[#76B340]"
              >
                Tentang Kami
                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#76B340] transition-all duration-300 group-hover:w-full" />
              </a>
              <a
                href="#kontak"
                class="relative group text-gray-600 font-medium transition-colors hover:text-[#76B340]"
              >
                Kontak
                <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#76B340] transition-all duration-300 group-hover:w-full" />
              </a>
              <Link
                :href="route('login')"
                class="bg-[#76B340] text-white px-6 py-2 rounded-full text-sm font-medium transform transition-all duration-300 hover:bg-[#659936] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-green-200 active:scale-95"
              >
                Login
              </Link>
            </nav>

            <div class="flex lg:hidden items-center">
              <button
                type="button"
                @click="toggleMobileMenu"
                class="text-[#76B340] hover:text-[#659936] focus:outline-none p-2 transition-colors"
                aria-label="Toggle navigation"
              >
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    v-if="!isMobileMenuOpen"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    v-else
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div
          v-show="isMobileMenuOpen"
          class="lg:hidden border-t border-gray-100 bg-white shadow-xl absolute w-full left-0 z-40 transform transition-all duration-300 ease-in-out"
        >
          <div class="px-4 pt-4 pb-6 space-y-3">
            <a
              href="#kegiatan"
              class="group flex items-center px-4 py-3 rounded-xl text-base font-medium text-gray-600 transition-all duration-300 hover:bg-green-50 hover:text-[#76B340] hover:translate-x-2 hover:shadow-sm"
              @click="closeMobileMenu"
            >
              <span class="w-1.5 h-5 bg-[#76B340] rounded-full mr-3 opacity-0 transform -translate-x-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0" />
              Kegiatan
            </a>
            <a
              href="#tentang"
              class="group flex items-center px-4 py-3 rounded-xl text-base font-medium text-gray-600 transition-all duration-300 hover:bg-green-50 hover:text-[#76B340] hover:translate-x-2 hover:shadow-sm"
              @click="closeMobileMenu"
            >
              <span class="w-1.5 h-5 bg-[#76B340] rounded-full mr-3 opacity-0 transform -translate-x-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0" />
              Tentang Kami
            </a>
            <a
              href="#kontak"
              class="group flex items-center px-4 py-3 rounded-xl text-base font-medium text-gray-600 transition-all duration-300 hover:bg-green-50 hover:text-[#76B340] hover:translate-x-2 hover:shadow-sm"
              @click="closeMobileMenu"
            >
              <span class="w-1.5 h-5 bg-[#76B340] rounded-full mr-3 opacity-0 transform -translate-x-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0" />
              Kontak
            </a>
            <div class="pt-4 px-1">
              <Link
                :href="route('login')"
                class="block w-full text-center bg-[#76B340] text-white px-4 py-3 rounded-xl text-base font-bold shadow-md transition-all duration-300 hover:bg-[#659936] hover:shadow-xl hover:-translate-y-1 active:scale-95"
                @click="closeMobileMenu"
              >
                Login
              </Link>
            </div>
          </div>
        </div>
      </header>

      <section
        ref="heroRef"
        class="hero-section relative bg-cover bg-center min-h-[500px] md:min-h-[600px] flex items-center text-white overflow-hidden"
        :style="{ backgroundImage: `url('${assets.hero}')` }"
      >
        <div class="absolute inset-0 bg-gradient-to-r from-[#76B340]/90 via-[#76B340]/80 to-[#76B340]/70 md:from-[#76B340]/80 md:via-[#76B340]/70 md:to-[#76B340]/60" />
        
        <!-- Animated background elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl opacity-50 animate-pulse"></div>
          <div class="absolute bottom-1/4 left-1/4 w-72 h-72 bg-emerald-400/10 rounded-full blur-3xl opacity-30 animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10 relative py-20">
          <!-- Headline with staggered animation -->
          <h1 
            :class="[
              'text-3xl sm:text-4xl md:text-6xl font-extrabold mb-4 sm:mb-6 drop-shadow-lg transition-all duration-1000',
              heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'
            ]"
          >
            Halo, Selamat Datang di <br>
            <span class="inline-block mt-1 sm:mt-2 bg-gradient-to-r from-white to-emerald-100 bg-clip-text text-transparent">
              Terminal Pintar!
            </span>
          </h1>
          
          <!-- Subheadline -->
          <p 
            :class="[
              'text-base sm:text-lg md:text-xl font-light max-w-3xl mx-auto mb-8 sm:mb-10 leading-relaxed transition-all duration-1000 delay-100 px-2 sm:px-0',
              heroVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'
            ]"
          >
            Mari bergabung bersama kami untuk belajar, berbagi inspirasi, menjadi relawan, 
            atau berdonasi demi mendukung masa depan yang lebih baik.
          </p>
          
          <!-- CTA Button with animation -->
          <div 
            :class="[
              'transition-all duration-1000 delay-200',
              heroVisible ? 'opacity-100 translate-y-0 scale-100' : 'opacity-0 translate-y-10 scale-95'
            ]"
          >
            <a
              href="#kegiatan"
              class="inline-flex items-center gap-2 bg-white text-[#76B340] px-8 py-4 rounded-full text-base font-bold shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 group"
            >
              Jelajah Lebih Lanjut
            </a>
          </div>

          <!-- Trust signals with staggered animation -->
          <div 
            :class="[
              'mt-16 flex flex-wrap justify-center gap-8 items-center transition-all duration-1000 delay-300',
              heroVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-95'
            ]"
          >
            <div class="text-center group cursor-default">
              <div class="text-3xl md:text-4xl font-extrabold text-white group-hover:scale-110 transition-transform">
                {{ heroStats.students_count }}+
              </div>
              <div class="text-sm text-white/80 font-medium">Siswa Binaan</div>
            </div>
            <div class="hidden sm:block w-px h-12 bg-white/30"></div>
            <div class="text-center group cursor-default">
              <div class="text-3xl md:text-4xl font-extrabold text-white group-hover:scale-110 transition-transform">
                {{ heroStats.news_count }}+
              </div>
              <div class="text-sm text-white/80 font-medium">Kegiatan Terlaksana</div>
            </div>
          </div>
        </div>
      </section>

      <section id="kegiatan" class="py-16 md:py-24 bg-slate-50">
        <div ref="featuresRef" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 
            :class="[
              'text-3xl md:text-4xl font-extrabold text-center mb-4 relative inline-block left-1/2 -translate-x-1/2 transition-all duration-1000',
              featuresVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'
            ]"
          >
            <span class="bg-gradient-to-r from-[#76B340] to-emerald-600 bg-clip-text text-transparent">
              Dokumentasi Kegiatan Terbaru
            </span>
            <span class="block h-1 w-24 bg-gradient-to-r from-[#76B340] to-emerald-600 mx-auto mt-4 rounded-full" />
          </h2>
          
          <p 
            :class="[
              'text-center text-slate-600 max-w-2xl mx-auto mb-12 transition-all duration-1000 delay-100',
              featuresVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'
            ]"
          >
            Lihat berbagai kegiatan dan program yang telah kami laksanakan untuk membangun masa depan yang lebih baik
          </p>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <template v-if="!isLoading && kegiatan.length">
              <article
                v-for="(item, index) in kegiatan"
                :key="item.id"
                :class="[
                  'group bg-white rounded-2xl shadow-lg hover:shadow-2xl overflow-hidden flex flex-col transition-all duration-700 hover:-translate-y-3 border border-slate-100 hover:border-[#76B340]/30 relative',
                  activitiesVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'
                ]"
                :style="activitiesVisible ? { transitionDelay: getStaggerDelay(index) } : {}"
              >
                <!-- Image Container with Advanced Effects -->
                <div class="h-64 overflow-hidden relative">
                  <!-- Main Image -->
                  <img
                    :src="item.gambar_url || placeholderImage"
                    :alt="item.judul"
                    class="h-full w-full object-cover transform group-hover:scale-110 group-hover:rotate-1 transition-all duration-700 ease-out"
                    @error="handleImageError"
                  >
                  
                  <!-- Gradient Overlay - Bottom to Top -->
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity duration-500" />
                  
                  <!-- Animated Gradient Border Effect -->
                  <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#76B340]/20 via-transparent to-emerald-500/20" />
                  </div>
                  
                  
                  <!-- Date Badge - Top Right with Animation -->
                  <div class="absolute top-4 right-4 z-10 transform group-hover:-translate-y-1 transition-transform duration-300">
                    <div class="flex flex-col items-end gap-1">
                      <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-gradient-to-r from-[#76B340] to-emerald-600 text-white shadow-lg backdrop-blur-sm">
                        {{ formatTanggal(item.tanggal_publikasi) }}
                      </span>
                    </div>
                  </div>
                </div>
                
                <!-- Content Container -->
                <div class="p-6 grow flex flex-col">
                  <!-- Title with Gradient on Hover -->
                  <h3 class="font-extrabold text-xl mb-3 text-slate-900 group-hover:bg-gradient-to-r group-hover:from-[#76B340] group-hover:to-emerald-600 group-hover:bg-clip-text group-hover:text-transparent transition-all duration-300 leading-tight line-clamp-2">
                    {{ item.judul }}
                  </h3>
                  
                  
                  <!-- Description -->
                  <p class="text-slate-600 text-sm leading-relaxed line-clamp-3 mb-6 flex-grow">
                    {{ item.deskripsi ?? item.deskripsi_singkat }}
                  </p>
                  
                  <!-- CTA Button with Icon -->
                  <Link
                    :href="route('public.news.show', item.slug)"
                    class="group/btn relative inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl text-sm font-bold overflow-hidden transition-all duration-300 border-2 border-[#76B340] text-[#76B340] hover:text-white"
                  >
                    <!-- Animated Background -->
                    <span class="absolute inset-0 bg-gradient-to-r from-[#76B340] to-emerald-600 transform -translate-x-full group-hover/btn:translate-x-0 transition-transform duration-300 ease-out" />
                    
                    <!-- Button Content -->
                    <span class="relative flex items-center gap-2">
                      Baca Selengkapnya
                    
                    </span>
                  </Link>
                </div>
                
                <!-- Decorative Corner Element -->
                <div class="absolute top-0 right-0 w-20 h-20 opacity-0 group-hover:opacity-100 transition-opacity duration-500 overflow-hidden pointer-events-none">
                  <div class="absolute -top-10 -right-10 w-20 h-20 bg-gradient-to-br from-[#76B340]/20 to-transparent rounded-full blur-xl" />
                </div>
              </article>
            </template>

            <template v-else-if="isLoading">
              <div
                v-for="n in 3"
                :key="n"
                class="bg-white rounded-2xl shadow-lg overflow-hidden animate-pulse"
              >
                <div class="h-56 w-full bg-gray-200" />
                <div class="p-6">
                  <div class="h-6 bg-gray-200 rounded mb-3 w-3/4" />
                  <div class="h-4 bg-gray-200 rounded w-1/3 mb-4" />
                  <div class="space-y-2">
                    <div class="h-4 bg-gray-200 rounded" />
                    <div class="h-4 bg-gray-200 rounded" />
                    <div class="h-4 bg-gray-200 rounded w-2/3" />
                  </div>
                </div>
              </div>
            </template>

            <p
              v-else
              class="md:col-span-3 text-center text-gray-500"
            >
              Belum ada kegiatan yang dipublikasikan.
            </p>
          </div>

          <div 
            :class="[
              'text-center mt-16 transition-all duration-1000 delay-500',
              activitiesVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'
            ]"
          >
            <Link
              :href="route('public.news.index')"
              class="group inline-flex items-center justify-center gap-2 px-8 py-4 text-lg font-bold rounded-xl bg-gradient-to-r from-[#76B340] to-emerald-600 text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:scale-105 active:scale-95"
            >
              Lihat Semua Kegiatan
              
            </Link>
          </div>
        </div>
      </section>

      <section ref="aboutRef" id="tentang" class="py-16 md:py-24 bg-gradient-to-br from-slate-50 via-white to-emerald-50/30 relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-gradient-to-br from-[#76B340]/10 to-transparent rounded-full blur-3xl opacity-60 animate-float"></div>
          <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-72 h-72 bg-gradient-to-tr from-orange-200/20 to-transparent rounded-full blur-3xl opacity-50 animate-float-delayed"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <!-- Section Header -->
          <div 
            :class="[
              'text-center mb-20 transition-all duration-1000',
              aboutVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'
            ]"
          >
            <h2 class="text-3xl md:text-5xl font-extrabold mb-4">
              <span class="bg-gradient-to-r from-[#76B340] via-emerald-600 to-[#76B340] bg-clip-text text-transparent animate-text-shimmer bg-[length:200%_100%]">
                Tentang Kami
              </span>
            </h2>
            <p class="text-slate-600 text-lg max-w-2xl mx-auto">
              Perjalanan kami dalam membangun masa depan yang lebih baik untuk anak-anak Indonesia
            </p>
            <div class="mt-6 flex items-center justify-center gap-2">
              <div class="h-1 w-20 bg-gradient-to-r from-transparent via-[#76B340] to-transparent rounded-full"></div>
              <div class="h-2 w-2 rounded-full bg-[#76B340]"></div>
              <div class="h-1 w-20 bg-gradient-to-r from-transparent via-[#76B340] to-transparent rounded-full"></div>
            </div>
          </div>

          <!-- Timeline Cards with Connecting Line -->
          <div class="relative">
            <!-- Connecting Line (Desktop) -->
            <div 
              :class="[
                'hidden md:block absolute top-1/2 left-0 right-0 h-1 bg-gradient-to-r from-[#76B340]/20 via-[#76B340] to-orange-400/20 -translate-y-1/2 rounded-full transition-all duration-1000 delay-300',
                aboutVisible ? 'opacity-100 scale-x-100' : 'opacity-0 scale-x-0'
              ]"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-[#76B340] to-orange-400 rounded-full animate-pulse"></div>
            </div>

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-16 relative">
              <!-- Card 1: SEJARAH -->
              <article 
                :class="[
                  'group relative transition-all duration-700 delay-500',
                  aboutVisible ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-20'
                ]"
              >
                <!-- Card Container -->
                <div class="relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border-2 border-slate-100 hover:border-[#76B340]/50 hover:-translate-y-2">
                  <!-- Gradient Background on Hover -->
                  <div class="absolute inset-0 bg-gradient-to-br from-[#76B340]/5 to-emerald-50/50 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                  
                  <!-- Content -->
                  <div class="relative z-10">
                    <!-- Icon Container with Animation -->
                    <div class="relative mb-6 flex justify-center">
                      <!-- Animated Ring -->
                      <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-24 h-24 rounded-full border-4 border-[#76B340]/20 group-hover:border-[#76B340]/40 transition-all duration-500 group-hover:scale-110"></div>
                        <div class="absolute w-28 h-28 rounded-full border-2 border-[#76B340]/10 group-hover:border-[#76B340]/20 transition-all duration-700 group-hover:scale-125 group-hover:rotate-180"></div>
                      </div>
                      
                      <!-- Icon Circle -->
                      <div class="relative w-20 h-20 bg-gradient-to-br from-[#76B340] to-emerald-600 rounded-full flex items-center justify-center shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white transform group-hover:rotate-12 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        
                        <!-- Pulse Effect -->
                        <div class="absolute inset-0 rounded-full bg-[#76B340] opacity-0 group-hover:opacity-30 group-hover:scale-150 transition-all duration-1000"></div>
                      </div>
                    </div>

                    <!-- Title -->
                    <h3 class="font-extrabold text-3xl text-center mb-4 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent group-hover:from-[#76B340] group-hover:to-emerald-600 transition-all duration-500">
                      SEJARAH
                    </h3>

                    <!-- Decorative Line -->
                    <div class="flex items-center justify-center gap-2 mb-6">
                      <div class="h-px w-12 bg-gradient-to-r from-transparent to-[#76B340]/50"></div>
                      <div class="h-1.5 w-1.5 rounded-full bg-[#76B340]"></div>
                      <div class="h-px w-12 bg-gradient-to-l from-transparent to-[#76B340]/50"></div>
                    </div>

                    <!-- Description -->
                    <p class="text-slate-600 leading-relaxed text-center">
                      Terminal Pintar bermula dari <span class="font-semibold text-[#76B340]">inisiatif relawan</span> yang peduli terhadap anak-anak perkotaan yang rentan. Komunitas ini menjadi <span class="font-semibold text-emerald-600">ruang aman</span> untuk bertumbuh, belajar, dan mengekspresikan kreativitas.
                    </p>

                    <!-- Stats Badge -->
                    
                  </div>

                  <!-- Corner Decoration -->
                  <div class="absolute top-0 right-0 w-32 h-32 overflow-hidden rounded-tr-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <div class="absolute -top-16 -right-16 w-32 h-32 bg-gradient-to-br from-[#76B340]/20 to-transparent rounded-full blur-2xl"></div>
                  </div>
                </div>

                <!-- Connection Point (Desktop) -->
                <div class="hidden md:block absolute top-1/2 -right-8 w-16 h-16 -translate-y-1/2 z-20">
                  <div class="w-6 h-6 rounded-full bg-white border-4 border-[#76B340] shadow-lg group-hover:scale-125 group-hover:shadow-xl transition-all duration-500 mx-auto">
                    <div class="absolute inset-0 rounded-full bg-[#76B340] opacity-0 group-hover:opacity-50 group-hover:scale-150 transition-all duration-700"></div>
                  </div>
                </div>
              </article>

              <!-- Card 2: VISI -->
              <article 
                :class="[
                  'group relative md:mt-0 transition-all duration-700 delay-700',
                  aboutVisible ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-20'
                ]"
              >
                <!-- Card Container -->
                <div class="relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border-2 border-slate-100 hover:border-orange-400/50 hover:-translate-y-2">
                  <!-- Gradient Background on Hover -->
                  <div class="absolute inset-0 bg-gradient-to-br from-orange-50/50 to-amber-50/30 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                  
                  <!-- Content -->
                  <div class="relative z-10">
                    <!-- Icon Container with Animation -->
                    <div class="relative mb-6 flex justify-center">
                      <!-- Animated Ring -->
                      <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-24 h-24 rounded-full border-4 border-orange-400/20 group-hover:border-orange-400/40 transition-all duration-500 group-hover:scale-110"></div>
                        <div class="absolute w-28 h-28 rounded-full border-2 border-orange-300/10 group-hover:border-orange-300/20 transition-all duration-700 group-hover:scale-125 group-hover:rotate-180"></div>
                      </div>
                      
                      <!-- Icon Circle -->
                      <div class="relative w-20 h-20 bg-gradient-to-br from-orange-400 to-amber-500 rounded-full flex items-center justify-center shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white transform group-hover:-rotate-12 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        
                        <!-- Pulse Effect -->
                        <div class="absolute inset-0 rounded-full bg-orange-400 opacity-0 group-hover:opacity-30 group-hover:scale-150 transition-all duration-1000"></div>
                      </div>
                    </div>

                    <!-- Title -->
                    <h3 class="font-extrabold text-3xl text-center mb-4 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent group-hover:from-orange-500 group-hover:to-amber-600 transition-all duration-500">
                      VISI
                    </h3>

                    <!-- Decorative Line -->
                    <div class="flex items-center justify-center gap-2 mb-6">
                      <div class="h-px w-12 bg-gradient-to-r from-transparent to-orange-400/50"></div>
                      <div class="h-1.5 w-1.5 rounded-full bg-orange-400"></div>
                      <div class="h-px w-12 bg-gradient-to-l from-transparent to-orange-400/50"></div>
                    </div>

                    <!-- Description -->
                    <p class="text-slate-600 leading-relaxed text-center">
                      Menjadi <span class="font-semibold text-orange-500">teman perjalanan</span> bagi anak-anak untuk menemukan kembali arti <span class="font-semibold text-amber-600">rumah, pendidikan, dan harapan</span>.
                    </p>

                    
                  </div>

                  <!-- Corner Decoration -->
                  <div class="absolute top-0 left-0 w-32 h-32 overflow-hidden rounded-tl-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <div class="absolute -top-16 -left-16 w-32 h-32 bg-gradient-to-br from-orange-300/20 to-transparent rounded-full blur-2xl"></div>
                  </div>
                </div>

                <!-- Connection Point (Desktop) -->
                <div class="hidden md:block absolute top-1/2 -left-8 w-16 h-16 -translate-y-1/2 z-20">
                  <div class="w-6 h-6 rounded-full bg-white border-4 border-orange-400 shadow-lg group-hover:scale-125 group-hover:shadow-xl transition-all duration-500 mx-auto">
                    <div class="absolute inset-0 rounded-full bg-orange-400 opacity-0 group-hover:opacity-50 group-hover:scale-150 transition-all duration-700"></div>
                  </div>
                </div>
              </article>
            </div>
          </div>

          <!-- Bottom CTA -->
          <div 
            :class="[
              'mt-20 text-center transition-all duration-1000 delay-900',
              aboutVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'
            ]"
          >
            <p class="text-slate-600 mb-6 text-lg">
              Bergabunglah dengan kami dalam perjalanan ini
            </p>
            <a
              href="#kontak"
              class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-[#76B340] to-emerald-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 group"
            >
              Hubungi Kami
              
            </a>
          </div>
        </div>
      </section>

      <section ref="teamRef" class="py-16 md:py-24 bg-gradient-to-br from-white via-slate-50 to-emerald-50/20 relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-[#76B340]/5 rounded-full blur-3xl animate-float"></div>
          <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-orange-200/10 rounded-full blur-3xl animate-float-delayed"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <!-- Section Header -->
          <div 
            :class="[
              'text-center mb-20 transition-all duration-1000',
              teamVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'
            ]"
          >
            <h2 class="text-3xl md:text-5xl font-extrabold mb-4">
              <span class="bg-gradient-to-r from-[#76B340] via-emerald-600 to-[#76B340] bg-clip-text text-transparent animate-text-shimmer bg-[length:200%_100%]">
                Tim Pengurus
              </span>
            </h2>
            <p class="text-slate-600 text-lg max-w-2xl mx-auto">
              Orang-orang hebat di balik Terminal Pintar yang berdedikasi untuk masa depan anak-anak Indonesia
            </p>
            <div class="mt-6 flex items-center justify-center gap-2">
              <div class="h-1 w-16 bg-gradient-to-r from-transparent via-[#76B340] to-transparent rounded-full"></div>
              <div class="h-2 w-2 rounded-full bg-[#76B340]"></div>
              <div class="h-1 w-16 bg-gradient-to-r from-transparent via-[#76B340] to-transparent rounded-full"></div>
            </div>
          </div>

          <!-- Team Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-5xl mx-auto">
            <!-- Team Member 1: Nabila -->
            <article 
              :class="[
                'group relative transition-all duration-700 delay-300',
                teamVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'
              ]"
            >
              <!-- Card Container -->
              <div class="relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border-2 border-slate-100 hover:border-[#76B340]/50 hover:-translate-y-3 transform perspective-1000">
                <!-- Gradient Overlay on Hover -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#76B340]/5 to-emerald-50/50 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <!-- Content -->
                <div class="relative z-10">
                  <!-- Avatar Container -->
                  <div class="relative mb-6 flex justify-center">
                    <!-- Animated Rings -->
                    <div class="absolute inset-0 flex items-center justify-center">
                      <div class="w-44 h-44 rounded-full border-4 border-[#76B340]/20 group-hover:border-[#76B340]/40 transition-all duration-700 group-hover:scale-110 group-hover:rotate-180"></div>
                      <div class="absolute w-48 h-48 rounded-full border-2 border-[#76B340]/10 group-hover:border-[#76B340]/30 transition-all duration-1000 group-hover:scale-125 group-hover:-rotate-180"></div>
                    </div>
                    
                    <!-- Avatar Image -->
                    <div class="relative">
                      <img
                        :src="assets.pengurus"
                        alt="Nabila Nurshani"
                        class="w-40 h-40 rounded-full object-cover shadow-xl relative z-10 group-hover:scale-105 transition-all duration-500 border-4 border-white group-hover:border-[#76B340]"
                      >
                      <!-- Status Badge -->
                      <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-gradient-to-br from-[#76B340] to-emerald-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300 z-20">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                      </div>
                    </div>
                  </div>

                  <!-- Name & Role -->
                  <div class="text-center mb-4">
                    <h4 class="font-extrabold text-xl mb-2 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent group-hover:from-[#76B340] group-hover:to-emerald-600 transition-all duration-500">
                      Nabila Nurshani
                    </h4>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#76B340]/10 border border-[#76B340]/20 group-hover:bg-[#76B340]/20 group-hover:border-[#76B340]/40 transition-all duration-300">
                      <svg class="w-4 h-4 text-[#76B340]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                      </svg>
                      <span class="text-sm font-bold text-[#76B340]">Pengurus</span>
                    </div>
                  </div>

                  <!-- Bio/Description -->
                  <p class="text-slate-600 text-sm text-center leading-relaxed mb-6">
                    Berdedikasi dalam mengelola program pendidikan dan pengembangan komunitas
                  </p>

                  <!-- Social Links -->
                  
                </div>

                <!-- Corner Decoration -->
                <div class="absolute top-0 right-0 w-24 h-24 overflow-hidden rounded-tr-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                  <div class="absolute -top-12 -right-12 w-24 h-24 bg-gradient-to-br from-[#76B340]/20 to-transparent rounded-full blur-xl"></div>
                </div>
              </div>
            </article>

            <!-- Team Member 2: Andi (Founder) -->
            <article 
              :class="[
                'group relative md:mt-8 transition-all duration-700 delay-500',
                teamVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'
              ]"
            >
              <!-- Card Container -->
              <div class="relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border-2 border-slate-100 hover:border-orange-400/50 hover:-translate-y-3 transform perspective-1000">
                <!-- Gradient Overlay on Hover -->
                <div class="absolute inset-0 bg-gradient-to-br from-orange-50/50 to-amber-50/30 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <!-- Founder Badge -->
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-30">
                  <div class="px-4 py-2 bg-gradient-to-r from-orange-400 to-amber-500 rounded-full shadow-lg">
                    <div class="flex items-center gap-2">
                      <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      <span class="text-xs font-bold text-white">FOUNDER</span>
                    </div>
                  </div>
                </div>

                <!-- Content -->
                <div class="relative z-10 mt-4">
                  <!-- Avatar Container -->
                  <div class="relative mb-6 flex justify-center">
                    <!-- Animated Rings -->
                    <div class="absolute inset-0 flex items-center justify-center">
                      <div class="w-44 h-44 rounded-full border-4 border-orange-400/20 group-hover:border-orange-400/40 transition-all duration-700 group-hover:scale-110 group-hover:rotate-180"></div>
                      <div class="absolute w-48 h-48 rounded-full border-2 border-orange-300/10 group-hover:border-orange-300/30 transition-all duration-1000 group-hover:scale-125 group-hover:-rotate-180"></div>
                    </div>
                    
                    <!-- Avatar Image -->
                    <div class="relative">
                      <img
                        :src="assets.pengurus"
                        alt="Andi Rahmadi"
                        class="w-40 h-40 rounded-full object-cover shadow-xl relative z-10 group-hover:scale-105 transition-all duration-500 border-4 border-white group-hover:border-orange-400"
                      >
                      <!-- Status Badge -->
                      <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-gradient-to-br from-orange-400 to-amber-500 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300 z-20">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                      </div>
                    </div>
                  </div>

                  <!-- Name & Role -->
                  <div class="text-center mb-4">
                    <h4 class="font-extrabold text-xl mb-2 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent group-hover:from-orange-500 group-hover:to-amber-600 transition-all duration-500">
                      Andi Rahmadi
                    </h4>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-orange-400/10 border border-orange-400/20 group-hover:bg-orange-400/20 group-hover:border-orange-400/40 transition-all duration-300">
                      <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                      </svg>
                      <span class="text-sm font-bold text-orange-500">Pendiri</span>
                    </div>
                  </div>

                  <!-- Bio/Description -->
                  <p class="text-slate-600 text-sm text-center leading-relaxed mb-6">
                    Visioner yang menginisiasi Terminal Pintar untuk memberdayakan anak-anak Indonesia
                  </p>

                  <!-- Social Links -->
                  
                </div>

                <!-- Corner Decoration -->
                <div class="absolute top-0 left-0 w-24 h-24 overflow-hidden rounded-tl-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                  <div class="absolute -top-12 -left-12 w-24 h-24 bg-gradient-to-br from-orange-300/20 to-transparent rounded-full blur-2xl"></div>
                </div>
              </div>
            </article>

            <!-- Team Member 3: Hanifah -->
            <article 
              :class="[
                'group relative transition-all duration-700 delay-700',
                teamVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'
              ]"
            >
              <!-- Card Container -->
              <div class="relative bg-white rounded-3xl p-8 shadow-lg hover:shadow-2xl transition-all duration-500 border-2 border-slate-100 hover:border-[#76B340]/50 hover:-translate-y-3 transform perspective-1000">
                <!-- Gradient Overlay on Hover -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#76B340]/5 to-emerald-50/50 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <!-- Content -->
                <div class="relative z-10">
                  <!-- Avatar Container -->
                  <div class="relative mb-6 flex justify-center">
                    <!-- Animated Rings -->
                    <div class="absolute inset-0 flex items-center justify-center">
                      <div class="w-44 h-44 rounded-full border-4 border-[#76B340]/20 group-hover:border-[#76B340]/40 transition-all duration-700 group-hover:scale-110 group-hover:rotate-180"></div>
                      <div class="absolute w-48 h-48 rounded-full border-2 border-[#76B340]/10 group-hover:border-[#76B340]/30 transition-all duration-1000 group-hover:scale-125 group-hover:-rotate-180"></div>
                    </div>
                    
                    <!-- Avatar Image -->
                    <div class="relative">
                      <img
                        :src="assets.pengurus"
                        alt="Hanifah Ahmad"
                        class="w-40 h-40 rounded-full object-cover shadow-xl relative z-10 group-hover:scale-105 transition-all duration-500 border-4 border-white group-hover:border-[#76B340]"
                      >
                      <!-- Status Badge -->
                      <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-gradient-to-br from-[#76B340] to-emerald-600 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300 z-20">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                      </div>
                    </div>
                  </div>

                  <!-- Name & Role -->
                  <div class="text-center mb-4">
                    <h4 class="font-extrabold text-xl mb-2 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent group-hover:from-[#76B340] group-hover:to-emerald-600 transition-all duration-500">
                      Hanifah Ahmad
                    </h4>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#76B340]/10 border border-[#76B340]/20 group-hover:bg-[#76B340]/20 group-hover:border-[#76B340]/40 transition-all duration-300">
                      <svg class="w-4 h-4 text-[#76B340]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                      </svg>
                      <span class="text-sm font-bold text-[#76B340]">Pengurus</span>
                    </div>
                  </div>

                  <!-- Bio/Description -->
                  <p class="text-slate-600 text-sm text-center leading-relaxed mb-6">
                    Berkomitmen dalam mengembangkan program dan memberdayakan relawan
                  </p>

                  <!-- Social Links -->
                  
                </div>

                <!-- Corner Decoration -->
                <div class="absolute top-0 right-0 w-24 h-24 overflow-hidden rounded-tr-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                  <div class="absolute -top-12 -right-12 w-24 h-24 bg-gradient-to-br from-[#76B340]/20 to-transparent rounded-full blur-xl"></div>
                </div>
              </div>
            </article>
          </div>
        </div>
      </section>

      
        <!-- Animated Background Elements -->

      <section ref="ctaRef" class="py-24 bg-gradient-to-br from-slate-50 via-white to-emerald-50/20 relative overflow-hidden">

        <div class="absolute inset-0 overflow-hidden pointer-events-none">
          <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-[#76B340]/10 rounded-full blur-3xl animate-float"></div>
          <div class="absolute bottom-1/3 left-1/4 w-80 h-80 bg-orange-200/10 rounded-full blur-3xl animate-float-delayed"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div 
            :class="[
              'text-center mb-16 transition-all duration-1000',
              ctaVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'
            ]"
          >
            <h2 class="text-4xl md:text-5xl font-extrabold mb-4">
              <span class="bg-gradient-to-r from-[#76B340] via-emerald-600 to-orange-500 bg-clip-text text-transparent animate-text-shimmer bg-[length:200%_100%]">
                Mari Berkontribusi Membangun Harapan!
              </span>
            </h2>
            <p class="text-slate-600 text-lg max-w-2xl mx-auto">
              Setiap kontribusi Anda, sekecil apapun, membawa perubahan besar bagi masa depan anak-anak Indonesia
            </p>
            <div class="mt-6 flex items-center justify-center gap-2">
              <div class="h-1 w-20 bg-gradient-to-r from-transparent via-[#76B340] to-transparent rounded-full"></div>
              <div class="h-2 w-2 rounded-full bg-[#76B340]"></div>
              <div class="h-1 w-20 bg-gradient-to-r from-transparent via-orange-400 to-transparent rounded-full"></div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 items-stretch">
            
            <article 
              :class="[
                'group relative transition-all duration-700 delay-300 h-full',
                ctaVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'
              ]"
            >
              <div class="relative bg-white rounded-3xl p-10 shadow-xl hover:shadow-2xl transition-all duration-500 border-2 border-slate-100 hover:border-[#76B340]/50 hover:-translate-y-4 transform perspective-1000 h-full flex flex-col">
                <div class="absolute inset-0 bg-gradient-to-br from-[#76B340]/5 via-emerald-50/50 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10 text-center flex-1 flex flex-col">
                  <div class="relative mb-8 flex justify-center">
                    <div class="absolute inset-0 flex items-center justify-center">
                      <div class="w-32 h-32 rounded-full border-4 border-[#76B340]/20 group-hover:border-[#76B340]/40 transition-all duration-700 group-hover:scale-110 group-hover:rotate-180"></div>
                      <div class="absolute w-36 h-36 rounded-full border-2 border-[#76B340]/10 group-hover:border-[#76B340]/30 transition-all duration-1000 group-hover:scale-125 group-hover:-rotate-180"></div>
                    </div>
                    
                    <div class="relative w-28 h-28 bg-gradient-to-br from-[#76B340] to-emerald-600 rounded-full flex items-center justify-center shadow-xl group-hover:shadow-2xl group-hover:scale-110 transition-all duration-500 overflow-hidden">
                      <img :src="assets.donasi" alt="Donasi" class="h-20 w-20 rounded-full object-cover relative z-10 group-hover:scale-110 transition-transform duration-500">
                      <div class="absolute inset-0 rounded-full bg-white opacity-0 group-hover:opacity-20 group-hover:scale-150 transition-all duration-1000"></div>
                    </div>

                    <div class="absolute -top-2 -right-2 w-12 h-12 bg-gradient-to-br from-emerald-400 to-[#76B340] rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-12 transition-all duration-300">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>

                  <h3 class="font-extrabold text-3xl mb-4 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent group-hover:from-[#76B340] group-hover:to-emerald-600 transition-all duration-500">
                    DONASI
                  </h3>

                  <div class="flex items-center justify-center gap-2 mb-6">
                    <div class="h-px w-16 bg-gradient-to-r from-transparent to-[#76B340]/50"></div>
                    <div class="h-1.5 w-1.5 rounded-full bg-[#76B340]"></div>
                    <div class="h-px w-16 bg-gradient-to-l from-transparent to-[#76B340]/50"></div>
                  </div>

                  <p class="text-slate-600 leading-relaxed mb-8 flex-1">
                    Mari ikut berdonasi untuk membantu mereka yang membutuhkan. <span class="font-semibold text-[#76B340]">Dukungan Anda sangat berarti</span> untuk masa depan mereka.
                  </p>

                  <a
                    href="https://forms.gle/CJAebiE78GAQN62E6"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group/btn relative inline-flex items-center justify-center gap-2 w-full px-8 py-4 rounded-xl text-base font-bold overflow-hidden transition-all duration-300 border-2 border-[#76B340] text-white bg-gradient-to-r from-[#76B340] to-emerald-600 hover:shadow-xl hover:scale-105 active:scale-95"
                  >
                    <span class="relative flex items-center gap-2">
                      Lihat Cara Berdonasi
                      
                    </span>
                  </a>
                </div>

                <div class="absolute top-0 right-0 w-32 h-32 overflow-hidden rounded-tr-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                  <div class="absolute -top-16 -right-16 w-32 h-32 bg-gradient-to-br from-[#76B340]/20 to-transparent rounded-full blur-2xl"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[#76B340] to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-b-3xl"></div>
              </div>
            </article>

            <article 
              :class="[
                'group relative md:mt-0 transition-all duration-700 delay-500 h-full',
                ctaVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-20'
              ]"
            >
              <div class="relative bg-white rounded-3xl p-10 shadow-xl hover:shadow-2xl transition-all duration-500 border-2 border-slate-100 hover:border-orange-400/50 hover:-translate-y-4 transform perspective-1000 h-full flex flex-col">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-50/50 via-amber-50/30 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10 text-center flex-1 flex flex-col">
                  <div class="relative mb-8 flex justify-center">
                    <div class="absolute inset-0 flex items-center justify-center">
                      <div class="w-32 h-32 rounded-full border-4 border-orange-400/20 group-hover:border-orange-400/40 transition-all duration-700 group-hover:scale-110 group-hover:rotate-180"></div>
                      <div class="absolute w-36 h-36 rounded-full border-2 border-orange-300/10 group-hover:border-orange-300/30 transition-all duration-1000 group-hover:scale-125 group-hover:-rotate-180"></div>
                    </div>
                    
                    <div class="relative w-28 h-28 bg-gradient-to-br from-orange-400 to-amber-500 rounded-full flex items-center justify-center shadow-xl group-hover:shadow-2xl group-hover:scale-110 transition-all duration-500 overflow-hidden">
                      <img :src="assets.relawan" alt="Relawan" class="h-20 w-20 rounded-full object-cover relative z-10 group-hover:scale-110 transition-transform duration-500">
                      <div class="absolute inset-0 rounded-full bg-white opacity-0 group-hover:opacity-20 group-hover:scale-150 transition-all duration-1000"></div>
                    </div>

                    <div class="absolute -top-2 -right-2 w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-12 transition-all duration-300">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                      </svg>
                    </div>
                  </div>

                  <h3 class="font-extrabold text-3xl mb-4 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent group-hover:from-orange-500 group-hover:to-amber-600 transition-all duration-500">
                    RELAWAN
                  </h3>

                  <div class="flex items-center justify-center gap-2 mb-6">
                    <div class="h-px w-16 bg-gradient-to-r from-transparent to-orange-400/50"></div>
                    <div class="h-1.5 w-1.5 rounded-full bg-orange-400"></div>
                    <div class="h-px w-16 bg-gradient-to-l from-transparent to-orange-400/50"></div>
                  </div>

                  <p class="text-slate-600 leading-relaxed mb-8 flex-1">
                    Mari ikut menjadi relawan untuk membantu mereka yang membutuhkan. <span class="font-semibold text-orange-500">Bantuan Anda sangat dinanti</span> untuk perubahan nyata.
                  </p>

                  <a
                    href="https://forms.gle/1hgE5aW6Qwpd9Jg29"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group/btn relative inline-flex items-center justify-center gap-2 w-full px-8 py-4 rounded-xl text-base font-bold overflow-hidden transition-all duration-300 border-2 border-orange-400 text-white bg-gradient-to-r from-orange-400 to-amber-500 hover:shadow-xl hover:scale-105 active:scale-95"
                  >
                    <span class="relative flex items-center gap-2">
                      Lihat Cara Mendaftar
                      
                    </span>
                  </a>
                </div>

                <div class="absolute top-0 left-0 w-32 h-32 overflow-hidden rounded-tl-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                  <div class="absolute -top-16 -left-16 w-32 h-32 bg-gradient-to-br from-orange-300/20 to-transparent rounded-full blur-2xl"></div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-orange-400 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-b-3xl"></div>
              </div>
            </article>
          </div>


          <!-- Bottom Encouragement -->
          <!-- <div 
            :class="[
              'mt-16 text-center transition-all duration-1000 delay-700',
              ctaVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'
            ]"
          >
            <p class="text-slate-600 text-lg mb-4">
              Bersama kita bisa membuat perbedaan
            </p>
            <div class="flex items-center justify-center gap-6 text-sm text-slate-500">
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Transparan</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Terpercaya</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Berdampak</span>
              </div>
            </div>
          </div> -->

        </div>
      </section>

      <footer id="kontak" class="bg-[#76B340] text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-8 md:pl-20 grid grid-cols-1 md:grid-cols-4 gap-12">
          <!-- GMaps Embed -->
          <div class="md:col-span-1 group">
            <h4 class="font-bold text-xl mb-6 flex items-center gap-2">
              Lokasi Kami
            </h4>
            <div class="rounded-2xl overflow-hidden border-4 border-white/20 shadow-xl group-hover:border-white/40 transition-all duration-500 h-48">
              <iframe 
                src="https://maps.google.com/maps?q=Wanda, Jakarta Timur RT.11/RW.6 gg Anggrek No 18 Rambutan, Kec. Ciracas, Kota Jakarta Timur&t=&z=17&ie=UTF8&iwloc=&output=embed" 
                class="w-full h-full grayscale hover:grayscale-0 transition-all duration-700"
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                title="Lokasi Terminal Pintar"
              ></iframe>
            </div>
          </div>

          <div class="group">
            <h4 class="font-bold text-xl mb-6 flex items-center gap-2">
              Alamat
            </h4>
            <a
              href="https://maps.app.goo.gl/6aiHX1HWpzMQ9WWBA"
              target="_blank"
              class="flex items-start gap-3 hover:translate-x-2 transition-transform duration-200 cursor-pointer leading-relaxed text-left"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
              </svg>
              <span>
                Wanda, Jakarta Timur RT.11/RW.6 gg Anggrek No 18 (Patokan gg depan Sutet), Rambutan, Kec. Ciracas, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13830
              </span>
            </a>
          </div>

          <div class="group">
            <h4 class="font-bold text-xl mb-6 flex items-center gap-2">
              Kontak Kami
            </h4>
            <a
              href="https://wa.me/6281285535095"
              target="_blank"
              class="mb-3 flex items-center gap-3 hover:translate-x-2 transition-transform duration-200 cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
              </svg>
              <span class="truncate">+62812-8553-5095</span>
            </a>
            <a
              href="mailto:terminalpintarofficial@gmail.com"
              class="mb-3 flex items-center gap-3 hover:translate-x-2 transition-transform duration-200 cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span class="break-all">terminalpintarofficial@gmail.com</span>
            </a>
          </div>

          <div class="group">
            <h4 class="font-bold text-xl mb-6 flex items-center gap-2">
              Media Sosial
            </h4>
            <a
              href="https://instagram.com/terminalpintar_id"
              target="_blank"
              class="flex items-center gap-3 mb-3 hover:text-green-100 hover:translate-x-2 transition-all duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
              </svg>
              @terminalpintar_id
            </a>
            <a
              href="https://youtube.com/@TerminalPintar"
              target="_blank"
              class="flex items-center gap-3 mb-3 hover:text-green-100 hover:translate-x-2 transition-all duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
              </svg>
              Terminal Pintar
            </a>
            <a
              href="https://tiktok.com/@terminalpintar"
              target="_blank"
              class="flex items-center gap-3 mb-3 hover:text-green-100 hover:translate-x-2 transition-all duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.49-3.35-3.98-5.6-.47-2.24.08-4.54 1.5-6.33 1.44-1.82 3.63-2.95 5.93-3.02v4.02c-1.32-.07-2.67.55-3.47 1.63-.82 1.11-1.02 2.58-.56 3.86.47 1.31 1.6 2.34 2.96 2.69 1.35.35 2.83.03 3.91-.93.76-.68 1.21-1.65 1.26-2.66V.02h-2.27z" />
              </svg>
              @terminalpintar
            </a>
          </div>
        </div>

        <div class="mt-16 border-t border-green-700 pt-8 text-center text-sm text-white/80">
          © 2025 Terminal Pintar. Semua Hak Dilindungi.
        </div>
      </footer>
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
