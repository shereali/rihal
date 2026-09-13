<template>
  <div class="page-wrapper">
    <!-- Header Row -->
    <div class="page-header-row">
      <div class="header-title-block">
        <span class="eyebrow">অর্থায়ন ও ফান্ড ব্যবস্থাপনা</span>
        <h1>অনুদান তালিকা (Donations Directory)</h1>
        <p class="page-subtitle">সকল দান, সাদাকাহ, যাকাত ও স্পনসরশিপ অনুদানের হিসাব ও রসিদ ব্যবস্থাপনা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/finance/donations/create" class="btn btn-primary">
          <icon name="plus" /> নতুন অনুদান এন্ট্রি
        </NuxtLink>
        <NuxtLink to="/finance/donors" class="btn btn-outline">
          <icon name="users" /> দাতা তালিকা
        </NuxtLink>
        <button class="btn btn-outline" @click="loadDonations">
          <icon name="refresh" /> রিফ্রেশ
        </button>
      </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="stats-grid" v-if="donations.length || !loading">
      <div class="stat-card">
        <div class="stat-icon-wrap blue"><icon name="money" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(totalDonationAmount) }} ৳</span>
          <span class="stat-label">মোট অনুদান সংগৃহীত</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap green"><icon name="check-circle" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ totalCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">মোট অনুদান এন্ট্রি</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap amber"><icon name="users" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ uniqueDonorsCount.toLocaleString('bn-BD') }}</span>
          <span class="stat-label">অনন্য দাতা</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon-wrap purple"><icon name="building" /></div>
        <div class="stat-content">
          <span class="stat-value">{{ formatCurrency(averageDonation) }} ৳</span>
          <span class="stat-label">গড় অনুদান</span>
        </div>
      </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="toolbar card">
      <div class="search-box">
        <icon name="search" class="search-icon" />
        <input v-model="filters.search" placeholder="রেফারেন্স নং বা দাতা খুঁজুন..." @keyup.enter="applyFilters" />
        <button v-if="filters.search" class="clear-search-btn" @click="filters.search = ''; applyFilters()">×</button>
      </div>

      <select v-model="filters.fund_id" class="form-select" @change="applyFilters">
        <option value="">সব ফান্ড (All Funds)</option>
        <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name_bn }}</option>
      </select>

      <select v-model="filters.payment_method" class="form-select" @change="applyFilters">
        <option value="">সব মাধ্যম (Payment Method)</option>
        <option value="নগদ">নগদ (Cash)</option>
        <option value="ব্যাংক">ব্যাংক (Bank)</option>
        <option value="মোবাইল ব্যাংকিং">মোবাইল ব্যাংকিং (bKash/Nagad)</option>
        <option value="চেক">চেক (Cheque)</option>
      </select>

      <button class="btn btn-outline btn-sm" @click="applyFilters">
        ফিল্টার প্রয়োগ
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state card">
      <div class="spinner" />
      <p>অনুদান তালিকা লোড হচ্ছে...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!donations.length" class="empty-state card">
      <div class="empty-icon-wrap"><icon name="money" /></div>
      <h3>কোনো অনুদান রেকর্ড পাওয়া যায়নি</h3>
      <p>নতুন অনুদান বা দান এন্ট্রি করে ফান্ড সংগ্রহ শুরু করুন</p>
      <NuxtLink to="/finance/donations/create" class="btn btn-primary">
        <icon name="plus" /> প্রথম অনুদান এন্ট্রি করুন
      </NuxtLink>
    </div>

    <!-- Data Table -->
    <div v-else class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>আইডি / রেফারেন্স</th>
              <th>দাতার নাম</th>
              <th>ফান্ড</th>
              <th>পরিমাণ (৳)</th>
              <th>পদ্ধতি</th>
              <th>তারিখ</th>
              <th>রসিদ অবস্থা</th>
              <th class="text-right">পদক্ষেপ</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in donations" :key="d.id">
              <td class="font-mono">
                <span class="ref-badge">#{{ d.id }}</span>
                <span v-if="d.transaction_reference" class="sub-ref">{{ d.transaction_reference }}</span>
              </td>
              <td>
                <div class="donor-cell">
                  <span class="donor-name">{{ d.is_anonymous ? 'গোপনীয় দাতা' : (d.donor?.name_bn || d.donor?.name_en || 'অজানা দাতা') }}</span>
                  <span v-if="d.is_anonymous" class="badge-anonymous">গোপনীয়</span>
                </div>
              </td>
              <td>
                <span class="fund-pill">{{ d.fund?.name_bn || 'সাধারণ ফান্ড' }}</span>
              </td>
              <td class="amount-cell">
                <strong>{{ formatCurrency(d.amount) }} ৳</strong>
              </td>
              <td>
                <span class="method-chip">{{ d.payment_method || 'নগদ' }}</span>
              </td>
              <td>{{ formatDate(d.donation_date || d.created_at) }}</td>
              <td>
                <span class="status-pill" :class="d.receipt_generated ? 'status-active' : 'status-pending'">
                  {{ d.receipt_generated ? 'রসিদ তৈরি' : 'রসিদ বাকি' }}
                </span>
              </td>
              <td class="text-right">
                <div class="action-buttons">
                  <NuxtLink :to="`/finance/donations/${d.id}`" class="action-btn" title="বিস্তারিত দেখুন">
                    <icon name="eye" />
                  </NuxtLink>
                  <button class="action-btn text-primary" @click="previewReceipt(d)" title="রসিদ দেখুন">
                    <icon name="file-text" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="pagination && pagination.last_page > 1" class="pagination-footer">
        <div class="pagination-info">
          মোট <span class="highlight">{{ pagination.total?.toLocaleString('bn-BD') }}</span> টি রেকর্ডের মধ্যে 
          পৃষ্ঠা {{ pagination.current_page?.toLocaleString('bn-BD') }} / {{ pagination.last_page?.toLocaleString('bn-BD') }}
        </div>
        <div class="pagination-controls">
          <button
            class="btn btn-outline btn-sm"
            :disabled="filters.page <= 1"
            @click="changePage(filters.page - 1)"
          >
            পূর্ববর্তী
          </button>
          <div class="page-numbers">
            <button
              v-for="p in visiblePages"
              :key="p"
              class="page-num-btn"
              :class="{ active: p === filters.page }"
              @click="changePage(p)"
            >
              {{ p.toLocaleString('bn-BD') }}
            </button>
          </div>
          <button
            class="btn btn-outline btn-sm"
            :disabled="filters.page >= pagination.last_page"
            @click="changePage(filters.page + 1)"
          >
            পরবর্তী
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Receipt Modal -->
    <div v-if="showReceiptModal" class="modal-overlay" @click.self="showReceiptModal = false">
      <div class="modal-card modal-md animate-fade-in">
        <div class="modal-header">
          <div class="modal-title-group">
            <h3>অনুদান রসিদ (Donation Receipt)</h3>
          </div>
          <button class="modal-close-btn" @click="showReceiptModal = false">×</button>
        </div>
        <div class="modal-body receipt-modal-body" id="printable-receipt">
          <div class="receipt-box">
            <div class="receipt-header">
              <h2>দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট</h2>
              <p>অফিসিয়াল অনুদান ও দান রসিদ</p>
              <div class="receipt-no">রসিদ নং: #DON-{{ selectedDonation?.id }}</div>
            </div>
            <hr class="receipt-divider" />
            <div class="receipt-grid">
              <div class="receipt-row">
                <span class="lbl">দাতার নাম:</span>
                <span class="val">{{ selectedDonation?.is_anonymous ? 'গোপনীয় দাতা' : (selectedDonation?.donor?.name_bn || selectedDonation?.donor?.name_en || 'সাধারণ দাতা') }}</span>
              </div>
              <div class="receipt-row">
                <span class="lbl">ফান্ডের নাম:</span>
                <span class="val">{{ selectedDonation?.fund?.name_bn || 'সাধারণ ফান্ড' }}</span>
              </div>
              <div class="receipt-row">
                <span class="lbl">অনুদানের পরিমাণ:</span>
                <span class="val highlight-amount">{{ formatCurrency(selectedDonation?.amount) }} ৳</span>
              </div>
              <div class="receipt-row">
                <span class="lbl">পরিশোধ পদ্ধতি:</span>
                <span class="val">{{ selectedDonation?.payment_method || 'নগদ' }}</span>
              </div>
              <div class="receipt-row">
                <span class="lbl">তারিখ:</span>
                <span class="val">{{ formatDate(selectedDonation?.donation_date || selectedDonation?.created_at) }}</span>
              </div>
              <div class="receipt-row" v-if="selectedDonation?.notes">
                <span class="lbl">মন্তব্য:</span>
                <span class="val">{{ selectedDonation?.notes }}</span>
              </div>
            </div>
            <div class="receipt-footer">
              <p class="gratitude">"আল্লাহ তাআলা আপনার এই নেক দান কবুল করুন। আমিন।"</p>
              <div class="signature-line">
                <span>হিসাবরক্ষক / অনুমোদিত স্বাক্ষর</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" @click="showReceiptModal = false">বন্ধ করুন</button>
          <button type="button" class="btn btn-primary" @click="printReceipt">
            <icon name="printer" /> প্রিন্ট করুন
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()

const donations = ref<any[]>([])
const funds = ref<any[]>([])
const loading = ref(true)
const pagination = ref<any>(null)

const showReceiptModal = ref(false)
const selectedDonation = ref<any>(null)

const filters = reactive({
  search: '',
  fund_id: '',
  payment_method: '',
  page: 1,
  per_page: 15,
})

const totalDonationAmount = computed(() => {
  return donations.value.reduce((sum, d) => sum + (Number(d.amount) || 0), 0)
})

const totalCount = computed(() => pagination.value?.total || donations.value.length)

const uniqueDonorsCount = computed(() => {
  const donorIds = new Set(donations.value.map(d => d.donor_id).filter(Boolean))
  return donorIds.size || donations.value.length
})

const averageDonation = computed(() => {
  if (!donations.value.length) return 0
  return Math.round(totalDonationAmount.value / donations.value.length)
})

const visiblePages = computed(() => {
  if (!pagination.value) return [1]
  const total = pagination.value.last_page || 1
  const cur = filters.page
  const pages: number[] = []
  for (let i = Math.max(1, cur - 2); i <= Math.min(total, cur + 2); i++) {
    pages.push(i)
  }
  return pages
})

async function loadDonations() {
  loading.value = true
  try {
    const q = new URLSearchParams()
    if (filters.search) q.set('search', filters.search)
    if (filters.fund_id) q.set('fund_id', filters.fund_id)
    if (filters.payment_method) q.set('payment_method', filters.payment_method)
    q.set('page', String(filters.page))
    q.set('per_page', String(filters.per_page))

    const [resDonations, resFunds] = await Promise.all([
      api.get(`/finance/donations?${q.toString()}`).catch(() => ({ data: { data: { data: [] } } })),
      api.get('/finance/funds?per_page=100').catch(() => ({ data: { data: [] } }))
    ])

    const dData = resDonations.data?.data
    if (dData && Array.isArray(dData.data)) {
      donations.value = dData.data
      pagination.value = {
        total: dData.total,
        current_page: dData.current_page,
        last_page: dData.last_page,
        per_page: dData.per_page,
      }
    } else if (Array.isArray(dData)) {
      donations.value = dData
      pagination.value = { total: dData.length, current_page: 1, last_page: 1 }
    } else {
      donations.value = []
    }

    const fData = resFunds.data?.data
    funds.value = Array.isArray(fData?.data) ? fData.data : (Array.isArray(fData) ? fData : [])
  } catch (e) {
    console.error('Error loading donations:', e)
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  filters.page = 1
  loadDonations()
}

function changePage(p: number) {
  filters.page = p
  loadDonations()
}

function previewReceipt(donation: any) {
  selectedDonation.value = donation
  showReceiptModal.value = true
}

function printReceipt() {
  window.print()
}

function formatCurrency(val: number) {
  if (!val) return '০'
  return Number(val).toLocaleString('bn-BD')
}

function formatDate(val: string) {
  if (!val) return '-'
  return new Date(val).toLocaleDateString('bn-BD', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

onMounted(loadDonations)
</script>

<style scoped>
.page-wrapper {
  max-width: 1440px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1.5rem;
  margin-bottom: 1.75rem;
  flex-wrap: wrap;
}

.eyebrow {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--color-primary, #0284c7);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.page-header-row h1 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #111827;
  margin: 0.25rem 0;
}

.page-subtitle {
  font-size: 0.95rem;
  color: #6b7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: #ffffff;
  border-radius: 14px;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.stat-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.stat-icon-wrap.blue { background: #e0f2fe; color: #0284c7; }
.stat-icon-wrap.green { background: #dcfce7; color: #16a34a; }
.stat-icon-wrap.purple { background: #f3e8ff; color: #9333ea; }
.stat-icon-wrap.amber { background: #fef3c7; color: #d97706; }

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.35rem;
  font-weight: 700;
  color: #111827;
}

.stat-label {
  font-size: 0.8rem;
  color: #6b7280;
}

/* Toolbar */
.toolbar {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  margin-bottom: 1.25rem;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 220px;
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 0.85rem;
  color: #9ca3af;
}

.search-box input {
  width: 100%;
  padding: 0.55rem 2rem 0.55rem 2.5rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  outline: none;
}

.clear-search-btn {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  font-size: 1.1rem;
  color: #9ca3af;
  cursor: pointer;
}

.form-select {
  padding: 0.55rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  background: #ffffff;
  outline: none;
}

/* Table Card */
.table-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.premium-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
  text-align: left;
}

.premium-table th {
  background: #f9fafb;
  padding: 0.85rem 1.25rem;
  font-weight: 600;
  color: #4b5563;
  border-bottom: 1px solid #e5e7eb;
}

.premium-table td {
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid #f3f4f6;
  color: #1f2937;
  vertical-align: middle;
}

.donor-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.donor-name {
  font-weight: 600;
  color: #111827;
}

.badge-anonymous {
  font-size: 0.7rem;
  background: #f3f4f6;
  color: #6b7280;
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
}

.fund-pill {
  display: inline-block;
  font-size: 0.8rem;
  padding: 0.2rem 0.6rem;
  background: #e0f2fe;
  color: #0369a1;
  border-radius: 9999px;
  font-weight: 500;
}

.method-chip {
  display: inline-block;
  font-size: 0.8rem;
  padding: 0.2rem 0.5rem;
  background: #f3f4f6;
  color: #4b5563;
  border-radius: 6px;
}

.status-pill {
  display: inline-block;
  font-size: 0.75rem;
  padding: 0.2rem 0.6rem;
  border-radius: 9999px;
  font-weight: 600;
}

.status-active {
  background: #dcfce7;
  color: #15803d;
}

.status-pending {
  background: #fef3c7;
  color: #b45309;
}

.action-buttons {
  display: flex;
  gap: 0.4rem;
  justify-content: flex-end;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.15s;
}

.action-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.action-btn.text-primary {
  color: var(--color-primary, #0284c7);
}

/* Pagination */
.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

.page-num-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  background: #ffffff;
  font-size: 0.85rem;
  cursor: pointer;
}

.page-num-btn.active {
  background: var(--color-primary, #0284c7);
  color: #ffffff;
  border-color: var(--color-primary, #0284c7);
}

/* Receipt Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-card.modal-md {
  max-width: 540px;
  width: 100%;
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-close-btn {
  background: none;
  border: none;
  font-size: 1.4rem;
  color: #9ca3af;
  cursor: pointer;
}

.receipt-modal-body {
  padding: 1.5rem;
  background: #f8fafc;
}

.receipt-box {
  background: #ffffff;
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  padding: 1.5rem;
}

.receipt-header {
  text-align: center;
}

.receipt-header h2 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.receipt-header p {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0.25rem 0 0.5rem;
}

.receipt-no {
  font-family: monospace;
  font-size: 0.85rem;
  font-weight: 600;
  color: #0284c7;
}

.receipt-divider {
  border: 0;
  border-top: 1px solid #e2e8f0;
  margin: 1rem 0;
}

.receipt-grid {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.receipt-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
}

.receipt-row .lbl {
  color: #64748b;
}

.receipt-row .val {
  font-weight: 600;
  color: #0f172a;
}

.highlight-amount {
  color: #16a34a !important;
  font-size: 1.05rem;
}

.receipt-footer {
  margin-top: 1.5rem;
  text-align: center;
}

.gratitude {
  font-size: 0.85rem;
  color: #475569;
  font-style: italic;
  margin-bottom: 1.5rem;
}

.signature-line {
  display: inline-block;
  border-top: 1px solid #94a3b8;
  padding-top: 0.35rem;
  font-size: 0.8rem;
  color: #64748b;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
}

@media print {
  body * {
    visibility: hidden;
  }
  #printable-receipt, #printable-receipt * {
    visibility: visible;
  }
  #printable-receipt {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
