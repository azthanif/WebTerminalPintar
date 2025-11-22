<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Masuk - Terminal Pintar" />

  <div class="min-h-screen flex items-center justify-center bg-slate-100">
    <div class="w-full max-w-md bg-white shadow rounded-2xl p-8">
      <h1 class="text-xl font-bold text-emerald-700 mb-1">
        Masuk ke Terminal Pintar
      </h1>
      <p class="text-sm text-slate-600 mb-6">
        Gunakan akun admin/guru/orang tua yang sudah terdaftar.
      </p>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Email
          </label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500"
          />
          <div v-if="form.errors.email" class="text-xs text-red-600 mt-1">
            {{ form.errors.email }}
          </div>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Password
          </label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500"
          />
          <div v-if="form.errors.password" class="text-xs text-red-600 mt-1">
            {{ form.errors.password }}
          </div>
        </div>

        <!-- Remember me -->
        <div class="flex items-center justify-between text-sm">
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" v-model="form.remember" />
            <span>Ingat saya</span>
          </label>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          class="w-full py-2 rounded-lg bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-700 disabled:opacity-50"
          :disabled="form.processing"
        >
          Masuk
        </button>
      </form>
    </div>
  </div>
</template>
