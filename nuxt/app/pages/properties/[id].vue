<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/properties">সম্পত্তি ও সম্পদ</NuxtLink>
      <icon name="chevron-down" class="breadcrumb-sep rotate-270" />
      <span>{{ property?.property_name_bn || 'সম্পত্তি বিবরণী' }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">সম্পদ ও অবকাঠামো</span>
        <h1>সম্পত্তির বিবরণী</h1>
        <p>{{ property?.property_name_bn }} — ধরণ, অবস্থা, মূল্য ও সংশ্লিষ্ট তথ্য</p>
      </div>
      <NuxtLink to="/properties" class="btn btn-outline">
        <icon name="arrow-left" /> ফিরে যান
      </NuxtLink>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>তথ্য লোড হচ্ছে...</p></div>
    <div v-else-if="!property" class="empty-card">
      <div class="empty-icon"><icon name="building" /></div>
      <h3>সম্পত্তি পাওয়া যায়নি</h3>
      <NuxtLink to="/properties" class="btn btn-primary">সম্পত্তি তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <!-- Main Property Info Card -->
      <div class="card detail-card">
        <div class="property-header">
          <div class="property-identification">
            <h2 class="property-title">{{ property.property_name_bn }}</h2>
            <span v-if="property.property_name_en" class="property-en">{{ property.property_name_en }}</span>
          </div>
          <span class="status-badge" :class="property.status">
            {{ formatStatus(property.status) }}
          </span>
        </div>

        <div class="info-grid">
          <div class="info-block">
            <label>ধরণ</label>
            <p>{{ property.property_type || 'নির্ধারিত নয়' }}</p>
          </div>
          <div class="info-block">
            <label>অবস্থা</label>
            <p>{{ formatStatus(property.status) }}</p>
          </div>
          <div class="info-block">
            <label>আয়তন (বর্গফুট)</label>
            <p>{{ property.land_area_sqft ? property.land_area_sqft.toLocaleString('bn-BD') + ' বর্গফুট' : '—' }}</p>
          </div>
          <div class="info-block wide">
            <label>বর্তমান বাজারমূল্য</label>
            <p class="highlight-val">{{ property.current_market_value ? formatCurrency(property.current_market_value) + ' টাকা' : 'নির্ধারিত নয়' }}</p>
          </div>
          <div class="info-block" v-if="property.registration_number">
            <label>দলিল / নিবন্ধন নং</label>
            <p>{{ property.registration_number }}</p>
          </div>
        </div>

        <div class="address-section">
          <div class="address-label">ঠিকানা / অবস্থান</div>
          <div class="address-text">{{ property.location_address_bn || property.location_address_en || 'ঠিকানা নির্ধারিত নয়' }}</div>
        </div>
      </div>

      <!-- Document Section -->
      <div class="card">
        <div class="card-header">
          <h3>ডকুমেন্ট ও ফাইল</h3>
          <button class="btn btn-sm btn-primary" @click="showDocModal = true">
            <icon name="plus" /> ডকুমেন্ট যোগ
          </button>
        </div>
        <div class="card-body">
          <div v-if="!documents.length" class="empty-slate">
            <icon name="file-text" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো ডকুমেন্ট সংযুক্ত নেই</p>
          </div>
          <div v-else class="doc-list">
            <div v-for="doc in documents" :key="doc.id" class="doc-item">
              <icon name="file-text" />
              <div class="doc-info">
                <span class="doc-title">{{ doc.document_title || doc.title || 'ডকুমেন্ট' }}</span>
                <small class="text-muted">{{ doc.document_type || doc.created_at?.slice(0, 10) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Maintenance Section -->
      <div class="card">
        <div class="card-header">
          <h3>রক্ষণাবেক্ষণ ইতিহাস</h3>
          <button class="btn btn-sm btn-primary" @click="showMaintModal = true">
            <icon name="plus" /> নতুন রক্ষণাবেক্ষণ
          </button>
        </div>
        <div class="card-body">
          <div v-if="!maintenance.length" class="empty-slate">
            <icon name="tools" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো রক্ষণাবেক্ষণ রেকর্ড নেই</p>
          </div>
          <div v-else class="maint-list">
            <div v-for="m in maintenance" :key="m.id" class="maint-item">
              <div class="maint-title-row">
                <strong>{{ m.title_bn || m.description_bn || 'রক্ষণাবেক্ষণ' }}</strong>
                <span class="badge" :class="m.status === 'completed' ? 'badge-success' : 'badge-warning'">
                  {{ m.status === 'completed' ? 'সম্পন্ন' : 'চলমান' }}
                </span>
              </div>
              <p class="maint-cost" v-if="m.cost">ব্যয়: {{ formatCurrency(m.cost) }} টাকা</p>
              <small class="text-muted">{{ m.maintenance_date || m.created_at?.slice(0, 10) }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Visitors Section -->
      <div class="card">
        <div class="card-header">
          <h3>পরিদর্শক ও ভিজিটর</h3>
          <button class="btn btn-sm btn-primary" @click="showVisitorModal = true">
            <icon name="plus" /> ভিজিটর এন্ট্রি
          </button>
        </div>
        <div class="card-body">
          <div v-if="!visitors.length" class="empty-slate">
            <icon name="users" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো ভিজিটর রেকর্ড নেই</p>
          </div>
          <div v-else class="visitor-list">
            <div v-for="v in visitors" :key="v.id" class="visitor-item">
              <strong>{{ v.visitor_name || v.name_bn }}</strong>
              <span>উদ্দেশ্য: {{ v.purpose || v.notes_bn || 'পরিদর্শন' }}</span>
              <small class="text-muted">{{ v.visit_date || v.created_at?.slice(0, 10) }}</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document Modal -->
    <div v-if="showDocModal" class="modal-overlay" @click.self="showDocModal = false">
      <div class="modal card">
        <div class="modal-header">
          <h3>ডকুমেন্ট যোগ করুন</h3>
          <button class="close-btn" @click="showDocModal = false">×</button>
        </div>
        <form @submit.prevent="saveDoc" class="modal-body">
          <div class="form-group">
            <label>ডকুমেন্টের নাম *</label>
            <input v-model="docForm.document_title" class="form-control" required placeholder="যেমন: জমি ক্রয় চুক্তিপত্র" />
          </div>
          <div class="form-group">
            <label>ডকুমেন্টের ধরণ</label>
            <input v-model="docForm.document_type" class="form-control" placeholder="যেমন: চুক্তি / দলিল" />
          </div>
          <div class="form-group">
            <label>ফাইল লিংক / URL</label>
            <input v-model="docForm.file_url" class="form-control" placeholder="https://..." />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving">সংরক্ষণ করুন</button>
            <button type="button" class="btn btn-ghost" @click="showDocModal = false">বাতিল</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Maintenance Modal -->
    <div v-if="showMaintModal" class="modal-overlay" @click.self="showMaintModal = false">
      <div class="modal card">
        <div class="modal-header">
          <h3>রক্ষণাবেক্ষণ এন্ট্রি</h3>
          <button class="close-btn" @click="showMaintModal = false">×</button>
        </div>
        <form @submit.prevent="saveMaint" class="modal-body">
          <div class="form-group">
            <label>কাজের বিবরণ *</label>
            <input v-model="maintForm.title_bn" class="form-control" required placeholder="যেমন: ভবন রং করা ও ছাদ মেরামত" />
          </div>
          <div class="form-group">
            <label>ব্যয় (টাকা)</label>
            <input v-model.number="maintForm.cost" type="number" min="0" class="form-control" placeholder="০" />
          </div>
          <div class="form-group">
            <label>তারিখ</label>
            <input v-model="maintForm.maintenance_date" type="date" class="form-control" />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving">সংরক্ষণ করুন</button>
            <button type="button" class="btn btn-ghost" @click="showMaintModal = false">বাতিল</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Visitor Modal -->
    <div v-if="showVisitorModal" class="modal-overlay" @click.self="showVisitorModal = false">
      <div class="modal card">
        <div class="modal-header">
          <h3>ভিজিটর এন্ট্রি</h3>
          <button class="close-btn" @click="showVisitorModal = false">×</button>
        </div>
        <form @submit.prevent="saveVisitor" class="modal-body">
          <div class="form-group">
            <label>ভিজিটরের নাম *</label>
            <input v-model="visitorForm.visitor_name" class="form-control" required placeholder="ভিজিটরের নাম" />
          </div>
          <div class="form-group">
            <label>পরিদর্শনের উদ্দেশ্য</label>
            <input v-model="visitorForm.purpose" class="form-control" placeholder="যেমন: জমি পরিদর্শন" />
          </div>
          <div class="form-group">
            <label>ফোন নম্বর</label>
            <input v-model="visitorForm.phone" class="form-control" placeholder="০১৭XXXXXXXX" />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving">সংরক্ষণ করুন</button>
            <button type="button" class="btn btn-ghost" @click="showVisitorModal = false">বাতিল</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const propertyId = route.params.id

const property = ref<any>(null)
const documents = ref<any[]>([])
const maintenance = ref<any[]>([])
const visitors = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)

const showDocModal = ref(false)
const showMaintModal = ref(false)
const showVisitorModal = ref(false)

const docForm = reactive({ document_title: '', document_type: '', file_url: '' })
const maintForm = reactive({ title_bn: '', cost: 0, maintenance_date: new Date().toISOString().slice(0, 10), status: 'completed' })
const visitorForm = reactive({ visitor_name: '', purpose: '', phone: '' })

async function loadData() {
  loading.value = true
  try {
    const [pRes, dRes, mRes, vRes] = await Promise.all([
      api.get(`/properties/${propertyId}`),
      api.get(`/properties/${propertyId}/documents`).catch(() => ({ data: { data: [] } })),
      api.get(`/properties/${propertyId}/maintenance`).catch(() => ({ data: { data: [] } })),
      api.get(`/properties/${propertyId}/visitors`).catch(() => ({ data: { data: [] } })),
    ])
    property.value = pRes.data?.data
    documents.value = dRes.data?.data || []
    maintenance.value = mRes.data?.data || []
    visitors.value = vRes.data?.data || []
  } catch (e) {
    console.error('Failed to load property details:', e)
  } finally {
    loading.value = false
  }
}

async function saveDoc() {
  saving.value = true
  try {
    await api.post(`/properties/${propertyId}/documents`, docForm)
    showDocModal.value = false
    docForm.document_title = ''
    docForm.document_type = ''
    docForm.file_url = ''
    await loadData()
  } catch (e) {
    console.error(e)
  } finally {
    saving.value = false
  }
}

async function saveMaint() {
  saving.value = true
  try {
    await api.post(`/properties/${propertyId}/maintenance`, maintForm)
    showMaintModal.value = false
    maintForm.title_bn = ''
    maintForm.cost = 0
    await loadData()
  } catch (e) {
    console.error(e)
  } finally {
    saving.value = false
  }
}

async function saveVisitor() {
  saving.value = true
  try {
    await api.post(`/properties/${propertyId}/visitors`, visitorForm)
    showVisitorModal.value = false
    visitorForm.visitor_name = ''
    visitorForm.purpose = ''
    visitorForm.phone = ''
    await loadData()
  } catch (e) {
    console.error(e)
  } finally {
    saving.value = false
  }
}

function formatStatus(status: string) {
  const map: Record<string, string> = {
    owned: 'নিজস্ব',
    rented: 'ভাড়া',
    leased: 'লিজ',
    under_construction: 'নির্মাণাধীন',
    completed: 'সম্পন্ন',
  }
  return map[status] || status || 'অন্যান্য'
}

function formatCurrency(val: number) {
  if (!val) return '০'
  return val.toLocaleString('bn-BD')
}

onMounted(loadData)
</script>

<style scoped>
.module-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--color-text-light);
  margin-bottom: 1rem;
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.rotate-270 {
  transform: rotate(270deg);
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.eyebrow {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--color-primary);
}

.detail-layout {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.25rem;
}

.detail-card {
  grid-column: 1 / -1;
}

.property-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
}

.property-title {
  font-size: 1.35rem;
  margin: 0;
}

.property-en {
  font-size: 0.85rem;
  color: var(--color-text-light);
}

.status-badge {
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  background: rgba(20, 80, 50, 0.1);
  color: var(--color-primary);
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  padding: 1.25rem;
}

.info-block label {
  font-size: 0.78rem;
  color: var(--color-text-light);
  display: block;
  margin-bottom: 0.2rem;
}

.info-block p {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0;
}

.highlight-val {
  color: var(--color-primary);
  font-size: 1.15rem !important;
}

.address-section {
  padding: 1rem 1.25rem;
  border-top: 1px solid var(--color-border-light);
  background: rgba(0, 0, 0, 0.015);
}

.address-label {
  font-size: 0.78rem;
  color: var(--color-text-light);
  margin-bottom: 0.2rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
}

.card-header h3 {
  font-size: 1rem;
  margin: 0;
}

.card-body {
  padding: 1.25rem;
}

.empty-slate {
  text-align: center;
  padding: 1.5rem;
  color: var(--color-text-light);
}

.doc-list, .maint-list, .visitor-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.doc-item, .maint-item, .visitor-item {
  padding: 0.65rem 0.85rem;
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-sm);
  background: var(--color-bg);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.maint-item, .visitor-item {
  flex-direction: column;
  align-items: flex-start;
}

.maint-title-row {
  display: flex;
  justify-content: space-between;
  width: 100%;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  padding: 1rem;
}

.modal {
  width: 100%;
  max-width: 480px;
  background: var(--color-bg-card);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
}

.modal-header h3 { margin: 0; font-size: 1.1rem; }
.modal-body { padding: 1.25rem; }

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.82rem;
  font-weight: 500;
  margin-bottom: 0.35rem;
}

.form-control {
  width: 100%;
  padding: 0.55rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.9rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.25rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
}

.btn-primary { background: var(--color-primary); color: #fff; }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn-ghost { background: transparent; color: var(--color-text); }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }

.card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 0.5rem;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>