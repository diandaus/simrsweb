<template>
  <div class="riwayat-timeline">
    <!-- Patient Info - Compact -->
    <div v-if="patient" class="patient-info-compact mb-3">
      <div class="patient-row-main">
        RM<span class="badge bg-primary">{{ patient.no_rkm_medis }}</span>
        <span class="patient-name">{{ patient.nm_pasien }}</span>
        <span class="badge" :class="patient.jk === 'L' ? 'bg-info' : 'bg-pink'">
          {{ patient.jk === 'L' ? 'L' : 'P' }}
        </span>
        <span v-if="patient.gol_darah" class="badge bg-danger">{{ patient.gol_darah }}</span>
      </div>
      <div class="patient-row-details">
        <span v-if="patient.tmp_lahir || patient.tgl_lahir" class="detail-chip">
          <i class="bi bi-calendar3"></i> {{ patient.tmp_lahir }}, {{ formatTanggal(patient.tgl_lahir) }}
        </span>
        <span v-if="patient.agama" class="detail-chip"><i class="bi bi-moon-stars"></i> {{ patient.agama }}</span>
        <span v-if="patient.stts_nikah" class="detail-chip"><i class="bi bi-heart"></i> {{ patient.stts_nikah }}</span>
        <span v-if="patient.pnd" class="detail-chip"><i class="bi bi-mortarboard"></i> {{ patient.pnd }}</span>
        <span v-if="patient.pekerjaan" class="detail-chip"><i class="bi bi-briefcase"></i> {{ patient.pekerjaan }}</span>
        <span v-if="patient.nm_ibu" class="detail-chip"><i class="bi bi-person-heart"></i> <span class="detail-label-inline">Nama Ibu:</span> {{ patient.nm_ibu }}</span>
        <span v-if="patient.nama_cacat && patient.nama_cacat !== 'TIDAK ADA'" class="detail-chip"><i class="bi bi-person-wheelchair"></i> {{ patient.nama_cacat }}</span>
      </div>
      <div v-if="patient.alamat" class="patient-row-address">
        <i class="bi bi-geo-alt"></i> {{ patient.alamat }}
      </div>
    </div>

    <div
      v-if="limit > 0 && timeline.length > limit"
      class="alert alert-warning py-2 px-3 mb-3"
    >
      Menampilkan {{ displayedTimeline.length }} dari {{ timeline.length }} kunjungan terbaru.
    </div>

    <div v-if="displayedTimeline.length > 0" class="riwayat-content">
      <div
        v-for="(visit, index) in displayedTimeline"
        :key="getVisitId(visit, index)"
        class="kunjungan-group mb-4"
        :class="{ collapsed: !isExpanded(getVisitId(visit, index)) }"
      >
        <div class="kunjungan-header">
          <div class="kunjungan-info">
            <div class="kunjungan-title">
              <i class="bi bi-calendar-event"></i>
              <span>Kunjungan #{{ visit.summary?.kunjungan_ke || displayedTimeline.length - index }}</span>
            </div>
            <div class="kunjungan-details">
              <span class="detail-item">
                <i class="bi bi-clock"></i>
                {{ visit.summary?.tgl_registrasi }} {{ visit.summary?.jam_reg }}
              </span>
              <span class="detail-item" v-if="visit.summary?.nm_dokter">
                <i class="bi bi-person-badge"></i>
                {{ visit.summary?.nm_dokter }}
              </span>
              <span class="detail-item" v-if="visit.summary?.nm_poli">
                <i class="bi bi-hospital"></i>
                {{ visit.summary?.nm_poli }}
              </span>
              <span class="detail-item" v-if="visit.summary?.penanggung_jawab">
                <i class="bi bi-shield-check"></i>
                {{ visit.summary?.penanggung_jawab }}
              </span>
              <span class="detail-item">
                <span class="badge" :class="visit.summary?.status_lanjut === 'Ranap' ? 'bg-danger' : 'bg-success'">
                  {{ visit.summary?.status_lanjut }}
                </span>
              </span>
            </div>
          </div>
        </div>

        <transition name="fade">
          <div v-show="isExpanded(getVisitId(visit, index))" class="kunjungan-body">
            <RiwayatPerawatanLegacyCard
              v-if="visit.legacy"
              :legacy="visit.legacy"
              :total-biaya="visit.billing?.grand_total || 0"
            />
            <div v-else class="alert alert-warning mb-0">
              Detail riwayat belum tersedia untuk kunjungan ini.
            </div>
          </div>
        </transition>
      </div>
    </div>

    <div v-else class="alert alert-info">
      <i class="bi bi-info-circle"></i> Belum ada data riwayat perawatan untuk pasien ini.
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import RiwayatPerawatanLegacyCard from '@/components/RiwayatPerawatanLegacyCard.vue'

const props = defineProps({
  timeline: { type: Array, default: () => [] },
  patient: { type: Object, default: null },
  autoExpand: { type: Boolean, default: true },
  limit: { type: Number, default: 5 }
})

const expandedState = ref({})

const displayedTimeline = computed(() => {
  if (!props.timeline) return []
  if (props.limit && props.limit > 0 && props.timeline.length > props.limit) {
    return props.timeline.slice(0, props.limit)
  }
  return props.timeline
})

const assignExpandedState = (data) => {
  const nextState = {}
  data.forEach((visit, index) => {
    nextState[getVisitId(visit, index)] = props.autoExpand
  })
  expandedState.value = nextState
}

const getVisitId = (visit, index) => visit?.summary?.no_rawat ?? `visit-${index}`

const isExpanded = (id) => expandedState.value[id] ?? false

const formatTanggal = (tanggal) => {
  if (!tanggal) return '-'
  try {
    const date = new Date(tanggal)
    return date.toLocaleDateString('id-ID', {
      day: '2-digit',
      month: 'long',
      year: 'numeric'
    })
  } catch {
    return tanggal
  }
}

watch(
  displayedTimeline,
  (newVal) => {
    assignExpandedState(newVal || [])
  },
  { immediate: true }
)
</script>

<style scoped>
/* Patient Info - Compact Style */
.patient-info-compact {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem 1rem;
}

.patient-row-main {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin-bottom: 0.5rem;
}

.patient-row-main .patient-name {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin-right: 0.25rem;
}

.patient-row-main .badge {
  font-size: 0.7rem;
  padding: 0.2rem 0.5rem;
}

.bg-pink {
  background-color: #ec4899 !important;
}

.patient-row-details {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.detail-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: #475569;
  background: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  border: 1px solid #e2e8f0;
}

.detail-chip i {
  font-size: 0.7rem;
  color: #667eea;
}

.detail-label-inline {
  color: #94a3b8;
  font-weight: 400;
}

.patient-row-address {
  font-size: 0.75rem;
  color: #64748b;
  padding-top: 0.5rem;
  border-top: 1px solid #e2e8f0;
}

.patient-row-address i {
  color: #667eea;
  margin-right: 0.25rem;
}

/* Kunjungan Group */
.kunjungan-group {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 0;
  background: #ffffff;
  overflow: hidden;
  transition: transform 0.2s ease;
}

.kunjungan-group.collapsed {
  opacity: 0.85;
}

.kunjungan-group:hover {
  transform: translateY(-2px);
}

.kunjungan-header {
  background: #ffffff;
  color: #0891b2;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  flex-wrap: wrap;
  border-bottom: 1px solid #e2e8f0;
}

.kunjungan-info {
  flex: 1;
  min-width: 280px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.kunjungan-title {
  font-weight: 700;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.kunjungan-details {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: flex-end;
}

.detail-item {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: #f0fdfa;
  color: #0891b2;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.85rem;
  border: 1px solid #99f6e4;
}

.kunjungan-body {
  padding: 1.25rem;
  background: #f8fafc;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  /* Patient Info Compact Mobile */
  .patient-info-compact {
    padding: 0.5rem 0.75rem;
  }

  .patient-row-main .patient-name {
    font-size: 0.9rem;
  }

  .detail-chip {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
  }

  .patient-row-address {
    font-size: 0.7rem;
  }

  /* Kunjungan Group Mobile */
  .kunjungan-header {
    flex-direction: column;
    text-align: left;
  }

  .kunjungan-info {
    flex-direction: column;
    align-items: flex-start;
  }

  .kunjungan-details {
    justify-content: flex-start;
  }
}
</style>

