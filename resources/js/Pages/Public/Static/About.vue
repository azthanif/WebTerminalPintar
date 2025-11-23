<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <PublicLayout>
    <Head title="Tentang Terminal Pintar" />

    <section class="container mx-auto px-6 py-10">
      <h1 class="text-3xl font-bold text-gray-800 mb-4">
        Tentang Terminal Pintar
      </h1>
      <p class="text-gray-600">
        (Isi profil / visi misi bisa diisi nanti atau diambil dari database Settings.)
      </p>
    </section>
  </PublicLayout>
</template>
