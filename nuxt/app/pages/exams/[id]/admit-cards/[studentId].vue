<template>
  <div class="print-root">
    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else-if="!card" class="loading-state"><div class="spinner" /></div>
    <div v-else class="admit-card">
      <div class="card-header">
        <div class="header-title">
          <div class="emblem">★</div>
          <div>
            <div class="org-name">{{ card.exam_name_bn || exam?.name_bn }}</div>
            <div class="org-sub">{{ exam?.class?.name_bn || 'সব শ্রেণি' }}</div>
          </div>
        </div>
        <div class="header-right">
          <div class="card-type">{{ card.exam_type }}</div>
          <div class="card-badge">ভরণা কার্ড</div>
        </div>
      </div>
      <div class="card-body">
        <div class="student-block">
          <div class="student-avatar">
            <img v-if="card.student?.photo_url" :src="card.student?.photo_url" />
            <span v-else>{{ card.student?.name_bn?.[0] || '?' }}</span>
          </div>
          <div class="student-info">
            <h2 class="student-name">{{ card.student?.name_bn }}</h2>
            <small class="student-name-en">{{ card.student?.name_en }}</small>
            <div class="info-row">
              <span>রেফারেন্স নং</span>
              <strong>{{ card.student?.roll_or_reg }}</strong>
            </div>
            <div class="info-row">
              <span>রক্তের গ্রুপ</span>
              <strong>{{ card.student?.blood_group || '—' }}</strong>
            </div>
            <div class="info-row">
              <span>র্সেণি</span>
              <strong>{{ exam?.class?.name_bn || '—' }}</strong>
            </div>
          </div>
        </div>
        <div class="seat-block">
          <div class="seat-label">আসন সংখ্যা</div>
          <div class="seat-number">{{ card.seat_label }}</div>
          <div class="info-row">
            <span>সারি</span><span>{{ card.row || '—' }}</span>
            <span>কলাম</span><span>{{ card.col || '—' }}</span>
          </div>
          <div class="info-row">
            <span>হল</span><span>{{ card.exam_venue || 'মূল ভবন' }}</span>
          </div>
        </div>
        <div class="exam-block">
          <div class="exam-title">পরীক্ষার তথ্য</div>
          <div class="info-row">
            <span>পরীক্ষা</span><span>{{ card.exam_name_bn }}</span>
          </div>
          <div class="info-row">
            <span>তারিখ</span><span>{{ card.exam_date }}</span>
          </div>
          <div class="info-row">
            <span>সময়</span><span>{{ card.exam_time }} – {{ card.exam_end_time }}</span>
          </div>
          <div class="info-row">
            <span>সময়কাল</span><span>{{ card.exam_duration_minutes }} মিনিট</span>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <div class="qr-section">
          <div class="qr-box">
            <canvas ref="qrCanvas" width="90" height="90" />
          </div>
          <div class="qr-text">
            <small>ভরণা কার্ড নং</small>
            <strong>{{ card.student?.roll_or_reg }}-{{ card.exam_id }}</strong>
          </div>
        </div>
        <div class="footer-note">
          সতর্কতা: এই কার্ড নিয়ে যাবেন। পরীক্ষা শুরুর ১৫ মিনিট আগে হলে পৌঁছাতে হবে।
        </div>
      </div>
      <div class="print-btn-row no-print">
        <button class="btn btn-primary" @click="print">
          <Icon name="printer" /> এই কার্ড প্রিন্ট করুন
        </button>
        <NuxtLink :to="`/exams/${examId}/admit-cards`" class="btn btn-ghost">
          <Icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import Icon from '~/components/Icon.vue'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const examId = route.params.exam?.toString() ?? route.params.id?.toString() ?? ''
const studentId = route.params.student?.toString() ?? route.params.studentId?.toString() ?? ''
const api = useApiClient()
const exam = ref<any>(null)
const card = ref<any>(null)
const loading = ref(true)
const qrCanvas = ref<HTMLCanvasElement | null>(null)

async function load() {
  loading.value = true
  try {
    const res = await api.get(`/exams/${examId}/admit-cards/${studentId}`)
    card.value = res.data?.data ?? null
    if (card.value) {
      const examRes = await api.get(`/exams/${examId}`)
      exam.value = examRes.data?.data ?? null
    }
  } catch(e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function print() {
  window.print()
}

onMounted(async () => {
  await load()
  if (card.value && qrCanvas.value) {
    try {
      const QRCode = await import('qrcode')
      QRCode.toCanvas(qrCanvas.value, `R:${card.value.student?.roll_or_reg}|E:${card.value.exam_id}|S:${card.value.student?.id}`, {
        width: 90,
        margin: 1,
        color: { dark: '#145032', light: '#ffffff' },
      })
    } catch {
      const ctx = qrCanvas.value?.getContext('2d')
      if (ctx) {
        ctx.fillStyle = '#145032'
        ctx.fillRect(0, 0, 90, 90)
        ctx.fillStyle = '#fff'
        ctx.font = '10px sans-serif'
        ctx.textAlign = 'center'
        ctx.fillText('রেফারেন্স', 45, 45)
      }
    }
  }
})
</script>

<style scoped>
.print-root { max-width: 700px; margin: 0 auto; padding: 1.2rem; }
.admit-card { border: 2px solid var(--color-primary); border-radius: 6px; background: #fff; overflow: hidden; }
.card-header { display:flex; justify-content:space-between; align-items:center; padding: .7rem 1rem; background: var(--color-primary); color: #fff; }
.header-title { display:flex; align-items:center; gap:.6rem; }
.emblem { font-size: 1.3rem; color: var(--color-accent); }
.org-name { font: 700 1rem var(--font-bn); }
.org-sub { font: .72rem var(--font-bn); opacity:.85; }
.header-right { text-align:right; }
.card-type { font: 600 .72rem var(--font-bn); opacity:.9; }
.card-badge { font: 800 .85rem var(--font-bn); background: var(--color-accent); color: #fff; padding:.15rem .6rem; border-radius:99px; }
.card-body { padding: 1rem; display:grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.student-block { display:flex; gap:.7rem; align-items:flex-start; padding-bottom:.6rem; border-bottom:1px solid var(--color-border-light); }
.student-avatar { width:52px; height:52px; border-radius:50%; background: var(--color-primary-10); display:grid; place-items:center; font: 2rem var(--font-bn); color: var(--color-primary); overflow:hidden; }
.student-avatar img { width:100%; height:100%; object-fit:cover; }
.student-name { margin:0; font: 700 1.15rem var(--font-bn); }
.student-name-en { color: var(--color-text-muted); font:.75rem var(--font-bn); }
.info-row { display:flex; justify-content:space-between; padding:.25rem 0; border-bottom:1px dashed var(--color-border-light); font:.72rem var(--font-bn); }
.info-row span { color: var(--color-text-muted); }
.info-row strong { color: var(--color-text); font-weight:600; }
.seat-block { background: var(--color-primary-50); border:1px solid var(--color-primary); border-radius:8px; padding:.7rem; }
.seat-label { font:.68rem var(--font-bn); color: var(--color-text-muted); margin-bottom:.2rem; }
.seat-number { font: 800 1.8rem var(--font-sans); color: var(--color-primary); text-align:center; margin:.2rem 0 .5rem; }
.exam-block { background: var(--color-accent-50); border:1px solid var(--color-accent); border-radius:8px; padding:.7rem; }
.exam-title { font: 700 .8rem var(--font-bn); margin-bottom:.4rem; color: var(--color-accent); }
.card-footer { display:flex; justify-content:space-between; align-items:center; padding: .7rem 1rem; border-top:2px solid var(--color-primary); background:#f9f9f7; }
.qr-section { display:flex; align-items:center; gap:.6rem; }
.qr-box { background:#fff; border:1px solid var(--color-border); border-radius:4px; padding:4px; }
.qr-text small { display:block; font:.6rem var(--font-bn); color: var(--color-text-muted); }
.qr-text strong { font:.8rem var(--font-bn); }
.footer-note { font:.65rem var(--font-bn); color: var(--color-text-muted); max-width: 400px; }
.print-btn-row { display:flex; gap:.5rem; margin-top:1rem; justify-content:center; }
</style>

<style>
@media print {
  .no-print { display: none !important; }
  .print-root { max-width: none; padding: 0; }
  .admit-card { border: 2px solid #000; box-shadow: none; }
  @page { size: A4; margin: 12mm; }
}
</style>
