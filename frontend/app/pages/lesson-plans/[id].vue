<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/lesson-plans">পাঠ পরিকল্পনা</NuxtLink>
      <icon name="chevron-down" class="breadcrumb-sep rotate-270" />
      <span>{{ plan?.topic_bn || 'পরিকল্পনা বিবরণ' }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">একাডেমিক পরিকল্পনা</span>
        <h1>{{ plan ? plan.topic_bn : 'পাঠ পরিকল্পনা বিবরণ' }}</h1>
        <p v-if="plan">শ্রেণি: {{ plan.class?.name_bn || 'সকল শ্রেণি' }} • বিষয়: {{ plan.subject?.name_bn || 'সাধারণ' }}</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/lesson-plans" class="btn btn-outline">
          <icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
        <button class="btn btn-primary" @click="openEditModal" v-if="plan">
          <icon name="pencil" /> সম্পাদনা করুন
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner" />
      <p>পাঠ পরিকল্পনা লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!plan" class="empty-card">
      <icon name="alert-circle" />
      <h3>পাঠ পরিকল্পনা পাওয়া যায়নি</h3>
      <NuxtLink to="/lesson-plans" class="btn btn-primary">তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <div class="card detail-card">
        <div class="plan-header">
          <div>
            <h2>{{ plan.topic_bn }}</h2>
            <span v-if="plan.topic_en" class="text-muted">{{ plan.topic_en }}</span>
          </div>
          <span class="status-badge" :class="plan.is_active ? 'active' : 'inactive'">
            {{ plan.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>

        <div class="info-grid">
          <div class="info-block">
            <label>শ্রেণির তারিখ</label>
            <p>{{ formatDate(plan.class_date) }}</p>
          </div>
          <div class="info-block">
            <label>শিক্ষক</label>
            <p>{{ plan.teacher?.user?.name_bn || plan.teacher?.name_bn || 'নির্ধারিত নয়' }}</p>
          </div>
          <div class="info-block">
            <label>শ্রেণি / জামাত</label>
            <p>{{ plan.class?.name_bn || 'সকল শ্রেণি' }}</p>
          </div>
          <div class="info-block">
            <label>বিষয়</label>
            <p>{{ plan.subject?.name_bn || 'নির্ধারিত নয়' }}</p>
          </div>
        </div>

        <div class="content-section" v-if="plan.content_bn">
          <h4>পাঠের মূল বিবরণ</h4>
          <div class="content-box">{{ plan.content_bn }}</div>
        </div>

        <div class="content-section" v-if="plan.objectives && plan.objectives.length">
          <h4>শিক্ষার উদ্দেশ্য ও লক্ষ্য</h4>
          <ul class="objectives-list">
            <li v-for="(obj, idx) in plan.objectives" :key="idx">• {{ obj }}</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showEdit" class="modal-overlay" @click.self="showEdit = false">
          <div class="modal-card modal-lg">
            <div class="modal-header">
              <h3>পাঠ পরিকল্পনা সম্পাদনা</h3>
              <button class="modal-close" @click="showEdit = false">×</button>
            </div>
            <form @submit.prevent="saveEdit">
              <div class="modal-body">
                <div class="form-group">
                  <label class="form-label">পাঠের বিষয় (বাংলায়) *</label>
                  <input v-model="editForm.topic_bn" class="form-control" required />
                </div>
                <div class="form-group">
                  <label class="form-label">পাঠের বিষয় (ইংরেজি)</label>
                  <input v-model="editForm.topic_en" class="form-control" />
                </div>
                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">শ্রেণির তারিখ</label>
                    <input v-model="editForm.class_date" type="date" class="form-control" />
                  </div>
                  <div class="form-group">
                    <label class="form-check-label" style="margin-top: 1.8rem;">
                      <input type="checkbox" v-model="editForm.is_active" /> সক্রিয় পরিকল্পনা
                    </label>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">পাঠের বিস্তারিত বিবরণ</label>
                  <textarea v-model="editForm.content_bn" class="form-control" rows="4"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-ghost" @click="showEdit = false">বাতিল</button>
                <button type="submit" class="btn btn-primary" :disabled="saving || !editForm.topic_bn">
                  <icon v-if="saving" name="loader" />
                  {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const planId = route.params.id

const plan = ref<any>(null)
const loading = ref(true)
const showEdit = ref(false)
const saving = ref(false)

const editForm = reactive({
  topic_bn: '',
  topic_en: '',
  class_date: '',
  content_bn: '',
  is_active: true,
})

function openEditModal() {
  if (!plan.value) return
  const p = plan.value
  editForm.topic_bn = p.topic_bn || ''
  editForm.topic_en = p.topic_en || ''
  editForm.class_date = p.class_date ? String(p.class_date).slice(0, 10) : ''
  editForm.content_bn = p.content_bn || ''
  editForm.is_active = p.is_active ?? true
  showEdit.value = true
}

async function saveEdit() {
  if (!editForm.topic_bn.trim()) return
  saving.value = true
  try {
    const res = await api.put(`/lesson-plans/${planId}`, editForm)
    plan.value = res.data?.data || { ...plan.value, ...editForm }
    showEdit.value = false
    alert('পাঠ পরিকল্পনা সফলভাবে আপডেট করা হয়েছে!')
  } catch (err: any) {
    console.error('Update lesson plan error:', err)
    alert(err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে')
  } finally {
    saving.value = false
  }
}

async function load() {
  loading.value = true
  try {
    const res = await api.get(`/lesson-plans/${planId}`)
    plan.value = res.data?.data
  } catch (e) {
    console.error('Failed to load lesson plan:', e)
  } finally {
    loading.value = false
  }
}

function formatDate(v: string | null | undefined) {
  return v ? new Date(v).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
}

onMounted(load)
</script>

<style scoped>
.module-page { max-width: 960px; margin: 0 auto; padding: 1.5rem; }
.breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1rem; }
.breadcrumb a { color: var(--color-primary); text-decoration: none; }
.page-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-actions { display: flex; gap: 0.5rem; }
.card { background: var(--color-bg-card, #fff); border: 1px solid var(--color-border); border-radius: 12px; padding: 1.5rem; }
.plan-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 1rem; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.info-block label { font-size: 0.8rem; color: var(--color-text-muted); }
.info-block p { font-weight: 600; margin: 0.25rem 0 0 0; }
.content-section { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--color-border); }
.content-section h4 { margin: 0 0 0.75rem 0; font-size: 1rem; color: var(--color-primary); }
.content-box { background: var(--color-bg-subtle, #f9fafb); padding: 1rem; border-radius: 8px; line-height: 1.6; }
.objectives-list { list-style: none; padding: 0; margin: 0; }
.objectives-list li { padding: 0.35rem 0; }
.status-badge { padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
.status-badge.active { background: #dcfce7; color: #166534; }
.status-badge.inactive { background: #fee2e2; color: #991b1b; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; }
.modal-card { background: var(--color-bg-card, #fff); border-radius: 12px; width: 100%; max-width: 600px; overflow: hidden; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-muted); }
.modal-body { padding: 1.5rem; max-height: 75vh; overflow-y: auto; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: 0.75rem; }
.form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.35rem; }
.form-label { font-size: 0.85rem; font-weight: 600; }
.form-control { padding: 0.55rem 0.85rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); font-size: 0.9rem; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
</style>