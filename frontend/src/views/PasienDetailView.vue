<template>
  <AppLayout>
  <div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <button class="btn btn-outline-secondary btn-sm mb-2" @click="router.back()">
          ← Kembali
        </button>
        <h3>👤 Detail Pasien</h3>
      </div>
      <div class="d-flex gap-2">
        <button
          class="btn btn-warning btn-sm"
          @click="router.push(`/pasien/${route.params.no_rkm_medis}/edit`)">
          ✏️ Edit Data
        </button>
        <button class="btn btn-danger btn-sm" @click="logout">Logout</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Memuat data pasien...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-else-if="pasien">
      <!-- Data Pasien -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">📋 Informasi Pasien</h5>
        </div>
        <div class="card-body">
          <!-- Identitas Utama -->
          <div class="row mb-4">
            <div class="col-md-12">
              <div class="bg-light p-3 rounded">
                <div class="row">
                  <div class="col-md-4">
                    <div class="mb-2">
                      <small class="text-muted d-block">No. Rekam Medis</small>
                      <h6 class="mb-0 text-primary fw-bold">{{ pasien.no_rkm_medis }}</h6>
                    </div>
                  </div>
                  <div class="col-md-8">
                    <div class="mb-2">
                      <small class="text-muted d-block">Nama Lengkap</small>
                      <h5 class="mb-0 fw-bold">{{ pasien.nm_pasien }}</h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Informasi Identitas -->
          <div class="row mb-3">
            <div class="col-md-12">
              <h6 class="text-primary border-bottom pb-2 mb-3">🆔 Data Identitas</h6>
            </div>
          </div>
          <div class="row mb-4">
            <div class="col-md-3">
              <small class="text-muted d-block">No. KTP</small>
              <strong>{{ pasien.no_ktp || '-' }}</strong>
            </div>
            <div class="col-md-3">
              <small class="text-muted d-block">No. BPJS</small>
              <strong>{{ pasien.no_peserta || '-' }}</strong>
            </div>
            <div class="col-md-3">
              <small class="text-muted d-block">Jenis Kelamin</small>
              <strong>{{ pasien.jk === 'L' ? '👨 Laki-laki' : '👩 Perempuan' }}</strong>
            </div>
            <div class="col-md-3">
              <small class="text-muted d-block">Gol. Darah</small>
              <strong><span class="badge bg-danger">{{ pasien.gol_darah || '-' }}</span></strong>
            </div>
          </div>

          <!-- Informasi Kelahiran -->
          <div class="row mb-3">
            <div class="col-md-12">
              <h6 class="text-primary border-bottom pb-2 mb-3">🎂 Data Kelahiran</h6>
            </div>
          </div>
          <div class="row mb-4">
            <div class="col-md-4">
              <small class="text-muted d-block">Tempat Lahir</small>
              <strong>{{ pasien.tmp_lahir }}</strong>
            </div>
            <div class="col-md-4">
              <small class="text-muted d-block">Tanggal Lahir</small>
              <strong>{{ formatDate(pasien.tgl_lahir) }}</strong>
            </div>
            <div class="col-md-4">
              <small class="text-muted d-block">Umur</small>
              <strong>{{ pasien.umur }}</strong>
            </div>
          </div>

          <!-- Informasi Demografi -->
          <div class="row mb-3">
            <div class="col-md-12">
              <h6 class="text-primary border-bottom pb-2 mb-3">👥 Data Demografi</h6>
            </div>
          </div>
          <div class="row mb-4">
            <div class="col-md-3">
              <small class="text-muted d-block">Agama</small>
              <strong>{{ pasien.agama || '-' }}</strong>
            </div>
            <div class="col-md-3">
              <small class="text-muted d-block">Status Nikah</small>
              <strong>{{ pasien.stts_nikah || '-' }}</strong>
            </div>
            <div class="col-md-3">
              <small class="text-muted d-block">Pendidikan</small>
              <strong>{{ pasien.pnd || '-' }}</strong>
            </div>
            <div class="col-md-3">
              <small class="text-muted d-block">Pekerjaan</small>
              <strong>{{ pasien.pekerjaan || '-' }}</strong>
            </div>
          </div>

          <!-- Informasi Kontak -->
          <div class="row mb-3">
            <div class="col-md-12">
              <h6 class="text-primary border-bottom pb-2 mb-3">📞 Data Kontak</h6>
            </div>
          </div>
          <div class="row">
            <div class="col-md-3">
              <small class="text-muted d-block">No. Telepon</small>
              <strong>{{ pasien.no_tlp || '-' }}</strong>
            </div>
            <div class="col-md-9">
              <small class="text-muted d-block">Alamat Lengkap</small>
              <strong>{{ pasien.alamat || '-' }}</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Kunjungan -->
      <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">📅 Riwayat Kunjungan (10 Terakhir)</h5>
        </div>
        <div class="card-body">
          <div v-if="!pasien.reg_periksa || pasien.reg_periksa.length === 0" class="text-center py-4">
            <p class="text-muted">Belum ada riwayat kunjungan</p>
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover table-striped">
              <thead class="table-light">
                <tr>
                  <th>No. Rawat</th>
                  <th>Tgl Registrasi</th>
                  <th>Jam</th>
                  <th>Kode Dokter</th>
                  <th>Kode Poli</th>
                  <th>Status</th>
                  <th>Status Lanjut</th>
                  <th>Biaya Reg</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="reg in pasien.reg_periksa" :key="reg.no_rawat">
                  <td><strong>{{ reg.no_rawat }}</strong></td>
                  <td>{{ formatDate(reg.tgl_registrasi) }}</td>
                  <td>{{ reg.jam_reg }}</td>
                  <td>{{ reg.kd_dokter }}</td>
                  <td>{{ reg.kd_poli }}</td>
                  <td>
                    <span class="badge" :class="getStatusBadge(reg.stts)">
                      {{ reg.stts }}
                    </span>
                  </td>
                  <td>
                    <span class="badge" :class="reg.status_lanjut === 'Ranap' ? 'bg-danger' : 'bg-info'">
                      {{ reg.status_lanjut }}
                    </span>
                  </td>
                  <td>Rp {{ formatRupiah(reg.biaya_reg) }}</td>
                  <td>
                    <button class="btn btn-sm btn-primary" @click="openRiwayatModal(reg.no_rawat)">
                      📋 Detail Riwayat
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Riwayat Perawatan -->
    <div class="modal fade" :class="{ show: showModalRiwayat, 'd-block': showModalRiwayat }" tabindex="-1" v-if="showModalRiwayat">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">📋 Riwayat Perawatan Detail - {{ selectedNoRawat }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeRiwayatModal"></button>
          </div>
          <div class="modal-body">
            <div v-if="loadingRiwayat" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Memuat data riwayat perawatan...</p>
            </div>

            <div v-else-if="errorRiwayat" class="alert alert-danger">{{ errorRiwayat }}</div>

            <div v-else-if="riwayat">
              <RiwayatPerawatanLegacyCard
                :legacy="riwayat"
                :total-biaya="totalBiaya"
              />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeRiwayatModal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade" :class="{ show: showModalRiwayat }" v-if="showModalRiwayat"></div>
  </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import RiwayatPerawatanLegacyCard from '@/components/RiwayatPerawatanLegacyCard.vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/axios'

const router = useRouter()
const route = useRoute()
const pasien = ref(null)
const loading = ref(false)
const error = ref('')

// Modal riwayat state
const showModalRiwayat = ref(false)
const selectedNoRawat = ref('')
const riwayat = ref(null)
const totalBiaya = ref(0)
const loadingRiwayat = ref(false)
const errorRiwayat = ref('')

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatRupiah = (amount) => {
  if (!amount) return '0'
  return new Intl.NumberFormat('id-ID').format(amount)
}

const getStatusBadge = (status) => {
  const badges = {
    'Belum': 'bg-warning',
    'Sudah': 'bg-success',
    'Batal': 'bg-danger',
    'Berkas Diterima': 'bg-info',
    'Dirujuk': 'bg-primary',
    'Meninggal': 'bg-dark',
    'Dirawat': 'bg-danger',
    'Pulang Paksa': 'bg-warning'
  }
  return badges[status] || 'bg-secondary'
}

const loadPasien = async () => {
  loading.value = true
  error.value = ''

  try {
    const noRkm = route.params.no_rkm_medis
    const { data } = await apiClient.get(`/pasien/${noRkm}`)

    if (data.success) {
      pasien.value = data.data
    } else {
      error.value = 'Gagal memuat data pasien'
    }
  } catch (err) {
    console.error('Load pasien error:', err)
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat memuat data'
  } finally {
    loading.value = false
  }
}

const openRiwayatModal = async (noRawat) => {
  selectedNoRawat.value = noRawat
  showModalRiwayat.value = true
  loadingRiwayat.value = true
  errorRiwayat.value = ''

  try {
    const { data } = await apiClient.get(`/riwayat-perawatan/${noRawat}`)

    if (data.success) {
      riwayat.value = data.data
      totalBiaya.value = data.total_biaya
    } else {
      errorRiwayat.value = 'Gagal memuat data riwayat perawatan'
    }
  } catch (err) {
    console.error('Load riwayat error:', err)
    errorRiwayat.value = err.response?.data?.message || 'Terjadi kesalahan saat memuat data'
  } finally {
    loadingRiwayat.value = false
  }
}

const closeRiwayatModal = () => {
  showModalRiwayat.value = false
  selectedNoRawat.value = ''
  riwayat.value = null
  totalBiaya.value = 0
  errorRiwayat.value = ''
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

onMounted(() => {
  loadPasien()
})
</script>

<style scoped>
.table td {
  padding: 0.5rem;
}

/* Modal styles */
.modal.show {
  display: block;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop.show {
  opacity: 0.5;
}
</style>
