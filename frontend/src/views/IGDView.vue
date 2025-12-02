<template>
  <AppLayout>
    <div class="container-fluid">
      <div class="page-header mb-4">
        <h2 class="page-title">🚑 IGD (Instalasi Gawat Darurat)</h2>
        <p class="page-subtitle">Manajemen Pasien IGD</p>
      </div>

      <!-- Filter Section -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Tanggal Dari</label>
              <input v-model="filter.tanggal_dari" type="date" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Tanggal Sampai</label>
              <input v-model="filter.tanggal_sampai" type="date" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Pencarian</label>
              <input v-model="filter.search" type="text" class="form-control" placeholder="Cari No. RM, Nama, Dokter...">
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <button @click="loadData" class="btn btn-primary w-100">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                Cari
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Patients Table -->
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">📋 Daftar Pasien IGD</h5>
        </div>
        <div class="card-body p-0">
          <div v-if="loading" class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div v-else-if="patients.length === 0" class="text-center p-5 text-muted">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-3">Tidak ada data pasien IGD</p>
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>No. Reg</th>
                  <th>No. Rawat</th>
                  <th>Tanggal</th>
                  <th>No. RM</th>
                  <th>Nama Pasien</th>
                  <th>JK</th>
                  <th>Umur</th>
                  <th>Dokter</th>
                  <th>Penjab</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="patient in patients" :key="patient.no_rawat">
                  <td>{{ patient.no_reg }}</td>
                  <td>{{ patient.no_rawat }}</td>
                  <td>{{ formatTanggal(patient.tgl_registrasi) }}<br><small class="text-muted">{{ patient.jam_reg }}</small></td>
                  <td>{{ patient.no_rkm_medis }}</td>
                  <td>
                    <strong>{{ patient.nm_pasien }}</strong>
                    <br><small class="text-muted">{{ patient.p_jawab }}</small>
                  </td>
                  <td>{{ patient.jk }}</td>
                  <td>{{ patient.umur }}</td>
                  <td><small>{{ patient.nm_dokter }}</small></td>
                  <td><small>{{ patient.png_jawab }}</small></td>
                  <td>
                    <span class="badge" :class="getStatusClass(patient.stts)">
                      {{ patient.stts }}
                    </span>
                  </td>
                  <td>
                    <button @click="viewDetail(patient)" class="btn btn-sm btn-info" title="Lihat Detail">
                      👁️
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-if="patients.length > 0" class="card-footer">
          <small class="text-muted">Total: {{ patients.length }} pasien</small>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import apiClient from '@/api/axios'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const patients = ref([])

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
  search: ''
})

const loadData = async () => {
  loading.value = true
  try {
    const { data } = await apiClient.get('/igd', {
      params: filter.value
    })
    if (data.success) {
      patients.value = data.data
    }
  } catch (error) {
    console.error('Error loading IGD patients:', error)
    alert('Gagal memuat data pasien IGD')
  } finally {
    loading.value = false
  }
}

const formatTanggal = (tanggal) => {
  if (!tanggal) return '-'
  const date = new Date(tanggal)
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const getStatusClass = (status) => {
  if (status === 'Belum') return 'bg-warning'
  if (status === 'Sudah') return 'bg-success'
  if (status === 'Batal') return 'bg-danger'
  return 'bg-secondary'
}

const viewDetail = (patient) => {
  router.push(`/pasien/${patient.no_rkm_medis}`)
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  color: #718096;
  font-size: 1rem;
  margin: 0;
}

.table td {
  vertical-align: middle;
}
</style>
