<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    UserIcon, 
    ArrowLeftIcon, 
    EnvelopeIcon, 
    PhoneIcon, 
    LockClosedIcon, 
    EyeIcon, 
    EyeSlashIcon,
    BriefcaseIcon,
    ShieldCheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
	user: {
		type: Object,
		default: null,
	},
	roles: {
		type: Array,
		default: () => [],
	},
})

defineOptions({
	layout: AdminLayout,
})

const page = usePage()
const FLASH_NOTIFICATION_DURATION = 4000

const pushFlashNotification = (type, message) => {
	if (!message) {
		return
	}

	const payload = {
		title: type === 'success' ? 'Berhasil' : 'Terjadi Kesalahan',
		message,
		duration: FLASH_NOTIFICATION_DURATION,
	}

	if (type === 'success') {
		push.success(payload)
	} else {
		push.error(payload)
	}
}

watch(
	() => ({
		success: page.props.flash?.success ?? null,
		error: page.props.flash?.error ?? null,
	}),
	({ success, error }) => {
		if (success) {
			pushFlashNotification('success', success)
		}

		if (error) {
			pushFlashNotification('error', error)
		}
	},
	{ immediate: true },
)

const isEdit = computed(() => Boolean(props.user?.id))
const rolesArray = computed(() => (Array.isArray(props.roles) ? props.roles : []))
const userRoleName = computed(() => props.user?.role?.name ?? null)
const isOrtu = computed(() => userRoleName.value === 'ortu')

const filteredRoles = computed(() => {
	if (isOrtu.value) {
		return rolesArray.value
	}
	return rolesArray.value.filter((role) => role.name !== 'ortu')
})

const initialRoleId = computed(() => {
	if (props.user?.role?.id) {
		return props.user.role.id
	}
	return filteredRoles.value[0]?.id ?? null
})

const showPassword = ref(false)
const emailFieldName = `user_email_${Math.random().toString(36).slice(2, 8)}`

const form = useForm({
	name: props.user?.name ?? '',
	email: props.user?.email ?? '',
	phone: props.user?.phone ?? '',
	role_id: initialRoleId.value,
	is_active: props.user?.is_active ?? true,
	password: '',
	password_confirmation: '',
})

const submit = () => {
	if (isEdit.value) {
		form.put(route('admin.users.update', props.user.id))
	} else {
		form.post(route('admin.users.store'))
	}
}

const togglePasswordVisibility = () => {
	showPassword.value = !showPassword.value
}
</script>

<template>
	<div class="space-y-8">
		<Head :title="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna'" />

        <!-- Header Section -->
        <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
            <div>
                 <Link :href="route('admin.users.index')" class="group inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-[var(--color-primary)] transition-colors mb-4">
                    <div class="rounded-full bg-slate-100 p-1 group-hover:bg-[var(--color-primary-lighter)] transition-colors">
                        <ArrowLeftIcon class="h-4 w-4" />
                    </div>
                    <span>Kembali ke Daftar</span>
                </Link>
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                    {{ isEdit ? 'Edit' : 'Tambah' }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-[var(--color-primary)]">Pengguna</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium text-lg max-w-2xl">
                    {{ isEdit ? 'Perbarui informasi dan hak akses pengguna sistem.' : 'Daftarkan pengguna baru untuk memberikan akses ke sistem.' }}
                </p>
            </div>
        </section>

		<section class="rounded-[2.5rem] border border-slate-200 bg-white shadow-sm overflow-hidden">
             <!-- Form Header -->
            <div class="px-8 pt-8 pb-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <UserIcon class="h-6 w-6 text-purple-500" />
                    Informasi Akun
                </h2>
            </div>
            
			<form @submit.prevent="submit" autocomplete="off" class="p-8 space-y-8">
                <!-- Personal Info -->
				<div class="grid gap-8 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <UserIcon class="h-5 w-5" />
                            </div>
                            <input v-model="form.name" type="text" placeholder="Masukkan nama lengkap"
                                    class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-[var(--color-primary)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[var(--color-primary-lighter)] transition-all bg-slate-50 hover:bg-white"
                                :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.name }" />
                        </div>
                        <p v-if="form.errors.name" class="mt-1.5 text-xs font-bold text-rose-500 flex items-center gap-1">
                            <ShieldCheckIcon class="h-3 w-3" />
                            {{ form.errors.name }}
                        </p>
                    </div>

					<div>
						<label class="block text-sm font-bold text-slate-700 mb-2">Email <span class="text-rose-500">*</span></label>
                         <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <EnvelopeIcon class="h-5 w-5" />
                            </div>
                            <input v-model="form.email" type="email" :name="emailFieldName" autocomplete="off" autocapitalize="none" spellcheck="false" inputmode="email"
                                placeholder="nama@email.com"
                            class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-[var(--color-primary)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[var(--color-primary-lighter)] transition-all bg-slate-50 hover:bg-white"
                                :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.email }" />
                        </div>
						<p v-if="form.errors.email" class="mt-1.5 text-xs font-bold text-rose-500 flex items-center gap-1">
                             <ShieldCheckIcon class="h-3 w-3" />
                            {{ form.errors.email }}
                        </p>
					</div>
                    
					<div>
						<label class="block text-sm font-bold text-slate-700 mb-2">Nomor Telepon / WA</label>
                         <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <PhoneIcon class="h-5 w-5" />
                            </div>
                            <input v-model="form.phone" type="text" placeholder="08xxxxxxxxxx"
                                    class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-[var(--color-primary)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[var(--color-primary-lighter)] transition-all bg-slate-50 hover:bg-white"
                                :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.phone }" />
                        </div>
						<p v-if="form.errors.phone" class="mt-1.5 text-xs font-bold text-rose-500 flex items-center gap-1">
                             <ShieldCheckIcon class="h-3 w-3" />
                            {{ form.errors.phone }}
                        </p>
					</div>
				</div>

				<div class="grid gap-8 md:grid-cols-2 border-t border-slate-100 pt-8">
					<div>
						<label class="block text-sm font-bold text-slate-700 mb-2">Role Pengguna <span class="text-rose-500">*</span></label>
                        <div class="relative">
                             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <BriefcaseIcon class="h-5 w-5" />
                            </div>
                            <select v-model="form.role_id" :disabled="isOrtu"
                                class="w-full appearance-none rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-bold focus:border-[var(--color-primary)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[var(--color-primary-lighter)] transition-all bg-slate-50 hover:bg-white disabled:opacity-60 disabled:cursor-not-allowed"
                                :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.role_id }">
                                <option value="" disabled>Pilih Hak Akses</option>
                                <option v-for="role in filteredRoles" :key="role.id" :value="role.id">
                                    {{ role.display_name ?? role.name }}
                                </option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
						<p v-if="form.errors.role_id" class="mt-1.5 text-xs font-bold text-rose-500 flex items-center gap-1">
                             <ShieldCheckIcon class="h-3 w-3" />
                            {{ form.errors.role_id }}
                        </p>
						<p v-if="isOrtu" class="mt-2 text-xs font-bold text-amber-600 bg-amber-50 p-2 rounded-lg border border-amber-100 flex items-center gap-1.5">
                            <ShieldCheckIcon class="h-4 w-4" />
							Role "Orang Tua" tidak dapat diubah karena terhubung ke data siswa.
						</p>
					</div>
                    
					<div>
						<label class="block text-sm font-bold text-slate-700 mb-2">Status Akun</label>
						<div class="flex gap-4 p-1">
							<label class="cursor-pointer group relative flex flex-1 items-center justify-center gap-2 rounded-xl border p-3 shadow-sm transition-all"
                                :class="form.is_active ? 'bg-emerald-50 border-emerald-200 text-emerald-700 ring-2 ring-emerald-100' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
								<input v-model="form.is_active" type="radio" :value="true" class="sr-only" />
                                <div class="h-4 w-4 rounded-full border border-current flex items-center justify-center">
                                    <div class="h-2 w-2 rounded-full bg-current transform scale-0 transition-transform" :class="{'scale-100': form.is_active}"></div>
                                </div>
								<span class="font-bold">Aktif</span>
							</label>
							<label class="cursor-pointer group relative flex flex-1 items-center justify-center gap-2 rounded-xl border p-3 shadow-sm transition-all"
                                :class="!form.is_active ? 'bg-rose-50 border-rose-200 text-rose-700 ring-2 ring-rose-100' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
								<input v-model="form.is_active" type="radio" :value="false" class="sr-only" />
                                 <div class="h-4 w-4 rounded-full border border-current flex items-center justify-center">
                                    <div class="h-2 w-2 rounded-full bg-current transform scale-0 transition-transform" :class="{'scale-100': !form.is_active}"></div>
                                </div>
								<span class="font-bold">Nonaktif</span>
							</label>
						</div>
					</div>
				</div>

				<div v-if="!isOrtu" class="rounded-2xl border border-sky-200 bg-sky-50 p-4 flex items-start gap-3">
                    <ShieldCheckIcon class="h-6 w-6 text-sky-600 shrink-0 mt-0.5" />
                    <div>
                        <p class="text-sm font-bold text-sky-800">Informasi Penting</p>
                        <p class="text-xs text-sky-600 mt-1 leading-relaxed">
                            Akun orang tua sebaiknya dibuat melalui menu <strong>Kelola Siswa</strong> agar terhubung otomatis. 
                            Menu ini lebih cocok untuk membuat akun <strong>Admin</strong> atau <strong>Guru</strong>.
                        </p>
                    </div>
				</div>

                <!-- Password Info Box (Edit Mode Only) -->
				<div v-if="isEdit" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
                     <LockClosedIcon class="h-6 w-6 text-amber-600 shrink-0 mt-0.5" />
                      <div>
                        <p class="text-sm font-bold text-amber-800">Keamanan Password</p>
                        <p class="text-xs text-amber-600 mt-1 leading-relaxed">
                            Kosongkan kolom password di bawah jika Anda tidak ingin mengubahnya. Password lama akan tetap digunakan.
                        </p>
                    </div>
				</div>

                <!-- Password Form -->
				<div class="grid gap-8 md:grid-cols-2 border-t border-slate-100 pt-8">
					<div>
						<label class="block text-sm font-bold text-slate-700 mb-2">
							{{ isEdit ? 'Password Baru (Opsional)' : 'Password' }}
						</label>
						<div class="relative">
                             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <LockClosedIcon class="h-5 w-5" />
                            </div>
							<input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••"
								class="w-full rounded-2xl border border-slate-200 pl-11 pr-12 py-3 text-sm font-medium focus:border-[var(--color-primary)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[var(--color-primary-lighter)] transition-all bg-slate-50 hover:bg-white"
								:class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.password }" />
							<button type="button" @click="togglePasswordVisibility"
								class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-slate-600 transition-colors"
								:aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
								<EyeIcon v-if="showPassword" class="h-5 w-5" />
								<EyeSlashIcon v-else class="h-5 w-5" />
							</button>
						</div>
						<p v-if="form.errors.password" class="mt-1.5 text-xs font-bold text-rose-500 flex items-center gap-1">
                             <ShieldCheckIcon class="h-3 w-3" />
                            {{ form.errors.password }}
                        </p>
					</div>
					<div>
						<label class="block text-sm font-bold text-slate-700 mb-2">Konfirmasi Password</label>
                        <div class="relative">
                             <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <LockClosedIcon class="h-5 w-5" />
                            </div>
						    <input v-model="form.password_confirmation" type="password" placeholder="Ulangi password"
							class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-[var(--color-primary)] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[var(--color-primary-lighter)] transition-all bg-slate-50 hover:bg-white" />
                        </div>
					</div>
				</div>

				<div class="flex justify-end gap-4 border-t border-slate-100 pt-8">
					<Link :href="route('admin.users.index')"
						class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-800 active:scale-95">
						Batal
					</Link>
					<button type="submit"
						class="group inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--color-primary)] px-8 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--color-primary-light)] transition hover:bg-[var(--color-primary-hover)] hover:scale-105 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
						:disabled="form.processing">
                         <div v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
						<span>{{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}</span>
					</button>
				</div>
			</form>
		</section>
		<Notivue v-slot="notification">
			<Notification :item="notification" />
		</Notivue>
	</div>
</template>
