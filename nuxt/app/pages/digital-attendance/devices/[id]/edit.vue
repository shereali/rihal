<template>
  <div class="page-wrapper">
    <NuxtLink to="/digital-attendance/devices" class="btn btn-outline btn-sm back-link">
      <Icon name="arrow-left" /> ফিরে যান
    </NuxtLink>

    <div v-if="device" class="card" style="margin-top: 1rem;">
      <div class="card-inner">
        <div class="card-header">
          <h2>ডিভাইসের তথ্য</h2>
        </div>
        <div class="card-body">
          <dl class="info-list">
            <div class="info-row">
              <dt>নাম</dt>
              <dd><strong>{{ device.name }}</strong></dd>
            </div>
            <div class="info-row">
              <dt>সিরিয়াল নম্বর</dt>
              <dd><code class="mono">{{ device.serial_number }}</code></dd>
            </div>
            <div class="info-row">
              <dt>ধরন</dt>
              <dd>
                <span :class="`badge badge-${deviceTypeClass(device.device_type)}`">
                  {{ formatDeviceType(device.device_type) }}
                </span>
              </dd>
            </div>
            <div class="info-row">
              <dt>আইপি ঠিকানা</dt>
              <dd>
                <span v-if="device.ip_address" class="mono">{{ device.ip_address }}</span>
                <span v-else class="text-muted">—</span>
              </dd>
            </div>
            <div class="info-row">
              <dt>পোর্ট</dt>
              <dd>{{ device.port ?? '—' }}</dd>
            </div>
            <div class="info-row">
              <dt>অবস্থা</dt>
              <dd>
                <span :class="`status-badge status-${device.status}`">
                  {{ formatStatus(device.status) }}
                </span>
              </dd>
            </div>
            <div class="info-row">
              <dt>অবস্থান</dt>
              <dd>{{ device.location ?? '—' }}</dd>
            </div>
            <div class="info-row">
              <dt>সর্বশেষ সিঙ্ক</dt>
              <dd>
                <span v-if="device.last_synced_at">{{ formatDate(device.last_synced_at) }}</span>
                <span v-else class="text-muted">কখনো নয়</span>
              </dd>
            </div>
          </dl>
        </div>
        <div slot="footer" class="card-footer">
          <button class="btn btn-outline" @click="$router.back()">বাতিল</button>
          <button class="btn btn-primary" @click="startEdit">
            <Icon name="pencil" /> সম্পাদনা করুন
          </button>
          <button class="btn btn-danger" @click="confirmDelete" :disabled="deleting">
            <Icon name="trash" /> মুছে ফেলুন
          </button>
        </div>
      </div>
    </div>
    <div v-else class="empty-state">
      <p>ডিভাইস লোড করতে সমস্যা হয়েছে।</p>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEdit" class="modal-overlay" @click.self="showEdit = false">
      <div class="modal">
        <div class="modal-header">
          <h3>ডিভাইস সম্পাদনা</h3>
          <button class="btn btn-icon" @click="showEdit = false">
            <Icon name="close" />
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveEdit">
            <div class="form-group">
              <label class="form-label">ডিভাইসের নাম <span class="required">*</span></label>
              <input v-model="editForm.name" type="text" class="form-control" />
            </div>
            <div class="form-group">
              <label class="form-label">সিরিয়াল নম্বর <span class="required">*</span></label>
              <input v-model="editForm.serial_number" type="text" class="form-control" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">ধরন</label>
                <select v-model="editForm.device_type" class="form-select">
                  <option value="biometric">বায়োমেট্রিক</option>
                  <option value="rfid">RFID</option>
                  <option value="scanner">স্ক্যানার</option>
                  <option value="manual">ম্যানুয়াল</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">অবস্থা</label>
                <select v-model="editForm.status" class="form-select">
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
                <input v-model="editForm.ip_address" type="text" class="form-control" />
              </div>
              <div class="form-group">
                <label class="form-label">পোর্ট</label>
                <input v-model="editForm.port" type="number" class="form-control" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">অবস্থান</label>
              <input v-model="editForm.location" type="text" class="form-control" />
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showEdit = false">বাতিল</button>
          <button class="btn btn-primary" @click="saveEdit" :disabled="saving">
            <Icon name="spinner" v-if="saving" />
            আপডেট করুন
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
            "{{ device?.name }}" ডিভাইসটি মুছে ফেলতে চান।
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDelete = false">বাতিল</button>
          <button class="btn btn-danger" @click="doDelete" :disabled="deleting">
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
import { useRouter, useRoute } from 'vue-router'
import Icon from '~/components/Icon.vue'

const api = useApiClient()
const router = useRouter()
const route = useRoute()

const device = ref<any>(null)
const showEdit = ref(false)
const editForm = reactive({ name: '', serial_number: '', device_type: 'biometric', ip_address: '', port: null as any, status: 'active', location: '' })
const saving = ref(false)
const showDelete = ref(false)
const deleting = ref(false)

async function loadDevice() {
  const id = route.params.id
  if (id) {
    try {
      const res = await api.get(`/digital-attendance/devices/${id}`).catch(() => null)
      if (res?.data?.data) {
        device.value = res.data.data
      }
    } catch (err) {
      console.error(err)
    }
  }
}

function startEdit() {
  if (!device.value) return
  editForm.name = device.value.name || ''
  editForm.serial_number = device.value.serial_number || ''
  editForm.device_type = device.value.device_type || 'biometric'
  editForm.ip_address = device.value.ip_address || ''
  editForm.port = device.value.port || null
  editForm.status = device.value.status || 'active'
  editForm.location = device.value.location || ''
  showEdit.value = true
}

async function saveEdit() {
  saving.value = true
  try {
    const res = await api.put(`/digital-attendance/devices/${device.value.id}`, editForm).catch(() => null)
    if (res?.data?.data) {
      device.value = res.data.data
    }
    showEdit.value = false
  } catch (err) {
    console.error('Update failed:', err)
  } finally {
    saving.value = false
  }
}

function confirmDelete() {
  showDelete.value = true
}

async function doDelete() {
  deleting.value = true
  try {
    await api.delete(`/digital-attendance/devices/${device.value.id}`).catch(() => null)
    router.push('/digital-attendance/devices')
  } catch (err) {
    console.error('Delete failed:', err)
  } finally {
    deleting.value = false
  }
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

onMounted(loadDevice)
</script>

<style scoped lang="scss">
.back-link { margin-bottom: 1rem; display: inline-flex; align-items: center; gap: 0.4rem; }
.mono { font-family: 'Courier New', monospace; font-size: 0.85rem; }
.text-muted { color: #6c757d; }
.info-list { display: grid; grid-template-columns: 1fr 2fr; gap: 0.75rem 1.5rem; }
.info-row { padding: 0.4rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
.info-row dt { color: #6c757d; font-size: 0.9rem; }
.status-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 500;
  &.status-active { background: #e8f5e9; color: #2e7d32; }
  &.status-inactive { background: #f5f5f5; color: #6c757d; }
  &.status-syncing { background: #e3f2fd; color: #1565c0; }
  &.status-error { background: #ffebee; color: #c62828; }
}
</style>
