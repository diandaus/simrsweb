<template>
  <div class="soap-layout">
    <!-- Sidebar Overlay Backdrop (Mobile Only) -->
    <div class="sidebar-backdrop" :class="{ show: isSidebarOpen }" @click="closeSidebar"></div>

    <!-- Patient Info Sidebar -->
    <aside class="patient-sidebar" :class="{ open: isSidebarOpen }">
      <div class="sidebar-header">
        <div class="d-flex align-items-center gap-2">
          <img src="/images/consultation.png" alt="Pemeriksaan" style="width: 28px; height: 28px; object-fit: contain;">
          <h5 class="mb-0">Pemeriksaan</h5>
        </div>
        <button class="btn-close-sidebar" @click="closeSidebar" title="Tutup">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="sidebar-section-title">
        <span>👤 Informasi Pasien</span>
        <span class="badge bg-info">{{ patient.png_jawab }}</span>
      </div>

      <div class="sidebar-content">
        <!-- Primary Info Card -->
        <div class="patient-card-primary">
          <div class="patient-rm">
            <i class="bi bi-card-text"></i>
            RM :<span class="rm-number">{{ patient.no_rkm_medis }}</span>
          </div>
          <div class="patient-name">{{ patient.nm_pasien }}</div>
          <div class="patient-meta">
            <span class="meta-item">
              <i class="bi bi-calendar3"></i>
              {{ patient.umur }}
            </span>
            <span class="meta-divider">•</span>
            <span class="meta-item">
              <i class="bi bi-{{ patient.jk === 'L' ? 'gender-male' : 'gender-female' }}"></i>
              {{ patient.jk === 'L' ? 'Laki-laki' : 'Perempuan' }}
            </span>
          </div>
        </div>

        <!-- Medical Info Sections -->
        <div class="medical-info-sections">
          <!-- Dokter & Poliklinik -->
          <div class="info-section">
            <div class="section-label">🏥 Pelayanan</div>
            <div class="info-group">
              <div class="info-row">
                <i class="bi bi-person-badge"></i>
                <div class="info-text">
                  <div class="info-label">Dokter</div>
                  <div class="info-value">{{ patient.nm_dokter }}</div>
                </div>
              </div>
              <div class="info-row">
                <i class="bi bi-hospital"></i>
                <div class="info-text">
                  <div class="info-label">Poliklinik</div>
                  <div class="info-value">{{ patient.nm_poli }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Rawat & Tanggal -->
          <div class="info-section">
            <div class="section-label">📋 Detail Kunjungan</div>
            <div class="info-group">
              <div class="info-row">
                <i class="bi bi-file-medical"></i>
                <div class="info-text">
                  <div class="info-label">No. Rawat</div>
                  <div class="info-value small">{{ patient.no_rawat }}</div>
                </div>
              </div>
              <div class="info-row">
                <i class="bi bi-calendar-check"></i>
                <div class="info-text">
                  <div class="info-label">Tgl Registrasi</div>
                  <div class="info-value small">
                    {{ formatTanggal(patient.tgl_registrasi) }}
                    <span class="text-muted ms-1">{{ patient.jam_reg }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Button Riwayat Perawatan -->
      <div class="sidebar-action-button">
        <button class="btn-riwayat-perawatan" @click="showRiwayatPerawatan" title="Lihat Riwayat Perawatan">
          <img src="/images/medical-record.png" alt="Riwayat Perawatan" class="btn-riwayat-icon">
          <span>Riwayat Perawatan</span>
        </button>
      </div>
    </aside>

    <!-- Main SOAP Form -->
    <main class="soap-main">
      <!-- Tab Navigation -->
      <div class="soap-tabs-wrapper">
        <!-- Hamburger Menu Button (Mobile Only) -->
        <button class="hamburger-btn" @click="toggleSidebar" title="Info Pasien">
          <span class="hamburger-icon">
            <span></span>
            <span></span>
            <span></span>
          </span>
        </button>
        <div class="soap-tabs">
          <button
            class="soap-tab"
            :class="{ active: activeInputTab === 'soap' }"
            @click="activeInputTab = 'soap'"
          >
            📋 SOAP/CPPT
          </button>
          <button
            class="soap-tab"
            :class="{ active: activeInputTab === 'resep' }"
            @click="activeInputTab = 'resep'"
          >
          <img src="/images/online-pharmacy (1).png" style="width: 20px; height: 20px; object-fit: contain;"> Resep
          </button>
          <button
            class="soap-tab"
            :class="{ active: activeInputTab === 'laboratorium' }"
            @click="activeInputTab = 'laboratorium'"
          >
            🔬 Laboratorium
          </button>
          <button
            class="soap-tab"
            :class="{ active: activeInputTab === 'radiologi' }"
            @click="activeInputTab = 'radiologi'"
          >
            📷 Radiologi
          </button>
          <button
            class="soap-tab"
            :class="{ active: activeInputTab === 'tindakan' }"
            @click="activeInputTab = 'tindakan'"
          >
            <img src="/images/stethoscope.png" style="width: 20px; height: 20px; object-fit: contain;">Tindakan
          </button>
          <button
            class="soap-tab"
            :class="{ active: activeInputTab === 'usg-ekg' }"
            @click="activeInputTab = 'usg-ekg'"
          >
            <img src="/images/usg.png" style="width: 20px; height: 20px; object-fit: contain;">USG/EKG
          </button>
        </div>
        <button @click="goBack" class="btn-back-main" title="Kembali">
          <img src="/images/return.png" alt="Kembali" class="btn-back-icon">
          Kembali
        </button>
      </div>

      <div class="soap-form-container" ref="soapFormContainer">
        <!-- Tab Content: SOAP -->
        <div v-if="activeInputTab === 'soap'" class="soap-content">
        <!-- Edit Mode Indicator -->
        <div v-if="isEditMode" class="alert alert-info mb-3">
          <strong>✏️ Mode Edit</strong> - Anda sedang mengedit SOAP yang sudah ada. Klik "Update SOAP" untuk simpan data.
        </div>
        <form @submit.prevent="submitSOAP" class="soap-grid-form">
          <!-- Left Column -->
          <div class="soap-column">
            <!-- Subjective -->
            <div class="soap-field field-large">
              <div class="field-header">
                <span class="field-badge badge-info">S</span>
                <label class="field-label">Subjective (Keluhan)</label>
              </div>
              <textarea
                v-model="form.subjective"
                class="form-control soap-textarea"
                placeholder="Keluhan yang disampaikan pasien..."
                required
              ></textarea>
            </div>

            <!-- Objective -->
            <div class="soap-field field-large">
              <div class="field-header">
                <span class="field-badge badge-warning">O</span>
                <label class="field-label">Objective (Pemeriksaan)</label>
              </div>
              <textarea
                v-model="form.objective"
                class="form-control soap-textarea"
                placeholder="Hasil pemeriksaan fisik..."
                required
              ></textarea>
            </div>

            <!-- Vital Signs -->
            <div class="vital-signs-section">
              <div class="vital-signs-grid">
                <div class="vital-item">
                  <label>TD</label>
                  <input v-model="form.tensi" type="text" class="form-control" placeholder="">
                </div>
                <div class="vital-item">
                  <label>Suhu</label>
                  <input v-model="form.suhu" type="text" class="form-control" placeholder="">
                </div>
                <div class="vital-item">
                  <label>Nadi</label>
                  <input v-model="form.nadi" type="text" class="form-control" placeholder="">
                </div>
                <div class="vital-item">
                  <label>RR</label>
                  <input v-model="form.respirasi" type="text" class="form-control" placeholder="">
                </div>
                <div class="vital-item">
                  <label>TB</label>
                  <input v-model="form.tinggi" type="text" class="form-control" placeholder="">
                </div>
                <div class="vital-item">
                  <label>BB</label>
                  <input v-model="form.berat" type="text" class="form-control" placeholder="">
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="soap-column">
            <!-- Assessment -->
            <div class="soap-field field-medium">
              <div class="field-header">
                <span class="field-badge badge-danger">A</span>
                <label class="field-label">Assessment (Diagnosis)</label>
              </div>
              <textarea
                v-model="form.assessment"
                class="form-control soap-textarea"
                placeholder="Diagnosis atau assessment..."
                required
              ></textarea>
            </div>

            <!-- Planning -->
            <div class="soap-field field-large">
              <div class="field-header">
                <span class="field-badge badge-success">P</span>
                <label class="field-label">Planning (Rencana)</label>
              </div>
              <textarea
                v-model="form.planning"
                class="form-control soap-textarea"
                placeholder="Rencana tindakan atau terapi..."
              ></textarea>
            </div>

            <!-- Evaluasi -->
            <div class="soap-field field-medium">
              <div class="field-header">
                <span class="field-badge badge-primary">E</span>
                <label class="field-label">Evaluasi</label>
              </div>
              <textarea
                v-model="form.evaluasi"
                class="form-control soap-textarea"
                placeholder="Evaluasi dan catatan lanjutan..."
              ></textarea>
            </div>

            <!-- Instruksi -->
            <div class="soap-field field-medium">
              <div class="field-header">
                <span class="field-badge badge-secondary">i</span>
                <label class="field-label">Instruksi</label>
              </div>
              <textarea
                v-model="form.instruksi"
                class="form-control soap-textarea"
                placeholder="Instruksi tambahan..."
              ></textarea>
            </div>
          </div>

          <!-- Action Buttons (Full Width) -->
          <div class="form-actions-bar">
            <button type="submit" class="btn btn-success btn-lg" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              {{ isEditMode ? '✏️ Update SOAP' : '💾 Simpan SOAP' }}
            </button>
            <button type="button" @click="clearForm" class="btn btn-warning btn-lg">
              🗑️ Clear
            </button>
            <button type="button" @click="showRiwayatSOAPIE" class="btn btn-info btn-lg">
              📋 Riwayat SOAPIE
            </button>
            <button type="button" @click="showRujukInternalModal" class="btn btn-primary btn-lg">
              🏥 Rujuk Internal
            </button>
            <button type="button" @click="goBack" class="btn btn-secondary btn-lg">
              ← Kembali
            </button>
          </div>

          <!-- Alert Error -->
          <div v-if="error" class="alert alert-danger mt-3">
            {{ error }}
          </div>
        </form>

        <!-- SOAP History / Rincian Riwayat -->
        <div class="soap-history-section" v-if="soapHistory.length > 0">
          <h5 class="history-title">📋 Rincian Riwayat</h5>
          <div class="table-responsive">
            <table class="table table-bordered soap-history-table">
              <thead>
                <tr>
                  <th rowspan="2" class="align-middle">No</th>
                  <th rowspan="2" class="align-middle">Tanggal</th>
                  <th>Suhu(C)</th>
                  <th>Tensi (mmHg)</th>
                  <th>Nadi(/menit)</th>
                  <th>RR(/menit)</th>
                  <th>Tinggi(cm)</th>
                  <th>Berat(kg)</th>
                  <th>GCS(E,V,M)</th>
                  <th>SPO2</th>
                  <th>Alergi</th>
                  <th rowspan="2" class="align-middle">Instruksi & Evaluasi</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="(item, index) in soapHistory" :key="index">
                  <tr>
                    <td rowspan="6" class="td-no align-top">{{ index + 1 }}</td>
                    <td rowspan="6" class="td-tanggal">
                      <div class="tanggal-content">
                        <div class="tanggal-info">
                          <strong>{{ item.no_rawat }}</strong><br>
                          {{ formatDateTime(item.tgl_perawatan, item.jam_rawat) }}<br>
                          <small class="text-muted">{{ item.nm_unit || 'UNIT' }}</small>
                        </div>
                        <div class="tanggal-actions">
                          <button
                            type="button"
                            class="btn-icon btn-edit"
                            title="Edit"
                            @click="editSOAP(item)"
                          >
                            ✏️
                          </button>
                          <button
                            type="button"
                            class="btn-icon btn-delete"
                            title="Hapus"
                            @click="deleteSOAP(item)"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                              <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                            </svg>
                          </button>
                        </div>
                      </div>
                    </td>
                    <!-- Vital Signs Row -->
                    <td class="text-center">{{ item.suhu_tubuh || '-' }}</td>
                    <td class="text-center">{{ item.tensi || '-' }}</td>
                    <td class="text-center">{{ item.nadi || '-' }}</td>
                    <td class="text-center">{{ item.respirasi || '-' }}</td>
                    <td class="text-center">{{ item.tinggi || '-' }}</td>
                    <td class="text-center">{{ item.berat || '-' }}</td>
                    <td class="text-center">{{ item.gcs || '-' }}</td>
                    <td class="text-center">{{ item.spo2 || '-' }}</td>
                    <td class="text-center">{{ item.alergi || '-' }}</td>
                    <td rowspan="6" class="td-instruksi align-top">
                      <div v-if="item.instruksi">
                        <strong>Instruksi:</strong> {{ item.instruksi }}
                      </div>
                      <div v-if="item.evaluasi" :class="{ 'mt-2': item.instruksi }">
                        <strong>Evaluasi:</strong> {{ item.evaluasi }}
                      </div>
                    </td>
                  </tr>
                  <!-- Kesadaran Row -->
                  <tr>
                    <td class="soap-label-cell">Kesadaran</td>
                    <td colspan="9" class="soap-value-cell">{{ item.kesadaran || 'Compos Mentis' }}</td>
                  </tr>
                  <!-- Subjective Row -->
                  <tr>
                    <td class="soap-label-cell">Subjective</td>
                    <td colspan="9" class="soap-value-cell">{{ item.keluhan || '-' }}</td>
                  </tr>
                  <!-- Objective Row -->
                  <tr>
                    <td class="soap-label-cell">Objective</td>
                    <td colspan="9" class="soap-value-cell">{{ item.pemeriksaan || '-' }}</td>
                  </tr>
                  <!-- Assesment Row -->
                  <tr>
                    <td class="soap-label-cell">Assesment</td>
                    <td colspan="9" class="soap-value-cell">{{ item.penilaian || '-' }}</td>
                  </tr>
                  <!-- Plan Row -->
                  <tr>
                    <td class="soap-label-cell">Plan</td>
                    <td colspan="9" class="soap-value-cell">{{ item.rtl || '-' }}</td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
        </div>

        <!-- Tab Content: Resep -->
        <div v-if="activeInputTab === 'resep'" class="resep-content">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="history-title">📋 Permintaan Resep Hari Ini</h5>
            <div class="d-flex gap-2">
              <button @click="showModalResep = true" class="btn btn-sm btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Input Resep
              </button>
              <button @click="loadRiwayatResepTab" class="btn btn-sm btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                  <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                  <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                </svg>
                Refresh
              </button>
            </div>
          </div>

          <div v-if="loadingRiwayatResep" class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>

          <div v-else-if="riwayatResep.length === 0" class="alert alert-info">
            Belum ada permintaan resep hari ini
          </div>

          <div v-else>
            <div v-for="(resep, index) in riwayatResep" :key="index" class="resep-card mb-3">
                <!-- Resep Header -->
                <div class="resep-header-card">
                  <div class="row align-items-center">
                    <div class="col-md-3">
                      <div class="resep-meta">
                        <strong>No. Resep:</strong> {{ resep.no_resep }}
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="resep-meta">
                        <strong>Tanggal:</strong> {{ formatDateTime(resep.tgl_peresepan, resep.jam_peresepan) }}
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="resep-meta">
                        <strong>Dokter:</strong> {{ resep.nm_dokter }}
                      </div>
                    </div>
                    <div class="col-md-3 text-end">
                      <span :class="resep.status === 'Sudah Terlayani' ? 'badge bg-success' : 'badge bg-warning'">
                        {{ resep.status }}
                      </span>
                      <span class="badge bg-info ms-1">{{ resep.status_asal }}</span>
                    </div>
                  </div>
                </div>

                <!-- Non Racikan -->
                <div v-if="resep.non_racikan && resep.non_racikan.length > 0" class="resep-body">
                  <h6 class="resep-subtitle">💊 Obat Non Racikan</h6>
                  <table class="table table-sm table-bordered mb-0">
                    <thead>
                      <tr>
                        <th>Kode</th>
                        <th>Nama Obat</th>
                        <th>Jumlah</th>
                        <th>Aturan Pakai</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(obat, idx) in resep.non_racikan" :key="idx">
                        <td>{{ obat.kode_brng }}</td>
                        <td>{{ obat.nama_brng }}</td>
                        <td>{{ obat.jml }} {{ obat.kode_sat }}</td>
                        <td>{{ obat.aturan_pakai }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Racikan -->
                <div v-if="resep.racikan && resep.racikan.length > 0" class="resep-body">
                  <h6 class="resep-subtitle">🧪 Obat Racikan</h6>
                  <div v-for="(racik, ridx) in resep.racikan" :key="ridx" class="racikan-item mb-3">
                    <div class="racikan-header">
                      <strong>No. Racik {{ racik.no_racik }}:</strong> {{ racik.nama_racik }}
                      <span class="badge bg-secondary ms-2">{{ racik.metode }}</span>
                      <span class="ms-2">Jumlah: {{ racik.jml_dr }}</span>
                      <span class="ms-2">Aturan: {{ racik.aturan_pakai }}</span>
                    </div>
                    <table class="table table-sm table-bordered mb-0 mt-2">
                      <thead>
                        <tr>
                          <th>Kode</th>
                          <th>Nama Obat</th>
                          <th>Jumlah</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(detail, didx) in racik.detail" :key="didx">
                          <td>{{ detail.kode_brng }}</td>
                          <td>{{ detail.nama_brng }}</td>
                          <td>{{ detail.jml }} {{ detail.kode_sat }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
        </div>

        <!-- Tab Content: Laboratorium -->
        <div v-if="activeInputTab === 'laboratorium'">
          <div class="lab-form-container">
            <!-- Form Input - Compact Row -->
            <div class="row g-2 mb-3">
              <!-- No Permintaan Lab -->
              <div class="col-md-2">
                <label class="form-label fw-bold small">No. Permintaan</label>
                <input
                  type="text"
                  class="form-control form-control-sm"
                  v-model="labForm.noorder"
                  readonly
                  placeholder="Auto"
                >
              </div>

              <!-- Indikasi/Klinis -->
              <div class="col-md-5">
                <label class="form-label fw-bold small">Indikasi/Klinis <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control form-control-sm"
                  v-model="labForm.diagnosa_klinis"
                  placeholder="Masukkan indikasi atau diagnosa klinis..."
                >
              </div>

              <!-- Informasi Tambahan -->
              <div class="col-md-5">
                <label class="form-label fw-bold small">Informasi Tambahan</label>
                <input
                  type="text"
                  class="form-control form-control-sm"
                  v-model="labForm.informasi_tambahan"
                  placeholder="Informasi tambahan (opsional)..."
                >
              </div>
            </div>

            <!-- Tab Jenis Lab -->
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeLabTab === 'pk' }"
                  @click="activeLabTab = 'pk'"
                  href="javascript:void(0)"
                >
                  🔬 Permintaan Lab PK
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeLabTab === 'pa' }"
                  @click="activeLabTab = 'pa'"
                  href="javascript:void(0)"
                >
                  🧪 Permintaan Lab PA
                </a>
              </li>
            </ul>

            <!-- Tab Content PK -->
            <div v-if="activeLabTab === 'pk'" class="lab-tab-content">
              <!-- Search Pemeriksaan -->
              <div class="mb-3">
                <div class="input-group">
                  <input
                    type="text"
                    class="form-control"
                    v-model="searchLabPK"
                    @input="searchPemeriksaanPK"
                    placeholder="Cari nama pemeriksaan Lab PK..."
                  >
                  <button class="btn btn-primary" @click="searchPemeriksaanPK">
                    <i class="bi bi-search"></i> Cari
                  </button>
                </div>
              </div>

              <!-- Daftar Pemeriksaan PK -->
              <div class="pemeriksaan-list">
                <div v-if="loadingLabPK" class="text-center p-3">
                  <div class="spinner-border spinner-border-sm text-primary"></div>
                  <span class="ms-2">Memuat data...</span>
                </div>
                <div v-else-if="pemeriksaanPKList.length === 0" class="text-center p-3 text-muted">
                  <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                  Tidak ada data pemeriksaan
                </div>
                <div v-else class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                  <table class="table table-hover table-sm">
                    <thead class="sticky-top bg-light">
                      <tr>
                        <th width="50px">
                          <input
                            type="checkbox"
                            @change="toggleAllPK"
                            :checked="isAllPKSelected"
                          >
                        </th>
                        <th>Kode</th>
                        <th>Nama Pemeriksaan</th>
                        <th>Kategori</th>
                        <th class="text-end">Biaya</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="item in pemeriksaanPKList"
                        :key="item.kd_jenis_prw"
                        @click="togglePemeriksaanPK(item)"
                        style="cursor: pointer;"
                        :class="{ 'table-active': selectedPemeriksaanPK.some(p => p.kd_jenis_prw === item.kd_jenis_prw) }"
                      >
                        <td @click.stop>
                          <input
                            type="checkbox"
                            v-model="selectedPemeriksaanPK"
                            :value="item"
                          >
                        </td>
                        <td><small>{{ item.kd_jenis_prw }}</small></td>
                        <td>{{ item.nm_perawatan }}</td>
                        <td><small class="text-muted">{{ item.kategori || '-' }}</small></td>
                        <td class="text-end"><small>{{ formatRupiah(item.total_byr) }}</small></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Selected Items Summary -->
                <div v-if="selectedPemeriksaanPK.length > 0" class="mt-3 p-3 bg-light rounded">
                  <strong>Pemeriksaan Terpilih ({{ selectedPemeriksaanPK.length }}):</strong>
                  <div class="d-flex flex-wrap gap-2 mt-2">
                    <span
                      v-for="(item, idx) in selectedPemeriksaanPK"
                      :key="idx"
                      class="badge bg-primary"
                    >
                      {{ item.nm_perawatan }}
                      <i
                        class="bi bi-x-circle ms-1"
                        style="cursor: pointer;"
                        @click="removeSelectedPK(item)"
                      ></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab Content PA -->
            <div v-if="activeLabTab === 'pa'" class="lab-tab-content">
              <!-- Search Pemeriksaan -->
              <div class="mb-3">
                <div class="input-group">
                  <input
                    type="text"
                    class="form-control"
                    v-model="searchLabPA"
                    @input="searchPemeriksaanPA"
                    placeholder="Cari nama pemeriksaan Lab PA..."
                  >
                  <button class="btn btn-primary" @click="searchPemeriksaanPA">
                    <i class="bi bi-search"></i> Cari
                  </button>
                </div>
              </div>

              <!-- Daftar Pemeriksaan PA -->
              <div class="pemeriksaan-list">
                <div v-if="loadingLabPA" class="text-center p-3">
                  <div class="spinner-border spinner-border-sm text-primary"></div>
                  <span class="ms-2">Memuat data...</span>
                </div>
                <div v-else-if="pemeriksaanPAList.length === 0" class="text-center p-3 text-muted">
                  <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                  Tidak ada data pemeriksaan
                </div>
                <div v-else class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                  <table class="table table-hover table-sm">
                    <thead class="sticky-top bg-light">
                      <tr>
                        <th width="50px">
                          <input
                            type="checkbox"
                            @change="toggleAllPA"
                            :checked="isAllPASelected"
                          >
                        </th>
                        <th>Kode</th>
                        <th>Nama Pemeriksaan</th>
                        <th>Kategori</th>
                        <th class="text-end">Biaya</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="item in pemeriksaanPAList"
                        :key="item.kd_jenis_prw"
                        @click="togglePemeriksaanPA(item)"
                        style="cursor: pointer;"
                        :class="{ 'table-active': selectedPemeriksaanPA.some(p => p.kd_jenis_prw === item.kd_jenis_prw) }"
                      >
                        <td @click.stop>
                          <input
                            type="checkbox"
                            v-model="selectedPemeriksaanPA"
                            :value="item"
                          >
                        </td>
                        <td><small>{{ item.kd_jenis_prw }}</small></td>
                        <td>{{ item.nm_perawatan }}</td>
                        <td><small class="text-muted">{{ item.kategori || '-' }}</small></td>
                        <td class="text-end"><small>{{ formatRupiah(item.total_byr) }}</small></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Selected Items Summary -->
                <div v-if="selectedPemeriksaanPA.length > 0" class="mt-3 p-3 bg-light rounded">
                  <strong>Pemeriksaan Terpilih ({{ selectedPemeriksaanPA.length }}):</strong>
                  <div class="d-flex flex-wrap gap-2 mt-2">
                    <span
                      v-for="(item, idx) in selectedPemeriksaanPA"
                      :key="idx"
                      class="badge bg-primary"
                    >
                      {{ item.nm_perawatan }}
                      <i
                        class="bi bi-x-circle ms-1"
                        style="cursor: pointer;"
                        @click="removeSelectedPA(item)"
                      ></i>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 d-flex gap-2">
              <button
                class="btn btn-success"
                @click="submitPermintaanLab"
                :disabled="loadingSubmitLab"
              >
                <span v-if="loadingSubmitLab" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-save me-2"></i>
                Simpan Permintaan Lab
              </button>
              <button
                class="btn btn-secondary"
                @click="clearLabForm"
              >
                <i class="bi bi-arrow-clockwise me-2"></i>
                Reset
              </button>
            </div>
          </div>

          <!-- Riwayat Permintaan Lab - Separated Section -->
          <div class="lab-history-section" v-if="riwayatLabPKList.length > 0 || riwayatLabPAList.length > 0 || loadingRiwayatPK || loadingRiwayatPA">
            <h5 class="history-title">📋 Riwayat Permintaan Laboratorium</h5>

            <!-- Tab Riwayat -->
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeRiwayatLabTab === 'pk' }"
                  @click="activeRiwayatLabTab = 'pk'; loadRiwayatLabPK()"
                  href="javascript:void(0)"
                >
                  🔬 Riwayat Lab PK
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeRiwayatLabTab === 'pa' }"
                  @click="activeRiwayatLabTab = 'pa'; loadRiwayatLabPA()"
                  href="javascript:void(0)"
                >
                  🧪 Riwayat Lab PA
                </a>
              </li>
            </ul>

            <!-- Tab Content Riwayat PK -->
            <div v-if="activeRiwayatLabTab === 'pk'">
              <div v-if="loadingRiwayatPK" class="text-center p-3">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2">Memuat riwayat...</span>
              </div>
              <div v-else-if="groupedRiwayatLabPK.length === 0" class="text-center p-3 text-muted">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                Belum ada riwayat permintaan Lab PK
              </div>
              <div v-else>
                <!-- Loop through each permintaan -->
                <div v-for="(permintaan, permIdx) in groupedRiwayatLabPK" :key="permIdx" class="lab-card mb-3">
                  <div class="lab-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <strong>{{ permintaan.noorder }}</strong>
                      </div>
                      <span
                        class="badge"
                        :class="{
                          'bg-warning': !permintaan.jam_sampel,
                          'bg-primary': permintaan.jam_sampel && !permintaan.jam_hasil,
                          'bg-success': permintaan.jam_hasil
                        }"
                      >
                        {{ permintaan.jam_hasil ? 'Selesai' : (permintaan.jam_sampel ? 'Dalam Proses' : 'Menunggu Sampel') }}
                      </span>
                    </div>
                  </div>

                  <div class="lab-card-body">
                    <!-- Pasien Info Table -->
                    <table class="table table-sm table-bordered mb-3">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 120px;">Tgl Permintaan</th>
                          <th style="width: 100px;">Jam</th>
                          <th style="width: 100px;">Tgl Sampel</th>
                          <th style="width: 100px;">Jam</th>
                          <th style="width: 100px;">Tgl Hasil</th>
                          <th style="width: 100px;">Jam</th>
                          <th style="width: 150px;">Dokter Perujuk</th>
                          <th style="width: 100px;">Poli</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>{{ permintaan.tgl_permintaan }}</td>
                          <td>{{ permintaan.jam_permintaan }}</td>
                          <td>{{ permintaan.tgl_sampel || '-' }}</td>
                          <td>{{ permintaan.jam_sampel || '-' }}</td>
                          <td>{{ permintaan.tgl_hasil || '-' }}</td>
                          <td>{{ permintaan.jam_hasil || '-' }}</td>
                          <td>{{ permintaan.dokter_perujuk }}</td>
                          <td>{{ permintaan.nm_poli }}</td>
                        </tr>
                      </tbody>
                    </table>

                    <!-- Item Pemeriksaan -->
                    <div class="mb-2">
                      <strong>Item Pemeriksaan:</strong>
                    </div>
                    <table class="table table-sm table-bordered table-hover">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 50px;">No</th>
                          <th>Jenis Pemeriksaan</th>
                          <th style="width: 100px;">Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <template v-for="(item, itemIdx) in permintaan.items" :key="itemIdx">
                          <tr>
                            <td class="text-center">{{ itemIdx + 1 }}</td>
                            <td><strong>{{ item.nm_perawatan }}</strong></td>
                            <td class="text-center">
                              <button
                                class="btn btn-sm btn-outline-primary"
                                @click="toggleLabDetail(permIdx, itemIdx)"
                              >
                                {{ item.showDetails ? '▲ Sembunyikan' : '▼ Detail' }}
                              </button>
                            </td>
                          </tr>

                          <!-- Detail Row -->
                          <tr v-if="item.showDetails" class="lab-detail-row">
                            <td colspan="3" class="p-3">
                              <div v-if="item.details && item.details.length > 0">
                                <table class="table table-sm table-bordered parameter-table">
                                  <thead class="table-secondary">
                                    <tr>
                                      <th>Parameter</th>
                                      <th style="width: 80px;">Satuan</th>
                                      <th style="width: 100px;">Nilai Rujukan L.D.</th>
                                      <th style="width: 100px;">Nilai Rujukan L.A.</th>
                                      <th style="width: 100px;">Nilai Rujukan P.D.</th>
                                      <th style="width: 100px;">Nilai Rujukan P.A.</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr v-for="(detail, detailIdx) in item.details" :key="detailIdx">
                                      <td>{{ detail.pemeriksaan }}</td>
                                      <td class="text-center">{{ detail.satuan }}</td>
                                      <td class="text-center">{{ detail.nilai_rujukan_ld || '-' }}</td>
                                      <td class="text-center">{{ detail.nilai_rujukan_la || '-' }}</td>
                                      <td class="text-center">{{ detail.nilai_rujukan_pd || '-' }}</td>
                                      <td class="text-center">{{ detail.nilai_rujukan_pa || '-' }}</td>
                                    </tr>
                                  </tbody>
                                </table>
                              </div>
                              <div v-else class="text-muted text-center py-3">
                                Belum ada data parameter untuk pemeriksaan ini
                              </div>
                            </td>
                          </tr>
                        </template>
                      </tbody>
                    </table>

                    <!-- Info Tambahan -->
                    <div class="row mt-3">
                      <div class="col-md-6">
                        <div class="info-box-small">
                          <div class="info-label-small">Diagnosis Klinis:</div>
                          <div class="info-value-small">{{ permintaan.diagnosa_klinis || '-' }}</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="info-box-small">
                          <div class="info-label-small">Informasi Tambahan:</div>
                          <div class="info-value-small">{{ permintaan.informasi_tambahan || '-' }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab Content Riwayat PA -->
            <div v-if="activeRiwayatLabTab === 'pa'">
              <div v-if="loadingRiwayatPA" class="text-center p-3">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2">Memuat riwayat...</span>
              </div>
              <div v-else-if="groupedRiwayatLabPA.length === 0" class="text-center p-3 text-muted">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                Belum ada riwayat permintaan Lab PA
              </div>
              <div v-else>
                <!-- Loop through each permintaan -->
                <div v-for="(permintaan, permIdx) in groupedRiwayatLabPA" :key="permIdx" class="lab-card mb-3">
                  <div class="lab-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <strong>{{ permintaan.noorder }}</strong>
                        <span class="ms-3 text-muted">{{ permintaan.no_rawat }}</span>
                      </div>
                      <span
                        class="badge"
                        :class="{
                          'bg-warning': !permintaan.jam_sampel,
                          'bg-primary': permintaan.jam_sampel && !permintaan.jam_hasil,
                          'bg-success': permintaan.jam_hasil
                        }"
                      >
                        {{ permintaan.jam_hasil ? 'Selesai' : (permintaan.jam_sampel ? 'Dalam Proses' : 'Menunggu Sampel') }}
                      </span>
                    </div>
                  </div>

                  <div class="lab-card-body">
                    <!-- Pasien Info Table -->
                    <table class="table table-sm table-bordered mb-3">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 120px;">Tgl Permintaan</th>
                          <th style="width: 100px;">Jam</th>
                          <th style="width: 100px;">Tgl Sampel</th>
                          <th style="width: 100px;">Jam</th>
                          <th style="width: 100px;">Tgl Hasil</th>
                          <th style="width: 100px;">Jam</th>
                          <th style="width: 150px;">Dokter Perujuk</th>
                          <th style="width: 100px;">Poli</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>{{ permintaan.tgl_permintaan }}</td>
                          <td>{{ permintaan.jam_permintaan }}</td>
                          <td>{{ permintaan.tgl_sampel || '-' }}</td>
                          <td>{{ permintaan.jam_sampel || '-' }}</td>
                          <td>{{ permintaan.tgl_hasil || '-' }}</td>
                          <td>{{ permintaan.jam_hasil || '-' }}</td>
                          <td>{{ permintaan.dokter_perujuk }}</td>
                          <td>{{ permintaan.nm_poli }}</td>
                        </tr>
                      </tbody>
                    </table>

                    <!-- Item Pemeriksaan -->
                    <div class="mb-2">
                      <strong>Item Pemeriksaan:</strong>
                    </div>
                    <table class="table table-sm table-bordered table-hover">
                      <thead class="table-light">
                        <tr>
                          <th style="width: 50px;">No</th>
                          <th>Jenis Pemeriksaan</th>
                          <th style="width: 100px;">Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <template v-for="(item, itemIdx) in permintaan.items" :key="itemIdx">
                          <tr>
                            <td class="text-center">{{ itemIdx + 1 }}</td>
                            <td><strong>{{ item.nm_perawatan }}</strong></td>
                            <td class="text-center">
                              <button
                                class="btn btn-sm btn-outline-primary"
                                @click="toggleLabDetailPA(permIdx, itemIdx)"
                              >
                                {{ item.showDetails ? '▲ Sembunyikan' : '▼ Detail' }}
                              </button>
                            </td>
                          </tr>

                          <!-- Detail Row -->
                          <tr v-if="item.showDetails" class="lab-detail-row">
                            <td colspan="3" class="p-3">
                              <div v-if="item.details && item.details.length > 0">
                                <table class="table table-sm table-bordered parameter-table">
                                  <thead class="table-secondary">
                                    <tr>
                                      <th>Pemeriksaan</th>
                                      <th style="width: 100px;">Nilai Rujukan L.D.</th>
                                      <th style="width: 100px;">Nilai Rujukan L.A.</th>
                                      <th style="width: 100px;">Nilai Rujukan P.D.</th>
                                      <th style="width: 100px;">Nilai Rujukan P.A.</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr v-for="(detail, detailIdx) in item.details" :key="detailIdx">
                                      <td>{{ detail.nm_perawatan }}</td>
                                      <td class="text-center">{{ detail.nilai_rujukan_ld || '-' }}</td>
                                      <td class="text-center">{{ detail.nilai_rujukan_la || '-' }}</td>
                                      <td class="text-center">{{ detail.nilai_rujukan_pd || '-' }}</td>
                                      <td class="text-center">{{ detail.nilai_rujukan_pa || '-' }}</td>
                                    </tr>
                                  </tbody>
                                </table>
                              </div>
                              <div v-else class="text-muted text-center py-3">
                                Belum ada data parameter untuk pemeriksaan ini
                              </div>
                            </td>
                          </tr>
                        </template>
                      </tbody>
                    </table>

                    <!-- Info Tambahan -->
                    <div class="row mt-3">
                      <div class="col-md-6">
                        <div class="info-box-small">
                          <div class="info-label-small">Diagnosis Klinis:</div>
                          <div class="info-value-small">{{ permintaan.diagnosa_klinis || '-' }}</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="info-box-small">
                          <div class="info-label-small">Informasi Tambahan:</div>
                          <div class="info-value-small">{{ permintaan.informasi_tambahan || '-' }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab Content: Radiologi -->
        <div v-if="activeInputTab === 'radiologi'">
          <div class="rad-form-container">
            <h5 class="form-title mb-3">📷 Form Permintaan Radiologi</h5>

            <!-- Form Input -->
            <div class="row mb-3">
              <div class="col-md-2">
                <label class="form-label fw-bold small">No. Permintaan</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="radForm.noorder"
                  readonly
                  placeholder="Auto Generate"
                />
              </div>
              <div class="col-md-5">
                <label class="form-label fw-bold small">Informasi Tambahan Permintaan Foto</label>
                <textarea
                  class="form-control"
                  rows="1"
                  v-model="radForm.informasi_tambahan"
                  placeholder="Masukkan informasi tambahan..."
                ></textarea>
              </div>
              <div class="col-md-5">
                <label class="form-label fw-bold small">Indikasi Pemeriksaan / Diagnosis Klinis</label>
                <textarea
                  class="form-control"
                  rows="1"
                  v-model="radForm.diagnosa_klinis"
                  placeholder="Masukkan diagnosis klinis..."
                ></textarea>
              </div>
            </div>

            <!-- Search Pemeriksaan Radiologi -->
            <div class="mb-3">
              <label class="form-label fw-bold">Cari Pemeriksaan Radiologi</label>
              <div class="input-group">
                <span class="input-group-text">🔍</span>
                <input
                  type="text"
                  class="form-control"
                  v-model="searchRad"
                  @input="searchPemeriksaanRad"
                  placeholder="Cari berdasarkan kode atau nama pemeriksaan..."
                />
              </div>
            </div>

            <!-- Tabel Pemeriksaan Radiologi -->
            <div v-if="pemeriksaanRadList.length > 0" class="pemeriksaan-list mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Daftar Pemeriksaan Radiologi</h6>
                <small class="text-muted">{{ pemeriksaanRadList.length }} item ditemukan</small>
              </div>
              <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                <table class="table table-sm table-hover table-bordered">
                  <thead class="table-light sticky-top">
                    <tr>
                      <th style="width: 50px;" class="text-center">P</th>
                      <th style="width: 120px;">Kode Periksa</th>
                      <th>Nama Pemeriksaan</th>
                      <th style="width: 120px;" class="text-end">Biaya</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(item, idx) in pemeriksaanRadList"
                      :key="idx"
                      @click="toggleSelectRad(item)"
                      :class="{ 'table-primary': isSelectedRad(item) }"
                      style="cursor: pointer;"
                    >
                      <td class="text-center">
                        <input
                          type="checkbox"
                          :checked="isSelectedRad(item)"
                          @change="toggleSelectRad(item)"
                        />
                      </td>
                      <td>{{ item.kd_jenis_prw }}</td>
                      <td>{{ item.nm_perawatan }}</td>
                      <td class="text-end">{{ formatRupiah(item.total_byr) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Pemeriksaan Terpilih -->
            <div v-if="selectedPemeriksaanRad.length > 0" class="selected-pemeriksaan mb-3">
              <h6 class="mb-2">Pemeriksaan Terpilih ({{ selectedPemeriksaanRad.length }} item)</h6>
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead class="table-success">
                    <tr>
                      <th style="width: 50px;">No</th>
                      <th style="width: 120px;">Kode</th>
                      <th>Nama Pemeriksaan</th>
                      <th style="width: 120px;" class="text-end">Biaya</th>
                      <th style="width: 60px;" class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, idx) in selectedPemeriksaanRad" :key="idx">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ item.kd_jenis_prw }}</td>
                      <td>{{ item.nm_perawatan }}</td>
                      <td class="text-end">{{ formatRupiah(item.total_byr) }}</td>
                      <td class="text-center">
                        <button
                          class="btn btn-sm btn-danger"
                          @click="removeSelectedRad(idx)"
                          title="Hapus"
                        >
                          ✕
                        </button>
                      </td>
                    </tr>
                    <tr class="table-light fw-bold">
                      <td colspan="3" class="text-end">Total Biaya:</td>
                      <td class="text-end">{{ formatRupiah(totalBiayaRad) }}</td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 justify-content-end">
              <button
                class="btn btn-secondary"
                @click="clearRadForm"
                :disabled="loadingSubmitRad"
              >
                Reset
              </button>
              <button
                class="btn btn-primary"
                @click="submitPermintaanRad"
                :disabled="selectedPemeriksaanRad.length === 0 || loadingSubmitRad"
              >
                <span v-if="loadingSubmitRad" class="spinner-border spinner-border-sm me-2"></span>
                {{ loadingSubmitRad ? 'Menyimpan...' : 'Simpan Permintaan' }}
              </button>
            </div>
          </div>

          <!-- Riwayat Permintaan Radiologi -->
          <div class="rad-history-section" v-if="riwayatRadList.length > 0 || loadingRiwayatRad">
            <h5 class="history-title">📋 Riwayat Permintaan Radiologi</h5>

            <div v-if="loadingRiwayatRad" class="text-center p-3">
              <div class="spinner-border spinner-border-sm text-primary"></div>
              <span class="ms-2">Memuat riwayat...</span>
            </div>

            <div v-else-if="riwayatRadList.length === 0" class="text-center p-3 text-muted">
              <i class="bi bi-inbox fs-4 d-block mb-2"></i>
              Belum ada riwayat permintaan radiologi
            </div>

            <div v-else>
              <!-- Loop through each permintaan -->
              <div v-for="(permintaan, permIdx) in riwayatRadList" :key="permIdx" class="rad-card mb-3">
                <div class="rad-card-header">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong>{{ permintaan.noorder }}</strong>
                      <span class="ms-3 text-muted">{{ permintaan.no_rawat }}</span>
                    </div>
                    <span
                      class="badge"
                      :class="{
                        'bg-warning': !permintaan.jam_sampel,
                        'bg-primary': permintaan.jam_sampel && !permintaan.jam_hasil,
                        'bg-success': permintaan.jam_hasil
                      }"
                    >
                      {{ permintaan.jam_hasil ? 'Selesai' : (permintaan.jam_sampel ? 'Dalam Proses' : 'Menunggu Sampel') }}
                    </span>
                  </div>
                </div>

                <div class="rad-card-body">
                  <!-- Pasien Info Table -->
                  <table class="table table-sm table-bordered mb-3">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 120px;">Tgl Permintaan</th>
                        <th style="width: 100px;">Jam</th>
                        <th style="width: 100px;">Tgl Sampel</th>
                        <th style="width: 100px;">Jam</th>
                        <th style="width: 100px;">Tgl Hasil</th>
                        <th style="width: 100px;">Jam</th>
                        <th style="width: 150px;">Dokter Perujuk</th>
                        <th style="width: 100px;">Jenis Bayar</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>{{ permintaan.tgl_permintaan }}</td>
                        <td>{{ permintaan.jam_permintaan }}</td>
                        <td>{{ permintaan.tgl_sampel || '-' }}</td>
                        <td>{{ permintaan.jam_sampel || '-' }}</td>
                        <td>{{ permintaan.tgl_hasil || '-' }}</td>
                        <td>{{ permintaan.jam_hasil || '-' }}</td>
                        <td>{{ permintaan.dokter_perujuk }}</td>
                        <td>{{ permintaan.png_jawab || '-' }}</td>
                      </tr>
                    </tbody>
                  </table>

                  <!-- Item Pemeriksaan -->
                  <div class="mb-2">
                    <strong>Item Pemeriksaan:</strong>
                  </div>
                  <table class="table table-sm table-bordered table-hover">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 50px;">No</th>
                        <th>Jenis Pemeriksaan</th>
                        <th style="width: 100px;">Status Bayar</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, itemIdx) in permintaan.pemeriksaan" :key="itemIdx">
                        <td class="text-center">{{ itemIdx + 1 }}</td>
                        <td><strong>{{ item.nm_perawatan }}</strong></td>
                        <td class="text-center">
                          <span class="badge" :class="item.stts_bayar === 'Sudah' ? 'bg-success' : 'bg-warning'">
                            {{ item.stts_bayar }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  <!-- Info Tambahan -->
                  <div class="row mt-3">
                    <div class="col-md-6">
                      <div class="info-box-small">
                        <div class="info-label-small">Diagnosis Klinis:</div>
                        <div class="info-value-small">{{ permintaan.diagnosa_klinis || '-' }}</div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-box-small">
                        <div class="info-label-small">Informasi Tambahan:</div>
                        <div class="info-value-small">{{ permintaan.informasi_tambahan || '-' }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab Content: Tindakan -->
        <div v-if="activeInputTab === 'tindakan'">
          <div class="tindakan-form-container">
            <!-- Form Input Tindakan -->
            <div class="tindakan-input-section mb-4">
              <h6 class="section-title mb-3">
                <i class="bi bi-clipboard-plus me-2"></i>Form Tindakan Medis
              </h6>

              <!-- Search Tindakan -->
              <div class="mb-3">
                <label class="form-label fw-bold">Cari Tindakan Medis</label>
                <div class="input-group">
                  <span class="input-group-text">🔍</span>
                  <input
                    type="text"
                    class="form-control"
                    v-model="searchTindakan"
                    @input="searchJenisTindakan"
                    placeholder="Cari berdasarkan kode atau nama tindakan..."
                  />
                </div>
              </div>

              <!-- Tabel Tindakan -->
              <div v-if="jenisTindakanList.length > 0" class="pemeriksaan-list mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="mb-0">Daftar Tindakan Medis</h6>
                  <small class="text-muted">{{ jenisTindakanList.length }} item ditemukan</small>
                </div>
                <div class="table-responsive" style="max-height: 150px; overflow-y: auto;">
                  <table class="table table-sm table-hover table-bordered">
                    <thead class="table-light sticky-top">
                      <tr>
                        <th style="width: 50px;" class="text-center">P</th>
                        <th style="width: 120px;">Kode Tindakan</th>
                        <th>Nama Tindakan</th>
                        <th style="width: 120px;" class="text-end">Tarif</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="(item, idx) in jenisTindakanList"
                        :key="idx"
                        @click="toggleSelectTindakan(item)"
                        :class="{ 'table-primary': isSelectedTindakan(item) }"
                        style="cursor: pointer;"
                      >
                        <td class="text-center">
                          <input
                            type="checkbox"
                            :checked="isSelectedTindakan(item)"
                            @change="toggleSelectTindakan(item)"
                          />
                        </td>
                        <td>{{ item.kd_jenis_prw }}</td>
                        <td>{{ item.nm_perawatan }}</td>
                        <td class="text-end">{{ formatRupiah(item.total_byrdr) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Tindakan Terpilih -->
              <div v-if="selectedTindakan.length > 0" class="selected-pemeriksaan mb-3">
                <h6 class="mb-2">Tindakan Terpilih ({{ selectedTindakan.length }} item)</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered">
                    <thead class="table-success">
                      <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 120px;">Kode</th>
                        <th>Nama Tindakan</th>
                        <th style="width: 120px;" class="text-end">Tarif</th>
                        <th style="width: 60px;" class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, idx) in selectedTindakan" :key="idx">
                        <td class="text-center">{{ idx + 1 }}</td>
                        <td>{{ item.kd_jenis_prw }}</td>
                        <td>{{ item.nm_perawatan }}</td>
                        <td class="text-end">{{ formatRupiah(item.total_byrdr) }}</td>
                        <td class="text-center">
                          <button
                            class="btn btn-sm btn-danger"
                            @click="removeSelectedTindakan(idx)"
                            title="Hapus"
                          >
                            ✕
                          </button>
                        </td>
                      </tr>
                      <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Total Biaya:</td>
                        <td class="text-end">{{ formatRupiah(totalBiayaTindakan) }}</td>
                        <td></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="d-flex gap-2 justify-content-end">
                <button
                  class="btn btn-secondary"
                  @click="clearTindakanForm"
                  :disabled="loadingSubmitTindakan"
                >
                  Reset
                </button>
                <button
                  class="btn btn-primary"
                  @click="submitTindakan"
                  :disabled="selectedTindakan.length === 0 || loadingSubmitTindakan"
                >
                  <span v-if="loadingSubmitTindakan" class="spinner-border spinner-border-sm me-2"></span>
                  {{ loadingSubmitTindakan ? 'Menyimpan...' : 'Simpan Tindakan' }}
                </button>
              </div>
            </div>

            <!-- Riwayat Tindakan -->
            <div class="tindakan-history-section">
              <h6 class="section-title mb-3">
                <i class="bi bi-clock-history me-2"></i>Riwayat Tindakan Medis
              </h6>

              <div v-if="loadingRiwayatTindakan" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat riwayat tindakan...</p>
              </div>

              <div v-else-if="riwayatTindakanList.length === 0" class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>Belum ada riwayat tindakan medis
              </div>

              <div v-else>
                <div class="table-responsive">
                  <table class="table table-sm table-hover table-bordered">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 15%">Tanggal</th>
                        <th style="width: 10%">Jam</th>
                        <th style="width: 15%">Kode</th>
                        <th style="width: 35%">Nama Tindakan</th>
                        <th style="width: 20%" class="text-end">Biaya</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in riwayatTindakanList" :key="index">
                        <td class="text-center">{{ index + 1 }}</td>
                        <td>{{ formatTanggal(item.tgl_perawatan) }}</td>
                        <td>{{ item.jam_rawat }}</td>
                        <td><small>{{ item.kd_jenis_prw }}</small></td>
                        <td><small>{{ item.nm_perawatan }}</small></td>
                        <td class="text-end">{{ formatRupiah(item.biaya_rawat) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab Content: USG/EKG -->
        <div v-if="activeInputTab === 'usg-ekg'">
          <!-- Sub-tabs untuk USG dan EKG -->
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: activeUsgEkgTab === 'usg' }"
                @click="activeUsgEkgTab = 'usg'"
                href="javascript:void(0)"
              >
                <img src="/images/usg (1).png" style="width: 18px; height: 18px; object-fit: contain; margin-right: 6px;"> USG
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: activeUsgEkgTab === 'ekg' }"
                @click="activeUsgEkgTab = 'ekg'"
                href="javascript:void(0)"
              >
                <img src="/images/ekg.png" style="width: 18px; height: 18px; object-fit: contain; margin-right: 6px;"> EKG
              </a>
            </li>
          </ul>

          <!-- Sub-tab Content: USG -->
          <div v-if="activeUsgEkgTab === 'usg'">
            <div class="usg-form-container">
              <!-- Form Input USG -->
              <div class="usg-input-section mb-4">
                <h6 class="section-title mb-3">
                  <i class="bi bi-tv me-2"></i>Input Hasil Baca USG
                </h6>

                <!-- Form Input -->
                <div class="card mb-3">
                  <div class="card-body">
                    <div class="mb-3">
                      <label class="form-label fw-bold">Hasil Pemeriksaan USG</label>
                      <textarea
                        class="form-control"
                        rows="10"
                        v-model="usgForm.hasil"
                        placeholder="Masukkan hasil pembacaan USG..."
                      ></textarea>
                    </div>

                    <!-- Upload Foto Section -->
                    <div class="mb-3">
                      <label class="form-label fw-bold">
                        <i class="bi bi-camera me-2"></i>Foto Hasil USG
                      </label>
                      <div class="photo-upload-section">
                        <!-- Hidden File Input -->
                        <input
                          type="file"
                          ref="usgPhotoInput"
                          @change="handleUsgPhotoSelect"
                          accept="image/*"
                          capture="environment"
                          multiple
                          style="display: none"
                        />

                        <!-- Upload Buttons -->
                        <div class="upload-buttons mb-3">
                          <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            @click="openUsgCamera"
                          >
                            <i class="bi bi-camera-fill me-2"></i>Ambil Foto
                          </button>
                          <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm"
                            @click="openUsgCamera"
                          >
                            <i class="bi bi-image me-2"></i>Pilih dari Galeri
                          </button>
                        </div>

                        <!-- Photo Preview Grid -->
                        <div v-if="usgPhotos.length > 0" class="photo-preview-grid">
                          <div
                            v-for="(photo, index) in usgPhotos"
                            :key="index"
                            class="photo-preview-item"
                          >
                            <img :src="photo.preview" :alt="photo.name" />
                            <button
                              type="button"
                              class="btn-remove-photo"
                              @click="removeUsgPhoto(index)"
                              title="Hapus foto"
                            >
                              <i class="bi bi-x-circle-fill"></i>
                            </button>
                            <div class="photo-name">{{ photo.name }}</div>
                          </div>
                        </div>

                        <small class="text-muted">
                          <i class="bi bi-info-circle me-1"></i>
                          Anda dapat mengunggah beberapa foto sekaligus
                        </small>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                      <button
                        class="btn btn-secondary"
                        @click="clearUsgForm"
                        :disabled="loadingSubmitUsg"
                      >
                        Reset
                      </button>
                      <button
                        class="btn btn-primary"
                        @click="submitUsg"
                        :disabled="!usgForm.hasil || loadingSubmitUsg"
                      >
                        <span v-if="loadingSubmitUsg" class="spinner-border spinner-border-sm me-2"></span>
                        {{ loadingSubmitUsg ? 'Menyimpan...' : 'Simpan Hasil USG' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Riwayat USG -->
              <div class="usg-history-section">
                <h6 class="section-title mb-3">
                  <i class="bi bi-clock-history me-2"></i>Riwayat Hasil USG
                </h6>

                <div v-if="loadingRiwayatUsg" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Memuat riwayat USG...</p>
                </div>

                <div v-else-if="riwayatUsgList.length === 0" class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>Belum ada riwayat hasil USG
                </div>

                <div v-else>
                  <div v-for="(item, index) in riwayatUsgList" :key="index" class="card mb-3">
                    <div class="card-header bg-info text-white">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <strong>USG</strong>
                          <span class="ms-3">{{ formatTanggal(item.tgl_periksa) }} {{ item.jam_periksa }}</span>
                        </div>
                        <span class="badge bg-light text-dark">{{ item.nm_dokter }}</span>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="hasil-content">
                        <pre style="white-space: pre-wrap; font-family: inherit; margin: 0;">{{ item.hasil }}</pre>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sub-tab Content: EKG -->
          <div v-if="activeUsgEkgTab === 'ekg'">
            <div class="ekg-form-container">
              <!-- Form Input EKG -->
              <div class="ekg-input-section mb-4">
                <h6 class="section-title mb-3">
                  <i class="bi bi-heart-pulse me-2"></i>Input Hasil Baca EKG
                </h6>

                <!-- Form Input -->
                <div class="card mb-3">
                  <div class="card-body">
                    <div class="mb-3">
                      <label class="form-label fw-bold">Hasil Pemeriksaan EKG</label>
                      <textarea
                        class="form-control"
                        rows="10"
                        v-model="ekgForm.hasil"
                        placeholder="Masukkan hasil pembacaan EKG..."
                      ></textarea>
                    </div>

                    <!-- Upload Foto Section -->
                    <div class="mb-3">
                      <label class="form-label fw-bold">
                        <i class="bi bi-camera me-2"></i>Foto Hasil EKG
                      </label>
                      <div class="photo-upload-section">
                        <!-- Hidden File Input -->
                        <input
                          type="file"
                          ref="ekgPhotoInput"
                          @change="handleEkgPhotoSelect"
                          accept="image/*"
                          capture="environment"
                          multiple
                          style="display: none"
                        />

                        <!-- Upload Buttons -->
                        <div class="upload-buttons mb-3">
                          <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            @click="openEkgCamera"
                          >
                            <i class="bi bi-camera-fill me-2"></i>Ambil Foto
                          </button>
                          <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm"
                            @click="openEkgCamera"
                          >
                            <i class="bi bi-image me-2"></i>Pilih dari Galeri
                          </button>
                        </div>

                        <!-- Photo Preview Grid -->
                        <div v-if="ekgPhotos.length > 0" class="photo-preview-grid">
                          <div
                            v-for="(photo, index) in ekgPhotos"
                            :key="index"
                            class="photo-preview-item"
                          >
                            <img :src="photo.preview" :alt="photo.name" />
                            <button
                              type="button"
                              class="btn-remove-photo"
                              @click="removeEkgPhoto(index)"
                              title="Hapus foto"
                            >
                              <i class="bi bi-x-circle-fill"></i>
                            </button>
                            <div class="photo-name">{{ photo.name }}</div>
                          </div>
                        </div>

                        <small class="text-muted">
                          <i class="bi bi-info-circle me-1"></i>
                          Anda dapat mengunggah beberapa foto sekaligus
                        </small>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end">
                      <button
                        class="btn btn-secondary"
                        @click="clearEkgForm"
                        :disabled="loadingSubmitEkg"
                      >
                        Reset
                      </button>
                      <button
                        class="btn btn-primary"
                        @click="submitEkg"
                        :disabled="!ekgForm.hasil || loadingSubmitEkg"
                      >
                        <span v-if="loadingSubmitEkg" class="spinner-border spinner-border-sm me-2"></span>
                        {{ loadingSubmitEkg ? 'Menyimpan...' : 'Simpan Hasil EKG' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Riwayat EKG -->
              <div class="ekg-history-section">
                <h6 class="section-title mb-3">
                  <i class="bi bi-clock-history me-2"></i>Riwayat Hasil EKG
                </h6>

                <div v-if="loadingRiwayatEkg" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Memuat riwayat EKG...</p>
                </div>

                <div v-else-if="riwayatEkgList.length === 0" class="alert alert-info">
                  <i class="bi bi-info-circle me-2"></i>Belum ada riwayat hasil EKG
                </div>

                <div v-else>
                  <div v-for="(item, index) in riwayatEkgList" :key="index" class="card mb-3">
                    <div class="card-header bg-danger text-white">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <strong>EKG</strong>
                          <span class="ms-3">{{ formatTanggal(item.tgl_periksa) }} {{ item.jam_periksa }}</span>
                        </div>
                        <span class="badge bg-light text-dark">{{ item.nm_dokter }}</span>
                      </div>
                    </div>
                    <div class="card-body">
                      <div class="hasil-content">
                        <pre style="white-space: pre-wrap; font-family: inherit; margin: 0;">{{ item.hasil }}</pre>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal Success Confirmation -->
    <div v-if="showSuccessAlert" class="modal-overlay" @click.self="closeSuccessAlert">
      <div class="modal-success-confirm" @click.stop>
        <div class="modal-success-icon">
          <div class="success-checkmark">✓</div>
        </div>
        <div class="modal-success-body">
          <h4 class="text-center mb-3">SOAP Berhasil Disimpan!</h4>
          <p class="text-center text-muted mb-4">
            Data pemeriksaan SOAP telah tersimpan dengan baik.<br>
            Apakah Anda ingin melanjutkan untuk menginput resep obat?
          </p>

          <div class="success-info-box mb-4">
            <div class="info-item">
              <span class="info-label">Pasien:</span>
              <span class="info-value">{{ patient.nm_pasien }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">No. RM:</span>
              <span class="info-value">{{ patient.no_rkm_medis }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">No. Rawat:</span>
              <span class="info-value">{{ patient.no_rawat }}</span>
            </div>
          </div>

          <div class="d-flex gap-3 justify-content-center">
            <button @click="lanjutkanResep" class="btn btn-lg btn-primary px-5">
              <span class="me-2">💊</span>
              Ya, Input Resep
            </button>
            <button @click="closeSuccessAlert" class="btn btn-lg btn-outline-secondary px-5">
              <span class="me-2">👌</span>
              Tidak, Selesai
            </button>
          </div>

          <div class="text-center mt-3">
            <small class="text-muted">
              Anda juga dapat menginput resep nanti melalui menu Resep
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Input Resep -->
    <div v-if="showModalResep" class="modal-overlay" @click="closeModalResep">
      <div class="modal-resep" @click.stop>
        <div class="modal-header-resep">
          <h5><img src="/images/pharmacy (2).png" style="width: 20px; height: 20px; object-fit: contain;"> Input Resep - {{ patient.nm_pasien }} ({{ patient.no_rkm_medis }})</h5>
          <button @click="closeModalResep" class="btn-close-modal">✕</button>
        </div>
        <div class="modal-body-resep">
          <!-- Tab Navigation -->
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: activeResepTab === 'non-racikan' }"
                @click="activeResepTab = 'non-racikan'"
                href="javascript:void(0)"
              >
                📦 Non Racikan
              </a>
            </li>
            <li class="nav-item">
              <a
                class="nav-link"
                :class="{ active: activeResepTab === 'racikan' }"
                @click="activeResepTab = 'racikan'"
                href="javascript:void(0)"
              >
                🧪 Racikan
              </a>
            </li>
          </ul>

          <!-- Tab Content: Non Racikan -->
          <div v-if="activeResepTab === 'non-racikan'" class="tab-content-resep">
              <div class="mb-3">
                <label class="form-label fw-bold">Cari Obat</label>
                <div class="search-obat-wrapper">
                  <div class="input-group">
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Ketik nama obat..."
                      v-model="searchObatNonRacikan"
                      @input="cariObatNonRacikan"
                    >
                    <button type="button" class="btn btn-primary" @click="cariObatNonRacikan">
                      🔍 Cari
                    </button>
                  </div>

                  <!-- Dropdown hasil pencarian -->
                <div v-if="showObatDropdown && obatList.length > 0" class="obat-dropdown">
                  <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th width="15%">Kode Barang</th>
                        <th>Nama Barang</th>
                        <th width="10%">Satuan</th>
                        <th width="10%">Stok</th>
                        <th width="15%">Harga (Rp)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="obat in obatList"
                        :key="obat.kode_brng"
                        class="obat-item-row"
                        @click="pilihObatNonRacikan(obat)"
                      >
                        <td><small>{{ obat.kode_brng }}</small></td>
                        <td>
                          <div class="obat-name-cell">{{ obat.nama_brng }}</div>
                          <div class="obat-extra-info">
                            <small class="text-muted">{{ obat.jenis_obat }} - {{ obat.nama_industri }}</small>
                          </div>
                        </td>
                        <td class="text-center">{{ obat.kode_sat }}</td>
                        <td class="text-center">
                          <span :class="obat.stok > 0 ? 'text-success' : 'text-danger'">
                            {{ obat.stok }}
                          </span>
                        </td>
                        <td class="text-end">{{ formatRupiah(obat.harga) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Daftar Obat yang Dipilih</label>
                <div v-if="resepNonRacikan.length === 0" class="alert alert-warning">
                  Belum ada obat yang dipilih
                </div>
                <div v-else class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                  <table class="table table-bordered table-sm">
                    <thead class="table-light">
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Obat</th>
                        <th width="10%">Jumlah</th>
                        <th width="10%">Satuan</th>
                        <th width="25%">Aturan Pakai</th>
                        <th width="5%">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(obat, index) in resepNonRacikan" :key="index">
                        <td class="text-center">{{ index + 1 }}</td>
                        <td>{{ obat.nama_brng }}</td>
                        <td>
                          <input
                            type="number"
                            class="form-control form-control-sm"
                            v-model="obat.jml"
                            min="1"
                            :max="obat.stok"
                            @focus="$event.target.select()"
                          >
                        </td>
                        <td>{{ obat.kode_sat }}</td>
                        <td>
                          <input
                            type="text"
                            class="form-control form-control-sm"
                            v-model="obat.aturan_pakai"
                            placeholder="3x1 sehari"
                          >
                        </td>
                        <td class="text-center">
                          <button
                            type="button"
                            @click="hapusObatNonRacikan(index)"
                            class="btn btn-sm btn-danger"
                          >
                            🗑️
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
          </div>

          <!-- Tab Content: Racikan -->
          <div v-if="activeResepTab === 'racikan'" class="tab-content-resep">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold">Nama Racikan</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="racikan.nama_racikan"
                    placeholder="Contoh: Racikan Batuk"
                  >
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Keterangan</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="racikan.keterangan"
                    placeholder="Keterangan tambahan"
                  >
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label fw-bold">Metode Racik</label>
                  <select class="form-select" v-model="racikan.metode_racik">
                    <option value="">Pilih Metode</option>
                    <option value="Kapsul">Kapsul</option>
                    <option value="Puyer">Puyer</option>
                    <option value="Sirup">Sirup</option>
                    <option value="Salep">Salep</option>
                    <option value="Krim">Krim</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-bold">Jumlah Racikan</label>
                  <input
                    type="number"
                    class="form-control"
                    v-model="racikan.jml_dr"
                    placeholder="Jumlah"
                    min="1"
                    @focus="$event.target.select()"
                  >
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-bold">Aturan Pakai</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="racikan.aturan_pakai"
                    placeholder="3x1 sehari"
                  >
                </div>
              </div>

              <hr>

              <div class="mb-3">
                <label class="form-label fw-bold">Detail Obat Racikan</label>
                <div class="search-obat-wrapper">
                  <div class="input-group mb-2">
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Ketik nama obat..."
                      v-model="searchObatRacikan"
                      @input="cariObatRacikan"
                    >
                    <button type="button" class="btn btn-primary" @click="cariObatRacikan">
                      🔍 Cari
                    </button>
                  </div>

                  <!-- Dropdown hasil pencarian racikan -->
                <div v-if="showObatDropdownRacikan && obatListRacikan.length > 0" class="obat-dropdown">
                  <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                      <tr>
                        <th width="15%">Kode Barang</th>
                        <th>Nama Barang</th>
                        <th width="10%">Satuan</th>
                        <th width="10%">Stok</th>
                        <th width="15%">Harga (Rp)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="obat in obatListRacikan"
                        :key="obat.kode_brng"
                        class="obat-item-row"
                        @click="pilihObatRacikan(obat)"
                      >
                        <td><small>{{ obat.kode_brng }}</small></td>
                        <td>
                          <div class="obat-name-cell">{{ obat.nama_brng }}</div>
                          <div class="obat-extra-info">
                            <small class="text-muted">{{ obat.jenis_obat }} - {{ obat.nama_industri }}</small>
                          </div>
                        </td>
                        <td class="text-center">{{ obat.kode_sat }}</td>
                        <td class="text-center">
                          <span :class="obat.stok > 0 ? 'text-success' : 'text-danger'">
                            {{ obat.stok }}
                          </span>
                        </td>
                        <td class="text-end">{{ formatRupiah(obat.harga) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Daftar Obat dalam Racikan</label>
                <div v-if="racikan.detail.length === 0" class="alert alert-warning">
                  Belum ada obat dalam racikan
                </div>
                <div v-else class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                  <table class="table table-bordered table-sm">
                    <thead class="table-light">
                      <tr>
                        <th width="5%">No</th>
                        <th>Nama Obat</th>
                        <th width="10%">Kapasitas</th>
                        <th width="10%">Kandungan</th>
                        <th width="10%">Jumlah</th>
                        <th width="10%">Satuan</th>
                        <th width="5%">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(obat, index) in racikan.detail" :key="index">
                        <td class="text-center">{{ index + 1 }}</td>
                        <td>{{ obat.nama_brng }}</td>
                        <td class="text-center">{{ obat.kapasitas }}</td>
                        <td class="text-center">{{ obat.kandungan }}</td>
                        <td class="text-center">{{ obat.jml }}</td>
                        <td class="text-center">{{ obat.kode_sat }}</td>
                        <td class="text-center">
                          <button
                            type="button"
                            @click="hapusObatRacikan(index)"
                            class="btn btn-sm btn-danger"
                          >
                            🗑️
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
          </div>

          <!-- Unified Save Button (outside tabs) -->
          <div class="modal-footer-actions mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
              <button type="button" @click="openModalRiwayatResep" class="btn btn-info">
                📋 Riwayat Resep
              </button>
              <div class="d-flex gap-2">
                <button type="button" @click="submitResepUnified" class="btn btn-success">
                  💾 Simpan Resep
                </button>
                <button type="button" @click="closeModalResep" class="btn btn-secondary">
                  ❌ Tutup
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Input Obat Non Racikan -->
    <div v-if="showModalInputObat" class="modal-overlay" @click="closeModalInputObat">
      <div class="modal-input-obat-simple" @click.stop>
        <form @submit.prevent="confirmTambahObat">
          <div class="row g-2 align-items-end">
            <div class="col-auto" style="width: 120px;">
              <label class="form-label mb-1">Jumlah <span class="text-danger">*</span></label>
              <input
                type="number"
                class="form-control"
                v-model.number="inputObatForm.jml"
                min="1"
                :max="selectedObatNonRacikan.stok"
                required
                autofocus
                @focus="$event.target.select()"
                @keyup.enter="confirmTambahObat">
            </div>
            <div class="col">
              <label class="form-label mb-1">Aturan Pakai <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                v-model="inputObatForm.aturan_pakai"
                placeholder="3x1 sehari setelah makan"
                required
                @keyup.enter="confirmTambahObat">
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-primary">
                ✅ Tambah
              </button>
            </div>
            <div class="col-auto">
              <button type="button" @click="closeModalInputObat" class="btn btn-secondary">
                ❌ Batal
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Input Obat Racikan -->
    <div v-if="showModalInputObatRacikan" class="modal-overlay" @click="closeModalInputObatRacikan">
      <div class="modal-input-obat-racikan" @click.stop>
        <div class="obat-racikan-info">
          <div class="obat-racikan-name">{{ selectedObatRacikan?.nama_brng }}</div>
          <div class="obat-racikan-details">
            <span>Kapasitas: <strong>{{ selectedObatRacikan?.kapasitas }}</strong></span>
            <span class="mx-2">|</span>
            <span>Stok: <strong>{{ selectedObatRacikan?.stok }}</strong> {{ selectedObatRacikan?.kode_sat }}</span>
          </div>
        </div>
        <form @submit.prevent="confirmTambahObatRacikan">
          <div class="row g-2 align-items-end">
            <div class="col-auto" style="width: 150px;">
              <label class="form-label mb-1">Kandungan <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                v-model="inputObatRacikanForm.kandungan"
                placeholder="200 atau 2/3"
                required
                autofocus
                @input="hitungJumlahObatRacikan"
                @keyup.enter="confirmTambahObatRacikan">
            </div>
            <div class="col-auto" style="width: 150px;">
              <label class="form-label mb-1">Jumlah <span class="text-muted">(otomatis)</span></label>
              <input
                type="number"
                class="form-control"
                v-model.number="inputObatRacikanForm.jml"
                min="0.1"
                step="0.1"
                readonly
                style="background-color: #f8f9fa;">
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-primary">
                ✅ Tambah
              </button>
            </div>
            <div class="col-auto">
              <button type="button" @click="closeModalInputObatRacikan" class="btn btn-secondary">
                ❌ Batal
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Riwayat Resep -->
    <div v-if="showModalRiwayatResep" class="modal-overlay" @click="closeModalRiwayatResep">
      <div class="modal-riwayat-resep" @click.stop>
        <div class="modal-header-custom">
          <h5>📋 Riwayat Resep - {{ patient.nm_pasien }} ({{ patient.no_rkm_medis }})</h5>
          <button @click="closeModalRiwayatResep" class="btn-close-modal">✕</button>
        </div>
        <div class="modal-body-custom">
          <div v-if="loadingRiwayatResep" class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div v-else-if="riwayatResep.length === 0" class="alert alert-info">
            Belum ada riwayat resep untuk pasien ini
          </div>
          <div v-else class="resep-history-container">
            <div v-for="(resep, index) in riwayatResep" :key="index" class="resep-card mb-3">
              <!-- Resep Header -->
              <div class="resep-header">
                <div class="row">
                  <div class="col-md-3">
                    <strong>No. Resep:</strong> {{ resep.no_resep }}
                  </div>
                  <div class="col-md-3">
                    <strong>Tanggal:</strong> {{ resep.tgl_peresepan }} {{ resep.jam_peresepan }}
                  </div>
                  <div class="col-md-3">
                    <strong>No. Rawat:</strong> {{ resep.no_rawat }}
                  </div>
                  <div class="col-md-3">
                    <strong>Dokter:</strong> {{ resep.nm_dokter }}
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-md-6">
                    <span class="badge" :class="resep.status === 'Sudah Terlayani' ? 'bg-success' : 'bg-warning'">
                      {{ resep.status }}
                    </span>
                    <span class="badge bg-info ms-2">{{ resep.status_asal }}</span>
                  </div>
                  <div class="col-md-6 text-end">
                    <button
                      @click="copyResepToForm(resep)"
                      class="btn btn-sm btn-primary"
                      title="Copy resep ini ke form input">
                      📋 Copy Resep
                    </button>
                  </div>
                </div>
              </div>

              <!-- Non Racikan -->
              <div v-if="resep.non_racikan && resep.non_racikan.length > 0" class="mt-3">
                <h6 class="text-primary">📦 Obat Non-Racikan</h6>
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th width="5%">No</th>
                      <th width="15%">Kode</th>
                      <th>Nama Obat</th>
                      <th width="10%">Jumlah</th>
                      <th width="10%">Satuan</th>
                      <th width="25%">Aturan Pakai</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(obat, idx) in resep.non_racikan" :key="idx">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ obat.kode_brng }}</td>
                      <td>{{ obat.nama_brng }}</td>
                      <td class="text-center">{{ obat.jml }}</td>
                      <td class="text-center">{{ obat.kode_sat }}</td>
                      <td>{{ obat.aturan_pakai }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Racikan -->
              <div v-if="resep.racikan && resep.racikan.length > 0" class="mt-3">
                <h6 class="text-success">🧪 Obat Racikan</h6>
                <div v-for="(racikan, ridx) in resep.racikan" :key="ridx" class="racikan-item mb-3">
                  <div class="racikan-header-info">
                    <strong>No. Racik {{ racikan.no_racik }}:</strong> {{ racikan.nama_racik }}
                    <span class="ms-2 badge bg-secondary">{{ racikan.metode || 'N/A' }}</span>
                    <span class="ms-2">| Jumlah: {{ racikan.jml_dr }}</span>
                    <span class="ms-2">| Aturan: {{ racikan.aturan_pakai }}</span>
                  </div>
                  <table class="table table-sm table-bordered mt-2">
                    <thead class="table-light">
                      <tr>
                        <th width="5%">No</th>
                        <th width="15%">Kode</th>
                        <th>Nama Obat</th>
                        <th width="12%">Jumlah</th>
                        <th width="12%">Satuan</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(detail, didx) in racikan.detail" :key="didx">
                        <td class="text-center">{{ didx + 1 }}</td>
                        <td><small>{{ detail.kode_brng }}</small></td>
                        <td><small>{{ detail.nama_brng }}</small></td>
                        <td class="text-center"><small>{{ detail.jml }}</small></td>
                        <td class="text-center"><small>{{ detail.kode_sat }}</small></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Riwayat SOAPIE -->
    <div v-if="showModalSOAPIE" class="modal-overlay" @click="closeModalSOAPIE">
      <div class="modal-soapie" @click.stop>
        <div class="modal-header-soapie">
          <h5>📋 Riwayat SOAPIE - {{ patient.nm_pasien }} ({{ patient.no_rkm_medis }})</h5>
          <button @click="closeModalSOAPIE" class="btn-close-modal">✕</button>
        </div>
        <div class="modal-body-soapie">
          <div v-if="loadingSOAPIE" class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
          <div v-else>
            <table class="table table-bordered soapie-table">
              <thead>
                <tr class="table-secondary">
                  <th width="7%">Tgl.Reg</th>
                  <th width="10%">No.Rawat</th>
                  <th width="5%">Status</th>
                  <th width="78%">S.O.A.P.I.E</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="(reg, index) in riwayatSOAPIE" :key="index">
                  <tr>
                    <td class="text-center align-top">{{ reg.tgl_registrasi }}</td>
                    <td class="text-center align-top">
                      {{ reg.no_rawat }}
                      <br>
                      <button
                        v-if="reg.pemeriksaan && reg.pemeriksaan.length > 0"
                        @click="copyToForm(reg.pemeriksaan[reg.pemeriksaan.length - 1])"
                        class="btn btn-sm btn-primary mt-1"
                        title="Copy data SOAP terakhir ke form"
                      >
                        📋 Copy
                      </button>
                    </td>
                    <td class="text-center align-top">{{ reg.status_lanjut }}</td>
                    <td class="p-0">
                      <table class="table table-sm mb-0 inner-table" v-if="reg.pemeriksaan && reg.pemeriksaan.length > 0">
                        <thead class="table-light">
                          <tr>
                            <th width="7%">Tanggal</th>
                            <th width="13%">Dokter/Paramedis</th>
                            <th width="14%">Subjek</th>
                            <th width="13%">Objek</th>
                            <th width="13%">Asesmen</th>
                            <th width="14%">Plan</th>
                            <th width="13%">Inst/Impl</th>
                            <th width="13%">Evaluasi</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(p, pIndex) in reg.pemeriksaan" :key="pIndex">
                            <td class="text-center">
                              {{ p.tgl_perawatan }}<br>{{ p.jam_rawat }}
                            </td>
                            <td class="text-center">
                              {{ p.nip }}<br>{{ p.nama }}
                            </td>
                            <td>{{ p.keluhan }}</td>
                            <td>
                              <div v-html="formatObjektif(p)"></div>
                            </td>
                            <td>{{ p.penilaian }}</td>
                            <td>{{ p.rtl }}</td>
                            <td>{{ p.instruksi }}</td>
                            <td>{{ p.evaluasi }}</td>
                          </tr>
                        </tbody>
                      </table>
                      <div v-else class="p-3 text-muted text-center">
                        Tidak ada data pemeriksaan
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Sweet Alert Modal -->
    <div v-if="sweetAlert.show" class="sweet-alert-overlay" @click.self="closeSweetAlert">
      <div class="sweet-alert-modal" @click.stop>
        <div class="sweet-alert-icon" :class="'icon-' + sweetAlert.type">
          <span v-if="sweetAlert.type === 'success'">✓</span>
          <span v-else-if="sweetAlert.type === 'error'">✕</span>
          <span v-else-if="sweetAlert.type === 'warning'">!</span>
          <span v-else-if="sweetAlert.type === 'info'">i</span>
          <span v-else>?</span>
        </div>
        <h3 class="sweet-alert-title">{{ sweetAlert.title }}</h3>
        <p class="sweet-alert-message">{{ sweetAlert.message }}</p>
        <div class="sweet-alert-buttons">
          <button
            v-if="sweetAlert.confirmText"
            @click="confirmSweetAlert"
            class="btn-sweet-confirm"
            :class="'btn-' + sweetAlert.type"
          >
            {{ sweetAlert.confirmText }}
          </button>
          <button
            v-if="sweetAlert.cancelText"
            @click="closeSweetAlert"
            class="btn-sweet-cancel"
          >
            {{ sweetAlert.cancelText }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Rujuk Internal -->
    <div v-if="showModalRujukInternal" class="modal-overlay" @click="closeModalRujukInternal">
      <div class="modal-content modal-md" @click.stop>
        <div class="modal-header">
          <h5 class="modal-title">🏥 Rujuk Internal Poli</h5>
          <button type="button" class="btn-close" @click="closeModalRujukInternal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Pasien</label>
            <input type="text" class="form-control" :value="`${patient.nm_pasien} (${patient.no_rkm_medis})`" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">No. Rawat</label>
            <input type="text" class="form-control" :value="patient.no_rawat" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Poliklinik Tujuan <span class="text-danger">*</span></label>
            <select v-model="rujukInternalForm.kd_poli" class="form-select" @change="onPoliChange">
              <option value="">-- Pilih Poliklinik --</option>
              <option v-for="poli in poliList" :key="poli.kd_poli" :value="poli.kd_poli">
                {{ poli.nm_poli }}
              </option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Dokter Tujuan <span class="text-danger">*</span></label>
            <select v-model="rujukInternalForm.kd_dokter" class="form-select">
              <option value="">-- Pilih Dokter --</option>
              <option v-for="dokter in dokterList" :key="dokter.kd_dokter" :value="dokter.kd_dokter">
                {{ dokter.nm_dokter }}
              </option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModalRujukInternal">Batal</button>
          <button type="button" class="btn btn-primary" @click="submitRujukInternal" :disabled="loadingRujukInternal">
            <span v-if="loadingRujukInternal" class="spinner-border spinner-border-sm me-2"></span>
            Simpan Rujukan
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Riwayat Perawatan -->
  <div v-if="isRiwayatModalOpen" class="modal-backdrop-riwayat" @click="closeRiwayatModal">
    <div class="modal-dialog-riwayat" @click.stop>
      <div class="modal-content-riwayat">
        <div class="modal-header-riwayat">
          <div>
            <h5 class="modal-title-riwayat">📋 Riwayat Perawatan</h5>
          </div>
          <div class="modal-header-actions">
            <button
              type="button"
              class="btn btn-sm btn-light me-2"
              @click.stop="printRiwayatTimeline"
              :disabled="riwayatLoading"
            >
              <i class="bi bi-printer"></i> Cetak
            </button>
            <button
              type="button"
              class="btn btn-sm btn-outline-light me-2"
              @click.stop="downloadRiwayatPasien"
              :disabled="riwayatActionLoading || riwayatLoading"
            >
              <span
                v-if="riwayatActionLoading"
                class="spinner-border spinner-border-sm me-1"
              ></span>
              <i v-else class="bi bi-download"></i> Unduh
            </button>
            <button type="button" class="btn-close-modal-riwayat" @click="closeRiwayatModal" title="Tutup">
              ×
            </button>
          </div>
        </div>
        <div class="modal-body-riwayat">
          <div v-if="riwayatLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Memuat seluruh riwayat perawatan pasien...</p>
            <small class="text-muted">Mohon tunggu, sedang mengumpulkan data dari semua kunjungan</small>
          </div>

          <div v-else-if="riwayatError" class="alert alert-danger">{{ riwayatError }}</div>

          <div v-else id="riwayat-modal-printable">
            <RiwayatTimelineSection
              :timeline="riwayatTimeline"
              :summary="riwayatSummaryInfo"
              :patient="riwayatPatientMeta || patient"
              :limit="5"
              @open-detail="handleOpenRiwayatDetail"
            />
          </div>
        </div>
        <div class="modal-footer-riwayat">
          <button type="button" class="btn btn-secondary" @click="closeRiwayatModal">
            <i class="bi bi-x-circle"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import apiClient from '@/api/axios'
import RiwayatTimelineSection from '@/components/RiwayatTimelineSection.vue'

const route = useRoute()
const router = useRouter()
const riwayatPrintStyles = `
  body { font-family: 'Inter', sans-serif; padding: 32px; background: #ffffff; color: #0f172a; }
  h1, h2, h3, h4, h5, h6 { color: #0f172a; }
  .kunjungan-group { border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px; overflow: hidden; }
  .kunjungan-header { background: #4c1d95; color: white; padding: 16px; }
  .kunjungan-body { padding: 16px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  th, td { border: 1px solid #e2e8f0; padding: 8px; font-size: 12px; }
  .summary-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
  .break-page { page-break-after: always; }
`

const loading = ref(false)
const error = ref('')
const activeInputTab = ref('soap')
const activeUsgEkgTab = ref('usg')
const soapFormContainer = ref(null)
const isSidebarOpen = ref(false)
const isRiwayatModalOpen = ref(false)
const riwayatTimeline = ref([])
const riwayatSummaryInfo = ref({ total_kunjungan: 0, total_biaya: 0 })
const riwayatPatientMeta = ref(null)
const riwayatLoading = ref(false)
const riwayatError = ref('')
const totalBiayaRiwayat = ref(0)
const riwayatActionLoading = ref(false)

// Patient data from route params
const patient = ref({
  no_rkm_medis: route.query.no_rkm_medis || '',
  nm_pasien: route.query.nm_pasien || '',
  umur: route.query.umur || '',
  jk: route.query.jk || '',
  nm_dokter: route.query.nm_dokter || '',
  nm_poli: route.query.nm_poli || '',
  no_rawat: route.query.no_rawat || '',
  tgl_registrasi: route.query.tgl_registrasi || '',
  jam_reg: route.query.jam_reg || '',
  png_jawab: route.query.png_jawab || ''
})

// SOAP Form
const form = ref({
  no_rawat: patient.value.no_rawat,
  subjective: '',
  objective: '',
  assessment: '',
  planning: '',
  instruksi: '',
  evaluasi: '',
  // Vital signs
  tensi: '',
  suhu: '',
  nadi: '',
  respirasi: '',
  tinggi: '',
  berat: ''
})

// Edit mode state
const isEditMode = ref(false)
const editingSOAP = ref(null) // Store tgl_perawatan and jam_rawat for update

// SOAP History
const soapHistory = ref([])

// Riwayat SOAPIE Modal
const showModalSOAPIE = ref(false)
const loadingSOAPIE = ref(false)
const riwayatSOAPIE = ref([])

// Riwayat Resep Modal
const showModalRiwayatResep = ref(false)
const loadingRiwayatResep = ref(false)
const riwayatResep = ref([])

// Rujuk Internal Modal
const showModalRujukInternal = ref(false)
const loadingRujukInternal = ref(false)
const poliList = ref([])
const dokterList = ref([])
const rujukInternalForm = ref({
  kd_poli: '',
  kd_dokter: ''
})

// Laboratorium
const activeLabTab = ref('pk')
const labForm = ref({
  noorder: '',
  diagnosa_klinis: '',
  informasi_tambahan: ''
})
const searchLabPK = ref('')
const searchLabPA = ref('')
const pemeriksaanPKList = ref([])
const pemeriksaanPAList = ref([])
const selectedPemeriksaanPK = ref([])
const selectedPemeriksaanPA = ref([])
const loadingLabPK = ref(false)
const loadingLabPA = ref(false)
const loadingSubmitLab = ref(false)
const activeRiwayatLabTab = ref('pk')
const riwayatLabPKList = ref([])
const riwayatLabPAList = ref([])
const loadingRiwayatPK = ref(false)
const loadingRiwayatPA = ref(false)

// Radiologi State
const radForm = ref({
  noorder: '',
  informasi_tambahan: '',
  diagnosa_klinis: ''
})
const searchRad = ref('')
const pemeriksaanRadList = ref([])
const selectedPemeriksaanRad = ref([])
const loadingSubmitRad = ref(false)
const riwayatRadList = ref([])
const loadingRiwayatRad = ref(false)

// Tindakan State
const searchTindakan = ref('')
const jenisTindakanList = ref([])
const selectedTindakan = ref([])
const loadingSubmitTindakan = ref(false)
const riwayatTindakanList = ref([])
const loadingRiwayatTindakan = ref(false)

// USG State
const usgForm = ref({
  hasil: ''
})
const usgPhotos = ref([])
const usgPhotoInput = ref(null)
const loadingSubmitUsg = ref(false)
const riwayatUsgList = ref([])
const loadingRiwayatUsg = ref(false)

// EKG State
const ekgForm = ref({
  hasil: ''
})
const ekgPhotos = ref([])
const ekgPhotoInput = ref(null)
const loadingSubmitEkg = ref(false)
const riwayatEkgList = ref([])
const loadingRiwayatEkg = ref(false)

// Computed untuk check all
const isAllPKSelected = computed(() => {
  return pemeriksaanPKList.value.length > 0 &&
         selectedPemeriksaanPK.value.length === pemeriksaanPKList.value.length
})

const isAllPASelected = computed(() => {
  return pemeriksaanPAList.value.length > 0 &&
         selectedPemeriksaanPA.value.length === pemeriksaanPAList.value.length
})

// Resep Modal
const showModalResep = ref(false)
const activeResepTab = ref('non-racikan')

// Non Racikan
const searchObatNonRacikan = ref('')
const resepNonRacikan = ref([])
const obatList = ref([])
const showObatDropdown = ref(false)

// Modal Input Obat Non Racikan
const showModalInputObat = ref(false)
const selectedObatNonRacikan = ref(null)
const inputObatForm = ref({
  jml: 1,
  aturan_pakai: ''
})

// Racikan
const searchObatRacikan = ref('')
const obatListRacikan = ref([])
const showObatDropdownRacikan = ref(false)
const racikan = ref({
  nama_racikan: '',
  keterangan: '',
  metode_racik: '',
  jml_dr: 1,
  aturan_pakai: '',
  detail: []
})

// Modal Input Obat Racikan
const showModalInputObatRacikan = ref(false)
const selectedObatRacikan = ref(null)
const inputObatRacikanForm = ref({
  kandungan: '',
  jml: 0
})

// Legacy (for backward compatibility)
const resepSearch = ref('')
const resepObat = ref([])

// Success Alert
const showSuccessAlert = ref(false)

// Sweet Alert Component
const sweetAlert = ref({
  show: false,
  type: '', // 'confirm', 'success', 'error', 'warning', 'info'
  title: '',
  message: '',
  confirmText: 'Yes',
  cancelText: 'Cancel',
  onConfirm: null,
  onCancel: null
})

const showSweetAlert = (options) => {
  sweetAlert.value = {
    show: true,
    type: options.type || 'info',
    title: options.title || 'Are you sure?',
    message: options.message || '',
    confirmText: options.confirmText || 'Yes',
    cancelText: options.cancelText || 'Cancel',
    onConfirm: options.onConfirm || null,
    onCancel: options.onCancel || null
  }
}

const closeSweetAlert = () => {
  if (sweetAlert.value.onCancel) {
    sweetAlert.value.onCancel()
  }
  sweetAlert.value.show = false
}

const confirmSweetAlert = () => {
  if (sweetAlert.value.onConfirm) {
    sweetAlert.value.onConfirm()
  }
  sweetAlert.value.show = false
}

const formatTanggal = (tanggal) => {
  if (!tanggal) return '-'
  const date = new Date(tanggal)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}/${month}/${year}`
}

const formatDateTime = (tanggal, jam) => {
  if (!tanggal) return '-'
  const date = new Date(tanggal)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}-${month}-${year} ${jam || ''}`
}

const formatRupiah = (angka) => {
  if (!angka) return 'Rp 0'
  const formatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  })
  return formatter.format(angka)
}

const loadSOAPHistory = async () => {
  try {
    const { data } = await apiClient.get(`/soap/history/${patient.value.no_rawat}`)
    if (data.success) {
      soapHistory.value = data.data
    }
  } catch (err) {
    console.error('Load SOAP history error:', err)
  }
}

const submitSOAP = async () => {
  loading.value = true
  error.value = ''
  showSuccessAlert.value = false

  try {
    let data

    if (isEditMode.value && editingSOAP.value) {
      // UPDATE mode - use PUT request with query params to avoid URL encoding issues
      const response = await apiClient.put('/soap/update', form.value, {
        params: {
          no_rawat: editingSOAP.value.no_rawat,
          tgl_perawatan: editingSOAP.value.tgl_perawatan,
          jam_rawat: editingSOAP.value.jam_rawat
        }
      })
      data = response.data
    } else {
      // INSERT mode - use POST request
      const response = await apiClient.post('/soap', form.value)
      data = response.data
    }

    if (data.success) {
      // Reload history after save
      await loadSOAPHistory()
      // Reset form and edit mode
      form.value = {
        no_rawat: patient.value.no_rawat,
        subjective: '',
        objective: '',
        assessment: '',
        planning: '',
        instruksi: '',
        evaluasi: '',
        tensi: '',
        suhu: '',
        nadi: '',
        respirasi: '',
        tinggi: '',
        berat: ''
      }
      isEditMode.value = false
      editingSOAP.value = null

      // Show success alert with option to continue to Resep
      showSuccessAlert.value = true
    } else {
      error.value = isEditMode.value ? 'Gagal mengupdate SOAP' : 'Gagal menyimpan SOAP'
    }
  } catch (err) {
    console.error('Submit SOAP error:', err)
    error.value = err.response?.data?.message || (isEditMode.value ? 'Terjadi kesalahan saat mengupdate SOAP' : 'Terjadi kesalahan saat menyimpan SOAP')
  } finally {
    loading.value = false
  }
}

const editSOAP = (item) => {
  // Enable edit mode
  isEditMode.value = true
  editingSOAP.value = {
    no_rawat: item.no_rawat,
    tgl_perawatan: item.tgl_perawatan,
    jam_rawat: item.jam_rawat
  }

  // Populate form with existing data
  form.value = {
    no_rawat: item.no_rawat,
    subjective: item.keluhan || '',
    objective: item.pemeriksaan || '',
    assessment: item.penilaian || '',
    planning: item.rtl || '',
    instruksi: item.instruksi || '',
    evaluasi: item.evaluasi || '',
    tensi: item.tensi || '',
    suhu: item.suhu_tubuh || '',
    nadi: item.nadi || '',
    respirasi: item.respirasi || '',
    tinggi: item.tinggi || '',
    berat: item.berat || ''
  }

  // Switch to SOAP tab and scroll to form
  activeInputTab.value = 'soap'

  // Wait for DOM update then scroll
  setTimeout(() => {
    if (soapFormContainer.value) {
      soapFormContainer.value.scrollTo({ top: 0, behavior: 'smooth' })
    }
    // Also scroll window to top to ensure form is visible
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }, 100)
}

const deleteSOAP = (item) => {
  showSweetAlert({
    type: 'warning',
    title: 'Yakin?',
    message: 'Kamu akan menghapus data SOAP!',
    confirmText: 'Iya',
    cancelText: 'Cancel',
    onConfirm: async () => {
      try {
        const { data } = await apiClient.delete(`/soap/${item.no_rawat}/${item.tgl_perawatan}/${item.jam_rawat}`)

        if (data.success) {
          showSweetAlert({
            type: 'success',
            title: 'Success!',
            message: 'SOAP berhasil dihapus',
            confirmText: 'OK',
            cancelText: '',
            onConfirm: async () => {
              await loadSOAPHistory()
            }
          })
        } else {
          showSweetAlert({
            type: 'error',
            title: 'Failed!',
            message: 'Gagal menghapus SOAP',
            confirmText: 'OK',
            cancelText: ''
          })
        }
      } catch (err) {
        console.error('Delete SOAP error:', err)
        showSweetAlert({
          type: 'error',
          title: 'Error!',
          message: err.response?.data?.message || 'Terjadi kesalahan saat menghapus SOAP',
          confirmText: 'OK',
          cancelText: ''
        })
      }
    }
  })
}

const clearForm = () => {
  form.value = {
    no_rawat: patient.value.no_rawat,
    subjective: '',
    objective: '',
    assessment: '',
    planning: '',
    instruksi: '',
    evaluasi: '',
    tensi: '',
    suhu: '',
    nadi: '',
    respirasi: '',
    tinggi: '',
    berat: ''
  }
  // Reset edit mode
  isEditMode.value = false
  editingSOAP.value = null
  error.value = ''
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const formatObjektif = (pemeriksaan) => {
  let html = pemeriksaan.pemeriksaan || ''

  // Add vital signs if available
  const vitals = []
  if (pemeriksaan.alergi) vitals.push(`Alergi : ${pemeriksaan.alergi}`)
  if (pemeriksaan.suhu_tubuh) vitals.push(`Suhu(C) : ${pemeriksaan.suhu_tubuh}`)
  if (pemeriksaan.tensi) vitals.push(`Tensi : ${pemeriksaan.tensi}`)
  if (pemeriksaan.nadi) vitals.push(`Nadi(/menit) : ${pemeriksaan.nadi}`)
  if (pemeriksaan.respirasi) vitals.push(`Respirasi(/menit) : ${pemeriksaan.respirasi}`)
  if (pemeriksaan.tinggi) vitals.push(`Tinggi(Cm) : ${pemeriksaan.tinggi}`)
  if (pemeriksaan.berat) vitals.push(`Berat(Kg) : ${pemeriksaan.berat}`)
  if (pemeriksaan.spo2) vitals.push(`SpO2(%) : ${pemeriksaan.spo2}`)
  if (pemeriksaan.gcs) vitals.push(`GCS(E,V,M) : ${pemeriksaan.gcs}`)
  if (pemeriksaan.kesadaran) vitals.push(`Kesadaran : ${pemeriksaan.kesadaran}`)

  if (vitals.length > 0) {
    html += '<br>' + vitals.join('<br>')
  }

  return html.replace(/\n/g, '<br>')
}

const showRiwayatSOAPIE = async () => {
  showModalSOAPIE.value = true
  loadingSOAPIE.value = true

  try {
    const { data } = await apiClient.get(`/soap/riwayat-soapie/${patient.value.no_rkm_medis}`)

    if (data.success) {
      riwayatSOAPIE.value = data.data
    }
  } catch (err) {
    console.error('Load SOAPIE error:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Gagal memuat riwayat SOAPIE',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingSOAPIE.value = false
  }
}

const closeModalSOAPIE = () => {
  showModalSOAPIE.value = false
}

// RUJUK INTERNAL FUNCTIONS
const showRujukInternalModal = async () => {
  // Validasi
  if (!patient.value.no_rawat || !patient.value.no_rkm_medis || !patient.value.nm_pasien) {
    showSweetAlert({
      type: 'warning',
      title: 'Peringatan',
      message: 'Data pasien tidak lengkap',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  showModalRujukInternal.value = true

  // Load Poliklinik
  await loadPoliklinik()
}

const loadPoliklinik = async () => {
  try {
    const { data } = await apiClient.get('/poliklinik')
    if (data.success) {
      poliList.value = data.data
    }
  } catch (err) {
    console.error('Error loading poliklinik:', err)
  }
}

const onPoliChange = async () => {
  // Reset dokter saat poli berubah
  rujukInternalForm.value.kd_dokter = ''
  dokterList.value = []

  if (rujukInternalForm.value.kd_poli) {
    await loadDokterByPoli(rujukInternalForm.value.kd_poli)
  }
}

const loadDokterByPoli = async (kd_poli) => {
  try {
    const { data } = await apiClient.get(`/dokter/bypoli/${kd_poli}`)
    if (data.success) {
      dokterList.value = data.data
    }
  } catch (err) {
    console.error('Error loading dokter:', err)
  }
}

const submitRujukInternal = async () => {
  // Validasi
  if (!rujukInternalForm.value.kd_poli) {
    showSweetAlert({
      type: 'warning',
      title: 'Peringatan',
      message: 'Poliklinik tujuan harus dipilih',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (!rujukInternalForm.value.kd_dokter) {
    showSweetAlert({
      type: 'warning',
      title: 'Peringatan',
      message: 'Dokter tujuan harus dipilih',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  loadingRujukInternal.value = true

  try {
    const { data } = await apiClient.post('/rujukan-internal', {
      no_rawat: patient.value.no_rawat,
      kd_dokter: rujukInternalForm.value.kd_dokter,
      kd_poli: rujukInternalForm.value.kd_poli
    })

    if (data.success) {
      showSweetAlert({
        type: 'success',
        title: 'Berhasil!',
        message: 'Rujukan internal berhasil disimpan',
        confirmText: 'OK',
        cancelText: ''
      })
      closeModalRujukInternal()
    } else {
      showSweetAlert({
        type: 'error',
        title: 'Gagal!',
        message: data.message || 'Gagal menyimpan rujukan internal',
        confirmText: 'OK',
        cancelText: ''
      })
    }
  } catch (err) {
    console.error('Error saving rujukan internal:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan rujukan',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingRujukInternal.value = false
  }
}

const closeModalRujukInternal = () => {
  showModalRujukInternal.value = false
  rujukInternalForm.value = {
    kd_poli: '',
    kd_dokter: ''
  }
  dokterList.value = []
}

// LABORATORIUM FUNCTIONS
const searchPemeriksaanPK = async () => {
  loadingLabPK.value = true
  try {
    const { data } = await apiClient.get('/laboratorium/pemeriksaan-pk', {
      params: { search: searchLabPK.value }
    })
    if (data.success) {
      pemeriksaanPKList.value = data.data
    }
  } catch (err) {
    console.error('Error loading pemeriksaan PK:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Gagal memuat data pemeriksaan PK',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingLabPK.value = false
  }
}

const searchPemeriksaanPA = async () => {
  loadingLabPA.value = true
  try {
    const { data } = await apiClient.get('/laboratorium/pemeriksaan-pa', {
      params: { search: searchLabPA.value }
    })
    if (data.success) {
      pemeriksaanPAList.value = data.data
    }
  } catch (err) {
    console.error('Error loading pemeriksaan PA:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Gagal memuat data pemeriksaan PA',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingLabPA.value = false
  }
}

const toggleAllPK = (event) => {
  if (event.target.checked) {
    selectedPemeriksaanPK.value = [...pemeriksaanPKList.value]
  } else {
    selectedPemeriksaanPK.value = []
  }
}

const toggleAllPA = (event) => {
  if (event.target.checked) {
    selectedPemeriksaanPA.value = [...pemeriksaanPAList.value]
  } else {
    selectedPemeriksaanPA.value = []
  }
}

const removeSelectedPK = (item) => {
  const index = selectedPemeriksaanPK.value.findIndex(p => p.kd_jenis_prw === item.kd_jenis_prw)
  if (index > -1) {
    selectedPemeriksaanPK.value.splice(index, 1)
  }
}

const removeSelectedPA = (item) => {
  const index = selectedPemeriksaanPA.value.findIndex(p => p.kd_jenis_prw === item.kd_jenis_prw)
  if (index > -1) {
    selectedPemeriksaanPA.value.splice(index, 1)
  }
}

// Toggle pemeriksaan PK saat klik row
const togglePemeriksaanPK = (item) => {
  const index = selectedPemeriksaanPK.value.findIndex(p => p.kd_jenis_prw === item.kd_jenis_prw)
  if (index > -1) {
    selectedPemeriksaanPK.value.splice(index, 1)
  } else {
    selectedPemeriksaanPK.value.push(item)
  }
}

// Toggle pemeriksaan PA saat klik row
const togglePemeriksaanPA = (item) => {
  const index = selectedPemeriksaanPA.value.findIndex(p => p.kd_jenis_prw === item.kd_jenis_prw)
  if (index > -1) {
    selectedPemeriksaanPA.value.splice(index, 1)
  } else {
    selectedPemeriksaanPA.value.push(item)
  }
}

const submitPermintaanLab = async () => {
  // Validasi
  if (!labForm.value.diagnosa_klinis) {
    showSweetAlert({
      type: 'warning',
      title: 'Peringatan',
      message: 'Indikasi/Klinis harus diisi',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (selectedPemeriksaanPK.value.length === 0 && selectedPemeriksaanPA.value.length === 0) {
    showSweetAlert({
      type: 'warning',
      title: 'Peringatan',
      message: 'Pilih minimal satu pemeriksaan laboratorium',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  loadingSubmitLab.value = true

  try {
    const { data } = await apiClient.post('/laboratorium/permintaan', {
      no_rawat: patient.value.no_rawat,
      diagnosa_klinis: labForm.value.diagnosa_klinis,
      informasi_tambahan: labForm.value.informasi_tambahan,
      pemeriksaan_pk: selectedPemeriksaanPK.value.map(p => ({
        kd_jenis_prw: p.kd_jenis_prw,
        nm_perawatan: p.nm_perawatan,
        biaya: p.total_byr
      })),
      pemeriksaan_pa: selectedPemeriksaanPA.value.map(p => ({
        kd_jenis_prw: p.kd_jenis_prw,
        nm_perawatan: p.nm_perawatan,
        biaya: p.total_byr
      }))
    })

    if (data.success) {
      showSweetAlert({
        type: 'success',
        title: 'Berhasil!',
        message: 'Permintaan laboratorium berhasil disimpan',
        confirmText: 'OK',
        cancelText: ''
      })
      // Update no order
      labForm.value.noorder = data.noorder_pk || data.noorder_pa || ''
      // Clear selections
      selectedPemeriksaanPK.value = []
      selectedPemeriksaanPA.value = []
      // Load riwayat - reload both PK and PA
      await loadRiwayatLabPK()
      await loadRiwayatLabPA()
    } else {
      showSweetAlert({
        type: 'error',
        title: 'Gagal!',
        message: data.message || 'Gagal menyimpan permintaan laboratorium',
        confirmText: 'OK',
        cancelText: ''
      })
    }
  } catch (err) {
    console.error('Error saving permintaan lab:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan permintaan',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingSubmitLab.value = false
  }
}

const loadRiwayatPermintaanLab = async () => {
  try {
    const { data } = await apiClient.get(`/laboratorium/riwayat/${patient.value.no_rawat}`)
    if (data.success) {
      riwayatLabList.value = data.data
    }
  } catch (err) {
    console.error('Error loading riwayat lab:', err)
  }
}

const clearLabForm = () => {
  labForm.value = {
    noorder: '',
    diagnosa_klinis: '',
    informasi_tambahan: ''
  }
  selectedPemeriksaanPK.value = []
  selectedPemeriksaanPA.value = []
  pemeriksaanPKList.value = []
  pemeriksaanPAList.value = []
  searchLabPK.value = ''
  searchLabPA.value = ''
}

// Ref untuk grouped data dengan reactive showDetails
const groupedRiwayatLabPK = ref([])
const groupedRiwayatLabPA = ref([])

// Function untuk group Lab PK data
const processGroupedLabPK = () => {
  const grouped = []
  let currentPermintaan = null

  riwayatLabPKList.value.forEach(item => {
    if (item.type === 'header') {
      // Start new permintaan
      currentPermintaan = {
        ...item,
        items: []
      }
      grouped.push(currentPermintaan)
    } else if (item.type === 'pemeriksaan' && currentPermintaan) {
      // Add pemeriksaan item
      currentPermintaan.items.push({
        ...item,
        showDetails: false,
        details: []
      })
    } else if (item.type === 'pemeriksaan-header') {
      // Skip header rows
    } else if (item.type === 'detail' && currentPermintaan && currentPermintaan.items.length > 0) {
      // Add detail to last pemeriksaan item
      const lastItem = currentPermintaan.items[currentPermintaan.items.length - 1]
      lastItem.details.push(item)
    }
  })

  groupedRiwayatLabPK.value = grouped
}

// Function untuk group Lab PA data
const processGroupedLabPA = () => {
  const grouped = []
  let currentPermintaan = null

  riwayatLabPAList.value.forEach(item => {
    if (item.type === 'header') {
      // Start new permintaan
      currentPermintaan = {
        ...item,
        items: []
      }
      grouped.push(currentPermintaan)
    } else if (item.type === 'pemeriksaan' && currentPermintaan) {
      // Add pemeriksaan item
      currentPermintaan.items.push({
        ...item,
        showDetails: false,
        details: []
      })
    } else if (item.type === 'pemeriksaan-header') {
      // Skip header rows
    } else if (item.type === 'detail' && currentPermintaan && currentPermintaan.items.length > 0) {
      // Add detail to last pemeriksaan item
      const lastItem = currentPermintaan.items[currentPermintaan.items.length - 1]
      lastItem.details.push(item)
    }
  })

  groupedRiwayatLabPA.value = grouped
}

// Toggle lab detail visibility
const toggleLabDetail = (permIdx, itemIdx) => {
  const permintaan = groupedRiwayatLabPK.value[permIdx]
  if (permintaan && permintaan.items[itemIdx]) {
    permintaan.items[itemIdx].showDetails = !permintaan.items[itemIdx].showDetails
  }
}

// Toggle lab detail PA visibility
const toggleLabDetailPA = (permIdx, itemIdx) => {
  const permintaan = groupedRiwayatLabPA.value[permIdx]
  if (permintaan && permintaan.items[itemIdx]) {
    permintaan.items[itemIdx].showDetails = !permintaan.items[itemIdx].showDetails
  }
}

// Load Riwayat Lab PK
const loadRiwayatLabPK = async () => {
  if (!patient.value?.no_rawat) return

  loadingRiwayatPK.value = true
  riwayatLabPKList.value = []
  groupedRiwayatLabPK.value = []

  try {
    const { data } = await apiClient.get(`/laboratorium/riwayat/${patient.value.no_rawat}`)

    if (data.success) {
      riwayatLabPKList.value = data.data
      // Process grouping after data loaded
      processGroupedLabPK()
    }
  } catch (err) {
    console.error('Error loading riwayat Lab PK:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Gagal memuat riwayat Lab PK',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingRiwayatPK.value = false
  }
}

// Load Riwayat Lab PA
const loadRiwayatLabPA = async () => {
  if (!patient.value?.no_rawat) return

  loadingRiwayatPA.value = true
  riwayatLabPAList.value = []
  groupedRiwayatLabPA.value = []

  try {
    const { data } = await apiClient.get(`/laboratorium/riwayat-pa/${patient.value.no_rawat}`)

    if (data.success) {
      riwayatLabPAList.value = data.data
      // Process grouping after data loaded
      processGroupedLabPA()
    }
  } catch (err) {
    console.error('Error loading riwayat Lab PA:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Gagal memuat riwayat Lab PA',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingRiwayatPA.value = false
  }
}

// RADIOLOGI FUNCTIONS

// Computed: Total Biaya Radiologi
const totalBiayaRad = computed(() => {
  return selectedPemeriksaanRad.value.reduce((total, item) => total + parseFloat(item.total_byr || 0), 0)
})

// Search Pemeriksaan Radiologi
const searchPemeriksaanRad = async () => {
  if (searchRad.value.length < 2) {
    pemeriksaanRadList.value = []
    return
  }

  try {
    const { data } = await apiClient.get('/radiologi/pemeriksaan', {
      params: { search: searchRad.value }
    })

    if (data.success) {
      pemeriksaanRadList.value = data.data
    }
  } catch (err) {
    console.error('Error searching radiologi:', err)
  }
}

// Check if radiologi is selected
const isSelectedRad = (item) => {
  return selectedPemeriksaanRad.value.some(s => s.kd_jenis_prw === item.kd_jenis_prw)
}

// Toggle select radiologi
const toggleSelectRad = (item) => {
  const index = selectedPemeriksaanRad.value.findIndex(s => s.kd_jenis_prw === item.kd_jenis_prw)
  if (index > -1) {
    selectedPemeriksaanRad.value.splice(index, 1)
  } else {
    selectedPemeriksaanRad.value.push(item)
  }
}

// Remove selected radiologi
const removeSelectedRad = (index) => {
  selectedPemeriksaanRad.value.splice(index, 1)
}

// Submit Permintaan Radiologi
const submitPermintaanRad = async () => {
  if (!patient.value?.no_rawat) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Data pasien tidak ditemukan',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (selectedPemeriksaanRad.value.length === 0) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Pilih minimal 1 pemeriksaan radiologi',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  loadingSubmitRad.value = true

  try {
    const { data } = await apiClient.post('/radiologi/permintaan', {
      no_rawat: patient.value.no_rawat,
      diagnosa_klinis: radForm.value.diagnosa_klinis,
      informasi_tambahan: radForm.value.informasi_tambahan,
      pemeriksaan: selectedPemeriksaanRad.value.map(p => ({
        kd_jenis_prw: p.kd_jenis_prw,
        nm_perawatan: p.nm_perawatan,
        biaya: p.total_byr
      }))
    })

    if (data.success) {
      showSweetAlert({
        type: 'success',
        title: 'Berhasil!',
        message: 'Permintaan radiologi berhasil disimpan',
        confirmText: 'OK',
        cancelText: ''
      })
      // Update no order
      radForm.value.noorder = data.noorder || ''
      // Clear selections
      selectedPemeriksaanRad.value = []
      pemeriksaanRadList.value = []
      searchRad.value = ''
      // Load riwayat
      await loadRiwayatRad()
    } else {
      showSweetAlert({
        type: 'error',
        title: 'Gagal!',
        message: data.message || 'Gagal menyimpan permintaan radiologi',
        confirmText: 'OK',
        cancelText: ''
      })
    }
  } catch (err) {
    console.error('Error saving permintaan radiologi:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan permintaan radiologi',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingSubmitRad.value = false
  }
}

// Load Riwayat Radiologi
const loadRiwayatRad = async () => {
  if (!patient.value?.no_rawat) return

  loadingRiwayatRad.value = true
  riwayatRadList.value = []

  try {
    const { data } = await apiClient.get(`/radiologi/riwayat/${patient.value.no_rawat}`)

    if (data.success) {
      riwayatRadList.value = data.data
    }
  } catch (err) {
    console.error('Error loading riwayat radiologi:', err)
  } finally {
    loadingRiwayatRad.value = false
  }
}

// Clear Radiologi Form
const clearRadForm = () => {
  radForm.value = {
    noorder: '',
    informasi_tambahan: '',
    diagnosa_klinis: ''
  }
  selectedPemeriksaanRad.value = []
  pemeriksaanRadList.value = []
  searchRad.value = ''
}

// ==================== TINDAKAN FUNCTIONS ====================

// Computed: Total Biaya Tindakan
const totalBiayaTindakan = computed(() => {
  return selectedTindakan.value.reduce((total, item) => total + parseFloat(item.total_byrdr || 0), 0)
})

// Search Jenis Tindakan
const searchJenisTindakan = async () => {
  if (searchTindakan.value.length < 2) {
    jenisTindakanList.value = []
    return
  }

  try {
    const { data } = await apiClient.get('/tindakan/jenis-perawatan', {
      params: { search: searchTindakan.value }
    })

    if (data.success) {
      jenisTindakanList.value = data.data
    }
  } catch (error) {
    console.error('Error searching jenis tindakan:', error)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Gagal memuat data jenis tindakan',
      confirmText: 'OK',
      cancelText: ''
    })
  }
}

// Check if item is selected
const isSelectedTindakan = (item) => {
  return selectedTindakan.value.some(selected => selected.kd_jenis_prw === item.kd_jenis_prw)
}

// Toggle select tindakan
const toggleSelectTindakan = (item) => {
  const index = selectedTindakan.value.findIndex(selected => selected.kd_jenis_prw === item.kd_jenis_prw)
  if (index > -1) {
    selectedTindakan.value.splice(index, 1)
  } else {
    selectedTindakan.value.push({ ...item })
  }
}

// Remove selected tindakan
const removeSelectedTindakan = (index) => {
  selectedTindakan.value.splice(index, 1)
}

// Submit Tindakan
const submitTindakan = async () => {
  if (!patient.value?.no_rawat) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Data pasien tidak ditemukan',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (selectedTindakan.value.length === 0) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Pilih minimal 1 tindakan medis',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  loadingSubmitTindakan.value = true

  try {
    const payload = {
      no_rawat: patient.value.no_rawat,
      tindakan: selectedTindakan.value.map(item => ({
        kd_jenis_prw: item.kd_jenis_prw,
        nm_perawatan: item.nm_perawatan,
        biaya_rawat: item.total_byrdr
      }))
    }

    const { data } = await apiClient.post('/tindakan/simpan', payload)

    if (data.success) {
      showSweetAlert({
        type: 'success',
        title: 'Berhasil!',
        message: 'Tindakan medis berhasil disimpan',
        confirmText: 'OK',
        cancelText: ''
      })
      // Clear selections
      clearTindakanForm()
      // Load riwayat
      await loadRiwayatTindakan()
    } else {
      showSweetAlert({
        type: 'error',
        title: 'Gagal!',
        message: data.message || 'Gagal menyimpan tindakan medis',
        confirmText: 'OK',
        cancelText: ''
      })
    }
  } catch (err) {
    console.error('Error saving tindakan:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan tindakan medis',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingSubmitTindakan.value = false
  }
}

// Load Riwayat Tindakan
const loadRiwayatTindakan = async () => {
  loadingRiwayatTindakan.value = true

  try {
    const { data } = await apiClient.get(`/tindakan/riwayat/${patient.value.no_rawat}`)

    if (data.success) {
      riwayatTindakanList.value = data.data
    }
  } catch (error) {
    console.error('Error loading riwayat tindakan:', error)
  } finally {
    loadingRiwayatTindakan.value = false
  }
}

// Clear Tindakan Form
const clearTindakanForm = () => {
  selectedTindakan.value = []
  jenisTindakanList.value = []
  searchTindakan.value = ''
}

// ==================== USG FUNCTIONS ====================

// Submit USG
const submitUsg = async () => {
  if (!patient.value?.no_rawat) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Data pasien tidak ditemukan',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (!usgForm.value.hasil) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Hasil pemeriksaan USG harus diisi',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  loadingSubmitUsg.value = true

  try {
    // Gunakan FormData untuk mengirim file
    const formData = new FormData()
    formData.append('no_rawat', patient.value.no_rawat)
    formData.append('jenis_pemeriksaan', 'USG')
    formData.append('hasil', usgForm.value.hasil)

    // Tambahkan foto jika ada
    if (usgPhotos.value.length > 0) {
      usgPhotos.value.forEach((photo, index) => {
        formData.append('photos', photo.file)
      })
    }

    const { data } = await apiClient.post('/hasil-penunjang/simpan', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (data.success) {
      showSweetAlert({
        type: 'success',
        title: 'Berhasil!',
        message: 'Hasil pemeriksaan USG berhasil disimpan',
        confirmText: 'OK',
        cancelText: ''
      })
      // Clear form
      clearUsgForm()
      // Load riwayat
      await loadRiwayatUsg()
    } else {
      showSweetAlert({
        type: 'error',
        title: 'Gagal!',
        message: data.message || 'Gagal menyimpan hasil pemeriksaan USG',
        confirmText: 'OK',
        cancelText: ''
      })
    }
  } catch (err) {
    console.error('Error saving hasil USG:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan hasil USG',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingSubmitUsg.value = false
  }
}

// Load Riwayat USG
const loadRiwayatUsg = async () => {
  if (!patient.value?.no_rawat) return

  loadingRiwayatUsg.value = true

  try {
    const { data } = await apiClient.get(`/hasil-penunjang/riwayat/${patient.value.no_rawat}?jenis=USG`)

    if (data.success) {
      riwayatUsgList.value = data.data
    }
  } catch (error) {
    console.error('Error loading riwayat USG:', error)
  } finally {
    loadingRiwayatUsg.value = false
  }
}

// Handle USG Photo Upload/Capture
const handleUsgPhotoSelect = (event) => {
  const files = event.target.files
  if (files && files.length > 0) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i]
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          usgPhotos.value.push({
            file: file,
            preview: e.target.result,
            name: file.name
          })
        }
        reader.readAsDataURL(file)
      }
    }
  }
  // Reset input untuk bisa upload file yang sama lagi
  if (event.target) {
    event.target.value = ''
  }
}

const removeUsgPhoto = (index) => {
  usgPhotos.value.splice(index, 1)
}

const openUsgCamera = () => {
  if (usgPhotoInput.value) {
    usgPhotoInput.value.click()
  }
}

// Clear USG Form
const clearUsgForm = () => {
  usgForm.value = {
    hasil: ''
  }
  usgPhotos.value = []
}

// ==================== EKG FUNCTIONS ====================

// Submit EKG
const submitEkg = async () => {
  if (!patient.value?.no_rawat) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Data pasien tidak ditemukan',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (!ekgForm.value.hasil) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Hasil pemeriksaan EKG harus diisi',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  loadingSubmitEkg.value = true

  try {
    // Gunakan FormData untuk mengirim file
    const formData = new FormData()
    formData.append('no_rawat', patient.value.no_rawat)
    formData.append('jenis_pemeriksaan', 'EKG')
    formData.append('hasil', ekgForm.value.hasil)

    // Tambahkan foto jika ada
    if (ekgPhotos.value.length > 0) {
      ekgPhotos.value.forEach((photo, index) => {
        formData.append('photos', photo.file)
      })
    }

    const { data } = await apiClient.post('/hasil-penunjang/simpan', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (data.success) {
      showSweetAlert({
        type: 'success',
        title: 'Berhasil!',
        message: 'Hasil pemeriksaan EKG berhasil disimpan',
        confirmText: 'OK',
        cancelText: ''
      })
      // Clear form
      clearEkgForm()
      // Load riwayat
      await loadRiwayatEkg()
    } else {
      showSweetAlert({
        type: 'error',
        title: 'Gagal!',
        message: data.message || 'Gagal menyimpan hasil pemeriksaan EKG',
        confirmText: 'OK',
        cancelText: ''
      })
    }
  } catch (err) {
    console.error('Error saving hasil EKG:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Terjadi kesalahan saat menyimpan hasil EKG',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingSubmitEkg.value = false
  }
}

// Load Riwayat EKG
const loadRiwayatEkg = async () => {
  if (!patient.value?.no_rawat) return

  loadingRiwayatEkg.value = true

  try {
    const { data } = await apiClient.get(`/hasil-penunjang/riwayat/${patient.value.no_rawat}?jenis=EKG`)

    if (data.success) {
      riwayatEkgList.value = data.data
    }
  } catch (error) {
    console.error('Error loading riwayat EKG:', error)
  } finally {
    loadingRiwayatEkg.value = false
  }
}

// Handle EKG Photo Upload/Capture
const handleEkgPhotoSelect = (event) => {
  const files = event.target.files
  if (files && files.length > 0) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i]
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          ekgPhotos.value.push({
            file: file,
            preview: e.target.result,
            name: file.name
          })
        }
        reader.readAsDataURL(file)
      }
    }
  }
  // Reset input untuk bisa upload file yang sama lagi
  if (event.target) {
    event.target.value = ''
  }
}

const removeEkgPhoto = (index) => {
  ekgPhotos.value.splice(index, 1)
}

const openEkgCamera = () => {
  if (ekgPhotoInput.value) {
    ekgPhotoInput.value.click()
  }
}

// Clear EKG Form
const clearEkgForm = () => {
  ekgForm.value = {
    hasil: ''
  }
  ekgPhotos.value = []
}

// RIWAYAT RESEP FUNCTIONS
const openModalRiwayatResep = async () => {
  showModalRiwayatResep.value = true
  loadingRiwayatResep.value = true
  riwayatResep.value = []

  try {
    const { data } = await apiClient.get(`/resep/riwayat/${patient.value.no_rkm_medis}`)

    if (data.success) {
      riwayatResep.value = data.data
    }
  } catch (err) {
    console.error('Load riwayat resep error:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Gagal memuat riwayat resep',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    loadingRiwayatResep.value = false
  }
}

const closeModalRiwayatResep = () => {
  showModalRiwayatResep.value = false
}

// Load riwayat resep for tab view
const loadRiwayatResepTab = async () => {
  loadingRiwayatResep.value = true
  riwayatResep.value = []

  try {
    const { data } = await apiClient.get(`/resep/riwayat/${patient.value.no_rkm_medis}`)

    if (data.success) {
      // Filter hanya resep hari ini - gunakan tanggal lokal, bukan UTC
      const today = new Date()
      const year = today.getFullYear()
      const month = String(today.getMonth() + 1).padStart(2, '0')
      const day = String(today.getDate()).padStart(2, '0')
      const todayStr = `${year}-${month}-${day}` // Format: YYYY-MM-DD (local timezone)

      riwayatResep.value = data.data.filter(resep => {
        // Normalisasi format tanggal dari berbagai kemungkinan format
        let resepDate = resep.tgl_peresepan

        // Jika format DD-MM-YYYY atau DD/MM/YYYY, konversi ke YYYY-MM-DD
        if (resepDate && (resepDate.includes('-') || resepDate.includes('/'))) {
          const parts = resepDate.split(/[-\/]/)
          if (parts.length === 3) {
            // Cek apakah format DD-MM-YYYY atau YYYY-MM-DD
            if (parts[0].length === 4) {
              // Format YYYY-MM-DD
              resepDate = resepDate.replace(/\//g, '-')
            } else {
              // Format DD-MM-YYYY atau DD/MM/YYYY, konversi ke YYYY-MM-DD
              resepDate = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`
            }
          }
        }

        console.log('Comparing:', resepDate, 'with', todayStr)
        return resepDate === todayStr
      })

      console.log('Filtered riwayat resep:', riwayatResep.value.length, 'dari total', data.data.length)
    }
  } catch (err) {
    console.error('Load riwayat resep error:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error',
      message: 'Gagal memuat riwayat resep'
    })
  } finally {
    loadingRiwayatResep.value = false
  }
}

// Reset form non racikan
const resetFormResepNonRacikan = () => {
  resepNonRacikan.value = []
  searchObatNonRacikan.value = ''
  obatList.value = []
  showObatDropdown.value = false
}

// Reset form racikan
const resetFormRacikan = () => {
  racikan.value = {
    nama_racikan: '',
    keterangan: '',
    metode_racik: '',
    jml_dr: 1,
    aturan_pakai: '',
    detail: []
  }
  searchObatRacikan.value = ''
  obatListRacikan.value = []
  showObatDropdownRacikan.value = false
}

// Validate racikan form
const isRacikanValid = () => {
  return racikan.value.nama_racikan &&
         racikan.value.jml_dr > 0 &&
         racikan.value.aturan_pakai &&
         racikan.value.detail.length > 0
}

const copyResepToForm = (resep) => {
  // Copy non-racikan
  if (resep.non_racikan && resep.non_racikan.length > 0) {
    resepNonRacikan.value = resep.non_racikan.map(obat => ({
      kode_brng: obat.kode_brng,
      nama_brng: obat.nama_brng,
      jml: obat.jml,
      kode_sat: obat.kode_sat,
      aturan_pakai: obat.aturan_pakai,
      stok: obat.stok || 0,
      kapasitas: obat.kapasitas || 1
    }))
  }

  // Copy racikan - hanya copy racikan pertama jika ada
  if (resep.racikan && resep.racikan.length > 0) {
    const racikanData = resep.racikan[0]
    racikan.value = {
      nama_racikan: racikanData.nama_racik,
      keterangan: racikanData.keterangan || '',
      metode_racik: racikanData.metode || '',
      jml_dr: racikanData.jml_dr,
      aturan_pakai: racikanData.aturan_pakai,
      detail: racikanData.detail.map(detail => ({
        kode_brng: detail.kode_brng,
        nama_brng: detail.nama_brng,
        jml: detail.jml,
        kode_sat: detail.kode_sat,
        kandungan: detail.kandungan || '',
        kapasitas: detail.kapasitas || 1,
        stok: detail.stok || 0
      }))
    }
  }

  // Close riwayat modal
  closeModalRiwayatResep()

  // No notification needed - user can see the copied data directly in the form
}

const copyToForm = (pemeriksaan) => {
  // Populate form with data from selected pemeriksaan
  form.value = {
    no_rawat: patient.value.no_rawat, // Keep current no_rawat
    subjective: pemeriksaan.keluhan || '',
    objective: pemeriksaan.pemeriksaan || '',
    assessment: pemeriksaan.penilaian || '',
    planning: pemeriksaan.rtl || '',
    instruksi: pemeriksaan.instruksi || '',
    evaluasi: pemeriksaan.evaluasi || '',
    tensi: pemeriksaan.tensi || '',
    suhu: pemeriksaan.suhu_tubuh || '',
    nadi: pemeriksaan.nadi || '',
    respirasi: pemeriksaan.respirasi || '',
    tinggi: pemeriksaan.tinggi || '',
    berat: pemeriksaan.berat || ''
  }

  // Close modal
  closeModalSOAPIE()

  // Scroll to form
  window.scrollTo({ top: 0, behavior: 'smooth' })

  // Show notification
  showSweetAlert({
    type: 'success',
    title: 'Success!',
    message: 'Data SOAP berhasil di-copy ke form!',
    confirmText: 'OK',
    cancelText: ''
  })
}

const closeSuccessAlert = () => {
  showSuccessAlert.value = false
}

const lanjutkanResep = () => {
  showSuccessAlert.value = false
  showModalResep.value = true
}

const closeModalResep = () => {
  showModalResep.value = false
  activeResepTab.value = 'non-racikan'
  // Reset Non Racikan
  searchObatNonRacikan.value = ''
  resepNonRacikan.value = []
  obatList.value = []
  showObatDropdown.value = false
  // Reset Racikan
  searchObatRacikan.value = ''
  obatListRacikan.value = []
  showObatDropdownRacikan.value = false
  racikan.value = {
    nama_racikan: '',
    keterangan: '',
    metode_racik: '',
    jml_dr: 1,
    aturan_pakai: '',
    detail: []
  }
  // Legacy
  resepSearch.value = ''
  resepObat.value = []
}

// NON RACIKAN FUNCTIONS
const cariObatNonRacikan = async () => {
  console.log('cariObatNonRacikan called', searchObatNonRacikan.value)

  if (!searchObatNonRacikan.value || searchObatNonRacikan.value.length < 2) {
    showObatDropdown.value = false
    return
  }

  try {
    console.log('Fetching obat...')
    const { data } = await apiClient.get('/obat/search', {
      params: { q: searchObatNonRacikan.value }
    })
    console.log('Obat search response:', data)

    if (data.success) {
      obatList.value = data.data
      showObatDropdown.value = true
      console.log('Obat list:', obatList.value)
    }
  } catch (err) {
    console.error('Search obat error:', err)
    console.error('Error response:', err.response)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Gagal mencari obat: ' + (err.response?.data?.message || err.message),
      confirmText: 'OK',
      cancelText: ''
    })
  }
}

const pilihObatNonRacikan = (obat) => {
  // Check if obat already exists
  const exists = resepNonRacikan.value.find(item => item.kode_brng === obat.kode_brng)
  if (exists) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Obat sudah ada dalam daftar!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Open modal untuk input jumlah dan aturan pakai
  selectedObatNonRacikan.value = obat
  inputObatForm.value = {
    jml: 1,
    aturan_pakai: ''
  }
  showModalInputObat.value = true
  showObatDropdown.value = false
}

const confirmTambahObat = () => {
  if (!inputObatForm.value.jml || inputObatForm.value.jml < 1) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Jumlah harus diisi minimal 1!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (!inputObatForm.value.aturan_pakai || inputObatForm.value.aturan_pakai.trim() === '') {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Aturan pakai harus diisi!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Validasi stok
  if (inputObatForm.value.jml > selectedObatNonRacikan.value.stok) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: `Jumlah melebihi stok tersedia (${selectedObatNonRacikan.value.stok})!`,
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Add to list
  resepNonRacikan.value.push({
    kode_brng: selectedObatNonRacikan.value.kode_brng,
    nama_brng: selectedObatNonRacikan.value.nama_brng,
    kode_sat: selectedObatNonRacikan.value.kode_sat,
    stok: selectedObatNonRacikan.value.stok,
    harga: selectedObatNonRacikan.value.harga,
    jml: inputObatForm.value.jml,
    aturan_pakai: inputObatForm.value.aturan_pakai
  })

  // Clear and close
  showModalInputObat.value = false
  selectedObatNonRacikan.value = null
  searchObatNonRacikan.value = ''
  inputObatForm.value = {
    jml: 1,
    aturan_pakai: ''
  }
}

const closeModalInputObat = () => {
  showModalInputObat.value = false
  selectedObatNonRacikan.value = null
  inputObatForm.value = {
    jml: 1,
    aturan_pakai: ''
  }
}

const hapusObatNonRacikan = (index) => {
  resepNonRacikan.value.splice(index, 1)
}

const submitResepNonRacikan = async () => {
  if (resepNonRacikan.value.length === 0) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Belum ada obat yang dipilih!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Validate aturan pakai
  for (const obat of resepNonRacikan.value) {
    if (!obat.aturan_pakai) {
      showSweetAlert({
        type: 'warning',
        title: 'Warning!',
        message: 'Harap lengkapi aturan pakai untuk semua obat!',
        confirmText: 'OK',
        cancelText: ''
      })
      return
    }
  }

  try {
    const payload = {
      no_rawat: patient.value.no_rawat,
      non_racikan: resepNonRacikan.value,
      racikan: [] // Empty array for racikan
    }

    const { data } = await apiClient.post('/resep', payload)

    if (data.success) {
      // Clear form
      resepNonRacikan.value = []

      // Close modal first
      closeModalResep()

      // Then show success alert
      setTimeout(() => {
        showSweetAlert({
          type: 'success',
          title: 'Success!',
          message: 'Resep obat berhasil disimpan! No Resep: ' + data.no_resep,
          confirmText: 'OK',
          cancelText: ''
        })
      }, 300)
    }
  } catch (err) {
    console.error('Submit resep error:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Gagal menyimpan resep',
      confirmText: 'OK',
      cancelText: ''
    })
  }
}

// RACIKAN FUNCTIONS
const cariObatRacikan = async () => {
  if (!searchObatRacikan.value || searchObatRacikan.value.length < 2) {
    showObatDropdownRacikan.value = false
    return
  }

  try {
    const { data } = await apiClient.get('/obat/search', {
      params: { q: searchObatRacikan.value }
    })
    if (data.success) {
      obatListRacikan.value = data.data
      showObatDropdownRacikan.value = true
    }
  } catch (err) {
    console.error('Search obat error:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Gagal mencari obat',
      confirmText: 'OK',
      cancelText: ''
    })
  }
}

const pilihObatRacikan = (obat) => {
  // Check if obat already exists in racikan detail
  const exists = racikan.value.detail.find(item => item.kode_brng === obat.kode_brng)
  if (exists) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Obat sudah ada dalam racikan!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Open modal untuk input kandungan dan jumlah
  selectedObatRacikan.value = obat
  inputObatRacikanForm.value = {
    kandungan: '',
    jml: 0
  }
  showModalInputObatRacikan.value = true
  showObatDropdownRacikan.value = false
}

// Hitung otomatis jumlah obat berdasarkan: (jml_racikan × kandungan) / kapasitas
const hitungJumlahObatRacikan = () => {
  let kandungan = 0
  const kandunganInput = String(inputObatRacikanForm.value.kandungan || '0').trim()

  // Check if input is a fraction (e.g., 2/3, 1/2)
  if (kandunganInput.includes('/')) {
    const parts = kandunganInput.split('/')
    if (parts.length === 2) {
      const numerator = parseFloat(parts[0])
      const denominator = parseFloat(parts[1])
      if (!isNaN(numerator) && !isNaN(denominator) && denominator !== 0) {
        kandungan = numerator / denominator
      }
    }
  } else {
    // Regular number
    kandungan = parseFloat(kandunganInput) || 0
  }

  const jmlRacikan = racikan.value.jml_dr || 1
  const kapasitas = selectedObatRacikan.value?.kapasitas || 1

  // Formula: (jumlah racikan × kandungan) / kapasitas
  const jumlah = (jmlRacikan * kandungan) / kapasitas

  // Round to 2 decimal places
  inputObatRacikanForm.value.jml = Math.round(jumlah * 100) / 100
}

const confirmTambahObatRacikan = () => {
  // Validasi kandungan
  const kandunganInput = String(inputObatRacikanForm.value.kandungan || '').trim()
  if (!kandunganInput) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Kandungan harus diisi!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Validate fraction or number format
  let kandunganValue = 0
  if (kandunganInput.includes('/')) {
    const parts = kandunganInput.split('/')
    if (parts.length !== 2 || isNaN(parts[0]) || isNaN(parts[1]) || parseFloat(parts[1]) === 0) {
      showSweetAlert({
        type: 'warning',
        title: 'Warning!',
        message: 'Format pecahan tidak valid! Gunakan format seperti: 2/3, 1/2',
        confirmText: 'OK',
        cancelText: ''
      })
      return
    }
    kandunganValue = parseFloat(parts[0]) / parseFloat(parts[1])
  } else {
    kandunganValue = parseFloat(kandunganInput)
    if (isNaN(kandunganValue)) {
      showSweetAlert({
        type: 'warning',
        title: 'Warning!',
        message: 'Kandungan harus berupa angka atau pecahan!',
        confirmText: 'OK',
        cancelText: ''
      })
      return
    }
  }

  if (kandunganValue < 0.1) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Kandungan minimal 0.1!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (!inputObatRacikanForm.value.jml || inputObatRacikanForm.value.jml < 0.1) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Jumlah harus diisi minimal 0.1!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Validasi stok
  if (inputObatRacikanForm.value.jml > selectedObatRacikan.value.stok) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: `Jumlah melebihi stok tersedia (${selectedObatRacikan.value.stok})!`,
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Add to racikan detail
  racikan.value.detail.push({
    kode_brng: selectedObatRacikan.value.kode_brng,
    nama_brng: selectedObatRacikan.value.nama_brng,
    kode_sat: selectedObatRacikan.value.kode_sat,
    kapasitas: selectedObatRacikan.value.kapasitas,
    stok: selectedObatRacikan.value.stok,
    harga: selectedObatRacikan.value.harga,
    kandungan: inputObatRacikanForm.value.kandungan,
    jml: inputObatRacikanForm.value.jml
  })

  // Clear and close
  showModalInputObatRacikan.value = false
  selectedObatRacikan.value = null
  searchObatRacikan.value = ''
  inputObatRacikanForm.value = {
    kandungan: '',
    jml: 0
  }
}

const closeModalInputObatRacikan = () => {
  showModalInputObatRacikan.value = false
  selectedObatRacikan.value = null
  inputObatRacikanForm.value = {
    kandungan: '',
    jml: 0
  }
}

const hapusObatRacikan = (index) => {
  racikan.value.detail.splice(index, 1)
}

const submitResepRacikan = async () => {
  // Validasi
  if (!racikan.value.nama_racikan) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Nama racikan harus diisi!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (!racikan.value.jml_dr || racikan.value.jml_dr < 1) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Jumlah racikan harus diisi!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (!racikan.value.aturan_pakai) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Aturan pakai harus diisi!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  if (racikan.value.detail.length === 0) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Detail obat racikan belum diisi!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  try {
    const payload = {
      no_rawat: patient.value.no_rawat,
      non_racikan: [], // Empty array for non-racikan
      racikan: [{
        nama_racikan: racikan.value.nama_racikan,
        metode_racik: racikan.value.metode_racik,
        jml_dr: racikan.value.jml_dr,
        aturan_pakai: racikan.value.aturan_pakai,
        detail: racikan.value.detail
      }]
    }

    const { data } = await apiClient.post('/resep', payload)

    if (data.success) {
      // Clear form
      racikan.value = {
        nama_racikan: '',
        keterangan: '',
        metode_racik: '',
        jml_dr: 1,
        aturan_pakai: '',
        detail: []
      }

      // Close modal first
      closeModalResep()

      // Then show success alert
      setTimeout(() => {
        showSweetAlert({
          type: 'success',
          title: 'Success!',
          message: 'Resep racikan berhasil disimpan! No Resep: ' + data.no_resep,
          confirmText: 'OK',
          cancelText: ''
        })
      }, 300)
    }
  } catch (err) {
    console.error('Submit racikan error:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Gagal menyimpan racikan',
      confirmText: 'OK',
      cancelText: ''
    })
  }
}

// UNIFIED SUBMIT FUNCTION - Save both non-racikan and racikan together
const submitResepUnified = async () => {
  // Validasi: minimal ada non-racikan atau racikan
  const hasNonRacikan = resepNonRacikan.value.length > 0
  const hasRacikan = racikan.value.detail.length > 0

  if (!hasNonRacikan && !hasRacikan) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Belum ada obat yang dipilih! Pilih minimal non-racikan atau racikan.',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  // Validate non-racikan aturan pakai
  if (hasNonRacikan) {
    for (const obat of resepNonRacikan.value) {
      if (!obat.aturan_pakai) {
        showSweetAlert({
          type: 'warning',
          title: 'Warning!',
          message: 'Harap lengkapi aturan pakai untuk semua obat non-racikan!',
          confirmText: 'OK',
          cancelText: ''
        })
        return
      }
    }
  }

  // Validate racikan if has detail
  if (hasRacikan) {
    if (!racikan.value.nama_racikan) {
      showSweetAlert({
        type: 'warning',
        title: 'Warning!',
        message: 'Nama racikan harus diisi!',
        confirmText: 'OK',
        cancelText: ''
      })
      return
    }

    if (!racikan.value.jml_dr || racikan.value.jml_dr < 1) {
      showSweetAlert({
        type: 'warning',
        title: 'Warning!',
        message: 'Jumlah racikan harus diisi!',
        confirmText: 'OK',
        cancelText: ''
      })
      return
    }

    if (!racikan.value.aturan_pakai) {
      showSweetAlert({
        type: 'warning',
        title: 'Warning!',
        message: 'Aturan pakai racikan harus diisi!',
        confirmText: 'OK',
        cancelText: ''
      })
      return
    }
  }

  try {
    // Build racikan array
    const racikanArray = hasRacikan ? [{
      nama_racikan: racikan.value.nama_racikan,
      metode_racik: racikan.value.metode_racik,
      jml_dr: racikan.value.jml_dr,
      aturan_pakai: racikan.value.aturan_pakai,
      detail: racikan.value.detail
    }] : []

    const payload = {
      no_rawat: patient.value.no_rawat,
      non_racikan: resepNonRacikan.value,
      racikan: racikanArray
    }

    const { data } = await apiClient.post('/resep', payload)

    if (data.success) {
      // Clear both forms first
      resepNonRacikan.value = []
      racikan.value = {
        nama_racikan: '',
        keterangan: '',
        metode_racik: '',
        jml_dr: 1,
        aturan_pakai: '',
        detail: []
      }

      // Close modal first
      closeModalResep()

      // Then show success alert (after modal closes, so z-index is correct)
      setTimeout(() => {
        showSweetAlert({
          type: 'success',
          title: 'Success!',
          message: `Resep berhasil disimpan!\nNo Resep: ${data.no_resep}\n${hasNonRacikan ? '✓ Non-Racikan' : ''}${hasNonRacikan && hasRacikan ? ' & ' : ''}${hasRacikan ? '✓ Racikan' : ''}`,
          confirmText: 'OK',
          cancelText: ''
        })
      }, 300)
    }
  } catch (err) {
    console.error('Submit resep unified error:', err)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: err.response?.data?.message || 'Gagal menyimpan resep',
      confirmText: 'OK',
      cancelText: ''
    })
  }
}

// LEGACY FUNCTIONS (for backward compatibility)
const hapusObat = (index) => {
  resepObat.value.splice(index, 1)
}

const submitResep = () => {
  if (resepObat.value.length === 0) {
    showSweetAlert({
      type: 'warning',
      title: 'Warning!',
      message: 'Belum ada obat yang dipilih!',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  showSweetAlert({
    type: 'info',
    title: 'Info',
    message: 'Fitur simpan resep sedang dalam pengembangan',
    confirmText: 'OK',
    cancelText: ''
  })
  closeModalResep()
}

const goBack = () => {
  router.back()
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const closeSidebar = () => {
  isSidebarOpen.value = false
}

const showRiwayatPerawatan = async () => {
  isRiwayatModalOpen.value = true
  riwayatLoading.value = true
  riwayatError.value = ''
  riwayatTimeline.value = []

  try {
    const { data } = await apiClient.get(`/riwayat-perawatan/pasien/${patient.value.no_rkm_medis}`)

    if (data.success) {
      const payload = data.data || {}
      riwayatTimeline.value = payload.kunjungan || []
      riwayatSummaryInfo.value = payload.summary || { total_kunjungan: 0, total_biaya: 0 }
      riwayatPatientMeta.value = payload.patient || null
      totalBiayaRiwayat.value = riwayatSummaryInfo.value.total_biaya || 0
    } else {
      riwayatError.value = 'Gagal memuat data riwayat perawatan'
    }
  } catch (err) {
    console.error('Load riwayat error:', err)
    riwayatError.value = err.response?.data?.message || 'Terjadi kesalahan saat memuat data'
  } finally {
    riwayatLoading.value = false
  }
}

const closeRiwayatModal = () => {
  isRiwayatModalOpen.value = false
  riwayatTimeline.value = []
  riwayatSummaryInfo.value = { total_kunjungan: 0, total_biaya: 0 }
  riwayatPatientMeta.value = null
  riwayatError.value = ''
}

const handleOpenRiwayatDetail = (noRawat) => {
  if (!noRawat) return
  closeRiwayatModal()
  router.push({ name: 'riwayat-perawatan', params: { no_rawat: noRawat } })
}

const openRiwayatFullPage = () => {
  closeRiwayatModal()
  router.push({
    name: 'riwayat-perawatan',
    params: { no_rawat: patient.value.no_rawat || 'riwayat' },
    query: { no_rkm_medis: patient.value.no_rkm_medis }
  })
}

const downloadRiwayatPasien = async () => {
  if (!patient.value.no_rkm_medis || riwayatActionLoading.value) return
  riwayatActionLoading.value = true
  try {
    const response = await apiClient.get(
      `/riwayat-perawatan/pasien/${patient.value.no_rkm_medis}/export`,
      { responseType: 'blob' }
    )
    const blob = new Blob([response.data], { type: 'application/json' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `riwayat_perawatan_${patient.value.no_rkm_medis}.json`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Download riwayat error:', error)
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: error.response?.data?.message || 'Gagal mengunduh riwayat perawatan',
      confirmText: 'OK',
      cancelText: ''
    })
  } finally {
    riwayatActionLoading.value = false
  }
}

const printRiwayatTimeline = () => {
  const container = document.getElementById('riwayat-modal-printable')
  if (!container) {
    showSweetAlert({
      type: 'warning',
      title: 'Perhatian',
      message: 'Konten riwayat belum siap untuk dicetak.',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  const printWindow = window.open('', '_blank', 'width=1200,height=800')
  if (!printWindow) {
    showSweetAlert({
      type: 'error',
      title: 'Error',
      message: 'Tidak dapat membuka jendela cetak.',
      confirmText: 'OK',
      cancelText: ''
    })
    return
  }

  printWindow.document.write(`
    <html>
      <head>
        <title>Riwayat Perawatan - ${patient.value.nm_pasien}</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <style>${riwayatPrintStyles}</style>
      </head>
      <body>
        ${container.innerHTML}
      </body>
    </html>
  `)
  printWindow.document.close()
  printWindow.focus()
  setTimeout(() => {
    printWindow.print()
    printWindow.close()
  }, 600)
}

onMounted(() => {
  // Validate if patient data exists
  if (!patient.value.no_rawat) {
    showSweetAlert({
      type: 'error',
      title: 'Error!',
      message: 'Data pasien tidak lengkap!',
      confirmText: 'OK',
      cancelText: '',
      onConfirm: () => {
        goBack()
      }
    })
    return
  }
  // Load SOAP history
  loadSOAPHistory()
})

// Watch for tab changes to load riwayat resep automatically
watch(activeInputTab, (newTab) => {
  if (newTab === 'resep') {
    loadRiwayatResepTab()
  }
  if (newTab === 'laboratorium' && riwayatLabPKList.value.length === 0) {
    loadRiwayatLabPK()
  }
  if (newTab === 'radiologi' && riwayatRadList.value.length === 0) {
    loadRiwayatRad()
  }
  if (newTab === 'tindakan' && riwayatTindakanList.value.length === 0) {
    loadRiwayatTindakan()
  }
  if (newTab === 'usg-ekg') {
    // Load riwayat based on active sub-tab
    if (activeUsgEkgTab.value === 'usg' && riwayatUsgList.value.length === 0) {
      loadRiwayatUsg()
    } else if (activeUsgEkgTab.value === 'ekg' && riwayatEkgList.value.length === 0) {
      loadRiwayatEkg()
    }
  }
})

// Watch for USG/EKG sub-tab changes
watch(activeUsgEkgTab, (newSubTab) => {
  if (activeInputTab.value === 'usg-ekg') {
    if (newSubTab === 'usg' && riwayatUsgList.value.length === 0) {
      loadRiwayatUsg()
    } else if (newSubTab === 'ekg' && riwayatEkgList.value.length === 0) {
      loadRiwayatEkg()
    }
  }
})
</script>

<style scoped>
/* Fullscreen Layout */
.soap-layout {
  display: flex;
  height: 100vh;
  width: 100vw;
  position: fixed;
  top: 0;
  left: 0;
  background-color: #f5f7fa;
  z-index: 9999;
  overflow: hidden;
}

/* Hamburger Menu Button - sama seperti InputOperasiView */
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

/* Sidebar Backdrop (Mobile Only) */
.sidebar-backdrop {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9998;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
}

.sidebar-backdrop.show {
  opacity: 1;
  pointer-events: auto;
}

/* Patient Sidebar */
.patient-sidebar {
  width: 320px;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
  overflow-y: auto;
  transition: transform 0.3s ease;
}

/* Close Sidebar Button (Mobile Only) */
.btn-close-sidebar {
  display: none;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  align-items: center;
  justify-content: center;
}

.btn-close-sidebar:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: rgba(0, 0, 0, 0.1);
}

.sidebar-header h5 {
  color: white;
  margin: 0;
  font-weight: 700;
  font-size: 1.1rem;
}

.sidebar-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.85rem;
  font-weight: 500;
}

/* Sidebar Action Button */
.sidebar-action-button {
  padding: 0.75rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-top: auto;
}

.btn-riwayat-perawatan {
  width: 100%;
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  color: #333;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.btn-riwayat-perawatan:hover {
  background: linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-color: rgba(0, 0, 0, 0.15);
}

.btn-riwayat-perawatan:active {
  transform: translateY(0);
}

.btn-riwayat-icon {
  width: 20px;
  height: 20px;
  object-fit: contain;
  filter: brightness(0);
  transition: all 0.3s ease;
}

.btn-riwayat-perawatan:hover .btn-riwayat-icon {
  filter: brightness(0) opacity(0.8);
}

.sidebar-section-title {
  padding: 1rem 1.5rem 0.5rem 1.5rem;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: rgba(255, 255, 255, 0.9);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
}

.sidebar-section-title .badge {
  text-transform: none;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.35rem 0.75rem;
  letter-spacing: normal;
}

.btn-close-custom {
  background: rgba(255, 255, 255, 0.2);
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
  transition: all 0.3s ease;
}

.btn-close-custom:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.sidebar-content {
  padding: 1rem 1.5rem 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

/* Primary Patient Card */
.patient-card-primary {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.patient-rm {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  opacity: 0.9;
}

.patient-rm i {
  font-size: 1rem;
}

.rm-number {
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.patient-name {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
  line-height: 1.3;
}

.patient-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  opacity: 0.95;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.meta-item i {
  font-size: 0.95rem;
}

.meta-divider {
  opacity: 0.6;
  font-size: 0.8rem;
}

/* Medical Info Sections - New Layout */
.medical-info-sections {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.info-section {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  padding: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
}

.section-label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  opacity: 0.9;
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.info-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-row {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  padding: 0.5rem;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.info-row:hover {
  background: rgba(255, 255, 255, 0.1);
}

.info-row > i {
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  padding-top: 8px;
}

.info-text {
  flex: 1;
  min-width: 0;
}

.info-text .info-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 0.75;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.info-text .info-value {
  font-size: 0.9rem;
  font-weight: 600;
  line-height: 1.3;
  word-wrap: break-word;
}

.info-text .info-value.small {
  font-size: 0.8rem;
  font-weight: 500;
  opacity: 0.95;
}

/* Legacy Medical Info Grid (kept for backward compatibility) */
.medical-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.info-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(5px);
  border-radius: 10px;
  padding: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.15);
  display: flex;
  gap: 0.75rem;
  transition: all 0.3s ease;
}

.info-card:hover {
  background: rgba(255, 255, 255, 0.15);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.info-card.full-width {
  grid-column: 1 / -1;
}

.info-icon {
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.info-details {
  flex: 1;
  min-width: 0;
}

.info-details label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 0.8;
  display: block;
  margin-bottom: 0.35rem;
  font-weight: 600;
}

.info-details .info-value {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.3;
  word-wrap: break-word;
}

.info-details .info-value.small {
  font-size: 0.85rem;
  font-weight: 500;
  opacity: 0.95;
}

/* Legacy support */
.patient-info-item {
  margin-bottom: 1.5rem;
}

.patient-info-item label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  opacity: 0.8;
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.patient-info-item .info-value {
  font-size: 1rem;
  font-weight: 500;
  line-height: 1.4;
}

.divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.2);
  margin: 1.5rem 0;
}

/* Main SOAP Content */
.soap-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Tab Navigation */
.soap-tabs-wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  border-bottom: 2px solid #e2e8f0;
  padding-right: 1rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  gap: 0.5rem;
}

.soap-tabs {
  display: flex;
  padding: 0.75rem 2rem 0 2rem;
  gap: 0.5rem;
  overflow-x: auto;
  flex: 1;
}

.btn-back-main {
  background: white;
  border: 1px solid #06b6d4;
  color: #06b6d4;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  transition: all 0.3s;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
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
  object-fit: contain;
  filter: brightness(0) saturate(100%) invert(70%) sepia(90%) saturate(1500%) hue-rotate(160deg) brightness(0.95) contrast(1);
  transition: all 0.3s;
}

.btn-back-main:hover .btn-back-icon {
  filter: brightness(0) saturate(100%) invert(65%) sepia(95%) saturate(1800%) hue-rotate(160deg) brightness(0.85) contrast(1);
}

.soap-tab {
  background: none;
  border: none;
  padding: 1rem 1.5rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #718096;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.soap-tab:hover {
  color: #06b6d4;
  background-color: #f8fafc;
}

.soap-tab.active {
  color: #06b6d4;
  border-bottom-color: #06b6d4;
  background-color: #f0fdfa;
}

/* Sub-tab Navigation */
.subtab-container {
  display: flex;
  gap: 0.5rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.subtab-btn {
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  padding: 0.75rem 1.5rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.3s ease;
  white-space: nowrap;
  flex: 1;
}

.subtab-btn:hover {
  color: #06b6d4;
  background-color: #f0fdfa;
  border-color: #06b6d4;
}

.subtab-btn.active {
  color: white;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  border-color: #06b6d4;
  box-shadow: 0 4px 6px rgba(6, 182, 212, 0.2);
}

/* Photo Upload Section */
.photo-upload-section {
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 2px dashed #cbd5e0;
}

.upload-buttons {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.upload-buttons .btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Photo Preview Grid */
.photo-preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.photo-preview-item {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #e2e8f0;
  background: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.photo-preview-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.photo-preview-item img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  display: block;
}

.btn-remove-photo {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: rgba(255, 255, 255, 0.95);
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
  color: #dc2626;
  font-size: 1.2rem;
  z-index: 10;
}

.btn-remove-photo:hover {
  transform: scale(1.1);
  background: #dc2626;
  color: white;
}

.photo-name {
  padding: 0.5rem;
  font-size: 0.75rem;
  color: #64748b;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

/* Responsive Photo Grid */
@media (max-width: 768px) {
  .photo-preview-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 0.75rem;
  }

  .photo-preview-item img {
    height: 120px;
  }

  .upload-buttons {
    flex-direction: column;
  }

  .upload-buttons .btn {
    width: 100%;
    justify-content: center;
  }
}

.soap-form-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background-color: #f5f7fa;
}

.soap-content {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Grid Form Layout */
.soap-grid-form {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: auto auto;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

/* Columns */
.soap-column {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 450px;
  overflow-y: auto;
}

.soap-column:first-child {
  grid-column: 1;
  grid-row: 1;
}

.soap-column:last-child {
  grid-column: 2;
  grid-row: 1;
}

/* SOAP Fields */
.soap-field {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
}

/* Make Subjective and Planning fields larger with same size */
.soap-field.field-large {
  flex: 0 0 120px;
  min-height: 120px;
}

/* Make Assessment, Evaluasi, Instruksi slightly bigger */
.soap-field.field-medium {
  flex: 0 0 90px;
  min-height: 90px;
}

.field-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.field-badge {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
  font-weight: 700;
  border-radius: 6px;
  flex-shrink: 0;
}

.field-badge.badge-info {
  background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
}

.field-badge.badge-warning {
  background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.field-badge.badge-danger {
  background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.field-badge.badge-success {
  background: linear-gradient(135deg, #198754 0%, #146c43 100%);
}

.field-badge.badge-primary {
  background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.field-badge.badge-secondary {
  background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
}

.field-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.soap-textarea {
  flex: 1;
  min-height: 0;
  resize: none;
  font-size: 0.875rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem;
  background: white;
  transition: all 0.3s ease;
}

.soap-textarea:focus {
  outline: none;
  border-color: #06b6d4;
  box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.3);
}

/* Vital Signs Section */
.vital-signs-section {
  background: white;
  padding: 0.75rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.vital-signs-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.vital-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.vital-item label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #718096;
  margin: 0;
  text-transform: uppercase;
}

.vital-item input {
  height: 32px;
  border: 2px solid #e2e8f0;
  border-radius: 6px;
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
  font-weight: 500;
  text-align: center;
}

.vital-item input:focus {
  outline: none;
  border-color: #06b6d4;
  box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
}

/* Action Buttons Bar */
.form-actions-bar {
  grid-column: 1 / 3;
  grid-row: 2;
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.form-actions-bar .btn {
  padding: 0.5rem 1.5rem;
  font-weight: 600;
  border-radius: 6px;
  font-size: 0.875rem;
}

/* Alert */
.alert {
  border-radius: 8px;
  border: none;
}

/* Tab Content Wrapper */
.tab-content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

/* Coming Soon */
.coming-soon {
  text-align: center;
  padding: 4rem 2rem;
}

.coming-soon-icon {
  font-size: 5rem;
  margin-bottom: 1.5rem;
  opacity: 0.7;
}

.coming-soon h3 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 0.75rem;
}

.coming-soon p {
  color: #718096;
  font-size: 1.1rem;
  margin: 0;
}

/* Scrollbar */
.patient-sidebar::-webkit-scrollbar,
.soap-form-container::-webkit-scrollbar {
  width: 8px;
}

.patient-sidebar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.patient-sidebar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}

.soap-form-container::-webkit-scrollbar-track {
  background: #f5f7fa;
}

.soap-form-container::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

/* SOAP History Section */
.soap-history-section {
  width: 100%;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.history-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2d3748;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e2e8f0;
}

.soap-history-table {
  font-size: 0.875rem;
  margin-bottom: 0;
}

.soap-history-table thead th {
  background-color: #f8f9fa;
  color: #2d3748;
  font-weight: 600;
  border: 1px solid #dee2e6;
  padding: 0.5rem;
  vertical-align: middle;
  font-size: 0.8rem;
  text-align: center;
}

.soap-history-table tbody td {
  border: 1px solid #dee2e6;
  padding: 0.5rem;
  font-size: 0.8rem;
}

.soap-history-table .td-no {
  font-size: 0.875rem;
  font-weight: 500;
  text-align: left;
  padding: 0.5rem;
}

.soap-history-table .td-tanggal {
  font-size: 0.75rem;
  padding: 0.5rem;
  vertical-align: top;
}

.tanggal-content {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  min-height: 120px;
}

.tanggal-info {
  text-align: left;
  flex: 1;
}

.soap-history-table .td-tanggal strong {
  font-size: 0.8rem;
  font-weight: 600;
}

.tanggal-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  align-items: center;
  padding-top: 0.5rem;
  border-top: 1px solid #e2e8f0;
  margin-top: 0.5rem;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon:hover {
  background-color: #f8f9fa;
  transform: scale(1.1);
}

.btn-edit:hover {
  background-color: #e3f2fd;
}

.btn-delete {
  background-color: #dc3545;
  color: white;
}

.btn-delete:hover {
  background-color: #c82333;
}

/* SOAP Label and Value Cells */
.soap-label-cell {
  font-weight: 700;
  color: #2d3748;
  background-color: #f8f9fa;
  padding: 0.5rem !important;
  width: 100px;
  vertical-align: top;
}

.soap-value-cell {
  color: #495057;
  padding: 0.5rem !important;
  line-height: 1.5;
  word-wrap: break-word;
  white-space: pre-wrap;
  vertical-align: top;
}

/* Instruksi & Evaluasi Column */
.td-instruksi {
  font-size: 0.75rem;
  padding: 0.75rem !important;
  vertical-align: top;
}

.td-instruksi strong {
  font-weight: 600;
  color: #2d3748;
}

.mt-2 {
  margin-top: 0.5rem;
}

.table-responsive {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* Modal Overlay (shared by all modals) */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 2rem;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Modal Success Confirmation */
.modal-success-confirm {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 550px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.4s ease;
  overflow: hidden;
}

@keyframes slideUp {
  from {
    transform: translateY(50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-success-icon {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  padding: 3rem 2rem 2rem 2rem;
  text-align: center;
}

.success-checkmark {
  width: 80px;
  height: 80px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #10b981;
  margin: 0 auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  animation: scaleIn 0.5s ease;
}

@keyframes scaleIn {
  from {
    transform: scale(0);
  }
  to {
    transform: scale(1);
  }
}

.modal-success-body {
  padding: 2rem;
}

.modal-success-body h4 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.success-info-box {
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e2e8f0;
}

.info-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.info-label {
  font-weight: 600;
  color: #64748b;
  font-size: 0.9rem;
}

.info-value {
  font-weight: 600;
  color: #1f2937;
  font-size: 0.9rem;
}

/* Modal Resep */
.modal-resep {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-header-resep {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  border-bottom: 2px solid #0891b2;
  background: #ffffff;
  color: #0891b2;
  border-radius: 12px 12px 0 0;
}

.modal-header-resep h5 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #0891b2;
}

.modal-body-resep {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

/* Modal Input Obat (Quick Input) */

.modal-input-obat-simple {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease-out;
}

.modal-input-obat-racikan {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease-out;
}

.obat-racikan-info {
  background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
  border: 1px solid #93c5fd;
  border-radius: 6px;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
}

.obat-racikan-name {
  font-size: 1rem;
  font-weight: 700;
  color: #1e40af;
  margin-bottom: 0.25rem;
}

.obat-racikan-details {
  font-size: 0.875rem;
  color: #475569;
}

.modal-input-obat {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 750px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease-out;
}

.modal-header-input {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 2px solid #e2e8f0;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border-radius: 12px 12px 0 0;
}

.modal-header-input h6 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
}

.btn-close-small {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.btn-close-small:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body-input {
  padding: 1.5rem;
}

.obat-info-box {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  border: 1px solid #bae6fd;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.obat-name-main {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0c4a6e;
  margin-bottom: 0.75rem;
}

.obat-detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.detail-value {
  font-size: 0.95rem;
  color: #334155;
}

/* Modal Riwayat Resep */
.modal-riwayat-resep {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 1200px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease-out;
}

.modal-header-custom {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 12px 12px 0 0;
}

.modal-header-custom h5 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-body-custom {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.resep-history-container {
  max-height: 65vh;
  overflow-y: auto;
}

.resep-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 1rem;
  background: #f8f9fa;
}

.resep-header {
  background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
  padding: 1rem;
  border-radius: 6px;
  border-left: 4px solid #4caf50;
}

.racikan-header-info {
  background: white;
  padding: 0.75rem;
  border-radius: 4px;
  border-left: 3px solid #667eea;
  font-size: 0.95rem;
}

.racikan-item {
  border: 1px solid #c8e6c9;
  border-radius: 6px;
  padding: 0.75rem;
  background: white;
}

/* Modal SOAPIE */

.modal-soapie {
  background: white;
  border-radius: 12px;
  width: 95%;
  max-width: 1400px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-header-soapie {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #e2e8f0;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  border-radius: 12px 12px 0 0;
}

.modal-header-soapie h5 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
}

.btn-close-modal {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.btn-close-modal:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body-soapie {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.soapie-table {
  font-size: 0.85rem;
  margin-bottom: 0;
}

.soapie-table th {
  background-color: #f8f9fa;
  font-weight: 600;
  padding: 0.75rem;
  border: 1px solid #dee2e6;
}

.soapie-table td {
  padding: 0.75rem;
  border: 1px solid #dee2e6;
  vertical-align: top;
}

.inner-table {
  border: none;
}

.inner-table th {
  background-color: #fffef8;
  font-size: 0.8rem;
  padding: 0.5rem;
  font-weight: 600;
  border: 1px solid #dee2e6;
}

.inner-table td {
  font-size: 0.8rem;
  padding: 0.5rem;
  border: 1px solid #dee2e6;
  line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
  /* Show hamburger menu on mobile */
  .hamburger-btn {
    display: flex;
  }

  /* Show backdrop on mobile */
  .sidebar-backdrop {
    display: block;
  }

  /* Show close button in sidebar on mobile */
  .btn-close-sidebar {
    display: flex;
  }

  /* Hide sidebar by default on mobile */
  .patient-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 85%;
    max-width: 320px;
    height: 100vh;
    z-index: 10000;
    transform: translateX(-100%);
  }

  /* Show sidebar when open */
  .patient-sidebar.open {
    transform: translateX(0);
  }

  /* Make main content full width on mobile */
  .soap-main {
    width: 100%;
    margin-left: 0;
  }

  .soap-form-container {
    padding: 1rem;
  }

  .soap-grid-form {
    padding: 1rem;
  }

  /* Adjust tabs for mobile */
  .soap-tabs {
    padding: 0.75rem 1rem 0 1rem;
    gap: 0.25rem;
  }

  .soap-tab {
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
  }

  /* Adjust subtabs for mobile - keep horizontal */
  .subtab-container {
    padding: 0.75rem;
    gap: 0.5rem;
  }

  .subtab-btn {
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    flex: 1;
  }

  .soap-history-table {
    font-size: 0.7rem;
  }

  .soap-history-table thead th {
    font-size: 0.7rem;
    padding: 0.4rem;
  }

  .soap-history-table tbody td {
    font-size: 0.7rem;
    padding: 0.4rem;
  }

  .soap-label-cell {
    width: 80px;
    font-size: 0.7rem;
    padding: 0.4rem !important;
  }

  .soap-value-cell {
    font-size: 0.7rem;
    padding: 0.4rem !important;
  }

  .td-instruksi {
    font-size: 0.7rem;
  }
}

/* Extra responsive for very small screens */
@media (max-width: 480px) {
  .patient-sidebar {
    width: 90%;
    max-width: 280px;
  }

  .soap-tabs {
    gap: 0;
  }

  .soap-tab {
    padding: 0.6rem 0.75rem;
    font-size: 0.8rem;
  }

  .hamburger-btn {
    padding: 0.5rem;
  }

  .hamburger-icon {
    width: 20px;
  }

  /* Adjust subtabs for very small screens - keep horizontal */
  .subtab-container {
    padding: 0.5rem;
    gap: 0.4rem;
  }

  .subtab-btn {
    padding: 0.65rem 0.75rem;
    font-size: 0.8rem;
  }
}

/* Sweet Alert Styles */
.sweet-alert-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.sweet-alert-modal {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  width: 90%;
  max-width: 450px;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
}

.sweet-alert-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  margin: 0 auto 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: bold;
  animation: scaleIn 0.4s ease;
}

.sweet-alert-icon.icon-success {
  background: #d1fae5;
  color: #10b981;
  border: 3px solid #10b981;
}

.sweet-alert-icon.icon-error {
  background: #fee2e2;
  color: #ef4444;
  border: 3px solid #ef4444;
}

.sweet-alert-icon.icon-warning {
  background: #fef3c7;
  color: #f59e0b;
  border: 3px solid #f59e0b;
}

.sweet-alert-icon.icon-info {
  background: #dbeafe;
  color: #3b82f6;
  border: 3px solid #3b82f6;
}

.sweet-alert-title {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
  color: #1f2937;
}

.sweet-alert-message {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}

.sweet-alert-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-sweet-confirm,
.btn-sweet-cancel {
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 120px;
}

.btn-sweet-confirm.btn-success {
  background: #10b981;
  color: white;
}

.btn-sweet-confirm.btn-success:hover {
  background: #059669;
}

.btn-sweet-confirm.btn-error {
  background: #ef4444;
  color: white;
}

.btn-sweet-confirm.btn-error:hover {
  background: #dc2626;
}

.btn-sweet-confirm.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-sweet-confirm.btn-warning:hover {
  background: #d97706;
}

.btn-sweet-confirm.btn-info {
  background: #3b82f6;
  color: white;
}

.btn-sweet-confirm.btn-info:hover {
  background: #2563eb;
}

.btn-sweet-cancel {
  background: #e5e7eb;
  color: #4b5563;
}

.btn-sweet-cancel:hover {
  background: #d1d5db;
}

/* Obat Dropdown Styles */
.search-obat-wrapper {
  position: relative;
}

.obat-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  max-height: 350px;
  overflow-y: auto;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  margin-top: 4px;
}

.obat-item {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.obat-item:hover {
  background-color: #f8f9fa;
}

.obat-item:last-child {
  border-bottom: none;
}

.obat-name {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 4px;
}

.obat-info {
  font-size: 0.85rem;
  color: #6b7280;
}

.obat-item-row {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.obat-item-row:hover {
  background-color: #f0f9ff !important;
}

.obat-name-cell {
  font-weight: 600;
  color: #2d3748;
  font-size: 0.9rem;
  line-height: 1.3;
}

.obat-extra-info {
  margin-top: 2px;
  line-height: 1.2;
}

.obat-dropdown .table {
  margin-bottom: 0;
}

.obat-dropdown .table thead th {
  position: sticky;
  top: 0;
  background: #f8f9fa;
  z-index: 10;
  font-size: 0.85rem;
  padding: 8px;
  border-bottom: 2px solid #dee2e6;
}

.obat-dropdown .table tbody td {
  font-size: 0.85rem;
  padding: 8px;
  vertical-align: middle;
}

/* Laboratorium Form */
.lab-form-container {
  width: 100%;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
}

/* Laboratorium History Section */
.lab-history-section {
  width: 100%;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  margin-top: 1.5rem;
}

.lab-history-section .table {
  font-size: 0.75rem;
}

.lab-history-section .table thead th {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.4rem 0.5rem;
}

.lab-history-section .table tbody td {
  font-size: 0.75rem;
  padding: 0.4rem 0.5rem;
}

.lab-history-section .table tbody td small {
  font-size: 0.7rem;
}

/* Lab Card Styles */
.lab-card {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.lab-card-header {
  background-color: #3498db;
  color: white;
  padding: 15px 20px;
  font-weight: bold;
}

.lab-card-body {
  padding: 20px;
}

.lab-detail-row {
  background-color: #f9f9f9;
}

.parameter-table {
  margin-top: 10px;
  font-size: 0.85rem;
}

.parameter-table th {
  background-color: #e9ecef;
  font-size: 0.8rem;
  padding: 8px 12px;
}

.parameter-table td {
  padding: 8px 12px;
  font-size: 0.85rem;
}

.info-box-small {
  background-color: #f8f9fa;
  padding: 10px 15px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
}

.info-label-small {
  font-weight: 600;
  font-size: 0.85rem;
  color: #555;
  margin-bottom: 5px;
}

.info-value-small {
  font-size: 0.9rem;
  color: #333;
}

/* Radiologi Form & History */
.rad-form-container {
  width: 100%;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
}

.rad-history-section {
  width: 100%;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  margin-top: 1.5rem;
}

.rad-history-section .table {
  font-size: 0.75rem;
}

.rad-history-section .table thead th {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.4rem 0.5rem;
}

.rad-history-section .table tbody td {
  font-size: 0.75rem;
  padding: 0.4rem 0.5rem;
}

.rad-history-section .table tbody td small {
  font-size: 0.7rem;
}

.rad-card {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.rad-card-header {
  background-color: #9333ea;
  color: white;
  padding: 15px 20px;
  font-weight: bold;
}

.rad-card-body {
  padding: 20px;
}

.lab-tab-content {
  padding: 1rem 0;
}

.pemeriksaan-list {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1rem;
  background: white;
}

.pemeriksaan-list .table {
  margin-bottom: 0;
}

.pemeriksaan-list .table thead {
  position: sticky;
  top: 0;
  z-index: 5;
}

/* Tab Content Resep */
.tab-content-resep {
  min-height: 400px;
}

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

/* Resep Content Styles */
.resep-content {
  padding: 1.5rem;
}

.resep-header-section {
  margin-bottom: 1.5rem;
}

.section-title {
  color: #1e293b;
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.section-subtitle {
  color: #64748b;
  font-size: 0.875rem;
  margin-bottom: 0;
}

.nav-tabs-resep {
  border-bottom: 2px solid #e2e8f0;
  margin-bottom: 1.5rem;
}

.nav-tabs-resep .nav-link {
  color: #64748b;
  border: none;
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  transition: all 0.3s;
}

.nav-tabs-resep .nav-link.active {
  color: #3b82f6;
  background: transparent;
  border-bottom: 2px solid #3b82f6;
}

.nav-tabs-resep .nav-link:hover:not(.active) {
  color: #3b82f6;
  background: #f1f5f9;
}

.tab-icon {
  margin-right: 0.5rem;
}

.tab-content-resep {
  padding: 1.5rem;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.resep-header-card {
  background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
  padding: 1rem;
  border-radius: 6px 6px 0 0;
  border-left: 4px solid #4caf50;
}

.resep-meta {
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.resep-meta strong {
  color: #1e293b;
}

.resep-body {
  padding: 1rem;
  background: #ffffff;
}

.resep-subtitle {
  color: #1e293b;
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e2e8f0;
}

.racikan-item {
  background: #f8fafc;
  padding: 0.75rem;
  border-radius: 6px;
  border-left: 3px solid #8b5cf6;
}

.racikan-header {
  font-size: 0.875rem;
  color: #334155;
  margin-bottom: 0.5rem;
}

.history-title {
  color: #1e293b;
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0;
}

.cursor-pointer {
  cursor: pointer;
}

.cursor-pointer:hover {
  background-color: #f8fafc !important;
}

/* Modal Rujuk Internal */
.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.4s ease;
  overflow: hidden;
}

.modal-md {
  width: 90%;
  max-width: 600px;
}

.modal-header {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
}

.btn-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  font-size: 1.5rem;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.btn-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

.btn-close::before {
  content: '×';
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  background-color: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

/* Modal Riwayat Perawatan */
.modal-backdrop-riwayat {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 1rem;
  backdrop-filter: blur(4px);
}

.modal-dialog-riwayat {
  width: 100%;
  max-width: 1200px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-content-riwayat {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow: hidden;
}

.modal-header-riwayat {
  padding: 0.875rem 1.25rem;
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: none;
}

.modal-title-riwayat {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
}

.modal-subtitle-riwayat {
  margin: 0.25rem 0 0 0;
  font-size: 0.875rem;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.9);
}

.modal-header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.modal-header-actions .btn {
  font-weight: 600;
}

.btn-close-modal-riwayat {
  background: white;
  border: none;
  color: #333;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 2rem;
  font-weight: 300;
  line-height: 1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  padding: 0;
}

.btn-close-modal-riwayat:hover {
  background: #f8f9fa;
  color: #dc3545;
  transform: scale(1.1) rotate(90deg);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.modal-body-riwayat {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.patient-info-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px;
  padding: 1rem;
  border: 1px solid #dee2e6;
}

.patient-info-row {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
  min-width: 200px;
}

.info-item i {
  font-size: 1.5rem;
}

.info-label {
  font-size: 0.75rem;
  color: #6c757d;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-size: 0.95rem;
  color: #212529;
  font-weight: 600;
}

/* Kunjungan Group Styling */
.kunjungan-group {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 1rem;
  background: #f8f9fa;
}

.kunjungan-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
  padding: 1rem 1.5rem;
  margin-bottom: 1rem;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.kunjungan-info {
  flex: 1;
  min-width: 300px;
}

.kunjungan-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.kunjungan-title i {
  font-size: 1.3rem;
}

.kunjungan-details {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  font-size: 0.9rem;
}

.kunjungan-details .detail-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
}

.kunjungan-details .detail-item i {
  font-size: 1rem;
}

.kunjungan-biaya {
  text-align: right;
}

.biaya-label {
  font-size: 0.8rem;
  opacity: 0.9;
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.biaya-value {
  font-size: 1.4rem;
  font-weight: 700;
}

.modal-footer-riwayat {
  padding: 1rem 1.5rem;
  background-color: #f8fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.modal-footer-riwayat .btn {
  padding: 0.5rem 1.5rem;
  font-weight: 600;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.modal-footer-riwayat .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.riwayat-content {
  max-width: 100%;
}

.riwayat-content .card {
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.riwayat-content .card-header {
  padding: 0.75rem 1rem;
  font-weight: 600;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.riwayat-content .card-header h6 {
  margin: 0;
  font-size: 0.95rem;
}

.riwayat-content .table-sm td,
.riwayat-content .table-sm th {
  padding: 0.5rem;
  font-size: 0.875rem;
}

.riwayat-content .table-hover tbody tr:hover {
  background-color: #f8fafc;
}

/* Responsive Modal */
@media (max-width: 768px) {
  .modal-backdrop-riwayat {
    padding: 0;
  }

  .modal-dialog-riwayat {
    max-width: 100%;
    max-height: 100vh;
  }

  .modal-content-riwayat {
    border-radius: 0;
    max-height: 100vh;
  }

  .modal-header-riwayat {
    padding: 1rem;
  }

  .modal-title-riwayat {
    font-size: 1rem;
  }

  .modal-body-riwayat {
    padding: 1rem;
  }

  .modal-footer-riwayat {
    padding: 0.75rem 1rem;
  }

  .modal-subtitle-riwayat {
    font-size: 0.75rem;
  }

  .patient-info-row {
    flex-direction: column;
    gap: 0.75rem;
  }

  .info-item {
    min-width: 100%;
  }

  .kunjungan-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .kunjungan-info {
    min-width: 100%;
  }

  .kunjungan-biaya {
    width: 100%;
    text-align: left;
    border-top: 1px solid rgba(255, 255, 255, 0.3);
    padding-top: 0.75rem;
  }

  .kunjungan-details {
    font-size: 0.8rem;
    gap: 0.5rem;
  }

  .kunjungan-details .detail-item {
    padding: 0.2rem 0.5rem;
  }

  .riwayat-content .table-responsive {
    font-size: 0.8rem;
  }
}
</style>
