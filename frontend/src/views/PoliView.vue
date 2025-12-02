<template>
  <AppLayout>
    <div class="container-fluid">
      <!-- Filter Dropdown Button -->
      <div class="filter-dropdown-container mb-0 d-flex justify-content-end">
        <div class="dropdown">
          <button
            class="btn btn-filter dropdown-toggle"
            type="button"
            id="filterDropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false"
          >
            <i class="bi bi-funnel me-2"></i> Filter
          </button>
          <div class="dropdown-menu dropdown-menu-filter p-3" aria-labelledby="filterDropdown">
            <!-- Tanggal Dari -->
            <div class="mb-1">
              <input
                v-model="filter.tanggal_dari"
                type="date"
                class="form-control"
                @change="applyFilter"
              >
            </div>

            <!-- Tanggal Sampai -->
            <div class="mb-1">
              <input
                v-model="filter.tanggal_sampai"
                type="date"
                class="form-control"
                @change="applyFilter"
              >
            </div>

            <!-- Poli -->
            <div class="mb-1">
              <button
                class="btn btn-filter-collapse w-100"
                @click="showPoliModal = true"
              >
                <i class="bi bi-chevron-right me-2"></i>
                Poli
                <span v-if="filter.nm_poli" class="badge bg-primary ms-2">{{ filter.nm_poli }}</span>
              </button>
            </div>

            <!-- Dokter -->
            <div class="mb-1">
              <button
                class="btn btn-filter-collapse w-100"
                @click="showDokterModal = true"
              >
                <i class="bi bi-chevron-right me-2"></i>
                Dokter
                <span v-if="filter.nm_dokter" class="badge bg-primary ms-2">{{ filter.nm_dokter }}</span>
              </button>
            </div>

            <!-- Cara Bayar (hanya untuk tab daftar) -->
            <div v-if="activeTab === 'daftar'" class="mb-1">
              <button
                class="btn btn-filter-collapse w-100"
                @click="showCaraBayarModal = true"
              >
                <i class="bi bi-chevron-right me-2"></i>
                Cara Bayar
                <span v-if="filter.kd_pj" class="badge bg-primary ms-2">{{ getPenjabName(filter.kd_pj) }}</span>
              </button>
            </div>

            <!-- Status -->
            <div class="mb-1">
              <div class="filter-buttons">
                <button
                  class="btn btn-filter-option"
                  :class="{ active: filter.stts === 'Belum' }"
                  @click="selectFilter('stts', 'Belum')"
                >
                  Belum Diperiksa
                </button>
                <button
                  class="btn btn-filter-option"
                  :class="{ active: filter.stts === 'Sudah' }"
                  @click="selectFilter('stts', 'Sudah')"
                >
                  Selesai Diperiksa
                </button>
                <button
                  class="btn btn-filter-option"
                  :class="{ active: filter.stts === 'Batal' }"
                  @click="selectFilter('stts', 'Batal')"
                >
                  Batal
                </button>
              </div>
            </div>

            <!-- Status Bayar (hanya untuk tab daftar) -->
            <div v-if="activeTab === 'daftar'" class="mb-1">
              <div class="filter-buttons">
                <button
                  class="btn btn-filter-option"
                  :class="{ active: filter.status_bayar === 'Sudah Bayar' }"
                  @click="selectFilter('status_bayar', 'Sudah Bayar')"
                >
                  Sudah Bayar
                </button>
                <button
                  class="btn btn-filter-option"
                  :class="{ active: filter.status_bayar === 'Belum Bayar' }"
                  @click="selectFilter('status_bayar', 'Belum Bayar')"
                >
                  Belum Bayar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Poli -->
      <div v-if="showPoliModal" class="filter-modal-overlay" @click="showPoliModal = false">
        <div class="filter-modal" @click.stop>
          <div class="filter-modal-header">
            <h6 class="mb-0">Pilih Poli</h6>
            <button class="btn-close-modal" @click="showPoliModal = false">
              <i class="bi bi-x"></i>
            </button>
          </div>
          <div class="filter-modal-body">
            <div class="filter-buttons">
              <button
                class="btn btn-filter-option"
                :class="{ active: filter.nm_poli === '' }"
                @click="selectFilterAndClose('nm_poli', '', 'poli')"
              >
                Semua
              </button>
              <button
                v-for="poli in listPoli"
                :key="poli.kd_poli"
                class="btn btn-filter-option"
                :class="{ active: filter.nm_poli === poli.nm_poli }"
                @click="selectFilterAndClose('nm_poli', poli.nm_poli, 'poli')"
              >
                {{ poli.nm_poli }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Dokter -->
      <div v-if="showDokterModal" class="filter-modal-overlay" @click="showDokterModal = false">
        <div class="filter-modal" @click.stop>
          <div class="filter-modal-header">
            <h6 class="mb-0">Pilih Dokter</h6>
            <button class="btn-close-modal" @click="showDokterModal = false">
              <i class="bi bi-x"></i>
            </button>
          </div>
          <div class="filter-modal-body">
            <div class="filter-buttons">
              <button
                class="btn btn-filter-option"
                :class="{ active: filter.nm_dokter === '' }"
                @click="selectFilterAndClose('nm_dokter', '', 'dokter')"
              >
                Semua
              </button>
              <button
                v-for="dokter in listDokter"
                :key="dokter.kd_dokter"
                class="btn btn-filter-option"
                :class="{ active: filter.nm_dokter === dokter.nm_dokter }"
                @click="selectFilterAndClose('nm_dokter', dokter.nm_dokter, 'dokter')"
              >
                {{ dokter.nm_dokter }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Cara Bayar -->
      <div v-if="showCaraBayarModal" class="filter-modal-overlay" @click="showCaraBayarModal = false">
        <div class="filter-modal" @click.stop>
          <div class="filter-modal-header">
            <h6 class="mb-0">Pilih Cara Bayar</h6>
            <button class="btn-close-modal" @click="showCaraBayarModal = false">
              <i class="bi bi-x"></i>
            </button>
          </div>
          <div class="filter-modal-body">
            <div class="filter-buttons">
              <button
                class="btn btn-filter-option"
                :class="{ active: filter.kd_pj === '' }"
                @click="selectFilterAndClose('kd_pj', '', 'carabayar')"
              >
                Semua
              </button>
              <button
                v-for="penjab in listPenjab"
                :key="penjab.kd_pj"
                class="btn btn-filter-option"
                :class="{ active: filter.kd_pj === penjab.kd_pj }"
                @click="selectFilterAndClose('kd_pj', penjab.kd_pj, 'carabayar')"
              >
                {{ penjab.png_jawab }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation & Content Container -->
      <div class="sticky-tabs-container mt-1">
        <div class="card shadow-sm">
          <!-- Tab Navigation -->
          <div class="card-header bg-white p-0 sticky-tabs">
            <div class="d-flex justify-content-between align-items-center">
              <ul class="nav nav-tabs border-0 mb-0">
                <li class="nav-item">
                  <a class="nav-link" :class="{ active: activeTab === 'daftar' }" @click="activeTab = 'daftar'" href="#" @click.prevent>
                    📋 Daftar Pasien Poliklinik
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" :class="{ active: activeTab === 'rujukan' }" @click="activeTab = 'rujukan'" href="#" @click.prevent>
                    🔄 Rujukan Internal Poli
                  </a>
                </li>
              </ul>
              <div class="search-box me-3">
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                  </span>
                  <input
                    v-model="filter.search"
                    type="text"
                    class="form-control border-start-0"
                    placeholder="Cari No. RM, Nama..."
                    @input="onSearchInput"
                  >
                  <button
                    v-if="filter.search"
                    class="btn btn-sm btn-link text-muted"
                    @click="clearSearch"
                    type="button"
                  >
                    <i class="bi bi-x-circle"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Patients Table - Tab Daftar Pasien -->
          <div v-if="activeTab === 'daftar'" class="card-body p-0">
            <div v-if="loading" class="text-center p-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else-if="patients.length === 0" class="text-center p-5 text-muted">
              <i class="bi bi-inbox" style="font-size: 3rem;"></i>
              <p class="mt-3">Tidak ada data pasien poliklinik</p>
            </div>
            <div v-else class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light sticky-thead">
                  <tr>
                    <th>No. RM</th>
                    <th>Nama Pasien</th>
                    <th>Antrian</th>
                    <th>Dokter</th>
                    <th>Poliklinik</th>
                    <th>Cara Bayar</th>
                    <th>No. Rawat</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Status Bayar</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="patient in sortedPatients" :key="patient.no_rawat" :class="{ 'row-status-sudah': patient.stts === 'Sudah' }">
                    <td>
                      <div class="dropdown">
                        <button class="btn btn-link text-decoration-none p-0 dropdown-toggle" type="button" :id="'dropdown-' + patient.no_rawat" data-bs-toggle="dropdown" aria-expanded="false">
                          {{ patient.no_rkm_medis }}
                        </button>
                        <ul class="dropdown-menu" :aria-labelledby="'dropdown-' + patient.no_rawat">
                          <li>
                            <a class="dropdown-item" href="#" @click.prevent="inputSOAP(patient)">
                              📝 Input SOAP
                            </a>
                          </li>
                        </ul>
                      </div>
                    </td>
                    <td><strong>{{ patient.nm_pasien }}</strong> ({{ patient.umur }})</td>
                    <td>
                      <button
                        class="btn btn-antrian"
                        @click="panggilAntrian(patient)"
                        :title="'Panggil antrian nomor ' + patient.no_reg"
                      >
                        <i class="bi bi-megaphone me-1"></i>
                        {{ patient.no_reg }}
                      </button>
                    </td>
                    <td><small>{{ patient.nm_dokter }}</small></td>
                    <td><small>{{ patient.nm_poli }}</small></td>
                    <td><small>{{ patient.png_jawab }}</small></td>
                    <td><small>{{ patient.no_rawat }}</small></td>
                    <td>{{ formatTanggal(patient.tgl_registrasi) }}<br><small class="text-muted">{{ patient.jam_reg }}</small></td>
                    <td>
                      <span class="badge" :class="getStatusClass(patient.stts)">
                        {{ patient.stts }}
                      </span>
                    </td>
                    <td>
                      <span class="badge" :class="getStatusBayarClass(patient.status_bayar)">
                        {{ patient.status_bayar }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div v-if="patients.length > 0" class="card-footer">
            <small class="text-muted">Total: {{ patients.length }} pasien</small>
          </div>

          <!-- Rujukan Internal Table - Tab Rujukan Internal -->
          <div v-if="activeTab === 'rujukan'" class="card-body p-0">
            <div v-if="loading" class="text-center p-5">
              <div class="spinner-border text-info" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else-if="rujukanInternal.length === 0" class="text-center p-5 text-muted">
              <i class="bi bi-inbox" style="font-size: 3rem;"></i>
              <p class="mt-3">Tidak ada data rujukan internal poli</p>
            </div>
            <div v-else class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light sticky-thead">
                  <tr>
                    <th>No. RM</th>
                    <th>Nama Pasien</th>
                    <th>Dokter Rujukan</th>
                    <th>Poli Rujukan</th>
                    <th>Penanggung Jawab</th>
                    <th>No. Rawat</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>No. Telp</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="rujukan in rujukanInternal" :key="rujukan.no_rawat">
                    <td>
                      <div class="dropdown">
                        <button class="btn btn-link text-decoration-none p-0 dropdown-toggle" type="button" :id="'dropdown-rujukan-' + rujukan.no_rawat" data-bs-toggle="dropdown" aria-expanded="false">
                          {{ rujukan.no_rkm_medis }}
                        </button>
                        <ul class="dropdown-menu" :aria-labelledby="'dropdown-rujukan-' + rujukan.no_rawat">
                          <li>
                            <a class="dropdown-item" href="#" @click.prevent="inputSOAP(rujukan)">
                              📝 Input SOAP
                            </a>
                          </li>
                        </ul>
                      </div>
                    </td>
                    <td>
                      <strong>{{ rujukan.nm_pasien }}</strong>
                      <br>
                      <small class="text-muted">{{ rujukan.umur }}</small>
                    </td>
                    <td><small>{{ rujukan.nm_dokter }}</small></td>
                    <td><small>{{ rujukan.nm_poli }}</small></td>
                    <td>
                      <small>{{ rujukan.png_jawab }}</small>
                      <br>
                      <small class="text-muted">{{ rujukan.p_jawab }} ({{ rujukan.hubunganpj }})</small>
                    </td>
                    <td><small>{{ rujukan.no_rawat }}</small></td>
                    <td>{{ formatTanggal(rujukan.tgl_registrasi) }}<br><small class="text-muted">{{ rujukan.jam_reg }}</small></td>
                    <td>
                      <span class="badge" :class="getStatusClass(rujukan.stts)">
                        {{ rujukan.stts }}
                      </span>
                    </td>
                    <td><small>{{ rujukan.no_tlp || '-' }}</small></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div v-if="rujukanInternal.length > 0" class="card-footer">
            <small class="text-muted">Total: {{ rujukanInternal.length }} rujukan internal</small>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import apiClient from '@/api/axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const activeTab = ref('daftar')
const patients = ref([])
const rujukanInternal = ref([])
const listPoli = ref([])
const listDokter = ref([])
const listPenjab = ref([])

// Toggle state untuk modal filter
const showPoliModal = ref(false)
const showDokterModal = ref(false)
const showCaraBayarModal = ref(false)

// Get logged in user
const user = computed(() => {
  const userData = localStorage.getItem('user')
  return userData ? JSON.parse(userData) : null
})

// Helper function untuk mendapatkan tanggal hari ini
const getTodayDate = () => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const filter = ref({
  tanggal_dari: getTodayDate(),
  tanggal_sampai: getTodayDate(),
  search: '',
  nm_poli: '',
  nm_dokter: '',
  kd_pj: '',
  stts: '',
  status_bayar: ''
})

// Computed property untuk sorting patients
const sortedPatients = computed(() => {
  return [...patients.value].sort((a, b) => {
    const statusA = a.stts === 'Sudah' ? 1 : 0
    const statusB = b.stts === 'Sudah' ? 1 : 0

    // Jika status berbeda (satu "Sudah", satu bukan), yang "Sudah" ke bawah
    if (statusA !== statusB) {
      return statusA - statusB
    }

    // Jika status sama, urutkan berdasarkan no_reg (ascending)
    const noRegA = parseInt(a.no_reg) || 0
    const noRegB = parseInt(b.no_reg) || 0
    return noRegA - noRegB
  })
})

const loadData = async () => {
  loading.value = true
  try {
    const params = { ...filter.value }

    // Auto filter by dokter if user is not admin
    if (user.value && user.value.role !== 'admin' && user.value.nama) {
      params.nm_dokter = user.value.nama
    }

    const { data } = await apiClient.get('/poli', {
      params
    })
    if (data.success) {
      patients.value = data.data
    }
  } catch (error) {
    console.error('Error loading Poli patients:', error)
    alert('Gagal memuat data pasien poliklinik')
  } finally {
    loading.value = false
  }
}

const loadPoli = async () => {
  try {
    const { data } = await apiClient.get('/poli/list-poli')
    if (data.success) {
      listPoli.value = data.data
    }
  } catch (error) {
    console.error('Error loading Poli list:', error)
  }
}

const loadDokter = async () => {
  try {
    const { data } = await apiClient.get('/poli/list-dokter')
    if (data.success) {
      listDokter.value = data.data
    }
  } catch (error) {
    console.error('Error loading Dokter list:', error)
  }
}

const loadPenjab = async () => {
  try {
    const { data } = await apiClient.get('/poli/list-penjab')
    if (data.success) {
      listPenjab.value = data.data
    }
  } catch (error) {
    console.error('Error loading Penjab list:', error)
  }
}

const loadRujukanInternal = async () => {
  loading.value = true
  try {
    const params = {
      tanggal_dari: filter.value.tanggal_dari,
      tanggal_sampai: filter.value.tanggal_sampai,
      search: filter.value.search,
      nm_poli: filter.value.nm_poli,
      nm_dokter: filter.value.nm_dokter,
      stts: filter.value.stts
    }

    // Auto filter by dokter if user is not admin
    if (user.value && user.value.role !== 'admin' && user.value.nama) {
      params.nm_dokter = user.value.nama
    }

    const { data } = await apiClient.get('/poli/rujukan-internal', {
      params
    })
    if (data.success) {
      rujukanInternal.value = data.data
    }
  } catch (error) {
    console.error('Error loading Rujukan Internal:', error)
    alert('Gagal memuat data rujukan internal')
  } finally {
    loading.value = false
  }
}

const formatTanggal = (tanggal) => {
  if (!tanggal) return '-'
  const date = new Date(tanggal)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}/${month}/${year}`
}

const getStatusClass = (status) => {
  if (status === 'Belum') return 'bg-warning'
  if (status === 'Sudah') return 'bg-success'
  if (status === 'Batal') return 'bg-danger'
  return 'bg-secondary'
}

const getStatusBayarClass = (status) => {
  if (status === 'Sudah Bayar') return 'bg-success'
  if (status === 'Belum Bayar') return 'bg-warning'
  return 'bg-secondary'
}

const panggilAntrian = (patient) => {
  // TODO: Implementasi panggil antrian
  // Bisa menggunakan Web Speech API atau sistem antrian lainnya
  console.log('Panggil antrian:', patient.no_reg, patient.nm_pasien)
  alert(`Memanggil antrian nomor ${patient.no_reg}\n${patient.nm_pasien}\nKe ${patient.nm_poli}`)
}

const inputSOAP = (patient) => {
  router.push({
    path: '/soap',
    query: {
      no_rkm_medis: patient.no_rkm_medis,
      nm_pasien: patient.nm_pasien,
      umur: patient.umur,
      nm_dokter: patient.nm_dokter,
      nm_poli: patient.nm_poli,
      no_rawat: patient.no_rawat,
      tgl_registrasi: patient.tgl_registrasi,
      jam_reg: patient.jam_reg,
      png_jawab: patient.png_jawab
    }
  })
}

// Auto search langsung tanpa debounce
const onSearchInput = () => {
  if (activeTab.value === 'daftar') {
    loadData()
  } else {
    loadRujukanInternal()
  }
}

// Clear search
const clearSearch = () => {
  filter.value.search = ''
  if (activeTab.value === 'daftar') {
    loadData()
  } else {
    loadRujukanInternal()
  }
}

// Select filter dan apply
const selectFilter = (filterKey, value) => {
  filter.value[filterKey] = value
  applyFilter()
}

// Select filter dan close modal
const selectFilterAndClose = (filterKey, value, modalType) => {
  filter.value[filterKey] = value
  applyFilter()

  // Close modal
  if (modalType === 'poli') showPoliModal.value = false
  if (modalType === 'dokter') showDokterModal.value = false
  if (modalType === 'carabayar') showCaraBayarModal.value = false
}

// Apply filter
const applyFilter = () => {
  if (activeTab.value === 'daftar') {
    loadData()
  } else {
    loadRujukanInternal()
  }
}

// Helper function untuk mendapatkan nama penjab
const getPenjabName = (kd_pj) => {
  const penjab = listPenjab.value.find(p => p.kd_pj === kd_pj)
  return penjab ? penjab.png_jawab : ''
}

// Watch for tab changes
watch(activeTab, (newTab) => {
  if (newTab === 'daftar') {
    loadData()
  } else if (newTab === 'rujukan') {
    loadRujukanInternal()
  }
})

onMounted(() => {
  loadPoli()
  loadDokter()
  loadPenjab()
  loadData()
})
</script>

<style scoped>
/* Filter Dropdown */
.filter-dropdown-container {
  padding: 0;
  position: relative;
  z-index: 1050;
}

.btn-filter {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
  border: none;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
  transition: all 0.3s ease;
  position: relative;
  z-index: 1051;
}

.btn-filter:hover {
  background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
  transform: translateY(-1px);
  color: white;
}

.dropdown-menu-filter {
  width: 195px;
  max-height: 450px;
  overflow-y: auto;
  border: none;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  border-radius: 12px;
  position: absolute;
  z-index: 1052;
}

.filter-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.btn-filter-option {
  background: #475569;
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.875rem;
  text-align: left;
  transition: all 0.3s ease;
  width: 100%;
}

.btn-filter-option:hover {
  background: #334155;
  color: white;
  transform: translateX(4px);
}

.btn-filter-option.active {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.dropdown-menu-filter .form-label {
  color: #1e293b;
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
}

.dropdown-menu-filter .form-control {
  border: 2px solid #e2e8f0;
  border-radius: 6px;
  padding: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.dropdown-menu-filter .form-control:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Button Collapse Filter */
.btn-filter-collapse {
  background: #e2e8f0;
  color: #1e293b;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  text-align: left;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
}

.btn-filter-collapse:hover {
  background: #cbd5e1;
  color: #1e293b;
}

.btn-filter-collapse.active {
  background: #3b82f6;
  color: white;
}

.btn-filter-collapse .badge {
  margin-left: auto;
  font-size: 0.75rem;
}

/* Filter Modal */
.filter-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  z-index: 1060;
  padding: 1rem;
}

.filter-modal {
  background: white;
  border-radius: 12px;
  width: 300px;
  max-height: calc(100vh - 2rem);
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  margin-top: 60px;
}

.filter-modal-header {
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8f9fa;
}

.filter-modal-header h6 {
  font-weight: 600;
  color: #1e293b;
}

.btn-close-modal {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #64748b;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.2s;
}

.btn-close-modal:hover {
  background: #e2e8f0;
  color: #1e293b;
}

.filter-modal-body {
  padding: 1rem;
  overflow-y: auto;
  flex: 1;
}

.table td {
  vertical-align: middle;
}

/* Button Antrian */
.btn-antrian {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  padding: 0.3rem 0.75rem;
  border-radius: 5px;
  font-weight: 600;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.btn-antrian:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
  transform: translateY(-1px);
}

.btn-antrian:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.btn-antrian i {
  font-size: 0.75rem;
}

/* Baris dengan status Sudah */
.row-status-sudah {
  background-color: #d1f4e0 !important;
}

.row-status-sudah td {
  background-color: #d1f4e0 !important;
}

.row-status-sudah:hover {
  background-color: #b8efd0 !important;
}

.row-status-sudah:hover td {
  background-color: #b8efd0 !important;
}

.sticky-tabs {
  position: sticky;
  top: 0;
  z-index: 1000;
  background-color: white;
  border-bottom: 2px solid #dee2e6;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.sticky-tabs .nav-tabs {
  margin-bottom: 0 !important;
  padding: 0.5rem 0 0 1rem;
}

/* Search Box */
.search-box {
  padding: 0.5rem 0;
}

.search-box .input-group {
  width: 250px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.search-box .input-group:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-box .input-group-text {
  border: none;
  padding: 0.375rem 0.5rem;
}

.search-box .form-control {
  border: none;
  padding: 0.375rem 0.5rem;
  box-shadow: none !important;
}

.search-box .form-control:focus {
  border: none;
}

.search-box .btn-link {
  border: none;
  padding: 0.375rem 0.5rem;
  text-decoration: none;
}

.search-box .btn-link:hover {
  background: #f3f4f6;
}

.sticky-tabs .nav-link {
  border: none;
  border-bottom: 3px solid transparent;
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.3s ease;
}

.sticky-tabs .nav-link:hover {
  color: #0d6efd;
  background-color: #f8f9fa;
  border-radius: 8px 8px 0 0;
}

.sticky-tabs .nav-link.active {
  color: #0d6efd;
  background-color: transparent;
  border-bottom-color: #0d6efd;
  font-weight: 600;
}

.table-responsive {
  max-height: calc(100vh - 300px);
  overflow-y: auto;
}

.sticky-thead th {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: #f8f9fa;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
  border-bottom: 2px solid #dee2e6;
}
</style>
