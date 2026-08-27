<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">ডিজিটাল হাজিরা</span>
        <h1>হাজিরা ডিভাইস</h1>
        <p>বায়োমেট্রিক ও আইডি কার্ড ডিভাইস পরিচালনা করুন</p>
      </div>
      <div class="page-actions">
        <button class="btn btn-primary" @click="showCreate = true">
          <Icon name="plus" /> নতুন ডিভাইস
        </button>
      </div>
    </div>

    <div class="card">
      <div class="card-inner">
        <div slot="header" class="card-header-inner">
          <div class="search-bar">
            <Icon name="search" />
            <input
              v-model="search"
              type="text"
              placeholder="ডিভাইস খুঁজুন..."
              @input="debounceSearch"
              class="search-input"
            />
          </div>
          <div class="filter-row">
            <select v-model="statusFilter" class="form-select sm">
              <option value="">সব অবস্থা</option>
              <option value="active">সক্রিয়</option>
              <option value="inactive">নিষ্ক্রিয়</option>
              <option value="syncing">সিঙ্ক করছে</option>
              <option value="error">ত্রুটি</option>
            </select>
          </div>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>ডিভাইস লোড হচ্ছে...</p>
        </div>

        <table v-else-if="devices.data?.length" class="data-table">
          <thead>
            <tr>
              <th>নাম</th>
              <th>সিরিয়াল নম্বর</th>
              <th>ধরন</th>
              <th>আইপি ঠিকানা</th>
              <th>অবস্থা</th>
              <th>সর্বশেষ সিঙ্ক</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in devices.data" :key="d.id">
              <td><strong>{{ d.name }}</strong></td>
              <td><code class="mono">{{ d.serial_number }}</code></td>
              <td>
                <span :class="`badge badge-${deviceTypeClass(d.device_type)}`">
                  {{ formatDeviceType(d.device_type) }}
                </span>
              </td>
              <td>
                <span v-if="d.ip_address" class="mono text-muted">{{ d.ip_address }}</span>
                <span v-else class="text-muted">—</span>
              </td>
              <td>
                <span :class="`status-dot status-${d.status}`" :title="formatStatus(d.status)"></span>
                <span class="ml-1">{{ formatStatus(d.status) }}</span>
              </td>
              <td>
                <span v-if="d.last_synced_at">{{ formatDate(d.last_synced_at) }}</span>
                <span v-else class="text-muted">কখনো নয়</span>
              </td>
              <td class="actions-cell">
                <button class="btn btn-icon btn-sm" @click="editDevice(d)" title="সম্পাদনা">
                  <Icon name="pencil" />
                </button>
                <button class="btn btn-icon btn-sm text-danger" @click="deleteDevice(d)" title="মুছে ফেলুন">
                  <Icon name="trash" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="empty-state">
          <Icon name="table" large />
          <h3>কোনো ডিভাইস নেই</h3>
          <p>এখনও কোনো হাজিরা ডিভাইস যোগ করা হয়নি।</p>
          <button class="btn btn-primary" @click="showCreate = true">প্রথম ডিভাইস যোগ করুন</button>
        </div>

        <div slot="footer" class="card-footer-inner">
          <div class="pagination-info">
            {{ devices.from }}–{{ devices.to }} / {{ devices.total }} ডিভাইস
          </div>
          <div class="pagination" v-if="devices.last_page > 1">
            <button
              class="btn btn-outline btn-sm"
              :disabled="!devices.prev_page_url"
              @click="goPage(devices.current_page - 1)"
            >
              <Icon name="chevron-left" />
            </button>
            <span class="page-info">পৃষ্ঠা {{ devices.current_page }} / {{ devices.last_page }}</span>
            <button
              class="btn btn-outline btn-sm"
              :disabled="!devices.next_page_url"
              @click="goPage(devices.current_page + 1)"
            >
              <Icon name="chevron-right" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreate" class="modal-overlay" @click.self="showCreate = false">
      <div class="modal" :class="{ 'modal-lg': editingDevice }">
        <div class="modal-header">
          <h3>{{ editingDevice ? 'ডিভাইস সম্পাদনা' : 'নতুন ডিভাইস' }}</h3>
          <button class="btn btn-icon" @click="showCreate = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveDevice">
            <div class="form-group">
              <label class="form-label">ডিভাইসের নাম <span class="required">*</span></label>
              <input v-model="form.name" type="text" class="form-control" placeholder="যেমন: কক্ষ ৫ বায়োমেট্রিক স্ক্যানার" />
            </div>
            <div class="form-group">
              <label class="form-label">সিরিয়াল নম্বর <span class="required">*</span></label>
              <input v-model="form.serial_number" type="text" class="form-control" placeholder="ডিভাইসের সিরিয়াল নম্বর" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">ধরন <span class="required">*</span></label>
                <select v-model="form.device_type" class="form-select">
                  <option value="biometric">বায়োমেট্রিক</option>
                  <option value="rfid"> RFID</option>
                  <option value="scanner"> স্ক্যানার</option>
                  <option value="manual"> ম্যানুয়াল</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">অবস্থা <span class="required">*</span></label>
                <select v-model="form.status" class="form-select">
                  <option value="active">সক্রিয়</option>
                  <option value="inactive">নিষ্ক্রিয়</option>
                  <option value="syncing">সিঙ্ক করছে</option>
                  <option value="error">ত্রুটি</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">আইপি ঠিকানা</label>
                <input v-model="form.ip_address" type="text" class="form-control" placeholder="192.168.1.100" />
              </div>
              <div class="form-group">
                <label class="form-label">পোর্ট</label>
                <input v-model="form.port" type="number" class="form-control" placeholder="8080" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">অবস্থান</label>
              <input v-model="form.location" type="text" class="form-control" placeholder="যেমন: প্রধান কক্ষ, তৃতীয় তলা" />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showCreate = false">বাতিল</button>
          <button class="btn btn-primary" @click="saveDevice" :disabled="saving">
            <Icon name="spinner" v-if="saving" />
            {{ editingDevice ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirm -->
    <div v-if="showDelete" class="modal-overlay" @click.self="showDelete = false">
      <div class="modal">
        <div class="modal-header">
          <h3>আপনি কি নিশ্চিত?</h3>
          <button class="btn btn-icon" @click="showDelete = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <p>
            "{{ deleteTarget?.name }}" নামে ডিভাইসটি মুছে ফেলতে চান। এই কাজটি পূর্বাবস্থায় ফেরানো যাবে না।
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDelete = false">বাতিল</button>
          <button class="btn btn-danger" @click="confirmDelete" :disabled="deleting">
            <Icon name="spinner" v-if="deleting" />
            মুছে ফেলুন
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import Icon from '~/components/Icon.vue'

const api = useApiClient()

const loading = ref(true)
const devices = ref<any>({ data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null })
const search = ref('')
const statusFilter = ref('')
const showCreate = ref(false)
const editingDevice = ref<any>(null)
const form = reactive({
  name: '',
  serial_number: '',
  device_type: 'biometric',
  ip_address: '',
  port: null as any,
  status: 'active',
  location: '',
})
const showDelete = ref(false)
const deleteTarget = ref<any>(null)
const saving = ref(false)
const deleting = ref(false)
let searchTimeout: any = null
const per_page = 15

async function fetchDevices(page = 1) {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: String(page),
      per_page: String(per_page),
      ...(search.value ? { search: search.value } : {}),
      ...(statusFilter.value ? { status: statusFilter.value } : {}),
    })
    const res = await api.get(`/digital-attendance/devices?${params}`).catch(() => null)
    devices.value = res?.data || { data: [], from: 0, to: 0, total: 0, current_page: 1, last_page: 1, prev_page_url: null, next_page_url: null }
  } catch (err) {
    console.error('Failed to fetch devices:', err)
  } finally {
    loading.value = false
  }
}

function debounceSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchDevices(1), 300)
}

function goPage(page: number) {
  if (page < 1 || page > devices.value.last_page) return
  fetchDevices(page)
}

function editDevice(device: any) {
  editingDevice.value = device
  form.name = device.name || ''
  form.serial_number = device.serial_number || ''
  form.device_type = device.device_type || 'biometric'
  form.ip_address = device.ip_address || ''
  form.port = device.port || null
  form.status = device.status || 'active'
  form.location = device.location || ''
  showCreate.value = true
}

async function saveDevice() {
  saving.value = true
  try {
    const url = editingDevice.value
      ? `/digital-attendance/devices/${editingDevice.value.id}`
      : '/digital-attendance/devices'
    if (editingDevice.value) {
      await api.put(url, form).catch(() => null)
    } else {
      await api.post(url, form).catch(() => null)
    }
    showCreate.value = false
    editingDevice.value = null
    resetForm()
    fetchDevices(devices.value.current_page)
  } catch (err) {
    console.error('Save failed:', err)
  } finally {
    saving.value = false
  }
}

function resetForm() {
  form.name = ''
  form.serial_number = ''
  form.device_type = 'biometric'
  form.ip_address = ''
  form.port = null
  form.status = 'active'
  form.location = ''
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/digital-attendance/devices/${deleteTarget.value.id}`).catch(() => null)
    showDelete.value = false
    deleteTarget.value = null
    fetchDevices(devices.value.current_page)
  } catch (err) {
    console.error('Delete failed:', err)
  } finally {
    deleting.value = false
  }
}

function deleteDevice(device: any) {
  deleteTarget.value = device
  showDelete.value = true
}

function deviceTypeClass(type: string) {
  const map: Record<string, string> = { biometric: 'green', rfid: 'blue', scanner: 'purple', manual: 'gray' }
  return map[type] || 'gray'
}

function formatDeviceType(type: string) {
  const map: Record<string, string> = { biometric: 'বায়োমেট্রিক', rfid: 'RFID', scanner: 'স্ক্যানার', manual: 'ম্যানুয়াল' }
  return map[type] || type
}

function formatStatus(status: string) {
  const map: Record<string, string> = { active: 'সক্রিয়', inactive: 'নিষ্ক্রিয়', syncing: 'সিঙ্ক করছে', error: 'ত্রুটি' }
  return map[status] || status
}

function formatDate(date: string) {
  if (!date) return '—'
  try { return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) }
  catch { return date }
}

onMounted(() => {
  fetchDevices()
})
</script>

<style scoped lang="scss">
.modal-lg {
  max-width: 640px;
}
.status-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  &::before {
    content: '';
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
  }
  &.status-active::before { background: #2e7d32; }
  &.status-inactive::before { background: #9e9e9e; }
  &.status-syncing::before { background: #1565c0; animation: pulse 1.5s infinite; }
  &.status-error::before { background: #c62828; }

  @keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.4; }
    100% { opacity: 1; }
  }
}
.mono {
  font-family: 'Courier New', monospace;
  font-size: 0.85rem;
}
.text-muted {
  color: #6c757d;
}
.actions-cell {
  white-space: nowrap;
}
</style>
