<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'

const isMobileMenuOpen = ref(false)
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const page = usePage()
const user = page.props.auth?.user ?? null
</script>

<template>
  <nav class="bg-white/95 backdrop-blur-sm shadow-md sticky top-0 z-50 transition-all duration-300">
    <div class="container mx-auto px-4 sm:px-6 py-4">
      <div class="flex justify-between items-center">
        <!-- Logo -->
        <Link 
          :href="route('public.home')" 
          class="group flex items-center gap-3"
        >
          <img 
            :src="'/images/logo-terminal-pintar.png'" 
            alt="Logo Terminal Pintar" 
            class="h-16 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
          />
        </Link>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex space-x-1 items-center">
          <Link
            :href="route('public.home')"
            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-[#84994F] hover:bg-[#84994F]/10 rounded-lg transition-all duration-300 relative group"
          >
            Beranda
            <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-[#84994F] group-hover:w-3/4 transition-all duration-300"></span>
          </Link>
          <Link
            :href="route('public.news.index')"
            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-[#84994F] hover:bg-[#84994F]/10 rounded-lg transition-all duration-300 relative group"
          >
            Berita & Dokumentasi
            <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-[#84994F] group-hover:w-3/4 transition-all duration-300"></span>
          </Link>
          <Link
            :href="route('public.about')"
            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-[#84994F] hover:bg-[#84994F]/10 rounded-lg transition-all duration-300 relative group"
          >
            Tentang Kami
            <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-[#84994F] group-hover:w-3/4 transition-all duration-300"></span>
          </Link>
          <Link
            :href="route('public.contact')"
            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-[#84994F] hover:bg-[#84994F]/10 rounded-lg transition-all duration-300 relative group"
          >
            Kontak
            <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-[#84994F] group-hover:w-3/4 transition-all duration-300"></span>
          </Link>
        </div>

        <!-- Desktop Auth -->
        <div class="hidden md:flex items-center gap-3">
          <div v-if="user" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200">
            <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[#84994F] to-[#6B7D3F] flex items-center justify-center text-xs font-bold text-white shadow-sm">
              {{ user.name?.charAt(0).toUpperCase() }}
            </div>
            <div class="text-sm">
              <div class="font-semibold text-slate-900">{{ user.name }}</div>
              <div class="text-xs text-slate-500">{{ user.nama_role || 'Pengguna' }}</div>
            </div>
          </div>
          <Link
            v-else
            :href="route('login')"
            class="px-6 py-2.5 text-sm font-semibold rounded-lg border-2 border-[#84994F] text-[#84994F] hover:bg-[#84994F] hover:text-white transition-all duration-300 shadow-sm hover:shadow-md active:scale-95"
          >
            Masuk
          </Link>
        </div>

        <!-- Mobile toggle -->
        <button 
          class="md:hidden p-2 text-slate-600 hover:text-[#84994F] hover:bg-slate-100 rounded-lg transition-all duration-300 active:scale-95" 
          @click="toggleMobileMenu"
          :aria-label="isMobileMenuOpen ? 'Close menu' : 'Open menu'"
          :aria-expanded="isMobileMenuOpen"
        >
          <Bars3Icon v-if="!isMobileMenuOpen" class="h-6 w-6" />
          <XMarkIcon v-else class="h-6 w-6" />
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <Transition
      enter-active-class="transition ease-out duration-300 transform"
      enter-from-class="opacity-0 -translate-y-4 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-200 transform"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 -translate-y-4 scale-95"
    >
      <div v-if="isMobileMenuOpen" class="md:hidden border-t bg-white shadow-2xl relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-32 h-32 bg-[#84994F]/5 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-32 h-32 bg-[#84994F]/10 rounded-full blur-2xl"></div>

        <div class="px-5 py-6 space-y-2 relative z-10">
          <div class="flex flex-col gap-1">
            <Link
              v-for="item in [
                { name: 'Beranda', route: 'public.home' },
                { name: 'Berita & Dokumentasi', route: 'public.news.index' },
                { name: 'Tentang Kami', route: 'public.about' },
                { name: 'Kontak', route: 'public.contact' }
              ]"
              :key="item.name"
              :href="route(item.route)"
              class="flex items-center px-4 py-3.5 text-slate-700 hover:text-[#84994F] hover:bg-[#84994F]/5 rounded-xl font-bold transition-all duration-200 group"
              @click="isMobileMenuOpen = false"
            >
              <span class="w-1.5 h-6 bg-[#84994F] rounded-full mr-3 opacity-0 -translate-x-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-x-0"></span>
              {{ item.name }}
            </Link>
          </div>
          
          <!-- User info or login -->
          <div class="pt-4 mt-2 border-t border-slate-100">
            <div v-if="user" class="px-4 py-4 bg-slate-50/80 backdrop-blur-sm rounded-2xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
              <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#84994F] to-[#6B7D3F] flex items-center justify-center text-lg font-bold text-white shadow-lg ring-4 ring-white">
                  {{ user.name?.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="font-extrabold text-slate-900 leading-tight">{{ user.name }}</div>
                  <div class="text-xs font-bold text-[#84994F] mt-0.5 uppercase tracking-wider">{{ user.nama_role || 'Pengguna' }}</div>
                </div>
              </div>
            </div>
            <Link
              v-else
              :href="route('login')"
              class="flex items-center justify-center gap-2 w-full px-6 py-4 mt-2 text-white font-extrabold bg-gradient-to-r from-[#84994F] to-[#6B7D3F] rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5 focus:ring-4 focus:ring-[#84994F]/30 active:scale-95"
              @click="isMobileMenuOpen = false"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Masuk ke Aplikasi
            </Link>
          </div>
        </div>
      </div>
    </Transition>
  </nav>
</template>
