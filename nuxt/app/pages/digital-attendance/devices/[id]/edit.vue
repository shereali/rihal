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

<script>
import Icon from '~/components/Icon.vue'

export default {
  components: { Icon },
  data() {
    return {
      device: null,
      showEdit: false,
      editForm: { name: '', serial_number: '', device_type: 'biometric', ip_address: '', port: null, status: 'active', location: '' },
      saving: false,
      showDelete: false,
      deleting: false,
    }
  },

  async asyncData({ params, error }) {
    try {
      const res = await fetch(`${process.env.apiBase}/digital-attendance/devices/${params.id}`)
      if (!res.ok) throw new Error('না পাওয়া গেল')
      const json = await res.json()
      if (json.status === 200 && json.data) {
        return { device: json.data }
      }
      throw new Error(json.message || 'ডিভাইস লোড করতে সমস্যা')
    } catch (err) {
      error({ statusCode: 404, message: err.message })
      return { device: null }
    }
  },

  methods: {
    startEdit() {
      if (!this.device) return
      this.editForm = {
        name: this.device.name || '',
        serial_number: this.device.serial_number || '',
        device_type: this.device.device_type || 'biometric',
        ip_address: this.device.ip_address || '',
        port: this.device.port || null,
        status: this.device.status || 'active',
        location: this.device.location || '',
      }
      this.showEdit = true
    },

    async saveEdit() {
      this.saving = true
      try {
        const res = await fetch(`${process.env.apiBase}/digital-attendance/devices/${this.device.id}`, {
          method: 'put',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.editForm),
        })
        const json = await res.json()
        if (json.status && json.status < 500 && json.data) {
          this.device = json.data
          this.showEdit = false
          this.editForm = { name: '', serial_number: '', device_type: 'biometric', ip_address: '', port: null, status: 'active', location: '' }
        } else {
          alert(json.message || 'আপডেটে সমস্যা হয়েছে')
        }
      } catch (err) {
        console.error('Update failed:', err)
        alert('আপডেটে সমস্যা হয়েছে')
      } finally {
        this.saving = false
      }
    },

    confirmDelete() {
      this.showDelete = true
    },

    async doDelete() {
      this.deleting = true
      try {
        const res = await fetch(`${process.env.apiBase}/digital-attendance/devices/${this.device.id}`, { method: 'delete' })
        const json = await res.json()
        if (json.status && json.status < 500) {
          this.$router.push('/digital-attendance/devices')
        } else {
          alert(json.message || 'মুছে ফেলতে সমস্যা হয়েছে')
          this.showDelete = false
        }
      } catch (err) {
        console.error('Delete failed:', err)
        alert('মুছে ফেলতে সমস্যা হয়েছে')
        this.showDelete = false
      } finally {
        this.deleting = false
      }
    },

    deviceTypeClass(type) {
      const map = { biometric: 'green', rfid: 'blue', scanner: 'purple', manual: 'gray' }
      return map[type] || 'gray'
    },
    formatDeviceType(type) {
      const map = { biometric: 'বায়োমেট্রিক', rfid: 'RFID', scanner: 'স্ক্যানার', manual: 'ম্যানুয়াল' }
      return map[type] || type
    },
    formatStatus(status) {
      const map = { active: 'সক্রিয়', inactive: 'নিষ্ক্রিয়', syncing: 'সিঙ্ক করছে', error: 'ত্রুটি' }
      return map[status] || status
    },
    formatDate(date) {
      if (!date) return ''
      try {
        return new Date(date).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' })
      } catch { return date }
    },
  },
}
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
