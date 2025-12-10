<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  kegiatan: {
    type: Array,
    default: () => [],
  },
})

const isMobileMenuOpen = ref(false)
const isLoading = ref(false)

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
            <a href="#" class="flex items-center group" @click.prevent="closeMobileMenu">
              <img
                :src="assets.logo"
                alt="Logo Terminal Pintar"
                class="h-10 w-10 mr-3 rounded-full transition-transform duration-500 group-hover:rotate-12 group-hover:scale-110"
              >
              <span class="text-2xl font-bold text-[#76B340] tracking-tight transition-colors duration-300 group-hover:text-[#5a8a30]">
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
        class="hero-section relative bg-cover bg-center h-[500px] flex items-center text-white"
        :style="{ backgroundImage: `url('${assets.hero}')` }"
      >
        <div class="absolute inset-0 bg-linear-to-r from-[#76B340]/70 to-[#76B340]/50" />
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center z-10 relative">
          <h1 class="text-5xl md:text-6xl font-bold mb-6 italic drop-shadow-md">
            Halo, Selamat Datang di <br> Terminal Pintar!
          </h1>
          <p class="text-lg md:text-xl font-light max-w-3xl mx-auto mb-10 italic opacity-90">
            Mari bergabung bersama kami untuk belajar, berbagi inspirasi, menjadi relawan, atau berdonasi demi mendukung masa depan yang lebih baik.
          </p>
          <a
            href="#kegiatan"
            class="inline-block bg-white text-[#76B340] px-8 py-3 rounded-full text-base font-bold shadow-lg hover:bg-gray-50 hover:scale-105 hover:shadow-xl active:scale-95 transition-all duration-300"
          >
            Jelajah Lebih Lanjut
          </a>
        </div>
      </section>

      <section id="kegiatan" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-3xl font-bold text-center text-[#76B340] mb-12 relative inline-block left-1/2 -translate-x-1/2">
            Dokumentasi Kegiatan Terbaru
            <span class="block h-1 w-20 bg-[#76B340] mx-auto mt-4 rounded-full" />
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <template v-if="!isLoading && kegiatan.length">
              <article
                v-for="item in kegiatan"
                :key="item.id"
                class="group bg-white rounded-2xl shadow-md hover:shadow-2xl overflow-hidden flex flex-col transition-all duration-300 hover:-translate-y-2 border border-transparent hover:border-green-100"
              >
                <div class="h-56 overflow-hidden relative">
                  <img
                    :src="item.gambar_url || placeholderImage"
                    :alt="item.judul"
                    class="h-full w-full object-cover transform group-hover:scale-110 transition-transform duration-700"
                    @error="handleImageError"
                  >
                  <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity duration-300" />
                </div>
                <div class="p-6 grow">
                  <h3 class="font-bold text-xl mb-2 text-gray-900 group-hover:text-[#76B340] transition-colors">
                    {{ item.judul }}
                  </h3>
                  <div class="flex items-center mb-3">
                    <span class="text-xs font-semibold bg-green-100 text-[#76B340] px-2 py-1 rounded">
                      {{ formatTanggal(item.tanggal_publikasi) }}
                    </span>
                  </div>
                  <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">
                    {{ item.deskripsi ?? item.deskripsi_singkat }}
                  </p>
                </div>
                <div class="p-6 pt-0 mt-auto">
                  <Link
                    :href="route('public.news.show', item.slug)"
                    class="block w-full text-center border-2 border-[#76B340] text-[#76B340] px-5 py-2.5 rounded-xl text-sm font-bold group-hover:bg-[#76B340] group-hover:text-white transition-all duration-300"
                  >
                    Baca Selengkapnya
                  </Link>
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

          <div class="text-center mt-16">
            <Link
              :href="route('public.news.index')"
              class="group inline-flex items-center justify-center px-8 py-3 text-lg font-bold rounded-xl bg-[#76B340] text-white shadow-md transition-all duration-300 hover:bg-[#659936] hover:shadow-xl hover:-translate-y-1"
            >
              Lihat semua kegiatan
            </Link>
          </div>
        </div>
      </section>

      <section id="tentang" class="py-24 bg-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-green-100 rounded-full opacity-90 z-0" />
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-72 h-72 bg-orange-100 rounded-full opacity-90 z-0" />

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <h2 class="text-3xl font-bold text-center text-[#76B340] mb-16">
            Tentang Kami
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <div class="bg-white border-2 border-gray-100 p-8 rounded-2xl transition-all duration-300 hover:border-[#76B340] hover:shadow-xl hover:-translate-y-1 group">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#76B340]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="font-bold text-2xl text-gray-800 group-hover:text-[#76B340] mb-4 text-center transition-colors">
                SEJARAH
              </h3>
              <p class="text-gray-600 leading-relaxed text-center text-sm">
                Terminal Pintar bermula dari inisiatif relawan yang peduli terhadap anak-anak perkotaan yang rentan. Komunitas ini menjadi ruang aman untuk bertumbuh, belajar, dan mengekspresikan kreativitas.
              </p>
            </div>

            <div class="bg-white border-2 border-gray-100 p-8 rounded-2xl transition-all duration-300 hover:border-[#EB9232] hover:shadow-xl hover:-translate-y-1 group">
              <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[#EB9232]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
              </div>
              <h3 class="font-bold text-2xl text-gray-800 group-hover:text-[#EB9232] mb-4 text-center transition-colors">
                VISI
              </h3>
              <p class="text-gray-600 leading-relaxed text-center text-sm">
                Menjadi teman perjalanan anak-anak untuk menemukan kembali arti rumah, pendidikan, dan harapan.
              </p>
            </div>
          </div>
        </div>
      </section>

      <section class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-3xl font-bold text-center text-[#76B340] mb-16">
            Pengurus
          </h2>
          <div class="flex flex-wrap justify-center gap-x-20 gap-y-12">
            <div class="group flex flex-col items-center text-center w-52">
              <div class="relative mb-6">
                <div class="absolute inset-0 bg-[#76B340] rounded-full opacity-0 group-hover:opacity-20 transform scale-90 group-hover:scale-110 transition-all duration-300" />
                <img
                  :src="assets.pengurus"
                  alt="Nabila Nurshani"
                  class="w-36 h-36 rounded-full object-cover shadow-md relative z-10 group-hover:scale-105 transition-transform duration-300 border-4 border-white group-hover:border-[#76B340]"
                >
              </div>
              <h4 class="font-bold text-xl text-gray-800 group-hover:text-[#76B340] transition-colors">Nabila Nurshani</h4>
              <p class="text-gray-500 font-medium">Pengurus</p>
            </div>

            <div class="group flex flex-col items-center text-center w-52">
              <div class="relative mb-6">
                <div class="absolute inset-0 bg-[#EB9232] rounded-full opacity-0 group-hover:opacity-20 transform scale-90 group-hover:scale-110 transition-all duration-300" />
                <img
                  :src="assets.pengurus"
                  alt="Andi Rahmadi"
                  class="w-36 h-36 rounded-full object-cover shadow-md relative z-10 group-hover:scale-105 transition-transform duration-300 border-4 border-white group-hover:border-[#EB9232]"
                >
              </div>
              <h4 class="font-bold text-xl text-gray-800 group-hover:text-[#EB9232] transition-colors">Andi Rahmadi</h4>
              <p class="text-gray-500 font-medium">Pendiri</p>
            </div>

            <div class="group flex flex-col items-center text-center w-52">
              <div class="relative mb-6">
                <div class="absolute inset-0 bg-[#76B340] rounded-full opacity-0 group-hover:opacity-20 transform scale-90 group-hover:scale-110 transition-all duration-300" />
                <img
                  :src="assets.pengurus"
                  alt="Hanifah Ahmad"
                  class="w-36 h-36 rounded-full object-cover shadow-md relative z-10 group-hover:scale-105 transition-transform duration-300 border-4 border-white group-hover:border-[#76B340]"
                >
              </div>
              <h4 class="font-bold text-xl text-gray-800 group-hover:text-[#76B340] transition-colors">Hanifah Ahmad</h4>
              <p class="text-gray-500 font-medium">Pengurus</p>
            </div>
          </div>
        </div>
      </section>

      <section class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-3xl font-bold text-center text-[#76B340] mb-12">
            Mari Berkontribusi Membangun Harapan!
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
            <div class="group bg-white border-2 border-gray-100 rounded-2xl p-8 text-center shadow-sm transition-all duration-300 hover:shadow-xl hover:border-[#76B340] hover:-translate-y-2">
              <div class="bg-green-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-[#76B340] transition-colors duration-300">
                <img
                  :src="assets.donasi"
                  alt="Donasi"
                  class="h-20 w-20 rounded-full object-cover"
                >
              </div>
              <h3 class="font-bold text-2xl text-gray-800 group-hover:text-[#76B340] mb-3 transition-colors">DONASI</h3>
              <p class="text-gray-600 mb-8 leading-relaxed">Mari ikut berdonasi untuk membantu mereka yang membutuhkan. Dukungan Anda sangat berarti.</p>
              <a
                href="#"
                class="inline-block w-full bg-[#76B340] text-white px-6 py-3 rounded-xl text-base font-bold shadow-md hover:bg-[#659a35] hover:shadow-lg active:scale-95 transition-all duration-200"
              >
                Lihat Cara Berdonasi
              </a>
            </div>

            <div class="group bg-white border-2 border-gray-100 rounded-2xl p-8 text-center shadow-sm transition-all duration-300 hover:shadow-xl hover:border-[#EB9232] hover:-translate-y-2">
              <div class="bg-orange-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-[#EB9232] transition-colors duration-300">
                <img
                  :src="assets.relawan"
                  alt="Relawan"
                  class="h-20 w-20 rounded-full object-cover"
                >
              </div>
              <h3 class="font-bold text-2xl text-gray-800 group-hover:text-[#EB9232] mb-3 transition-colors">RELAWAN</h3>
              <p class="text-gray-600 mb-8 leading-relaxed">Mari ikut menjadi relawan untuk membantu mereka yang membutuhkan. Bantuan Anda sangat dinanti.</p>
              <a
                href="#"
                class="inline-block w-full bg-[#EB9232] text-white px-6 py-3 rounded-xl text-base font-bold shadow-md hover:bg-[#d6822a] hover:shadow-lg active:scale-95 transition-all duration-200"
              >
                Lihat Cara Mendaftar
              </a>
            </div>
          </div>
        </div>
      </section>

      <footer id="kontak" class="bg-[#76B340] text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-8 md:pl-40 grid grid-cols-1 md:grid-cols-3 gap-12">
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
                Gang Anggrek No 18, RT.11/RW.6, Rambutan, Kec. Ciracas, Kota Jakarta Timur, DKI Jakarta 13830
              </span>
            </a>
          </div>

          <div class="group">
            <h4 class="font-bold text-xl mb-6 flex items-center gap-2">
              Kontak Kami
            </h4>
            <a
              href="https://wa.me/628123456789"
              target="_blank"
              class="mb-3 flex items-center gap-3 hover:translate-x-2 transition-transform duration-200 cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
              </svg>
              <span class="truncate">+62 812 3456 789</span>
            </a>
            <a
              href="mailto:terminalpintar@gmail.com"
              class="mb-3 flex items-center gap-3 hover:translate-x-2 transition-transform duration-200 cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span class="break-all">terminalpintar@gmail.com</span>
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
              href="https://youtube.com/@terminalpintar"
              target="_blank"
              class="flex items-center gap-3 mb-3 hover:text-green-100 hover:translate-x-2 transition-all duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
              </svg>
              @terminalpintar
            </a>
            <a
              href="https://tiktok.com/@terminalpintar"
              target="_blank"
              class="flex items-center gap-3 mb-3 hover:text-green-100 hover:translate-x-2 transition-all duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.49-3.35-3.98-5.6-.47-2.24.08-4.54 1.5-6.33 1.44-1.82 3.63-2.95 5.93-3.02v4.02c-1.32-.07-2.67.55-3.47 1.63-.82 1.11-1.02 2.58-.56 3.86.47 1.31 1.6 2.34 2.96 2.69 1.35.35 2.83.03 3.91-.93.76-.68 1.21-1.65 1.26-2.66V.02h-2.27z" />
              </svg>
              Terminal Pintar
            </a>
          </div>
        </div>

        <div class="mt-16 border-t border-green-700 pt-8 text-center text-sm text-white/80">
          © {{ new Date().getFullYear() }} Terminal Pintar. All rights reserved.
        </div>
      </footer>
    </div>
  </PublicLayout>
</template>
