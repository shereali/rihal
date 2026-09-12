<template>
  <div class="plans-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">একাডেমিক কার্যক্রম</span>
        <h1>পাঠ পরিকল্পনা</h1>
        <p>শিক্ষকদের দৈনিক পাঠ, উদ্দেশ্য ও শ্রেণি কার্যক্রম সাজান</p>
      </div>
      <button class="btn btn-primary" @click="showForm = !showForm">
        <icon name="plus" /> নতুন পাঠ পরিকল্পনা
      </button>
    </div>

    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" />
        <input v-model="search" placeholder="পাঠের বিষয় খুঁজুন..." @keyup.enter="load" />
      </div>
      <button class="btn btn-outline btn-sm" @click="load">
        <icon name="refresh" /> রিফ্রেশ
      </button>
    </div>

    <form v-if="showForm" class="create-panel card" @submit.prevent="createPlan">
      <div class="form-heading">
        <div>
          <h2>নতুন পাঠ পরিকল্পনা</h2>
          <p>আজকের পাঠের মূল তথ্য লিখুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>
      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div class="form-grid">
        <div class="form-group wide">
          <label>পাঠের বিষয় *</label>
          <input v-model="form.topic_bn" class="form-control" required placeholder="যেমন: ইসলামের পাঁচ স্তম্ভ" />
        </div>
        <div class="form-group">
          <label>শ্রেণির তারিখ</label>
          <input v-model="form.class_date" type="date" class="form-control" />
        </div>
        <div class="form-group wide">
          <label>পাঠের বিবরণ</label>
          <textarea v-model="form.content_bn" class="form-control" rows="3" placeholder="আজকের পাঠে যা পড়ানো হবে..."></textarea>
        </div>
        <div class="form-group wide">
          <label>শিক্ষার উদ্দেশ্য</label>
          <textarea v-model="form.objectives_text" class="form-control" rows="2" placeholder="এই পাঠ শেষে শিক্ষার্থী কী শিখবে?"></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিকল্পনা সংরক্ষণ করুন' }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-state">
      <div class="spinner" />
      <p>পাঠ পরিকল্পনা লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!plans.length" class="empty-card">
      <div class="empty-icon"><icon name="academic" /></div>
      <h3>কোনো পাঠ পরিকল্পনা নেই</h3>
      <p>নতুন পরিকল্পনা তৈরি করে আপনার ক্লাসগুলো গুছিয়ে নিন</p>
      <button class="btn btn-primary" @click="showForm = true">পরিকল্পনা তৈরি করুন</button>
    </div>

    <div v-else class="plan-grid">
      <article v-for="plan in plans" :key="plan.id" class="plan-card">
        <div class="plan-top">
          <span class="date-chip"><icon name="calendar" /> {{ formatDate(plan.class_date) }}</span>
          <span class="status" :class="plan.is_active ? 'active' : 'inactive'">
            {{ plan.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>
        <h3>{{ plan.topic_bn || plan.topic_en }}</h3>
        <p>{{ plan.content_bn || 'পাঠের বিস্তারিত যোগ করা হয়নি' }}</p>
        <div class="plan-meta">
          <span><icon name="academic" /> {{ plan.class?.name_bn || 'সব শ্রেণি' }}</span>
          <span><icon name="book" /> {{ plan.subject?.name_bn || 'বিষয় নেই' }}</span>
        </div>
        <div class="plan-footer">
          <span>শিক্ষক: {{ plan.teacher?.user?.name_bn || 'নির্ধারিত নয়' }}</span>
          <button class="icon-btn" title="মুছে ফেলুন" @click="confirmDelete(plan)">
            <icon name="delete" />
          </button>
        </div>
      </article>
    </div>

    <!-- In-App Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-card modal-sm animate-fade-in">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>পাঠ পরিকল্পনা মুছে ফেলা</h3>
          </div>
          <button class="close-btn" @click="showDeleteModal = false">×</button>
        </div>
        <div class="modal-body" style="padding: 1.25rem 1.5rem;">
          <p style="color: var(--color-text-light, #4b5563); font-size: 0.95rem; line-height: 1.5;">
            আপনি কি নিশ্চিত যে <strong>"{{ deleteTarget?.topic_bn || 'এই পাঠ পরিকল্পনা' }}"</strong> মুছে ফেলতে চান?
          </p>
        </div>
        <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid var(--color-border-light, #e5e7eb);">
          <button type="button" class="btn btn-ghost" @click="showDeleteModal = false" :disabled="deleting">
            বাতিল
          </button>
          <button type="button" class="btn btn-danger" @click="executeDelete" :disabled="deleting" style="background: #ef4444; color: #fff; border: none; padding: 0.5rem 1.25rem; border-radius: 0.5rem; cursor: pointer;">
            <span v-if="deleting">মুছে ফেলা হচ্ছে...</span>
            <span v-else>নিশ্চিত মুছুন</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const plans = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')
const search = ref('')

const showDeleteModal = ref(false)
const deleteTarget = ref<any>(null)
const deleting = ref(false)

const form = reactive({
  topic_bn: '',
  content_bn: '',
  objectives_text: '',
  class_date: '',
})

async function load() {
  loading.value = true
  try {
    const q = search.value ? `?search=${encodeURIComponent(search.value)}` : ''
    const r = await api.get(`/lesson-plans${q}`)
    plans.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function createPlan() {
  saving.value = true
  error.value = ''
  try {
    const payload = {
      ...form,
      objectives: form.objectives_text ? [form.objectives_text] : [],
    }
    delete (payload as any).objectives_text
    await api.post('/lesson-plans', payload)
    form.topic_bn = ''
    form.content_bn = ''
    form.objectives_text = ''
    form.class_date = ''
    showForm.value = false
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'পাঠ পরিকল্পনা তৈরি করা যায়নি'
  } finally {
    saving.value = false
  }
}

function confirmDelete(plan: any) {
  deleteTarget.value = plan
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/lesson-plans/${deleteTarget.value.id}`)
    showDeleteModal.value = false
    deleteTarget.value = null
    await load()
  } catch (e) {
    console.error(e)
  } finally {
    deleting.value = false
  }
}

function formatDate(v: string) {
  return v
    ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
    : 'তারিখ নেই'
}

onMounted(load)
</script>

<style scoped>
.plans-page {
  max-width: 1400px;
  margin: 0 auto;
  padding-bottom: 2rem;
}
.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 1.4rem;
}
.eyebrow {
  color: var(--color-primary);
  font: 600 0.78rem var(--font-bn);
}
.page-header-row h1 {
  margin: 0.25rem 0;
  color: var(--color-primary);
  font: 700 1.65rem var(--font-bn);
}
.page-header-row p {
  color: var(--color-text-light);
  font: 0.88rem var(--font-bn);
}
.toolbar {
  display: flex;
  gap: 0.7rem;
  padding: 0.7rem;
  margin-bottom: 1rem;
}
.search-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
  padding: 0 0.75rem;
  background: var(--color-bg-muted);
  border-radius: 10px;
}
.search-box input {
  width: 100%;
  padding: 0.65rem 0;
  border: 0;
  outline: 0;
  background: transparent;
  font: 0.86rem var(--font-bn);
}
.create-panel {
  padding: 1.25rem;
  margin-bottom: 1rem;
  border: 1px solid var(--color-primary-100);
}
.form-heading {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}
.form-heading h2 {
  font: 700 1.05rem var(--font-bn);
}
.form-heading p {
  margin-top: 0.2rem;
  color: var(--color-text-light);
  font: 0.78rem var(--font-bn);
}
.close-btn {
  border: 0;
  background: transparent;
  color: var(--color-text-muted);
  font-size: 1.5rem;
  cursor: pointer;
}
.form-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 0.8rem;
}
.form-group.wide {
  grid-column: span 2;
}
.form-group label {
  display: block;
  margin-bottom: 0.35rem;
  font: 600 0.8rem var(--font-bn);
}
.form-actions {
  display: flex;
  gap: 0.6rem;
  margin-top: 0.4rem;
}
.plan-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}
.plan-card {
  display: flex;
  flex-direction: column;
  min-height: 235px;
  padding: 1.1rem;
  background: #fff;
  border: 1px solid var(--color-border-light);
  border-radius: 17px;
  box-shadow: var(--shadow-sm);
  transition: 0.2s;
}
.plan-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}
.plan-top,
.plan-meta,
.plan-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
}
.date-chip,
.status {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.25rem 0.6rem;
  border-radius: 99px;
  font: 0.7rem var(--font-bn);
}
.date-chip {
  color: var(--color-primary);
  background: var(--color-primary-50);
}
.status.active {
  color: #19724a;
  background: #e6f4ec;
}
.status.inactive {
  color: #a05c35;
  background: #fff0e4;
}
.plan-card h3 {
  margin: 1rem 0 0.4rem;
  color: var(--color-text);
  font: 700 1rem var(--font-bn);
}
.plan-card > p {
  min-height: 45px;
  color: var(--color-text-light);
  font: 0.8rem/1.6 var(--font-bn);
}
.plan-meta {
  padding: 0.8rem 0;
  margin-top: auto;
  border-top: 1px solid var(--color-border-light);
  color: var(--color-text-light);
  font: 0.7rem var(--font-bn);
}
.plan-meta span {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}
.plan-footer {
  padding-top: 0.65rem;
  color: var(--color-text-muted);
  font: 0.7rem var(--font-bn);
}
.icon-btn {
  display: grid;
  place-items: center;
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: 8px;
  color: var(--color-error);
  background: var(--color-error-bg);
  cursor: pointer;
}
.empty-card {
  padding: 3rem;
  text-align: center;
  background: #fff;
  border: 1px dashed var(--color-border);
  border-radius: 18px;
}
.empty-icon {
  display: grid;
  place-items: center;
  width: 54px;
  height: 54px;
  margin: 0 auto 1rem;
  border-radius: 16px;
  color: var(--color-primary);
  background: var(--color-primary-50);
  font-size: 1.4rem;
}
.empty-card h3 {
  font: 700 1rem var(--font-bn);
}
.empty-card p {
  margin: 0.4rem 0 1rem;
  color: var(--color-text-light);
  font: 0.8rem var(--font-bn);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}
.modal-card.modal-sm {
  max-width: 440px;
  width: 100%;
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--color-border-light, #e5e7eb);
}
.modal-title-group h3 {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text, #111827);
}

@media (max-width: 1050px) {
  .plan-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (max-width: 700px) {
  .page-header-row {
    align-items: flex-start;
    flex-direction: column;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
  .form-group.wide {
    grid-column: auto;
  }
  .plan-grid {
    grid-template-columns: 1fr;
  }
}
</style>
