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

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useRouter, useRoute } from 'vue-router'
import Icon from '~/components/Icon.vue'

const api = useApiClient()
const router = useRouter()
const route = useRoute()

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
const saving = ref(false)

async function loadDevice() {
  const id = route.params.id
  if (id && id !== 'new') {
    try {
      const res = await api.get(`/digital-attendance/devices/${id}`).catch(() => null)
      if (res?.data?.data) {
        editingDevice.value = res.data.data
        form.name = res.data.data.name || ''
        form.serial_number = res.data.data.serial_number || ''
        form.device_type = res.data.data.device_type || 'biometric'
        form.ip_address = res.data.data.ip_address || ''
        form.port = res.data.data.port || null
        form.status = res.data.data.status || 'active'
        form.location = res.data.data.location || ''
      }
    } catch (err) {
      console.error(err)
    }
  }
}

async function saveDevice() {
  saving.value = true
  try {
    const url = editingDevice.value ? `/digital-attendance/devices/${editingDevice.value.id}` : '/digital-attendance/devices'
    if (editingDevice.value) {
      await api.put(url, form).catch(() => null)
    } else {
      await api.post(url, form).catch(() => null)
    }
    router.push('/digital-attendance/devices')
  } catch (err) {
    console.error('Save failed:', err)
  } finally {
    saving.value = false
  }
}

onMounted(loadDevice)
</script>

<style scoped lang="scss">
.back-link {
  margin-bottom: 1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}
</style>
