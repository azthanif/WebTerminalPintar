<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

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

const isEdit = computed(() => !!props.student)

const form = useForm({
    student_id: props.student?.student_id ?? '',
    name: props.student?.name ?? '',
    education_level: props.student?.education_level ?? '',
    parent_id: props.student?.parent_id ?? '',
    status: props.student?.status ?? 'active',
    date_of_birth: props.student?.date_of_birth ?? '',
    school_name: props.student?.school_name ?? '',
    address: props.student?.address ?? '',
})

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.students.update', props.student.id))
    } else {
        form.post(route('admin.students.store'))
    }
}
</script>

<template>
    <div>

        <Head :title="isEdit ? 'Edit Siswa' : 'Tambah Siswa'" />

        <div class="min-h-screen flex items-center justify-center bg-gray-100">
            <div class="w-full max-w-xl bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
                <!-- Header -->
                <div class="bg-green-600 px-5 py-3 flex items-center justify-between">
                    <div>
                        <h1 class="text-sm font-semibold text-white">
                            {{ isEdit ? 'Edit Data Siswa' : 'Tambah Siswa Baru' }}
                        </h1>
                        <p class="text-[11px] text-white/80">
                            Lengkapi data siswa dengan informasi yang akurat.
                        </p>
                    </div>
                    <Link :href="route('admin.students.index')"
                        class="text-white/80 text-lg leading-none hover:text-white">
                    ×
                    </Link>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="px-5 py-4 space-y-4">
                    <!-- ID Siswa -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            ID Siswa
                        </label>
                        <input v-model="form.student_id" type="text"
                            class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                        <div v-if="form.errors.student_id" class="text-[11px] text-red-600 mt-1">
                            {{ form.errors.student_id }}
                        </div>
                    </div>

                    <!-- Nama -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Nama Lengkap
                        </label>
                        <input v-model="form.name" type="text"
                            class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                        <div v-if="form.errors.name" class="text-[11px] text-red-600 mt-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Pendidikan & Orang Tua -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Pendidikan Terakhir
                            </label>
                            <select v-model="form.education_level"
                                class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                                <option value="">Pilih pendidikan</option>
                                <option v-for="opt in educationOptions" :key="opt" :value="opt">
                                    {{ opt }}
                                </option>
                            </select>
                            <div v-if="form.errors.education_level" class="text-[11px] text-red-600 mt-1">
                                {{ form.errors.education_level }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Orang Tua (akun)
                            </label>
                            <select v-model="form.parent_id"
                                class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                                <option value="">Tanpa akun orang tua</option>
                                <option v-for="p in parents" :key="p.id" :value="p.id">
                                    {{ p.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.parent_id" class="text-[11px] text-red-600 mt-1">
                                {{ form.errors.parent_id }}
                            </div>
                        </div>
                    </div>

                    <!-- Status & Tanggal lahir -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Status Siswa
                            </label>
                            <div class="flex gap-4 mt-1 text-xs text-gray-700">
                                <label class="inline-flex items-center gap-1">
                                    <input v-model="form.status" type="radio" value="active"
                                        class="h-3 w-3 text-green-600 border-gray-300" />
                                    <span>Aktif</span>
                                </label>
                                <label class="inline-flex items-center gap-1">
                                    <input v-model="form.status" type="radio" value="inactive"
                                        class="h-3 w-3 text-green-600 border-gray-300" />
                                    <span>Nonaktif</span>
                                </label>
                            </div>
                            <div v-if="form.errors.status" class="text-[11px] text-red-600 mt-1">
                                {{ form.errors.status }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Tanggal Lahir
                            </label>
                            <input v-model="form.date_of_birth" type="date"
                                class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                            <div v-if="form.errors.date_of_birth" class="text-[11px] text-red-600 mt-1">
                                {{ form.errors.date_of_birth }}
                            </div>
                        </div>
                    </div>

                    <!-- Sekolah & Alamat -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Nama Sekolah
                        </label>
                        <input v-model="form.school_name" type="text"
                            class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                        <div v-if="form.errors.school_name" class="text-[11px] text-red-600 mt-1">
                            {{ form.errors.school_name }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Alamat
                        </label>
                        <textarea v-model="form.address" rows="3"
                            class="w-full rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500" />
                        <div v-if="form.errors.address" class="text-[11px] text-red-600 mt-1">
                            {{ form.errors.address }}
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="pt-2 flex justify-end gap-3">
                        <Link :href="route('admin.students.index')"
                            class="px-4 py-1.5 rounded-full border border-gray-200 text-xs text-gray-700 bg-white hover:bg-gray-50">
                        Batal
                        </Link>
                        <button type="submit"
                            class="px-5 py-1.5 rounded-full bg-green-600 text-xs font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                            :disabled="form.processing">
                            {{ isEdit ? 'Simpan Perubahan' : 'Simpan Siswa' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</template>
