<template>
  <div class="riwayat-legacy-card" v-if="legacy">

              <!-- Triase IGD Primer -->
              <div v-if="legacy.triase_primer" class="card shadow-sm mb-3">
                <div class="card-header" style="background-color: #AA0000; color: white;">
                  <h6 class="mb-0">🚨 Triase Gawat Darurat - Primer (Zona Merah)</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                      <tbody>
                        <tr>
                          <td width="30%"><strong>Cara Masuk</strong></td>
                          <td>{{ legacy.triase_primer.cara_masuk }}</td>
                        </tr>
                        <tr>
                          <td><strong>Transportasi</strong></td>
                          <td>{{ legacy.triase_primer.alat_transportasi }}</td>
                        </tr>
                        <tr>
                          <td><strong>Alasan Kedatangan</strong></td>
                          <td>{{ legacy.triase_primer.alasan_kedatangan }}</td>
                        </tr>
                        <tr>
                          <td><strong>Keterangan Kedatangan</strong></td>
                          <td>{{ legacy.triase_primer.keterangan_kedatangan }}</td>
                        </tr>
                        <tr>
                          <td><strong>Macam Kasus</strong></td>
                          <td>{{ legacy.triase_primer.macam_kasus }}</td>
                        </tr>
                        <tr>
                          <td colspan="2" class="table-secondary text-center"><strong>Triase Primer</strong></td>
                        </tr>
                        <tr>
                          <td><strong>Keluhan Utama</strong></td>
                          <td v-html="legacy.triase_primer.keluhan_utama.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <tr>
                          <td><strong>Tanda Vital</strong></td>
                          <td>
                            Suhu (C): {{ legacy.triase_primer.suhu }},
                            Nyeri: {{ legacy.triase_primer.nyeri }},
                            Tensi: {{ legacy.triase_primer.tekanan_darah }},
                            Nadi(/menit): {{ legacy.triase_primer.nadi }},
                            Saturasi O²(%): {{ legacy.triase_primer.saturasi_o2 }},
                            Respirasi(/menit): {{ legacy.triase_primer.pernapasan }}
                          </td>
                        </tr>
                        <tr>
                          <td><strong>Kebutuhan Khusus</strong></td>
                          <td>{{ legacy.triase_primer.kebutuhan_khusus }}</td>
                        </tr>
                        <!-- Skala 1 - Immediate/Segera -->
                        <template v-if="legacy.triase_primer.skala1 && legacy.triase_primer.skala1.length > 0">
                          <tr>
                            <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                            <td class="text-center text-white" style="background-color: #AA0000;"><strong>Immediate/Segera</strong></td>
                          </tr>
                          <template v-for="(pemeriksaan, pIndex) in legacy.triase_primer.skala1" :key="'s1-' + pIndex">
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
                        <template v-if="legacy.triase_primer.skala2 && legacy.triase_primer.skala2.length > 0">
                          <tr>
                            <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                            <td class="text-center text-white" style="background-color: #FF0000;"><strong>Emergensi</strong></td>
                          </tr>
                          <template v-for="(pemeriksaan, pIndex) in legacy.triase_primer.skala2" :key="'s2-' + pIndex">
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
                          <td :style="{ backgroundColor: getTriaseColor(legacy.triase_primer), color: 'white' }">
                            Zona Merah {{ legacy.triase_primer.plan }}
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="table-secondary text-center"><strong>Petugas Triase Primer</strong></td>
                        </tr>
                        <tr>
                          <td><strong>Tanggal & Jam</strong></td>
                          <td>{{ legacy.triase_primer.tanggaltriase }}</td>
                        </tr>
                        <tr>
                          <td><strong>Catatan</strong></td>
                          <td>{{ legacy.triase_primer.catatan }}</td>
                        </tr>
                        <tr>
                          <td><strong>Dokter/Petugas IGD</strong></td>
                          <td>{{ legacy.triase_primer.nik }} - {{ legacy.triase_primer.nama }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Triase IGD Sekunder -->
              <div v-if="legacy.triase_sekunder" class="card shadow-sm mb-3">
                <div class="card-header" style="background-color: #C8C800; color: white;">
                  <h6 class="mb-0">🚨 Triase Gawat Darurat - Sekunder</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                      <tbody>
                        <tr>
                          <td width="30%"><strong>Cara Masuk</strong></td>
                          <td>{{ legacy.triase_sekunder.cara_masuk }}</td>
                        </tr>
                        <tr>
                          <td><strong>Transportasi</strong></td>
                          <td>{{ legacy.triase_sekunder.alat_transportasi }}</td>
                        </tr>
                        <tr>
                          <td><strong>Alasan Kedatangan</strong></td>
                          <td>{{ legacy.triase_sekunder.alasan_kedatangan }}</td>
                        </tr>
                        <tr>
                          <td><strong>Keterangan Kedatangan</strong></td>
                          <td>{{ legacy.triase_sekunder.keterangan_kedatangan }}</td>
                        </tr>
                        <tr>
                          <td><strong>Macam Kasus</strong></td>
                          <td>{{ legacy.triase_sekunder.macam_kasus }}</td>
                        </tr>
                        <tr>
                          <td colspan="2" class="table-secondary text-center"><strong>Triase Sekunder</strong></td>
                        </tr>
                        <tr>
                          <td><strong>Anamnesa Singkat</strong></td>
                          <td v-html="legacy.triase_sekunder.anamnesa_singkat.replace(/\n/g, '<br>')"></td>
                        </tr>
                        <tr>
                          <td><strong>Tanda Vital</strong></td>
                          <td>
                            Suhu (C): {{ legacy.triase_sekunder.suhu }},
                            Nyeri: {{ legacy.triase_sekunder.nyeri }},
                            Tensi: {{ legacy.triase_sekunder.tekanan_darah }},
                            Nadi(/menit): {{ legacy.triase_sekunder.nadi }},
                            Saturasi O²(%): {{ legacy.triase_sekunder.saturasi_o2 }},
                            Respirasi(/menit): {{ legacy.triase_sekunder.pernapasan }}
                          </td>
                        </tr>
                        <!-- Skala 3 - Urgensi -->
                        <template v-if="legacy.triase_sekunder.skala3 && legacy.triase_sekunder.skala3.length > 0">
                          <tr>
                            <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                            <td class="text-center text-white" style="background-color: #C8C800;"><strong>Urgensi</strong></td>
                          </tr>
                          <template v-for="(pemeriksaan, pIndex) in legacy.triase_sekunder.skala3" :key="'s3-' + pIndex">
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
                        <template v-if="legacy.triase_sekunder.skala4 && legacy.triase_sekunder.skala4.length > 0">
                          <tr>
                            <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                            <td class="text-center text-white" style="background-color: #00AA00;"><strong>Semi Urgensi/Urgensi Rendah</strong></td>
                          </tr>
                          <template v-for="(pemeriksaan, pIndex) in legacy.triase_sekunder.skala4" :key="'s4-' + pIndex">
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
                        <template v-if="legacy.triase_sekunder.skala5 && legacy.triase_sekunder.skala5.length > 0">
                          <tr>
                            <td class="table-secondary text-center"><strong>Pemeriksaan</strong></td>
                            <td class="text-center text-white" style="background-color: #969696;"><strong>Non Urgensi</strong></td>
                          </tr>
                          <template v-for="(pemeriksaan, pIndex) in legacy.triase_sekunder.skala5" :key="'s5-' + pIndex">
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
                          <td :style="{ backgroundColor: getTriaseColor(legacy.triase_sekunder), color: 'white' }">
                            {{ legacy.triase_sekunder.plan }}
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" class="table-secondary text-center"><strong>Petugas Triase Sekunder</strong></td>
                        </tr>
                        <tr>
                          <td><strong>Tanggal & Jam</strong></td>
                          <td>{{ legacy.triase_sekunder.tanggaltriase }}</td>
                        </tr>
                        <tr>
                          <td><strong>Catatan</strong></td>
                          <td>{{ legacy.triase_sekunder.catatan }}</td>
                        </tr>
                        <tr>
                          <td><strong>Dokter/Petugas IGD</strong></td>
                          <td>{{ legacy.triase_sekunder.nik }} - {{ legacy.triase_sekunder.nama }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Asuhan Medis IGD -->
              <div v-if="legacy.asuhan_medis_igd && legacy.asuhan_medis_igd.length > 0" class="card shadow-sm mb-3">
                <div class="card-header bg-danger text-white">
                  <h6 class="mb-0">🚑 Pengkajian Awal Medis IGD</h6>
                </div>
                <div class="card-body">
                  <div v-for="(item, index) in legacy.asuhan_medis_igd" :key="index" class="mb-4">
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
                      <div class="text-center mb-3">
                        <img src="/images/semua.png" alt="Gambar Lokalis" class="img-fluid" style="max-width: 600px;">
                      </div>
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
                    <hr v-if="index < legacy.asuhan_medis_igd.length - 1">
                  </div>
                </div>
              </div>

              <!-- Pemeriksaan Rawat Jalan (SOAP) -->
              <div v-if="legacy.pemeriksaan_ralan && legacy.pemeriksaan_ralan.length > 0" class="card shadow-sm mb-3">
                <div class="card-header" style="background-color: #17a2b8; color: white;">
                  <h6 class="mb-0">📋 Pemeriksaan Rawat Jalan (SOAP)</h6>
                </div>
                <div class="card-body">
                  <div v-for="(item, index) in legacy.pemeriksaan_ralan" :key="index" class="mb-4">
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
                    <hr v-if="index < legacy.pemeriksaan_ralan.length - 1">
                  </div>
                </div>
              </div>

              <!-- Pemeriksaan Rawat Inap (SOAP) -->
              <div v-if="legacy.pemeriksaan_ranap && legacy.pemeriksaan_ranap.length > 0" class="card shadow-sm mb-3">
                <div class="card-header" style="background-color: #6f42c1; color: white;">
                  <h6 class="mb-0">🛏️ Pemeriksaan Rawat Inap (SOAP)</h6>
                </div>
                <div class="card-body">
                  <div v-for="(item, index) in legacy.pemeriksaan_ranap" :key="index" class="mb-4">
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
                          <!-- Subjek -->
                          <tr v-if="item.keluhan">
                            <td colspan="2"></td>
                            <td colspan="2"><strong>Subjek</strong></td>
                            <td colspan="6" v-html="item.keluhan.replace(/\n/g, '<br>')"></td>
                          </tr>
                          <!-- Objek -->
                          <tr v-if="item.pemeriksaan">
                            <td colspan="2"></td>
                            <td colspan="2"><strong>Objek</strong></td>
                            <td colspan="6" v-html="item.pemeriksaan.replace(/\n/g, '<br>')"></td>
                          </tr>
                          <!-- Tanda Vital Header -->
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
                          </tr>
                          <!-- Alergi -->
                          <tr v-if="item.alergi">
                            <td colspan="2"></td>
                            <td colspan="2"><strong>Alergi</strong></td>
                            <td colspan="6">: {{ item.alergi }}</td>
                          </tr>
                          <!-- Asesmen -->
                          <tr v-if="item.penilaian">
                            <td colspan="2"></td>
                            <td colspan="2"><strong>Asesmen</strong></td>
                            <td colspan="6" v-html="': ' + item.penilaian.replace(/\n/g, '<br>')"></td>
                          </tr>
                          <!-- Plan -->
                          <tr v-if="item.rtl">
                            <td colspan="2"></td>
                            <td colspan="2"><strong>Plan</strong></td>
                            <td colspan="6" v-html="': ' + item.rtl.replace(/\n/g, '<br>')"></td>
                          </tr>
                          <!-- Instruksi/Implementasi -->
                          <tr v-if="item.instruksi">
                            <td colspan="2"></td>
                            <td colspan="2"><strong>Inst/Impl</strong></td>
                            <td colspan="6" v-html="': ' + item.instruksi.replace(/\n/g, '<br>')"></td>
                          </tr>
                          <!-- Evaluasi -->
                          <tr v-if="item.evaluasi">
                            <td colspan="2"></td>
                            <td colspan="2"><strong>Evaluasi</strong></td>
                            <td colspan="6" v-html="': ' + item.evaluasi.replace(/\n/g, '<br>')"></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <hr v-if="index < legacy.pemeriksaan_ranap.length - 1">
                  </div>
                </div>
              </div>

              <!-- Tindakan Rawat Jalan Dokter -->
              <div v-if="legacy.rawat_jalan_dokter && legacy.rawat_jalan_dokter.length > 0" class="card shadow-sm mb-3">
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
                        <tr v-for="(item, index) in legacy.rawat_jalan_dokter" :key="index">
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
              <div v-if="legacy.rawat_jalan_paramedis && legacy.rawat_jalan_paramedis.length > 0" class="card shadow-sm mb-3">
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
                        <tr v-for="(item, index) in legacy.rawat_jalan_paramedis" :key="index">
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

              <!-- Penggunaan Kamar Inap -->
              <div v-if="legacy.kamar_inap && legacy.kamar_inap.length > 0" class="card shadow-sm mb-3">
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
                        <tr v-for="(item, index) in legacy.kamar_inap" :key="index">
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
              <div v-if="legacy.radiologi && legacy.radiologi.length > 0" class="card shadow-sm mb-3">
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
                        <tr v-for="(item, index) in legacy.radiologi" :key="index">
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
              <div v-if="legacy.hasil_radiologi && legacy.hasil_radiologi.length > 0" class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white">
                  <h6 class="mb-0">📝 Hasil/Bacaan Radiologi</h6>
                </div>
                <div class="card-body">
                  <div v-for="(item, index) in legacy.hasil_radiologi" :key="index" class="mb-3">
                    <div class="fw-bold">{{ item.tgl_periksa }} {{ item.jam }}</div>
                    <div class="mt-1" v-html="item.hasil.replace(/\n/g, '<br>')"></div>
                    <hr v-if="index < legacy.hasil_radiologi.length - 1">
                  </div>
                </div>
              </div>

              <!-- Gambar Radiologi -->
              <div v-if="legacy.gambar_radiologi && legacy.gambar_radiologi.length > 0" class="card shadow-sm mb-3">
                <div class="card-header" style="background-color: #6c757d; color: white;">
                  <h6 class="mb-0">🖼️ Gambar Radiologi</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                      <thead class="table-light">
                        <tr>
                          <th width="5%">No.</th>
                          <th width="15%">Tanggal</th>
                          <th width="80%">Gambar Radiologi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(item, index) in legacy.gambar_radiologi" :key="index">
                          <td align="center">{{ index + 1 }}</td>
                          <td>{{ item.tgl_periksa }} {{ item.jam }}</td>
                          <td align="center">
                            <div v-if="item.gambar_url" class="py-2">
                              <a :href="item.gambar_url" target="_blank">
                                <img
                                  :src="item.gambar_url"
                                  :alt="'Gambar Radiologi ' + item.tgl_periksa"
                                  class="img-fluid border rounded"
                                  style="max-width: 450px; max-height: 450px; cursor: pointer;"
                                  @error="handleImageError"
                                >
                              </a>
                              <div class="small text-muted mt-2">Klik gambar untuk melihat ukuran penuh</div>
                            </div>
                            <div v-else class="text-muted py-3">
                              <i>Gambar tidak tersedia</i>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Pemeriksaan Laboratorium PK & MB -->
              <div v-if="legacy.laboratorium_pkmb && legacy.laboratorium_pkmb.length > 0" class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                  <h6 class="mb-0">🔬 Pemeriksaan Laboratorium PK & MB</h6>
                </div>
                <div class="card-body">
                  <div v-for="(labGroup, groupIndex) in legacy.laboratorium_pkmb" :key="groupIndex" class="mb-4">
                    <div v-for="(pemeriksaan, pIndex) in labGroup.pemeriksaan" :key="pIndex" class="mb-3">
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                          <thead class="table-light">
                            <tr v-if="pIndex === 0">
                              <th width="5%">No.</th>
                              <th width="15%">Tanggal</th>
                              <th width="10%">Kode</th>
                              <th width="25%">Nama Pemeriksaan</th>
                              <th width="18%">Dokter PJ</th>
                              <th width="17%">Petugas</th>
                              <th width="10%" class="text-end">Biaya</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td align="center">{{ pIndex + 1 }}</td>
                              <td>{{ labGroup.tgl_periksa }} {{ labGroup.jam }}</td>
                              <td>{{ pemeriksaan.kd_jenis_prw }}</td>
                              <td>{{ pemeriksaan.nm_perawatan }}</td>
                              <td>{{ pemeriksaan.nm_dokter }}</td>
                              <td>{{ pemeriksaan.nama }}</td>
                              <td class="text-end">{{ formatRupiah(pemeriksaan.biaya) }}</td>
                            </tr>
                            <!-- Detail Pemeriksaan -->
                            <tr v-if="pemeriksaan.detail && pemeriksaan.detail.length > 0">
                              <td colspan="7" class="p-0">
                                <table class="table table-sm mb-0">
                                  <thead class="table-secondary">
                                    <tr>
                                      <th width="40%" class="text-center">Detail Pemeriksaan</th>
                                      <th width="25%" class="text-center">Hasil</th>
                                      <th width="25%" class="text-center">Nilai Rujukan</th>
                                      <th width="10%" class="text-end">Biaya</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr v-for="(detail, dIndex) in pemeriksaan.detail" :key="dIndex">
                                      <td>{{ detail.Pemeriksaan }}</td>
                                      <td :class="getLabResultClass(detail.keterangan)">
                                        {{ detail.nilai }} {{ detail.satuan }}
                                      </td>
                                      <td v-html="detail.nilai_rujukan.replace(/\n/g, '<br>')"></td>
                                      <td class="text-end">{{ formatRupiah(detail.biaya_item) }}</td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                            <!-- Kesan dan Saran -->
                            <tr v-if="pemeriksaan.saran_kesan">
                              <td colspan="7">
                                <div><strong>Kesan:</strong> {{ pemeriksaan.saran_kesan.kesan }}</div>
                                <div><strong>Saran:</strong> {{ pemeriksaan.saran_kesan.saran }}</div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                    <hr v-if="groupIndex < legacy.laboratorium_pkmb.length - 1">
                  </div>
                </div>
              </div>

              <!-- Pemeriksaan Laboratorium PA -->
              <div v-if="legacy.laboratorium_pa && legacy.laboratorium_pa.length > 0" class="card shadow-sm mb-3">
                <div class="card-header bg-warning text-dark">
                  <h6 class="mb-0">🔬 Pemeriksaan Laboratorium PA</h6>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                      <thead class="table-light">
                        <tr>
                          <th width="5%">No.</th>
                          <th width="15%">Tanggal</th>
                          <th width="10%">Kode</th>
                          <th width="25%">Nama Pemeriksaan</th>
                          <th width="18%">Dokter PJ</th>
                          <th width="17%">Petugas</th>
                          <th width="10%" class="text-end">Biaya</th>
                        </tr>
                      </thead>
                      <tbody>
                        <template v-for="(item, index) in legacy.laboratorium_pa" :key="index">
                          <tr>
                            <td align="center">{{ index + 1 }}</td>
                            <td>{{ item.tgl_periksa }} {{ item.jam }}</td>
                            <td>{{ item.kd_jenis_prw }}</td>
                            <td>{{ item.nm_perawatan }}</td>
                            <td>{{ item.nm_dokter }}</td>
                            <td>{{ item.nama }}</td>
                            <td class="text-end">{{ formatRupiah(item.biaya) }}</td>
                          </tr>
                          <tr v-if="item.detail">
                            <td colspan="7">
                              <div class="p-2">
                                <div class="mb-2"><strong>Diagnosa Klinis:</strong> {{ item.detail.diagnosa_klinik }}</div>
                                <div class="mb-2"><strong>Makroskopik:</strong> {{ item.detail.makroskopik }}</div>
                                <div class="mb-2"><strong>Mikroskopik:</strong> {{ item.detail.mikroskopik }}</div>
                                <div class="mb-2"><strong>Kesimpulan:</strong> {{ item.detail.kesimpulan }}</div>
                                <div class="mb-2"><strong>Kesan:</strong> {{ item.detail.kesan }}</div>
                                <div v-if="item.gambar_url" class="text-center mt-3">
                                  <a :href="item.gambar_url" target="_blank">
                                    <img
                                      :src="item.gambar_url"
                                      :alt="'Gambar PA - ' + item.nm_perawatan"
                                      class="img-fluid border rounded"
                                      style="max-width: 450px; max-height: 450px; cursor: pointer;"
                                      @error="handleImageError"
                                    >
                                  </a>
                                  <div class="small text-muted mt-2">Klik gambar untuk melihat ukuran penuh</div>
                                </div>
                              </div>
                            </td>
                          </tr>
                        </template>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Pemberian Obat -->
              <div v-if="legacy.pemberian_obat && legacy.pemberian_obat.length > 0" class="card shadow-sm mb-3">
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
                        <tr v-for="(item, index) in legacy.pemberian_obat" :key="index">
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

              <!-- Resep Pulang -->
              <div v-if="legacy.resep_pulang && legacy.resep_pulang.length > 0" class="card shadow-sm mb-3">
                <div class="card-header" style="background-color: #20c997; color: white;">
                  <h6 class="mb-0">💊 Resep Pulang</h6>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                      <thead class="table-light">
                        <tr>
                          <th width="5%">No.</th>
                          <th width="10%">Kode</th>
                          <th width="40%">Nama Obat/BHP/Alkes</th>
                          <th width="20%">Dosis</th>
                          <th width="10%">Jumlah</th>
                          <th width="15%" class="text-end">Biaya</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(item, index) in legacy.resep_pulang" :key="index">
                          <td>{{ index + 1 }}</td>
                          <td>{{ item.kode_brng }}</td>
                          <td>{{ item.nama_brng }}</td>
                          <td>{{ item.dosis || '-' }}</td>
                          <td>{{ item.jml_barang }} {{ item.kode_sat }}</td>
                          <td class="text-end">{{ formatRupiah(item.total) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- PPN Obat -->
              <div v-if="legacy.ppn_obat && legacy.ppn_obat > 0" class="card shadow-sm mb-3">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">🧾 PPN Obat</h6>
                    <h6 class="mb-0 text-dark">{{ formatRupiah(legacy.ppn_obat) }}</h6>
                  </div>
                </div>
              </div>

              <!-- Tambahan Biaya -->
              <div v-if="legacy.tambahan_biaya && legacy.tambahan_biaya.length > 0" class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
                  <h6 class="mb-0">➕ Tambahan Biaya</h6>
                </div>
                <div class="card-body p-0">
                  <table class="table table-sm mb-0">
                    <tbody>
                      <tr v-for="(item, index) in legacy.tambahan_biaya" :key="index">
                        <td width="80%">{{ item.nama_biaya }}</td>
                        <td width="20%" class="text-end">{{ formatRupiah(item.besar_biaya) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Potongan Biaya -->
              <div v-if="legacy.potongan_biaya && legacy.potongan_biaya.length > 0" class="card shadow-sm mb-3">
                <div class="card-header bg-danger text-white">
                  <h6 class="mb-0">➖ Potongan Biaya</h6>
                </div>
                <div class="card-body p-0">
                  <table class="table table-sm mb-0">
                    <tbody>
                      <tr v-for="(item, index) in legacy.potongan_biaya" :key="index">
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
                    <h4 class="mb-0 text-primary">Rp {{ formatRupiah(displayTotalBiaya) }}</h4>
                  </div>
                </div>
              </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  legacy: { type: Object, required: true },
  totalBiaya: { type: Number, default: 0 }
})

const displayTotalBiaya = computed(() => props.totalBiaya || 0)

const formatRupiah = (amount) => {
  if (!amount) return '0'
  return new Intl.NumberFormat('id-ID').format(amount)
}

const getLabResultClass = (keterangan) => {
  if (!keterangan) return ''
  if (typeof keterangan !== 'string') return ''
  switch (keterangan.toLowerCase()) {
    case 'l':
      return 'text-primary'
    case 'h':
      return 'text-danger'
    case 't':
      return 'fw-bold'
    default:
      return ''
  }
}

const getTriaseColor = (triaseData) => {
  if (!triaseData) return '#969696'
  if (triaseData.skala1 && triaseData.skala1.length > 0) return '#AA0000'
  if (triaseData.skala2 && triaseData.skala2.length > 0) return '#FF0000'
  if (triaseData.skala3 && triaseData.skala3.length > 0) return '#C8C800'
  if (triaseData.skala4 && triaseData.skala4.length > 0) return '#00AA00'
  if (triaseData.skala5 && triaseData.skala5.length > 0) return '#969696'
  return '#969696'
}

const handleImageError = (event) => {
  event.target.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="450" height="450"%3E%3Crect width="450" height="450" fill="%23f0f0f0"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="20" fill="%23999"%3EGambar tidak dapat dimuat%3C/text%3E%3C/svg%3E'
  event.target.style.cursor = 'default'
}
</script>

<style scoped>
.riwayat-legacy-card .table td {
  padding: 0.5rem;
}
</style>
