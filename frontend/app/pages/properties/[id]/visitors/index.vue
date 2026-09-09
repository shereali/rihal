<template>
  <div class="page-wrapper">
    <div class="breadcrumb">
      <NuxtLink :to="`/properties/${propertyId}`" class="breadcrumb-current">সম্পত্তির বিবরণী</NuxtLink>
      <span class="sep">/</span>
      <span class="breadcrumb-current">ভিজিটর</span>
    </div>
    <div class="page-header">
      <h1>সম্পত্তি ভিজিটর</h1>
      <div class="header-actions">
        <button class="btn btn-primary" @click="showForm = true">
          <icon name="plus" /> নতুন ভিজিটর
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>ভিজিটর তথ্য লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!visitors.length" class="empty-state">
      <div class="empty-icon"><icon name="users" /></div>
      <h3>কোনো ভিজিটর নেই</h3>
      <p>এখনো কোনো ভিজিটর রেকর্ড করা হয়নি</p>
    </div>

    <div v-else class="visitors-table">
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>ভিজিটরের নাম</th>
              <th>ফোন</th>
              <th>ভিজিটের উদ্দেশ্য</th>
              <th>আগমনের তারিখ</th>
              <th>অবস্থা</th>
              <th>কর্ম</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(v, idx) in visitors" :key="v.id">
              <td class="text-center">{{ (currentPage - 1) * perPage + idx + 1 }}</td>
              <td class="visitor-name">{{ v.name_bn || v.name_en }}</td>
              <td>{{ v.phone || '-' }}</td>
              <td class="purpose-cell">{{ v.purpose_bn || v.purpose_en || '-' }}</td>
              <td class="text-center">{{ formatDate(v.arrival_date) }}</td>
              <td>
                <span class="status-badge" :class="statusClass(v.status)">
                  {{ statusLabel(v.status) }}
                </span>
              </td>
              <td class="text-center">
                <button class="btn-sm" @click="showVisitorDetail(v)">
                  <icon name="eye" /> দেখুন
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add visitor modal -->
    <div class="modal-overlay" v-if="showForm" @click.self="showForm = false">
      <div class="modal card">
        <div class="modal-header">
          <h2>নতুন ভিজিটর যোগ করুন</h2>
          <button class="close-btn" @click="showForm = false">×</button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label>নাম (বাংলা)</label>
              <input v-model="form.name_bn" class="form-control" placeholder="ভিজিটরের পূর্ণ নাম (বাংলা)" />
            </div>
            <div class="form-group">
              <label>নাম (ইংরেজি)</label>
              <input v-model="form.name_en" class="form-control" placeholder="Visitor's full name" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>ফোন</label>
              <input v-model="form.phone" class="form-control" placeholder="০১৭১২৩৪৫৬৭৮" />
            </div>
            <div class="form-group">
              <label>ইমেইল</label>
              <input v-model="form.email" type="email" class="form-control" placeholder="email@example.com" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>আগমনের তারিখ</label>
              <input v-model="form.arrival_date" type="date" class="form-control" />
            </div>
            <div class="form-group">
              <label>অবস্থা</label>
              <select v-model="form.status" class="form-control">
                <option value="pending">মুলতুবি</option>
                <option value="arrived">আসয়ন</option>
                <option value="departed">যাওয়া</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>উদ্দেশ্য (বাংলা)</label>
            <input v-model="form.purpose_bn" class="form-control" placeholder="ভিজিটের উদ্দেশ্য (বাংলা)" />
          </div>
          <div class="form-group">
            <label>উদ্দেশ্য (ইংরেজি)</label>
            <input v-model="form.purpose_en" class="form-control" placeholder="Purpose of visit" />
          </div>
          <div class="form-group">
            <label>ছবি</label>
            <input v-model="form.photo_url" class="form-control" placeholder="ছবির URL (যদি থাকে)" />
          </div>
          <div class="form-group">
            <label>অতিরিক্ত নোট</label>
            <textarea v-model="form.notes_bn" class="form-control" rows="2" placeholder="অতিরিক্ত তথ্য, নোট..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" @click="saveVisitor" :disabled="saving">
            {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'ভিজিটর যোগ করুন' }}
          </button>
          <button class="btn btn-ghost" @click="showForm = false">বাতিল</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const propertyId = computed(() => route.params.id as string)
const visitors = ref([])
const loading = ref(true)
const showForm = ref(false)
const saving = ref(false)
const currentPage = ref(1)
const perPage = 15

const form = ref({
  name_bn: '',
  name_en: '',
  phone: '',
  email: '',
  arrival_date: '',
  status: 'pending',
  purpose_bn: '',
  purpose_en: '',
  photo_url: '',
  notes_bn: '',
})

onMounted(async () => {
  try {
    const r = await api.get(`/properties/${propertyId.value}/visitors`)
    visitors.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error('Failed to load visitors:', e)
  } finally {
    loading.value = false
  }
})

function statusClass(status) {
  const map = {
    pending: 'pending',
    arrived: 'arrived',
    departed: 'departed',
  }
  return map[status] || 'pending'
}

function statusLabel(status) {
  const map = {
    pending: 'মুলতুবি',
    arrived: 'আসয়ন',
    departed: 'যাওয়া',
  }
  return map[status] || status || '-'
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

function showVisitorDetail(v) {
  // Could open a detail modal — for now just a placeholder
  alert(`ভিজিটর: ${v.name_bn || v.name_en}\nফোন: ${v.phone}\nউদ্দেশ্য: ${v.purpose_bn || v.purpose_en}`)
}

async function saveVisitor() {
  saving.value = true
  try {
    await api.post(`/properties/${propertyId.value}/visitors`, form.value)
    showForm.value = false
    form.value = {
      name_bn: '', name_en: '', phone: '', email: '',
      arrival_date: '', status: 'pending',
      purpose_bn: '', purpose_en: '', photo_url: '', notes_bn: '',
    }
    // Reload
    const r = await api.get(`/properties/${propertyId.value}/visitors`)
    visitors.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error('Failed to save visitor:', e)
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.page-wrapper {
  max-width: 1100px;
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
  font-family: var(--font-bn);
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.breadcrumb .sep {
  color: var(--color-text-muted);
}

.header-actions {
  margin-left: auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.2rem;
}

.page-header h1 {
  font-size: 1.4rem;
  color: var(--color-primary);
  font-family: var(--font-bn);
  margin: 0;
}

.page-header .header-actions {
  display: flex;
  gap: 0.6rem;
}

.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 4rem 0;
}

.empty-state {
  text-align: center;
  padding: 3rem 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.7rem;
}

.empty-icon {
  width: 48px;
  height: 48px;
  color: var(--color-text-muted);
  margin-bottom: 0.5rem;
}

.empty-state h3 {
  font-family: var(--font-bn);
  font-size: 1.05rem;
  color: var(--color-text);
  margin: 0;
}

.empty-state p {
  color: var(--color-text-muted);
  font-family: var(--font-bn);
  margin: 0;
  font-size: 0.82rem;
}

.visitors-table {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
  font-family: var(--font-bn);
}

.data-table thead {
  background: rgba(0, 0, 0, 0.04);
}

.data-table th {
  padding: 0.6rem 0.8rem;
  text-align: left;
  font-size: 0.65rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
  font-weight: 600;
  border-bottom: 1px solid var(--color-border-light);
  white-space: nowrap;
}

.data-table td {
  padding: 0.55rem 0.8rem;
  border-bottom: 1px solid var(--color-border-light);
  color: var(--color-text);
}

.data-table tbody tr:last-child td {
  border-bottom: none;
}

.text-center {
  text-align: center;
}

.visitor-name {
  font-weight: 600;
}

.purpose-cell {
  color: var(--color-text-muted);
}

.status-badge {
  padding: 0.15rem 0.5rem;
  border-radius: 99px;
  font-size: 0.65rem;
  font-weight: 600;
  white-space: nowrap;
  display: inline-block;
  font-family: var(--font-bn);
}

.status-badge.pending {
  background: #fff0e4;
  color: #a05c35;
}

.status-badge.arrived {
  background: #e3f2fa;
  color: #1a5276;
}

.status-badge.departed {
  background: #e6f4ec;
  color: #19724a;
}

.btn-sm {
  padding: 0.3rem 0.6rem;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 600;
  font-family: var(--font-bn);
  background: transparent;
  border: 1px solid var(--color-border);
  color: var(--color-text-muted);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.btn-sm:hover {
  background: var(--color-bg-muted);
  color: var(--color-text);
}

.btn-sm icon {
  width: 12px;
  height: 12px;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 500;
}

.modal {
  width: 100%;
  max-width: 560px;
  background: white;
  border-radius: 15px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.2rem;
  border-bottom: 1px solid var(--color-border-light);
}

.modal-header h2 {
  font-family: var(--font-bn);
  font-size: 1.1rem;
  color: var(--color-primary);
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--color-text-muted);
  cursor: pointer;
  padding: 0;
}

.modal-body {
  padding: 1.2rem;
  max-height: 60vh;
  overflow-y: auto;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.8rem;
  margin-bottom: 0.8rem;
}

.form-group {
  margin-bottom: 0.8rem;
}

.form-group label {
  display: block;
  font-size: 0.72rem;
  color: var(--color-text-muted);
  font-weight: 600;
  margin-bottom: 0.3rem;
  font-family: var(--font-bn);
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.form-control {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 0.85rem;
  font-family: var(--font-bn);
  background: white;
  color: var(--color-text);
  outline: none;
}

.form-control:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px var(--color-primary-100);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  padding: 0.8rem 1.2rem;
  border-top: 1px solid var(--color-border-light);
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.82rem;
  font-family: var(--font-bn);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  border: 1px solid transparent;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-ghost {
  background: transparent;
  color: var(--color-text-muted);
}

.btn-ghost:hover {
  background: var(--color-bg-muted);
}

@media (max-width: 600px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>