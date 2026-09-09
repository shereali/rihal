<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">যাতায়াত ও পরিবহন</span>
        <h1>পরিবহন রুট ব্যবস্থাপনা</h1>
        <p class="page-subtitle">মাদ্রাসার বাস রুট, স্টপেজ, দূরত্ব, ভাড়া ও সময়সূচী পরিচালনা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate">
          <icon name="plus" /> নতুন রুট যোগ করুন
        </button>
        <button class="btn btn-outline" @click="loadRoutes">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Stats summary -->
    <div class="stats-grid" v-if="routes.length">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="bus" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ routes.length.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট পরিবহন রুট</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ activeCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">সক্রিয় বাস রুট</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(averageFare) }} ৳</span>
          <span class="stat-label">গড় যাতায়াত ভাড়া</span>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="রুটের নাম, শুরু বা গন্তব্য খুঁজুন..." @keyup.enter="loadRoutes" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; loadRoutes()">×</button>
      </div>
      <select v-model="statusFilter" class="form-select" @change="loadRoutes">
        <option value="">সব অবস্থা</option>
        <option value="active">সক্রিয় রুট</option>
        <option value="inactive">নিষ্ক্রিয় রুট</option>
      </select>
      <div class="pagination-info" v-if="routes.length">
        মোট <span class="highlight">{{ routes.length.toLocaleString('bn-BD') }}</span> টি রুট
      </div>
    </div>

    <!-- Create / Edit Route Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ editingId ? 'রুট সম্পাদনা' : 'নতুন রুট যোগ করুন' }}</h3>
            <p>রুটের নাম, প্রারম্ভিক ও শেষ স্থান, দূরত্ব এবং সময়সূচী নির্ধারণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showForm = false">×</button>
        </div>

        <form @submit.prevent="saveRoute" class="modal-form">
          <div v-if="error" class="alert alert-error">{{ error }}</div>

          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">রুটের নাম (বাংলা) *</label>
              <input v-model="form.route_name_bn" class="form-input" required placeholder="যেমন: মিরপুর ১০ - মাদ্রাসা ক্যাম্পাস" />
            </div>
            <div class="form-group">
              <label class="form-label">রুটের নাম (ইংরেজি)</label>
              <input v-model="form.route_name_en" class="form-input" placeholder="e.g. Mirpur 10 - Campus" />
            </div>
            <div class="form-group">
              <label class="form-label">শুরুর স্থান *</label>
              <input v-model="form.start_point" class="form-input" required placeholder="যেমন: মিরপুর ১০ গোলচত্বর" />
            </div>
            <div class="form-group">
              <label class="form-label">গন্তব্য / শেষ স্থান *</label>
              <input v-model="form.end_point" class="form-input" required placeholder="যেমন: মাদ্রাসা মূল ফটক" />
            </div>
            <div class="form-group">
              <label class="form-label">দূরত্ব (কিলোমিটার)</label>
              <input v-model.number="form.distance_km" type="number" step="0.1" min="0" class="form-input" placeholder="৮.৫" />
            </div>
            <div class="form-group">
              <label class="form-label">মাসিক যাতায়াত ভাড়া (টাকা)</label>
              <input v-model.number="form.fare" type="number" min="0" class="form-input" placeholder="১৫০০" />
            </div>
            <div class="form-group">
              <label class="form-label">ছাড়ার সময়</label>
              <input v-model="form.departure_time" type="time" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">পৌঁছানোর সময়</label>
              <input v-model="form.arrival_time" type="time" class="form-input" />
            </div>
            <div class="form-group wide">
              <label class="custom-checkbox">
                <input type="checkbox" v-model="form.is_active" />
                <span class="checkbox-text">সক্রিয় রুট হিসেবে পরিচালনা করুন</span>
              </label>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'রুট যোগ করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>পরিবহন রুট লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!routes.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="bus" /></div>
      <h3>কোনো পরিবহন রুট পাওয়া যায়নি</h3>
      <p>নতুন রুট তৈরি করে পরিবহন ব্যবস্থা পরিচালনা শুরু করুন</p>
      <button class="btn btn-primary" @click="openCreate"><icon name="plus" /> প্রথম রুট যোগ করুন</button>
    </div>

    <!-- Routes Grid -->
    <div v-else class="routes-grid">
      <div v-for="r in routes" :key="r.id" class="route-card card">
        <div class="route-card-header">
          <div class="route-icon-box">
            <icon name="bus" />
          </div>
          <div class="route-title-block">
            <h3>{{ r.route_name_bn || r.route_name_en }}</h3>
            <span class="route-stops">{{ r.start_point }} → {{ r.end_point }}</span>
          </div>
          <span class="status-pill" :class="r.is_active ? 'badge-approved' : 'badge-rejected'">
            <span class="status-dot" />
            {{ r.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
          </span>
        </div>

        <div class="route-details">
          <div class="route-meta-row" v-if="r.distance_km">
            <icon name="building" class="meta-icon" />
            <span>দূরত্ব: <strong>{{ r.distance_km }} কিমি</strong></span>
          </div>
          <div class="route-meta-row" v-if="r.fare">
            <icon name="money" class="meta-icon" />
            <span>ভাড়া: <strong>{{ formatCurrency(r.fare) }} ৳</strong></span>
          </div>
          <div class="route-meta-row" v-if="r.departure_time || r.arrival_time">
            <icon name="clock" class="meta-icon" />
            <span>সময়: {{ r.departure_time || '—' }} থেকে {{ r.arrival_time || '—' }}</span>
          </div>
        </div>

        <div class="route-actions">
          <NuxtLink :to="`/transport/routes/${r.id}`" class="view-link">
            স্টপেজ ও বিবরণ <icon name="arrow-right" />
          </NuxtLink>
          <div class="action-buttons">
            <button class="action-btn" @click="openEdit(r)" title="সম্পাদনা">
              <icon name="pencil" />
            </button>
            <button class="action-btn text-danger" @click="deleteRoute(r.id)" title="মুছুন">
              <icon name="delete" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, computed } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const routes = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')

const search = ref('')
const statusFilter = ref('')

const form = reactive({
  route_name_bn: '',
  route_name_en: '',
  start_point: '',
  end_point: '',
  distance_km: 0,
  fare: 0,
  departure_time: '',
  arrival_time: '',
  is_active: true,
})

const activeCount = computed(() => routes.value.filter(r => r.is_active).length)
const averageFare = computed(() => {
  if (!routes.value.length) return 0
  const sum = routes.value.reduce((acc, r) => acc + (Number(r.fare) || 0), 0)
  return Math.round(sum / routes.value.length)
})

async function loadRoutes() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (statusFilter.value) q.set('status', statusFilter.value)

    const res = await api.get(`/transport/routes?${q.toString()}`)
    routes.value = res.data?.data?.data || res.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingId.value = null
  error.value = ''
  form.route_name_bn = ''
  form.route_name_en = ''
  form.start_point = ''
  form.end_point = ''
  form.distance_km = 0
  form.fare = 0
  form.departure_time = ''
  form.arrival_time = ''
  form.is_active = true
  showForm.value = true
}

function openEdit(r: any) {
  editingId.value = r.id
  error.value = ''
  form.route_name_bn = r.route_name_bn || ''
  form.route_name_en = r.route_name_en || ''
  form.start_point = r.start_point || ''
  form.end_point = r.end_point || ''
  form.distance_km = Number(r.distance_km) || 0
  form.fare = Number(r.fare) || 0
  form.departure_time = r.departure_time || ''
  form.arrival_time = r.arrival_time || ''
  form.is_active = !!r.is_active
  showForm.value = true
}

async function saveRoute() {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/transport/routes/${editingId.value}`, form)
    } else {
      await api.post('/transport/routes', form)
    }
    showForm.value = false
    await loadRoutes()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'রুট সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function deleteRoute(id: number) {
  if (!confirm('আপনি কি এই পরিবহন রুট মুছে ফেলতে চান?')) return
  try {
    await api.delete(`/transport/routes/${id}`)
    await loadRoutes()
  } catch (e) {
    console.error(e)
  }
}

function formatCurrency(val: number) {
  if (!val) return '০'
  return val.toLocaleString('bn-BD')
}

onMounted(loadRoutes)
</script>

<style scoped>
.page-wrapper {
  max-width: 1320px;
  margin: 0 auto;
  padding: 1.75rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.75rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.eyebrow {
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  color: var(--color-primary);
  letter-spacing: 0.08em;
}

.header-title-block h1 {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0.2rem 0 0.35rem;
  color: var(--color-text);
}

.page-subtitle {
  color: var(--color-text-light);
  font-size: 0.88rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.6rem;
  align-items: center;
}

.clear-search-btn {
  background: none;
  border: none;
  font-size: 1.1rem;
  color: var(--color-text-light);
  cursor: pointer;
  padding: 0 0.2rem;
}

.pagination-info {
  margin-left: auto;
  font-size: 0.85rem;
  color: var(--color-text-light);
}

.pagination-info .highlight {
  font-weight: 700;
  color: var(--color-primary);
}

/* Routes Grid */
.routes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

.route-card {
  padding: 1.35rem;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 14px;
}

.route-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.route-card-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 1rem;
  padding-bottom: 0.85rem;
  border-bottom: 1px solid var(--color-border-light);
}

.route-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.route-title-block {
  flex: 1;
  min-width: 0;
}

.route-title-block h3 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 0.2rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.route-stops {
  font-size: 0.78rem;
  color: var(--color-text-light);
  display: block;
}

.route-details {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin-bottom: 1.25rem;
  font-size: 0.84rem;
  color: var(--color-text-light);
}

.route-meta-row {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.meta-icon {
  font-size: 0.95rem;
  color: var(--color-text-muted);
}

.route-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid var(--color-border-light);
}

.view-link {
  font-size: 0.84rem;
  font-weight: 600;
  color: var(--color-primary);
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  text-decoration: none;
}

.view-link:hover {
  text-decoration: underline;
}

.action-buttons {
  display: flex;
  gap: 0.35rem;
}

.action-btn {
  width: 30px;
  height: 30px;
  border-radius: 6px;
  border: 1px solid var(--color-border-light);
  background: var(--color-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--color-text-light);
  transition: all 0.15s ease;
}

.action-btn:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--color-text);
  transform: translateY(-1px);
}

.action-btn.text-danger:hover {
  color: #dc2626;
  border-color: #dc2626;
  background: rgba(239, 68, 68, 0.1);
}

.btn {
  padding: 0.6rem 1.15rem;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #145032 0%, #1a6b43 100%);
  color: #fff;
  box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35);
}

.btn-outline {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.btn-ghost {
  background: transparent;
  color: var(--color-text);
}

.btn-ghost:hover {
  background: rgba(0, 0, 0, 0.05);
}

.modal-title-group h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 0.2rem; }
.modal-title-group p { font-size: 0.82rem; color: var(--color-text-light); margin: 0; }
.modal-close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-light); line-height: 1; }
.modal-form { padding: 1.5rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
.form-group.wide { grid-column: 1 / -1; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-light); }

.empty-icon-wrap {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 1rem;
}
.empty-state h3 { font-size: 1.2rem; margin: 0 0 0.35rem; color: var(--color-text); }
.empty-state p { font-size: 0.88rem; margin: 0 0 1.25rem; }
</style>