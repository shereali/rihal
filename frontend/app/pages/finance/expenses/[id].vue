<template>
  <div class="detail-page">
    <div class="page-header">
      <div class="header-left">
        <NuxtLink to="/finance" class="back-link"><icon name="arrow-left" /> ফিরে যান</NuxtLink>
        <h1 v-if="expense">{{ expense.description_bn }}</h1>
        <p v-else class="text-muted">ব্যয় লোড হচ্ছে...</p>
      </div>
      <div class="header-actions" v-if="expense">
        <span class="badge" :class="expense.is_paid ? 'badge-success' : 'badge-warning'">
          {{ expense.is_paid ? 'পরিশোধিত' : 'অপরিশোধিত' }}
        </span>
        <button class="btn btn-primary btn-sm" @click="openEditModal">
          <icon name="pencil" /> তথ্য সম্পাদনা
        </button>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div v-if="expense" class="detail-grid">
      <div class="card">
        <h3>ব্যয়ের বিবরণ</h3>
        <dl class="info-list">
          <div><dt>বিষয়</dt><dd>{{ expense.description_bn }}</dd></div>
          <div v-if="expense.description_en"><dt>বিষয় (ইং)</dt><dd>{{ expense.description_en }}</dd></div>
          <div><dt>পরিমাণ</dt><dd>৳{{ expense.amount }}</dd></div>
          <div><dt>তারিখ</dt><dd>{{ formatDate(expense.transaction_date) }}</dd></div>
          <div><dt>পদ্ধতি</dt><dd>{{ expense.payment_method || 'নগদ' }}</dd></div>
          <div><dt>প্রদাতা / ভেন্ডর</dt><dd>{{ expense.vendor?.name_bn || expense.payee_name || '-' }}</dd></div>
          <div><dt>ফান্ড</dt><dd>{{ expense.fund?.name_bn || '-' }}</dd></div>
          <div><dt>অনুমোদিত</dt><dd>{{ expense.is_approved ? 'হ্যাঁ' : 'না' }}</dd></div>
          <div><dt>পরিশোধিত</dt><dd>{{ expense.is_paid ? 'হ্যাঁ' : 'না' }}</dd></div>
          <div v-if="expense.notes"><dt>মন্তব্য</dt><dd>{{ expense.notes }}</dd></div>
          <div><dt>তৈরির তারিখ</dt><dd>{{ formatDate(expense.created_at) }}</dd></div>
        </dl>
      </div>
    </div>

    <!-- Edit Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showEdit" class="modal-overlay" @click.self="showEdit = false">
          <div class="modal-card">
            <div class="modal-header">
              <h3>ব্যয় সম্পাদনা</h3>
              <button class="modal-close" @click="showEdit = false">×</button>
            </div>
            <form @submit.prevent="saveEdit">
              <div class="modal-body">
                <div class="form-group">
                  <label class="form-label">বিবরণ / বিষয় (বাংলায়) *</label>
                  <input v-model="editForm.description_bn" class="form-control" required />
                </div>
                <div class="form-row-2">
                  <div class="form-group">
                    <label class="form-label">পরিমাণ (৳) *</label>
                    <input v-model.number="editForm.amount" type="number" step="0.01" min="0" class="form-control" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label">তারিখ</label>
                    <input v-model="editForm.transaction_date" type="date" class="form-control" />
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
                    <label class="form-label">পরিশোধ অবস্থা</label>
                    <select v-model="editForm.is_paid" class="form-select">
                      <option :value="true">পরিশোধিত</option>
                      <option :value="false">অপরিশোধিত</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">মন্তব্য / নোট</label>
                  <textarea v-model="editForm.notes" class="form-control" rows="2"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-ghost" @click="showEdit = false">বাতিল</button>
                <button type="submit" class="btn btn-primary" :disabled="saving || !editForm.description_bn">
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

const expense = ref<any>(null)
const error = ref('')
const showEdit = ref(false)
const saving = ref(false)

const editForm = reactive({
  description_bn: '',
  description_en: '',
  amount: 0,
  transaction_date: '',
  payment_method: 'নগদ',
  is_paid: true,
  notes: '',
})

const formatDate = (d: string | null | undefined) =>
  d ? new Date(d).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'

function openEditModal() {
  if (!expense.value) return
  const e = expense.value
  editForm.description_bn = e.description_bn || ''
  editForm.description_en = e.description_en || ''
  editForm.amount = e.amount ? Number(e.amount) : 0
  editForm.transaction_date = e.transaction_date ? String(e.transaction_date).slice(0, 10) : ''
  editForm.payment_method = e.payment_method || 'নগদ'
  editForm.is_paid = Boolean(e.is_paid)
  editForm.notes = e.notes || ''
  showEdit.value = true
}

async function saveEdit() {
  if (!editForm.description_bn.trim()) return
  saving.value = true
  try {
    const res = await api.put(`/finance/expenses/${route.params.id}`, editForm)
    expense.value = res.data?.data || { ...expense.value, ...editForm }
    showEdit.value = false
    alert('ব্যয়ের তথ্য সফলভাবে আপডেট করা হয়েছে!')
  } catch (err: any) {
    console.error('Update expense failed:', err)
    alert(err?.response?.data?.message || 'সংরক্ষণে ত্রুটি হয়েছে')
  } finally {
    saving.value = false
  }
}

async function load() {
  error.value = ''
  try {
    const res = await api.get(`/finance/expenses/${route.params.id}`)
    expense.value = res.data.data
  } catch (e: any) {
    error.value = e?.response?.data?.message ?? 'ব্যয় লোড করা যায়নি'
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
.badge-warning { background: #fff3e0; color: #e65100; }
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
