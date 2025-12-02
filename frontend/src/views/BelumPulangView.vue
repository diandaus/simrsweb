<template>
  <AppLayout>
    <div class="container-fluid">
      
      <!-- Filter Section - Compact -->
      <div class="card mb-3">
        <div class="card-body py-2">
          <div class="row g-2 align-items-end">
            <!-- Date Range -->
            <div class="col-auto">
              <label class="form-label mb-1 small">Tgl Masuk:</label>
              <input type="date" class="form-control form-control-sm" v-model="tglMasukStart" style="width: 140px;">
            </div>
            <div class="col-auto">
              <span class="text-muted" style="margin-top: 28px;">s/d</span>
            </div>
            <div class="col-auto">
              <label class="form-label mb-1 small">&nbsp;</label>
              <input type="date" class="form-control form-control-sm" v-model="tglMasukEnd" style="width: 140px;">
            </div>

            <div class="col-auto">
              <select class="form-select form-select-sm" v-model="bangsalFilter" style="width: 150px;">
                <option value="">Semua Bangsal</option>
                <option v-for="b in bangsalList" :key="b.kd_bangsal" :value="b.nm_bangsal">{{ b.nm_bangsal }}</option>
              </select>
            </div>

             <div class="col-auto">
              <input type="text" class="form-control form-control-sm" v-model="search" placeholder="Cari..." @keyup.enter="loadData" style="width: 180px;">
            </div>

            <div class="col-auto">
              <button class="btn btn-primary btn-sm" @click="loadData" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-search"></i>
                Cari
              </button>
              <button class="btn btn-secondary btn-sm ms-1" @click="resetFilter" :disabled="loading">
                <i class="bi bi-arrow-clockwise"></i>
                Reset
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 text-muted">Memuat data...</p>
      </div>

      <!-- Table Section -->
      <div v-else class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <span class="fw-bold">
            <i class="bi bi-table me-2"></i>
            Data Rawat Inap
          </span>
          <span class="badge bg-primary">{{ dataList.length }} pasien</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered mb-0 table-compact">
              <thead class="table-primary sticky-top">
                <tr>
                  <th style="min-width: 150px;">No Rawat</th>
                  <th style="min-width: 100px;">Nomer RM</th>
                  <th style="min-width: 200px;">Nama Pasien</th>
                  <th style="min-width: 200px;">Alamat Pasien</th>
                  <th style="min-width: 150px;">Penanggung Jawab</th>
                  <th style="min-width: 120px;">Hubungan P.J.</th>
                  <th style="min-width: 100px;">Jenis Bayar</th>
                  <th style="min-width: 180px;">Kamar</th>
                  <th class="text-end" style="min-width: 120px;">Tarif Kamar</th>
                  <th style="min-width: 150px;">Diagnosa Awal</th>
                  <th style="min-width: 150px;">Diagnosa Akhir</th>
                  <th style="min-width: 110px;">Tgl.Masuk</th>
                  <th style="min-width: 90px;">Jam Masuk</th>
                  <th style="min-width: 110px;">Tgl.Keluar</th>
                  <th style="min-width: 90px;">Jam Keluar</th>
                  <th class="text-end" style="min-width: 120px;">Ttl.Biaya</th>
                  <th style="min-width: 150px;">Status Pulang</th>
                  <th class="text-center" style="min-width: 80px;">Lama</th>
                  <th style="min-width: 150px;">Dokter</th>
                  <th style="min-width: 110px;">Status Bayar</th>
                  <th style="min-width: 100px;">Agama</th>
                </tr>
              </thead>
              <tbody>
                <template v-if="dataList.length === 0">
                  <tr>
                    <td colspan="21" class="text-center py-5 text-muted">
                      <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                      Tidak ada data
                    </td>
                  </tr>
                </template>
                <template v-else v-for="(item, index) in dataList" :key="item.no_rawat">
                  <!-- Main Patient Row - Single Line -->
                  <tr>
                    <td>{{ item.no_rawat }}</td>
                    <td>{{ item.no_rkm_medis }}</td>
                    <td>{{ item.nm_pasien }} ({{ item.umur }})</td>
                    <td>{{ item.alamat }}</td>
                    <td>{{ item.p_jawab }}</td>
                    <td>{{ item.hubunganpj }}</td>
                    <td>{{ item.png_jawab }}</td>
                    <td>{{ item.kamar }}</td>
                    <td class="text-end">{{ formatRupiah(item.trf_kamar) }}</td>
                    <td>{{ item.diagnosa_awal || '-' }}</td>
                    <td>{{ item.diagnosa_akhir || '-' }}</td>
                    <td>{{ formatTanggal(item.tgl_masuk) }}</td>
                    <td>{{ item.jam_masuk }}</td>
                    <td>{{ item.tgl_keluar ? formatTanggal(item.tgl_keluar) : '-' }}</td>
                    <td>{{ item.jam_keluar || '-' }}</td>
                    <td class="text-end">{{ formatRupiah(item.ttl_biaya) }}</td>
                    <td>
                      <span class="badge" :class="item.stts_pulang === '-' ? 'bg-warning text-dark' : 'bg-success'">
                        {{ item.stts_pulang === '-' ? 'Belum Pulang' : item.stts_pulang }}
                      </span>
                    </td>
                    <td class="text-center">{{ item.lama }}</td>
                    <td>{{ getDokterName(item) }}</td>
                    <td>
                      <span class="badge" :class="item.status_bayar === 'Sudah Bayar' ? 'bg-success' : 'bg-danger'">
                        {{ item.status_bayar }}
                      </span>
                    </td>
                    <td>{{ item.agama }}</td>
                  </tr>

                  <!-- Ranap Gabung (Bayi) Row - Single Line -->
                  <tr v-if="item.ranap_gabung" class="table-warning">
                    <td colspan="2">
                      <i class="bi bi-arrow-return-right me-1"></i>
                      <small class="text-muted">BAYI GABUNG</small>
                    </td>
                    <td>{{ item.ranap_gabung.nm_pasien }} ({{ item.ranap_gabung.umur }})</td>
                    <td>{{ item.ranap_gabung.alamat }}</td>
                    <td colspan="3"></td>
                    <td>{{ item.kamar }}</td>
                    <td class="text-end">{{ formatRupiah(item.trf_kamar * 0.5) }}</td>
                    <td colspan="2"></td>
                    <td>{{ formatTanggal(item.tgl_masuk) }}</td>
                    <td>{{ item.jam_masuk }}</td>
                    <td>{{ item.tgl_keluar ? formatTanggal(item.tgl_keluar) : '-' }}</td>
                    <td>{{ item.jam_keluar || '-' }}</td>
                    <td class="text-end">{{ formatRupiah(item.ttl_biaya * 0.5) }}</td>
                    <td colspan="5">
                      <small class="text-muted fst-italic">Tarif 50% dari pasien utama</small>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import apiClient from '@/api/axios'
import Swal from 'sweetalert2'

// State
const loading = ref(false)
const dataList = ref([])
const bangsalList = ref([])

// Get today's date in YYYY-MM-DD format
const getTodayDate = () => {
  const today = new Date()
  return today.toISOString().split('T')[0]
}

// Get date 30 days ago
const getDate30DaysAgo = () => {
  const date = new Date()
  date.setDate(date.getDate() - 30)
  return date.toISOString().split('T')[0]
}

// Filters
const tglMasukStart = ref(getDate30DaysAgo())
const tglMasukEnd = ref(getTodayDate())
const bangsalFilter = ref('')
const statusBayarFilter = ref('')
const search = ref('')

// Load Bangsal List
const loadBangsal = async () => {
  try {
    const { data } = await apiClient.get('/ranap/bangsal')
    if (data.success) {
      bangsalList.value = data.data
    }
  } catch (error) {
    console.error('Error loading bangsal:', error)
  }
}

// Load Data
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      tgl_masuk_start: tglMasukStart.value,
      tgl_masuk_end: tglMasukEnd.value,
      bangsal: bangsalFilter.value,
      status_bayar: statusBayarFilter.value,
      search: search.value
    }

    const { data } = await apiClient.get('/ranap', { params })

    if (data.success) {
      dataList.value = data.data
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: data.message || 'Gagal memuat data'
      })
    }
  } catch (error) {
    console.error('Error loading data:', error)
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || 'Terjadi kesalahan saat memuat data'
    })
  } finally {
    loading.value = false
  }
}

// Reset Filter
const resetFilter = () => {
  tglMasukStart.value = getDate30DaysAgo()
  tglMasukEnd.value = getTodayDate()
  bangsalFilter.value = ''
  statusBayarFilter.value = ''
  search.value = ''
  loadData()
}

// Format Helpers
const formatRupiah = (value) => {
  if (!value) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
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

// Get Dokter Name - prioritize DPJP, fallback to original dokter
const getDokterName = (item) => {
  // Jika ada dokter DPJP, tampilkan dokter DPJP
  if (item.nm_dokter_dpjp) {
    return item.nm_dokter_dpjp
  }
  // Jika tidak ada DPJP, tampilkan dokter sebelumnya
  return item.nm_dokter || '-'
}

// Initialize
onMounted(() => {
  loadBangsal()
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

.table {
  font-size: 0.813rem;
}

.table-compact {
  font-size: 0.75rem;
}

.table-compact td,
.table-compact th {
  padding: 0.35rem 0.5rem;
  vertical-align: middle;
  white-space: nowrap;
}

.table thead th {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: #cfe2ff !important;
  font-weight: 600;
  white-space: nowrap;
  font-size: 0.75rem;
  padding: 0.5rem 0.5rem;
  border-right: 1px solid #dee2e6;
}

.table tbody tr:hover {
  background-color: #f8f9fa;
}

.table-bordered {
  border: 1px solid #dee2e6;
}

.table-bordered td,
.table-bordered th {
  border: 1px solid #dee2e6;
}

.btn-check:checked + .btn-outline-primary {
  background-color: #0d6efd;
  color: white;
}

.card {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  border: 1px solid #e9ecef;
}

.table-warning {
  background-color: #fff3cd !important;
}

.table-warning:hover {
  background-color: #ffe69c !important;
}
</style>
