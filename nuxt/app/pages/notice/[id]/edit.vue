<template>
  <div class="notice-edit">
    <div class="page-header">
      <NuxtLink to="/notice" class="btn btn-outline btn-sm">
        <icon :name="mdiArrowLeft" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink to="/notice" class="btn btn-secondary btn-sm">
          <icon :name="mdiClose" /> বাতিল
        </NuxtLink>
        <button class="btn btn-primary btn-sm" :disabled="saving || !isFormValid" @click="saveNotice">
          <icon v-if="saving" :name="mdiLoad" /><icon v-else :name="mdiContentSave" />
          {{ editing ? 'আপডেট' : 'প্রকাশ' }}
        </button>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <form @submit.prevent="saveNotice">
          <div class="form-section">
            <h4 class="section-title">মৌলিক তথ্য</h4>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">শিরোনাম (বাংলা) *</label>
                <input v-model="form.title_bn" type="text" class="form-control" placeholder="বিজ্ঞপ্তির শিরোনাম বাংলায়" required />
                <p v-if="errors.title_bn" class="error-message">{{ errors.title_bn[0] }}</p>
              </div>
              <div class="form-group">
                <label class="form-label">শিরোনাম (ইংরেজি)</label>
                <input v-model="form.title_en" type="text" class="form-control" placeholder="Notice title in English" />
              </div>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">বিভাগ</label>
                <select v-model="form.category" class="form-control">
                  <option value="">নির্বাচন করুন</option>
                  <option value="সাধারণ">সাধারণ</option>
                  <option value="শিক্ষা">শিক্ষা</option>
                  <option value="পরীক্ষা">পরীক্ষা</option>
                  <option value="ছুটি">ছুটি</option>
                  <option value="অনুষ্ঠান">অনুষ্ঠান</option>
                  <option value="যোগাযোগ">যোগাযোগ</option>
                  <option value="সতর্কবার্তা">সতর্কবার্তা</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">গুরুত্বপূর্ণতা</label>
                <select v-model="form.priority" class="form-control">
                  <option value="normal">সাধারণ</option>
                  <option value="low">নিম্ন</option>
                  <option value="high">উচ্চ</option>
                  <option value="urgent">জরুরি</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4 class="section-title">বিবরণ</h4>
            <div class="form-group">
              <label class="form-label">বিবরণ (বাংলা) *</label>
              <textarea v-model="form.content_bn" class="form-control" rows="6" placeholder="বিজ্ঞপ্তির বিস্তারিত বিবরণ বাংলায় লিখুন..." required />
              <p v-if="errors.content_bn" class="error-message">{{ errors.content_bn[0] }}</p>
            </div>
            <div class="form-group">
              <label class="form-label">বিবরণ (ইংরেজি)</label>
              <textarea v-model="form.content_en" class="form-control" rows="4" placeholder="Notice details in English (optional)" />
            </div>
          </div>

          <div class="form-section">
            <h4 class="section-title">প্রকাশনা সেটিংস</h4>
            <div class="form-row form-row-2">
              <div class="form-group form-check">
                <label class="form-check-label"><input type="checkbox" v-model="form.is_pinned" class="form-check-input" /> পিন করুন (শীর্ষে দেখাবে)</label>
              </div>
              <div class="form-group form-check">
                <label class="form-check-label"><input type="checkbox" v-model="form.is_active" class="form-check-input" /> সক্রিয় (ডিফল্ট: হ্যাঁ)</label>
              </div>
            </div>
            <div class="form-row form-row-2">
              <div class="form-group">
                <label class="form-label">প্রকাশের তারিখ</label>
                <input v-model="form.scheduled_at" type="date" class="form-control" />
                <p class="form-hint text-muted">খালি রাখলে এখনই প্রকাশিত হবে</p>
              </div>
              <div class="form-group">
                <label class="form-label">মেয়াদ শেষের তারিখ</label>
                <input v-model="form.expired_at" type="date" class="form-control" />
                <p class="form-hint text-muted">খালি রাখলে চিরস্থায়ী</p>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="saving || !isFormValid">
              <icon v-if="saving" :name="mdiLoad" /><icon v-else :name="mdiContentSave" />
              {{ editing ? 'আপডেট' : 'প্রকাশ' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const editing = computed(() => route.params.id !== 'create')
const saving = ref(false)
const errors = ref<any>({})

const form = reactive({
  title_bn: '', title_en: '', content_bn: '', content_en: '',
  category: '', priority: 'normal', is_pinned: false, is_active: true,
  scheduled_at: '', expired_at: '', target_audience: [] as string[], attachments: [] as string[], channels: [] as string[],
})

const isFormValid = computed(() => form.title_bn.trim().length >= 3 && form.content_bn.trim().length >= 10)

async function loadNotice() {
  if (!editing.value) return
  try {
    const res = await api.get(`/notices/${route.params.id}`)
    const n = res.data.data
    form.title_bn = n.title_bn || ''; form.title_en = n.title_en || ''; form.content_bn = n.content_bn || ''; form.content_en = n.content_en || ''
    form.category = n.category || ''; form.priority = n.priority || 'normal'; form.is_pinned = n.is_pinned || false
    form.is_active = n.is_active ?? true; form.scheduled_at = n.scheduled_at || ''; form.expired_at = n.expired_at || ''
    form.target_audience = n.target_audience || []; form.attachments = n.attachments || []; form.channels = n.channels || []
  } catch (error) { console.error('Failed to load notice:', error); navigateTo('/notice') }
}

async function saveNotice() {
  saving.value = true; errors.value = {}
  try {
    if (editing.value) await api.put(`/notices/${route.params.id}`, form)
    else await api.post('/notices', form)
    navigateTo('/notice')
  } catch (error: any) {
    if (error.response?.data?.errors) errors.value = error.response.data.errors
    else alert('সংরক্ষণে ত্রুটি: ' + (error.message || 'অজানা ত্রুটি'))
  } finally { saving.value = false }
}

onMounted(() => { if (editing.value) loadNotice() })
</script>

<style scoped>
.notice-edit { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.header-actions { display: flex; gap: 0.5rem; }
.form-section { margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border-light); }
.form-section:last-of-type { border-bottom: none; }
.section-title { font-size: 1rem; font-weight: 600; color: var(--color-text); margin: 0 0 1rem 0; }
.form-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.form-row-2 { grid-template-columns: repeat(2, 1fr); }
.form-group { display: flex; flex-direction: column; gap: 0.375rem; }
.form-label { font-size: 0.875rem; font-weight: 500; color: var(--color-text); }
.form-control { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: 0.9375rem; color: var(--color-text); background: var(--color-bg); }
.form-control:focus { outline: none; border-color: var(--color-primary); box-shadow: 0 0 0 2px var(--color-primary-light); }
textarea.form-control { resize: vertical; }
form-check { flex-direction: row; align-items: center; gap: 0.5rem; }
form-check-label { font-size: 0.875rem; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; }
form-check-input { width: 18px; height: 18px; }
.form-hint { font-size: 0.8125rem; }
.form-actions { padding-top: 1rem; }
</style>
