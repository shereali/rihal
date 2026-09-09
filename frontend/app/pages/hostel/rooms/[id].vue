<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NuxtLink to="/hostel">হোস্টেল ও কক্ষ</NuxtLink>
      <icon name="chevron-down" class="breadcrumb-sep rotate-270" />
      <span>কক্ষ {{ room?.room_number }}</span>
    </div>

    <div class="page-header-row">
      <div>
        <span class="eyebrow">আবাসিক ব্যবস্থাপনা</span>
        <h1>কক্ষের বিবরণী — {{ room?.room_number }}</h1>
        <p>{{ room?.block ? room.block + ' ব্লক | ' : '' }} ধারণক্ষমতা, সুযোগ-সুবিধা ও আবাসিক শিক্ষার্থী তালিকা</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openEdit" v-if="room">
          <icon name="pencil" /> সম্পাদনা
        </button>
        <NuxtLink to="/hostel" class="btn btn-outline">
          <icon name="arrow-left" /> ফিরে যান
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>কক্ষ লোড হচ্ছে...</p></div>
    <div v-else-if="!room" class="empty-card">
      <div class="empty-icon"><icon name="building" /></div>
      <h3>কক্ষ পাওয়া যায়নি</h3>
      <NuxtLink to="/hostel" class="btn btn-primary">কক্ষ তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="detail-layout">
      <!-- Main Room Card -->
      <div class="card detail-card">
        <div class="room-header">
          <div class="room-identifier">
            <h2>কক্ষ নম্বর: {{ room.room_number }}</h2>
            <span v-if="room.block" class="room-block-tag">{{ room.block }}</span>
            <span v-if="room.floor" class="room-floor-tag">তলা: {{ room.floor }}</span>
          </div>
          <span class="status-badge" :class="room.is_available ? 'available' : 'occupied'">
            {{ room.is_available ? 'খালি' : (room.current_occupancy >= room.capacity ? 'পূর্ণ' : 'আংশিক') }}
          </span>
        </div>

        <div class="occupancy-section">
          <div class="occupancy-bar">
            <div class="occupancy-fill" :style="{ width: ((room.current_occupancy || 0) / (room.capacity || 1) * 100) + '%' }" />
          </div>
          <span class="occupancy-text">
            <strong>{{ room.current_occupancy || 0 }} জন</strong> বসবাস করছে (মোট ধারণক্ষমতা {{ room.capacity || 1 }} জন)
          </span>
        </div>

        <div class="info-grid">
          <div class="info-block">
            <label>ধারণক্ষমতা</label>
            <p>{{ room.capacity || 'নেই' }} জন</p>
          </div>
          <div class="info-block">
            <label>মাসিক ভাড়া</label>
            <p class="highlight-val">{{ room.monthly_rent ? formatCurrency(room.monthly_rent) + ' টাকা/মাস' : 'নির্ধারিত নয়' }}</p>
          </div>
          <div class="info-block">
            <label>বর্তমান অবস্থা</label>
            <p>{{ room.is_available ? 'বরাদ্দের জন্য উন্মুক্ত' : 'বন্ধ / পূর্ণ' }}</p>
          </div>
          <div class="info-block wide">
            <label>সুযোগ-সুবিধা</label>
            <div v-if="room.amenities && (Array.isArray(room.amenities) ? room.amenities.length : room.amenities)" class="amenities-tags">
              <span v-for="a in (Array.isArray(room.amenities) ? room.amenities : room.amenities.split(','))" :key="a" class="amenity-tag">
                {{ a.trim() }}
              </span>
            </div>
            <p v-else class="text-muted">কোনো বিশেষ সুবিধা উল্লেখ নেই</p>
          </div>
        </div>
      </div>

      <!-- Warden Card -->
      <div class="card">
        <div class="card-header"><h3>দায়িত্বপ্রাপ্ত ওয়ার্ডেন</h3></div>
        <div class="card-body">
          <div v-if="room.warden" class="warden-profile">
            <div class="warden-avatar"><icon name="user-circle" /></div>
            <div class="warden-info">
              <h4>{{ room.warden.name_bn || room.warden.name_en }}</h4>
              <p class="text-muted" v-if="room.warden.phone">ফোন: {{ room.warden.phone }}</p>
              <p class="text-muted" v-if="room.warden.email">ইমেইল: {{ room.warden.email }}</p>
            </div>
          </div>
          <div v-else class="empty-slate">
            <icon name="user-circle" class="empty-icon-slate" />
            <p class="text-muted">ওয়ার্ডেন নির্ধারিত নেই</p>
          </div>
        </div>
      </div>

      <!-- Occupant Students Card -->
      <div class="card">
        <div class="card-header">
          <h3>কক্ষের শিক্ষার্থী তালিকা</h3>
        </div>
        <div class="card-body">
          <div class="empty-slate">
            <icon name="users" class="empty-icon-slate" />
            <p class="text-muted">এখনও কোনো শিক্ষার্থী বরাদ্দ নেই</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal card">
        <div class="modal-header">
          <h3>কক্ষ তথ্য সম্পাদনা</h3>
          <button class="close-btn" @click="showEditModal = false">×</button>
        </div>
        <form @submit.prevent="saveEdit" class="modal-body">
          <div class="form-group">
            <label>কক্ষ নম্বর *</label>
            <input v-model="editForm.room_number" class="form-control" required />
          </div>
          <div class="form-group">
            <label>বিল্ডিং / ব্লক</label>
            <input v-model="editForm.block" class="form-control" />
          </div>
          <div class="form-group">
            <label>তলা</label>
            <input v-model="editForm.floor" class="form-control" />
          </div>
          <div class="form-group">
            <label>ধারণক্ষমতা (জন) *</label>
            <input v-model.number="editForm.capacity" type="number" class="form-control" min="1" required />
          </div>
          <div class="form-group">
            <label>মাসিক ভাড়া (টাকা)</label>
            <input v-model.number="editForm.monthly_rent" type="number" class="form-control" min="0" />
          </div>
          <div class="form-group">
            <label>ওয়ার্ডেন</label>
            <select v-model="editForm.warden_id" class="form-control">
              <option value="">ওয়ার্ডেন নির্বাচন করুন</option>
              <option v-for="w in wardenOptions" :key="w.id" :value="w.id">{{ w.name_bn || w.name_en }}</option>
            </select>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving">আপডেট করুন</button>
            <button type="button" class="btn btn-ghost" @click="showEditModal = false">বাতিল</button>
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
const roomId = route.params.id

const room = ref<any>(null)
const wardenOptions = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showEditModal = ref(false)

const editForm = reactive({
  room_number: '',
  block: '',
  floor: '',
  capacity: 4,
  monthly_rent: 0,
  warden_id: '' as string | number,
  is_available: true,
})

async function loadRoom() {
  loading.value = true
  try {
    const r = await api.get(`/hostel-rooms/${roomId}`)
    room.value = r.data?.data
  } catch (e) {
    console.error('Failed to load room:', e)
  } finally {
    loading.value = false
  }
}

async function loadWardens() {
  try {
    const r = await api.get('/hr/staff?per_page=100')
    wardenOptions.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error(e)
  }
}

function openEdit() {
  if (!room.value) return
  editForm.room_number = room.value.room_number || ''
  editForm.block = room.value.block || ''
  editForm.floor = room.value.floor || ''
  editForm.capacity = Number(room.value.capacity) || 4
  editForm.monthly_rent = Number(room.value.monthly_rent) || 0
  editForm.warden_id = room.value.warden_id || ''
  editForm.is_available = !!room.value.is_available
  showEditModal.value = true
}

async function saveEdit() {
  saving.value = true
  try {
    await api.put(`/hostel-rooms/${roomId}`, editForm)
    showEditModal.value = false
    await loadRoom()
  } catch (e) {
    console.error(e)
  } finally {
    saving.value = false
  }
}

function formatCurrency(val: number) {
  if (!val) return '০'
  return val.toLocaleString('bn-BD')
}

onMounted(() => {
  loadRoom()
  loadWardens()
})
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

.room-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
}

.room-identifier {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.room-identifier h2 {
  font-size: 1.35rem;
  margin: 0;
}

.room-block-tag, .room-floor-tag {
  font-size: 0.8rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  color: var(--color-text-light);
}

.status-badge {
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-badge.available {
  background: rgba(16, 185, 129, 0.15);
  color: #10b981;
}

.status-badge.occupied {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}

.occupancy-section {
  padding: 1.25rem;
  border-bottom: 1px solid var(--color-border-light);
}

.occupancy-bar {
  height: 8px;
  background: rgba(0, 0, 0, 0.08);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.occupancy-fill {
  height: 100%;
  background: var(--color-primary);
  border-radius: 4px;
}

.occupancy-text {
  font-size: 0.85rem;
  color: var(--color-text-light);
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

.amenities-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.35rem;
}

.amenity-tag {
  background: rgba(20, 80, 50, 0.08);
  color: var(--color-primary);
  font-size: 0.8rem;
  padding: 0.25rem 0.6rem;
  border-radius: 4px;
  font-weight: 500;
}

.card-header {
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

.warden-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.warden-avatar {
  font-size: 2.2rem;
  color: var(--color-primary);
}

.warden-info h4 {
  margin: 0 0 0.2rem;
  font-size: 1rem;
}

.warden-info p {
  margin: 0;
  font-size: 0.82rem;
}

.empty-slate {
  text-align: center;
  padding: 1.5rem;
  color: var(--color-text-light);
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