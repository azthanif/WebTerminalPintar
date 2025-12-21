<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import axios from 'axios'

// Inertia form
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

// UI States
const showPassword = ref(false)
const showForgotPasswordModal = ref(false)
const captchaAnswer = ref('')
const captchaError = ref(false)
const isCaptchaLoading = ref(false)

// CAPTCHA Logic
const captchaData = ref([])

const generateCaptcha = async () => {
    isCaptchaLoading.value = true
    try {
        const response = await axios.get('/captcha/generate')
        captchaData.value = response.data.captcha
        captchaAnswer.value = ''
        captchaError.value = false
    } catch (error) {
        console.error('Failed to load CAPTCHA', error)
    } finally {
        isCaptchaLoading.value = false
    }
}

const openForgotPasswordModal = () => {
  generateCaptcha()
  showForgotPasswordModal.value = true
}

const closeForgotPasswordModal = () => {
  showForgotPasswordModal.value = false
  captchaAnswer.value = ''
  captchaError.value = false
}

const verifyCaptchaAndContact = async () => {
  if (!captchaAnswer.value) return

  try {
    const response = await axios.post('/captcha/verify', {
      answer: captchaAnswer.value
    })
    
    if (response.data.success) {
      const adminPhone = '6281285535095'
      const adminName = 'Andi Rachmadi'
      const message = `Permisi Admin ${adminName}, saya lupa password akun saya, tolong berikan saya password baru`
      
      const whatsappUrl = `https://api.whatsapp.com/send?phone=${adminPhone}&text=${encodeURIComponent(message)}`
      
      window.open(whatsappUrl, '_blank')
      closeForgotPasswordModal()
    }
  } catch (error) {
    captchaError.value = true
    setTimeout(() => {
      generateCaptcha()
    }, 1500)
  }
}

// Logo
const logoUrl = '/images/logo-terminal-pintar.png'
</script>

<template>
  <Head title="Masuk - Terminal Pintar" />

  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#5a8a30] to-[#4a7028] p-3 sm:p-6 lg:p-8">
    <div class="relative w-full max-w-7xl min-h-[450px] sm:min-h-[550px] md:h-[600px] flex flex-col md:flex-row overflow-hidden rounded-xl sm:rounded-3xl shadow-2xl">
      <!-- Left Side - Illustration/Branding (Hidden on mobile) -->
      <div class="hidden md:flex md:w-1/2 lg:w-5/12 bg-gradient-to-br from-slate-50 to-amber-50/30 relative overflow-hidden">
        <!-- Organic curved divider -->
        <svg class="absolute right-0 top-0 h-full w-24 lg:w-32 text-slate-50" viewBox="0 0 100 600" preserveAspectRatio="none">
          <path d="M0,0 Q50,150 30,300 T0,600 L100,600 L100,0 Z" fill="currentColor"/>
        </svg>
        
        <!-- Decorative circles -->
        <div class="absolute top-10 left-10 w-24 lg:w-32 h-24 lg:h-32 bg-[#76B340]/20 rounded-full opacity-60"></div>
        <div class="absolute top-32 lg:top-40 right-16 lg:right-20 w-20 lg:w-24 h-20 lg:h-24 bg-amber-200/40 rounded-full opacity-50"></div>
        <div class="absolute bottom-16 lg:bottom-20 left-16 lg:left-20 w-16 lg:w-20 h-16 lg:h-20 bg-[#76B340]/30 rounded-full opacity-60"></div>
        <div class="absolute top-16 lg:top-20 right-8 lg:right-10 w-12 lg:w-16 h-12 lg:h-16 bg-amber-300/30 rounded-full opacity-40"></div>
        
        <!-- Logo and Content -->
        <div class="relative z-10 flex flex-col items-center justify-center w-full p-8 lg:p-12">
          <img 
            :src="logoUrl" 
            alt="Terminal Pintar Logo" 
            class="w-64 lg:w-96 h-64 lg:h-96 object-contain"
          />
        </div>
        
        <!-- Bottom copyright -->
        <div class="absolute bottom-4 lg:bottom-6 left-4 lg:left-6 text-xs text-slate-500">
          © 2025 Terminal Pintar. Semua Hak Dilindungi.<br/>
          
        </div>
      </div>

      <!-- Right Side - Login Form -->
      <div class="w-full md:w-1/2 lg:w-7/12 bg-gradient-to-br from-[#6ba239] via-[#5a8a30] to-[#4a7028] flex items-center justify-center p-5 sm:p-8 md:p-10 lg:p-12 relative">
        <!-- Decorative circles on right side -->
        <div class="absolute top-16 sm:top-20 right-8 sm:right-10 w-20 sm:w-24 h-20 sm:h-24 bg-[#76B340]/15 rounded-full opacity-30"></div>
        <div class="absolute bottom-24 sm:bottom-32 left-8 sm:left-10 w-24 sm:w-32 h-24 sm:h-32 bg-[#8bc34a]/10 rounded-full opacity-25"></div>
        <div class="absolute top-1/2 right-16 sm:right-20 w-12 sm:w-16 h-12 sm:h-16 bg-amber-400/10 rounded-full opacity-30"></div>
        
        <!-- Login Form -->
        <div class="relative z-10 w-full max-w-sm">
          <!-- Mobile Logo -->
          <div class="md:hidden flex justify-center mb-4 sm:mb-8">
            <img 
              :src="logoUrl" 
              alt="Terminal Pintar Logo" 
              class="w-20 h-20 sm:w-32 sm:h-32 object-contain"
            />
          </div>
          
          <h1 class="text-2xl sm:text-4xl font-bold text-white mb-5 sm:mb-8">Login</h1>
          
          <form @submit.prevent="submit" class="space-y-5 sm:space-y-6">
            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-medium text-amber-50 mb-2">Email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="Masukkan email Anda"
                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base rounded-lg bg-[#4a7028]/40 border border-[#76B340]/30 text-white placeholder-amber-100/40 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all"
                required
              />
              <div v-if="form.errors.email" class="mt-2 text-xs text-red-300 flex items-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ form.errors.email }}
              </div>
            </div>

            <!-- Password Field -->
            <div>
              <label for="password" class="block text-sm font-medium text-amber-50 mb-2">Password</label>
              <div class="relative">
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Masukkan password Anda"
                  class="w-full px-3 sm:px-4 py-2.5 sm:py-3 pr-10 sm:pr-12 text-sm sm:text-base rounded-lg bg-[#4a7028]/40 border border-[#76B340]/30 text-white placeholder-amber-100/40 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all"
                  required
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 text-amber-200 hover:text-amber-100 transition-colors p-1"
                >
                  <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                  </svg>
                </button>
              </div>
              <div v-if="form.errors.password" class="mt-2 text-xs text-red-300 flex items-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ form.errors.password }}
              </div>
              <div class="flex flex-col xs:flex-row xs:items-center xs:justify-between gap-2 mt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer group">
                  <input 
                    type="checkbox" 
                    v-model="form.remember" 
                    class="w-4 h-4 text-amber-400 border-[#76B340] rounded focus:ring-amber-400 bg-[#4a7028]/40 cursor-pointer"
                  />
                  <span class="text-xs sm:text-sm text-amber-100 group-hover:text-amber-50 transition-colors">Ingat Saya</span>
                </label>
                <button
                  type="button"
                  @click="openForgotPasswordModal"
                  class="text-xs sm:text-sm text-amber-200 hover:text-amber-100 transition-colors font-medium text-left xs:text-right"
                >
                  Lupa Password?
                </button>
              </div>
            </div>

            <!-- Login Button -->
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full py-2.5 sm:py-3 px-4 text-sm sm:text-base bg-gradient-to-r from-amber-500 to-amber-600 text-white font-semibold rounded-lg hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 focus:ring-offset-[#5a8a30] transition-all transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed shadow-lg"
            >
              <span v-if="!form.processing">Login to Terminal Pintar</span>
              <span v-else class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading...
              </span>
            </button>
          </form>

          <!-- Register Link -->
          <div class="mt-5 sm:mt-6 text-center">
            <p class="text-amber-100 text-xs sm:text-sm">
              Belum punya akun? Hubungi administrator
            </p>
          </div>

          <!-- Bottom Links -->
          <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-[#76B340]/30">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-0 text-xs text-amber-200/70">
              <a href="#" class="hover:text-amber-100 transition-colors text-center sm:text-left">Terms and Services</a>
              <div class="text-center sm:text-right">
                <p>Ada masalah? Hubungi kami</p>
                <a href="mailto:info@terminalpintar.com" class="hover:text-amber-100 transition-colors underline">info@terminalpintar.com</a>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Forgot Password Modal -->
    <div
      v-if="showForgotPasswordModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-fade-in"
      @click.self="closeForgotPasswordModal"
    >
      <div class="bg-white rounded-xl sm:rounded-2xl shadow-2xl max-w-md w-full p-6 sm:p-8 transform animate-scale-in max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-5 sm:mb-6">
          <h3 class="text-xl sm:text-2xl font-extrabold bg-gradient-to-r from-[#76B340] to-[#5a8a30] bg-clip-text text-transparent">
            Lupa Password?
          </h3>
          <button
            @click="closeForgotPasswordModal"
            class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center transition-colors flex-shrink-0"
          >
            <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Info Text -->
        <div class="mb-5 sm:mb-6 p-3 sm:p-4 bg-amber-50 border border-amber-200 rounded-lg sm:rounded-xl">
          <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
            Untuk mendapatkan password baru, Anda akan dihubungkan ke admin melalui WhatsApp. Silakan selesaikan CAPTCHA terlebih dahulu.
          </p>
        </div>

        <!-- CAPTCHA -->
        <div class="mb-5 sm:mb-6">
          <label class="block text-sm font-bold text-slate-700 mb-3">
            Verifikasi CAPTCHA
          </label>
          <div class="flex items-center gap-3 sm:gap-4 mb-4">
            <div class="flex-1 bg-gradient-to-r from-[#76B340]/10 to-amber-100/50 border-2 border-[#76B340]/30 rounded-lg sm:rounded-xl p-3 sm:p-4 text-center overflow-hidden relative min-h-[64px] flex items-center justify-center">
              
              <!-- Loading Overlay -->
              <div v-if="isCaptchaLoading" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center z-30">
                  <svg class="animate-spin h-6 w-6 text-[#76B340]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
              </div>

              <!-- Noise patterns -->
              <div class="absolute inset-0 opacity-10 pointer-events-none select-none flex flex-wrap gap-2">
                  <div v-for="n in 20" :key="n" class="h-px w-8 bg-slate-900 transform flex-shrink-0" :style="{ rotate: `${Math.random() * 360}deg`, left: `${Math.random() * 100}%`, top: `${Math.random() * 100}%` }"></div>
              </div>
              
              <div class="flex justify-center items-center gap-1 sm:gap-2 select-none relative z-10">
                <span v-for="(item, index) in captchaData" :key="index" 
                    class="font-black text-[#76B340] inline-block tracking-widest"
                    :style="item.style">
                    {{ item.char }}
                </span>
              </div>
              
              <!-- Reload Button -->
              <button 
                type="button" 
                @click="generateCaptcha"
                :disabled="isCaptchaLoading"
                class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-full hover:bg-[#76B340]/10 text-[#76B340] transition-colors z-20 group disabled:opacity-50"
                title="Ganti Kode"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:rotate-180 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
              </button>
            </div>
          </div>
          <input
            v-model="captchaAnswer"
            type="text"
            placeholder="Ketik kode di atas"
            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border-2 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-[#76B340] focus:border-[#76B340] transition-all"
            :class="captchaError ? 'border-red-500 animate-shake' : 'border-slate-200'"
            @keyup.enter="verifyCaptchaAndContact"
            :disabled="isCaptchaLoading"
          />
          <p v-if="captchaError" class="mt-2 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            Jawaban salah atau sudah kadaluarsa! Silakan coba lagi.
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col xs:flex-row gap-3">
          <button
            @click="closeForgotPasswordModal"
            class="flex-1 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base rounded-lg sm:rounded-xl border-2 border-slate-300 text-slate-700 font-bold hover:bg-slate-50 transition-all duration-300"
          >
            Batal
          </button>
          <button
            @click="verifyCaptchaAndContact"
            :disabled="isCaptchaLoading || !captchaAnswer"
            class="flex-1 px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base rounded-lg sm:rounded-xl bg-gradient-to-r from-[#76B340] to-[#5a8a30] text-white font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-50 disabled:transform-none"
          >
            <svg class="w-4 sm:w-5 h-4 sm:h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
              <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
            <span class="hidden xs:inline">Hubungi Admin</span>
            <span class="xs:hidden">Admin</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Fixed Back to Home Button (Screen Bottom Left) -->
    <div class="fixed bottom-4 left-4 sm:bottom-6 sm:left-6 z-50">
        <a 
            href="/" 
            class="flex items-center gap-2 sm:gap-3 px-4 sm:px-5 py-2 sm:py-2.5 bg-white/10 hover:bg-white/20 backdrop-blur-xl border border-white/30 rounded-full text-white text-[10px] sm:text-sm font-bold shadow-[0_8px_32px_rgba(0,0,0,0.3)] transform hover:-translate-y-1.5 transition-all duration-500 group overflow-hidden"
        >
            <!-- Animated background glow -->
            <div class="absolute inset-0 bg-gradient-to-r from-amber-400/0 via-amber-400/20 to-amber-400/0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            
            <div class="relative z-10 w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-amber-400 flex items-center justify-center shadow-inner transform group-hover:-rotate-12 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-5 sm:w-5 text-[#5a8a30]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </div>
            <span class="relative z-10 pr-1 sm:pr-2 tracking-wide group-hover:text-amber-300 transition-colors">KEMBALI KE BERANDA</span>
        </a>
    </div>
  </div>
</template>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scale-in {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-10px); }
  75% { transform: translateX(10px); }
}

.animate-fade-in {
  animation: fade-in 0.5s ease-out;
}

.animate-scale-in {
  animation: scale-in 0.3s ease-out;
}

.animate-shake {
  animation: shake 0.5s ease-in-out;
}
</style>
