```
<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
import { 
    HomeIcon, 
    UsersIcon, 
    AcademicCapIcon, 
    NewspaperIcon, 
    BookOpenIcon,
    XMarkIcon,
    Bars3Icon,
    ChevronDownIcon,
    ArrowLeftOnRectangleIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    UserIcon
} from '@heroicons/vue/24/outline'

const page = usePage()

const sidebarOpen = ref(false)
const profileMenuOpen = ref(false)
const flashVisible = ref(false)
const isScrolled = ref(false) // Scroll state

const user = computed(() => page.props.auth?.user ?? null)
const flash = computed(() => page.props.flash ?? {})

const navigation = [
	{
		label: 'Dashboard',
		routeName: 'admin.dashboard',
		patterns: ['admin.dashboard'],
		icon: HomeIcon,
	},
	{
		label: 'Pengguna',
		routeName: 'admin.users.index',
		patterns: ['admin.users.*'],
		icon: UsersIcon,
	},
	{
		label: 'Siswa',
		routeName: 'admin.students.index',
		patterns: ['admin.students.*'],
		icon: AcademicCapIcon,
	},
	{
		label: 'Berita & Dokumentasi',
		routeName: 'admin.berita.index',
		patterns: ['admin.berita.*'],
		icon: NewspaperIcon,
	},
	{
		label: 'Perpustakaan',
		routeName: 'admin.books.index',
		patterns: ['admin.books.*', 'admin.loans.*', 'admin.perpustakaan.*'],
		icon: BookOpenIcon,
	},
]

const isActive = (item) => {
	if (!item.patterns) {
		return false
	}

	return item.patterns.some((pattern) => {
		try {
			return route().current(pattern)
		} catch (error) {
			return false
		}
	})
}

const navHref = (item) => {
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
	profileMenuOpen.value = false
}

// Header Scroll Effect Logic
const handleScroll = () => {
    isScrolled.value = window.scrollY > 10
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})

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
	{ immediate: true },
)
</script>

<template>
	<div class="min-h-screen bg-[#F5F5F4] font-sans text-slate-900">
		<div class="flex min-h-screen">
			<!-- Off-canvas backdrop -->
			<div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/20 lg:hidden" @click="closeSidebar" />

			<!-- Sidebar -->
			<aside :class="[
				'fixed inset-y-0 left-0 z-40 w-72 h-screen bg-[var(--color-primary)] border-r-0 transition-transform duration-300 ease-in-out flex flex-col overflow-hidden',
				sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
			]">
				<!-- Section A: Brand & Profile (Top) -->
				<div class="flex flex-col gap-6 px-8 py-8 text-white">
					<!-- Brand -->
					<div class="flex items-center gap-3">
						<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-white backdrop-blur-md shadow-inner">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75v-4.5m0 4.5h4.5m-4.5 0l6-6m-9.75 5.25v1.5h1.5m-1.5-1.5l-6-6m16.5 7.5v6a2.25 2.25 0 01-2.25 2.25h-15A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
							</svg>
						</div>
						<div>
							<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Terminal Pintar</p>
							<p class="text-xl font-extrabold tracking-tight">Panel Admin</p>
						</div>
						<button class="ml-auto lg:hidden text-white/70 hover:text-white" @click="closeSidebar">
							<XMarkIcon class="h-6 w-6" />
						</button>
					</div>

					<!-- Profile Short (Vertical) -->
					<div class="flex flex-col items-center gap-3 rounded-3xl bg-white/10 p-6 backdrop-blur-sm border border-white/5 transition hover:bg-white/15 group cursor-pointer text-center">
						<div class="flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-md ring-4 ring-white/10 group-hover:ring-white/20 transition-all">
                            <!-- Force color with inline style to prevent white-on-white inheritance issues -->
                            <UserIcon class="h-8 w-8" style="color: #84994F;" />
						</div>
						<div class="w-full overflow-hidden">
							<p class="truncate text-base font-bold tracking-wide text-white">{{ user?.name || 'Administrator' }}</p>
							<p class="truncate text-xs text-white/70 font-medium group-hover:text-white transition-colors mt-0.5">{{ user?.email }}</p>
						</div>
					</div>
				</div>

				<!-- Section B: Menu (Middle) -->
				<nav class="flex-1 pl-4 py-4 space-y-1 overflow-hidden">
					<Link v-for="item in navigation" :key="item.label" :href="navHref(item)"
						class="relative flex items-center gap-4 px-6 py-4 text-sm font-bold transition-all duration-200 rounded-l-full group"
						:class="isActive(item)
							? 'bg-[#F5F5F4] text-[var(--color-primary)] z-20'
							: 'text-white/70 hover:bg-white/10 hover:text-white z-10'
							" @click="closeSidebar">
						
                        <!-- Active Indicator Curves (Top & Bottom) - Precise Alignment -->
                        <!-- Top Curve -->
                        <div v-if="isActive(item)" class="absolute right-0 -top-5 w-5 h-5 bg-transparent rounded-br-3xl shadow-[5px_5px_0_0_#F5F5F4] pointer-events-none"></div>
                        <!-- Bottom Curve -->
                        <div v-if="isActive(item)" class="absolute right-0 -bottom-5 w-5 h-5 bg-transparent rounded-tr-3xl shadow-[5px_-5px_0_0_#F5F5F4] pointer-events-none"></div>

                        <component 
                            :is="item.icon" 
                            class="h-6 w-6 transition-transform duration-300 group-hover:scale-110"
                            :class="isActive(item) ? 'text-[var(--color-primary)]' : 'text-current opacity-70 group-hover:opacity-100'"
                        />
						
						<span class="tracking-wide">{{ item.label }}</span>
					</Link>
				</nav>

				<!-- Section C: Logout (Bottom) -->
				<div class="p-6">
					<button @click="confirmLogout" class="group flex w-full items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-sm font-medium text-white transition-all hover:bg-white hover:text-[var(--color-danger)] hover:border-white shadow-sm hover:shadow-md active:scale-95 duration-200">
						<ArrowLeftOnRectangleIcon class="h-5 w-5 transition-transform group-hover:-translate-x-1" />
						<span>Keluar Aplikasi</span>
					</button>
                    <p class="mt-4 text-center text-[10px] text-white/30">
                        &copy; 2024 Terminal Pintar v1.0
                    </p>
				</div>
			</aside>

			<!-- Main area -->
			<div class="flex-1 lg:pl-72 bg-[#F5F5F4] transition-all duration-300">
				<!-- Header (Glassmorphism Scroll Effect) -->
				<header 
                    class="sticky top-0 z-40 flex items-center justify-between px-8 py-5 transition-all duration-300"
                    :class="isScrolled ? 'bg-white/80 backdrop-blur-md shadow-sm py-4 border-b border-slate-100' : 'bg-transparent border-transparent'"
                >
					<div class="flex items-center gap-4">
						<button class="rounded-xl border border-slate-200 p-2 text-slate-600 shadow-sm hover:bg-slate-50 lg:hidden hover:scale-105 active:scale-95 transition-transform"
							@click="toggleSidebar">
							<Bars3Icon class="h-5 w-5" />
						</button>
						<div>
						<h2 class="page-title text-3xl font-bold tracking-tight leading-tight bg-gradient-to-r from-slate-800 via-[var(--color-primary)] to-slate-800 bg-clip-text text-transparent bg-[length:200%_100%] animate-gradient-shift hover:scale-105 transition-transform duration-300 cursor-default">
							{{ page.props.title || 'Dashboard' }}
						</h2>
					</div>
					</div>
				</header>

				<!-- Content -->
				<main class="flex-1 overflow-y-auto px-8 pb-8 pt-2 scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent">
                    <Transition name="page" mode="out-in">
                        <div :key="$page.url">
                            <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-200 ease-in"
                                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                                <div v-if="flashVisible && (flash.success || flash.error)"
                                    :class="[
                                        'mb-8 flex items-start gap-4 rounded-3xl border px-6 py-4 shadow-sm backdrop-blur-sm',
                                        flash.success
                                            ? 'border-emerald-100 bg-emerald-50/80 text-emerald-800'
                                            : 'border-rose-100 bg-rose-50/80 text-rose-800',
                                    ]">
                                    <component :is="flash.success ? CheckCircleIcon : ExclamationCircleIcon" class="h-6 w-6 shrink-0" />
                                    <div>
                                        <p class="font-bold text-base">
                                            {{ flash.success ? 'Berhasil' : 'Terjadi Kesalahan' }}
                                        </p>
                                        <p class="mt-1 text-sm opacity-90">{{ flash.success || flash.error }}</p>
                                    </div>
                                </div>
                            </transition>

                            <slot />
                        </div>
                    </Transition>
				</main>
			</div>
		</div>
	</div>
</template>
