<template>
  <div class="container-fluid mt-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <button class="btn btn-outline-secondary btn-sm mb-2" @click="router.back()">
          ← Kembali
        </button>
        <h3>{{ isEdit ? '✏️ Edit Pasien' : '➕ Registrasi Pasien Baru' }}</h3>
      </div>
      <button class="btn btn-danger btn-sm" @click="logout">Logout</button>
    </div>

    <div v-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-if="success" class="alert alert-success">{{ success }}</div>

    <form @submit.prevent="submitForm">
      <!-- Data Identitas -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">📋 Data Identitas</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">No. Rekam Medis *</label>
              <input
                v-model="form.no_rkm_medis"
                type="text"
                class="form-control"
                :disabled="isEdit"
                required
              />
              <small class="text-muted">Format: 6 digit angka</small>
            </div>
            <div class="col-md-8">
              <label class="form-label">Nama Lengkap *</label>
              <input
                v-model="form.nm_pasien"
                type="text"
                class="form-control"
                required
              />
            </div>
            <div class="col-md-4">
              <label class="form-label">No. KTP</label>
              <input
                v-model="form.no_ktp"
                type="text"
                class="form-control"
                maxlength="20"
              />
            </div>
            <div class="col-md-4">
              <label class="form-label">No. BPJS</label>
              <input
                v-model="form.no_peserta"
                type="text"
                class="form-control"
                maxlength="25"
              />
            </div>
            <div class="col-md-4">
              <label class="form-label">Jenis Kelamin *</label>
              <select v-model="form.jk" class="form-select" required>
                <option value="">Pilih...</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tempat Lahir</label>
              <input
                v-model="form.tmp_lahir"
                type="text"
                class="form-control"
                maxlength="15"
              />
            </div>
            <div class="col-md-4">
              <label class="form-label">Tanggal Lahir *</label>
              <input
                v-model="form.tgl_lahir"
                type="date"
                class="form-control"
                required
              />
            </div>
            <div class="col-md-4">
              <label class="form-label">Gol. Darah</label>
              <select v-model="form.gol_darah" class="form-select">
                <option value="-">-</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="O">O</option>
                <option value="AB">AB</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Data Kontak -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">📞 Data Kontak & Alamat</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-12">
              <label class="form-label">Alamat</label>
              <textarea
                v-model="form.alamat"
                class="form-control"
                rows="2"
                maxlength="200"
              ></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">No. Telepon</label>
              <input
                v-model="form.no_tlp"
                type="text"
                class="form-control"
                maxlength="40"
              />
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="form-control"
                maxlength="50"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Data Lainnya -->
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0">ℹ️ Data Lainnya</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Agama</label>
              <select v-model="form.agama" class="form-select">
                <option value="">Pilih...</option>
                <option value="ISLAM">Islam</option>
                <option value="KRISTEN">Kristen</option>
                <option value="KATOLIK">Katolik</option>
                <option value="HINDU">Hindu</option>
                <option value="BUDDHA">Buddha</option>
                <option value="KHONGHUCU">Khonghucu</option>
                <option value="LAINNYA">Lainnya</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Status Nikah</label>
              <select v-model="form.stts_nikah" class="form-select">
                <option value="">Pilih...</option>
                <option value="BELUM MENIKAH">Belum Menikah</option>
                <option value="MENIKAH">Menikah</option>
                <option value="JANDA">Janda</option>
                <option value="DUDHA">Dudha</option>
                <option value="JOMBLO">Jomblo</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Pendidikan</label>
              <select v-model="form.pnd" class="form-select">
                <option value="-">-</option>
                <option value="TS">TS</option>
                <option value="TK">TK</option>
                <option value="SD">SD</option>
                <option value="SMP">SMP</option>
                <option value="SMA">SMA</option>
                <option value="SLTA/SEDERAJAT">SLTA/SEDERAJAT</option>
                <option value="D1">D1</option>
                <option value="D2">D2</option>
                <option value="D3">D3</option>
                <option value="D4">D4</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Pekerjaan</label>
              <input
                v-model="form.pekerjaan"
                type="text"
                class="form-control"
                maxlength="60"
              />
            </div>
            <div class="col-md-6">
              <label class="form-label">Nama Ibu Kandung</label>
              <input
                v-model="form.nm_ibu"
                type="text"
                class="form-control"
                maxlength="40"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Buttons -->
      <div class="d-flex justify-content-end gap-2 mb-4">
        <button type="button" class="btn btn-secondary" @click="router.back()">
          Batal
        </button>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          <span v-if="loading">
            <span class="spinner-border spinner-border-sm me-2"></span>
            Menyimpan...
          </span>
          <span v-else>
            💾 Simpan Data
          </span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/axios'

const router = useRouter()
const route = useRoute()
const loading = ref(false)
const error = ref('')
const success = ref('')
const isEdit = ref(false)

const form = ref({
  no_rkm_medis: '',
  nm_pasien: '',
  no_ktp: '',
  no_peserta: '',
  jk: '',
  tmp_lahir: '',
  tgl_lahir: '',
  gol_darah: '-',
  alamat: '',
  no_tlp: '',
  email: '',
  agama: '',
  stts_nikah: '',
  pnd: '-',
  pekerjaan: '',
  nm_ibu: ''
})

const loadPasien = async () => {
  try {
    const noRkm = route.params.no_rkm_medis
    const { data } = await apiClient.get(`/pasien/${noRkm}`)

    if (data.success) {
      const p = data.data
      form.value = {
        no_rkm_medis: p.no_rkm_medis,
        nm_pasien: p.nm_pasien,
        no_ktp: p.no_ktp || '',
        no_peserta: p.no_peserta || '',
        jk: p.jk,
        tmp_lahir: p.tmp_lahir || '',
        tgl_lahir: p.tgl_lahir ? p.tgl_lahir.split('T')[0] : '',
        gol_darah: p.gol_darah || '-',
        alamat: p.alamat || '',
        no_tlp: p.no_tlp || '',
        email: p.email || '',
        agama: p.agama || '',
        stts_nikah: p.stts_nikah || '',
        pnd: p.pnd || '-',
        pekerjaan: p.pekerjaan || '',
        nm_ibu: p.nm_ibu || ''
      }
    }
  } catch (err) {
    console.error('Load pasien error:', err)
    error.value = 'Gagal memuat data pasien'
  }
}

const submitForm = async () => {
  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const payload = { ...form.value }

    if (isEdit.value) {
      await apiClient.put(`/pasien/${payload.no_rkm_medis}`, payload)
      success.value = 'Data pasien berhasil diupdate!'
    } else {
      await apiClient.post('/pasien', payload)
      success.value = 'Pasien baru berhasil ditambahkan!'
    }

    setTimeout(() => {
      router.push('/')
    }, 1500)
  } catch (err) {
    console.error('Submit form error:', err)
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data'
  } finally {
    loading.value = false
  }
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

onMounted(() => {
  if (route.params.no_rkm_medis) {
    isEdit.value = true
    loadPasien()
  }
})
</script>

<style scoped>
.form-label {
  font-weight: 600;
  margin-bottom: 0.5rem;
}
</style>
