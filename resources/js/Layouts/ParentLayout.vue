<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { 
    HomeIcon, 
    CalendarIcon, 
    ChatBubbleBottomCenterTextIcon,
    UserGroupIcon,
    ChevronDownIcon,
    XMarkIcon,
    UserIcon,
    Bars3Icon
} from '@heroicons/vue/24/outline'

const page = usePage()
const sidebarOpen = ref(false)
const studentMenuOpen = ref(false)
const isScrolled = ref(false) // Scroll state

const navigation = computed(() => {
  const currentUrl = page.url

  const baseItems = [
    {
      name: 'Beranda',
      href: route('orang-tua.dashboard'),
      patterns: ['orang-tua.dashboard'],
      // Using Heroicon component directly
      icon: HomeIcon,
    },
    {
      name: 'Jadwal',
      href: route('orang-tua.schedules.index'),
      patterns: ['orang-tua.schedules.*'],
      icon: CalendarIcon,
    },
    {
      name: 'Catatan Guru',
      href: route('orang-tua.notes.index'),
      patterns: ['orang-tua.notes.*'],
      icon: ChatBubbleBottomCenterTextIcon,
    },
  ]

  return baseItems.map((item) => ({
    ...item,
    active: !!currentUrl && (item.patterns?.some((pattern) => route().current(pattern)) ?? false),
  }))
})

// --- LOGIKA DATA USER & SISWA ---
const userName = computed(() => page.props.auth?.user?.name ?? 'Orang Tua')
const userEmail = computed(() => page.props.auth?.user?.email ?? '-')
const userInitials = computed(() => userName.value.charAt(0).toUpperCase())

// Ambil data dari Middleware HandleInertiaRequests
// PENTING: Data ada di auth.user, bukan langsung di auth
const myStudents = computed(() => {
  const students = page.props.auth?.user?.students ?? []
  return Array.isArray(students) ? students : []
})

const activeStudentId = computed(() => {
  return page.props.auth?.user?.active_student_id
})

// Cari nama anak yang sedang aktif
const activeStudentName = computed(() => {
  if (!myStudents.value.length) {
    return 'Belum ada siswa'
  }
  
  // Gunakan == untuk perbandingan yang lebih fleksibel (number vs string)
  const active = myStudents.value.find(s => s.id == activeStudentId.value) 
                 ?? myStudents.value[0]
  return active?.name ?? 'Siswa'
})

// Fungsi Ganti Siswa
const switchStudent = (studentId) => {
  // Tutup menu
  studentMenuOpen.value = false
  
  // Kirim request POST ke backend
  router.post(route('orang-tua.switch.student'), {
    student_id: studentId
  }, {
    preserveScroll: true,
    onSuccess: () => {},
  })
}

const confirmLogout = () => {
  Swal.fire({
    title: 'Keluar Aplikasi?',
    text: "Apakah Anda yakin ingin mengakhiri sesi ini?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#84994f',
    confirmButtonText: 'Ya, Keluar',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    width: '400px',
    customClass: {
      popup: 'rounded-xl font-sans',
      title: 'text-xl font-bold text-gray-800',
      confirmButton: 'rounded-full px-6 shadow-lg',
      cancelButton: 'rounded-full px-6'
    }
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('logout'))
    }
  })
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const handleScroll = () => {
    isScrolled.value = window.scrollY > 10
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="min-h-screen bg-[var(--color-body)] font-sans text-slate-900">
    <Head :title="navigation.find(item => item.active)?.name ?? 'Orang Tua'" />

    <div class="flex min-h-screen">
      <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/20 lg:hidden" @click="closeSidebar" />

      <aside
        :class="[
          'fixed inset-y-0 left-0 z-50 w-72 bg-[var(--color-primary)] border-r-0 transform transition-transform duration-300 ease-in-out flex flex-col',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        ]"
      >
        <!-- Section A: Brand & Profile (Top) -->
        <div class="flex flex-col gap-6 px-8 py-8 text-white">
          <!-- Brand -->
          <div class="flex items-center gap-3">
             <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-lg p-1">
                <img 
                  :src="'/images/logo-terminal-pintar.png'" 
                  alt="Logo Terminal Pintar" 
                  class="h-full w-full object-contain"
                />
             </div>
             <div>
                <p class="text-xl font-extrabold tracking-tight">Orang Tua</p>
             </div>
             <button class="lg:hidden text-white/70 hover:text-white ml-auto" @click="closeSidebar">
                <XMarkIcon class="h-6 w-6" />
             </button>
          </div>

          <!-- Profile Short (Vertical) -->
          <div class="flex flex-col items-center gap-3 rounded-3xl bg-white/10 p-6 backdrop-blur-sm border border-white/5 transition hover:bg-white/15 group cursor-pointer text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-md ring-4 ring-white/10 group-hover:ring-white/20 transition-all">
              <UserIcon class="h-8 w-8" style="color: #84994F;" />
            </div>
            <div class="w-full overflow-hidden">
              <p class="truncate text-base font-bold tracking-wide text-white">{{ userName }}</p>
              <p class="truncate text-xs text-white/70 font-medium group-hover:text-white transition-colors mt-0.5">{{ userEmail }}</p>
            </div>
          </div>
        </div>

        <!-- Section B: Menu (Middle) -->
        <nav class="flex-1 overflow-y-auto pl-4 py-4 space-y-1">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            class="relative flex items-center gap-4 px-6 py-4 text-sm font-bold transition-all duration-200 rounded-l-full group"
            :class="item.active 
                ? 'bg-[var(--color-body)] text-[var(--color-primary)] z-20' 
                : 'text-white/70 hover:bg-white/10 hover:text-white z-10'"
            @click="closeSidebar"
          >
             <!-- Active Indicator Curves (Top & Bottom) -->
             <div v-if="item.active" class="absolute right-0 -top-5 w-5 h-5 bg-transparent rounded-br-3xl shadow-[5px_5px_0_0_var(--color-body)] pointer-events-none"></div>
             <div v-if="item.active" class="absolute right-0 -bottom-5 w-5 h-5 bg-transparent rounded-tr-3xl shadow-[5px_-5px_0_0_var(--color-body)] pointer-events-none"></div>

            <component 
                :is="item.icon" 
                class="h-6 w-6 transition-transform duration-300 group-hover:scale-110"
                :class="item.active ? 'text-[var(--color-primary)]' : 'text-current opacity-70 group-hover:opacity-100'"
            />
            <span class="tracking-wide">{{ item.name }}</span>
          </Link>
        </nav>

        <!-- Section C: Logout (Bottom) -->
        <div class="p-6">
           <button @click="confirmLogout" class="group flex w-full items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-sm font-medium text-white transition-all hover:bg-white hover:text-[var(--color-danger)] hover:border-white shadow-sm hover:shadow-md active:scale-95 duration-200">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
               <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
               <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l3 3-3 3M15 12H3" />
             </svg>
             <span>Keluar Aplikasi</span>
           </button>
           <p class="mt-4 text-center text-[10px] text-white/30">
               &copy; 2025 Terminal Pintar. Semua Hak Dilindungi.
           </p>
        </div>
      </aside>

      <div class="flex-1 lg:pl-72 bg-[var(--color-body)] transition-all duration-300">
        <!-- Header (Glassmorphism Scroll Effect) -->
        <header 
            class="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-8 py-3 sm:py-5 transition-all duration-300"
            :class="isScrolled ? 'bg-white/80 backdrop-blur-md shadow-sm py-2 sm:py-4 border-b border-slate-100' : 'bg-transparent border-transparent'"
        >
          <div class="flex items-center gap-4">
             <button
              class="rounded-xl border border-slate-200 p-2 text-slate-600 shadow-sm hover:bg-slate-50 lg:hidden hover:scale-105 active:scale-95 transition-transform"
              @click="toggleSidebar"
            >
              <Bars3Icon class="h-5 w-5" />
            </button>
             <div>
                <h2 class="page-title text-xl sm:text-2xl md:text-3xl font-bold tracking-tight leading-tight bg-gradient-to-r from-slate-800 via-[var(--color-primary)] to-slate-800 bg-clip-text text-transparent bg-[length:200%_100%] animate-gradient-shift hover:scale-105 transition-transform duration-300 cursor-default">
                {{ navigation.find(item => item.active)?.name || 'Dashboard' }}
              </h2>
             </div>
          </div>

          <div class="flex items-center gap-3">
              
              <div class="relative" v-if="myStudents.length > 0">
                <button
                  @click="studentMenuOpen = !studentMenuOpen"
                  class="flex items-center gap-2 rounded-full border border-slate-200 bg-white pl-4 pr-2 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition"
                >
                  <div class="flex flex-col items-end leading-none mr-1">
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider">Siswa</span>
                    <span class="text-emerald-600 font-bold">{{ activeStudentName }}</span>
                  </div>
                  <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </button>

                <div
                  v-if="studentMenuOpen"
                  class="absolute right-0 mt-2 w-64 rounded-xl border border-slate-100 bg-white p-2 shadow-xl z-50 origin-top-right"
                >
                  <p class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Pilih Profil Siswa</p>
                  
                  <button
                    v-for="student in myStudents"
                    :key="student.id"
                    @click="switchStudent(student.id)"
                    class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left transition-colors"
                    :class="student.name === activeStudentName ? 'bg-emerald-50 text-emerald-700' : 'hover:bg-slate-50 text-slate-600'"
                  >
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold"
                             :class="student.name === activeStudentName ? 'bg-emerald-200 text-emerald-700' : 'bg-slate-200 text-slate-600'">
                            {{ student.name.charAt(0) }}
                        </div>
                        <span class="font-medium text-sm">{{ student.name }}</span>
                    </div>
                    
                    <svg v-if="student.name === activeStudentName" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
                
                <div v-if="studentMenuOpen" class="fixed inset-0 z-40 bg-transparent" @click="studentMenuOpen = false"></div>
              </div>
          </div>
        </header>

        <main class="flex-1 overflow-y-auto px-4 sm:px-8 pb-8 pt-2 scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent">
          <Transition name="page" mode="out-in">
             <div :key="$page.url">
                <slot />
             </div>
          </Transition>
        </main>
      </div>
    </div>
  </div>
</template>