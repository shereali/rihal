<template>
  <div class="page-header">
    <div class="breadcrumb">
      <NuxtLink to="/dashboard">Home</NuxtLink>
      <span>/</span>
      <span>HR</span>
    </div>
    <h1>Staff & Employees</h1>
    <p class="subtitle">Manage teaching staff, non-teaching staff, and employees</p>
  </div>

  <div class="CreatePanel" v-if="showForm">
    <button class="close-btn" @click="showForm = false">×</button>
    <h2>Add New Staff Member</h2>
    <div class="form-grid">
      <div class="form-field">
        <label>Full Name (Bangla)</label>
        <input v-model="form.name_bn" type="text" placeholder="নাম (বাংলা)" />
      </div>
      <div class="form-field">
        <label>Full Name (English)</label>
        <input v-model="form.name_en" type="text" placeholder="Full Name" />
      </div>
      <div class="form-field">
        <label>Designation</label>
        <input v-model="form.designation" type="text" placeholder="e.g. Headmaster, Teacher, Accountant" />
      </div>
      <div class="form-field">
        <label>Department</label>
        <select v-model="form.department">
          <option value="">Select Department</option>
          <option value="Academic">Academic</option>
          <option value="Administration">Administration</option>
          <option value="Finance">Finance</option>
          <option value="IT">IT</option>
          <option value="Support">Support</option>
        </select>
      </div>
      <div class="form-field">
        <label>Phone</label>
        <input v-model="form.phone" type="text" placeholder="+880 1712 345678" />
      </div>
      <div class="form-field">
        <label>Email</label>
        <input v-model="form.email" type="email" placeholder="email@example.com" />
      </div>
      <div class="form-field">
        <label>Joining Date</label>
        <input v-model="form.join_date" type="date" />
      </div>
      <div class="form-field">
        <label>Monthly Salary (BDT)</label>
        <input v-model.number="form.salary" type="number" min="0" placeholder="0" />
      </div>
      <div class="form-field">
        <label>Father's Name / Spouse Name</label>
        <input v-model="form.fathers_name_bn" type="text" placeholder="জনাব/স্বামীর নাম" />
      </div>
      <div class="form-field">
        <label>ID / NID Number</label>
        <input v-model="form.nid_number" type="text" placeholder="ID or NID" />
      </div>
      <div class="form-field full">
        <label>Address (Bangla)</label>
        <textarea v-model="form.address_bn" rows="2" placeholder="ঠিকানা (বাংলা)"></textarea>
      </div>
      <div class="form-field full">
        <label>Address (English)</label>
        <textarea v-model="form.address_en" rows="2" placeholder="Address"></textarea>
      </div>
      <div class="form-field full">
        <label>Bio / Description (Bangla)</label>
        <textarea v-model="form.bio_bn" rows="3" placeholder="জীবনী বা বর্ণনা (বাংলা)"></textarea>
      </div>
      <div class="form-field full">
        <label>Bio / Description (English)</label>
        <textarea v-model="form.bio_en" rows="3" placeholder="Bio or description"></textarea>
      </div>
      <div class="form-field">
        <label>Photo URL</label>
        <input v-model="form.photo_url" type="url" placeholder="https://..." />
      </div>
      <div class="form-field">
        <label>Status</label>
        <select v-model="form.is_active">
          <option :value="true">Active</option>
          <option :value="false">Inactive</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn-primary" @click="submitForm" :disabled="submitting">
        {{ submitting ? 'Saving...' : 'Save Staff Member' }}
      </button>
    </div>
  </div>

  <div class="list-header">
    <div class="search-filter">
      <input v-model="search" type="text" placeholder="Search by name, designation, phone, email, department..." />
      <div class="filter-dropdown">
        <select v-model="departmentFilter">
          <option value="">All Departments</option>
          <option value="Academic">Academic</option>
          <option value="Administration">Administration</option>
          <option value="Finance">Finance</option>
          <option value="IT">IT</option>
          <option value="Support">Support</option>
        </select>
      </div>
    </div>
    <button class="btn-primary" @click="showForm = !showForm">
      <icon name="plus" /> New Staff
    </button>
  </div>

  <div v-if="loading" class="loading-state">
    <span class="spinner" />
    <p>Loading staff...</p>
  </div>

  <div v-else-if="!filteredStaff.length" class="empty-state">
    <div class="empty-icon"><icon name="users" /></div>
    <p>No staff members found</p>
    <NuxtLink to="/hr/staff/create" class="btn-primary">Add First Staff</NuxtLink>
  </div>

  <div v-else class="staff-grid">
    <div v-for="(staff, idx) in filteredStaff" :key="staff.id" class="staff-card">
      <div class="staff-avatar">
        <icon name="user-circle" />
      </div>
      <div class="staff-info">
        <h3>{{ staff.name_bn || staff.name_en }}</h3>
        <span class="designation">{{ staff.designation || '—' }}</span>
        <div class="staff-meta">
          <span>{{ staff.phone || '—' }}</span>
          <span v-if="staff.email">{{ staff.email }}</span>
        </div>
        <div class="staff-details">
          <div class="info-item">
            <span class="label">Department</span>
            <span class="value">{{ staff.department || '—' }}</span>
          </div>
          <div class="info-item">
            <span class="label">Joined</span>
            <span class="value">{{ formatDate(staff.join_date) }}</span>
          </div>
          <div v-if="staff.salary" class="info-item salary">
            <span class="label">Salary (BDT)</span>
            <span class="value">{{ staff.salary.toLocaleString('bn-BD') }}</span>
          </div>
          <div v-if="staff.is_active !== undefined" class="info-item">
            <span class="label">Status</span>
            <span class="value status-badge" :class="staff.is_active ? 'active' : 'inactive'">
              {{ staff.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
      </div>
      <div class="staff-actions">
        <NuxtLink :to="`/hr/staff/${staff.id}`" class="btn-secondary">View</NuxtLink>
        <NuxtLink :to="`/hr/staff/${staff.id}/edit`" class="btn-outline">Edit</NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const staff = ref([])
const filteredStaff = ref([])
const loading = ref(true)
const submitting = ref(false)
const showForm = ref(false)
const search = ref('')
const departmentFilter = ref('')

const form = ref({
  name_bn: '',
  name_en: '',
  designation: '',
  department: 'Academic',
  phone: '',
  email: '',
  join_date: new Date().toISOString().split('T')[0],
  salary: 0,
  fathers_name_bn: '',
  nid_number: '',
  address_bn: '',
  address_en: '',
  bio_bn: '',
  bio_en: '',
  photo_url: '',
  is_active: true,
  tenant_id: 1, // will be set from auth or tenant context
})

onMounted(async () => {
  try {
    const r = await api.get('/hr/staff')
    staff.value = r.data?.data?.data || r.data?.data || []
    filterStaff()
  } catch (e) {
    console.error('Failed to load staff:', e)
  } finally {
    loading.value = false
  }
})

function filterStaff() {
  let list = staff.value
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(s =>
      (s.name_bn?.toLowerCase().includes(q) ?? false) ||
      (s.name_en?.toLowerCase().includes(q) ?? false) ||
      (s.designation?.toLowerCase().includes(q) ?? false) ||
      (s.phone?.toLowerCase().includes(q) ?? false) ||
      (s.email?.toLowerCase().includes(q) ?? false) ||
      (s.department?.toLowerCase().includes(q) ?? false)
    )
  }
  if (departmentFilter.value) {
    list = list.filter(s => (s.department || '').toLowerCase() === departmentFilter.value.toLowerCase())
  }
  filteredStaff.value = list
}

watch([search, departmentFilter], filterStaff)

async function submitForm() {
  submitting.value = true
  try {
    await api.post('/hr/staff', form.value)
    form.value = {
      name_bn: '', name_en: '', designation: '', department: 'Academic',
      phone: '', email: '', join_date: new Date().toISOString().split('T')[0],
      salary: 0, fathers_name_bn: '', nid_number: '',
      address_bn: '', address_en: '', bio_bn: '', bio_en: '',
      photo_url: '', is_active: true, tenant_id: 1,
    }
    showForm.value = false
    // refresh
    const r = await api.get('/hr/staff')
    staff.value = r.data?.data?.data || r.data?.data || []
    filterStaff()
  } catch (e) {
    console.error('Failed to create staff:', e)
  } finally {
    submitting.value = false
  }
}

function formatDate(dateStr: string | null | undefined) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('en-US', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}
</script>

<style scoped>
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.8rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  font-size: 0.8rem;
  color: var(--color-text-muted);
  margin-bottom: 0.3rem;
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.breadcrumb a:hover {
  text-decoration: underline;
}

h1 {
  font-size: 1.6rem;
  color: var(--color-primary);
  font-family: var(--font-bn);
  margin: 0 0 0.2rem;
}

.subtitle {
  color: var(--color-text-muted);
  font-family: var(--font-bn);
  font-size: 0.85rem;
  margin: 0;
}

.CreatePanel {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 15px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  box-shadow: var(--shadow-sm);
}

.close-btn {
  position: absolute;
  top: 1rem;
  right: 1.2rem;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--color-text-muted);
  line-height: 1;
}

.close-btn:hover {
  color: var(--color-text);
}

.CreatePanel h2 {
  font-family: var(--font-bn);
  font-size: 1.1rem;
  color: var(--color-primary);
  margin: 0 0 1rem;
  padding-right: 2rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.8rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.form-field.full {
  grid-column: 1 / -1;
}

.form-field label {
  font-size: 0.72rem;
  font-weight: 600;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.02em;
  font-family: var(--font-bn);
}

.form-field input, .form-field select, .form-field textarea {
  padding: 0.55rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 0.85rem;
  font-family: var(--font-bn);
  background: var(--color-bg);
  color: var(--color-text);
  outline: none;
  transition: border-color 0.15s;
}

.form-field input:focus, .form-field select:focus, .form-field textarea:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px var(--color-primary-100);
}

.form-field select {
  cursor: pointer;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--color-border-light);
}

.btn-primary {
  padding: 0.55rem 1.2rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.82rem;
  cursor: pointer;
  font-family: var(--font-bn);
  transition: opacity 0.15s;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.search-filter {
  display: flex;
  gap: 0.5rem;
  flex: 1;
  max-width: 450px;
}

.search-filter input {
  flex: 1;
  padding: 0.5rem 0.7rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-family: var(--font-bn);
  font-size: 0.85rem;
  outline: none;
}

.search-filter input:focus {
  border-color: var(--color-primary);
}

.filter-dropdown select {
  padding: 0.5rem 0.7rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-family: var(--font-bn);
  font-size: 0.85rem;
  background: white;
  outline: none;
}

.loading-state, .empty-state {
  text-align: center;
  padding: 4rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.8rem;
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--color-primary-100);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  width: 48px;
  height: 48px;
  color: var(--color-text-muted);
}

.empty-state p {
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.staff-grid {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.staff-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.9rem 1.2rem;
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 12px;
  transition: box-shadow 0.15s, transform 0.15s;
}

.staff-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-1px);
}

.staff-avatar {
  width: 48px;
  height: 48px;
  background: var(--color-primary-100);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-primary);
  flex-shrink: 0;
}

.staff-avatar icon {
  width: 24px;
  height: 24px;
}

.staff-info {
  flex: 1;
  min-width: 0;
}

.staff-info h3 {
  font-family: var(--font-bn);
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--color-text);
  margin: 0 0 0.1rem;
}

.designation {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.staff-meta {
  display: flex;
  gap: 0.3rem;
  font-size: 0.72rem;
  color: var(--color-text-muted);
  margin-bottom: 0.4rem;
  font-family: var(--font-bn);
}

.staff-details {
  display: flex;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.info-item .label {
  font-size: 0.65rem;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.info-item .value {
  font-size: 0.78rem;
  color: var(--color-text);
  font-family: var(--font-bn);
}

.info-item.salary .value {
  color: var(--color-primary);
  font-weight: 600;
}

.status-badge {
  padding: 0.1rem 0.35rem;
  border-radius: 99px;
  font-size: 0.6rem;
  font-weight: 600;
}

.status-badge.active {
  background: #e6f4ec;
  color: #19724a;
}

.status-badge.inactive {
  background: #fde8e8;
  color: #a03030;
}

.staff-actions {
  flex-shrink: 0;
  display: flex;
  gap: 0.4rem;
}

.btn-secondary, .btn-outline {
  padding: 0.4rem 0.7rem;
  border-radius: 6px;
  font-size: 0.72rem;
  font-family: var(--font-bn);
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: opacity 0.15s;
}

.btn-secondary {
  background: var(--color-primary);
  color: white;
}

.btn-secondary:hover {
  opacity: 0.9;
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--color-border);
  color: var(--color-text);
}

.btn-outline:hover {
  background: var(--color-bg-muted);
}
</style>