<template>
  <div class="operasi-layout">
    <!-- Mobile Sidebar Overlay -->
    <div v-if="showSidebar" class="sidebar-overlay" @click="closeSidebar"></div>

    <!-- Patient Info Sidebar -->
    <aside class="patient-sidebar" :class="{ 'sidebar-open': showSidebar }">
      <div class="sidebar-header">
        <div>
          <h5 class="mb-1"><img src="/images/surgery-room.png" style="width: 25px; height: 25px; object-fit: contain;"> Input Data Operasi</h5>
        </div>
      </div>

      <div class="sidebar-section-title">👤 Informasi Pasien</div>

      <div class="sidebar-content">
        <div class="patient-info-item">
          <label>No. RM</label>
          <div class="info-value text-primary fw-bold fs-4">{{ patient.no_rkm_medis }}</div>
        </div>

        <div class="patient-info-item">
          <label>Nama Pasien</label>
          <div class="info-value fw-bold fs-5">{{ patient.nm_pasien }}</div>
        </div>

        <div class="patient-info-item">
          <label>Umur</label>
          <div class="info-value">{{ patient.umur }}</div>
        </div>

        <div class="patient-info-item">
          <label>Jenis Kelamin</label>
          <div class="info-value">{{ patient.jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
        </div>

        <div class="patient-info-item">
          <label>Dokter Operasi</label>
          <div class="info-value">{{ patient.nm_dokter }}</div>
        </div>

        <div class="divider"></div>

        <div class="patient-info-item">
          <label>No. Rawat</label>
          <div class="info-value small text-muted">{{ patient.no_rawat }}</div>
        </div>

        <div class="patient-info-item">
          <label>Tanggal Operasi</label>
          <div class="info-value small">{{ formatDate(patient.tanggal) }}</div>
        </div>

        <div class="patient-info-item">
          <label>Jadwal</label>
          <div class="info-value small">
            {{ patient.jam_mulai }} - {{ patient.jam_selesai }}
          </div>
        </div>

        <div class="patient-info-item">
          <label>Paket Operasi</label>
          <div class="info-value small">
            <strong>{{ patient.kode_paket }}</strong><br>
            {{ patient.nm_perawatan }}
          </div>
        </div>

        <div class="patient-info-item">
          <label>Ruang OK</label>
          <div class="info-value">
            <span class="badge bg-info">{{ patient.nm_ruang_ok }}</span>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="operasi-main">
      <!-- Tab Navigation -->
      <div class="operasi-tabs-wrapper">
        <button class="hamburger-btn" @click="toggleSidebar" title="Info Pasien">
          <span class="hamburger-icon">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </button>
        <div class="operasi-tabs">
          <button
            class="operasi-tab"
            :class="{ active: activeTab === 'tim' }"
            @click="activeTab = 'tim'"
          >
            👥 Jadwal Operasi
          </button>
          <button
            class="operasi-tab"
            :class="{ active: activeTab === 'laporan' }"
            @click="activeTab = 'laporan'"
          >
            📄 Laporan Operasi
          </button>
          <button
            class="operasi-tab"
            :class="{ active: activeTab === 'anastesi' }"
            @click="activeTab = 'anastesi'"
          >
            💉 Laporan Anastesi
          </button>
          <button
            class="operasi-tab"
            :class="{ active: activeTab === 'obat' }"
            @click="activeTab = 'obat'"
          >
            💊 Obat & Alat
          </button>
        </div>
        <button @click="goBack" class="btn-back-main" title="Kembali">
          <img src="/images/return.png" alt="Kembali" class="btn-back-icon">
          Kembali
        </button>
      </div>

      <div class="operasi-form-container">
        <!-- Tab Content: Tim Operasi -->
        <div v-if="activeTab === 'tim'" class="tab-content-operasi">
          <div class="row g-3">
            <!-- Tanggal Operasi -->
            <div class="col-md-4">
              <label class="form-label">Tanggal Operasi <span class="text-danger">*</span></label>
              <input
                v-model="timOperasi.tanggal"
                type="date"
                class="form-control"
                :disabled="loading"
              >
            </div>

            <!-- Jam Mulai -->
            <div class="col-md-4">
              <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
              <input
                v-model="timOperasi.jam_mulai"
                type="time"
                step="1"
                class="form-control"
                :disabled="loading"
              >
            </div>

            <!-- Jam Selesai -->
            <div class="col-md-4">
              <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
              <input
                v-model="timOperasi.jam_selesai"
                type="time"
                step="1"
                class="form-control"
                :disabled="loading"
              >
            </div>

            <!-- Status -->
            <div class="col-md-6">
              <label class="form-label">Status Operasi <span class="text-danger">*</span></label>
              <select v-model="timOperasi.status" class="form-select" :disabled="loading">
                <option value="">-- Pilih Status --</option>
                <option value="Proses Operasi">Proses Operasi</option>
                <option value="Selesai">Selesai</option>
              </select>
            </div>

            <!-- Buttons -->
            <div class="col-12">
              <div class="d-flex gap-2 mt-3">
                <button
                  @click="saveTimOperasi"
                  class="btn btn-primary"
                  :disabled="loading || !isTimOperasiValid"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-save me-2"></i>
                  {{ loading ? 'Menyimpan...' : 'Simpan' }}
                </button>
                <button
                  @click="resetTimOperasi"
                  class="btn btn-secondary"
                  :disabled="loading"
                >
                  <i class="bi bi-x-circle me-2"></i>
                  Reset
                </button>
                <button
                  @click="openDetailRiwayat"
                  class="btn btn-info"
                  :disabled="loading"
                >
                  <i class="bi bi-file-text me-2"></i>
                  Detail Riwayat
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab Content: Laporan Operasi -->
        <div v-if="activeTab === 'laporan'" class="tab-content-operasi">
          <h5 class="section-title mb-3 text-teal">📄 Input Laporan Operasi</h5>
          <div class="row g-3">
            <!-- Dokter Bedah -->
            <div class="col-md-4">
              <label class="form-label">Dokter Bedah</label>
              <select v-model="laporanOperasi.kd_dokter_bedah" class="form-select" :disabled="loadingLaporan">
                <option value="-">-- Pilih Dokter Bedah --</option>
                <option v-for="dokter in listDokter" :key="dokter.kd_dokter" :value="dokter.kd_dokter">
                  {{ dokter.nm_dokter }}
                </option>
              </select>
            </div>

            <!-- Dokter Anestesi -->
            <div class="col-md-4">
              <label class="form-label">Dokter Anestesi</label>
              <select v-model="laporanOperasi.kd_dokter_anestesi" class="form-select" :disabled="loadingLaporan">
                <option value="-">-- Pilih Dokter Anestesi --</option>
                <option v-for="dokter in listDokter" :key="dokter.kd_dokter" :value="dokter.kd_dokter">
                  {{ dokter.nm_dokter }}
                </option>
              </select>
            </div>

            <!-- Asisten Bedah (Petugas) -->
            <div class="col-md-4">
              <label class="form-label">Asisten Bedah</label>
              <select v-model="laporanOperasi.nip_perawat_ok" class="form-select" :disabled="loadingLaporan">
                <option value="">-- Pilih Asisten --</option>
                <option v-for="petugas in listPetugas" :key="petugas.nip" :value="petugas.nip">
                  {{ petugas.nama }}
                </option>
              </select>
            </div>

            <!-- Tanggal Operasi -->
            <div class="col-md-6">
              <label class="form-label">Tanggal & Jam Operasi <span class="text-danger">*</span></label>
              <input
                v-model="laporanOperasi.tanggal"
                type="datetime-local"
                class="form-control"
                :disabled="loadingLaporan"
              >
            </div>

            <!-- Selesai Operasi -->
            <div class="col-md-6">
              <label class="form-label">Selesai Operasi</label>
              <input
                v-model="laporanOperasi.selesaioperasi"
                type="datetime-local"
                class="form-control"
                :disabled="loadingLaporan"
              >
            </div>

            <!-- Jenis -->
            <div class="col-md-6">
              <label class="form-label">Jenis <span class="text-danger">*</span></label>
              <input
                v-model="laporanOperasi.jenis"
                type="text"
                class="form-control"
                placeholder="Masukkan jenis operasi"
                :disabled="loadingLaporan"
              >
            </div>

            <!-- Kategori -->
            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select v-model="laporanOperasi.kategori" class="form-select" :disabled="loadingLaporan">
                <option value="-">-</option>
                <option value="Khusus">Khusus</option>
                <option value="Besar">Besar</option>
                <option value="Sedang">Sedang</option>
                <option value="Kecil">Kecil</option>
                <option value="Cito">Cito</option>
                <option value="Non Cito">Non Cito</option>
              </select>
            </div>

            <!-- Tindakan -->
            <div class="col-md-12">
              <label class="form-label">Tindakan <span class="text-danger">*</span></label>
              <input
                v-model="laporanOperasi.tindakan"
                type="text"
                class="form-control"
                placeholder="Masukkan tindakan operasi"
                :disabled="loadingLaporan"
              >
            </div>

            <!-- Diagnosis Pre Operasi -->
            <div class="col-md-6">
              <label class="form-label">Diagnosis Pre Operasi</label>
              <textarea
                v-model="laporanOperasi.diagnosis_pre"
                class="form-control"
                rows="3"
                placeholder="Diagnosis sebelum operasi"
                :disabled="loadingLaporan"
              ></textarea>
            </div>

            <!-- Diagnosis Post Operasi -->
            <div class="col-md-6">
              <label class="form-label">Diagnosis Post Operasi</label>
              <textarea
                v-model="laporanOperasi.diagnosis_post"
                class="form-control"
                rows="3"
                placeholder="Diagnosis setelah operasi"
                :disabled="loadingLaporan"
              ></textarea>
            </div>

            <!-- Jaringan -->
            <div class="col-md-12">
              <label class="form-label">Jaringan yang Diambil</label>
              <textarea
                v-model="laporanOperasi.jaringan"
                class="form-control"
                rows="2"
                placeholder="Jaringan yang diambil saat operasi"
                :disabled="loadingLaporan"
              ></textarea>
            </div>

            <!-- Pemeriksaan PA -->
            <div class="col-md-4">
              <label class="form-label">Pemeriksaan PA</label>
              <select v-model="laporanOperasi.pemeriksaan_pa" class="form-select" :disabled="loadingLaporan">
                <option value="Tidak">Tidak</option>
                <option value="Ya">Ya</option>
              </select>
            </div>

            <!-- Keterangan PA -->
            <div class="col-md-2">
              <label class="form-label">Ket. PA</label>
              <input
                v-model="laporanOperasi.ket_pa"
                type="text"
                class="form-control"
                :disabled="loadingLaporan"
              >
            </div>

            <!-- Output Cairan -->
            <div class="col-md-4">
              <label class="form-label">Output Cairan</label>
              <select v-model="laporanOperasi.output_cairan" class="form-select" :disabled="loadingLaporan">
                <option value="Darah">Darah</option>
                <option value="Cairan">Cairan</option>
              </select>
            </div>

            <!-- Keterangan Output -->
            <div class="col-md-2">
              <label class="form-label">Ket. Output</label>
              <input
                v-model="laporanOperasi.ket_output"
                type="text"
                class="form-control"
                :disabled="loadingLaporan"
              >
            </div>

            <!-- Laporan Operasi -->
            <div class="col-md-12">
              <label class="form-label">Laporan Operasi</label>
              <textarea
                v-model="laporanOperasi.laporan_operasi"
                class="form-control"
                rows="5"
                placeholder="Laporan lengkap operasi..."
                :disabled="loadingLaporan"
              ></textarea>
            </div>

            <!-- Buttons -->
            <div class="col-12">
              <div class="d-flex gap-2 mt-3">
                <button
                  @click="saveLaporanOperasi"
                  class="btn btn-primary"
                  :disabled="loadingLaporan || !isLaporanValid"
                >
                  <span v-if="loadingLaporan" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-save me-2"></i>
                  {{ loadingLaporan ? 'Menyimpan...' : 'Simpan Laporan' }}
                </button>
                <button
                  @click="resetLaporanOperasi"
                  class="btn btn-secondary"
                  :disabled="loadingLaporan"
                >
                  <i class="bi bi-x-circle me-2"></i>
                  Reset
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Riwayat Laporan Operasi -->
        <div class="card mt-3" v-if="activeTab === 'laporan'">
          <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 text-teal">📋 Riwayat Laporan Operasi</h5>
            <div v-if="riwayatLaporan.length > 0" class="btn-group btn-group-sm">
              <button
                @click="editLaporanOperasi(riwayatLaporan[0])"
                class="btn btn-outline-warning btn-sm"
                title="Edit"
              >
                <i class="bi bi-pencil"></i> Edit
              </button>
              <button
                @click="deleteLaporanOperasi(riwayatLaporan[0])"
                class="btn btn-outline-danger btn-sm"
                title="Hapus"
              >
                <i class="bi bi-trash"></i> Hapus
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div v-if="loadingRiwayatLaporan" class="text-center p-4">
              <div class="spinner-border text-primary"></div>
            </div>
            <div v-else-if="riwayatLaporan.length === 0" class="alert alert-info m-3">
              Belum ada data laporan operasi
            </div>
            <div v-else class="table-responsive">
              <table class="table table-bordered table-riwayat-laporan mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Tanggal</th>
                    <th>Jenis Anestesi</th>
                    <th>Tindakan</th>
                    <th>Dokter Bedah</th>
                    <th>Dokter Anestesi</th>
                    <th>Kategori</th>
                    <th>Diagnosis Pre</th>
                    <th>Diagnosis Post</th>
                    <th>Laporan Operasi</th>
                    <th>Selesai Operasi</th>
                    <th>Petugas OK</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(laporan, index) in riwayatLaporan" :key="index">
                    <td>{{ formatDateTime(laporan.tanggal) }}</td>
                    <td>{{ laporan.jenis || '-' }}</td>
                    <td>{{ laporan.tindakan || '-' }}</td>
                    <td>{{ laporan.nm_dokter_bedah || '-' }}</td>
                    <td>{{ laporan.nm_dokter_anestesi || '-' }}</td>
                    <td>{{ laporan.kategori || '-' }}</td>
                    <td>{{ laporan.diagnosis_pre || '-' }}</td>
                    <td>{{ laporan.diagnosis_post || '-' }}</td>
                    <td class="text-wrap" style="max-width: 200px;">{{ laporan.laporan_operasi || '-' }}</td>
                    <td>{{ formatDateTime(laporan.selesaioperasi) }}</td>
                    <td>{{ laporan.nm_perawat_ok || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Tab Content: Monitoring Anestesi -->
        <div v-if="activeTab === 'anastesi'" class="tab-content-operasi">
          <h5 class="section-title mb-3">💉 Monitoring/Pemantauan Anestesi - Sedasi</h5>

          <!-- Form Input Monitoring -->
          <div class="card mb-3">
            <div class="card-header bg-primary text-white">
              <h6 class="mb-0">📝 Input Data Monitoring</h6>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-3">
                  <label class="form-label">Waktu Monitoring <span class="text-danger">*</span></label>
                  <input v-model="monitoringInput.waktu_monitoring" type="datetime-local" class="form-control" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">Menit Ke-</label>
                  <input v-model="monitoringInput.menit_ke" type="number" class="form-control" placeholder="15" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">TD Systole</label>
                  <input v-model="monitoringInput.td_systole" type="text" class="form-control" placeholder="120" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">TD Diastole</label>
                  <input v-model="monitoringInput.td_diastole" type="text" class="form-control" placeholder="80" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">Heart Rate (bpm)</label>
                  <input v-model="monitoringInput.heart_rate" type="text" class="form-control" placeholder="80" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">RR (x/mnt)</label>
                  <input v-model="monitoringInput.respiratory_rate" type="text" class="form-control" placeholder="20" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">SpO2 (%)</label>
                  <input v-model="monitoringInput.spo2" type="text" class="form-control" placeholder="98" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">EtCO2 (mmHg)</label>
                  <input v-model="monitoringInput.etco2" type="text" class="form-control" placeholder="35" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">Suhu (°C)</label>
                  <input v-model="monitoringInput.temperature" type="text" class="form-control" placeholder="36.5" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">MAP (mmHg)</label>
                  <input v-model="monitoringInput.map" type="text" class="form-control" placeholder="93" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">CVP (cmH2O)</label>
                  <input v-model="monitoringInput.cvp" type="text" class="form-control" placeholder="8" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-3">
                  <label class="form-label">Obat Diberikan</label>
                  <input v-model="monitoringInput.obat_diberikan" type="text" class="form-control" placeholder="Nama obat" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-3">
                  <label class="form-label">Dosis Obat</label>
                  <input v-model="monitoringInput.dosis_obat" type="text" class="form-control" placeholder="Dosis" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">Cairan Masuk (ml)</label>
                  <input v-model="monitoringInput.cairan_masuk" type="text" class="form-control" placeholder="500" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">Urine (ml)</label>
                  <input v-model="monitoringInput.urine_output" type="text" class="form-control" placeholder="100" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-2">
                  <label class="form-label">Perdarahan (ml)</label>
                  <input v-model="monitoringInput.perdarahan" type="text" class="form-control" placeholder="50" :disabled="loadingAnestesi">
                </div>

                <div class="col-md-3">
                  <label class="form-label">Petugas Pencatat</label>
                  <select v-model="monitoringInput.nip_petugas" class="form-select" :disabled="loadingAnestesi">
                    <option value="">-- Pilih Petugas --</option>
                    <option v-for="petugas in listPetugas" :key="petugas.nip" :value="petugas.nip">
                      {{ petugas.nama }}
                    </option>
                  </select>
                </div>

                <div class="col-md-12">
                  <label class="form-label">Catatan</label>
                  <textarea v-model="monitoringInput.catatan" class="form-control" rows="2" placeholder="Catatan khusus..." :disabled="loadingAnestesi"></textarea>
                </div>

                <div class="col-12">
                  <button @click="addMonitoring" class="btn btn-success" :disabled="loadingAnestesi || !monitoringInput.waktu_monitoring">
                    <i class="bi bi-plus-circle me-2"></i>
                    Tambah Data Monitoring
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabel Data Monitoring -->
          <div class="card mb-3">
            <div class="card-header bg-info text-white">
              <h6 class="mb-0">📊 Data Monitoring Tersimpan</h6>
            </div>
            <div class="card-body p-0">
              <div v-if="loadingMonitoringList" class="text-center p-4">
                <div class="spinner-border text-primary"></div>
              </div>
              <div v-else-if="monitoringList.length === 0" class="alert alert-info m-3">
                Belum ada data monitoring
              </div>
              <div v-else class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Waktu</th>
                      <th>Menit Ke</th>
                      <th>TD</th>
                      <th>HR</th>
                      <th>RR</th>
                      <th>SpO2</th>
                      <th>EtCO2</th>
                      <th>Suhu</th>
                      <th>MAP</th>
                      <th>CVP</th>
                      <th>Obat</th>
                      <th>Cairan</th>
                      <th>Urine</th>
                      <th>Darah</th>
                      <th>Catatan</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in monitoringList" :key="item.id">
                      <td>{{ formatDateTime(item.waktu_monitoring) }}</td>
                      <td class="text-center">{{ item.menit_ke || '-' }}</td>
                      <td>{{ item.td_systole }}/{{ item.td_diastole }}</td>
                      <td>{{ item.heart_rate }}</td>
                      <td>{{ item.respiratory_rate }}</td>
                      <td>{{ item.spo2 }}</td>
                      <td>{{ item.etco2 }}</td>
                      <td>{{ item.temperature }}</td>
                      <td>{{ item.map }}</td>
                      <td>{{ item.cvp }}</td>
                      <td><small>{{ item.obat_diberikan || '-' }}</small></td>
                      <td>{{ item.cairan_masuk || '-' }}</td>
                      <td>{{ item.urine_output || '-' }}</td>
                      <td>{{ item.perdarahan || '-' }}</td>
                      <td><small>{{ item.catatan || '-' }}</small></td>
                      <td>
                        <button @click="deleteMonitoring(item.id)" class="btn btn-danger btn-sm" :disabled="loadingAnestesi">
                          <i class="bi bi-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Grafik Monitoring -->
          <div v-if="monitoringList.length > 0" class="card">
            <div class="card-header bg-success text-white">
              <h6 class="mb-0">📈 Grafik Monitoring Vital Signs</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Grafik Tekanan Darah & Heart Rate -->
                <div class="col-md-6 mb-3">
                  <canvas ref="chartTD"></canvas>
                </div>

                <!-- Grafik SpO2 & RR -->
                <div class="col-md-6 mb-3">
                  <canvas ref="chartSpO2"></canvas>
                </div>

                <!-- Grafik Suhu & MAP -->
                <div class="col-md-6 mb-3">
                  <canvas ref="chartSuhu"></canvas>
                </div>

                <!-- Grafik Cairan & Output -->
                <div class="col-md-6 mb-3">
                  <canvas ref="chartCairan"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab Content: Obat & Alat -->
        <div v-if="activeTab === 'obat'" class="tab-content-operasi">
          <h5 class="section-title mb-3">💊 Obat & Alat Kesehatan</h5>
          <div class="alert alert-info">
            <p class="mb-0">Form obat dan alat kesehatan dalam pengembangan</p>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal Detail Riwayat -->
  <div class="modal fade" :class="{ show: showModalRiwayat, 'd-block': showModalRiwayat }" tabindex="-1" v-if="showModalRiwayat">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: white;">
          <h5 class="modal-title">📋 Detail Riwayat Perawatan - {{ patient.no_rawat }}</h5>
          <button type="button" class="btn-close btn-close-white" @click="closeModalRiwayat"></button>
        </div>
        <div class="modal-body">
          <div v-if="loadingRiwayat" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data riwayat perawatan...</p>
          </div>

          <div v-else-if="errorRiwayat" class="alert alert-danger">{{ errorRiwayat }}</div>

          <div v-else-if="riwayatData">
            <!-- Triase IGD Primer -->
            <div v-if="riwayatData.triase_primer" class="card shadow-sm mb-3">
              <div class="card-header" style="background-color: #AA0000; color: white;">
                <h6 class="mb-0">🚨 Triase Gawat Darurat - Primer (Zona Merah)</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm table-bordered mb-0">
                    <tbody>
                      <tr>
                        <td width="30%"><strong>Cara Masuk</strong></td>
                        <td>{{ riwayatData.triase_primer.cara_masuk }}</td>
                      </tr>
                      <tr>
                        <td><strong>Transportasi</strong></td>
                        <td>{{ riwayatData.triase_primer.alat_transportasi }}</td>
                      </tr>
                      <tr>
                        <td><strong>Alasan Kedatangan</strong></td>
                        <td>{{ riwayatData.triase_primer.alasan_kedatangan }}</td>
                      </tr>
                      <tr>
                        <td><strong>Keterangan Kedatangan</strong></td>
                        <td>{{ riwayatData.triase_primer.keterangan_kedatangan }}</td>
                      </tr>
                      <tr>
                        <td><strong>Macam Kasus</strong></td>
                        <td>{{ riwayatData.triase_primer.macam_kasus }}</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="table-secondary text-center"><strong>Triase Primer</strong></td>
                      </tr>
                      <tr>
                        <td><strong>Keluhan Utama</strong></td>
                        <td v-html="riwayatData.triase_primer.keluhan_utama.replace(/\n/g, '<br>')"></td>
                      </tr>
                      <tr>
                        <td><strong>Tanda Vital</strong></td>
                        <td>
                          Suhu (C): {{ riwayatData.triase_primer.suhu }},
                          Nyeri: {{ riwayatData.triase_primer.nyeri }},
                          Tensi: {{ riwayatData.triase_primer.tekanan_darah }},
                          Nadi(/menit): {{ riwayatData.triase_primer.nadi }},
                          Saturasi O²(%): {{ riwayatData.triase_primer.saturasi_o2 }},
                          Respirasi(/menit): {{ riwayatData.triase_primer.pernapasan }}
                        </td>
                      </tr>
                      <tr>
                        <td><strong>Kebutuhan Khusus</strong></td>
                        <td>{{ riwayatData.triase_primer.kebutuhan_khusus }}</td>
                      </tr>
                      <!-- Skala 1 - Immediate/Segera -->
                      <template v-if="riwayatData.triase_primer.skala1 && riwayatData.triase_primer.skala1.length > 0">
                        <tr>
                          <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                          <td class="text-center text-white" style="background-color: #AA0000;"><strong>Immediate/Segera</strong></td>
                        </tr>
                        <template v-for="(pemeriksaan, pIndex) in riwayatData.triase_primer.skala1" :key="'s1-' + pIndex">
                          <tr>
                            <td>{{ pemeriksaan.nama_pemeriksaan }}</td>
                            <td style="background-color: #AA0000; color: white;">
                              <div v-for="(detail, dIndex) in pemeriksaan.details" :key="'s1d-' + dIndex">
                                • {{ detail.pengkajian_skala1 }}
                              </div>
                            </td>
                          </tr>
                        </template>
                      </template>
                      <!-- Skala 2 - Emergensi -->
                      <template v-if="riwayatData.triase_primer.skala2 && riwayatData.triase_primer.skala2.length > 0">
                        <tr>
                          <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                          <td class="text-center text-white" style="background-color: #FF0000;"><strong>Emergensi</strong></td>
                        </tr>
                        <template v-for="(pemeriksaan, pIndex) in riwayatData.triase_primer.skala2" :key="'s2-' + pIndex">
                          <tr>
                            <td>{{ pemeriksaan.nama_pemeriksaan }}</td>
                            <td style="background-color: #FF0000; color: white;">
                              <div v-for="(detail, dIndex) in pemeriksaan.details" :key="'s2d-' + dIndex">
                                • {{ detail.pengkajian_skala2 }}
                              </div>
                            </td>
                          </tr>
                        </template>
                      </template>
                      <tr>
                        <td><strong>Plan/Keputusan</strong></td>
                        <td :style="{ backgroundColor: getTriaseColor(riwayatData.triase_primer), color: 'white' }">
                          Zona Merah {{ riwayatData.triase_primer.plan }}
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2" class="table-secondary text-center"><strong>Petugas Triase Primer</strong></td>
                      </tr>
                      <tr>
                        <td><strong>Tanggal & Jam</strong></td>
                        <td>{{ riwayatData.triase_primer.tanggaltriase }}</td>
                      </tr>
                      <tr>
                        <td><strong>Catatan</strong></td>
                        <td>{{ riwayatData.triase_primer.catatan }}</td>
                      </tr>
                      <tr>
                        <td><strong>Dokter/Petugas IGD</strong></td>
                        <td>{{ riwayatData.triase_primer.nik }} - {{ riwayatData.triase_primer.nama }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Triase IGD Sekunder -->
            <div v-if="riwayatData.triase_sekunder" class="card shadow-sm mb-3">
              <div class="card-header" style="background-color: #C8C800; color: white;">
                <h6 class="mb-0">🚨 Triase Gawat Darurat - Sekunder</h6>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm table-bordered mb-0">
                    <tbody>
                      <tr>
                        <td width="30%"><strong>Cara Masuk</strong></td>
                        <td>{{ riwayatData.triase_sekunder.cara_masuk }}</td>
                      </tr>
                      <tr>
                        <td><strong>Transportasi</strong></td>
                        <td>{{ riwayatData.triase_sekunder.alat_transportasi }}</td>
                      </tr>
                      <tr>
                        <td><strong>Alasan Kedatangan</strong></td>
                        <td>{{ riwayatData.triase_sekunder.alasan_kedatangan }}</td>
                      </tr>
                      <tr>
                        <td><strong>Keterangan Kedatangan</strong></td>
                        <td>{{ riwayatData.triase_sekunder.keterangan_kedatangan }}</td>
                      </tr>
                      <tr>
                        <td><strong>Macam Kasus</strong></td>
                        <td>{{ riwayatData.triase_sekunder.macam_kasus }}</td>
                      </tr>
                      <tr>
                        <td colspan="2" class="table-secondary text-center"><strong>Triase Sekunder</strong></td>
                      </tr>
                      <tr>
                        <td><strong>Anamnesa Singkat</strong></td>
                        <td v-html="riwayatData.triase_sekunder.anamnesa_singkat.replace(/\n/g, '<br>')"></td>
                      </tr>
                      <tr>
                        <td><strong>Tanda Vital</strong></td>
                        <td>
                          Suhu (C): {{ riwayatData.triase_sekunder.suhu }},
                          Nyeri: {{ riwayatData.triase_sekunder.nyeri }},
                          Tensi: {{ riwayatData.triase_sekunder.tekanan_darah }},
                          Nadi(/menit): {{ riwayatData.triase_sekunder.nadi }},
                          Saturasi O²(%): {{ riwayatData.triase_sekunder.saturasi_o2 }},
                          Respirasi(/menit): {{ riwayatData.triase_sekunder.pernapasan }}
                        </td>
                      </tr>
                      <!-- Skala 3 - Urgensi -->
                      <template v-if="riwayatData.triase_sekunder.skala3 && riwayatData.triase_sekunder.skala3.length > 0">
                        <tr>
                          <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                          <td class="text-center text-white" style="background-color: #C8C800;"><strong>Urgensi</strong></td>
                        </tr>
                        <template v-for="(pemeriksaan, pIndex) in riwayatData.triase_sekunder.skala3" :key="'s3-' + pIndex">
                          <tr>
                            <td>{{ pemeriksaan.nama_pemeriksaan }}</td>
                            <td style="background-color: #C8C800; color: white;">
                              <div v-for="(detail, dIndex) in pemeriksaan.details" :key="'s3d-' + dIndex">
                                • {{ detail.pengkajian_skala3 }}
                              </div>
                            </td>
                          </tr>
                        </template>
                      </template>
                      <!-- Skala 4 - Semi Urgensi -->
                      <template v-if="riwayatData.triase_sekunder.skala4 && riwayatData.triase_sekunder.skala4.length > 0">
                        <tr>
                          <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                          <td class="text-center text-white" style="background-color: #00AA00;"><strong>Semi Urgensi/Urgensi Rendah</strong></td>
                        </tr>
                        <template v-for="(pemeriksaan, pIndex) in riwayatData.triase_sekunder.skala4" :key="'s4-' + pIndex">
                          <tr>
                            <td>{{ pemeriksaan.nama_pemeriksaan }}</td>
                            <td style="background-color: #00AA00; color: white;">
                              <div v-for="(detail, dIndex) in pemeriksaan.details" :key="'s4d-' + dIndex">
                                • {{ detail.pengkajian_skala4 }}
                              </div>
                            </td>
                          </tr>
                        </template>
                      </template>
                      <!-- Skala 5 - Non Urgensi -->
                      <template v-if="riwayatData.triase_sekunder.skala5 && riwayatData.triase_sekunder.skala5.length > 0">
                        <tr>
                          <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                          <td class="text-center text-white" style="background-color: #969696;"><strong>Non Urgensi</strong></td>
                        </tr>
                        <template v-for="(pemeriksaan, pIndex) in riwayatData.triase_sekunder.skala5" :key="'s5-' + pIndex">
                          <tr>
                            <td>{{ pemeriksaan.nama_pemeriksaan }}</td>
                            <td style="background-color: #969696; color: white;">
                              <div v-for="(detail, dIndex) in pemeriksaan.details" :key="'s5d-' + dIndex">
                                • {{ detail.pengkajian_skala5 }}
                              </div>
                            </td>
                          </tr>
                        </template>
                      </template>
                      <tr>
                        <td><strong>Plan/Keputusan</strong></td>
                        <td :style="{ backgroundColor: getTriaseColor(riwayatData.triase_sekunder), color: 'white' }">
                          {{ riwayatData.triase_sekunder.plan }}
                        </td>
                      </tr>
                      <tr>
                        <td colspan="2" class="table-secondary text-center"><strong>Petugas Triase Sekunder</strong></td>
                      </tr>
                      <tr>
                        <td><strong>Tanggal & Jam</strong></td>
                        <td>{{ riwayatData.triase_sekunder.tanggaltriase }}</td>
                      </tr>
                      <tr>
                        <td><strong>Catatan</strong></td>
                        <td>{{ riwayatData.triase_sekunder.catatan }}</td>
                      </tr>
                      <tr>
                        <td><strong>Dokter/Petugas IGD</strong></td>
                        <td>{{ riwayatData.triase_sekunder.nik }} - {{ riwayatData.triase_sekunder.nama }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Asuhan Medis IGD -->
            <div v-if="riwayatData.asuhan_medis_igd && riwayatData.asuhan_medis_igd.length > 0" class="card shadow-sm mb-3">
              <div class="card-header bg-danger text-white">
                <h6 class="mb-0">🚑 Pengkajian Awal Medis IGD</h6>
              </div>
              <div class="card-body">
                <div v-for="(item, index) in riwayatData.asuhan_medis_igd" :key="index" class="mb-4">
                  <div class="row mb-3">
                    <div class="col-md-4"><strong>Tanggal:</strong> {{ item.tanggal }}</div>
                    <div class="col-md-4"><strong>Dokter:</strong> {{ item.kd_dokter }} - {{ item.nm_dokter }}</div>
                    <div class="col-md-4"><strong>Anamnesis:</strong> {{ item.anamnesis }}{{ item.hubungan ? ', ' + item.hubungan : '' }}</div>
                  </div>
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
                  <div class="border rounded p-3 mb-3">
                    <h6 class="text-primary mb-3">III. STATUS LOKALIS</h6>
                    <div><strong>Keterangan:</strong><br>{{ item.ket_lokalis }}</div>
                  </div>
                  <div class="border rounded p-3 mb-3">
                    <h6 class="text-primary mb-3">IV. PEMERIKSAAN PENUNJANG</h6>
                    <div class="row">
                      <div class="col-md-4"><strong>EKG:</strong><br>{{ item.ekg }}</div>
                      <div class="col-md-4"><strong>Radiologi:</strong><br>{{ item.rad }}</div>
                      <div class="col-md-4"><strong>Laborat:</strong><br>{{ item.lab }}</div>
                    </div>
                  </div>
                  <div class="border rounded p-3 mb-3">
                    <h6 class="text-primary mb-3">V. DIAGNOSIS/ASESMEN</h6>
                    <div>{{ item.diagnosis }}</div>
                  </div>
                  <div class="border rounded p-3 mb-3">
                    <h6 class="text-primary mb-3">VI. TATALAKSANA</h6>
                    <div>{{ item.tata }}</div>
                  </div>
                  <hr v-if="index < riwayatData.asuhan_medis_igd.length - 1">
                </div>
              </div>
            </div>

            <!-- Pemeriksaan Rawat Jalan (SOAP) -->
            <div v-if="riwayatData.pemeriksaan_ralan && riwayatData.pemeriksaan_ralan.length > 0" class="card shadow-sm mb-3">
              <div class="card-header" style="background-color: #17a2b8; color: white;">
                <h6 class="mb-0">📋 Pemeriksaan Rawat Jalan (SOAP)</h6>
              </div>
              <div class="card-body">
                <div v-for="(item, index) in riwayatData.pemeriksaan_ralan" :key="index" class="mb-4">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                      <tbody>
                        <tr class="table-secondary">
                          <td width="5%" class="text-center"><strong>No.</strong></td>
                          <td width="15%"><strong>Tanggal</strong></td>
                          <td width="53%" colspan="7"><strong>Dokter/Paramedis</strong></td>
                          <td width="27%" colspan="3"><strong>Profesi/Jabatan/Departemen</strong></td>
                        </tr>
                        <tr>
                          <td class="text-center">{{ index + 1 }}</td>
                          <td>{{ item.tgl_perawatan }} {{ item.jam_rawat }}</td>
                          <td colspan="7">{{ item.nip }} - {{ item.nama }}</td>
                          <td colspan="3">{{ item.jbtn }}</td>
                        </tr>
                        <!-- Subjek -->
                        <tr v-if="item.keluhan">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Subjek</strong></td>
                          <td colspan="8" v-html="item.keluhan.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <!-- Objek -->
                        <tr v-if="item.pemeriksaan">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Objek</strong></td>
                          <td colspan="8" v-html="item.pemeriksaan.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <!-- Tanda Vital Header -->
                        <tr class="table-secondary">
                          <td colspan="2"></td>
                          <td width="8%" class="text-center"><strong>Suhu(C)</strong></td>
                          <td width="8%" class="text-center"><strong>Tensi</strong></td>
                          <td width="8%" class="text-center"><strong>Nadi(/mnt)</strong></td>
                          <td width="8%" class="text-center"><strong>Resp(/mnt)</strong></td>
                          <td width="8%" class="text-center"><strong>Tinggi(Cm)</strong></td>
                          <td width="8%" class="text-center"><strong>Berat(Kg)</strong></td>
                          <td width="8%" class="text-center"><strong>SpO2(%)</strong></td>
                          <td width="8%" class="text-center"><strong>GCS(E,V,M)</strong></td>
                          <td width="8%" class="text-center"><strong>Kesadaran</strong></td>
                          <td width="8%" class="text-center"><strong>L.P.(Cm)</strong></td>
                        </tr>
                        <!-- Tanda Vital Values -->
                        <tr>
                          <td colspan="2"></td>
                          <td class="text-center">{{ item.suhu_tubuh }}</td>
                          <td class="text-center">{{ item.tensi }}</td>
                          <td class="text-center">{{ item.nadi }}</td>
                          <td class="text-center">{{ item.respirasi }}</td>
                          <td class="text-center">{{ item.tinggi }}</td>
                          <td class="text-center">{{ item.berat }}</td>
                          <td class="text-center">{{ item.spo2 }}</td>
                          <td class="text-center">{{ item.gcs }}</td>
                          <td class="text-center">{{ item.kesadaran }}</td>
                          <td class="text-center">{{ item.lingkar_perut }}</td>
                        </tr>
                        <!-- Alergi -->
                        <tr v-if="item.alergi">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Alergi</strong></td>
                          <td colspan="8">: {{ item.alergi }}</td>
                        </tr>
                        <!-- Asesmen -->
                        <tr v-if="item.penilaian">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Asesmen</strong></td>
                          <td colspan="8" v-html="': ' + item.penilaian.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <!-- Plan -->
                        <tr v-if="item.rtl">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Plan</strong></td>
                          <td colspan="8" v-html="': ' + item.rtl.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <!-- Instruksi/Implementasi -->
                        <tr v-if="item.instruksi">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Inst/Impl</strong></td>
                          <td colspan="8" v-html="': ' + item.instruksi.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <!-- Evaluasi -->
                        <tr v-if="item.evaluasi">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Evaluasi</strong></td>
                          <td colspan="8" v-html="': ' + item.evaluasi.replace(/\n/g, '<br>')"></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <hr v-if="index < riwayatData.pemeriksaan_ralan.length - 1">
                </div>
              </div>
            </div>

            <!-- Pemeriksaan Rawat Inap (SOAP) -->
            <div v-if="riwayatData.pemeriksaan_ranap && riwayatData.pemeriksaan_ranap.length > 0" class="card shadow-sm mb-3">
              <div class="card-header" style="background-color: #6f42c1; color: white;">
                <h6 class="mb-0">🛏️ Pemeriksaan Rawat Inap (SOAP)</h6>
              </div>
              <div class="card-body">
                <div v-for="(item, index) in riwayatData.pemeriksaan_ranap" :key="index" class="mb-4">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                      <tbody>
                        <tr class="table-secondary">
                          <td width="5%" class="text-center"><strong>No.</strong></td>
                          <td width="15%"><strong>Tanggal</strong></td>
                          <td width="50%" colspan="6"><strong>Dokter/Paramedis</strong></td>
                          <td width="30%" colspan="3"><strong>Profesi/Jabatan/Departemen</strong></td>
                        </tr>
                        <tr>
                          <td class="text-center">{{ index + 1 }}</td>
                          <td>{{ item.tgl_perawatan }} {{ item.jam_rawat }}</td>
                          <td colspan="6">{{ item.nip }} - {{ item.nama }}</td>
                          <td colspan="3">{{ item.jbtn }}</td>
                        </tr>
                        <tr v-if="item.keluhan">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Subjek</strong></td>
                          <td colspan="6" v-html="item.keluhan.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <tr v-if="item.pemeriksaan">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Objek</strong></td>
                          <td colspan="6" v-html="item.pemeriksaan.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <tr class="table-secondary">
                          <td colspan="2"></td>
                          <td width="9%" class="text-center"><strong>Suhu(C)</strong></td>
                          <td width="9%" class="text-center"><strong>Tensi</strong></td>
                          <td width="9%" class="text-center"><strong>Nadi(/mnt)</strong></td>
                          <td width="9%" class="text-center"><strong>Resp(/mnt)</strong></td>
                          <td width="9%" class="text-center"><strong>Tinggi(Cm)</strong></td>
                          <td width="9%" class="text-center"><strong>Berat(Kg)</strong></td>
                          <td width="9%" class="text-center"><strong>SpO2(%)</strong></td>
                          <td width="9%" class="text-center"><strong>GCS(E,V,M)</strong></td>
                          <td width="9%" class="text-center"><strong>Kesadaran</strong></td>
                        </tr>
                        <tr>
                          <td colspan="2"></td>
                          <td class="text-center">{{ item.suhu_tubuh }}</td>
                          <td class="text-center">{{ item.tensi }}</td>
                          <td class="text-center">{{ item.nadi }}</td>
                          <td class="text-center">{{ item.respirasi }}</td>
                          <td class="text-center">{{ item.tinggi }}</td>
                          <td class="text-center">{{ item.berat }}</td>
                          <td class="text-center">{{ item.spo2 }}</td>
                          <td class="text-center">{{ item.gcs }}</td>
                          <td class="text-center">{{ item.kesadaran }}</td>
                        </tr>
                        <tr v-if="item.alergi">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Alergi</strong></td>
                          <td colspan="6">: {{ item.alergi }}</td>
                        </tr>
                        <tr v-if="item.penilaian">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Asesmen</strong></td>
                          <td colspan="6" v-html="': ' + item.penilaian.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <tr v-if="item.rtl">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Plan</strong></td>
                          <td colspan="6" v-html="': ' + item.rtl.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <tr v-if="item.instruksi">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Inst/Impl</strong></td>
                          <td colspan="6" v-html="': ' + item.instruksi.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <tr v-if="item.evaluasi">
                          <td colspan="2"></td>
                          <td colspan="2"><strong>Evaluasi</strong></td>
                          <td colspan="6" v-html="': ' + item.evaluasi.replace(/\n/g, '<br>')"></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <hr v-if="index < riwayatData.pemeriksaan_ranap.length - 1">
                </div>
              </div>
            </div>

            <!-- Pemberian Obat -->
            <div v-if="riwayatData.pemberian_obat && riwayatData.pemberian_obat.length > 0" class="card shadow-sm mb-3">
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
                      <tr v-for="(item, index) in riwayatData.pemberian_obat" :key="index">
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

            <!-- Tindakan Rawat Jalan Dokter -->
            <div v-if="riwayatData.rawat_jalan_dokter && riwayatData.rawat_jalan_dokter.length > 0" class="card shadow-sm mb-3">
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
                      <tr v-for="(item, index) in riwayatData.rawat_jalan_dokter" :key="index">
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

            <!-- Total Biaya -->
            <div v-if="totalBiaya > 0" class="card shadow-sm border-primary">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Total Biaya Perawatan</h5>
                  <h4 class="mb-0 text-primary">Rp {{ formatRupiah(totalBiaya) }}</h4>
                </div>
              </div>
            </div>

            <!-- Jika tidak ada data -->
            <div v-if="!riwayatData.pemeriksaan_ralan && !riwayatData.pemeriksaan_ranap && !riwayatData.pemberian_obat && !riwayatData.rawat_jalan_dokter" class="alert alert-info">
              <p class="mb-0">Belum ada riwayat perawatan untuk nomor rawat ini</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModalRiwayat">Tutup</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade" :class="{ show: showModalRiwayat }" v-if="showModalRiwayat"></div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '@/api/axios'
import { Chart, registerables } from 'chart.js'
import Swal from 'sweetalert2'

Chart.register(...registerables)

const route = useRoute()
const router = useRouter()

const activeTab = ref('tim')
const loading = ref(false)
const loadingLaporan = ref(false)
const loadingAnestesi = ref(false)
const loadingMonitoringList = ref(false)

// Sidebar state
const showSidebar = ref(false)

// Lists for dropdowns
const listDokter = ref([])
const listPetugas = ref([])

// Riwayat Laporan Operasi
const riwayatLaporan = ref([])
const loadingRiwayatLaporan = ref(false)

// Monitoring Anestesi
const monitoringList = ref([])
const monitoringInput = ref({
  waktu_monitoring: '',
  menit_ke: null,
  td_systole: '',
  td_diastole: '',
  heart_rate: '',
  respiratory_rate: '',
  spo2: '',
  etco2: '',
  temperature: '',
  map: '',
  cvp: '',
  obat_diberikan: '',
  dosis_obat: '',
  cairan_masuk: '',
  urine_output: '',
  perdarahan: '',
  catatan: '',
  nip_petugas: ''
})

// Chart refs
const chartTD = ref(null)
const chartSpO2 = ref(null)
const chartSuhu = ref(null)
const chartCairan = ref(null)

// Chart instances
let chartInstanceTD = null
let chartInstanceSpO2 = null
let chartInstanceSuhu = null
let chartInstanceCairan = null

// Modal Riwayat state
const showModalRiwayat = ref(false)
const loadingRiwayat = ref(false)
const errorRiwayat = ref('')
const riwayatData = ref(null)
const totalBiaya = ref(0)

// Patient data from route params
const patient = ref({
  no_rawat: route.query.no_rawat || '',
  no_rkm_medis: route.query.no_rkm_medis || '',
  nm_pasien: route.query.nm_pasien || '',
  umur: route.query.umur || '',
  jk: route.query.jk || '',
  kd_dokter: route.query.kd_dokter || '',
  nm_dokter: route.query.nm_dokter || '',
  tanggal: route.query.tanggal || '',
  jam_mulai: route.query.jam_mulai || '',
  jam_selesai: route.query.jam_selesai || '',
  kode_paket: route.query.kode_paket || '',
  nm_perawatan: route.query.nm_perawatan || '',
  kd_ruang_ok: route.query.kd_ruang_ok || '',
  nm_ruang_ok: route.query.nm_ruang_ok || ''
})

// Helper function to combine date and time to datetime-local format
const combineDateTimeLocal = (date, time) => {
  if (!date || !time) return ''
  // Format: YYYY-MM-DDTHH:MM:SS -> YYYY-MM-DDTHH:MM
  return `${date}T${time.substring(0, 5)}`
}

// Helper function to combine date and time to datetime format for database
const combineDateTime = (date, time) => {
  if (!date || !time) return ''
  return `${date} ${time}`
}

// Tim Operasi Form Data
const timOperasi = ref({
  tanggal: patient.value.tanggal || '',
  jam_mulai: patient.value.jam_mulai || '',
  jam_selesai: patient.value.jam_selesai || '',
  status: ''
})

// Laporan Operasi Form Data
const laporanOperasi = ref({
  tanggal: combineDateTimeLocal(patient.value.tanggal, patient.value.jam_mulai),
  jenis: '-',
  tindakan: patient.value.nm_perawatan || '',
  kd_dokter_bedah: patient.value.kd_dokter || '-',
  kd_dokter_anestesi: '-',
  kategori: '-',
  diagnosis_pre: '',
  diagnosis_post: '',
  jaringan: '',
  pemeriksaan_pa: 'Tidak',
  ket_pa: '-',
  output_cairan: 'Darah',
  ket_output: '-',
  laporan_operasi: '',
  selesaioperasi: combineDateTimeLocal(patient.value.tanggal, patient.value.jam_selesai),
  nip_perawat_ok: ''
})

// Laporan Anestesi Form Data
const laporanAnestesi = ref({
  tanggal: combineDateTimeLocal(patient.value.tanggal, patient.value.jam_mulai),
  kd_dokter_bedah: patient.value.kd_dokter || '',
  kd_dokter_anestesi: '',
  nip_perawat_ok: '',
  diagnosa_pre_bedah: '',
  diagnosa_pasca_bedah: '',
  tindakan_jenis_pembedahan: patient.value.nm_perawatan || '',
  // Pre Induksi
  pre_induksi_jam: '',
  pre_induksi_kesadaran: '',
  pre_induksi_td: '',
  pre_induksi_nadi: '',
  pre_induksi_rr: '',
  pre_induksi_suhu: '',
  pre_induksi_o2: '',
  pre_induksi_tb: '',
  pre_induksi_bb: '',
  pre_induksi_rhesus: '',
  pre_induksi_hb: '',
  pre_induksi_ht: '',
  pre_induksi_leko: '',
  pre_induksi_trombo: '',
  pre_induksi_btct: '',
  pre_induksi_gds: '',
  pre_induksi_lainlain: '',
  // Monitoring
  monitoring_ekg: 'Tidak',
  monitoring_ekg_keterangan: '',
  monitoring_arteri: 'Tidak',
  monitoring_arteri_keterangan: '',
  monitoring_cvp: 'Tidak',
  monitoring_cvp_keterangan: '',
  monitoring_etco: 'Tidak',
  monitoring_stetoskop: 'Tidak',
  monitoring_nibp: 'Tidak',
  monitoring_ngt: 'Tidak',
  monitoring_bis: 'Tidak',
  monitoring_cath_a_pulmo: 'Tidak',
  monitoring_spo2: 'Tidak',
  monitoring_kateter: 'Tidak',
  monitoring_temp: 'Tidak',
  monitoring_lainlain: '',
  // Status Fisik
  status_fisik_asa: '',
  status_fisik_alergi: 'Tidak',
  status_fisik_alergi_keterangan: '',
  status_fisik_penyulit_sedasi: '',
  // Perencanaan Lanjut
  perencanaan_lanjut_sedasi: 'Tidak',
  perencanaan_lanjut_sedasi_keterangan: '',
  perencanaan_lanjut_spinal: 'Tidak',
  perencanaan_lanjut_epidural: 'Tidak',
  perencanaan_lanjut_anestesi_umum: 'Tidak',
  perencanaan_lanjut_anestesi_umum_keterangan: '',
  perencanaan_lanjut_blok_perifer: 'Tidak',
  perencanaan_lanjut_blok_perifer_keterangan: '',
  perencanaan_batal: 'Tidak',
  perencanaan_batal_alasan: ''
})

// Validation
const isTimOperasiValid = computed(() => {
  return timOperasi.value.tanggal &&
         timOperasi.value.jam_mulai &&
         timOperasi.value.jam_selesai &&
         timOperasi.value.status
})

const isLaporanValid = computed(() => {
  return laporanOperasi.value.tanggal &&
         laporanOperasi.value.jenis &&
         laporanOperasi.value.tindakan
})

const isAnestesiValid = computed(() => {
  return laporanAnestesi.value.tanggal &&
         laporanAnestesi.value.kd_dokter_bedah &&
         laporanAnestesi.value.kd_dokter_anestesi
})

const formatDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  return `${day}/${month}/${year}`
}

const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  const d = new Date(datetime)
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  const hour = String(d.getHours()).padStart(2, '0')
  const minute = String(d.getMinutes()).padStart(2, '0')
  return `${day}/${month}/${year} ${hour}:${minute}`
}

const formatRupiah = (amount) => {
  if (!amount) return '0'
  return new Intl.NumberFormat('id-ID').format(amount)
}

const getTriaseColor = (triaseData) => {
  // Determine color based on which skala is present
  if (!triaseData) return '#969696' // Default abu-abu

  // Check triase primer (skala 1 & 2)
  if (triaseData.skala1 && triaseData.skala1.length > 0) {
    return '#AA0000' // Merah tua - Immediate
  }
  if (triaseData.skala2 && triaseData.skala2.length > 0) {
    return '#FF0000' // Merah - Emergensi
  }

  // Check triase sekunder (skala 3, 4, 5)
  if (triaseData.skala3 && triaseData.skala3.length > 0) {
    return '#C8C800' // Kuning - Urgensi
  }
  if (triaseData.skala4 && triaseData.skala4.length > 0) {
    return '#00AA00' // Hijau - Semi Urgensi
  }
  if (triaseData.skala5 && triaseData.skala5.length > 0) {
    return '#969696' // Abu-abu - Non Urgensi
  }

  return '#969696' // Default
}

const goBack = () => {
  router.back()
}

const toggleSidebar = () => {
  showSidebar.value = !showSidebar.value
}

const closeSidebar = () => {
  showSidebar.value = false
}

// Watch activeTab to close sidebar when switching tabs on mobile
watch(activeTab, () => {
  if (window.innerWidth <= 767) {
    closeSidebar()
  }
})

const saveTimOperasi = async () => {
  if (!isTimOperasiValid.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Data Tidak Lengkap',
      text: 'Mohon lengkapi semua field yang wajib diisi',
      confirmButtonColor: '#0891b2'
    })
    return
  }

  loading.value = true
  try {
    const payload = {
      no_rawat: patient.value.no_rawat,
      tanggal: timOperasi.value.tanggal,
      jam_mulai: timOperasi.value.jam_mulai,
      jam_selesai: timOperasi.value.jam_selesai,
      status: timOperasi.value.status
    }

    const { data } = await apiClient.put('/operasi/update-tim', payload)

    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data tim operasi berhasil disimpan',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      })
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: data.message || 'Gagal menyimpan data',
        confirmButtonColor: '#0891b2'
      })
    }
  } catch (err) {
    console.error('Save tim operasi error:', err)
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Kesalahan',
      text: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data',
      confirmButtonColor: '#0891b2'
    })
  } finally {
    loading.value = false
  }
}

const resetTimOperasi = () => {
  timOperasi.value = {
    tanggal: patient.value.tanggal || '',
    jam_mulai: patient.value.jam_mulai || '',
    jam_selesai: patient.value.jam_selesai || '',
    status: ''
  }
}

const openDetailRiwayat = async () => {
  if (!patient.value.no_rawat) {
    Swal.fire({
      icon: 'warning',
      title: 'Data Tidak Lengkap',
      text: 'No. Rawat tidak tersedia',
      confirmButtonColor: '#0891b2'
    })
    return
  }

  showModalRiwayat.value = true
  loadingRiwayat.value = true
  errorRiwayat.value = ''
  riwayatData.value = null

  try {
    const { data } = await apiClient.get(`/riwayat-perawatan/${patient.value.no_rawat}`)

    if (data.success) {
      riwayatData.value = data.data
      totalBiaya.value = data.total_biaya || 0
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

const closeModalRiwayat = () => {
  showModalRiwayat.value = false
  riwayatData.value = null
  totalBiaya.value = 0
  errorRiwayat.value = ''
}

const loadDokter = async () => {
  try {
    const { data } = await apiClient.get('/operasi/dokter')
    if (data.success) {
      listDokter.value = data.data
    }
  } catch (err) {
    console.error('Load dokter error:', err)
  }
}

const loadPetugas = async () => {
  try {
    const { data } = await apiClient.get('/operasi/petugas')
    if (data.success) {
      listPetugas.value = data.data
    }
  } catch (err) {
    console.error('Load petugas error:', err)
  }
}

const saveLaporanOperasi = async () => {
  if (!isLaporanValid.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Data Tidak Lengkap',
      text: 'Mohon lengkapi tanggal, jenis, dan tindakan operasi',
      confirmButtonColor: '#0891b2'
    })
    return
  }

  loadingLaporan.value = true
  try {
    const payload = {
      no_rawat: patient.value.no_rawat,
      tanggal: laporanOperasi.value.tanggal,
      jenis: laporanOperasi.value.jenis,
      tindakan: laporanOperasi.value.tindakan,
      kd_dokter_bedah: laporanOperasi.value.kd_dokter_bedah,
      kd_dokter_anestesi: laporanOperasi.value.kd_dokter_anestesi,
      kategori: laporanOperasi.value.kategori,
      diagnosis_pre: laporanOperasi.value.diagnosis_pre,
      diagnosis_post: laporanOperasi.value.diagnosis_post,
      jaringan: laporanOperasi.value.jaringan,
      pemeriksaan_pa: laporanOperasi.value.pemeriksaan_pa,
      ket_pa: laporanOperasi.value.ket_pa,
      output_cairan: laporanOperasi.value.output_cairan,
      ket_output: laporanOperasi.value.ket_output,
      laporan_operasi: laporanOperasi.value.laporan_operasi,
      selesaioperasi: laporanOperasi.value.selesaioperasi,
      nip_perawat_ok: laporanOperasi.value.nip_perawat_ok
    }

    const { data } = await apiClient.post('/operasi/laporan', payload)

    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Laporan operasi berhasil disimpan',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      })
      // Refresh riwayat laporan
      fetchRiwayatLaporan()
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: data.message || 'Gagal menyimpan laporan',
        confirmButtonColor: '#0891b2'
      })
    }
  } catch (err) {
    console.error('Save laporan operasi error:', err)
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Kesalahan',
      text: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan laporan',
      confirmButtonColor: '#0891b2'
    })
  } finally {
    loadingLaporan.value = false
  }
}

const resetLaporanOperasi = () => {
  laporanOperasi.value = {
    tanggal: combineDateTimeLocal(patient.value.tanggal, patient.value.jam_mulai),
    jenis: '-',
    tindakan: patient.value.nm_perawatan || '',
    kd_dokter_bedah: patient.value.kd_dokter || '-',
    kd_dokter_anestesi: '-',
    kategori: '-',
    diagnosis_pre: '',
    diagnosis_post: '',
    jaringan: '',
    pemeriksaan_pa: 'Tidak',
    ket_pa: '-',
    output_cairan: 'Darah',
    ket_output: '-',
    laporan_operasi: '',
    selesaioperasi: combineDateTimeLocal(patient.value.tanggal, patient.value.jam_selesai),
    nip_perawat_ok: ''
  }
}

// Fetch riwayat laporan operasi
const fetchRiwayatLaporan = async () => {
  if (!patient.value.no_rawat) return

  loadingRiwayatLaporan.value = true
  try {
    const { data } = await apiClient.get(`/operasi/laporan/${encodeURIComponent(patient.value.no_rawat)}`)
    if (data.success) {
      riwayatLaporan.value = data.data || []
    }
  } catch (err) {
    console.error('Fetch riwayat laporan error:', err)
  } finally {
    loadingRiwayatLaporan.value = false
  }
}

// Edit laporan operasi - load data ke form
const editLaporanOperasi = (laporan) => {
  laporanOperasi.value = {
    tanggal: laporan.tanggal || '',
    jenis: laporan.jenis || '-',
    tindakan: laporan.tindakan || '',
    kd_dokter_bedah: laporan.kd_dokter_bedah || '-',
    kd_dokter_anestesi: laporan.kd_dokter_anestesi || '-',
    kategori: laporan.kategori || '-',
    diagnosis_pre: laporan.diagnosis_pre || '',
    diagnosis_post: laporan.diagnosis_post || '',
    jaringan: laporan.jaringan || '',
    pemeriksaan_pa: laporan.pemeriksaan_pa || 'Tidak',
    ket_pa: laporan.ket_pa || '-',
    output_cairan: laporan.output_cairan || 'Darah',
    ket_output: laporan.ket_output || '-',
    laporan_operasi: laporan.laporan_operasi || '',
    selesaioperasi: laporan.selesaioperasi || '',
    nip_perawat_ok: laporan.nip_perawat_ok || ''
  }

  // Scroll ke form
  window.scrollTo({ top: 0, behavior: 'smooth' })

  Swal.fire({
    icon: 'info',
    title: 'Mode Edit',
    text: 'Data laporan telah dimuat ke form. Silakan edit dan simpan kembali.',
    confirmButtonColor: '#0891b2',
    timer: 2000,
    timerProgressBar: true
  })
}

// Hapus laporan operasi
const deleteLaporanOperasi = async (laporan) => {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Konfirmasi Hapus',
    text: `Apakah Anda yakin ingin menghapus laporan operasi tanggal ${formatDateTime(laporan.tanggal)}?`,
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal'
  })

  if (result.isConfirmed) {
    try {
      const { data } = await apiClient.delete(`/operasi/laporan/${encodeURIComponent(patient.value.no_rawat)}/${encodeURIComponent(laporan.tanggal)}`)

      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Laporan operasi berhasil dihapus',
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true
        })
        // Refresh riwayat
        fetchRiwayatLaporan()
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: data.message || 'Gagal menghapus laporan',
          confirmButtonColor: '#0891b2'
        })
      }
    } catch (err) {
      console.error('Delete laporan error:', err)
      Swal.fire({
        icon: 'error',
        title: 'Terjadi Kesalahan',
        text: err.response?.data?.message || 'Terjadi kesalahan saat menghapus laporan',
        confirmButtonColor: '#0891b2'
      })
    }
  }
}

const saveLaporanAnestesi = async () => {
  if (!isAnestesiValid.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Data Tidak Lengkap',
      text: 'Mohon lengkapi tanggal, dokter bedah, dan dokter anestesi',
      confirmButtonColor: '#0891b2'
    })
    return
  }

  loadingAnestesi.value = true
  try {
    const payload = {
      no_rawat: patient.value.no_rawat,
      ...laporanAnestesi.value
    }

    const { data } = await apiClient.post('/operasi/anestesi', payload)

    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Laporan anestesi berhasil disimpan',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      })
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: data.message || 'Gagal menyimpan laporan anestesi',
        confirmButtonColor: '#0891b2'
      })
    }
  } catch (err) {
    console.error('Save laporan anestesi error:', err)
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Kesalahan',
      text: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan laporan anestesi',
      confirmButtonColor: '#0891b2'
    })
  } finally {
    loadingAnestesi.value = false
  }
}

const resetLaporanAnestesi = () => {
  laporanAnestesi.value = {
    tanggal: combineDateTimeLocal(patient.value.tanggal, patient.value.jam_mulai),
    kd_dokter_bedah: patient.value.kd_dokter || '',
    kd_dokter_anestesi: '',
    nip_perawat_ok: '',
    diagnosa_pre_bedah: '',
    diagnosa_pasca_bedah: '',
    tindakan_jenis_pembedahan: patient.value.nm_perawatan || '',
    pre_induksi_jam: '',
    pre_induksi_kesadaran: '',
    pre_induksi_td: '',
    pre_induksi_nadi: '',
    pre_induksi_rr: '',
    pre_induksi_suhu: '',
    pre_induksi_o2: '',
    pre_induksi_tb: '',
    pre_induksi_bb: '',
    pre_induksi_rhesus: '',
    pre_induksi_hb: '',
    pre_induksi_ht: '',
    pre_induksi_leko: '',
    pre_induksi_trombo: '',
    pre_induksi_btct: '',
    pre_induksi_gds: '',
    pre_induksi_lainlain: '',
    monitoring_ekg: 'Tidak',
    monitoring_ekg_keterangan: '',
    monitoring_arteri: 'Tidak',
    monitoring_arteri_keterangan: '',
    monitoring_cvp: 'Tidak',
    monitoring_cvp_keterangan: '',
    monitoring_etco: 'Tidak',
    monitoring_stetoskop: 'Tidak',
    monitoring_nibp: 'Tidak',
    monitoring_ngt: 'Tidak',
    monitoring_bis: 'Tidak',
    monitoring_cath_a_pulmo: 'Tidak',
    monitoring_spo2: 'Tidak',
    monitoring_kateter: 'Tidak',
    monitoring_temp: 'Tidak',
    monitoring_lainlain: '',
    status_fisik_asa: '',
    status_fisik_alergi: 'Tidak',
    status_fisik_alergi_keterangan: '',
    status_fisik_penyulit_sedasi: '',
    perencanaan_lanjut_sedasi: 'Tidak',
    perencanaan_lanjut_sedasi_keterangan: '',
    perencanaan_lanjut_spinal: 'Tidak',
    perencanaan_lanjut_epidural: 'Tidak',
    perencanaan_lanjut_anestesi_umum: 'Tidak',
    perencanaan_lanjut_anestesi_umum_keterangan: '',
    perencanaan_lanjut_blok_perifer: 'Tidak',
    perencanaan_lanjut_blok_perifer_keterangan: '',
    perencanaan_batal: 'Tidak',
    perencanaan_batal_alasan: ''
  }
}

// Monitoring Anestesi Functions
const loadMonitoringList = async () => {
  loadingMonitoringList.value = true
  try {
    const { data } = await apiClient.get('/operasi/monitoring', {
      params: {
        no_rawat: patient.value.no_rawat,
        tanggal_operasi: patient.value.tanggal
      }
    })

    if (data.success) {
      monitoringList.value = data.data
    }
  } catch (err) {
    console.error('Load monitoring list error:', err)
  } finally {
    loadingMonitoringList.value = false
  }
}

const addMonitoring = async () => {
  if (!monitoringInput.value.waktu_monitoring) {
    Swal.fire({
      icon: 'warning',
      title: 'Data Tidak Lengkap',
      text: 'Waktu monitoring harus diisi',
      confirmButtonColor: '#0891b2'
    })
    return
  }

  loadingAnestesi.value = true
  try {
    const payload = {
      no_rawat: patient.value.no_rawat,
      tanggal_operasi: patient.value.tanggal,
      ...monitoringInput.value
    }

    const { data } = await apiClient.post('/operasi/monitoring', payload)

    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data monitoring berhasil ditambahkan',
        confirmButtonColor: '#0891b2',
        timer: 1500,
        showConfirmButton: false
      })
      // Reset form
      monitoringInput.value = {
        waktu_monitoring: '',
        menit_ke: null,
        td_systole: '',
        td_diastole: '',
        heart_rate: '',
        respiratory_rate: '',
        spo2: '',
        etco2: '',
        temperature: '',
        map: '',
        cvp: '',
        obat_diberikan: '',
        dosis_obat: '',
        cairan_masuk: '',
        urine_output: '',
        perdarahan: '',
        catatan: '',
        nip_petugas: ''
      }
      // Reload list
      loadMonitoringList()
    }
  } catch (err) {
    console.error('Add monitoring error:', err)
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Kesalahan',
      text: err.response?.data?.message || 'Terjadi kesalahan saat menambahkan data monitoring',
      confirmButtonColor: '#0891b2'
    })
  } finally {
    loadingAnestesi.value = false
  }
}

const deleteMonitoring = async (id) => {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Konfirmasi Hapus',
    text: 'Yakin ingin menghapus data monitoring ini?',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal'
  })

  if (!result.isConfirmed) {
    return
  }

  loadingAnestesi.value = true
  try {
    const { data } = await apiClient.delete(`/operasi/monitoring/${id}`)

    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data monitoring berhasil dihapus',
        confirmButtonColor: '#0891b2',
        timer: 1500,
        showConfirmButton: false
      })
      loadMonitoringList()
    }
  } catch (err) {
    console.error('Delete monitoring error:', err)
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Kesalahan',
      text: err.response?.data?.message || 'Terjadi kesalahan saat menghapus data monitoring',
      confirmButtonColor: '#0891b2'
    })
  } finally {
    loadingAnestesi.value = false
  }
}

// Create Charts
const createCharts = async () => {
  await nextTick()

  if (monitoringList.value.length === 0) return

  // Destroy existing charts
  if (chartInstanceTD) chartInstanceTD.destroy()
  if (chartInstanceSpO2) chartInstanceSpO2.destroy()
  if (chartInstanceSuhu) chartInstanceSuhu.destroy()
  if (chartInstanceCairan) chartInstanceCairan.destroy()

  // Prepare data
  const labels = monitoringList.value.map(item => {
    const d = new Date(item.waktu_monitoring)
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
  })

  // Chart 1: Tekanan Darah & Heart Rate
  if (chartTD.value) {
    chartInstanceTD = new Chart(chartTD.value, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'TD Systole (mmHg)',
            data: monitoringList.value.map(item => item.td_systole || null),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            yAxisID: 'y'
          },
          {
            label: 'TD Diastole (mmHg)',
            data: monitoringList.value.map(item => item.td_diastole || null),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1,
            yAxisID: 'y'
          },
          {
            label: 'Heart Rate (bpm)',
            data: monitoringList.value.map(item => item.heart_rate || null),
            borderColor: 'rgb(255, 206, 86)',
            backgroundColor: 'rgba(255, 206, 86, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          title: {
            display: true,
            text: 'Tekanan Darah & Heart Rate'
          }
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: {
              display: true,
              text: 'TD (mmHg)'
            }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: {
              display: true,
              text: 'HR (bpm)'
            },
            grid: {
              drawOnChartArea: false,
            }
          }
        }
      }
    })
  }

  // Chart 2: SpO2, RR, EtCO2
  if (chartSpO2.value) {
    chartInstanceSpO2 = new Chart(chartSpO2.value, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'SpO2 (%)',
            data: monitoringList.value.map(item => item.spo2 || null),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            yAxisID: 'y'
          },
          {
            label: 'RR (x/mnt)',
            data: monitoringList.value.map(item => item.respiratory_rate || null),
            borderColor: 'rgb(153, 102, 255)',
            backgroundColor: 'rgba(153, 102, 255, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
          },
          {
            label: 'EtCO2 (mmHg)',
            data: monitoringList.value.map(item => item.etco2 || null),
            borderColor: 'rgb(255, 159, 64)',
            backgroundColor: 'rgba(255, 159, 64, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          title: {
            display: true,
            text: 'SpO2, Respiratory Rate & EtCO2'
          }
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: {
              display: true,
              text: 'SpO2 (%)'
            },
            min: 80,
            max: 100
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: {
              display: true,
              text: 'RR & EtCO2'
            },
            grid: {
              drawOnChartArea: false,
            }
          }
        }
      }
    })
  }

  // Chart 3: Suhu & MAP
  if (chartSuhu.value) {
    chartInstanceSuhu = new Chart(chartSuhu.value, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Suhu (°C)',
            data: monitoringList.value.map(item => item.temperature || null),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            yAxisID: 'y'
          },
          {
            label: 'MAP (mmHg)',
            data: monitoringList.value.map(item => item.map || null),
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
          },
          {
            label: 'CVP (cmH2O)',
            data: monitoringList.value.map(item => item.cvp || null),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          title: {
            display: true,
            text: 'Suhu, MAP & CVP'
          }
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: {
              display: true,
              text: 'Suhu (°C)'
            },
            min: 35,
            max: 39
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: {
              display: true,
              text: 'MAP & CVP'
            },
            grid: {
              drawOnChartArea: false,
            }
          }
        }
      }
    })
  }

  // Chart 4: Cairan Masuk, Urine, Perdarahan (Cumulative)
  if (chartCairan.value) {
    let cumulativeCairan = 0
    let cumulativeUrine = 0
    let cumulativePerdarahan = 0

    const cairanData = monitoringList.value.map(item => {
      cumulativeCairan += parseInt(item.cairan_masuk || 0)
      return cumulativeCairan
    })

    const urineData = monitoringList.value.map(item => {
      cumulativeUrine += parseInt(item.urine_output || 0)
      return cumulativeUrine
    })

    const perdarahanData = monitoringList.value.map(item => {
      cumulativePerdarahan += parseInt(item.perdarahan || 0)
      return cumulativePerdarahan
    })

    chartInstanceCairan = new Chart(chartCairan.value, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Cairan Masuk Kumulatif (ml)',
            data: cairanData,
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1,
            fill: true
          },
          {
            label: 'Urine Output Kumulatif (ml)',
            data: urineData,
            borderColor: 'rgb(255, 206, 86)',
            backgroundColor: 'rgba(255, 206, 86, 0.1)',
            tension: 0.1,
            fill: true
          },
          {
            label: 'Perdarahan Kumulatif (ml)',
            data: perdarahanData,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          title: {
            display: true,
            text: 'Balance Cairan (Kumulatif)'
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: 'Volume (ml)'
            }
          }
        }
      }
    })
  }
}

// Watch for changes in monitoringList to update charts
watch(monitoringList, () => {
  createCharts()
}, { deep: true })

// Watch activeTab to recreate charts when switching back to anastesi tab
watch(activeTab, (newTab) => {
  if (newTab === 'anastesi' && monitoringList.value.length > 0) {
    nextTick(() => {
      createCharts()
    })
  }
})

// Load data on mount
onMounted(() => {
  loadDokter()
  loadPetugas()
  loadMonitoringList().then(() => {
    createCharts()
  })
  fetchRiwayatLaporan()
})
</script>

<style scoped>
/* Layout sama dengan SOAP */
.operasi-layout {
  display: flex;
  height: 100vh;
  width: 100vw;
  position: fixed;
  top: 0;
  left: 0;
  background-color: #f5f7fa;
}

/* Sidebar Overlay */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 998;
  display: none;
}

.patient-sidebar {
  width: 320px;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  overflow-y: auto;
  flex-shrink: 0;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
  position: relative; /* Static on desktop */
  height: 100vh;
}

/* Desktop: Sidebar always visible */
@media (min-width: 1025px) {
  .patient-sidebar {
    position: relative;
    transform: translateX(0);
  }

  .operasi-main {
    margin-left: 0;
  }
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255,255,255,0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.sidebar-header h5 {
  color: white;
  font-weight: 600;
  margin: 0;
}

.btn-close-sidebar {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  flex-shrink: 0;
}

.btn-close-sidebar:hover {
  background: rgba(255,255,255,0.3);
  transform: rotate(90deg);
}

.sidebar-section-title {
  padding: 0.75rem 1.5rem;
  background: rgba(0,0,0,0.2);
  font-weight: 600;
  font-size: 0.875rem;
  letter-spacing: 0.5px;
}

.sidebar-content {
  padding: 1.5rem;
}

.patient-info-item {
  margin-bottom: 1.25rem;
}

.patient-info-item label {
  font-size: 0.75rem;
  opacity: 0.8;
  margin-bottom: 0.25rem;
  display: block;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.patient-info-item .info-value {
  font-size: 0.95rem;
  font-weight: 500;
}

.divider {
  height: 1px;
  background: rgba(255,255,255,0.2);
  margin: 1.5rem 0;
}

.operasi-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  width: 100%;
  margin-left: 0;
}

/* Hamburger Menu Button */
.hamburger-btn {
  background: transparent;
  border: none;
  padding: 0.75rem;
  cursor: pointer;
  display: none; /* Hidden by default on desktop */
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
  border-radius: 8px;
  flex-shrink: 0;
}

.hamburger-btn:hover {
  background: #f1f5f9;
}

.hamburger-icon {
  display: flex;
  flex-direction: column;
  gap: 4px;
  width: 24px;
}

.hamburger-icon span {
  display: block;
  height: 3px;
  background: #0891b2;
  border-radius: 2px;
  transition: all 0.3s;
}

.operasi-tabs-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  border-bottom: 2px solid #e2e8f0;
  padding-right: 1rem;
  gap: 0.5rem;
}

.operasi-tabs {
  display: flex;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  flex: 1;
  overflow-x: auto;
}

.btn-back-main {
  background: transparent;
  border: 1.5px solid #06b6d4;
  font-size: 0.95rem;
  font-weight: 500;
  color: #06b6d4;
  cursor: pointer;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: all 0.3s;
  flex-shrink: 0;
  display: flex;
  align-items: center;
}

.btn-back-main:hover {
  background: #f0fdfa;
  color: #0891b2;
  border-color: #0891b2;
  transform: translateX(-2px);
}

.btn-back-icon {
  width: 16px;
  height: 16px;
  margin-right: 0.5rem;
  object-fit: contain;
  filter: brightness(0) saturate(100%) invert(70%) sepia(90%) saturate(1500%) hue-rotate(160deg) brightness(0.95) contrast(1);
  transition: all 0.3s;
}

.btn-back-main:hover .btn-back-icon {
  filter: brightness(0) saturate(100%) invert(65%) sepia(95%) saturate(1800%) hue-rotate(160deg) brightness(0.85) contrast(1);
}

.operasi-tab {
  padding: 0.75rem 1.5rem;
  border: none;
  background: transparent;
  border-bottom: 3px solid transparent;
  border-radius: 0;
  font-weight: 500;
  color: #64748b;
  cursor: pointer;
  transition: all 0.3s;
  white-space: nowrap;
  font-size: 0.95rem;
  position: relative;
}

.operasi-tab:hover {
  background: #f8fafc;
  color: #334155;
}

.operasi-tab.active {
  background: white;
  color: #06b6d4;
  border-bottom: 3px solid #06b6d4;
  box-shadow: none;
  font-weight: 600;
  margin-bottom: -2px; /* Overlap dengan border wrapper */
}

.operasi-form-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.tab-content-operasi {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.section-title {
  color: #1e293b;
  font-weight: 600;
}

/* Scrollbar */
.patient-sidebar::-webkit-scrollbar,
.operasi-form-container::-webkit-scrollbar {
  width: 8px;
}

.patient-sidebar::-webkit-scrollbar-track {
  background: rgba(255,255,255,0.1);
}

.patient-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.3);
  border-radius: 4px;
}

.operasi-form-container::-webkit-scrollbar-track {
  background: #f5f7fa;
}

.operasi-form-container::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

/* Modal styles */
.modal.show {
  display: block;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop.show {
  opacity: 0.5;
}

/* Show overlay when sidebar is open */
.sidebar-overlay {
  display: block;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease-in-out;
}

.patient-sidebar.sidebar-open ~ .operasi-main .sidebar-overlay,
.sidebar-overlay {
  opacity: 1;
  pointer-events: auto;
}

/* Responsive Styles */

/* Tablet - 768px to 1024px */
@media (max-width: 1024px) {
  .patient-sidebar {
    width: 280px;
  }

  /* Show hamburger menu on tablet and below */
  .hamburger-btn {
    display: flex;
  }

  /* Sidebar as overlay on tablet */
  .patient-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    z-index: 999;
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
  }

  .patient-sidebar.sidebar-open {
    transform: translateX(0);
  }

  .sidebar-overlay {
    display: block;
  }

  .operasi-main {
    width: 100%;
    margin-left: 0;
  }

  .sidebar-header {
    padding: 1.25rem;
  }

  .sidebar-content {
    padding: 1.25rem;
  }

  .patient-info-item {
    margin-bottom: 1rem;
  }

  .patient-info-item .info-value {
    font-size: 0.9rem;
  }

  .operasi-tabs {
    padding: 0.75rem 1rem;
    gap: 0.25rem;
  }

  .operasi-tab {
    padding: 0.6rem 1.25rem;
    font-size: 0.875rem;
  }

  .operasi-form-container {
    padding: 1.25rem;
  }

  .tab-content-operasi {
    padding: 1.5rem;
  }

  /* Table responsive */
  .table-responsive {
    font-size: 0.85rem;
  }

  .table td,
  .table th {
    padding: 0.5rem 0.4rem;
    font-size: 0.8rem;
  }

  /* Chart containers */
  .card-body canvas {
    max-height: 250px;
  }
}

/* Mobile - max 767px (below tablet) */
@media (max-width: 767px) {
  .operasi-layout {
    flex-direction: row;
    position: relative;
  }

  /* Sidebar stays as overlay */
  .patient-sidebar {
    width: 85%;
    max-width: 320px;
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    box-shadow: 2px 0 10px rgba(0,0,0,0.2);
    z-index: 999;
    transform: translateX(-100%);
  }

  .patient-sidebar.sidebar-open {
    transform: translateX(0);
  }

  .sidebar-overlay {
    display: block;
  }

  .operasi-main {
    width: 100%;
    margin-left: 0;
  }

  .sidebar-header {
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .btn-close-sidebar {
    display: flex;
  }

  .sidebar-header h5 {
    font-size: 1rem;
  }

  .sidebar-header img {
    width: 20px !important;
    height: 20px !important;
  }

  .sidebar-section-title {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
  }

  .sidebar-content {
    padding: 1rem;
  }

  .patient-info-item {
    margin-bottom: 0.75rem;
  }

  .patient-info-item label {
    font-size: 0.7rem;
  }

  .patient-info-item .info-value {
    font-size: 0.85rem;
  }

  .patient-info-item .info-value.fs-4 {
    font-size: 1.25rem !important;
  }

  .patient-info-item .info-value.fs-5 {
    font-size: 1.1rem !important;
  }

  .divider {
    margin: 1rem 0;
  }

  /* Main content */
  .operasi-main {
    height: auto;
    flex: 1;
    overflow: visible;
  }

  .operasi-tabs-wrapper {
    padding-right: 0.5rem;
    position: sticky;
    top: 0;
    z-index: 50;
  }

  .operasi-tabs {
    padding: 0.5rem 0.75rem;
    gap: 0.25rem;
    overflow-x: auto;
    scrollbar-width: thin;
  }

  .operasi-tab {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    white-space: nowrap;
  }

  .btn-back-main {
    font-size: 0.875rem;
    padding: 0.4rem 0.8rem;
  }

  .btn-back-icon {
    width: 14px;
    height: 14px;
  }

  .operasi-form-container {
    padding: 1rem;
    overflow-y: visible;
    height: auto;
  }

  .tab-content-operasi {
    padding: 1rem;
  }

  .section-title {
    font-size: 1.1rem;
    margin-bottom: 1rem !important;
  }

  /* Form adjustments - Stack vertical di mobile */
  .row.g-3 {
    gap: 0.75rem !important;
  }

  /* Force all columns to full width on mobile */
  .row .col-md-4,
  .row .col-md-6,
  .row .col-md-3,
  .row .col-md-2 {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .form-label {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
  }

  .form-control,
  .form-select {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
  }

  textarea.form-control {
    font-size: 0.875rem;
  }

  .btn {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
  }

  .btn i {
    font-size: 0.875rem;
  }

  /* Table responsive */
  .table-responsive {
    font-size: 0.75rem;
    overflow-x: auto;
  }

  .table {
    font-size: 0.75rem;
  }

  .table td,
  .table th {
    padding: 0.4rem 0.3rem;
    font-size: 0.75rem;
    white-space: nowrap;
  }

  .table .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.7rem;
  }

  /* Card adjustments */
  .card {
    margin-bottom: 1rem !important;
  }

  .card-header {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }

  .card-header h6 {
    font-size: 0.9rem;
    margin-bottom: 0;
  }

  .card-body {
    padding: 1rem;
  }

  /* Chart containers */
  .card-body canvas {
    max-height: 200px;
  }

  .card-body .row .col-md-6 {
    margin-bottom: 1rem;
  }

  /* Modal adjustments */
  .modal-dialog.modal-fullscreen .modal-header {
    padding: 1rem;
  }

  .modal-dialog.modal-fullscreen .modal-title {
    font-size: 1rem;
  }

  .modal-dialog.modal-fullscreen .modal-body {
    padding: 1rem;
  }

  .modal-body .card {
    margin-bottom: 1rem;
  }

  .modal-body .table-responsive {
    font-size: 0.7rem;
  }

  /* Button groups */
  .d-flex.gap-2 {
    gap: 0.5rem !important;
    flex-wrap: wrap;
  }

  .d-flex.gap-2 .btn {
    flex: 1 1 auto;
    min-width: 120px;
  }
}

/* Small Mobile - max 480px */
@media (max-width: 480px) {
  .patient-sidebar {
    width: 90%;
    max-width: 280px;
  }

  .sidebar-header h5 {
    font-size: 0.9rem;
  }

  .sidebar-content {
    padding: 0.75rem;
  }

  .patient-info-item {
    margin-bottom: 0.6rem;
  }

  .patient-info-item label {
    font-size: 0.65rem;
  }

  .patient-info-item .info-value {
    font-size: 0.8rem;
  }

  .patient-info-item .info-value.fs-4 {
    font-size: 1.1rem !important;
  }

  .patient-info-item .info-value.fs-5 {
    font-size: 1rem !important;
  }

  .operasi-tabs {
    padding: 0.4rem 0.5rem;
  }

  .operasi-tab {
    padding: 0.4rem 0.75rem;
    font-size: 0.75rem;
  }

  .btn-back-main {
    font-size: 0.8rem;
    padding: 0.3rem 0.6rem;
  }

  .btn-back-icon {
    width: 12px;
    height: 12px;
  }

  .operasi-form-container {
    padding: 0.75rem;
  }

  .tab-content-operasi {
    padding: 0.75rem;
  }

  .section-title {
    font-size: 1rem;
  }

  /* Make full width on small mobile only */
  .row .col-md-4,
  .row .col-md-6,
  .row .col-md-3,
  .row .col-md-2 {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .row .col-md-12 {
    flex: 0 0 100%;
    max-width: 100%;
  }

  /* Form elements */
  .form-label {
    font-size: 0.8rem;
  }

  .form-control,
  .form-select {
    font-size: 0.8rem;
    padding: 0.4rem 0.6rem;
  }

  .btn {
    font-size: 0.8rem;
    padding: 0.45rem 0.85rem;
  }

  /* Cards */
  .card-header {
    padding: 0.6rem 0.75rem;
    font-size: 0.85rem;
  }

  .card-header h6 {
    font-size: 0.85rem;
  }

  .card-body {
    padding: 0.75rem;
  }

  /* Tables - hide less important columns */
  .table th:nth-child(n+9),
  .table td:nth-child(n+9) {
    display: none;
  }

  .table td,
  .table th {
    padding: 0.3rem 0.2rem;
    font-size: 0.7rem;
  }

  /* Chart containers */
  .card-body canvas {
    max-height: 180px;
  }

  /* Button groups stack vertical */
  .d-flex.gap-2 {
    flex-direction: column;
  }

  .d-flex.gap-2 .btn {
    width: 100%;
    min-width: unset;
  }

  /* Modal */
  .modal-dialog.modal-fullscreen .modal-header {
    padding: 0.75rem;
  }

  .modal-dialog.modal-fullscreen .modal-title {
    font-size: 0.9rem;
  }

  .modal-dialog.modal-fullscreen .modal-body {
    padding: 0.75rem;
  }
}

/* Landscape mode optimization for tablets/mobile */
@media (max-width: 1024px) and (orientation: landscape) {
  .operasi-layout {
    flex-direction: row;
  }

  .patient-sidebar {
    width: 280px;
    height: 100vh;
  }

  .patient-sidebar.sidebar-open {
    transform: translateX(0);
  }

  .operasi-main {
    height: 100vh;
    width: 100%;
  }

  .operasi-form-container {
    overflow-y: auto;
  }

  .section-title {
    font-size: 1rem;
  }

  /* Show more table columns in landscape */
  .table th:nth-child(n+9),
  .table td:nth-child(n+9) {
    display: table-cell;
  }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {
  .btn {
    min-height: 42px;
    padding: 0.6rem 1rem;
  }

  .operasi-tab {
    min-height: 42px;
    padding: 0.6rem 1.25rem;
  }

  .btn-back-main {
    min-width: 44px;
    min-height: 44px;
  }

  .form-control,
  .form-select {
    min-height: 42px;
  }

  .table .btn-sm {
    min-height: 36px;
    padding: 0.4rem 0.75rem;
  }

  /* Larger tap targets for table actions */
  .dropdown-toggle {
    min-width: 44px;
    min-height: 38px;
  }

  /* Better scrolling */
  .operasi-tabs,
  .table-responsive {
    -webkit-overflow-scrolling: touch;
  }
}

/* Custom background teal for card headers */
.bg-teal {
  background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%) !important;
}

.text-teal {
  color: #0891b2 !important;
}

/* Riwayat Laporan Section */
.riwayat-laporan-section {
  margin-top: 1.5rem;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.riwayat-laporan-section .history-title {
  background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
  color: white;
  padding: 0.75rem 1rem;
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

/* Tabel Riwayat Laporan Operasi Styles */
.table-riwayat-laporan {
  font-size: 0.8rem;
  margin-bottom: 0;
}

.table-riwayat-laporan thead th {
  background-color: #f8f9fa;
  color: #495057;
  font-weight: 500;
  white-space: nowrap;
  vertical-align: middle;
  padding: 0.5rem;
}

.table-riwayat-laporan tbody td {
  vertical-align: middle;
  padding: 0.4rem 0.5rem;
}

.table-riwayat-laporan tbody tr:hover {
  background-color: #f0fdfa;
}

.table-riwayat-laporan .btn-group-sm .btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.table-riwayat-laporan .btn-warning {
  background-color: #f59e0b;
  border-color: #f59e0b;
  color: white;
}

.table-riwayat-laporan .btn-warning:hover {
  background-color: #d97706;
  border-color: #d97706;
}

/* Print optimization */
@media print {
  .patient-sidebar,
  .operasi-tabs-wrapper,
  .btn,
  .modal-backdrop {
    display: none !important;
  }

  .operasi-layout {
    position: relative;
    height: auto;
  }

  .operasi-main {
    overflow: visible;
  }

  .operasi-form-container {
    padding: 0;
  }

  .tab-content-operasi {
    box-shadow: none;
    padding: 1rem;
  }
}
</style>
