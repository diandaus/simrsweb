import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../views/DashboardView.vue'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import PasienDetailView from '../views/PasienDetailView.vue'
import PasienFormView from '../views/PasienFormView.vue'
import RawatJalanView from '../views/RawatJalanView.vue'
import IGDView from '../views/IGDView.vue'
import PoliView from '../views/PoliView.vue'
import RawatInapView from '../views/RawatInapView.vue'
import BelumPulangView from '../views/BelumPulangView.vue'
import PulangView from '../views/PulangView.vue'
import LaboratoriumView from '../views/LaboratoriumView.vue'
import PermintaanLabPKView from '../views/PermintaanLabPKView.vue'
import PermintaanLabPAView from '../views/PermintaanLabPAView.vue'
import PeriksaLabPKView from '../views/PeriksaLabPKView.vue'
import PeriksaLabPAView from '../views/PeriksaLabPAView.vue'
import RadiologiView from '../views/RadiologiView.vue'
import PermintaanRadiologiView from '../views/PermintaanRadiologiView.vue'
import PeriksaRadiologiView from '../views/PeriksaRadiologiView.vue'
import JadwalOperasiView from '../views/JadwalOperasiView.vue'
import InputOperasiView from '../views/InputOperasiView.vue'
import FarmasiView from '../views/FarmasiView.vue'
import RiwayatPerawatanView from '../views/RiwayatPerawatanView.vue'
import SOAPView from '../views/SOAPView.vue'

const routes = [
  { path: '/login', component: LoginView },
  { path: '/', component: DashboardView, meta: { requiresAuth: true } },
  { path: '/dashboard', component: DashboardView, meta: { requiresAuth: true } },
  { path: '/pasien', component: HomeView, meta: { requiresAuth: true } },
  { path: '/pasien/tambah', component: PasienFormView, meta: { requiresAuth: true } },
  { path: '/pasien/:no_rkm_medis', component: PasienDetailView, meta: { requiresAuth: true } },
  { path: '/pasien/:no_rkm_medis/edit', component: PasienFormView, meta: { requiresAuth: true } },
  { path: '/riwayat-perawatan/:no_rawat(.*)', name: 'riwayat-perawatan', component: RiwayatPerawatanView, meta: { requiresAuth: true } },
  { path: '/rawat-jalan', component: RawatJalanView, meta: { requiresAuth: true } },
  { path: '/rawat-jalan/igd', component: IGDView, meta: { requiresAuth: true } },
  { path: '/rawat-jalan/poli', component: PoliView, meta: { requiresAuth: true } },
  { path: '/rawat-inap', component: RawatInapView, meta: { requiresAuth: true } },
  { path: '/rawat-inap/belum-pulang', component: BelumPulangView, meta: { requiresAuth: true } },
  { path: '/rawat-inap/pulang', component: PulangView, meta: { requiresAuth: true } },
  { path: '/laboratorium', component: LaboratoriumView, meta: { requiresAuth: true } },
  { path: '/laboratorium/permintaan-pk', component: PermintaanLabPKView, meta: { requiresAuth: true } },
  { path: '/laboratorium/permintaan-pa', component: PermintaanLabPAView, meta: { requiresAuth: true } },
  { path: '/laboratorium/periksa-pk', component: PeriksaLabPKView, meta: { requiresAuth: true } },
  { path: '/laboratorium/periksa-pa', component: PeriksaLabPAView, meta: { requiresAuth: true } },
  { path: '/radiologi', component: RadiologiView, meta: { requiresAuth: true } },
  { path: '/radiologi/permintaan', component: PermintaanRadiologiView, meta: { requiresAuth: true } },
  { path: '/radiologi/periksa', component: PeriksaRadiologiView, meta: { requiresAuth: true } },
  { path: '/jadwal-operasi', component: JadwalOperasiView, meta: { requiresAuth: true } },
  { path: '/input-operasi', name: 'input-operasi', component: InputOperasiView, meta: { requiresAuth: true } },
  { path: '/farmasi', component: FarmasiView, meta: { requiresAuth: true } },
  { path: '/soap', component: SOAPView, meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Helper function untuk cek akses menu
const hasAccessToRoute = (path, user) => {
  if (!user) return false
  
  // Admin punya akses ke semua
  if (user.role === 'admin') return true
  
  // Jika tidak ada menu permissions atau kosong, beri akses ke semua (backward compatible)
  if (!user.menu_permissions || user.menu_permissions.length === 0) return true
  
  // Mapping path pattern ke menu key (urutan penting: paling spesifik dulu)
  const pathToMenu = [
    { pattern: /^\/rawat-jalan(\/|$)/, menu: 'rawat-jalan' },
    { pattern: /^\/rawat-inap(\/|$)/, menu: 'rawat-inap' },
    { pattern: /^\/laboratorium(\/|$)/, menu: 'laboratorium' },
    { pattern: /^\/radiologi(\/|$)/, menu: 'radiologi' },
    { pattern: /^\/jadwal-operasi$/, menu: 'jadwal-operasi' },
    { pattern: /^\/input-operasi/, menu: 'jadwal-operasi' },
    { pattern: /^\/farmasi/, menu: 'farmasi' },
    { pattern: /^\/pasien/, menu: 'pasien' },
    { pattern: /^\/soap/, menu: 'rawat-jalan' },
    { pattern: /^\/riwayat-perawatan/, menu: 'pasien' },
    { pattern: /^\/$/, menu: 'dashboard' },
    { pattern: /^\/dashboard/, menu: 'dashboard' }
  ]
  
  // Cek path dengan pattern
  for (const { pattern, menu } of pathToMenu) {
    if (pattern.test(path)) {
      return user.menu_permissions.includes(menu)
    }
  }
  
  return true // Default allow jika tidak ketemu mapping
}

// Helper untuk get first allowed route
const getFirstAllowedRoute = (user) => {
  if (!user || user.role === 'admin') return '/dashboard'
  if (!user.menu_permissions || user.menu_permissions.length === 0) return '/dashboard'
  
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
  
  const firstMenu = user.menu_permissions[0]
  return menuRoutes[firstMenu] || '/dashboard'
}

// Middleware untuk proteksi login dan permission
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const userJson = localStorage.getItem('user')
  const user = userJson ? JSON.parse(userJson) : null
  
  // Cek apakah perlu auth
  if (to.meta.requiresAuth && !token) {
    next('/login')
    return
  }
  
  // Jika sudah login, cek permission
  if (to.meta.requiresAuth && user) {
    // Cek akses ke route ini
    if (!hasAccessToRoute(to.path, user)) {
      // Redirect ke menu pertama yang diizinkan
      const allowedRoute = getFirstAllowedRoute(user)
      if (allowedRoute !== to.path) {
        next(allowedRoute)
        return
      }
    }
  }
  
  next()
})

export default router