<template>
  <div class="page-wrapper">
    <div class="breadcrumb">
      <NuxtLink to="/hr">স্টাফ ও কর্মী</NuxtLink>
      <span class="sep">/</span>
      <NuxtLink :to="`/hr/staff/${staffId}`">{{ staffName || 'কর্মকর্তা' }}</NuxtLink>
      <span class="sep">/</span>
      <span class="breadcrumb-current">সম্পাদনা</span>
    </div>

    <div class="page-header">
      <div class="header-left">
        <NuxtLink :to="`/hr/staff/${staffId}`" class="btn btn-outline btn-sm">
          <icon name="arrow-left" /> প্রোফাইলে ফিরে যান
        </NuxtLink>
        <div class="title-group">
          <h1>কর্মকর্তা তথ্য সম্পাদনা</h1>
          <p class="text-muted" v-if="staffName"><strong>{{ staffName }}</strong> এর বিস্তারিত তথ্য হালনাগাদ করুন</p>
        </div>
      </div>
      <div class="header-actions">
        <NuxtLink :to="`/hr/staff/${staffId}`" class="btn btn-ghost btn-sm">
          বাতিল
        </NuxtLink>
        <button class="btn btn-primary btn-sm" :disabled="saving || !form.name_bn" @click="saveStaff">
          <icon v-if="saving" name="loader" />
          <icon v-else name="save" />
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন' }}
        </button>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">
      <icon name="alert-circle" />
      <span>{{ error }}</span>
    </div>

    <div v-if="success" class="alert alert-success">
      <icon name="check-circle" />
      <span>{{ success }}</span>
    </div>

    <div v-if="loading" class="card loading-card">
      <div class="spinner" />
      <p>তথ্য লোড হচ্ছে...</p>
    </div>

    <div v-else class="card form-card">
      <form @submit.prevent="saveStaff" class="card-body">
        <div class="form-section">
          <h4 class="section-title">ব্যক্তিগত ও পরিচিতি তথ্য</h4>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">নাম (বাংলায়) <span class="required-star">*</span></label>
              <input v-model="form.name_bn" type="text" class="form-control" required placeholder="যেমন: মাওলানা হাফিজুর রহমান" />
            </div>
            <div class="form-group">
              <label class="form-label">নাম (ইংরেজিতে)</label>
              <input v-model="form.name_en" type="text" class="form-control" placeholder="e.g. Mawlana Hafizur Rahman" />
            </div>
          </div>

          <div class="form-row form-row-3">
            <div class="form-group">
              <label class="form-label">পদবী</label>
              <input v-model="form.designation" type="text" class="form-control" placeholder="উস্তাদ, প্রিন্সিপাল, হিসাবরক্ষক..." />
            </div>
            <div class="form-group">
              <label class="form-label">বিভাগ</label>
              <select v-model="form.department" class="form-control form-select">
                <option value="">নির্বাচন করুন</option>
                <option value="Academic">একাডেমিক</option>
                <option value="Administration">প্রশাসন</option>
                <option value="Finance">হিসাব ও অর্থ</option>
                <option value="IT">আইটি</option>
                <option value="Support">সহায়ক কর্মী</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">মাসিক বেতন (৳)</label>
              <input v-model.number="form.salary" type="number" class="form-control" min="0" placeholder="0.00" />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h4 class="section-title">যোগাযোগ ও পরিচয়পত্র</h4>
          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">মোবাইল ফোন নম্বর</label>
              <input v-model="form.phone" type="tel" class="form-control" placeholder="017XXXXXXXX" />
            </div>
            <div class="form-group">
              <label class="form-label">ইমেইল ঠিকানা</label>
              <input v-model="form.email" type="email" class="form-control" placeholder="staff@example.com" />
            </div>
          </div>

          <div class="form-row form-row-2">
            <div class="form-group">
              <label class="form-label">জাতীয় পরিচয়পত্র নম্বর (NID)</label>
              <input v-model="form.nid_number" type="text" class="form-control" placeholder="১০ বা ১৭ ডিজিটের NID" />
            </div>
            <div class="form-group">
              <label class="form-label">যোগদানের তারিখ</label>
              <input v-model="form.join_date" type="date" class="form-control" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">স্থায়ী ও বর্তমান ঠিকানা</label>
            <textarea v-model="form.address_bn" class="form-control" rows="3" placeholder="গ্রাম/মহল্লা, ডাকঘর, থানা/উপজেলা, জেলা..."></textarea>
          </div>
        </div>

        <div class="form-section">
          <h4 class="section-title">জীবনী ও বিবরণ</h4>
          <div class="form-group">
            <label class="form-label">জীবনী (বাংলা)</label>
            <textarea v-model="form.bio_bn" class="form-control" rows="3" placeholder="শিক্ষাগত যোগ্যতা, অভিজ্ঞতা ইত্যাদি..."></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">জীবনী (ইংরেজি)</label>
            <textarea v-model="form.bio_en" class="form-control" rows="2" placeholder="Educational background, experience..."></textarea>
          </div>
          <div class="form-group form-check mt-3">
            <label class="form-check-label">
              <input type="checkbox" v-model="form.is_active" class="form-check-input" />
              <span>কর্মকর্তা/কর্মচারী বর্তমানে সক্রিয় (Active) আছেন</span>
            </label>
          </div>
        </div>

        <div class="form-actions-footer">
          <NuxtLink :to="`/hr/staff/${staffId}`" class="btn btn-ghost">
            বাতিল
          </NuxtLink>
          <button type="submit" class="btn btn-primary btn-lg" :disabled="saving || !form.name_bn">
            <icon v-if="saving" name="loader" />
            <icon v-else name="save" />
            {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()

const staffId = computed(() => route.params.id as string)
const staffName = ref('')
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const success = ref('')

const form = reactive({
  name_bn: '',
  name_en: '',
  phone: '',
  email: '',
  designation: '',
  department: '',
  salary: 0,
  nid_number: '',
  join_date: '',
  address_bn: '',
  bio_bn: '',
  bio_en: '',
  is_active: true,
})

async function loadStaff() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get(`/hr/staff/${staffId.value}`)
    const s = res.data?.data
    if (!s) {
      error.value = 'কর্মকর্তার কোনো তথ্য পাওয়া যায়নি।'
      return
    }

    staffName.value = s.name_bn || s.name_en || 'কর্মকর্তা'
    form.name_bn = s.name_bn || ''
    form.name_en = s.name_en || ''
    form.phone = s.phone || s.user?.phone || ''
    form.email = s.email || s.user?.email || ''
    form.designation = s.designation || ''
    form.department = s.department || ''
    form.salary = s.salary ? Number(s.salary) : 0
    form.nid_number = s.nid_number || ''
    form.join_date = s.join_date ? String(s.join_date).slice(0, 10) : ''
    form.address_bn = s.address_bn || ''
    form.bio_bn = s.bio_bn || ''
    form.bio_en = s.bio_en || ''
    form.is_active = s.is_active ?? true
  } catch (err: any) {
    console.error('Failed to load staff:', err)
    error.value = err?.response?.data?.message || 'তথ্য লোড করতে সমস্যা হয়েছে।'
  } finally {
    loading.value = false
  }
}

async function saveStaff() {
  if (!form.name_bn.trim()) return
  saving.value = true
  error.value = ''
  success.value = ''

  try {
    const res = await api.put(`/hr/staff/${staffId.value}`, form)
    success.value = res.data?.message || 'কর্মকর্তার তথ্য সফলভাবে আপডেট করা হয়েছে!'
    setTimeout(() => {
      navigateTo(`/hr/staff/${staffId.value}`)
    }, 700)
  } catch (err: any) {
    console.error('Failed to update staff:', err)
    error.value = err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে। তথ্যাবলী পুনরায় যাচাই করুন।'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  loadStaff()
})
</script>

<style scoped>
.page-wrapper {
  max-width: 900px;
  margin: 0 auto;
  padding: 1.5rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 1rem;
  font-size: 0.82rem;
  color: var(--color-text-muted);
}

.breadcrumb .sep {
  color: var(--color-text-muted);
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.breadcrumb .breadcrumb-current {
  color: var(--color-text);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.title-group h1 {
  font-size: 1.4rem;
  margin: 0;
  color: var(--color-primary);
  font-family: var(--font-bn);
}

.title-group p {
  margin: 0.2rem 0 0 0;
  font-size: 0.85rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card {
  background: var(--color-bg-card, #fff);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md, 8px);
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--color-border);
}

.form-section:last-of-type {
  border-bottom: none;
  margin-bottom: 1rem;
  padding-bottom: 0;
}

.section-title {
  font-size: 1.05rem;
  color: var(--color-primary);
  margin: 0 0 1.25rem 0;
  font-family: var(--font-bn);
}

.form-row {
  display: grid;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-row-2 {
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.form-row-3 {
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 1rem;
}

.form-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--color-text);
}

.required-star {
  color: #dc2626;
}

.form-control, .form-select {
  padding: 0.6rem 0.85rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm, 6px);
  background: var(--color-bg);
  font-size: 0.9rem;
  color: var(--color-text);
}

.form-control:focus, .form-select:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.form-actions-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border);
}

.alert {
  padding: 0.85rem 1.25rem;
  border-radius: var(--radius-sm, 6px);
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.alert-error {
  background: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}

.alert-success {
  background: #f0fdf4;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.loading-card {
  text-align: center;
  padding: 3rem 1rem;
}
</style>
