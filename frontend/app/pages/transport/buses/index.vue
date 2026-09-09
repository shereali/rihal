<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">যাতায়াত ও পরিবহন</span>
        <h1>যানবাহন ও বাস ব্যবস্থাপনা</h1>
        <p class="page-subtitle">মাদ্রাসার যানবাহন, চালক, ফিটনেস ও রুট বরাদ্দ পরিচালনা করুন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate">
          <icon name="plus" /> নতুন যানবাহন
        </button>
        <button class="btn btn-outline" @click="load">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" />
        <input v-model="search" placeholder="বাস নম্বর বা রেজিস্ট্রেশন নম্বর খুঁজুন..." @keyup.enter="load" />
      </div>
      <select v-model="routeFilter" class="form-control compact" @change="load">
        <option value="">সব রুট</option>
        <option v-for="r in routeOptions" :key="r.id" :value="r.id">{{ r.route_name_bn || r.route_name_en }}</option>
      </select>
      <select v-model="statusFilter" class="form-control compact" @change="load">
        <option value="">সব অবস্থা</option>
        <option value="active">সক্রিয়</option>
        <option value="inactive">নিষ্ক্রিয়</option>
      </select>
    </div>

    <!-- Create / Edit Panel -->
    <form v-if="showForm" class="create-panel card" @submit.prevent="saveBus">
      <div class="form-heading">
        <div>
          <h2>{{ editingId ? 'যানবাহন সম্পাদনা' : 'নতুন যানবাহন যোগ করুন' }}</h2>
          <p>বাসের তথ্য, রুট, চালক ও ডকুমেন্ট পূরণ করুন</p>
        </div>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>

      <div v-if="error" class="alert alert-error">{{ error }}</div>

      <div class="form-grid">
        <div class="form-group wide">
          <label>বাস নম্বর / নাম *</label>
          <input v-model="form.bus_number" class="form-control" required placeholder="যেমন: মাদ্রাসা বাস-০১" />
        </div>
        <div class="form-group">
          <label>রুট নির্বাচন</label>
          <select v-model="form.route_id" class="form-control">
            <option value="">রুট নির্বাচন করুন</option>
            <option v-for="r in routeOptions" :key="r.id" :value="r.id">{{ r.route_name_bn || r.route_name_en }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>চালক নির্বাচন</label>
          <select v-model="form.driver_id" class="form-control">
            <option value="">চালক নির্বাচন করুন</option>
            <option v-for="d in driverOptions" :key="d.id" :value="d.id">{{ d.name_bn || d.name_en }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>ধারণক্ষমতা (আসন সংখ্যা)</label>
          <input v-model.number="form.capacity" type="number" class="form-control" min="1" placeholder="৪০" />
        </div>
        <div class="form-group">
          <label>যানবাহনের ধরণ</label>
          <input v-model="form.vehicle_type" class="form-control" placeholder="যেমন: মিনিবাস / মাইক্রোবাস" />
        </div>
        <div class="form-group">
          <label>রেজিস্ট্রেশন নম্বর</label>
          <input v-model="form.registration_number" class="form-control" placeholder="যেমন: ঢাকা মেট্রো-চ-১২৩৪৫৬" />
        </div>
        <div class="form-group">
          <label>বীমা শেষ তারিখ</label>
          <input v-model="form.insurance_expiry" type="date" class="form-control" />
        </div>
        <div class="form-group">
          <label>ফিটনেস শেষ তারিখ</label>
          <input v-model="form.fitness_expiry" type="date" class="form-control" />
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="saving">
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'যানবাহন যোগ করুন') }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
      </div>
    </form>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>বাসের তালিকা লোড হচ্ছে...</p></div>

    <div v-else-if="!buses.length" class="empty-card">
      <div class="empty-icon"><icon name="bus" /></div>
      <h3>এখনও কোনো যানবাহন নেই</h3>
      <p>নতুন বাস যোগ করে যাতায়াত ব্যবস্থাপনা শুরু করুন</p>
      <button class="btn btn-primary" @click="openCreate">প্রথম বাস যোগ করুন</button>
    </div>

    <div v-else class="buses-table card">
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>বাস নম্বর</th>
              <th>রুট</th>
              <th>চালক</th>
              <th>ধারণক্ষমতা</th>
              <th>রেজিস্ট্রেশন নং</th>
              <th>ফিটনেস মেয়াদ</th>
              <th>অবস্থা</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in buses" :key="b.id">
              <td><strong>{{ b.bus_number }}</strong></td>
              <td>{{ b.route?.route_name_bn || b.route?.route_name_en || '—' }}</td>
              <td>{{ b.driver?.name_bn || b.driver?.name_en || '—' }}</td>
              <td>{{ b.capacity ? b.capacity + ' আসন' : '—' }}</td>
              <td>{{ b.registration_number || '—' }}</td>
              <td>{{ b.fitness_expiry || '—' }}</td>
              <td>
                <span class="status-badge" :class="b.is_active ? 'active' : 'inactive'">
                  {{ b.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                </span>
              </td>
              <td class="text-right">
                <div class="action-buttons">
                  <button class="btn-icon" @click="openEdit(b)" title="সম্পাদনা">
                    <icon name="pencil" />
                  </button>
                  <button class="btn-icon text-danger" @click="deleteBus(b.id)" title="মুছুন">
                    <icon name="delete" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const buses = ref<any[]>([])
const routeOptions = ref<any[]>([])
const driverOptions = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')
const search = ref('')
const routeFilter = ref('')
const statusFilter = ref('')

const form = reactive({
  bus_number: '',
  route_id: '' as string | number,
  driver_id: '' as string | number,
  capacity: 40,
  vehicle_type: '',
  registration_number: '',
  insurance_expiry: '',
  fitness_expiry: '',
  is_active: true,
})

async function load() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (routeFilter.value) q.set('route_id', routeFilter.value)
    if (statusFilter.value) q.set('status', statusFilter.value)
    const r = await api.get(`/transport/buses?${q.toString()}`)
    buses.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function loadMeta() {
  try {
    const [rRes, dRes] = await Promise.all([
      api.get('/transport/routes?per_page=100').catch(() => ({ data: { data: [] } })),
      api.get('/hr/staff?per_page=100').catch(() => ({ data: { data: [] } })),
    ])
    routeOptions.value = rRes.data?.data?.data || rRes.data?.data || []
    driverOptions.value = dRes.data?.data?.data || dRes.data?.data || []
  } catch (e) {
    console.error(e)
  }
}

function openCreate() {
  editingId.value = null
  error.value = ''
  form.bus_number = ''
  form.route_id = ''
  form.driver_id = ''
  form.capacity = 40
  form.vehicle_type = ''
  form.registration_number = ''
  form.insurance_expiry = ''
  form.fitness_expiry = ''
  form.is_active = true
  showForm.value = true
}

function openEdit(b: any) {
  editingId.value = b.id
  error.value = ''
  form.bus_number = b.bus_number || ''
  form.route_id = b.route_id || ''
  form.driver_id = b.driver_id || ''
  form.capacity = Number(b.capacity) || 40
  form.vehicle_type = b.vehicle_type || ''
  form.registration_number = b.registration_number || ''
  form.insurance_expiry = b.insurance_expiry || ''
  form.fitness_expiry = b.fitness_expiry || ''
  form.is_active = !!b.is_active
  showForm.value = true
}

async function saveBus() {
  saving.value = true
  error.value = ''
  try {
    const payload = {
      ...form,
      route_id: form.route_id || undefined,
      driver_id: form.driver_id || undefined,
    }
    if (editingId.value) {
      await api.put(`/transport/buses/${editingId.value}`, payload)
    } else {
      await api.post('/transport/buses', payload)
    }
    showForm.value = false
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'বাস সংরক্ষণে ত্রুটি দেখা দিয়েছে।'
  } finally {
    saving.value = false
  }
}

async function deleteBus(id: number) {
  if (!confirm('আপনি কি নিশ্চিত যে এই যানবাহন মুছে ফেলতে চান?')) return
  try {
    await api.delete(`/transport/buses/${id}`)
    await load()
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  load()
  loadMeta()
})
</script>

<style scoped>
.module-page {
  max-width: 1300px;
  margin: 0 auto;
  padding: 1.5rem;
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

.page-subtitle {
  color: var(--color-text-light);
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.toolbar {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  padding: 0.85rem 1.25rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 0.45rem 0.75rem;
  flex: 1;
  min-width: 200px;
}

.search-box input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 0.9rem;
  color: var(--color-text);
}

.search-box input:focus {
  outline: none;
}

.form-control.compact {
  padding: 0.45rem 0.75rem;
  font-size: 0.85rem;
  min-width: 150px;
}

.create-panel {
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  border: 1px solid var(--color-primary-light);
}

.form-heading {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--color-border-light);
}

.form-heading h2 {
  font-size: 1.15rem;
  margin: 0;
}

.form-heading p {
  font-size: 0.8rem;
  color: var(--color-text-light);
  margin: 0.2rem 0 0;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--color-text-light);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
}

.form-group.wide {
  grid-column: 1 / -1;
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
  gap: 0.75rem;
  margin-top: 1.25rem;
  justify-content: flex-end;
}

.table-responsive {
  overflow-x: auto;
}

.table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}

.table th, .table td {
  padding: 0.85rem 1rem;
  text-align: left;
  border-bottom: 1px solid var(--color-border-light);
}

.table th {
  background: rgba(0, 0, 0, 0.02);
  font-weight: 600;
  color: var(--color-text-light);
  font-size: 0.8rem;
}

.table-hover tr:hover {
  background: rgba(0, 0, 0, 0.015);
}

.status-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
}

.status-badge.active {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.status-badge.inactive {
  background: rgba(239, 68, 68, 0.15);
  color: #ef4444;
}

.action-buttons {
  display: flex;
  gap: 0.35rem;
  justify-content: flex-end;
}

.btn-icon {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.3rem;
  border-radius: 4px;
  color: var(--color-text-light);
}

.btn-icon:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--color-text);
}

.btn-icon.text-danger:hover {
  color: var(--color-error);
  background: rgba(220, 38, 38, 0.1);
}

.btn {
  padding: 0.55rem 1.1rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
}

.btn-primary { background: var(--color-primary); color: #fff; }
.btn-outline { background: transparent; border: 1px solid var(--color-border); color: var(--color-text); }
.btn-ghost { background: transparent; color: var(--color-text); }

.card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md);
}

.empty-card, .loading-state {
  padding: 3rem;
  text-align: center;
  color: var(--color-text-light);
  background: var(--color-bg-card);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border-light);
}

.empty-icon {
  font-size: 2.5rem;
  color: var(--color-primary);
  margin-bottom: 0.5rem;
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 0.75rem;
}

@keyframes spin { to { transform: rotate(360deg); } }

.alert {
  padding: 0.65rem 0.9rem;
  border-radius: var(--radius-sm);
  margin-bottom: 1rem;
  font-size: 0.85rem;
}

.alert-error {
  background: #fce4e4;
  color: var(--color-error);
}
</style>