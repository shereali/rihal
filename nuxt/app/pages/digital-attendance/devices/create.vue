<template>
  <div class="module-page">
    <div class="breadcrumb"><NavLink to="/digital-attendance/devices">ডিজিটাল হাজিরা ডিভাইস</NavLink><Icon name="chevron-right" class="crumb-sep" /><span>নতুন ডিভাইস</span></div>
    <div class="module-card card">
      <div class="module-card-header"><h1>নতুন ডিজিটাল হাজিরা ডিভাইস</h1></div>
      <form @submit.prevent="submit">
        <div class="grid gap">
          <div class="field"><label>ডিভাইসের নাম *</label><input v-model="form.device_name" type="text" class="form-control" required placeholder="যেমন: হল-১ বায়োমেট্রিক রিডার" /></div>
          <div class="field"><label>ধরন *</label>
            <select v-model="form.device_type" class="form-control" required>
              <option value="">নির্বাচন করুন</option>
              <option value="bio">বায়োমেট্রিক (আঙুলের ছাপ)</option>
              <option value="gateway">গেটওয়ে (আইডি কার্ড রিডার)</option>
              <option value="software">সফটওয়্যার (মোবাইল অ্যাপ ভিত্তিক)</option>
            </select>
          </div>
          <div class="grid-2">
            <div class="field"><label>আইপি ঠিকানা</label><input v-model="form.ip_address" type="text" class="form-control" placeholder="যেমন: 192.168.1.100" /></div>
            <div class="field"><label>পোর্ট</label><input v-model="form.port" type="number" class="form-control" min="1" max="65535" placeholder="যেমন: 8080" /></div>
          </div>
          <div class="field checkbox-field"><label class="checkbox-label"><input type="checkbox" v-model="form.is_active" /> ডিফল্টভাবে সক্রিয়</label></div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn btn-outline" @click="$router.back()">বাতিল করুন</button>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            <span v-if="saving">ডিভাইস যোগ হচ্ছে...</span>
            <span v-else>ডিভাইস যোগ করুন</span>
          </button>
        </div>
      </form>
    </div>
    <ApiAlert :message="alert" v-if="alert" />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const saving = ref(false)
const alert = ref('')
const form = reactive({
  device_name: '',
  device_type: 'bio',
  ip_address: '',
  port: 8080,
  is_active: true,
})
async function submit() {
  saving.value = true; alert.value = ''
  try {
    await api.post('/digital-attendance/devices', form)
    alert.value = 'ডিভাইস সফলভাবে যোগ করা হয়েছে।'
    form.device_name = ''
    form.ip_address = ''
    form.port = 8080
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'ডিভাইস যোগ করতে ত্রুটি হয়েছে।'
  } finally { saving.value = false }
}
</script>
