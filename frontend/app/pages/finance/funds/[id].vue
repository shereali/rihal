<template>
  <div class="detail-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1 v-if="fund">{{ fund.name_bn }}</h1>
        <p v-else class="text-muted">ফান্ড লোড হচ্ছে...</p>
      </div>
      <div class="header-actions" v-if="fund">
        <span class="badge" :class="fund.is_active ? 'badge-success' : 'badge-secondary'">
          {{ fund.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
        </span>
        <button class="btn btn-primary btn-sm" @click="openEditModal">
          <icon name="pencil" /> তথ্য সম্পাদনা
        </button>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="fund" class="detail-grid">
      <div class="card">
        <h3>ফান্ডের তথ্য</h3>
        <dl class="info-list">
          <div><dt>নাম (বাংলা)</dt><dd>{{ fund.name_bn }}</dd></div>
          <div v-if="fund.name_en"><dt>নাম (ইংরেজি)</dt><dd>{{ fund.name_en }}</dd></div>
          <div><dt>ধরণ</dt><dd>{{ fund.type || 'সাধারণ ফান্ড' }}</dd></div>
          <div><dt>লক্ষ্যমাত্রা</dt><dd>৳{{ fund.target_amount || 0 }}</dd></div>
          <div><dt>সংগ্রহিত</dt><dd>৳{{ fund.collected_amount || 0 }}</dd></div>
          <div><dt>বর্তমান ব্যালেন্স</dt><dd>৳{{ fund.balance || 0 }}</dd></div>
          <div v-if="fund.description_bn"><dt>বিবরণ</dt><dd>{{ fund.description_bn }}</dd></div>
          <div v-if="fund.description"><dt>বিবরণ (ইং)</dt><dd>{{ fund.description }}</dd></div>
          <div v-if="fund.zakat_eligible_balance"><dt>জাকাত যোগ্য ব্যালেন্স</dt><dd>৳{{ fund.zakat_eligible_balance }}</dd></div>
          <div><dt>তৈরির তারিখ</dt><dd>{{ formatDate(fund.created_at) }}</dd></div>
        </dl>
      </div>

      <div class="card">
        <h3>অগ্রগতি</h3>
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
        </div>
        <p class="text-muted">{{ progressPercent }}% সংগ্রহিত (৳{{ fund.collected_amount || 0 }} / ৳{{ fund.target_amount || 0 }})</p>
      </div>
    </div>

    <!-- Edit Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showEdit" class="modal-overlay" @click.self="showEdit = false">
          <div class="modal-card">
            <div class="modal-header">
              <h3>ফান্ড তথ্য সম্পাদনা</h3>
              <button class="modal-close" @click="showEdit = false">×</button>
            </div>
            <form @submit.prevent="saveEdit">
              <div class="modal-body">
                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">ফান্ডের নাম (বাংলা) *</label>
                    <input v-model="editForm.name_bn" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label">ফান্ডের নাম (ইংরেজি)</label>
                    <input v-model="editForm.name_en" class="form-control" />
                  </div>
                </div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">ফান্ডের ধরণ</label>
                    <select v-model="editForm.type" class="form-select">
                      <option value="general">সাধারণ তহবিল (General)</option>
                      <option value="zakat">যাকাত তহবিল (Zakat)</option>
                      <option value="lillah">লিল্লাহ ফান্ড (Lillah)</option>
                      <option value="building">নির্মাণ তহবিল (Building)</option>
                      <option value="orphan">এতিম ফান্ড (Orphan)</option>
                      <option value="other">অন্যান্য (Other)</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">লক্ষ্যমাত্রা (৳)</label>
                    <input v-model.number="editForm.target_amount" type="number" min="0" class="form-control" />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">বিবরণ (বাংলা)</label>
                  <textarea v-model="editForm.description_bn" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                  <label class="form-check-label">
                    <input type="checkbox" v-model="editForm.is_active" /> সক্রিয় ফান্ড হিসেবে সংরক্ষণ করুন
                  </label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-ghost" @click="showEdit = false">বাতিল</button>
                <button type="submit" class="btn btn-primary" :disabled="saving || !editForm.name_bn">
                  <icon v-if="saving" name="loader" />
                  {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'পরিবর্তন সংরক্ষণ করুন' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const route = useRoute()
const api = useApiClient()
const { isAuthenticated } = useAuth()

const fund = ref<any>(null)
const error = ref('')
const showEdit = ref(false)
const saving = ref(false)

const editForm = reactive({
  name_bn: '',
  name_en: '',
  type: 'general',
  target_amount: 0,
  description_bn: '',
  is_active: true,
})

const progressPercent = computed(() => {
  if (!fund.value?.target_amount) return 0
  const pct = ((fund.value.collected_amount || 0) / fund.value.target_amount) * 100
  return Math.min(100, Math.round(pct))
})

const formatDate = (d: string | null | undefined) =>
  d ? new Date(d).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'

function openEditModal() {
  if (!fund.value) return
  const f = fund.value
  editForm.name_bn = f.name_bn || ''
  editForm.name_en = f.name_en || ''
  editForm.type = f.type || 'general'
  editForm.target_amount = f.target_amount ? Number(f.target_amount) : 0
  editForm.description_bn = f.description_bn || f.description || ''
  editForm.is_active = f.is_active ?? true
  showEdit.value = true
}

async function saveEdit() {
  if (!editForm.name_bn.trim()) return
  saving.value = true
  try {
    const res = await api.put(`/finance/funds/${route.params.id}`, editForm)
    fund.value = res.data?.data || { ...fund.value, ...editForm }
    showEdit.value = false
    alert('ফান্ডের তথ্য সফলভাবে আপডেট করা হয়েছে!')
  } catch (err: any) {
    console.error('Update fund failed:', err)
    alert(err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে')
  } finally {
    saving.value = false
  }
}

async function load() {
  error.value = ''
  try {
    const res = await api.get(`/finance/funds/${route.params.id}`)
    fund.value = res.data.data
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ফান্ড লোড করা যায়নি'
  }
}

if (isAuthenticated.value) onMounted(load)
</script>

<style scoped>
.detail-page { max-width: 960px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-actions { display: flex; align-items: center; gap: 0.75rem; }
.header-left h1 { margin: 0.5rem 0 0; font-family: var(--font-bn); font-size: 1.4rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--color-primary); text-decoration: none; font-family: var(--font-bn); }
.detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1.25rem; }
.card h3 { margin: 0 0 1rem; font-family: var(--font-bn); }
.info-list div { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--color-border-light); }
.info-list dt { color: var(--color-text-light); font-family: var(--font-bn); }
.info-list dd { font-weight: 600; margin: 0; }
.progress-bar { height: 12px; background: var(--color-border-light); border-radius: 6px; overflow: hidden; margin-bottom: 0.5rem; }
.progress-fill { height: 100%; background: var(--color-primary); transition: width 0.3s; }
.alert-error { background: #fce4e4; color: var(--color-error); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-family: var(--font-bn); }
.badge { padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-family: var(--font-bn); }
.badge-success { background: #e8f5e9; color: var(--color-success); }
.badge-secondary { background: #eee; color: var(--color-text-light); }
.text-muted { color: var(--color-text-light); font-family: var(--font-bn); }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; }
.modal-card { background: var(--color-bg-card, #fff); border-radius: 12px; width: 100%; max-width: 550px; overflow: hidden; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-muted); }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: 0.75rem; }
.form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.35rem; }
.form-label { font-size: 0.85rem; font-weight: 600; }
.form-control, .form-select { padding: 0.55rem 0.85rem; border: 1px solid var(--color-border); border-radius: 6px; background: var(--color-bg); font-size: 0.9rem; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
</style>
