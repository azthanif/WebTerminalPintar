<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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
	<div class="space-y-10">
		<Head :title="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna'" />

		<section>
			<Link :href="route('admin.users.index')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
				← Kembali ke Daftar Pengguna
			</Link>
			<h1 class="mt-3 text-3xl font-bold text-emerald-600">
				{{ isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
			</h1>
			<p class="mt-1 text-sm text-slate-500">
				{{ isEdit
					? 'Ubah informasi akun dan status akses pengguna.'
					: 'Lengkapi detail pengguna untuk memberi akses ke sistem.' }}
			</p>
		</section>

		<section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
			<form @submit.prevent="submit" autocomplete="off" class="space-y-6 p-8">
				<div>
					<label class="block text-sm font-medium text-slate-700">Nama Lengkap<span class="text-rose-500">*</span></label>
					<input v-model="form.name" type="text" placeholder="Masukkan nama lengkap"
						class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
						:class="{ 'border-rose-400': form.errors.name }" />
					<p v-if="form.errors.name" class="mt-1 text-xs text-rose-500">{{ form.errors.name }}</p>
				</div>

				<div class="grid gap-6 md:grid-cols-2">
					<div>
						<label class="block text-sm font-medium text-slate-700">Email<span class="text-rose-500">*</span></label>
						<input v-model="form.email" type="email" :name="emailFieldName" autocomplete="off" autocapitalize="none" spellcheck="false" inputmode="email"
							placeholder="...@gmail.com"
							class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
							:class="{ 'border-rose-400': form.errors.email }" />
						<p v-if="form.errors.email" class="mt-1 text-xs text-rose-500">{{ form.errors.email }}</p>
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700">Nomor Telepon / WhatsApp</label>
						<input v-model="form.phone" type="text" placeholder="08xxxxxxxxxx"
							class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
							:class="{ 'border-rose-400': form.errors.phone }" />
						<p v-if="form.errors.phone" class="mt-1 text-xs text-rose-500">{{ form.errors.phone }}</p>
					</div>
				</div>

				<div class="grid gap-6 md:grid-cols-2">
					<div>
						<label class="block text-sm font-medium text-slate-700">Role Pengguna<span class="text-rose-500">*</span></label>
						<select v-model="form.role_id" :disabled="isOrtu"
							class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
							:class="[
								{ 'border-rose-400': form.errors.role_id },
								isOrtu ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : 'bg-white',
							]">
							<option value="" disabled>Pilih Role</option>
							<option v-for="role in filteredRoles" :key="role.id" :value="role.id">
								{{ role.display_name ?? role.name }}
							</option>
						</select>
						<p v-if="form.errors.role_id" class="mt-1 text-xs text-rose-500">{{ form.errors.role_id }}</p>
						<p class="mt-1 text-xs text-slate-500">Tentukan akses pengguna sesuai tugasnya.</p>
						<p v-if="isOrtu" class="mt-1 text-xs text-amber-600">
							⚠️ Role "Orang Tua" tidak dapat diubah karena terhubung ke data siswa.
						</p>
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700">Status Akun</label>
						<div class="mt-3 flex gap-6">
							<label class="inline-flex items-center gap-2 text-sm text-slate-700">
								<input v-model="form.is_active" type="radio" :value="true" class="text-emerald-500" />
								<span>Aktif</span>
							</label>
							<label class="inline-flex items-center gap-2 text-sm text-slate-700">
								<input v-model="form.is_active" type="radio" :value="false" class="text-emerald-500" />
								<span>Nonaktif</span>
							</label>
						</div>
						<p class="mt-2 text-xs text-slate-500">Aktif: pengguna dapat login dan menggunakan seluruh fitur. Nonaktif: akses akan diblokir tanpa menghapus akun.</p>
					</div>
				</div>

				<div v-if="!isOrtu" class="rounded-2xl border border-sky-100 bg-sky-50 p-4 text-sm text-sky-700">
					<strong>Info:</strong> Akun orang tua dibuat dan dihubungkan langsung dari modul Kelola Siswa. Gunakan menu tersebut untuk menambahkan siswa sekaligus membuat akun orang tua baru.
				</div>

				<div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm text-amber-800">
					<strong>Catatan Password:</strong> Kosongkan kolom password jika tidak ingin mengubah. Sistem akan mempertahankan password sebelumnya.
				</div>

				<div class="grid gap-6 md:grid-cols-2">
					<div>
						<label class="block text-sm font-medium text-slate-700">
							{{ isEdit ? 'Password Baru (Opsional)' : 'Password' }}
						</label>
						<div class="relative mt-2">
							<input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••"
								class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 pr-11 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
								:class="{ 'border-rose-400': form.errors.password }" />
							<button type="button" @click="togglePasswordVisibility"
								class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600"
								:aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
								<svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
									<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
								</svg>
								<svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
									<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.21 16.057 7 19 11.478 19c1.482 0 2.895-.27 4.178-.762m3.364-2.145A10.45 10.45 0 0021.022 12C19.746 7.943 15.956 5 11.478 5c-.74 0-1.467.074-2.169.217" />
									<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 00-3-3m0 0a3 3 0 00-3 3m3-3l8.25 8.25m-8.25-8.25L4.5 4.5" />
								</svg>
							</button>
						</div>
						<p v-if="form.errors.password" class="mt-1 text-xs text-rose-500">{{ form.errors.password }}</p>
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700">Konfirmasi Password</label>
						<input v-model="form.password_confirmation" type="password" placeholder="Ulangi password"
							class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
					</div>
				</div>

				<div class="flex justify-end gap-3">
					<Link :href="route('admin.users.index')"
						class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
						Batal
					</Link>
					<button type="submit"
						class="rounded-2xl bg-emerald-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-600 disabled:opacity-60"
						:disabled="form.processing">
						{{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
					</button>
				</div>
			</form>
		</section>
	</div>
</template>
