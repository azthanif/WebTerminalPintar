<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    book: {
        type: Object,
        default: null,
    },
    categories: {
        type: Array,
        default: () => [],
    },
    statuses: {
        type: Object,
        default: () => ({}),
    },
})

defineOptions({
    layout: AdminLayout,
})

const isEdit = computed(() => !!props.book)

const form = useForm({
    code: props.book?.code ?? '',
    title: props.book?.title ?? '',
    author: props.book?.author ?? '',
    category: props.book?.category ?? props.categories[0] ?? 'Umum',
    status: props.book?.status ?? 'available',
    published_year: props.book?.published_year ?? new Date().getFullYear(),
    total_pages: props.book?.total_pages ?? 0,
    total_stock: props.book?.total_stock ?? 1,
    available_stock: props.book?.available_stock ?? 1,
    description: props.book?.description ?? '',
})

watch(
    () => form.total_stock,
    (value) => {
        if (!value) return
        if (form.available_stock > value) {
            form.available_stock = value
        }
    },
)

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.books.update', props.book.id))
    } else {
        form.post(route('admin.books.store'))
    }
}
</script>

<template>
    <AdminLayout>
        <div class="space-y-10">
        <Head :title="isEdit ? 'Edit Buku' : 'Tambah Buku'" />

        <section>
            <Link :href="route('admin.books.index')"
                class="text-sm font-medium text-emerald-600 transition hover:text-emerald-700">
                ← Kembali ke Daftar Buku
            </Link>
            <h1 class="mt-3 text-3xl font-bold text-emerald-600">
                {{ isEdit ? 'Edit Data Buku' : 'Tambah Koleksi Perpustakaan' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Pastikan detail buku lengkap dan akurat agar mudah dicari siswa maupun guru.
            </p>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
            <form @submit.prevent="submit" class="space-y-8 p-8">
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-800">
                    <strong>Tips:</strong> Gunakan kode unik, isi deskripsi singkat isi buku, dan sesuaikan stok tersedia
                    dengan total eksemplar.
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Kode Buku<span
                                    class="text-rose-500">*</span></label>
                            <input v-model="form.code" type="text"
                                class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-400': form.errors.code }" />
                            <p v-if="form.errors.code" class="mt-1 text-xs text-rose-500">{{ form.errors.code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Judul Buku<span
                                    class="text-rose-500">*</span></label>
                            <input v-model="form.title" type="text"
                                class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-400': form.errors.title }" />
                            <p v-if="form.errors.title" class="mt-1 text-xs text-rose-500">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Penulis</label>
                            <input v-model="form.author" type="text"
                                class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-400': form.errors.author }" />
                            <p v-if="form.errors.author" class="mt-1 text-xs text-rose-500">{{ form.errors.author }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Kategori</label>
                            <select v-model="form.category"
                                class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-400': form.errors.category }">
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500">Gunakan kategori khusus bila buku sering dicari.</p>
                            <p v-if="form.errors.category" class="mt-1 text-xs text-rose-500">{{ form.errors.category }}</p>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Status Koleksi</label>
                                <select v-model="form.status"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    :class="{ 'border-rose-400': form.errors.status }">
                                    <option v-for="(label, value) in statuses" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-xs text-rose-500">{{ form.errors.status }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tahun Terbit</label>
                                <input v-model.number="form.published_year" type="number"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    :class="{ 'border-rose-400': form.errors.published_year }" />
                                <p v-if="form.errors.published_year" class="mt-1 text-xs text-rose-500">
                                    {{ form.errors.published_year }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Jumlah Halaman</label>
                                <input v-model.number="form.total_pages" type="number" min="0"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    :class="{ 'border-rose-400': form.errors.total_pages }" />
                                <p v-if="form.errors.total_pages" class="mt-1 text-xs text-rose-500">
                                    {{ form.errors.total_pages }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Total Stok</label>
                                <input v-model.number="form.total_stock" type="number" min="1"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    :class="{ 'border-rose-400': form.errors.total_stock }" />
                                <p v-if="form.errors.total_stock" class="mt-1 text-xs text-rose-500">
                                    {{ form.errors.total_stock }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Stok Tersedia</label>
                            <input v-model.number="form.available_stock" type="number" min="0" :max="form.total_stock"
                                class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-400': form.errors.available_stock }" />
                            <p class="mt-1 text-xs text-slate-500">Nilai ini otomatis dibatasi oleh total stok.</p>
                            <p v-if="form.errors.available_stock" class="mt-1 text-xs text-rose-500">
                                {{ form.errors.available_stock }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Deskripsi</label>
                            <textarea v-model="form.description" rows="5"
                                class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm leading-relaxed focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                :class="{ 'border-rose-400': form.errors.description }"></textarea>
                            <p class="mt-1 text-xs text-slate-500">Masukkan ringkasan singkat untuk membantu pencarian.</p>
                            <p v-if="form.errors.description" class="mt-1 text-xs text-rose-500">
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <Link :href="route('admin.books.index')"
                        class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                    </Link>
                    <button type="submit"
                        class="rounded-2xl bg-emerald-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-600 disabled:opacity-60"
                        :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Simpan Buku' }}
                    </button>
                </div>
            </form>
        </section>
        </div>
    </AdminLayout>
</template>
