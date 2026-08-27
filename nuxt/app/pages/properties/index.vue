<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">সম্পদ ও অবকাঠামো</span>
        <h1>সম্পত্তি ও সম্পদ ব্যবস্থাপনা</h1>
        <p class="page-subtitle">মাদ্রাসার জমি, ভবন, যানবাহন, সরঞ্জাম — তালিকা, মূল্য ও অবস্থান</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate">
          <icon name="plus" /> নতুন সম্পত্তি
        </button>
        <button class="btn btn-outline" @click="loadProperties">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Stats summary -->
    <div class="stats-grid" v-if="properties.length">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ properties.length.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট সম্পত্তি</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(totalValuation) }} ৳</span>
          <span class="stat-label">মোট বাজারমূল্য</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ ownedCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">নিজস্ব সম্পত্তি</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="clock" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ rentedCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">ভাড়া / লিজ</span>
        </div>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="সম্পত্তির নাম বা ঠিকানা খুঁজুন..." @keyup.enter="loadProperties" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; loadProperties()">×</button>
      </div>
      <select v-model="typeFilter" class="form-select" @change="loadProperties">
        <option value="">সব ধরনের সম্পত্তি (All Types)</option>
        <option value="জমি">জমি</option>
        <option value="ভবন">ভবন</option>
        <option value="যানবাহন">যানবাহন</option>
        <option value="সরঞ্জাম">সরঞ্জাম</option>
      </select>
      <select v-model="statusFilter" class="form-select" @change="loadProperties">
        <option value="">সব অবস্থা</option>
        <option value="active">ব্যবহারযোগ্য / সক্রিয়</option>
        <option value="under_construction">নির্মাণাধীন</option>
        <option value="rented">ভাড়া দেওয়া</option>
        <option value="inactive">নিষ্ক্রিয়</option>
      </select>
      <div class="pagination-info" v-if="properties.length">
        মোট <span class="highlight">{{ properties.length.toLocaleString('bn-BD') }}</span> টি সম্পত্তি
      </div>
    </div>

    <!-- Create / Edit Property Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ editingId ? 'সম্পত্তি সম্পাদনা' : 'নতুন সম্পত্তি যুক্ত করুন' }}</h3>
            <p>সম্পত্তির বিবরণ, ধরন, অবস্থান ও আর্থিক তথ্য লিখুন</p>
          </div>
          <button class="modal-close-btn" @click="showForm = false">×</button>
        </div>

        <form @submit.prevent="saveProperty" class="modal-form">
          <div v-if="error" class="alert alert-error">{{ error }}</div>

          <div class="form-grid">
            <div class="form-group wide">
              <label class="form-label">সম্পত্তির নাম (বাংলা) *</label>
              <input v-model="form.property_name_bn" class="form-input" required placeholder="যেমন: প্রধান ক্যাম্পাস ভবন" />
            </div>
            <div class="form-group">
              <label class="form-label">সম্পত্তির নাম (ইংরেজি)</label>
              <input v-model="form.property_name_en" class="form-input" placeholder="e.g. Main Campus Building" />
            </div>
            <div class="form-group">
              <label class="form-label">সম্পত্তির ধরন *</label>
              <select v-model="form.property_type" class="form-select" required>
                <option value="ভবন">ভবন (Building)</option>
                <option value="জমি">জমি (Land)</option>
                <option value="যানবাহন">যানবাহন (Vehicle)</option>
                <option value="সরঞ্জাম">সরঞ্জাম (Equipment)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">মালিকানার অবস্থা *</label>
              <select v-model="form.ownership_type" class="form-select">
                <option value="owned">নিজস্ব (Owned)</option>
                <option value="rented">ভাড়া (Rented)</option>
                <option value="leased">লিজ (Leased)</option>
                <option value="waqf">ওয়াকফ (Waqf)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">বর্তমান বাজারমূল্য (টাকা)</label>
              <input v-model.number="form.current_market_value" type="number" min="0" class="form-input" placeholder="০" />
            </div>
            <div class="form-group">
              <label class="form-label">জমির পরিমাণ / আয়তন (বর্গফুট)</label>
              <input v-model.number="form.land_area_sqft" type="number" min="0" class="form-input" placeholder="যেমন: ১২০০" />
            </div>
            <div class="form-group">
              <label class="form-label">দলিল / রেজিস্ট্রেশন নম্বর</label>
              <input v-model="form.registration_number" class="form-input" placeholder="রেজিস্ট্রেশন নম্বর" />
            </div>
            <div class="form-group wide">
              <label class="form-label">অবস্থান / ঠিকানা (বাংলা)</label>
              <input v-model="form.location_address_bn" class="form-input" placeholder="পূর্ণ ঠিকানা..." />
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'সম্পত্তি যোগ করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>সম্পত্তি তালিকা লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!properties.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="building" /></div>
      <h3>কোনো সম্পত্তি পাওয়া যায়নি</h3>
      <p>নতুন সম্পত্তি যুক্ত করে রেকর্ড রাখা শুরু করুন</p>
      <button class="btn btn-primary" @click="openCreate"><icon name="plus" /> প্রথম সম্পত্তি যুক্ত করুন</button>
    </div>

    <!-- Properties Grid -->
    <div v-else class="properties-grid">
      <div v-for="prop in properties" :key="prop.id" class="prop-card card">
        <div class="prop-header">
          <div class="prop-icon-box">
            <icon :name="typeIcon(prop.property_type)" />
          </div>
          <div class="prop-title-block">
            <h3>{{ prop.property_name_bn || prop.property_name_en }}</h3>
            <span class="type-tag">{{ prop.property_type || 'স্থাবর' }}</span>
          </div>
          <span class="status-pill" :class="statusClass(prop.status)">
            <span class="status-dot" />
            {{ statusLabel(prop.status) }}
          </span>
        </div>

        <div class="prop-details">
          <div class="prop-meta-row" v-if="prop.location_address_bn">
            <icon name="building" class="meta-icon" />
            <span>{{ prop.location_address_bn }}</span>
          </div>
          <div class="prop-meta-row" v-if="prop.land_area_sqft">
            <icon name="calendar" class="meta-icon" />
            <span>আয়তন: <strong>{{ prop.land_area_sqft.toLocaleString('bn-BD') }} বর্গফুট</strong></span>
          </div>
          <div class="prop-meta-row" v-if="prop.current_market_value">
            <icon name="money" class="meta-icon" />
            <span>বাজারমূল্য: <strong>{{ formatCurrency(prop.current_market_value) }} ৳</strong></span>
          </div>
        </div>

        <div class="prop-actions">
          <NuxtLink :to="`/properties/${prop.id}`" class="view-link">
            বিস্তারিত ও ডকুমেন্টস <icon name="arrow-right" />
          </NuxtLink>
          <div class="action-buttons">
            <button class="action-btn" @click="openEdit(prop)" title="সম্পাদনা">
              <icon name="pencil" />
            </button>
            <button class="action-btn text-danger" @click="deleteProperty(prop.id)" title="মুছুন">
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
const properties = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')

const search = ref('')
const typeFilter = ref('')
const statusFilter = ref('')

const form = reactive({
  property_name_bn: '',
  property_name_en: '',
  property_type: 'ভবন',
  ownership_type: 'owned',
  location_address_bn: '',
  land_area_sqft: 0,
  current_market_value: 0,
  registration_number: '',
  status: 'active',
})

const totalValuation = computed(() => properties.value.reduce((sum, p) => sum + (Number(p.current_market_value) || 0), 0))
const ownedCount = computed(() => properties.value.filter(p => p.ownership_type === 'owned' || !p.ownership_type).length)
const rentedCount = computed(() => properties.value.filter(p => p.ownership_type === 'rented' || p.ownership_type === 'leased').length)

async function loadProperties() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (typeFilter.value) q.set('type', typeFilter.value)
    if (statusFilter.value) q.set('status', statusFilter.value)

    const r = await api.get(`/properties?${q.toString()}`)
    properties.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingId.value = null
  error.value = ''
  form.property_name_bn = ''
  form.property_name_en = ''
  form.property_type = 'ভবন'
  form.ownership_type = 'owned'
  form.location_address_bn = ''
  form.land_area_sqft = 0
  form.current_market_value = 0
  form.registration_number = ''
  form.status = 'active'
  showForm.value = true
}

function openEdit(prop: any) {
  editingId.value = prop.id
  error.value = ''
  form.property_name_bn = prop.property_name_bn || ''
  form.property_name_en = prop.property_name_en || ''
  form.property_type = prop.property_type || 'ভবন'
  form.ownership_type = prop.ownership_type || 'owned'
  form.location_address_bn = prop.location_address_bn || ''
  form.land_area_sqft = Number(prop.land_area_sqft) || 0
  form.current_market_value = Number(prop.current_market_value) || 0
  form.registration_number = prop.registration_number || ''
  form.status = prop.status || 'active'
  showForm.value = true
}

async function saveProperty() {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/properties/${editingId.value}`, form)
    } else {
      await api.post('/properties', form)
    }
    showForm.value = false
    await loadProperties()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'সম্পত্তি সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function deleteProperty(id: number) {
  if (!confirm('আপনি কি এই সম্পত্তি মুছে ফেলতে চান?')) return
  try {
    await api.delete(`/properties/${id}`)
    await loadProperties()
  } catch (e) {
    console.error(e)
  }
}

function formatCurrency(val: number) {
  if (!val) return '০'
  return val.toLocaleString('bn-BD')
}

function typeIcon(t: string) {
  if (t === 'যানবাহন') return 'bus'
  if (t === 'জমি') return 'calendar'
  return 'building'
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    active: 'ব্যবহারযোগ্য',
    under_construction: 'নির্মাণাধীন',
    rented: 'ভাড়া দেওয়া',
    inactive: 'নিষ্ক্রিয়',
  }
  return map[s] || s || 'সক্রিয়'
}

function statusClass(s: string) {
  if (s === 'under_construction') return 'badge-pending'
  if (s === 'inactive') return 'badge-rejected'
  return 'badge-approved'
}

onMounted(loadProperties)
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

/* Properties Grid */
.properties-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
  gap: 1.25rem;
}

.prop-card {
  padding: 1.35rem;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 14px;
}

.prop-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.prop-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 1rem;
  padding-bottom: 0.85rem;
  border-bottom: 1px solid var(--color-border-light);
}

.prop-icon-box {
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

.prop-title-block {
  flex: 1;
  min-width: 0;
}

.prop-title-block h3 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 0.2rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.type-tag {
  display: inline-block;
  padding: 0.15rem 0.55rem;
  background: rgba(20, 80, 50, 0.07);
  color: var(--color-primary);
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.prop-details {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin-bottom: 1.25rem;
  font-size: 0.84rem;
  color: var(--color-text-light);
}

.prop-meta-row {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.meta-icon {
  font-size: 0.95rem;
  color: var(--color-text-muted);
}

.prop-actions {
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