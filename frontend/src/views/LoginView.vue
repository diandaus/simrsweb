<template>
  <div class="login-wrapper">
    <div class="login-container">
      <!-- Left Panel - Logo -->
      <div class="login-left">
        <div class="logo-section">
          <img v-if="customLogo" :src="customLogo" alt="Logo" class="logo-image">
          <div v-else class="default-logo">
            <div class="stethoscope-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4.8 2.3A.3.3 0 1 0 5 2H4a2 2 0 0 0-2 2v5a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6V4a2 2 0 0 0-2-2h-1a.2.2 0 1 0 .3.3"/>
                <path d="M8 15v1a6 6 0 0 0 6 6v0a6 6 0 0 0 6-6v-4"/>
                <circle cx="20" cy="10" r="2"/>
              </svg>
            </div>
            <h1 class="logo-text">SIMRS</h1>
            <p class="logo-subtitle">Sistem Informasi Manajemen Rumah Sakit</p>
          </div>
        </div>
      </div>

      <!-- Right Panel - Login Form -->
      <div class="login-right">
        <div class="login-form">
          <h2 class="login-title">Login</h2>

          <div class="form-group">
            <label for="username" class="form-label">Id Admin:</label>
            <input
              v-model="username"
              type="text"
              class="form-input"
              id="username"
              placeholder="Masukkan NIP/NIK"
              @keyup.enter="login"
            />
          </div>

          <div class="form-group">
            <label for="password" class="form-label">Password :</label>
            <div class="password-input-wrapper">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input"
                id="password"
                placeholder="Masukkan Password"
                @keyup.enter="login"
              />
              <button
                type="button"
                class="toggle-password"
                @click="showPassword = !showPassword"
                tabindex="-1"
              >
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
          </div>

          <p v-if="error" class="error-message">{{ error }}</p>

          <button
            class="btn-login"
            @click="login"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Loading...' : 'Login' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Copyright Footer -->
    <div class="copyright">
      Copyright © Firdaus IT RS Islam Ibnusina Sigli. All rights reserved
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/axios'

const username = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)
const customLogo = ref('')
const router = useRouter()

const getFirstAllowedRoute = (user) => {
  // Admin selalu ke dashboard
  if (user.role === 'admin') {
    return '/'
  }

  // Jika tidak ada menu permissions atau kosong, ke dashboard
  if (!user.menu_permissions || user.menu_permissions.length === 0) {
    return '/'
  }

  // Mapping menu key ke route path
  const menuRoutes = {
    'dashboard': '/dashboard',
    'pasien': '/pasien',
    'rawat-jalan': '/rawat-jalan/igd',
    'rawat-inap': '/rawat-inap/belum-pulang',
    'laboratorium': '/laboratorium/permintaan-pk',
    'radiologi': '/radiologi/permintaan',
    'jadwal-operasi': '/jadwal-operasi',
    'farmasi': '/farmasi'
  }

  // Ambil menu pertama yang diizinkan
  const firstMenu = user.menu_permissions[0]
  return menuRoutes[firstMenu] || '/'
}

const login = async () => {
  if (!username.value || !password.value) {
    error.value = 'Username dan password harus diisi'
    return
  }

  error.value = ''
  loading.value = true

  try {
    const { data } = await apiClient.post('/login', {
      username: username.value,
      password: password.value
    })

    if (data.success && data.data.token) {
      localStorage.setItem('token', data.data.token)
      localStorage.setItem('user', JSON.stringify(data.data.user))
      
      // Redirect ke menu pertama yang diizinkan
      const redirectPath = getFirstAllowedRoute(data.data.user)
      router.push(redirectPath)
    } else {
      error.value = data.message || 'Login gagal'
    }
  } catch (err) {
    console.error('Login error:', err)
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat login'
  } finally {
    loading.value = false
  }
}

// Load custom logo if available
onMounted(() => {
  const settings = localStorage.getItem('app-settings')
  if (settings) {
    const parsedSettings = JSON.parse(settings)
    customLogo.value = parsedSettings.customLogo || ''
  }
})
</script>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: white;
  padding: 2rem;
}

.login-container {
  display: flex;
  width: 100%;
  max-width: 1000px;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  min-height: 500px;
}

/* Left Panel - Logo */
.login-left {
  flex: 1;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  padding: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 20px;
}

.logo-section {
  text-align: center;
  color: white;
}

.logo-image {
  max-width: 200px;
  max-height: 200px;
  object-fit: contain;
  margin-bottom: 1rem;
}

.default-logo {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.stethoscope-icon {
  margin-bottom: 1rem;
}

.logo-text {
  font-size: 3.5rem;
  font-weight: 700;
  margin: 0;
  letter-spacing: 2px;
}

.logo-subtitle {
  font-size: 0.9rem;
  opacity: 0.9;
  margin: 0;
  font-weight: 300;
}

/* Right Panel - Login Form */
.login-right {
  flex: 1;
  padding: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0 20px 20px 0;
  background: white;
}

.login-form {
  width: 100%;
  max-width: 400px;
}

.login-title {
  font-size: 2rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 2rem;
  text-align: left;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  font-size: 0.95rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 0.5rem;
}

.password-input-wrapper {
  position: relative;
  width: 100%;
}

.form-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8fafc;
}

.password-input-wrapper .form-input {
  padding-right: 3rem;
}

.toggle-password {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.3s ease;
  border-radius: 4px;
}

.toggle-password:hover {
  color: #06b6d4;
  background: rgba(6, 182, 212, 0.1);
}

.toggle-password svg {
  width: 20px;
  height: 20px;
}

.form-input:focus {
  outline: none;
  border-color: #06b6d4;
  background: white;
  box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
}

.form-input::placeholder {
  color: #a0aec0;
}

.error-message {
  color: #e53e3e;
  font-size: 0.875rem;
  margin: 0.5rem 0 1rem 0;
  text-align: left;
}

.btn-login {
  width: 100%;
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 1.5rem;
  box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4);
}

.btn-login:active:not(:disabled) {
  transform: translateY(0);
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Copyright Footer */
.copyright {
  text-align: center;
  color: #4a5568;
  font-size: 0.875rem;
  margin-top: 2rem;
  font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
  .login-container {
    flex-direction: column;
    max-width: 450px;
  }

  .login-left {
    padding: 2rem;
    min-height: 250px;
    border-radius: 20px 20px 0 0;
  }

  .logo-text {
    font-size: 2.5rem;
  }

  .login-right {
    padding: 2rem;
    border-radius: 0 0 20px 20px;
  }

  .login-title {
    font-size: 1.5rem;
  }
}
</style>
