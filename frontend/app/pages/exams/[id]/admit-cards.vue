<template>
  <div class="admit-list-page">
    <div class="page-header">
      <div>
        <span class="eyebrow">ভরণা কার্ড</span>
        <h1 v-if="exam">{{ exam.name_bn || exam.title_bn }} — ভরণা কার্ড তালিকা</h1>
        <h1 v-else>ভরণা কার্ড তালিকা</h1>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="printAll" :disabled="printing">
          {{ printing ? 'মুদ্রণ হচ্ছে...' : 'সব কার্ড মুদ্রণ শুরু করুন' }}
        </button>
      </div>
    </div>
    <div v-if="error" class="alert alert-error mb-2">{{ error }}</div>
    <div v-if="loading" class="loading-state"><div class="spinner" /></div>
    <div v-else class="admit-grid">
      <div v-for="item in cards" :key="item.student?.id" class="admit-row">
        <div class="admit-mini">
          <img v-if="item.student?.photo_url" :src="item.student?.photo_url" class="mini-avatar" />
          <div class="mini-info">
            <b>{{ item.student?.name_bn }}</b>
            <small>{{ item.student?.name_en || '' }} · {{ item.student?.roll_or_reg }}</small>
            <small class="text-muted">{{ item.seat_label || 'অনির্ধারিত' }}</small>
          </div>
        </div>
        <div class="admit-actions">
          <button class="btn btn-outline btn-sm" @click="openCard(item.student?.id)">
            <Icon name="eye" /> কার্ড দেখুন
          </button>
          <button class="btn btn-outline btn-sm" @click="printOne(item.student?.id)">
            <Icon name="printer" /> প্রিন্ট
          </button>
        </div>
      </div>
      <div v-if="!cards.length" class="empty-card">
        <Icon name="document" class="empty-icon" />
        <h3>কোনো ভরণা কার্ড নেই</h3>
        <p>সিট বরাদ্দ করার পর ভরণা কার্ড তৈরি হবে</p>
        <NuxtLink :to="`/exams/${examId}/seats`" class="btn btn-primary">
          <Icon name="plus" /> আসন বরাদ্দ করতে যান
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
const api = useApiClient()
const exam = ref<any>(null)
const cards = ref<any[]>([])
const loading = ref(true)
const error = ref('')
const printing = ref(false)

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get(`/exams/${examId}/seats`)
    exam.value = res.data?.data?.exam ?? null
    const plan = res.data?.data?.seat_plan ?? {}
    const students = res.data?.data?.enrolled_students ?? []
    cards.value = Object.entries(plan).map(([studentIdStr, info]) => {
      const student = students.find(s => String(s.id) === studentIdStr)
      return {
        student,
        seat_label: info?.label ?? 'অনির্ধারিত',
        row: info?.row,
        col: info?.col,
      }
    })
  } catch(e:any) {
    error.value = e?.response?.data?.message ?? 'ডেটা লোড করা যায়নি'
  } finally {
    loading.value = false
  }
}

function openCard(studentId:number|undefined) {
  if (!studentId) return
  window.open(`/exams/${examId}/admit-cards/${studentId}`, '_blank')
}

function printOne(studentId:number|undefined) {
  if (!studentId) return
  window.open(`/exams/${examId}/admit-cards/${studentId}`, '_blank')
}

async function printAll() {
  printing.value = true
  for (const item of cards.value) {
    if (!item.student?.id) continue
    await new Promise(r => setTimeout(r, 400))
    window.open(`/exams/${examId}/admit-cards/${item.student.id}`, '_blank')
  }
  printing.value = false
  alert('প্রতিটি ভরণা কার্ড একটি নতুন ট্যাবে খোলা হয়েছে। প্রিন্ট করতে প্রতিটি ট্যাবে Ctrl+P চাপুন।')
}

onMounted(load)
</script>

<style scoped>
.admit-list-page { max-width: 900px; margin:0 auto; padding:1.5rem; }
.page-header { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.3rem; }
.eyebrow { color: var(--color-primary); font: 600 .78rem var(--font-bn); }
.admit-list-page h1 { margin:.25rem 0; color: var(--color-primary); font: 700 1.4rem var(--font-bn); }
.header-actions { display:flex; gap:.5rem; }
.admit-grid { display:flex; flex-direction:column; gap:.5rem; }
.admit-row { display:flex; align-items:center; justify-content:space-between; padding:.8rem; background:#fff; border:1px solid var(--color-border-light); border-radius:12px; }
.admit-mini { display:flex; align-items:center; gap:.6rem; }
.mini-avatar { width:40px; height:40px; border-radius:50%; object-fit:cover; }
.mini-info b { font:.85rem var(--font-bn); display:block; }
.mini-info small { font:.68rem var(--font-bn); color: var(--color-text-muted); }
.admit-actions { display:flex; gap:.4rem; }
.empty-card { padding:2.5rem; text-align:center; background:#fff; border:1px dashed var(--color-border); border-radius:16px; }
.empty-card h3 { margin:.5rem 0; font:700 1rem var(--font-bn); }
.empty-card p { color: var(--color-text-muted); font:.8rem var(--font-bn); }
.empty-icon { color: var(--color-primary); font-size: 2rem; }
.mb-2 { margin-bottom: .5rem; }
</style>
