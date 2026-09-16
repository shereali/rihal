<template>
  <div class="manage-page slide-up-fade">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">অ্যাডমিন সেটআপ</span>
        <h1>একাডেমিক সেটআপ</h1>
        <p class="subtitle">শ্রেণি, সেকশন ও বিষয় তৈরি ও পরিচালনা করুন</p>
      </div>
    </div>

    <!-- Tab navigation -->
    <div class="tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.value" 
        :class="{ active: active === tab.value }" 
        @click="active = tab.value"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="manage-layout">
      <!-- Form card -->
      <form class="card form-card" @submit.prevent="create">
        <h2>{{ currentLabel }} যোগ করুন</h2>
        <div v-if="error" class="alert alert-error">{{ error }}</div>

        <div class="form-group">
          <label>বাংলা নাম *</label>
          <input v-model="form.name_bn" class="form-control" required placeholder="বাংলা নাম লিখুন" />
        </div>

        <div class="form-group">
          <label>ইংরেজি নাম</label>
          <input v-model="form.name_en" class="form-control" placeholder="ইংরেজি নাম লিখুন" />
        </div>

        <div v-if="active === 'classes'" class="form-group">
          <label>গ্রেড লেভেল</label>
          <input v-model.number="form.grade_level" type="number" class="form-control" placeholder="0" />
        </div>

        <div v-if="active === 'classes'" class="form-group">
          <label>ধরন</label>
          <input v-model="form.class_type" class="form-control" placeholder="regular" />
        </div>

        <div v-if="active === 'sections'" class="form-group">
          <label>শ্রেণি *</label>
          <select v-model="form.class_id" class="form-control" required>
            <option value="">শ্রেণি নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn }}</option>
          </select>
        </div>

        <div v-if="active === 'subjects'" class="form-group">
          <label>কোড</label>
          <input v-model="form.code" class="form-control" placeholder="বিষয় কোড" />
        </div>

        <div v-if="active === 'subjects'" class="form-group">
          <label>বিষয়ের ধরন</label>
          <input v-model="form.subject_type" class="form-control" placeholder="regular" />
        </div>

        <button class="btn btn-primary btn-block mt-4" :disabled="saving">
          <Icon v-if="saving" name="mdi:loading" class="animate-spin mr-1" />
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'যোগ করুন' }}
        </button>
      </form>

      <!-- List card -->
      <section class="card list-card">
        <div class="list-heading">
          <div>
            <h2>{{ currentLabel }} তালিকা</h2>
            <p>{{ items.length }}টি রেকর্ড পাওয়া গেছে</p>
          </div>
          <button class="btn btn-outline btn-sm" @click="load" :disabled="loading">
            <Icon name="mdi:refresh" :class="{ 'animate-spin': loading }" /> রিফ্রেশ
          </button>
        </div>

        <div v-if="loading" class="loading-state">
          <Icon name="mdi:loading" class="animate-spin" size="32" />
          <span>লোড হচ্ছে...</span>
        </div>

        <div v-else class="managed-list">
          <div v-for="item in items" :key="item.id" class="managed-item">
            <div class="item-info">
              <b>{{ item.name_bn }}</b>
              <small>{{ item.name_en || item.class_type || item.subject_type || '' }}</small>
            </div>
            <span class="item-meta">
              {{ active === 'sections' ? className(item.class_id) : active === 'subjects' ? (item.code || 'কোড নেই') : `গ্রেড ${item.grade_level || '—'}` }}
            </span>
            <button class="delete-btn" title="মুছে ফেলুন" @click="confirmDelete(item)">
              <Icon name="mdi:delete-outline" />
            </button>
          </div>

          <div v-if="!items.length" class="empty-inline">
            <Icon name="mdi:playlist-remove" size="40" style="color: var(--color-border);" />
            <p class="mt-2">কোনো {{ currentLabel }} পাওয়া যায়নি</p>
          </div>
        </div>
      </section>
    </div>

    <!-- In-App Delete Confirmation Modal -->
    <ClientOnly>
      <Teleport to="body">
        <div v-if="showDeleteModal && deleteTarget" class="modal-overlay" @click.self="showDeleteModal = false">
          <div class="modal-card">
            <div class="modal-header">
              <h3>{{ currentLabel }} মুছে ফেলা নিশ্চিতকরণ</h3>
              <button class="modal-close-btn" @click="showDeleteModal = false">
                <Icon name="mdi:close" />
              </button>
            </div>
            <div class="modal-body">
              <p>
                আপনি কি নিশ্চিত যে <strong>"{{ deleteTarget.name_bn }}"</strong> {{ currentLabel }}টি মুছে ফেলতে চান?
                এই ক্রিয়াটি পূর্বাবস্থায় ফিরিয়ে আনা যাবে না।
              </p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="showDeleteModal = false" :disabled="deleting">
                বাতিল
              </button>
              <button class="btn btn-danger" @click="executeDelete" :disabled="deleting">
                <Icon v-if="deleting" name="mdi:loading" class="animate-spin mr-1" />
                মুছে ফেলুন
              </button>
            </div>
          </div>
        </div>
      </Teleport>
    </ClientOnly>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const tabs = [
  { value: 'classes', label: 'শ্রেণি' },
  { value: 'sections', label: 'সেকশন' },
  { value: 'subjects', label: 'বিষয়' }
]
const active = ref('classes')
const items = ref<any[]>([])
const classes = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const error = ref('')

// In-app delete modal state
const showDeleteModal = ref(false)
const deleteTarget = ref<any>(null)
const deleting = ref(false)

const form = reactive<any>({
  name_bn: '',
  name_en: '',
  grade_level: 0,
  class_type: 'regular',
  class_id: '',
  code: '',
  subject_type: 'regular'
})

const currentLabel = computed(() => tabs.find(t => t.value === active.value)?.label || 'রেকর্ড')
const endpoint = computed(() => `/academic/${active.value}`)

async function load() {
  loading.value = true
  try {
    const r = await api.get(endpoint.value)
    items.value = r.data?.data || []
    if (active.value === 'classes') {
      classes.value = items.value
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function create() {
  saving.value = true
  error.value = ''
  try {
    const payload: any = {
      name_bn: form.name_bn,
      name_en: form.name_en
    }
    if (active.value === 'classes') {
      Object.assign(payload, {
        grade_level: form.grade_level,
        class_type: form.class_type
      })
    }
    if (active.value === 'sections') {
      payload.class_id = form.class_id || null
    }
    if (active.value === 'subjects') {
      Object.assign(payload, {
        code: form.code,
        subject_type: form.subject_type
      })
    }
    await api.post(endpoint.value, payload)
    form.name_bn = ''
    form.name_en = ''
    form.code = ''
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'রেকর্ড তৈরি করা যায়নি'
  } finally {
    saving.value = false
  }
}

function confirmDelete(item: any) {
  deleteTarget.value = item
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`${endpoint.value}/${deleteTarget.value.id}`)
    showDeleteModal.value = false
    deleteTarget.value = null
    await load()
  } catch (e) {
    console.error('Delete failed:', e)
  } finally {
    deleting.value = false
  }
}

function className(id: number) {
  return classes.value.find(c => c.id === id)?.name_bn || 'শ্রেণি নেই'
}

watch(active, () => {
  error.value = ''
  load()
})

onMounted(async () => {
  await load()
  const r = await api.get('/academic/classes').catch(() => ({ data: { data: [] } }))
  classes.value = r.data?.data || []
})
</script>

<style scoped>
.manage-page {
  max-width: 1300px;
  margin: 0 auto;
  padding: 1.5rem;
}
.page-header-row {
  margin-bottom: 1.2rem;
}
.eyebrow {
  color: var(--color-primary);
  font: 600 0.78rem var(--font-bn);
}
.page-header-row h1 {
  margin: 0.25rem 0;
  color: var(--color-primary);
  font: 700 1.65rem var(--font-bn);
}
.subtitle {
  color: var(--color-text-muted);
  font: 0.88rem var(--font-bn);
  margin: 0;
}
.tabs {
  display: flex;
  gap: 0.35rem;
  width: max-content;
  margin-bottom: 1.2rem;
  padding: 0.3rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md, 12px);
}
.tabs button {
  padding: 0.55rem 1.25rem;
  border: 0;
  border-radius: var(--radius-sm, 8px);
  background: transparent;
  color: var(--color-text-muted);
  font: 600 0.85rem var(--font-bn);
  cursor: pointer;
  transition: all 0.2s;
}
.tabs button.active {
  color: #fff;
  background: var(--color-primary);
  box-shadow: 0 2px 8px rgba(20, 80, 50, 0.2);
}
.manage-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 1.25rem;
}
.card {
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md, 14px);
  box-shadow: var(--shadow-sm);
}
.form-card {
  padding: 1.25rem;
  height: max-content;
}
.form-card h2, .list-heading h2 {
  font: 700 1.1rem var(--font-bn);
  margin: 0;
}
.form-group {
  margin-top: 1rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.35rem;
  font: 600 0.8rem var(--font-bn);
  color: var(--color-text);
}
.form-control {
  width: 100%;
  padding: 0.55rem 0.85rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.9rem;
}
.list-card {
  min-height: 350px;
  padding: 1.25rem;
}
.list-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 0.9rem;
  border-bottom: 1px solid var(--color-border-light);
}
.list-heading p {
  margin-top: 0.2rem;
  color: var(--color-text-muted);
  font: 0.75rem var(--font-bn);
}
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 3rem 0;
  color: var(--color-text-muted);
}
.managed-list {
  display: grid;
}
.managed-item {
  display: grid;
  grid-template-columns: 1fr auto auto;
  align-items: center;
  gap: 0.85rem;
  padding: 0.85rem 0.35rem;
  border-bottom: 1px solid var(--color-border-light);
}
.managed-item:hover {
  background: var(--color-bg-subtle, #fbfcfd);
}
.item-info b {
  display: block;
  font: 600 0.9rem var(--font-bn);
  color: var(--color-text);
}
.item-info small {
  display: block;
  margin-top: 0.15rem;
  color: var(--color-text-muted);
  font: 0.72rem var(--font-bn);
}
.item-meta {
  color: var(--color-text-muted);
  font: 500 0.8rem var(--font-bn);
  background: var(--color-bg-subtle);
  padding: 0.25rem 0.6rem;
  border-radius: var(--radius-sm);
}
.delete-btn {
  display: grid;
  place-items: center;
  width: 32px;
  height: 32px;
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: var(--radius-sm);
  color: var(--color-error, #dc2626);
  background: rgba(239, 68, 68, 0.08);
  cursor: pointer;
  transition: all 0.2s;
}
.delete-btn:hover {
  background: rgba(239, 68, 68, 0.18);
}
.empty-inline {
  padding: 3.5rem 1rem;
  text-align: center;
  color: var(--color-text-muted);
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: grid;
  place-items: center;
  z-index: 1000;
  padding: 1rem;
}
.modal-card {
  background: var(--color-bg-card, #fff);
  border-radius: var(--radius-md, 12px);
  border: 1px solid var(--color-border);
  width: 100%;
  max-width: 440px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
  overflow: hidden;
  animation: modalPop 0.2s ease-out;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.1rem 1.4rem;
  border-bottom: 1px solid var(--color-border-light);
}
.modal-header h3 {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}
.modal-close-btn {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: var(--color-text-muted);
}
.modal-body {
  padding: 1.4rem;
  font-size: 0.95rem;
  line-height: 1.5;
  color: var(--color-text);
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.4rem;
  background: var(--color-bg-subtle, #f8fafc);
  border-top: 1px solid var(--color-border-light);
}

@media(max-width: 800px) {
  .manage-layout {
    grid-template-columns: 1fr;
  }
}
@media(max-width: 500px) {
  .tabs {
    width: 100%;
  }
  .tabs button {
    flex: 1;
  }
}
</style>
