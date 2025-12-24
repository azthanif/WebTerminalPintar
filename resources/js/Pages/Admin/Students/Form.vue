<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    UserIcon, 
    ArrowLeftIcon, 
    AcademicCapIcon, 
    CalendarIcon, 
    BuildingLibraryIcon, 
    MapPinIcon, 
    UserPlusIcon, 
    ShieldCheckIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    EyeIcon,
    EyeSlashIcon,
    LockClosedIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    student: {
        type: Object,
        default: null,
    },
    parents: {
        type: Array,
        default: () => [],
    },
    educationOptions: {
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

const isEdit = computed(() => Boolean(props.student?.id))
const parentsList = computed(() => (Array.isArray(props.parents) ? props.parents : []))
const educationList = computed(() => (Array.isArray(props.educationOptions) ? props.educationOptions : []))
const parentSearchTerm = ref('')
const selectedParentName = ref('')
const hasParentSearch = computed(() => parentSearchTerm.value.trim().length > 0)
const filteredParents = computed(() => {
    const source = parentsList.value
    const term = parentSearchTerm.value.trim().toLowerCase()

    if (!term) {
        return []
    }

    return source.filter((parent) => parent.name.toLowerCase().includes(term)).slice(0, 8)
})

const form = useForm({
    student_id: props.student?.student_id ?? '',
    name: props.student?.name ?? '',
    education_level: props.student?.education_level ?? '',
    parent_id: props.student?.parent_id ?? '',
    status: props.student?.status ?? 'active',
    date_of_birth: props.student?.date_of_birth ?? '',
    school_name: props.student?.school_name ?? '',
    address: props.student?.address ?? '',
    create_parent_account: false,
    new_parent_name: '',
    new_parent_email: '',
    new_parent_phone: '',
    new_parent_password: '',
})

const findParentById = (id) => parentsList.value.find((parent) => String(parent.id) === String(id))

const initialParent = findParentById(form.parent_id)
if (initialParent) {
    parentSearchTerm.value = initialParent.name
    selectedParentName.value = initialParent.name
}

if (!parentsList.value.length && !props.student?.parent_id) {
    form.create_parent_account = true
}

const isParentSelected = (parentId) => String(form.parent_id ?? '') === String(parentId ?? '')

watch(parentSearchTerm, (value) => {
    const normalizedValue = value.trim()

    if (!normalizedValue) {
        if (form.parent_id) {
            form.parent_id = ''
        }
        selectedParentName.value = ''
        return
    }

    if (selectedParentName.value && normalizedValue !== selectedParentName.value) {
        selectedParentName.value = ''
        form.parent_id = ''
    }
})

const selectParent = (parent) => {
    if (!parent) return
    selectedParentName.value = parent.name
    parentSearchTerm.value = parent.name
    form.parent_id = parent.id
}

const clearParentSelection = () => {
    selectedParentName.value = ''
    parentSearchTerm.value = ''
    form.parent_id = ''
}

const useExistingParent = () => {
    form.create_parent_account = false
    form.new_parent_name = ''
    form.new_parent_email = ''
    form.new_parent_phone = ''
    form.new_parent_password = ''
}

const useNewParent = () => {
    form.create_parent_account = true
    clearParentSelection()
    form.new_parent_password = ''
}

watch(
    () => form.create_parent_account,
    (value) => {
        if (value) {
            form.parent_id = ''
        } else {
            form.new_parent_name = ''
            form.new_parent_email = ''
            form.new_parent_phone = ''
        }
    },
)

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.students.update', props.student.id))
    } else {
        form.post(route('admin.students.store'))
    }
}

const showParentPassword = ref(false)

const toggleParentPasswordVisibility = () => {
    showParentPassword.value = !showParentPassword.value
}

const maxDate = new Date().toISOString().split('T')[0];

</script>

<template>
    <div class="space-y-8">
        <Head :title="isEdit ? 'Edit Siswa' : 'Tambah Siswa'" />

         <!-- Header Section -->
        <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
            <div>
                 <Link :href="route('admin.students.index')" class="group inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-emerald-600 transition-colors mb-4">
                    <div class="rounded-full bg-slate-100 p-1 group-hover:bg-emerald-100 transition-colors">
                        <ArrowLeftIcon class="h-4 w-4" />
                    </div>
                    <span>Kembali ke Daftar</span>
                </Link>
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
                    {{ isEdit ? 'Edit' : 'Tambah' }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-[var(--color-primary)]">Siswa</span>
                </h1>
                <p class="mt-2 text-slate-500 font-medium text-lg max-w-2xl">
                    {{ isEdit ? 'Perbarui data akademik dan informasi pribadi siswa.' : 'Daftarkan siswa baru ke dalam sistem pembelajaran.' }}
                </p>
            </div>
        </section>

        <section class="rounded-[2.5rem] border border-slate-200 bg-white shadow-sm overflow-hidden">
             <!-- Form Header -->
            <div class="px-8 pt-8 pb-4 border-b border-slate-100 bg-emerald-50/30">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <AcademicCapIcon class="h-6 w-6 text-emerald-600" />
                    Data Akademik & Pribadi
                </h2>
            </div>

            <form @submit.prevent="submit" class="p-8 space-y-8">
                <!-- ID & Basic Info -->
                <div class="grid gap-8 md:grid-cols-2">
                    <div v-if="isEdit">
                         <label class="block text-sm font-bold text-slate-700 mb-2">ID Siswa</label>
                         <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <span class="text-xs font-black">ID</span>
                            </div>
                            <input v-model="form.student_id" type="text" readonly
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 py-3 text-sm font-bold text-slate-500 focus:outline-none cursor-not-allowed" />
                        </div>
                        <p class="mt-1.5 text-xs font-semibold text-slate-400">ID dibuat otomatis oleh sistem.</p>
                    </div>
                    <!-- <div v-else class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 flex items-start gap-3">
                         <ShieldCheckIcon class="h-6 w-6 text-emerald-600 shrink-0 mt-0.5" />
                        <div>
                             <p class="text-sm font-bold text-emerald-800">ID Siswa Otomatis</p>
                             <p class="text-xs text-emerald-600 mt-1 leading-relaxed">Sistem akan men-generate ID unik (misal: <strong>SW001</strong>) setelah data disimpan. Anda tidak perlu mengisinya.</p>
                        </div>
                    </div> -->

                    <div>
                         <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Lahir <span class="text-rose-500">*</span></label>
                         <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <CalendarIcon class="h-5 w-5" />
                            </div>
                            <input v-model="form.date_of_birth" type="date" :max="maxDate" required
                                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-slate-50 hover:bg-white"
                                :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.date_of_birth }" />
                        </div>
                        <p v-if="form.errors.date_of_birth" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.date_of_birth }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                         <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <UserIcon class="h-5 w-5" />
                            </div>
                            <input v-model="form.name" type="text" placeholder="Masukkan nama lengkap siswa"
                                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-slate-50 hover:bg-white"
                                :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.name }" />
                        </div>
                        <p v-if="form.errors.name" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.name }}</p>
                    </div>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    <div>
                         <label class="block text-sm font-bold text-slate-700 mb-2">Pendidikan Terakhir</label>
                         <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <AcademicCapIcon class="h-5 w-5" />
                            </div>
                            <select v-model="form.education_level"
                                class="w-full appearance-none rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-bold focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-slate-50 hover:bg-white"
                                :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.education_level }">
                                <option value="">Pilih Pendidikan</option>
                                <option v-for="opt in educationList" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                             <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <p v-if="form.errors.education_level" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.education_level }}</p>
                    </div>
                     <div>
                         <label class="block text-sm font-bold text-slate-700 mb-2">Nama Sekolah</label>
                         <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <BuildingLibraryIcon class="h-5 w-5" />
                            </div>
                            <input v-model="form.school_name" type="text" placeholder="Asal sekolah"
                                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-slate-50 hover:bg-white"
                                :class="{ 'border-rose-300 ring-4 ring-rose-50': form.errors.school_name }" />
                        </div>
                        <p v-if="form.errors.school_name" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.school_name }}</p>
                    </div>
                </div>

                <!-- Parent Section -->
                <div class="border-t border-slate-100 pt-8">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <UserPlusIcon class="h-5 w-5 text-emerald-600" />
                        Informasi Orang Tua
                    </h3>
                    
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="md:col-span-1">
                             <label class="block text-sm font-medium text-slate-500 mb-4">Pilih metode:</label>
                             <div class="space-y-3">
                                <label class="cursor-pointer group relative flex items-center gap-3 rounded-2xl border p-4 shadow-sm transition-all"
                                    :class="!form.create_parent_account ? 'bg-emerald-50 border-emerald-200 ring-2 ring-emerald-100' : 'bg-white border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="parentMode" :checked="!form.create_parent_account" @change="useExistingParent" class="sr-only" />
                                    <div class="h-5 w-5 rounded-full border border-slate-300 flex items-center justify-center bg-white" 
                                         :class="{'border-emerald-500': !form.create_parent_account}">
                                        <div v-if="!form.create_parent_account" class="h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                                    </div>
                                    <div>
                                         <span class="block text-sm font-bold text-slate-700">Hubungkan Akun</span>
                                         <span class="block text-xs text-slate-500 mt-0.5">Pilih orang tua yang sudah ada</span>
                                    </div>
                                </label>

                                <label class="cursor-pointer group relative flex items-center gap-3 rounded-2xl border p-4 shadow-sm transition-all"
                                    :class="form.create_parent_account ? 'bg-emerald-50 border-emerald-200 ring-2 ring-emerald-100' : 'bg-white border-slate-200 hover:bg-slate-50'">
                                    <input type="radio" name="parentMode" :checked="form.create_parent_account" @change="useNewParent" class="sr-only" />
                                    <div class="h-5 w-5 rounded-full border border-slate-300 flex items-center justify-center bg-white"
                                         :class="{'border-emerald-500': form.create_parent_account}">
                                        <div v-if="form.create_parent_account" class="h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                                    </div>
                                     <div>
                                         <span class="block text-sm font-bold text-slate-700">Buat Akun Baru</span>
                                         <span class="block text-xs text-slate-500 mt-0.5">Daftarkan orang tua baru</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                             <!-- Existing Parent Search -->
                            <div v-if="!form.create_parent_account" class="space-y-4 rounded-[2rem] border border-slate-200 bg-slate-50/50 p-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Cari Orang Tua</label>
                                    <div class="relative">
                                         <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <MagnifyingGlassIcon class="h-5 w-5" />
                                        </div>
                                        <input v-model="parentSearchTerm" type="text" :disabled="!parentsList.length"
                                            placeholder="Ketik nama orang tua..."
                                            class="w-full rounded-2xl border border-slate-200 pl-11 pr-10 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-white"
                                            :class="[
                                                form.errors.parent_id ? 'border-rose-400' : '',
                                                !parentsList.length ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : '',
                                            ]" />
                                         <button v-if="parentSearchTerm" type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-full text-slate-400 hover:bg-rose-100 hover:text-rose-500 transition-colors"
                                            @click="clearParentSelection">
                                            <XMarkIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <div v-if="parentsList.length && hasParentSearch && !selectedParentName" class="mt-2 max-h-60 overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-sm scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent">
                                        <button type="button" v-for="parent in filteredParents" :key="parent.id"
                                            class="group flex w-full items-center justify-between px-4 py-3 text-left transition-colors hover:bg-emerald-50 border-b border-slate-50 last:border-0"
                                            @click="selectParent(parent)">
                                            <span class="text-sm font-medium text-slate-700 group-hover:text-emerald-700">{{ parent.name }}</span>
                                        </button>
                                         <div v-if="!filteredParents.length" class="px-4 py-6 text-center">
                                            <p class="text-xs font-medium text-slate-400">Tidak ditemukan.</p>
                                        </div>
                                    </div>
                                    <div v-if="selectedParentName" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-emerald-100 px-4 py-2 text-sm font-bold text-emerald-700">
                                        <ShieldCheckIcon class="h-4 w-4" />
                                        Orang Tua Terpilih: {{ selectedParentName }}
                                    </div>
                                    <p v-if="form.errors.parent_id" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.parent_id }}</p>
                                </div>
                            </div>

                             <!-- New Parent Form -->
                            <div v-else class="space-y-4 rounded-[2rem] border border-emerald-100 bg-emerald-50/50 p-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Orang Tua <span class="text-rose-500">*</span></label>
                                    <input v-model="form.new_parent_name" type="text" placeholder="Masukkan nama"
                                        class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-white"
                                        :class="{ 'border-rose-400': form.errors.new_parent_name }" />
                                    <p v-if="form.errors.new_parent_name" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.new_parent_name }}</p>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                     <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Email <span class="text-rose-500">*</span></label>
                                        <input v-model="form.new_parent_email" type="email" placeholder="email@contoh.com"
                                            class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-white"
                                            :class="{ 'border-rose-400': form.errors.new_parent_email }" />
                                        <p v-if="form.errors.new_parent_email" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.new_parent_email }}</p>
                                    </div>
                                     <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Nomor Telepon</label>
                                        <input v-model="form.new_parent_phone" type="text" placeholder="08xxxxxxxxxx"
                                            class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-white"
                                            :class="{ 'border-rose-400': form.errors.new_parent_phone }" />
                                    </div>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                    </div>
                                </div>
                                    <div class="mt-4">
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Password Akun <span class="text-rose-500">*</span></label>

                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <LockClosedIcon class="h-5 w-5" />
                                        </div>
                                    
                                        <input 
                                            v-model="form.new_parent_password" 
                                            :type="showParentPassword ? 'text' : 'password'" 
                                            placeholder="Masukan password akun"
                                            class="w-full rounded-2xl border border-slate-200 pl-11 pr-12 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-white"
                                            :class="{ 'border-rose-400': form.errors.new_parent_password }" 
                                        />
                                    
                                        <button 
                                            type="button" 
                                            @click="toggleParentPasswordVisibility"
                                            class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-slate-600 transition-colors"
                                            tabindex="-1"
                                        >
                                            <EyeIcon v-if="showParentPassword" class="h-5 w-5" />
                                            <EyeSlashIcon v-else class="h-5 w-5" />
                                        </button>
                                    </div>
                                
                                    <p v-if="form.errors.new_parent_password" class="mt-1.5 text-xs font-bold text-rose-500 flex items-center gap-1">
                                        <ShieldCheckIcon class="h-3 w-3" />
                                        {{ form.errors.new_parent_password }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address & Status -->
                 <div class="border-t border-slate-100 pt-8 grid gap-8 md:grid-cols-2">
                     <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Status Siswa</label>
						<div class="flex gap-4 p-1">
							<label class="cursor-pointer group relative flex flex-1 items-center justify-center gap-2 rounded-xl border p-3 shadow-sm transition-all"
                                :class="form.status === 'active' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 ring-2 ring-emerald-100' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
								<input v-model="form.status" type="radio" value="active" class="sr-only" />
                                <div class="h-4 w-4 rounded-full border border-current flex items-center justify-center">
                                    <div class="h-2 w-2 rounded-full bg-current transform scale-0 transition-transform" :class="{'scale-100': form.status === 'active'}"></div>
                                </div>
								<span class="font-bold">Aktif</span>
							</label>
							<label class="cursor-pointer group relative flex flex-1 items-center justify-center gap-2 rounded-xl border p-3 shadow-sm transition-all"
                                :class="form.status !== 'active' ? 'bg-rose-50 border-rose-200 text-rose-700 ring-2 ring-rose-100' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
								<input v-model="form.status" type="radio" value="inactive" class="sr-only" />
                                 <div class="h-4 w-4 rounded-full border border-current flex items-center justify-center">
                                    <div class="h-2 w-2 rounded-full bg-current transform scale-0 transition-transform" :class="{'scale-100': form.status !== 'active'}"></div>
                                </div>
								<span class="font-bold">Nonaktif</span>
							</label>
						</div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Alamat Domisili</label>
                         <div class="relative">
                            <div class="pointer-events-none absolute top-3 left-0 flex items-start pl-4 text-slate-400">
                                <MapPinIcon class="h-5 w-5" />
                            </div>
                            <textarea v-model="form.address" rows="3" placeholder="Alamat lengkap..."
                                class="w-full rounded-2xl border border-slate-200 pl-11 pr-4 py-3 text-sm font-medium focus:border-emerald-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 transition-all bg-slate-50 hover:bg-white resize-none"
                                :class="{ 'border-rose-400': form.errors.address }"></textarea>
                        </div>
                        <p v-if="form.errors.address" class="mt-1.5 text-xs font-bold text-rose-500">{{ form.errors.address }}</p>
                    </div>
                 </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-8">
                     <Link :href="route('admin.students.index')"
						class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 hover:text-slate-800 active:scale-95">
						Batal
					</Link>
					<button type="submit"
						class="group inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--color-primary)] px-8 py-3 text-sm font-bold text-white shadow-lg shadow-[var(--color-primary-light)] transition hover:bg-[var(--color-primary-hover)] hover:scale-105 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
						:disabled="form.processing">
                         <div v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
						<span>{{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Simpan Siswa' }}</span>
					</button>
                </div>
            </form>
        </section>
        <Notivue v-slot="notification">
            <Notification :item="notification" />
        </Notivue>
    </div>
</template>
