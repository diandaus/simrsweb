<template>
  <AppLayout>
    <div class="container-fluid">
      <!-- Filter Section -->
      <div class="filter-compact mb-3">
        <div class="row g-2 align-items-end">
          <div v-if="filter.status === 'Selesai'" class="col-auto">
            <label class="form-label small mb-1">Tgl Dari</label>
            <input v-model="filter.tanggal_dari" type="date" class="form-control form-control-sm" style="width: 130px;">
          </div>
          <div v-if="filter.status === 'Selesai'" class="col-auto">
            <label class="form-label small mb-1">Tgl Sampai</label>
            <input v-model="filter.tanggal_sampai" type="date" class="form-control form-control-sm" style="width: 130px;">
          </div>
          <div class="col-auto">
            <label class="form-label small mb-1">Pencarian</label>
            <input v-model="filter.search" type="text" class="form-control form-control-sm" placeholder="Cari No. RM, Nama..." style="width: 200px;" @keyup.enter="loadData">
          </div>
          <div class="col-auto">
            <button @click="loadData" class="btn btn-primary btn-sm">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-search me-1"></i> Cari
            </button>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="card shadow-sm">
        <!-- Tab Status Filter -->
        <ul class="nav nav-tabs mb-0 px-3 pt-2">
          <li class="nav-item">
            <a
              class="nav-link"
              :class="{ active: filter.status === 'Menunggu' }"
              @click="setStatusFilter('Menunggu')"
              href="javascript:void(0)"
            >
              <i class="bi bi-clock me-1"></i> Menunggu
              <span class="badge bg-warning text-dark ms-1">{{ countByStatus.menunggu }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a
              class="nav-link"
              :class="{ active: filter.status === 'Proses Operasi' }"
              @click="setStatusFilter('Proses Operasi')"
              href="javascript:void(0)"
            >
              <i class="bi bi-activity me-1"></i> Proses Operasi
              <span class="badge bg-info ms-1">{{ countByStatus.proses }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a
              class="nav-link"
              :class="{ active: filter.status === 'Selesai' }"
              @click="setStatusFilter('Selesai')"
              href="javascript:void(0)"
            >
              <i class="bi bi-check-circle me-1"></i> Selesai
              <span class="badge bg-success ms-1">{{ countByStatus.selesai }}</span>
            </a>
          </li>
        </ul>
        <div class="card-body p-0">
          <div v-if="loading" class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>

          <div v-else-if="jadwalOperasi.length === 0" class="alert alert-info m-3">
            Tidak ada data jadwal operasi
          </div>

          <div v-else class="table-responsive">
            <table class="table table-hover table-striped mb-0">
              <thead class="table-light sticky-top">
                <tr>
                  <th style="width: 50px;">No</th>
                  <th>No. Rawat</th>
                  <th>Pasien</th>
                  <th style="width: 80px;">Umur</th>
                  <th style="width: 60px;">JK</th>
                  <th>Tanggal</th>
                  <th>Jam Mulai</th>
                  <th>Jam Selesai</th>
                  <th>Status</th>
                  <th>Ruangan</th>
                  <th>Diagnosa</th>
                  <th>Paket Operasi</th>
                  <th>Dokter</th>
                  <th style="width: 80px;">Jenis</th>
                  <th>Ruang OK</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in jadwalOperasi" :key="index">
                  <td>{{ index + 1 }}</td>
                  <td>
                    <div class="dropdown">
                      <button class="btn-no-rawat dropdown-toggle" type="button" :id="'dropdown-' + index" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ item.no_rawat }}
                      </button>
                      <ul class="dropdown-menu" :aria-labelledby="'dropdown-' + index">
                        <li>
                          <a class="dropdown-item" href="#" @click.prevent="inputData(item)">
                            <i class="bi bi-pencil-square me-2"></i> Input Data
                          </a>
                        </li>
                      </ul>
                    </div>
                  </td>
                  <td>
                    <strong>{{ item.nm_pasien }}</strong><br>
                    <small class="text-muted">{{ item.no_rkm_medis }}</small>
                  </td>
                  <td>{{ item.umur }}</td>
                  <td>{{ item.jk }}</td>
                  <td>{{ formatDate(item.tanggal) }}</td>
                  <td>{{ item.jam_mulai }}</td>
                  <td>{{ item.jam_selesai }}</td>
                  <td>
                    <span :class="getStatusClass(item.status)">
                      {{ item.status }}
                    </span>
                  </td>
                  <td>{{ item.kamar }}</td>
                  <td><small>{{ item.diagnosa }}</small></td>
                  <td>
                    <small>{{ item.kode_paket }}</small><br>
                    {{ item.nm_perawatan }}
                  </td>
                  <td>{{ item.nm_dokter }}</td>
                  <td>
                    <span :class="item.order === 'Ranap' ? 'badge bg-info' : 'badge bg-success'">
                      {{ item.order }}
                    </span>
                  </td>
                  <td>{{ item.nm_ruang_ok }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-if="!loading && jadwalOperasi.length > 0" class="card-footer bg-light">
          <small class="text-muted">Total: <strong>{{ jadwalOperasi.length }}</strong> jadwal operasi</small>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '@/components/AppLayout.vue'
import apiClient from '@/api/axios'

const router = useRouter()
const loading = ref(false)
const jadwalOperasi = ref([])

const filter = ref({
  status: 'Menunggu',
  tanggal_dari: new Date().toISOString().split('T')[0],
  tanggal_sampai: new Date().toISOString().split('T')[0],
  search: ''
})

const countByStatus = reactive({
  menunggu: 0,
  proses: 0,
  selesai: 0
})

const setStatusFilter = (status) => {
  filter.value.status = status
  loadData()
}

const loadCountByStatus = async () => {
  try {
    // Load count for each status
    const [menungguRes, prosesRes] = await Promise.all([
      apiClient.get('/jadwal-operasi', { params: { status: 'Menunggu' } }),
      apiClient.get('/jadwal-operasi', { params: { status: 'Proses Operasi' } })
    ])

    if (menungguRes.data.success) {
      countByStatus.menunggu = menungguRes.data.data.length
    }
    if (prosesRes.data.success) {
      countByStatus.proses = prosesRes.data.data.length
    }
    // Selesai count akan di-set saat tab selesai dipilih (karena butuh tanggal)
    countByStatus.selesai = '-'
  } catch (err) {
    console.error('Load count error:', err)
  }
}

const loadData = async () => {
  loading.value = true
  try {
    const params = {
      status: filter.value.status,
      search: filter.value.search
    }

    if (filter.value.status === 'Rentang Tanggal' || filter.value.status === 'Selesai') {
      params.tanggal_dari = filter.value.tanggal_dari
      params.tanggal_sampai = filter.value.tanggal_sampai
    }

    const { data } = await apiClient.get('/jadwal-operasi', { params })

    if (data.success) {
      jadwalOperasi.value = data.data
    }
  } catch (err) {
    console.error('Load jadwal operasi error:', err)
  } finally {
    loading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  return `${day}/${month}/${year}`
}

const getStatusClass = (status) => {
  const classes = {
    'Menunggu': 'badge bg-warning',
    'Proses Operasi': 'badge bg-primary',
    'Selesai': 'badge bg-success',
    'Batal': 'badge bg-danger'
  }
  return classes[status] || 'badge bg-secondary'
}

const inputData = (item) => {
  router.push({
    name: 'input-operasi',
    query: {
      no_rawat: item.no_rawat,
      no_rkm_medis: item.no_rkm_medis,
      nm_pasien: item.nm_pasien,
      umur: item.umur,
      jk: item.jk,
      kd_dokter: item.kd_dokter,
      nm_dokter: item.nm_dokter,
      tanggal: item.tanggal,
      jam_mulai: item.jam_mulai,
      jam_selesai: item.jam_selesai,
      kode_paket: item.kode_paket,
      nm_perawatan: item.nm_perawatan,
      kd_ruang_ok: item.kd_ruang_ok,
      nm_ruang_ok: item.nm_ruang_ok
    }
  })
}

onMounted(() => {
  loadData()
  loadCountByStatus()
})
</script>

<style scoped>
/* Nav Tabs Style */
.nav-tabs .nav-link {
  cursor: pointer;
  color: #6b7280;
  font-weight: 500;
}

.nav-tabs .nav-link.active {
  color: #2563eb;
  font-weight: 600;
}

.nav-tabs .nav-link:hover {
  color: #2563eb;
}

.page-header-with-filter {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-left {
  flex-shrink: 0;
  min-width: 250px;
}

.filter-compact {
  background: white;
  padding: 0.75rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
}

.page-subtitle {
  color: #718096;
  font-size: 1rem;
  margin: 0;
}

.table thead.sticky-top {
  position: sticky;
  top: 0;
  z-index: 10;
}

.table td {
  vertical-align: middle;
  font-size: 0.875rem;
}

.table th {
  font-weight: 600;
  font-size: 0.875rem;
  white-space: nowrap;
}

.btn-no-rawat {
  background: white;
  border: 1px solid #06b6d4;
  color: #06b6d4;
  font-size: 0.875rem;
  font-weight: 500;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-no-rawat:hover {
  background: #f0fdfa;
  color: #0891b2;
  border-color: #0891b2;
}

.btn-no-rawat::after {
  border-top-color: #06b6d4;
  transition: all 0.3s;
}

.btn-no-rawat:hover::after {
  border-top-color: #0891b2;
}

/* Responsive Styles */

/* Tablet - 768px to 1024px */
@media (max-width: 1024px) {
  .page-header-with-filter {
    padding: 1.25rem;
    gap: 1.5rem;
  }

  .page-title {
    font-size: 1.75rem;
  }

  .page-title svg {
    width: 28px;
    height: 28px;
  }

  .filter-compact .form-select-sm,
  .filter-compact .form-control-sm {
    font-size: 0.85rem;
  }

  .table td,
  .table th {
    font-size: 0.8rem;
    padding: 0.5rem;
  }

  /* Reduce width of No column on tablet */
  .table th:first-child,
  .table td:first-child {
    width: 35px !important;
    min-width: 35px;
    max-width: 35px;
    padding: 0.5rem 0.25rem;
    text-align: center;
  }
}

/* Mobile - max 768px */
@media (max-width: 768px) {
  .page-header-with-filter {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
    padding: 1rem;
  }

  .header-left {
    min-width: unset;
    width: 100%;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .page-title svg {
    width: 24px;
    height: 24px;
    margin-right: 6px;
  }

  .filter-compact {
    width: 100%;
  }

  .filter-compact .row {
    flex-wrap: wrap !important;
    gap: 0.5rem;
  }

  .filter-compact .col-auto {
    flex: 1 1 auto;
    min-width: 120px;
  }

  .filter-compact .form-select-sm,
  .filter-compact .form-control-sm {
    width: 100% !important;
    font-size: 0.875rem;
  }

  .filter-compact .btn-sm {
    width: 100%;
  }

  /* Table responsive adjustments */
  .table-responsive {
    font-size: 0.75rem;
  }

  .table td,
  .table th {
    font-size: 0.75rem;
    padding: 0.4rem 0.3rem;
  }

  .table th {
    white-space: normal;
    min-width: 80px;
  }

  /* Reduce width of No column on mobile */
  .table th:first-child,
  .table td:first-child {
    width: 30px !important;
    min-width: 30px;
    max-width: 30px;
    padding: 0.4rem 0.15rem;
    text-align: center;
  }

  /* Hide less important columns di mobile */
  .table th:nth-child(4), /* Umur */
  .table td:nth-child(4),
  .table th:nth-child(5), /* JK */
  .table td:nth-child(5),
  .table th:nth-child(8), /* Jam Selesai */
  .table td:nth-child(8),
  .table th:nth-child(10), /* Ruangan */
  .table td:nth-child(10),
  .table th:nth-child(14), /* Jenis */
  .table td:nth-child(14),
  .table th:nth-child(15), /* Ruang OK */
  .table td:nth-child(15) {
    display: none;
  }

  /* Dropdown button lebih kecil */
  .btn-no-rawat {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }

  /* Badge lebih compact */
  .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.4rem;
  }

  /* Card footer */
  .card-footer {
    font-size: 0.8rem;
    padding: 0.75rem;
  }
}

/* Small Mobile - max 480px */
@media (max-width: 480px) {
  .page-header-with-filter {
    padding: 0.75rem;
  }

  .page-title {
    font-size: 1.25rem;
  }

  .page-title svg {
    width: 20px;
    height: 20px;
  }

  .filter-compact .col-auto {
    min-width: 100%;
    flex: 1 1 100%;
  }

  .filter-compact .form-label {
    font-size: 0.8rem;
  }

  /* Hide more columns */
  .table th:nth-child(11), /* Diagnosa */
  .table td:nth-child(11),
  .table th:nth-child(12), /* Paket Operasi */
  .table td:nth-child(12) {
    display: none;
  }

  /* Essential columns only */
  .table td,
  .table th {
    padding: 0.3rem 0.2rem;
  }

  /* Stack patient info */
  .table td strong {
    display: block;
    font-size: 0.75rem;
  }

  .table td small {
    font-size: 0.65rem;
  }

  /* Card padding */
  .card-body {
    padding: 0.5rem !important;
  }

  .card-footer {
    padding: 0.5rem;
    font-size: 0.75rem;
  }
}

/* Landscape mode optimization for tablets/mobile */
@media (max-width: 1024px) and (orientation: landscape) {
  .page-header-with-filter {
    padding: 0.75rem;
  }

  .filter-compact .row {
    flex-wrap: nowrap !important;
  }

  .filter-compact .col-auto {
    min-width: auto;
  }

  /* Show more columns in landscape */
  .table th:nth-child(4),
  .table td:nth-child(4),
  .table th:nth-child(5),
  .table td:nth-child(5),
  .table th:nth-child(10),
  .table td:nth-child(10) {
    display: table-cell;
  }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {
  .btn-sm {
    min-height: 38px;
    padding: 0.5rem 0.75rem;
  }

  .btn-no-rawat {
    min-width: 44px;
    min-height: 38px;
  }

  .dropdown-menu {
    font-size: 1rem;
  }

  .dropdown-item {
    padding: 0.75rem 1rem;
  }
}
</style>
