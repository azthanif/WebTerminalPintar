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
          class="group flex items-center gap-2"
        >
          <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-[#84994F] to-[#6B7D3F] flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300 group-hover:scale-105">
            <span class="text-white font-bold text-xl">TP</span>
          </div>
          <div>
            <span class="text-xl font-bold bg-gradient-to-r from-slate-800 to-[#84994F] bg-clip-text text-transparent">
              Terminal Pintar
            </span>
            <div class="text-[10px] text-slate-500 font-medium -mt-0.5">Learning Platform</div>
          </div>
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
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div v-if="isMobileMenuOpen" class="md:hidden border-t bg-white shadow-lg">
        <div class="px-4 py-3 space-y-1">
          <Link
            :href="route('public.home')"
            class="block px-4 py-3 text-slate-700 hover:text-[#84994F] hover:bg-[#84994F]/10 rounded-lg font-medium transition-all duration-200"
            @click="isMobileMenuOpen = false"
          >
            Beranda
          </Link>
          <Link
            :href="route('public.news.index')"
            class="block px-4 py-3 text-slate-700 hover:text-[#84994F] hover:bg-[#84994F]/10 rounded-lg font-medium transition-all duration-200"
            @click="isMobileMenuOpen = false"
          >
            Berita & Dokumentasi
          </Link>
          <Link
            :href="route('public.about')"
            class="block px-4 py-3 text-slate-700 hover:text-[#84994F] hover:bg-[#84994F]/10 rounded-lg font-medium transition-all duration-200"
            @click="isMobileMenuOpen = false"
          >
            Tentang Kami
          </Link>
          <Link
            :href="route('public.contact')"
            class="block px-4 py-3 text-slate-700 hover:text-[#84994F] hover:bg-[#84994F]/10 rounded-lg font-medium transition-all duration-200"
            @click="isMobileMenuOpen = false"
          >
            Kontak
          </Link>
          
          <!-- User info or login -->
          <div class="pt-3 border-t">
            <div v-if="user" class="px-4 py-3 bg-slate-50 rounded-lg">
              <div class="flex items-center gap-2">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-[#84994F] to-[#6B7D3F] flex items-center justify-center text-sm font-bold text-white">
                  {{ user.name?.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="font-semibold text-slate-900">{{ user.name }}</div>
                  <div class="text-xs text-slate-500">{{ user.nama_role || 'Pengguna' }}</div>
                </div>
              </div>
            </div>
            <Link
              v-else
              :href="route('login')"
              class="block px-4 py-3 text-[#84994F] font-semibold text-center bg-[#84994F]/10 hover:bg-[#84994F] hover:text-white rounded-lg transition-all duration-200"
              @click="isMobileMenuOpen = false"
            >
              Masuk
            </Link>
          </div>
        </div>
      </div>
    </Transition>
  </nav>
</template>
