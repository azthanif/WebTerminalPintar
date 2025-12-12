<!-- <script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const sidebarOpen = ref(false)
const profileMenuOpen = ref(false)
const studentMenuOpen = ref(false) // State untuk dropdown anak

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

// --- LOGIKA DATA USER & SISWA ---
const userName = computed(() => page.props.auth?.user?.name ?? 'Orang Tua')
const userEmail = computed(() => page.props.auth?.user?.email ?? '-')
const userInitials = computed(() => userName.value.charAt(0).toUpperCase())

// Ambil data dari Middleware HandleInertiaRequests
const myStudents = computed(() => page.props.auth?.students ?? [])
const activeStudentId = computed(() => page.props.auth?.active_student_id)

// Cari nama anak yang sedang aktif
const activeStudentName = computed(() => {
  if (!myStudents.value.length) return 'Belum ada siswa'
  // Jika ada ID di session, cari namanya. Jika tidak, ambil nama anak pertama.
  const active = myStudents.value.find(s => s.id === activeStudentId.value) 
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
    preserveScroll: true, // Jangan scroll ke atas
    onSuccess: () => {
       // Opsional: Beri notifikasi atau refresh halaman parsial
    }
  })
}

const logout = () => {
  router.post(route('logout'))
  profileMenuOpen.value = false
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
        class="fixed inset-0 z-40 bg-black/30 lg:hidden"
        @click="closeSidebar"
      />

      <aside
        :class="[
          'fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-100 transform transition-transform duration-300 ease-in-out flex flex-col shadow-lg shadow-black/5',
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
          <p class="text-slate-500 truncate">{{ userEmail }}</p>
        </div>
      </aside>

      <div class="flex-1 lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-slate-100 bg-white/80 backdrop-blur">
          <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
              <button class="rounded-xl border border-slate-200 p-2 text-slate-600 lg:hidden" @click="toggleSidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Portal Orang Tua</p>
                <p class="text-lg font-semibold text-slate-900">Dashboard</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              
              <div class="relative" v-if="myStudents.length > 0">
                <button
                  @click="studentMenuOpen = !studentMenuOpen; profileMenuOpen = false"
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
              <div class="relative">
                <button
                  class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-left text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition"
                  @click="profileMenuOpen = !profileMenuOpen; studentMenuOpen = false"
                >
                  <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 font-semibold">
                    {{ userInitials }}
                  </span>
                  <span class="hidden text-sm lg:block">{{ userName }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                  </svg>
                </button>

                <div
                  v-if="profileMenuOpen"
                  class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-100 bg-white p-2 text-sm shadow-xl z-50 origin-top-right"
                >
                  <div class="border-b border-slate-100 px-3 py-2 mb-1">
                    <p class="text-xs font-semibold text-slate-900">Akun Pengguna</p>
                    <p class="text-xs text-slate-500 truncate">{{ userEmail }}</p>
                  </div>
                  
                  <button
                    @click="logout"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-rose-600 hover:bg-rose-50 transition-colors"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l3 3-3 3M15 12H3" />
                    </svg>
                    Keluar
                  </button>
                </div>

                <div v-if="profileMenuOpen" class="fixed inset-0 z-40 bg-transparent" @click="profileMenuOpen = false"></div>
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
</template> -->

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'

const page = usePage()
const sidebarOpen = ref(false)
const profileMenuOpen = ref(false)
const studentMenuOpen = ref(false)

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
      icon: 'M4.5 5.25h15v13.5H4.5zM9 8.25h6M9 12h6M9 15.75h4.5',
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

// --- LOGIKA DATA USER & SISWA ---
const userName = computed(() => page.props.auth?.user?.name ?? 'Orang Tua')
const userEmail = computed(() => page.props.auth?.user?.email ?? '-')
const userInitials = computed(() => userName.value.charAt(0).toUpperCase())

// Ambil data dari Middleware HandleInertiaRequests
// PENTING: Data ada di auth.user, bukan langsung di auth
const myStudents = computed(() => {
  const students = page.props.auth?.user?.students ?? []
  console.log('📚 Students Data:', students) // Debug
  return Array.isArray(students) ? students : []
})

const activeStudentId = computed(() => {
  const id = page.props.auth?.user?.active_student_id
  console.log('✅ Active Student ID:', id) // Debug
  return id
})

// Cari nama anak yang sedang aktif
const activeStudentName = computed(() => {
  if (!myStudents.value.length) {
    console.log('⚠️ Tidak ada siswa')
    return 'Belum ada siswa'
  }
  
  // Gunakan == untuk perbandingan yang lebih fleksibel (number vs string)
  const active = myStudents.value.find(s => s.id == activeStudentId.value) 
                 ?? myStudents.value[0]
  
  console.log('👤 Active Student:', active)
  return active?.name ?? 'Siswa'
})

// Watch untuk debugging
watch([myStudents, activeStudentId], ([students, activeId]) => {
  console.log('🔄 Data berubah:', { 
    studentsCount: students.length, 
    activeId,
    students 
  })
}, { immediate: true })

// Fungsi Ganti Siswa
const switchStudent = (studentId) => {
  console.log('🔀 Switching to student:', studentId)
  
  // Tutup menu
  studentMenuOpen.value = false
  
  // Kirim request POST ke backend
  router.post(route('orang-tua.switch.student'), {
    student_id: studentId
  }, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('✅ Switch berhasil')
    },
    onError: (errors) => {
      console.error('❌ Switch gagal:', errors)
    }
  })
}

const logout = () => {
  router.post(route('logout'))
  profileMenuOpen.value = false
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
        class="fixed inset-0 z-40 bg-black/30 lg:hidden"
        @click="closeSidebar"
      />

      <aside
        :class="[
          'fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-100 transform transition-transform duration-300 ease-in-out flex flex-col shadow-lg shadow-black/5',
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
          <p class="text-slate-500 truncate">{{ userEmail }}</p>
        </div>
      </aside>

      <div class="flex-1 lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-slate-100 bg-white/80 backdrop-blur">
          <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
              <button class="rounded-xl border border-slate-200 p-2 text-slate-600 lg:hidden" @click="toggleSidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>
              <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Portal Orang Tua</p>
                <p class="text-lg font-semibold text-slate-900">Dashboard</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              
              <!-- Debug Info (hapus setelah selesai debugging) -->
              <div class="text-xs text-slate-400 hidden">
                Students: {{ myStudents.length }} | Active: {{ activeStudentId }}
              </div>
              
              <!-- Dropdown Siswa -->
              <div class="relative" v-if="myStudents.length > 0">
                <button
                  @click="studentMenuOpen = !studentMenuOpen; profileMenuOpen = false"
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
                  <p class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    Pilih Profil Siswa ({{ myStudents.length }})
                  </p>
                  
                  <button
                    v-for="student in myStudents"
                    :key="student.id"
                    @click="switchStudent(student.id)"
                    class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-left transition-colors"
                    :class="student.id == activeStudentId ? 'bg-emerald-50 text-emerald-700' : 'hover:bg-slate-50 text-slate-600'"
                  >
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold"
                             :class="student.id == activeStudentId ? 'bg-emerald-200 text-emerald-700' : 'bg-slate-200 text-slate-600'">
                            {{ student.name.charAt(0) }}
                        </div>
                        <span class="font-medium text-sm">{{ student.name }}</span>
                    </div>
                    
                    <svg v-if="student.id == activeStudentId" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
                
                <div v-if="studentMenuOpen" class="fixed inset-0 z-40 bg-transparent" @click="studentMenuOpen = false"></div>
              </div>
              
              <!-- Jika tidak ada siswa, tampilkan pesan -->
              <div v-else class="text-sm text-slate-500 italic px-3 py-1.5 bg-amber-50 rounded-full border border-amber-200">
                Belum ada siswa terdaftar
              </div>
              
              <div class="relative">
                <button
                  class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-left text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition"
                  @click="profileMenuOpen = !profileMenuOpen; studentMenuOpen = false"
                >
                  <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 font-semibold">
                    {{ userInitials }}
                  </span>
                  <span class="hidden text-sm lg:block">{{ userName }}</span>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                  </svg>
                </button>

                <div
                  v-if="profileMenuOpen"
                  class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-100 bg-white p-2 text-sm shadow-xl z-50 origin-top-right"
                >
                  <div class="border-b border-slate-100 px-3 py-2 mb-1">
                    <p class="text-xs font-semibold text-slate-900">Akun Pengguna</p>
                    <p class="text-xs text-slate-500 truncate">{{ userEmail }}</p>
                  </div>
                  
                  <button
                    @click="logout"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-rose-600 hover:bg-rose-50 transition-colors"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l3 3-3 3M15 12H3" />
                    </svg>
                    Keluar
                  </button>
                </div>

                <div v-if="profileMenuOpen" class="fixed inset-0 z-40 bg-transparent" @click="profileMenuOpen = false"></div>
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