<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Head } from '@inertiajs/vue3'
</script>

<template>
  <PublicLayout>
    <Head title="Kontak Terminal Pintar" />

    <section class="container mx-auto px-6 py-10">
      <h1 class="text-3xl font-bold text-gray-800 mb-4">
        Kontak
      </h1>
      <p class="text-gray-600 mb-4">
        Silakan hubungi kami melalui informasi berikut:
      </p>
      <ul class="text-gray-700 space-y-1 text-sm">
        <li>Alamat: (isi alamat sekolah)</li>
        <li>Telepon: (isi nomor)</li>
        <li>Email: (isi email)</li>
      </ul>
    </section>
  </PublicLayout>
</template>
