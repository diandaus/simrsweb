<template>
  <AppLayout>
    <div class="pasien-container">
      <div class="page-header mb-4">
        <div>
          <h2 class="page-title">📋 Daftar Pasien</h2>
          <p class="page-subtitle">Kelola data pasien SIMRS Khanza</p>
        </div>
        <button class="btn btn-success" @click="showModal = true">
          ➕ Tambah Pasien Baru
        </button>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-8">
              <input
                v-model="search"
                @input="searchPasien"
                type="text"
                class="form-control"
                placeholder="Cari berdasarkan No. RM, Nama, No. KTP, atau No. BPJS..."
              />
            </div>
            <div class="col-md-4">
              <select v-model="perPage" @change="loadPasien" class="form-select">
                <option :value="10">10 per halaman</option>
                <option :value="25">25 per halaman</option>
                <option :value="50">50 per halaman</option>
                <option :value="100">100 per halaman</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data pasien...</p>
      </div>

      <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

      <div v-else class="card shadow-sm">
        <div class="table-responsive">
          <table class="table table-hover table-striped mb-0">
            <thead class="table-primary">
              <tr>
                <th>No. RM</th>
                <th>Nama Pasien</th>
                <th>No. KTP</th>
                <th>JK</th>
                <th>Tgl Lahir</th>
                <th>Umur</th>
                <th>Alamat</th>
                <th>No. BPJS</th>
                <th>No. Telp</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="pasien.length === 0">
                <td colspan="10" class="text-center py-4">Tidak ada data pasien</td>
              </tr>
              <tr v-for="p in pasien" :key="p.no_rkm_medis">
                <td><strong>{{ p.no_rkm_medis }}</strong></td>
                <td>{{ p.nm_pasien }}</td>
                <td>{{ p.no_ktp || '-' }}</td>
                <td>{{ p.jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                <td>{{ formatDate(p.tgl_lahir) }}</td>
                <td>{{ p.umur }}</td>
                <td>{{ p.alamat || '-' }}</td>
                <td>{{ p.no_peserta || '-' }}</td>
                <td>{{ p.no_tlp || '-' }}</td>
                <td>
                  <button
                    class="btn btn-primary btn-sm"
                    @click="router.push(`/pasien/${p.no_rkm_medis}`)">
                    Detail
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card-footer bg-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <small class="text-muted">
                Menampilkan {{ pasien.length }} dari {{ meta.total }} data
              </small>
            </div>
            <nav>
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                  <button class="page-link" @click="changePage(meta.current_page - 1)">
                    Previous
                  </button>
                </li>
                <li
                  class="page-item"
                  v-for="page in totalPages"
                  :key="page"
                  :class="{ active: page === meta.current_page }"
                >
                  <button class="page-link" @click="changePage(page)">{{ page }}</button>
                </li>
                <li
                  class="page-item"
                  :class="{ disabled: meta.current_page === meta.last_page }"
                >
                  <button class="page-link" @click="changePage(meta.current_page + 1)">
                    Next
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Pasien -->
    <div class="modal fade" :class="{ show: showModal, 'd-block': showModal }" tabindex="-1" v-if="showModal">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">➕ Tambah Pasien Baru</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitPasien">
              <div class="row g-3">
                <!-- No. Rekam Medis -->
                <div class="col-md-6">
                  <label class="form-label">No. Rekam Medis <span class="text-danger">*</span></label>
                  <input v-model="form.no_rkm_medis" type="text" class="form-control" required>
                </div>

                <!-- Nama Pasien -->
                <div class="col-md-6">
                  <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                  <input v-model="form.nm_pasien" type="text" class="form-control" required>
                </div>

                <!-- No. KTP -->
                <div class="col-md-6">
                  <label class="form-label">No. KTP</label>
                  <input v-model="form.no_ktp" type="text" class="form-control" maxlength="16">
                </div>

                <!-- No. BPJS -->
                <div class="col-md-6">
                  <label class="form-label">No. BPJS</label>
                  <input v-model="form.no_peserta" type="text" class="form-control">
                </div>

                <!-- Jenis Kelamin -->
                <div class="col-md-6">
                  <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                  <select v-model="form.jk" class="form-select" required>
                    <option value="">Pilih...</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                  </select>
                </div>

                <!-- Tempat Lahir -->
                <div class="col-md-6">
                  <label class="form-label">Tempat Lahir</label>
                  <input v-model="form.tmp_lahir" type="text" class="form-control">
                </div>

                <!-- Tanggal Lahir -->
                <div class="col-md-6">
                  <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                  <input v-model="form.tgl_lahir" type="date" class="form-control" required>
                </div>

                <!-- Golongan Darah -->
                <div class="col-md-6">
                  <label class="form-label">Golongan Darah</label>
                  <select v-model="form.gol_darah" class="form-select">
                    <option value="">Pilih...</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="AB">AB</option>
                    <option value="O">O</option>
                  </select>
                </div>

                <!-- Agama -->
                <div class="col-md-6">
                  <label class="form-label">Agama</label>
                  <select v-model="form.agama" class="form-select">
                    <option value="">Pilih...</option>
                    <option value="Islam">Islam</option>
                    <option value="Kristen">Kristen</option>
                    <option value="Katholik">Katholik</option>
                    <option value="Hindu">Hindu</option>
                    <option value="Budha">Budha</option>
                    <option value="Konghucu">Konghucu</option>
                  </select>
                </div>

                <!-- Status Nikah -->
                <div class="col-md-6">
                  <label class="form-label">Status Nikah</label>
                  <select v-model="form.stts_nikah" class="form-select">
                    <option value="">Pilih...</option>
                    <option value="BELUM MENIKAH">Belum Menikah</option>
                    <option value="MENIKAH">Menikah</option>
                    <option value="CERAI">Cerai</option>
                    <option value="JANDA">Janda</option>
                    <option value="DUDHA">Duda</option>
                  </select>
                </div>

                <!-- Pendidikan -->
                <div class="col-md-6">
                  <label class="form-label">Pendidikan</label>
                  <select v-model="form.pnd" class="form-select">
                    <option value="">Pilih...</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                    <option value="D1">D1</option>
                    <option value="D2">D2</option>
                    <option value="D3">D3</option>
                    <option value="D4">D4</option>
                    <option value="S1">S1</option>
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                  </select>
                </div>

                <!-- Pekerjaan -->
                <div class="col-md-6">
                  <label class="form-label">Pekerjaan</label>
                  <input v-model="form.pekerjaan" type="text" class="form-control">
                </div>

                <!-- Alamat -->
                <div class="col-12">
                  <label class="form-label">Alamat</label>
                  <textarea v-model="form.alamat" class="form-control" rows="2"></textarea>
                </div>

                <!-- No. Telepon -->
                <div class="col-md-6">
                  <label class="form-label">No. Telepon</label>
                  <input v-model="form.no_tlp" type="text" class="form-control">
                </div>
              </div>

              <div v-if="formError" class="alert alert-danger mt-3">{{ formError }}</div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button type="button" class="btn btn-primary" @click="submitPasien" :disabled="formLoading">
              <span v-if="formLoading" class="spinner-border spinner-border-sm me-2"></span>
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade" :class="{ show: showModal }" v-if="showModal"></div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/axios'
import AppLayout from '@/components/AppLayout.vue'

const router = useRouter()
const pasien = ref([])
const loading = ref(false)
const error = ref('')
const search = ref('')
const perPage = ref(50)
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 50,
  total: 0
})

// Modal state
const showModal = ref(false)
const formLoading = ref(false)
const formError = ref('')
const form = ref({
  no_rkm_medis: '',
  nm_pasien: '',
  no_ktp: '',
  no_peserta: '',
  jk: '',
  tmp_lahir: '',
  tgl_lahir: '',
  gol_darah: '',
  agama: '',
  stts_nikah: '',
  pnd: '',
  pekerjaan: '',
  alamat: '',
  no_tlp: ''
})

const totalPages = computed(() => {
  const pages = []
  const maxPages = Math.min(meta.value.last_page, 10)
  for (let i = 1; i <= maxPages; i++) {
    pages.push(i)
  }
  return pages
})

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  })
}

const loadPasien = async (page = 1) => {
  loading.value = true
  error.value = ''

  try {
    const params = {
      per_page: perPage.value,
      page: page
    }

    if (search.value) {
      params.search = search.value
    }

    const { data } = await apiClient.get('/pasien', { params })

    if (data.success) {
      pasien.value = data.data
      meta.value = data.meta
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

let searchTimeout = null
const searchPasien = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadPasien(1)
  }, 500)
}

const changePage = (page) => {
  if (page >= 1 && page <= meta.value.last_page) {
    loadPasien(page)
  }
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
  // Reset form
  form.value = {
    no_rkm_medis: '',
    nm_pasien: '',
    no_ktp: '',
    no_peserta: '',
    jk: '',
    tmp_lahir: '',
    tgl_lahir: '',
    gol_darah: '',
    agama: '',
    stts_nikah: '',
    pnd: '',
    pekerjaan: '',
    alamat: '',
    no_tlp: ''
  }
}

const submitPasien = async () => {
  formLoading.value = true
  formError.value = ''

  try {
    const { data } = await apiClient.post('/pasien', form.value)

    if (data.success) {
      closeModal()
      loadPasien() // Reload list
      alert('Pasien berhasil ditambahkan!')
    } else {
      formError.value = 'Gagal menambahkan pasien'
    }
  } catch (err) {
    console.error('Submit pasien error:', err)
    formError.value = err.response?.data?.message || 'Terjadi kesalahan saat menambahkan pasien'
  } finally {
    formLoading.value = false
  }
}

onMounted(() => {
  loadPasien()
})
</script>

<style scoped>
.pasien-container {
  max-width: 1400px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

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

/* Modal styles */
.modal.show {
  display: block;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop.show {
  opacity: 0.5;
}
</style>