<template>
  <div class="page-wrapper">
    <NuxtLink to="/digital-attendance/devices" class="btn btn-outline btn-sm back-link">
      <Icon name="arrow-left" /> ফিরে যান
    </NuxtLink>

    <div class="card" style="margin-top: 1rem;">
      <div class="card-inner">
        <div class="card-header">
          <h2>{{ editingDevice ? 'ডিভাইস সম্পাদনা' : 'নতুন হাজিরা ডিভাইস' }}</h2>
        </div>
        <div class="card-body">
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
                  <option value="rfid">RFID</option>
                  <option value="scanner">স্ক্যানার</option>
                  <option value="manual">ম্যানুয়াল</option>
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
        <div slot="footer" class="card-footer">
          <button class="btn btn-outline" @click="$router.back()">বাতিল</button>
          <button class="btn btn-primary" @click="saveDevice" :disabled="saving">
            <Icon name="spinner" v-if="saving" />
            {{ editingDevice ? 'আপডেট করুন' : 'সংরক্ষণ করুন' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Icon from '~/components/ui/Icon.vue'

export default {
  components: { Icon },
  data() {
    return {
      editingDevice: null,
      form: {
        name: '',
        serial_number: '',
        device_type: 'biometric',
        ip_address: '',
        port: null,
        status: 'active',
        location: '',
      },
      saving: false,
    }
  },

  async asyncData({ params, error }) {
    if (params.id !== 'new') {
      try {
        const res = await fetch(`${process.env.apiBase}/digital-attendance/devices/${params.id}`)
        if (!res.ok) throw new Error('ডিভাইস পাওয়া যায়নি')
        const json = await res.json()
        if (json.status === 200 && json.data) {
          return {
            editingDevice: json.data,
            form: {
              name: json.data.name || '',
              serial_number: json.data.serial_number || '',
              device_type: json.data.device_type || 'biometric',
              ip_address: json.data.ip_address || '',
              port: json.data.port || null,
              status: json.data.status || 'active',
              location: json.data.location || '',
            },
          }
        }
        throw new Error(json.message || 'ডিভাইস লোড করতে সমস্যা')
      } catch (err) {
        error({ statusCode: 404, message: err.message })
      }
    }
    return {}
  },

  methods: {
    async saveDevice() {
      this.saving = true
      try {
        const url = this.editingDevice
          ? `${process.env.apiBase}/digital-attendance/devices/${this.editingDevice.id}`
          : `${process.env.apiBase}/digital-attendance/devices`
        const method = this.editingDevice ? 'put' : 'post'

        const res = await fetch(url, {
          method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(this.form),
        })
        const json = await res.json()

        if (json.status && json.status < 500) {
          this.$router.push('/digital-attendance/devices')
        } else {
          alert(json.message || 'সংরক্ষণে সমস্যা হয়েছে')
        }
      } catch (err) {
        console.error('Save failed:', err)
        alert('সংরক্ষণে সমস্যা হয়েছে')
      } finally {
        this.saving = false
      }
    },
  },
}
</script>

<style scoped lang="scss">
.back-link {
  margin-bottom: 1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}
</style>
