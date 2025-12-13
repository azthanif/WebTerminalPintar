<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Notivue, Notification, push } from 'notivue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

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
}

const useNewParent = () => {
    form.create_parent_account = true
    clearParentSelection()
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
</script>

<template>
    <div class="space-y-10">
        <Head :title="isEdit ? 'Edit Siswa' : 'Tambah Siswa'" />

        <section>
            <Link :href="route('admin.students.index')" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                ← Kembali ke Daftar Siswa
            </Link>
            <h1 class="mt-3 text-3xl font-bold text-emerald-600">
                {{ isEdit ? 'Edit Data Siswa' : 'Tambah Siswa Baru' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ isEdit
                    ? 'Perbarui informasi siswa agar data tetap akurat.'
                    : 'Lengkapi detail siswa dengan informasi yang akurat.' }}
            </p>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
            <form @submit.prevent="submit" class="space-y-6 p-8">
                <div v-if="isEdit" class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">ID Siswa</label>
                        <input v-model="form.student_id" type="text" readonly
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-600 focus:outline-none" />
                        <p class="mt-1 text-xs text-slate-500">ID ini dibuat otomatis oleh sistem dan tidak dapat diubah.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tanggal Lahir</label>
                        <input v-model="form.date_of_birth" type="date"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-400': form.errors.date_of_birth }" />
                        <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-rose-500">{{ form.errors.date_of_birth }}</p>
                    </div>
                </div>
                <div v-else class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-700">
                    <strong>ID Siswa Otomatis:</strong> Sistem akan membuat ID seperti <code>SW001</code>, <code>SW002</code>, dst. Kamu cukup mengisi data siswa saja.
                </div>

                <div v-if="!isEdit">
                    <label class="block text-sm font-medium text-slate-700">Tanggal Lahir</label>
                    <input v-model="form.date_of_birth" type="date"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        :class="{ 'border-rose-400': form.errors.date_of_birth }" />
                    <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-rose-500">{{ form.errors.date_of_birth }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama Lengkap<span class="text-rose-500">*</span></label>
                    <input v-model="form.name" type="text" placeholder="Masukkan nama siswa"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        :class="{ 'border-rose-400': form.errors.name }" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-rose-500">{{ form.errors.name }}</p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Pendidikan Terakhir</label>
                        <select v-model="form.education_level"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-400': form.errors.education_level }">
                            <option value="">Pilih pendidikan</option>
                            <option v-for="opt in educationList" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-500">Gunakan data terakhir yang tercatat.</p>
                        <p v-if="form.errors.education_level" class="mt-1 text-xs text-rose-500">{{ form.errors.education_level }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Akun Orang Tua</label>
                        <div class="mt-3 flex flex-col gap-2 rounded-2xl border border-slate-200 p-4 text-sm">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="parentMode" :checked="!form.create_parent_account" @change="useExistingParent" />
                                <span>Gunakan akun yang sudah ada</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="parentMode" :checked="form.create_parent_account" @change="useNewParent" />
                                <span>Buat akun orang tua baru</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div v-if="!form.create_parent_account" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <label class="block text-sm font-medium text-slate-700">Cari / Pilih Akun Orang Tua</label>
                    <div class="relative">
                        <input v-model="parentSearchTerm" type="text" :disabled="!parentsList.length"
                            placeholder="Ketik nama orang tua"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="[
                                form.errors.parent_id ? 'border-rose-400' : '',
                                !parentsList.length ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : '',
                            ]" />
                        <button v-if="parentSearchTerm" type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold uppercase tracking-widest text-slate-400 hover:text-rose-500"
                            @click="clearParentSelection">
                            Hapus
                        </button>
                    </div>
                    <div v-if="parentsList.length" class="max-h-56 overflow-y-auto rounded-2xl border border-slate-200 bg-white">
                        <template v-if="hasParentSearch">
                            <button type="button" v-for="parent in filteredParents" :key="parent.id"
                                class="flex w-full items-center justify-between border-b border-slate-50 px-4 py-2 text-left text-sm text-slate-700 last:border-b-0 hover:bg-emerald-50"
                                :class="{ 'bg-emerald-50 text-emerald-700': isParentSelected(parent.id) }"
                                @click="selectParent(parent)">
                                <span>{{ parent.name }}</span>
                                <span v-if="isParentSelected(parent.id)" class="text-xs font-semibold uppercase tracking-widest text-emerald-500">Dipilih</span>
                            </button>
                            <p v-if="hasParentSearch && !filteredParents.length" class="px-4 py-3 text-center text-xs text-slate-400">Nama orang tua tidak ditemukan.</p>
                        </template>
                        <p v-else class="px-4 py-3 text-center text-xs text-slate-400">Ketik nama orang tua untuk menampilkan hasil pencarian.</p>
                    </div>
                    <p v-else class="text-xs text-slate-400">Belum ada akun orang tua terdaftar.</p>
                    <p class="text-xs text-slate-500">Biarkan kolom kosong apabila siswa belum memiliki akun orang tua.</p>
                    <p v-if="form.errors.parent_id" class="text-xs text-rose-500">{{ form.errors.parent_id }}</p>
                </div>

                <div v-else class="space-y-4 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4">
                    <div class="flex items-start gap-3 text-sm text-emerald-700">
                        <span class="mt-0.5 font-semibold">Info:</span>
                        <span>Akun orang tua baru akan dibuat otomatis dan mendapatkan peran "Orang Tua". Password awal akan ditampilkan pada notifikasi setelah data tersimpan.</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nama Orang Tua<span class="text-rose-500">*</span></label>
                        <input v-model="form.new_parent_name" type="text" placeholder="Nama lengkap orang tua"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-400': form.errors.new_parent_name }" />
                        <p v-if="form.errors.new_parent_name" class="mt-1 text-xs text-rose-500">{{ form.errors.new_parent_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email Orang Tua<span class="text-rose-500">*</span></label>
                        <input v-model="form.new_parent_email" type="email" placeholder="ortu@example.com"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-400': form.errors.new_parent_email }" />
                        <p v-if="form.errors.new_parent_email" class="mt-1 text-xs text-rose-500">{{ form.errors.new_parent_email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nomor Telepon</label>
                        <input v-model="form.new_parent_phone" type="text" placeholder="08xxxxxxxxxx"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-400': form.errors.new_parent_phone }" />
                        <p class="mt-1 text-xs text-slate-500">Opsional namun membantu verifikasi akun.</p>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Status Siswa</label>
                        <div class="mt-3 flex gap-6">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input v-model="form.status" type="radio" value="active" class="text-emerald-500" />
                                <span>Aktif</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input v-model="form.status" type="radio" value="inactive" class="text-emerald-500" />
                                <span>Nonaktif</span>
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Nonaktifkan sementara bila siswa tidak lagi aktif mengikuti kegiatan.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nama Sekolah</label>
                        <input v-model="form.school_name" type="text" placeholder="Nama sekolah saat ini"
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            :class="{ 'border-rose-400': form.errors.school_name }" />
                        <p v-if="form.errors.school_name" class="mt-1 text-xs text-rose-500">{{ form.errors.school_name }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea v-model="form.address" rows="3" placeholder="Alamat lengkap siswa"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                        :class="{ 'border-rose-400': form.errors.address }"></textarea>
                    <p v-if="form.errors.address" class="mt-1 text-xs text-rose-500">{{ form.errors.address }}</p>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="route('admin.students.index')"
                        class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </Link>
                    <button
                        type="button"
                        @click="submit"
                        class="rounded-2xl bg-emerald-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-600 disabled:opacity-60"
                        :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Simpan Siswa' }}
                    </button>
                </div>
            </form>
        </section>
        <Notivue v-slot="notification">
            <Notification :item="notification" />
        </Notivue>
    </div>
</template>
