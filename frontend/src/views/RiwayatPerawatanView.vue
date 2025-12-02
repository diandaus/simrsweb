<template>
  <AppLayout>
    <div class="container-fluid mt-4 px-4">
      <div class="mb-4">
        <button class="btn btn-outline-secondary btn-sm mb-2" @click="router.back()">
          ← Kembali
        </button>
        <template v-if="isTimelineMode">
          <h3>📋 Riwayat Perawatan Pasien</h3>
          <p class="text-muted">
            No. Rekam Medis:
            <strong>{{ route.query.no_rkm_medis }}</strong>
            <span v-if="timelinePatient && timelinePatient.nm_pasien"> • {{ timelinePatient.nm_pasien }}</span>
          </p>
        </template>
        <template v-else>
          <h3>📋 Riwayat Perawatan Detail</h3>
          <p class="text-muted">No. Rawat: <strong>{{ route.params.no_rawat }}</strong></p>
        </template>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Memuat data riwayat perawatan...</p>
      </div>

      <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

      <RiwayatTimelineSection
        v-else-if="isTimelineMode"
        :timeline="timelineData"
        :summary="timelineSummary"
        :patient="timelinePatient"
        :limit="0"
        @open-detail="openDetailFromTimeline"
      />

      <div v-else-if="riwayat">
        <!-- Asuhan Medis IGD -->
        <div v-if="riwayat.asuhan_medis_igd && riwayat.asuhan_medis_igd.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-danger text-white">
            <h6 class="mb-0">🚑 Pengkajian Awal Medis IGD</h6>
          </div>
          <div class="card-body">
            <div v-for="(item, index) in riwayat.asuhan_medis_igd" :key="index" class="mb-4">
              <!-- Yang Melakukan Pengkajian -->
              <div class="row mb-3">
                <div class="col-md-4"><strong>Tanggal:</strong> {{ item.tanggal }}</div>
                <div class="col-md-4"><strong>Dokter:</strong> {{ item.kd_dokter }} - {{ item.nm_dokter }}</div>
                <div class="col-md-4"><strong>Anamnesis:</strong> {{ item.anamnesis }}{{ item.hubungan ? ', ' + item.hubungan : '' }}</div>
              </div>

              <!-- I. Riwayat Kesehatan -->
              <div class="border rounded p-3 mb-3">
                <h6 class="text-primary mb-3">I. RIWAYAT KESEHATAN</h6>
                <div class="mb-2"><strong>Keluhan Utama:</strong><br>{{ item.keluhan_utama }}</div>
                <div class="mb-2"><strong>Riwayat Penyakit Sekarang:</strong><br>{{ item.rps }}</div>
                <div class="row">
                  <div class="col-md-6 mb-2"><strong>Riwayat Penyakit Dahulu:</strong><br>{{ item.rpd }}</div>
                  <div class="col-md-6 mb-2"><strong>Riwayat Alergi:</strong><br>{{ item.alergi }}</div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-2"><strong>Riwayat Penyakit Keluarga:</strong><br>{{ item.rpk }}</div>
                  <div class="col-md-6 mb-2"><strong>Riwayat Penggunaan Obat:</strong><br>{{ item.rpo }}</div>
                </div>
              </div>

              <!-- II. Pemeriksaan Fisik -->
              <div class="border rounded p-3 mb-3">
                <h6 class="text-primary mb-3">II. PEMERIKSAAN FISIK</h6>
                <div class="row mb-2">
                  <div class="col-md-3"><strong>Keadaan Umum:</strong> {{ item.keadaan }}</div>
                  <div class="col-md-3"><strong>Kesadaran:</strong> {{ item.kesadaran }}</div>
                  <div class="col-md-3"><strong>GCS(E,V,M):</strong> {{ item.gcs }}</div>
                  <div class="col-md-3"><strong>TB:</strong> {{ item.tb }} cm</div>
                </div>
                <div class="row mb-2">
                  <div class="col-md-3"><strong>BB:</strong> {{ item.bb }} kg</div>
                  <div class="col-md-3"><strong>TD:</strong> {{ item.td }} mmHg</div>
                  <div class="col-md-3"><strong>Nadi:</strong> {{ item.nadi }} x/menit</div>
                  <div class="col-md-3"><strong>RR:</strong> {{ item.rr }} x/menit</div>
                </div>
                <div class="row mb-2">
                  <div class="col-md-3"><strong>Suhu:</strong> {{ item.suhu }} °C</div>
                  <div class="col-md-3"><strong>SpO2:</strong> {{ item.spo }} %</div>
                  <div class="col-md-3"><strong>Kepala:</strong> {{ item.kepala }}</div>
                  <div class="col-md-3"><strong>Mata:</strong> {{ item.mata }}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-md-3"><strong>Gigi & Mulut:</strong> {{ item.gigi }}</div>
                  <div class="col-md-3"><strong>Leher:</strong> {{ item.leher }}</div>
                  <div class="col-md-3"><strong>Thoraks:</strong> {{ item.thoraks }}</div>
                  <div class="col-md-3"><strong>Abdomen:</strong> {{ item.abdomen }}</div>
                </div>
                <div class="row mb-2">
                  <div class="col-md-3"><strong>Genital & Anus:</strong> {{ item.genital }}</div>
                  <div class="col-md-3"><strong>Ekstremitas:</strong> {{ item.ekstremitas }}</div>
                  <div class="col-md-6"><strong>Keterangan Fisik:</strong> {{ item.ket_fisik }}</div>
                </div>
              </div>

              <!-- III. Status Lokalis -->
              <div class="border rounded p-3 mb-3">
                <h6 class="text-primary mb-3">III. STATUS LOKALIS</h6>
                <div class="text-center mb-3">
                  <img src="/images/semua.png" alt="Gambar Lokalis" class="img-fluid" style="max-width: 100%; height: auto;">
                </div>
                <div><strong>Keterangan:</strong><br>{{ item.ket_lokalis }}</div>
              </div>

              <!-- IV. Pemeriksaan Penunjang -->
              <div class="border rounded p-3 mb-3">
                <h6 class="text-primary mb-3">IV. PEMERIKSAAN PENUNJANG</h6>
                <div class="row">
                  <div class="col-md-4"><strong>EKG:</strong><br>{{ item.ekg }}</div>
                  <div class="col-md-4"><strong>Radiologi:</strong><br>{{ item.rad }}</div>
                  <div class="col-md-4"><strong>Laborat:</strong><br>{{ item.lab }}</div>
                </div>
              </div>

              <!-- V. Diagnosis/Asesmen -->
              <div class="border rounded p-3 mb-3">
                <h6 class="text-primary mb-3">V. DIAGNOSIS/ASESMEN</h6>
                <div>{{ item.diagnosis }}</div>
              </div>

              <!-- VI. Tatalaksana -->
              <div class="border rounded p-3 mb-3">
                <h6 class="text-primary mb-3">VI. TATALAKSANA</h6>
                <div>{{ item.tata }}</div>
              </div>

              <hr v-if="index < riwayat.asuhan_medis_igd.length - 1">
            </div>
          </div>
        </div>

        <!-- Tindakan Rawat Jalan Dokter -->
        <div v-if="riwayat.rawat_jalan_dokter && riwayat.rawat_jalan_dokter.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-primary text-white">
            <h6 class="mb-0">🏥 Tindakan Rawat Jalan Dokter</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Tindakan/Perawatan</th>
                    <th>Dokter</th>
                    <th class="text-end">Biaya</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in riwayat.rawat_jalan_dokter" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.tgl_perawatan }} {{ item.jam_rawat }}</td>
                    <td>{{ item.kd_jenis_prw }}</td>
                    <td>{{ item.nm_perawatan }}</td>
                    <td>{{ item.nm_dokter }}</td>
                    <td class="text-end">{{ formatRupiah(item.biaya_rawat) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tindakan Rawat Jalan Paramedis -->
        <div v-if="riwayat.rawat_jalan_paramedis && riwayat.rawat_jalan_paramedis.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-success text-white">
            <h6 class="mb-0">👨‍⚕️ Tindakan Rawat Jalan Paramedis</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Tindakan/Perawatan</th>
                    <th>Paramedis</th>
                    <th class="text-end">Biaya</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in riwayat.rawat_jalan_paramedis" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.tgl_perawatan }} {{ item.jam_rawat }}</td>
                    <td>{{ item.kd_jenis_prw }}</td>
                    <td>{{ item.nm_perawatan }}</td>
                    <td>{{ item.nama }}</td>
                    <td class="text-end">{{ formatRupiah(item.biaya_rawat) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tindakan Rawat Jalan Dokter & Paramedis -->
        <div v-if="riwayat.rawat_jalan_dokter_paramedis && riwayat.rawat_jalan_dokter_paramedis.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-info text-white">
            <h6 class="mb-0">👨‍⚕️👩‍⚕️ Tindakan Rawat Jalan Dokter & Paramedis</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Tindakan/Perawatan</th>
                    <th>Dokter</th>
                    <th>Paramedis</th>
                    <th class="text-end">Biaya</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in riwayat.rawat_jalan_dokter_paramedis" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.tgl_perawatan }} {{ item.jam_rawat }}</td>
                    <td>{{ item.kd_jenis_prw }}</td>
                    <td>{{ item.nm_perawatan }}</td>
                    <td>{{ item.nm_dokter }}</td>
                    <td>{{ item.nama }}</td>
                    <td class="text-end">{{ formatRupiah(item.biaya_rawat) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Penggunaan Kamar Inap -->
        <div v-if="riwayat.kamar_inap && riwayat.kamar_inap.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-warning text-dark">
            <h6 class="mb-0">🛏️ Penggunaan Kamar Inap</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No.</th>
                    <th>Tanggal Masuk</th>
                    <th>Tanggal Keluar</th>
                    <th>Lama Inap</th>
                    <th>Kamar</th>
                    <th>Status</th>
                    <th class="text-end">Biaya</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in riwayat.kamar_inap" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.tgl_masuk }} {{ item.jam_masuk }}</td>
                    <td>{{ item.tgl_keluar }} {{ item.jam_keluar }}</td>
                    <td>{{ item.lama }} hari</td>
                    <td>{{ item.kd_kamar }}, {{ item.nm_bangsal }}</td>
                    <td>{{ item.stts_pulang }}</td>
                    <td class="text-end">{{ formatRupiah(item.ttl_biaya) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Pemeriksaan Radiologi -->
        <div v-if="riwayat.radiologi && riwayat.radiologi.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-secondary text-white">
            <h6 class="mb-0">📷 Pemeriksaan Radiologi</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Pemeriksaan</th>
                    <th>Dokter PJ</th>
                    <th>Petugas</th>
                    <th class="text-end">Biaya</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in riwayat.radiologi" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.tgl_periksa }} {{ item.jam }}</td>
                    <td>{{ item.kd_jenis_prw }}</td>
                    <td>
                      {{ item.nm_perawatan }}
                      <div v-if="item.proyeksi || item.kV || item.mAS" class="small text-muted">
                        <span v-if="item.proyeksi">Proyeksi: {{ item.proyeksi }}</span>
                        <span v-if="item.kV">, kV: {{ item.kV }}</span>
                        <span v-if="item.mAS">, mAS: {{ item.mAS }}</span>
                      </div>
                    </td>
                    <td>{{ item.nm_dokter }}</td>
                    <td>{{ item.nama }}</td>
                    <td class="text-end">{{ formatRupiah(item.biaya) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Hasil Radiologi -->
        <div v-if="riwayat.hasil_radiologi && riwayat.hasil_radiologi.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-dark text-white">
            <h6 class="mb-0">📝 Hasil/Bacaan Radiologi</h6>
          </div>
          <div class="card-body">
            <div v-for="(item, index) in riwayat.hasil_radiologi" :key="index" class="mb-3">
              <div class="fw-bold">{{ item.tgl_periksa }} {{ item.jam }}</div>
              <div class="mt-1" v-html="item.hasil.replace(/\n/g, '<br>')"></div>
              <hr v-if="index < riwayat.hasil_radiologi.length - 1">
            </div>
          </div>
        </div>

        <!-- Pemberian Obat -->
        <div v-if="riwayat.pemberian_obat && riwayat.pemberian_obat.length > 0" class="card shadow-sm mb-3">
          <div class="card-header" style="background-color: #e91e63; color: white;">
            <h6 class="mb-0">💊 Pemberian Obat/BHP/Alkes</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Nama Obat/BHP/Alkes</th>
                    <th>Jumlah</th>
                    <th>Aturan Pakai</th>
                    <th class="text-end">Biaya</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in riwayat.pemberian_obat" :key="index">
                    <td>{{ index + 1 }}</td>
                    <td>{{ item.tgl_perawatan }} {{ item.jam }}</td>
                    <td>{{ item.kode_brng }}</td>
                    <td>{{ item.nama_brng }}</td>
                    <td>{{ item.jml }} {{ item.kode_sat }}</td>
                    <td>{{ item.aturan_pakai || '-' }}</td>
                    <td class="text-end">{{ formatRupiah(item.total) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tambahan Biaya -->
        <div v-if="riwayat.tambahan_biaya && riwayat.tambahan_biaya.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-success text-white">
            <h6 class="mb-0">➕ Tambahan Biaya</h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0">
              <tbody>
                <tr v-for="(item, index) in riwayat.tambahan_biaya" :key="index">
                  <td width="80%">{{ item.nama_biaya }}</td>
                  <td width="20%" class="text-end">{{ formatRupiah(item.besar_biaya) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Potongan Biaya -->
        <div v-if="riwayat.potongan_biaya && riwayat.potongan_biaya.length > 0" class="card shadow-sm mb-3">
          <div class="card-header bg-danger text-white">
            <h6 class="mb-0">➖ Potongan Biaya</h6>
          </div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0">
              <tbody>
                <tr v-for="(item, index) in riwayat.potongan_biaya" :key="index">
                  <td width="80%">{{ item.nama_pengurangan }}</td>
                  <td width="20%" class="text-end">{{ formatRupiah(item.besar_pengurangan) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Total Biaya -->
        <div class="card shadow-sm border-primary">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Total Biaya Perawatan</h5>
              <h4 class="mb-0 text-primary">Rp {{ formatRupiah(totalBiaya) }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { useRouter, useRoute } from 'vue-router'
import apiClient from '@/api/axios'
import RiwayatTimelineSection from '@/components/RiwayatTimelineSection.vue'

const router = useRouter()
const route = useRoute()
const riwayat = ref(null)
const totalBiaya = ref(0)
const loading = ref(false)
const error = ref('')
const timelineData = ref([])
const timelineSummary = ref({ total_kunjungan: 0, total_biaya: 0 })
const timelinePatient = ref(null)

const isTimelineMode = computed(() => Boolean(route.query.no_rkm_medis))

const formatRupiah = (amount) => {
  if (!amount) return '0'
  return new Intl.NumberFormat('id-ID').format(amount)
}

const loadRiwayat = async () => {
  loading.value = true
  error.value = ''

  try {
    riwayat.value = null
    timelineData.value = []
    totalBiaya.value = 0

    if (isTimelineMode.value) {
      const noRkm = route.query.no_rkm_medis
      const { data } = await apiClient.get(`/riwayat-perawatan/pasien/${noRkm}`)
      if (data.success) {
        const payload = data.data || {}
        timelineData.value = payload.kunjungan || []
        timelineSummary.value = payload.summary || { total_kunjungan: 0, total_biaya: 0 }
        timelinePatient.value = payload.patient || null
      } else {
        error.value = 'Gagal memuat riwayat perawatan pasien'
      }
    } else {
      const noRawat = route.params.no_rawat
      const { data } = await apiClient.get(`/riwayat-perawatan/${noRawat}`)

      if (data.success) {
        riwayat.value = data.data
        totalBiaya.value = data.total_biaya
      } else {
        error.value = 'Gagal memuat data riwayat perawatan'
      }
    }
  } catch (err) {
    console.error('Load riwayat error:', err)
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat memuat data'
  } finally {
    loading.value = false
  }
}

const openDetailFromTimeline = (noRawat) => {
  if (!noRawat) return
  router.push({ name: 'riwayat-perawatan', params: { no_rawat: noRawat } })
}

onMounted(() => {
  loadRiwayat()
})

watch(
  () => [route.params.no_rawat, route.query.no_rkm_medis],
  () => {
    loadRiwayat()
  }
)
</script>

<style scoped>
.table-sm td,
.table-sm th {
  padding: 0.5rem;
  font-size: 0.9rem;
}
</style>
