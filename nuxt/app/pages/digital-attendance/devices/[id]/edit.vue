<template>
  <div class="module-page">
    <div class="breadcrumb">
      <NavLink to="/digital-attendance/devices">ডিজিটাল হাজিরা ডিভাইস</NavLink>
      <Icon name="chevron-right" class="crumb-sep" />
      <span>ডিভাইস সম্পাদনা</span>
    </div>
    <div class="module-card card" v-if="device">
      <div class="module-card-header"><h1>{{ device.device_name }}</h1></div>
      <form @submit.prevent="submit">
        <div class="grid gap">
          <div class="field"><label>ডিভাইসের নাম *</label><input v-model="form.device_name" type="text" class="form-control" required /></div>
          <div class="field"><label>ধরন *</label>
            <select v-model="form.device_type" class="form-control" required>
              <option value="bio">বায়োমেট্রিক (আঙুলের ছাপ)</option>
              <option value="gateway">গেটওয়ে (আইডি কার্ড রিডার)</option>
              <option value="software">সফটওয়্যার (মোবাইল অ্যাপ ভিত্তিক)</option>
            </select>
          </div>
          <div class="grid-2">
            <div class="field"><label>আইপি ঠিকানা</label><input v-model="form.ip_address" type="text" class="form-control" /></div>
            <div class="field"><label>পোর্ট</label><input v-model="form.port" type="number" class="form-control" min="1" max="65535" /></div>
          </div>
          <div class="field checkbox-field"><label class="checkbox-label"><input type="checkbox" v-model="form.is_active" /> সক্রিয়</label></div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-outline" @click="$router.back()">বাতিল করুন</button>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            <span v-if="saving">হালনাগাদ হচ্ছে...</span>
            <span v-else>হালনাগাদ করুন</span>
          </button>
        </div>
      </form>
    </div>
    <div v-else class="module-empty-state"><p>লোড হচ্ছে...</p></div>
    <ApiAlert :message="alert" v-if="alert" />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRoute } from 'vue-router'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const route = useRoute()
const saving = ref(false)
const alert = ref('')
const device = ref<any>(null)
const form = reactive({
  device_name: '',
  device_type: 'bio',
  ip_address: '',
  port: 8080,
  is_active: true,
})
async function load() {
  try {
    const res = await api.get(`/digital-attendance/devices/${route.params.id}`)
    device.value = res.data
    form.device_name = res.data?.device_name || ''
    form.device_type = res.data?.device_type || 'bio'
    form.ip_address = res.data?.ip_address || ''
    form.port = res.data?.port || 8080
    form.is_active = res.data?.is_active ?? true
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'ডিভাইস লোড করতে ত্রুটি হয়েছে।'
  }
}
async function submit() {
  saving.value = true; alert.value = ''
  try {
    await api.put(`/digital-attendance/devices/${route.params.id}`, form)
    alert.value = 'ডিভাইস হালনাগাদ করা হয়েছে।'
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'হালনাগাদ করতে ত্রুটি হয়েছে।'
  } finally { saving.value = false }
}
onMounted(() => { if (!isAuthenticated.value && !authLoading.value) return; load() })
</script>
