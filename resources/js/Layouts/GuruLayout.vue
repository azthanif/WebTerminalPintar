<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const page = usePage()

const sidebarOpen = ref(false)
const profileMenuOpen = ref(false)
const flashVisible = ref(false)

const user = computed(() => page.props.auth?.user ?? null)
const flash = computed(() => page.props.flash ?? {})

const navigation = [
  {
    label: 'Dashboard',
    routeName: 'guru.dashboard',
    patterns: ['guru.dashboard'],
    icon: 'M3 13.5l9-9 9 9M5.25 12V20.25H9.75V15.75H14.25V20.25H18.75V12',
  },
  {
    label: 'Jadwal & Materi',
    routeName: 'guru.schedules',
    patterns: ['guru.schedules'],
    icon: 'M4.5 5.25h15v13.5H4.5zM9 8.25h6M9 12h6M9 15.75h4.5',
  },
  {
    label: 'Kehadiran',
    routeName: 'guru.attendance',
    patterns: ['guru.attendance'],
    icon: 'M4.5 5.25h15M4.5 12h15M4.5 18.75h15',
  },
]

const isActive = item => {
  if (!item.patterns) return false
  return item.patterns.some(pattern => {
    try {
      return route().current(pattern)
    } catch (error) {
      return false
    }
  })
}

const navHref = item => {
  if (!item.routeName) {
    return item.href || '#'
  }
  try {
    return route(item.routeName)
  } catch (error) {
    return item.href || '#'
  }
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const toggleProfileMenu = () => {
  profileMenuOpen.value = !profileMenuOpen.value
}

const logout = () => {
  router.post(route('logout'))
  profileMenuOpen.value = false
}

watch(
  () => [flash.value?.success, flash.value?.error],
  ([success, error]) => {
    if (success || error) {
      flashVisible.value = true

      setTimeout(() => {
        flashVisible.value = false
      }, 3500)
    }
  },
  { immediate: true }
)
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <div class="flex min-h-screen">
      <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/30 lg:hidden" @click="closeSidebar" />

      <aside
        :class="[
          'fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-slate-100 transform transition-transform duration-300 ease-in-out flex flex-col',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        ]"
      >
        <div class="flex h-20 items-center justify-between border-b border-slate-100 px-6">
          <div>
            <p class="text-xs uppercase tracking-widest text-slate-400">Terminal Pintar</p>
            <p class="text-lg font-semibold text-slate-900">Portal Guru</p>
          </div>
          <button class="text-slate-500 hover:text-slate-700 lg:hidden" @click="closeSidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
          <Link
            v-for="item in navigation"
            :key="item.label"
            :href="navHref(item)"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition"
            :class="
              isActive(item)
                ? 'bg-emerald-50 text-emerald-600'
                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
            "
            @click="closeSidebar"
          >
            <span
              class="flex h-9 w-9 items-center justify-center rounded-lg"
              :class="
                isActive(item)
                  ? 'bg-emerald-100 text-emerald-600'
                  : 'bg-slate-100 text-slate-500'
              "
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
              </svg>
            </span>
            <span>{{ item.label }}</span>
          </Link>
        </nav>

        <div class="border-t border-slate-100 px-6 py-5 text-sm">
          <p class="text-xs uppercase tracking-widest text-slate-400">Masuk sebagai</p>
          <p class="mt-1 font-semibold text-slate-900">{{ user?.name || 'Guru' }}</p>
          <p class="text-slate-500">{{ user?.email }}</p>
        </div>
      </aside>

      <div class="flex-1 lg:pl-72">
        <header class="sticky top-0 z-20 border-b border-slate-100 bg-white/70 backdrop-blur">
          <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:px-10">
            <div class="flex items-center gap-3">
              <button class="rounded-xl border border-slate-200 p-2 text-slate-600 lg:hidden" @click="toggleSidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <div>
                <p class="text-xs uppercase tracking-widest text-slate-400">Modul Guru</p>
                <p class="text-lg font-semibold text-slate-900">{{ page.props.title || 'Dashboard Guru' }}</p>
              </div>
            </div>

            <div class="flex items-center gap-4">
              <div class="relative">
                <button
                  class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-left text-sm font-medium text-slate-700 shadow-sm"
                  @click="toggleProfileMenu"
                >
                  <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 font-semibold">
                    {{ user?.name?.charAt(0)?.toUpperCase() || 'G' }}
                  </span>
                  <span class="hidden text-sm lg:block">{{ user?.name || 'Guru' }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                  </svg>
                </button>

                <div
                  v-if="profileMenuOpen"
                  class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-100 bg-white p-2 text-sm shadow-xl"
                >
                  <p class="px-3 py-2 text-xs uppercase tracking-widest text-slate-400">{{ user?.email }}</p>
                  <button
                    @click="logout"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-rose-600 hover:bg-rose-50"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l3 3-3 3M15 12H3" />
                    </svg>
                    Keluar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </header>

        <main class="px-4 py-6 sm:px-6 lg:px-10">
          <transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
          >
            <div
              v-if="flashVisible && (flash.success || flash.error)"
              :class="[
                'mb-6 flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm shadow-sm',
                flash.success ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-rose-100 bg-rose-50 text-rose-700',
              ]"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path v-if="flash.success" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="font-semibold">{{ flash.success ? 'Berhasil' : 'Terjadi Kesalahan' }}</p>
                <p>{{ flash.success || flash.error }}</p>
              </div>
            </div>
          </transition>

          <div v-if="$slots['header-actions']" class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <slot name="header-actions" />
          </div>

          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
