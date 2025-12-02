<template>
  <AppLayout>
    <div class="dashboard-container">
      <div class="page-header mb-4">
        <h2 class="page-title">📊 Dashboard</h2>
        <p class="page-subtitle">Ringkasan dan Statistik SIMRS Khanza</p>
      </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Memuat data dashboard...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

    <div v-else>
      <!-- Statistik Cards -->
      <div class="row g-4 mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm border-0 bg-primary text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">Total Pasien</h6>
                  <h2 class="mb-0">{{ formatNumber(stats.total_pasien) }}</h2>
                </div>
                <div class="fs-1">👥</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm border-0 bg-success text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">Pasien Baru (Bulan Ini)</h6>
                  <h2 class="mb-0">{{ formatNumber(stats.pasien_bulan_ini) }}</h2>
                </div>
                <div class="fs-1">✨</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm border-0 bg-warning text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">Kunjungan Hari Ini</h6>
                  <h2 class="mb-0">{{ formatNumber(stats.kunjungan_hari_ini) }}</h2>
                </div>
                <div class="fs-1">📅</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm border-0 bg-info text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-1">Kunjungan (Bulan Ini)</h6>
                  <h2 class="mb-0">{{ formatNumber(stats.kunjungan_bulan_ini) }}</h2>
                </div>
                <div class="fs-1">📊</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="row g-4 mb-4">
        <!-- Kunjungan 7 Hari Terakhir -->
        <div class="col-md-8">
          <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
              <h5 class="mb-0">📈 Kunjungan 7 Hari Terakhir</h5>
            </div>
            <div class="card-body">
              <canvas ref="chartKunjungan7Hari"></canvas>
            </div>
          </div>
        </div>

        <!-- Status Kunjungan -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
              <h5 class="mb-0">📋 Status Kunjungan</h5>
            </div>
            <div class="card-body">
              <canvas ref="chartStatusKunjungan"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="row g-4 mb-4">
        <!-- Top 5 Poli -->
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
              <h5 class="mb-0">🏥 Top 5 Poli (Bulan Ini)</h5>
            </div>
            <div class="card-body">
              <canvas ref="chartTopPoli"></canvas>
            </div>
          </div>
        </div>

        <!-- Ralan vs Ranap -->
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-header bg-warning text-white">
              <h5 class="mb-0">🏨 Rawat Jalan vs Rawat Inap</h5>
            </div>
            <div class="card-body">
              <canvas ref="chartStatusLanjut"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0">⚡ Quick Actions</h5>
        </div>
        <div class="card-body">
          <div class="d-flex gap-3">
            <button class="btn btn-primary" @click="router.push('/pasien')">
              👥 Daftar Pasien
            </button>
            <button class="btn btn-success" @click="openModalTambahPasien">
              ➕ Tambah Pasien
            </button>
            <button class="btn btn-info" @click="loadDashboard">
              🔄 Refresh Data
            </button>
          </div>
        </div>
      </div>
    </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed, inject } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/axios'
import { Chart, registerables } from 'chart.js'
import AppLayout from '@/components/AppLayout.vue'

Chart.register(...registerables)

const router = useRouter()

// Fungsi untuk membuka modal tambah pasien dari parent (AppLayout)
const openModalTambahPasien = () => {
  // Emit event ke parent untuk membuka modal
  window.dispatchEvent(new CustomEvent('open-modal-tambah-pasien'))
}
const loading = ref(false)
const error = ref('')
const stats = ref({
  total_pasien: 0,
  pasien_bulan_ini: 0,
  kunjungan_hari_ini: 0,
  kunjungan_bulan_ini: 0
})
const dashboardData = ref(null)

const chartKunjungan7Hari = ref(null)
const chartStatusKunjungan = ref(null)
const chartTopPoli = ref(null)
const chartStatusLanjut = ref(null)

const formatNumber = (num) => {
  return new Intl.NumberFormat('id-ID').format(num)
}

const loadDashboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const { data } = await apiClient.get('/dashboard')

    if (data.success) {
      stats.value = data.data.statistik
      dashboardData.value = data.data

      // Render charts
      setTimeout(() => {
        renderCharts()
      }, 100)
    } else {
      error.value = 'Gagal memuat data dashboard'
    }
  } catch (err) {
    console.error('Load dashboard error:', err)
    error.value = err.response?.data?.message || 'Terjadi kesalahan saat memuat data'
  } finally {
    loading.value = false
  }
}

const renderCharts = () => {
  if (!dashboardData.value) return

  // Chart Kunjungan 7 Hari
  if (chartKunjungan7Hari.value) {
    const data = dashboardData.value.kunjungan_7_hari
    new Chart(chartKunjungan7Hari.value, {
      type: 'line',
      data: {
        labels: data.map(d => formatDate(d.tanggal)),
        datasets: [{
          label: 'Kunjungan',
          data: data.map(d => d.total),
          borderColor: 'rgb(54, 162, 235)',
          backgroundColor: 'rgba(54, 162, 235, 0.1)',
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    })
  }

  // Chart Status Kunjungan
  if (chartStatusKunjungan.value) {
    const data = dashboardData.value.kunjungan_per_status
    new Chart(chartStatusKunjungan.value, {
      type: 'doughnut',
      data: {
        labels: data.map(d => d.stts),
        datasets: [{
          data: data.map(d => d.total),
          backgroundColor: [
            'rgb(255, 205, 86)',
            'rgb(75, 192, 192)',
            'rgb(255, 99, 132)',
            'rgb(54, 162, 235)',
            'rgb(153, 102, 255)',
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    })
  }

  // Chart Top Poli
  if (chartTopPoli.value) {
    const data = dashboardData.value.top_poli
    new Chart(chartTopPoli.value, {
      type: 'bar',
      data: {
        labels: data.map(d => d.kd_poli),
        datasets: [{
          label: 'Jumlah Kunjungan',
          data: data.map(d => d.total),
          backgroundColor: 'rgba(54, 162, 235, 0.8)',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    })
  }

  // Chart Status Lanjut
  if (chartStatusLanjut.value) {
    const data = dashboardData.value.kunjungan_per_status_lanjut
    new Chart(chartStatusLanjut.value, {
      type: 'pie',
      data: {
        labels: data.map(d => d.status_lanjut),
        datasets: [{
          data: data.map(d => d.total),
          backgroundColor: [
            'rgb(75, 192, 192)',
            'rgb(255, 99, 132)',
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    })
  }
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short'
  })
}

onMounted(() => {
  loadDashboard()
})
</script>

<style scoped>
.dashboard-container {
  max-width: 1400px;
}

.page-header {
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

canvas {
  max-height: 300px;
}
</style>
