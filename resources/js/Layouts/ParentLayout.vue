<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const sidebarOpen = ref(false)

const navigation = computed(() => {
  const currentUrl = page.url

  const baseItems = [
    {
      name: 'Beranda',
      href: route('orang-tua.dashboard'),
      patterns: ['orang-tua.dashboard'],
      icon: 'M3 13.5l9-9 9 9M5.25 12V20.25H9.75V15.75H14.25V20.25H18.75V12',
    },
    {
      name: 'Jadwal',
      href: route('orang-tua.schedules.index'),
      patterns: ['orang-tua.schedules.*'],
      icon: 'M4.5 5.25h15M4.5 12h15M4.5 18.75h15',
    },
    {
      name: 'Catatan Guru',
      href: route('orang-tua.notes.index'),
      patterns: ['orang-tua.notes.*'],
      icon: 'M4 6h16M4 12h16M4 18h16',
    },
  ]

  return baseItems.map((item) => ({
    ...item,
    active: !!currentUrl && (item.patterns?.some((pattern) => route().current(pattern)) ?? false),
  }))
})

const userName = computed(() => page.props.auth?.user?.name ?? 'Orang Tua')
const userEmail = computed(() => page.props.auth?.user?.email ?? '-')

const logout = () => {
  router.post(route('logout'))
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <Head :title="navigation.find(item => item.active)?.name ?? 'Orang Tua'" />

    <div class="flex min-h-screen">
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 z-30 bg-black/30 lg:hidden"
        @click="closeSidebar"
      />

      <aside
        :class="[
          'fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-slate-100 transform transition-transform duration-300 ease-in-out flex flex-col shadow-lg shadow-black/5',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        ]"
      >
        <div class="flex items-center justify-between px-6 h-20 border-b border-slate-100">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Terminal Pintar</p>
            <p class="text-lg font-semibold text-slate-900">Orang Tua</p>
          </div>
          <button class="lg:hidden text-slate-500 hover:text-slate-700" @click="closeSidebar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition"
            :class="item.active ? 'bg-emerald-50 text-emerald-600' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'"
            @click="closeSidebar"
          >
            <span
              class="flex h-9 w-9 items-center justify-center rounded-lg"
              :class="item.active ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
              </svg>
            </span>
            <span>{{ item.name }}</span>
          </Link>
        </nav>

        <div class="border-t border-slate-100 px-6 py-5 text-sm">
          <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Masuk sebagai</p>
          <p class="mt-1 font-semibold text-slate-900">{{ userName }}</p>
          <p class="text-slate-500">{{ userEmail }}</p>
          <button
            class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-rose-500 hover:text-rose-600"
            @click="logout"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l-3 3m0 0l3 3m-3-3h12.75" />
            </svg>
            Keluar
          </button>
        </div>
      </aside>

      <div class="flex-1 lg:pl-72">
        <header class="sticky top-0 z-20 border-b border-slate-100 bg-white/80 backdrop-blur">
          <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
              <button class="rounded-xl border border-slate-200 p-2 text-slate-600 lg:hidden" @click="toggleSidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Halo</p>
                <p class="text-lg font-semibold text-slate-900">{{ userName }}</p>
              </div>
            </div>
          </div>
        </header>

        <main class="px-6 py-10">
          <slot />
        </main>
      </div>
    </div>
  </div>
</template>
