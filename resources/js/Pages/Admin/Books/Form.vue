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
    <div class="space-y-6">
        <Head :title="isEdit ? 'Edit Buku' : 'Tambah Buku'" />

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Perpustakaan</p>
                    <h1 class="mt-2 text-2xl font-semibold text-slate-900">
                        {{ isEdit ? 'Perbarui Buku' : 'Tambah Buku Baru' }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        Lengkapi detail buku agar siswa mudah menemukannya.
                    </p>
                </div>
                <Link :href="route('admin.books.index')"
                    class="rounded-full border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                Kembali
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Kode Buku</label>
                        <input v-model="form.code" type="text"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                        <p v-if="form.errors.code" class="mt-1 text-xs text-rose-500">{{ form.errors.code }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Judul</label>
                        <input v-model="form.title" type="text"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-rose-500">{{ form.errors.title }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Penulis</label>
                        <input v-model="form.author" type="text"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                        <p v-if="form.errors.author" class="mt-1 text-xs text-rose-500">{{ form.errors.author }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</label>
                        <select v-model="form.category"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none">
                            <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
                        </select>
                        <p v-if="form.errors.category" class="mt-1 text-xs text-rose-500">{{ form.errors.category }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                            <select v-model="form.status"
                                class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none">
                                <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-xs text-rose-500">{{ form.errors.status }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tahun Terbit</label>
                            <input v-model.number="form.published_year" type="number"
                                class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                            <p v-if="form.errors.published_year" class="mt-1 text-xs text-rose-500">{{ form.errors.published_year }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Jumlah Halaman</label>
                            <input v-model.number="form.total_pages" type="number" min="0"
                                class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                            <p v-if="form.errors.total_pages" class="mt-1 text-xs text-rose-500">{{ form.errors.total_pages }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Total Stok</label>
                            <input v-model.number="form.total_stock" type="number" min="1"
                                class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                            <p v-if="form.errors.total_stock" class="mt-1 text-xs text-rose-500">{{ form.errors.total_stock }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Stok Tersedia</label>
                        <input v-model.number="form.available_stock" type="number" min="0"
                            :max="form.total_stock"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none" />
                        <p v-if="form.errors.available_stock" class="mt-1 text-xs text-rose-500">{{ form.errors.available_stock }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Deskripsi</label>
                        <textarea v-model="form.description" rows="5"
                            class="mt-1 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm focus:border-emerald-500 focus:bg-white focus:outline-none"></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-xs text-rose-500">{{ form.errors.description }}</p>
                    </div>
                </div>

                <div class="lg:col-span-2 flex items-center justify-end gap-3 pt-4">
                    <Link :href="route('admin.books.index')"
                        class="rounded-full border border-slate-200 px-5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Batal
                    </Link>
                    <button type="submit"
                        class="rounded-full bg-emerald-500 px-6 py-2 text-xs font-semibold text-white shadow-sm shadow-emerald-200 transition hover:bg-emerald-600 disabled:opacity-60"
                        :disabled="form.processing">
                        {{ isEdit ? 'Simpan Perubahan' : 'Simpan Buku' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
