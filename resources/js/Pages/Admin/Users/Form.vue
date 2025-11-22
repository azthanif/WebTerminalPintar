<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

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

const isEdit = computed(() => !!props.user)

const rolesArray = computed(() =>
  Array.isArray(props.roles) ? props.roles : [],
)

const defaultRoleId = computed(() => {
  if (props.user && props.user.role) {
    return props.user.role.id
  }
  const guruRole = rolesArray.value.find((r) => r.name === 'guru')
  return guruRole ? guruRole.id : rolesArray.value[0]?.id ?? null
})

const form = useForm({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  phone: props.user?.phone ?? '',
  role_id: props.user?.role?.id ?? defaultRoleId.value,
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
</script>

<template>
  <div>

    <Head :title="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" />

    <!-- Latar belakang ungu muda seperti canvas -->
    <div class="min-h-screen flex items-center justify-center bg-[#FEF7FF]">
      <!-- Card form -->
      <div class="w-full max-w-md bg-white rounded-xl shadow-md overflow-hidden border border-[#ECE6F0]">
        <!-- Header hijau -->
        <div class="bg-[#78AE4E] px-5 py-3 flex items-center justify-between">
          <div>
            <h1 class="text-sm font-semibold text-white">
              {{ isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}
            </h1>
            <p class="text-[10px] text-white/80">
              Lengkapi data pengguna sesuai peran yang diinginkan.
            </p>
          </div>
          <Link :href="route('admin.users.index')" class="text-white/80 text-lg leading-none hover:text-white">
          ×
          </Link>
        </div>

        <!-- Isi form -->
        <form @submit.prevent="submit" class="px-5 py-4 space-y-3">
          <!-- Nama -->
          <div>
            <label class="block text-[11px] font-medium text-[#A0939A] mb-1">
              Nama Lengkap
            </label>
            <input v-model="form.name" type="text"
              class="w-full rounded-lg bg-[#F8F8F8] border border-[#E9ECEF] px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#78AE4E] focus:border-[#78AE4E]" />
            <div v-if="form.errors.name" class="text-[10px] text-red-600 mt-1">
              {{ form.errors.name }}
            </div>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-[11px] font-medium text-[#A0939A] mb-1">
              Email
            </label>
            <input v-model="form.email" type="email"
              class="w-full rounded-lg bg-[#F8F8F8] border border-[#E9ECEF] px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#78AE4E] focus:border-[#78AE4E]" />
            <div v-if="form.errors.email" class="text-[10px] text-red-600 mt-1">
              {{ form.errors.email }}
            </div>
          </div>

          <!-- Phone -->
          <div>
            <label class="block text-[11px] font-medium text-[#A0939A] mb-1">
              Nomor Telepon / WhatsApp
            </label>
            <input v-model="form.phone" type="text"
              class="w-full rounded-lg bg-[#F8F8F8] border border-[#E9ECEF] px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#78AE4E] focus:border-[#78AE4E]" />
            <div v-if="form.errors.phone" class="text-[10px] text-red-600 mt-1">
              {{ form.errors.phone }}
            </div>
          </div>

          <!-- Peran & Status -->
          <div class="grid grid-cols-1 gap-3">
            <div>
              <label class="block text-[11px] font-medium text-[#A0939A] mb-1">
                Peran Pengguna
              </label>
              <select v-model="form.role_id"
                class="w-full rounded-lg bg-[#F8F8F8] border border-[#E9ECEF] px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#78AE4E] focus:border-[#78AE4E]">
                <option v-for="role in rolesArray" :key="role.id" :value="role.id">
                  {{ role.display_name }}
                </option>
              </select>
              <div v-if="form.errors.role_id" class="text-[10px] text-red-600 mt-1">
                {{ form.errors.role_id }}
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-medium text-[#A0939A] mb-1">
                Status Akun
              </label>
              <div class="flex gap-4 mt-1 text-[11px] text-[#49454F]">
                <label class="inline-flex items-center gap-1">
                  <input v-model="form.is_active" type="radio" :value="true"
                    class="h-3 w-3 text-[#78AE4E] border-[#A0939A]" />
                  <span>Aktif</span>
                </label>
                <label class="inline-flex items-center gap-1">
                  <input v-model="form.is_active" type="radio" :value="false"
                    class="h-3 w-3 text-[#78AE4E] border-[#A0939A]" />
                  <span>Nonaktif</span>
                </label>
              </div>
              <div v-if="form.errors.is_active" class="text-[10px] text-red-600 mt-1">
                {{ form.errors.is_active }}
              </div>
            </div>
          </div>

          <!-- Password -->
          <div class="grid grid-cols-1 gap-3">
            <div>
              <label class="block text-[11px] font-medium text-[#A0939A] mb-1">
                {{ isEdit ? 'Password (opsional)' : 'Password' }}
              </label>
              <input v-model="form.password" type="password"
                class="w-full rounded-lg bg-[#F8F8F8] border border-[#E9ECEF] px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#78AE4E] focus:border-[#78AE4E]" />
              <div v-if="form.errors.password" class="text-[10px] text-red-600 mt-1">
                {{ form.errors.password }}
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-medium text-[#A0939A] mb-1">
                Konfirmasi Password
              </label>
              <input v-model="form.password_confirmation" type="password"
                class="w-full rounded-lg bg-[#F8F8F8] border border-[#E9ECEF] px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-[#78AE4E] focus:border-[#78AE4E]" />
            </div>
          </div>

          <!-- Tombol -->
          <div class="pt-2 flex justify-end gap-3">
            <Link :href="route('admin.users.index')"
              class="px-4 py-1.5 rounded-full border border-[#E9ECEF] text-[11px] text-[#49454F] bg-white hover:bg-[#F8F8F8]">
            Batal
            </Link>
            <button type="submit"
              class="px-5 py-1.5 rounded-full bg-[#78AE4E] text-[11px] font-semibold text-white hover:bg-[#76B340] disabled:opacity-50"
              :disabled="form.processing">
              {{ isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

</template>
