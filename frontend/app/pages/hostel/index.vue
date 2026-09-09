<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">আবাসিক ব্যবস্থাপনা</span>
        <h1>হোস্টেল ও কক্ষ ব্যবস্থাপনা</h1>
        <p class="page-subtitle">কক্ষ, ধারণক্ষমতা, আবাসিক অবস্থা ও ওয়ার্ডেন পরিচালনা করুন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="openCreate">
          <icon name="plus" /> নতুন কক্ষ যুক্ত করুন
        </button>
        <button class="btn btn-outline" @click="loadRooms">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="stats-grid" v-if="rooms.length">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ rooms.length.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট হোস্টেল কক্ষ</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ totalCapacity.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট সিট ধারণক্ষমতা</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ availableCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">খালি / বরাদ্দযোগ্য কক্ষ</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(totalMonthlyRent) }} ৳</span>
          <span class="stat-label">মোট মাসিক সিটভাড়া</span>
        </div>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="search" placeholder="কক্ষ নম্বর বা ব্লক খুঁজুন..." @keyup.enter="loadRooms" />
        <button v-if="search" class="clear-search-btn" @click="search = ''; loadRooms()">×</button>
      </div>
      <select v-model="statusFilter" class="form-select" @change="loadRooms">
        <option value="">সব অবস্থা (All Rooms)</option>
        <option value="available">খালি কক্ষ</option>
        <option value="occupied">পূর্ণ / আংশিক পূর্ণ</option>
        <option value="maintenance">মেরামত চলছে</option>
      </select>
      <div class="pagination-info" v-if="rooms.length">
        মোট <span class="highlight">{{ rooms.length.toLocaleString('bn-BD') }}</span> টি কক্ষ
      </div>
    </div>

    <!-- Create / Edit Room Modal -->
    <div v-if="showForm" class="modal-overlay" @click.self="showForm = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>{{ editingId ? 'কক্ষ সম্পাদনা' : 'নতুন কক্ষ যুক্ত করুন' }}</h3>
            <p>কক্ষ নম্বর, ব্লক, ধারণক্ষমতা ও মাসিক ভাড়া নির্ধারণ করুন</p>
          </div>
          <button class="modal-close-btn" @click="showForm = false">×</button>
        </div>

        <form @submit.prevent="saveRoom" class="modal-form">
          <div v-if="error" class="alert alert-error">{{ error }}</div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">কক্ষ নম্বর *</label>
              <input v-model="form.room_number" class="form-input" required placeholder="যেমন: ১০১ / A-202" />
            </div>
            <div class="form-group">
              <label class="form-label">ভবন / ব্লক</label>
              <input v-model="form.block_building" class="form-input" placeholder="যেমন: ব্লক-এ / মূল হোস্টেল" />
            </div>
            <div class="form-group">
              <label class="form-label">ধারণক্ষমতা (সিট সংখ্যা) *</label>
              <input v-model.number="form.capacity" type="number" min="1" class="form-input" required placeholder="৪" />
            </div>
            <div class="form-group">
              <label class="form-label">মাসিক সিট ভাড়া (টাকা)</label>
              <input v-model.number="form.monthly_rent" type="number" min="0" class="form-input" placeholder="১০০০" />
            </div>
            <div class="form-group">
              <label class="form-label">ওয়ার্ডেন / দায়িত্বশীল শিক্ষক</label>
              <select v-model="form.warden_id" class="form-select">
                <option value="">ওয়ার্ডেন নির্বাচন করুন</option>
                <option v-for="s in staffList" :key="s.id" :value="s.id">
                  {{ s.name_bn || s.name_en }} ({{ s.designation || 'শিক্ষক' }})
                </option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">বর্তমান অবস্থা</label>
              <select v-model="form.status" class="form-select">
                <option value="available">খালি (Available)</option>
                <option value="occupied">পূর্ণ (Occupied)</option>
                <option value="maintenance">মেরামতধীন (Maintenance)</option>
              </select>
            </div>
            <div class="form-group wide">
              <label class="form-label">কক্ষের সুযোগ-সুবিধা</label>
              <input v-model="form.amenities" class="form-input" placeholder="যেমন: ফ্যান, খাট, পড়ার টেবিল, সংযুক্ত বাথরুম" />
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" @click="showForm = false">বাতিল</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'সংরক্ষণ হচ্ছে...' : (editingId ? 'আপডেট করুন' : 'কক্ষ যোগ করুন') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>কক্ষ তালিকা লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!rooms.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="building" /></div>
      <h3>কোনো হোস্টেল কক্ষ পাওয়া যায়নি</h3>
      <p>নতুন কক্ষ যোগ করে হোস্টেল ব্যবস্থাপনা শুরু করুন</p>
      <button class="btn btn-primary" @click="openCreate"><icon name="plus" /> প্রথম কক্ষ যুক্ত করুন</button>
    </div>

    <!-- Rooms Grid -->
    <div v-else class="rooms-grid">
      <div v-for="room in rooms" :key="room.id" class="room-card card">
        <div class="room-card-header">
          <div class="room-icon-box">
            <icon name="building" />
          </div>
          <div class="room-title-block">
            <h3>কক্ষ {{ room.room_number }}</h3>
            <span class="block-tag">{{ room.block_building || 'প্রধান ব্লক' }}</span>
          </div>
          <span class="status-pill" :class="statusClass(room.status)">
            <span class="status-dot" />
            {{ statusLabel(room.status) }}
          </span>
        </div>

        <div class="room-details">
          <div class="room-meta-row">
            <icon name="users" class="meta-icon" />
            <span>সিট: <strong>{{ (room.current_occupancy || 0).toLocaleString('bn-BD') }} / {{ (room.capacity || 0).toLocaleString('bn-BD') }}</strong></span>
          </div>
          <div class="room-meta-row" v-if="room.monthly_rent">
            <icon name="money" class="meta-icon" />
            <span>ভাড়া: <strong>{{ formatCurrency(room.monthly_rent) }} ৳ /মাস</strong></span>
          </div>
          <div class="room-meta-row" v-if="room.warden_name">
            <icon name="user-circle" class="meta-icon" />
            <span>ওয়ার্ডেন: {{ room.warden_name }}</span>
          </div>
          <div class="room-meta-row" v-if="room.amenities">
            <icon name="check-circle" class="meta-icon" />
            <span class="amenities-text">{{ room.amenities }}</span>
          </div>
        </div>

        <!-- Occupancy Bar -->
        <div class="occupancy-bar-wrap">
          <div class="occupancy-bar-fill" :style="{ width: getOccupancyPercent(room) + '%' }" />
        </div>

        <div class="room-actions">
          <NuxtLink :to="`/hostel/rooms/${room.id}`" class="view-link">
            শিক্ষার্থী ও বিবরণ <icon name="arrow-right" />
          </NuxtLink>
          <div class="action-buttons">
            <button class="action-btn" @click="openEdit(room)" title="সম্পাদনা">
              <icon name="pencil" />
            </button>
            <button class="action-btn text-danger" @click="deleteRoom(room.id)" title="মুছুন">
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
const rooms = ref<any[]>([])
const staffList = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const editingId = ref<number | null>(null)
const error = ref('')

const search = ref('')
const statusFilter = ref('')

const form = reactive({
  room_number: '',
  block_building: '',
  capacity: 4,
  monthly_rent: 0,
  warden_id: '',
  status: 'available',
  amenities: '',
})

const totalCapacity = computed(() => rooms.value.reduce((sum, r) => sum + (Number(r.capacity) || 0), 0))
const availableCount = computed(() => rooms.value.filter(r => r.status === 'available' || (r.current_occupancy || 0) < (r.capacity || 1)).length)
const totalMonthlyRent = computed(() => rooms.value.reduce((sum, r) => sum + (Number(r.monthly_rent) || 0), 0))

async function loadRooms() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (search.value) q.set('search', search.value)
    if (statusFilter.value) q.set('status', statusFilter.value)

    const [rRes, sRes] = await Promise.all([
      api.get(`/hostel/rooms?${q.toString()}`),
      api.get('/hr/staff?per_page=100').catch(() => ({ data: { data: [] } })),
    ])
    rooms.value = rRes.data?.data?.data || rRes.data?.data || []
    staffList.value = sRes.data?.data?.data || sRes.data?.data || []
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingId.value = null
  error.value = ''
  form.room_number = ''
  form.block_building = ''
  form.capacity = 4
  form.monthly_rent = 0
  form.warden_id = ''
  form.status = 'available'
  form.amenities = ''
  showForm.value = true
}

function openEdit(room: any) {
  editingId.value = room.id
  error.value = ''
  form.room_number = room.room_number || ''
  form.block_building = room.block_building || ''
  form.capacity = Number(room.capacity) || 4
  form.monthly_rent = Number(room.monthly_rent) || 0
  form.warden_id = room.warden_id || ''
  form.status = room.status || 'available'
  form.amenities = room.amenities || ''
  showForm.value = true
}

async function saveRoom() {
  saving.value = true
  error.value = ''
  try {
    if (editingId.value) {
      await api.put(`/hostel/rooms/${editingId.value}`, form)
    } else {
      await api.post('/hostel/rooms', form)
    }
    showForm.value = false
    await loadRooms()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'কক্ষ সংরক্ষণ করা যায়নি'
  } finally {
    saving.value = false
  }
}

async function deleteRoom(id: number) {
  if (!confirm('আপনি কি এই কক্ষ মুছে ফেলতে চান?')) return
  try {
    await api.delete(`/hostel/rooms/${id}`)
    await loadRooms()
  } catch (e) {
    console.error(e)
  }
}

function getOccupancyPercent(room: any) {
  const cap = room.capacity || 1
  const occ = room.current_occupancy || 0
  return Math.min(100, Math.round((occ / cap) * 100))
}

function formatCurrency(val: number) {
  if (!val) return '০'
  return val.toLocaleString('bn-BD')
}

function statusLabel(s: string) {
  const map: Record<string, string> = {
    available: 'খালি',
    occupied: 'পূর্ণ',
    maintenance: 'মেরামত চলছে',
  }
  return map[s] || s || 'খালি'
}

function statusClass(s: string) {
  if (s === 'occupied') return 'badge-rejected'
  if (s === 'maintenance') return 'badge-pending'
  return 'badge-approved'
}

onMounted(loadRooms)
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

/* Rooms Grid */
.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

.room-card {
  padding: 1.35rem;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-radius: 14px;
}

.room-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.room-card-header {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  margin-bottom: 1rem;
  padding-bottom: 0.85rem;
  border-bottom: 1px solid var(--color-border-light);
}

.room-icon-box {
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

.room-title-block {
  flex: 1;
  min-width: 0;
}

.room-title-block h3 {
  font-size: 1.05rem;
  font-weight: 700;
  margin: 0 0 0.2rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.block-tag {
  display: inline-block;
  font-size: 0.75rem;
  color: var(--color-text-light);
  font-weight: 500;
}

.room-details {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin-bottom: 1rem;
  font-size: 0.84rem;
  color: var(--color-text-light);
}

.room-meta-row {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.meta-icon {
  font-size: 0.95rem;
  color: var(--color-text-muted);
}

.amenities-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.occupancy-bar-wrap {
  width: 100%;
  height: 6px;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 1.1rem;
}

.occupancy-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #10b981 0%, #145032 100%);
  border-radius: 3px;
  transition: width 0.3s ease;
}

.room-actions {
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