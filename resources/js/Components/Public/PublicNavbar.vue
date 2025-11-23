<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const isMobileMenuOpen = ref(false)
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const page = usePage()
const user = page.props.auth?.user ?? null
</script>

<template>
  <nav class="bg-white shadow-md sticky top-0 z-40">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
      <div class="text-2xl font-bold text-green-700">
        <Link :href="route('public.home')">Terminal Pintar</Link>
      </div>

      <div class="hidden md:flex space-x-6 items-center">
        <Link
          :href="route('public.home')"
          class="text-gray-600 hover:text-green-700"
        >
          Beranda
        </Link>
        <Link
          :href="route('public.news.index')"
          class="text-gray-600 hover:text-green-700"
        >
          Berita & Dokumentasi
        </Link>
        <Link
          :href="route('public.about')"
          class="text-gray-600 hover:text-green-700"
        >
          Tentang Kami
        </Link>
        <Link
          :href="route('public.contact')"
          class="text-gray-600 hover:text-green-700"
        >
          Kontak
        </Link>
      </div>

      <div class="hidden md:flex items-center space-x-4">
        <div v-if="user" class="flex items-center space-x-2">
          <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs font-semibold">
            {{ user.name?.charAt(0).toUpperCase() }}
          </div>
          <div class="text-sm">
            <div class="font-medium">{{ user.name }}</div>
            <div class="text-gray-500 text-xs">
              {{ user.nama_role || 'Pengguna' }}
            </div>
          </div>
        </div>
        <Link
          v-else
          :href="route('login')"
          class="px-4 py-2 rounded-lg border border-green-600 text-green-700 hover:bg-green-50 text-sm"
        >
          Masuk
        </Link>
      </div>

      <!-- Mobile toggle -->
      <button class="md:hidden text-gray-600" @click="toggleMobileMenu">
        ☰
      </button>
    </div>

    <!-- Mobile menu -->
    <div v-if="isMobileMenuOpen" class="md:hidden border-t bg-white">
      <div class="px-6 py-3 space-y-2">
        <Link
          :href="route('public.home')"
          class="block text-gray-700 hover:text-green-700"
        >
          Beranda
        </Link>
        <Link
          :href="route('public.news.index')"
          class="block text-gray-700 hover:text-green-700"
        >
          Berita & Dokumentasi
        </Link>
        <Link
          :href="route('public.about')"
          class="block text-gray-700 hover:text-green-700"
        >
          Tentang Kami
        </Link>
        <Link
          :href="route('public.contact')"
          class="block text-gray-700 hover:text-green-700"
        >
          Kontak
        </Link>
        <Link
          v-if="!user"
          :href="route('login')"
          class="block mt-2 text-green-700 font-medium"
        >
          Masuk
        </Link>
      </div>
    </div>
  </nav>
</template>
