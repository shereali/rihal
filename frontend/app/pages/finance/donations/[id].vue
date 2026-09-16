<template>
  <div class="detail-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1>দান #{{ route.params.id }}</h1>
      </div>
      <div class="header-actions" v-if="donation">
        <span class="badge" :class="donation.receipt_generated ? 'badge-success' : 'badge-secondary'">
          {{ donation.receipt_generated ? 'রসিদ তৈরি' : 'রসিদ বাকি' }}
        </span>
        <button class="btn btn-primary btn-sm" @click="openEditModal">
          <icon name="pencil" /> তথ্য সম্পাদনা
        </button>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="donation" class="detail-grid">
      <div class="card">
        <h3>দানের বিবরণ</h3>
        <dl class="info-list">
          <div><dt>দাতা</dt><dd>{{ donation.donor?.name_bn || donation.donor?.name_en || 'অজ্ঞাত/সাধারণ' }}</dd></div>
          <div><dt>ফান্ড</dt><dd>{{ donation.fund?.name_bn || '-' }}</dd></div>
          <div><dt>পরিমাণ</dt><dd>৳{{ donation.amount }}</dd></div>
          <div><dt>পদ্ধতি</dt><dd>{{ donation.payment_method || donation.donation_type || 'নগদ' }}</dd></div>
          <div><dt>তারিখ</dt><dd>{{ formatDate(donation.donation_date) }}</dd></div>
          <div><dt>রসিদ নম্বর</dt><dd>{{ donation.receipt_number || '—' }}</dd></div>
          <div><dt>গোপনীয় দান</dt><dd>{{ donation.is_anonymous ? 'হ্যাঁ' : 'না' }}</dd></div>
          <div v-if="donation.notes"><dt>মন্তব্য</dt><dd>{{ donation.notes }}</dd></div>
          <div><dt>তৈরির তারিখ</dt><dd>{{ formatDate(donation.created_at) }}</dd></div>
        </dl>
      </div>
    </div>

    <!-- Edit Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showEdit" class="modal-overlay" @click.self="showEdit = false">
          <div class="modal-card">
            <div class="modal-header">
              <h3>দানের তথ্য সম্পাদনা</h3>
              <button class="modal-close" @click="showEdit = false">×</button>
            </div>
            <form @submit.prevent="saveEdit">
              <div class="modal-body">
                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">পরিমাণ (৳) *</label>
                    <input v-model.number="editForm.amount" type="number" step="0.01" min="0" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label">দানের তারিখ</label>
                    <input v-model="editForm.donation_date" type="date" class="form-control" />
                  </div>
                </div>

                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">পেমেন্ট পদ্ধতি</label>
                    <select v-model="editForm.payment_method" class="form-select">
                      <option value="নগদ">নগদ</option>
                      <option value="ব্যাংক">ব্যাংক</option>
                      <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং</option>
                      <option value="চেক">চেক</option>
                      <option value="অন্যান্য">অন্যান্য</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">রসিদ নম্বর</label>
                    <input v-model="editForm.receipt_number" type="text" class="form-control" />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">মন্তব্য / নোট</label>
                  <textarea v-model="editForm.notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                  <label class="form-check-label">
                    <input type="checkbox" v-model="editForm.is_anonymous" /> গোপনীয় দান হিসেবে চিহ্নিত করুন
                  </label>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-ghost" @click="showEdit = false">বাতিল</button>
                <button type="submit" class="btn btn-primary" :disabled="saving || !editForm.amount">
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
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'

const route = useRoute()
const api = useApiClient()
const { isAuthenticated } = useAuth()

const donation = ref<any>(null)
const error = ref('')
const showEdit = ref(false)
const saving = ref(false)

const editForm = reactive({
  amount: 0,
  donation_date: '',
  payment_method: 'নগদ',
  receipt_number: '',
  notes: '',
  is_anonymous: false,
})

const formatDate = (d: string | null | undefined) =>
  d ? new Date(d).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'

function openEditModal() {
  if (!donation.value) return
  const d = donation.value
  editForm.amount = d.amount ? Number(d.amount) : 0
  editForm.donation_date = d.donation_date ? String(d.donation_date).slice(0, 10) : ''
  editForm.payment_method = d.payment_method || d.donation_type || 'নগদ'
  editForm.receipt_number = d.receipt_number || ''
  editForm.notes = d.notes || ''
  editForm.is_anonymous = Boolean(d.is_anonymous)
  showEdit.value = true
}

async function saveEdit() {
  if (!editForm.amount) return
  saving.value = true
  try {
    const res = await api.put(`/finance/donations/${route.params.id}`, editForm)
    donation.value = res.data?.data || { ...donation.value, ...editForm }
    showEdit.value = false
    alert('দানের তথ্য সফলভাবে আপডেট করা হয়েছে!')
  } catch (err: any) {
    console.error('Update donation failed:', err)
    alert(err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে')
  } finally {
    saving.value = false
  }
}

async function load() {
  error.value = ''
  try {
    const res = await api.get(`/finance/donations/${route.params.id}`)
    donation.value = res.data.data
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'দান লোড করা যায়নি'
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
.detail-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1.25rem; }
.card h3 { margin: 0 0 1rem; font-family: var(--font-bn); }
.info-list div { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--color-border-light); }
.info-list dt { color: var(--color-text-light); font-family: var(--font-bn); }
.info-list dd { font-weight: 600; margin: 0; }
.alert-error { background: #fce4e4; color: var(--color-error); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-family: var(--font-bn); }
.badge { padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.85rem; font-family: var(--font-bn); }
.badge-success { background: #e8f5e9; color: var(--color-success); }
.badge-secondary { background: #eee; color: var(--color-text-light); }

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
