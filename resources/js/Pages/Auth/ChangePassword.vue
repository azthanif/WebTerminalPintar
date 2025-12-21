<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ShieldCheckIcon, LockClosedIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'

const form = useForm({
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const submit = () => {
    form.post(route('auth.change-password.update'), {
        onSuccess: () => {
            // Optional: add success notification here if needed
        },
    })
}

const logoUrl = '/images/logo-terminal-pintar.png'
</script>

<template>
    <Head title="Ganti Password - Terminal Pintar" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#5a8a30] to-[#4a7028] p-4 sm:p-6 lg:p-8">
        <div class="relative w-full max-w-7xl min-h-[500px] sm:min-h-[550px] md:h-[600px] flex flex-col md:flex-row overflow-hidden rounded-2xl sm:rounded-3xl shadow-2xl bg-white">
            
            <!-- Left Side - Information -->
            <div class="hidden md:flex md:w-1/2 lg:w-5/12 bg-gradient-to-br from-slate-50 to-amber-50/30 relative overflow-hidden border-r border-slate-100">
                <!-- Organic curved divider -->
                <svg class="absolute right-0 top-0 h-full w-24 lg:w-32 text-slate-50" viewBox="0 0 100 600" preserveAspectRatio="none">
                    <path d="M0,0 Q50,150 30,300 T0,600 L100,600 L100,0 Z" fill="currentColor"/>
                </svg>
                
                <div class="relative z-10 flex flex-col items-center justify-center w-full p-8 lg:p-12 text-center">
                    <div class="mb-8 p-4 bg-white rounded-3xl shadow-xl transform transition-transform duration-500">
                        <img :src="logoUrl" alt="Logo" class="w-48 lg:w-60 h-48 lg:h-60 object-contain" />
                    </div>
                    
                    <!-- <h2 class="text-3xl lg:text-4xl font-black bg-gradient-to-r from-[#76B340] to-[#5a8a30] bg-clip-text text-transparent leading-tight mb-4">
                        Keamanan Akun Utama
                    </h2> -->
                    
                    <div class="max-w-xs space-y-4">
                        <div class="p-4 bg-white/60 backdrop-blur rounded-2xl border border-white shadow-sm flex items-start gap-3 text-left">
                            <ShieldCheckIcon class="h-6 w-6 text-emerald-500 flex-shrink-0" />
                            <p class="text-sm text-slate-600 font-medium">
                                Ini adalah login pertama Anda. Demi keamanan, silakan perbarui password default Anda.
                            </p>
                        </div>
                        <div class="p-4 bg-white/60 backdrop-blur rounded-2xl border border-white shadow-sm flex items-start gap-3 text-left">
                            <LockClosedIcon class="h-6 w-6 text-amber-500 flex-shrink-0" />
                            <p class="text-sm text-slate-600 font-medium">
                                Gunakan kombinasi huruf, angka, dan simbol untuk password yang lebih kuat.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bottom accent -->
                <!-- <div class="absolute bottom-10 left-10 flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full bg-[#76B340]"></div>
                    <span class="text-xs font-bold text-[#76B340] tracking-widest uppercase">Safe & Secure</span>
                </div> -->
            </div>

            <!-- Right Side - Form -->
            <div class="w-full md:w-1/2 lg:w-7/12 bg-gradient-to-br from-[#6ba239] via-[#5a8a30] to-[#4a7028] flex items-center justify-center p-6 sm:p-8 md:p-10 lg:p-12 relative overflow-hidden">
                <!-- Decorative elements -->
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-amber-400/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 w-full max-w-sm">
                     <!-- Mobile Logo -->
                    <div class="md:hidden flex justify-center mb-8">
                        <div class="p-3 bg-white rounded-2xl shadow-lg">
                            <img :src="logoUrl" alt="Logo" class="w-16 h-16 object-contain" />
                        </div>
                    </div>

                    <div class="mb-8">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-2">Setel Password</h1>
                        <p class="text-emerald-100/80 font-medium">Silakan buat password baru untuk melanjutkan ke Dashboard.</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- New Password -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-amber-50 ml-1">Password Baru</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-200 group-focus-within:text-white transition-colors">
                                    <LockClosedIcon class="h-5 w-5" />
                                </div>
                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="w-full pl-11 pr-12 py-3.5 bg-white/10 hover:bg-white/15 focus:bg-white/20 border border-white/20 focus:border-white/40 rounded-2xl text-white placeholder-emerald-100/30 focus:outline-none focus:ring-4 focus:ring-white/10 transition-all text-lg font-medium"
                                    placeholder="••••••••"
                                    required
                                />
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-emerald-200 hover:text-white transition-colors"
                                >
                                    <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                                    <EyeSlashIcon v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-xs font-bold text-rose-300 mt-2 px-1 flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-amber-50 ml-1">Konfirmasi Password</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-200 group-focus-within:text-white transition-colors">
                                    <ShieldCheckIcon class="h-5 w-5" />
                                </div>
                                <input
                                    v-model="form.password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    class="w-full pl-11 pr-12 py-3.5 bg-white/10 hover:bg-white/15 focus:bg-white/20 border border-white/20 focus:border-white/40 rounded-2xl text-white placeholder-emerald-100/30 focus:outline-none focus:ring-4 focus:ring-white/10 transition-all text-lg font-medium"
                                    placeholder="••••••••"
                                    required
                                />
                                <button 
                                    type="button" 
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-emerald-200 hover:text-white transition-colors"
                                >
                                    <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5" />
                                    <EyeSlashIcon v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <p v-if="form.errors.password_confirmation" class="text-xs font-bold text-rose-300 mt-2 px-1 flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full group relative overflow-hidden bg-white py-4 rounded-2xl font-black text-[#5a8a30] text-lg shadow-2xl hover:shadow-white/20 transform hover:-translate-y-1 active:translate-y-0 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span class="relative z-10 flex items-center justify-center gap-2">
                                    <span v-if="!form.processing">Simpan & Masuk Dashboard</span>
                                    <svg v-else class="animate-spin h-6 w-6 text-[#5a8a30]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <div class="absolute inset-0 bg-emerald-50 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500"></div>
                            </button>
                        </div>
                    </form>

                    <!-- Footer Info -->
                    <div class="mt-8 pt-6 border-t border-white/10 text-center">
                        <p class="text-emerald-100/40 text-xs font-bold tracking-widest uppercase">
                            Protected by Advanced Encryption
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Any additional custom animations could go here */
</style>
