<template>
  <div class="routine-page slide-up-fade">
    <div class="page-header-row">
      <div>
        <span class="eyebrow">একাডেমিক সময়সূচি</span>
        <h1>ক্লাস রুটিন</h1>
        <p class="subtitle">সাপ্তাহিক ক্লাস সময়সূচি তৈরি ও পরিচালনা করুন</p>
      </div>
      <button class="btn btn-primary" @click="showForm = !showForm">
        <Icon :name="showForm ? 'mdi:close' : 'mdi:plus'" /> {{ showForm ? 'ফর্ম বন্ধ করুন' : 'নতুন ক্লাস' }}
      </button>
    </div>

    <!-- Day tabs -->
    <div class="day-tabs">
      <button 
        v-for="day in days" 
        :key="day" 
        :class="{ active: activeDay === day }" 
        @click="activeDay = day"
      >
        {{ day }}
      </button>
    </div>

    <!-- Create form panel -->
    <form v-if="showForm" class="create-panel card" @submit.prevent="create">
      <div class="form-heading">
        <h2>{{ activeDay }}-এর জন্য নতুন ক্লাস</h2>
        <button type="button" class="close-btn" @click="showForm = false">×</button>
      </div>

      <div v-if="error" class="alert alert-error">{{ error }}</div>

      <div class="form-grid">
        <div class="form-group">
          <label>বার *</label>
          <select v-model="form.day_of_week" class="form-control" required>
            <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>শ্রেণি</label>
          <select v-model="form.class_id" class="form-control">
            <option value="">নির্বাচন করুন</option>
            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name_bn }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>বিষয়</label>
          <select v-model="form.subject_id" class="form-control">
            <option value="">নির্বাচন করুন</option>
            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name_bn }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>শুরুর সময় *</label>
          <input v-model="form.start_time" type="time" class="form-control" required />
        </div>

        <div class="form-group">
          <label>শেষের সময় *</label>
          <input v-model="form.end_time" type="time" class="form-control" required />
        </div>
      </div>

      <div class="form-actions">
        <button class="btn btn-primary" :disabled="saving">
          <Icon v-if="saving" name="mdi:loading" class="animate-spin mr-1" />
          {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'রুটিনে যোগ করুন' }}
        </button>
        <button type="button" class="btn btn-ghost" @click="showForm = false">
          বাতিল
        </button>
      </div>
    </form>

    <!-- Schedule list -->
    <div v-if="loading" class="loading-state">
      <Icon name="mdi:loading" class="animate-spin" size="36" />
      <span>রুটিন লোড হচ্ছে...</span>
    </div>

    <div v-else class="schedule">
      <div v-for="slot in filtered" :key="slot.id" class="slot-card">
        <div class="time">
          <b>{{ time(slot.start_time) }}</b>
          <span>{{ time(slot.end_time) }}</span>
        </div>
        <div class="slot-line" />
        <div class="slot-body">
          <span class="subject-badge">
            <Icon name="mdi:book-open-page-variant-outline" />
          </span>
          <div>
            <h3>{{ slot.subject?.name_bn || 'বিষয় নির্ধারিত নয়' }}</h3>
            <p>{{ slot.class?.name_bn || 'শ্রেণি নির্ধারিত নয়' }} · {{ slot.teacher?.name_bn || 'শিক্ষক নির্ধারিত নয়' }}</p>
          </div>
        </div>
        <button class="delete-btn" title="মুছে ফেলুন" @click="confirmDelete(slot)">
          <Icon name="mdi:delete-outline" />
        </button>
      </div>

      <div v-if="!filtered.length" class="empty-card">
        <div class="empty-icon">
          <Icon name="mdi:calendar-blank-outline" />
        </div>
        <h3>{{ activeDay }}-এর কোনো ক্লাস নেই</h3>
        <p>নতুন ক্লাস যোগ করে সাপ্তাহিক রুটিন সাজান</p>
      </div>
    </div>

    <!-- In-App Delete Confirmation Modal -->
    <div v-if="showDeleteModal && deleteTarget" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>ক্লাস রুটিন মুছে ফেলা</h3>
          <button class="modal-close-btn" @click="showDeleteModal = false">
            <Icon name="mdi:close" />
          </button>
        </div>
        <div class="modal-body">
          <p>
            আপনি কি নিশ্চিত যে <strong>{{ activeDay }}</strong>-এর 
            <strong>"{{ deleteTarget.subject?.name_bn || 'এই ক্লাস' }}"</strong> 
            ({{ time(deleteTarget.start_time) }} - {{ time(deleteTarget.end_time) }}) রুটিন থেকে সরাতে চান?
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
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const days = ['শনিবার', 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার']
const activeDay = ref('শনিবার')
const rows = ref<any[]>([])
const classes = ref<any[]>([])
const subjects = ref<any[]>([])
const loading = ref(true)
const saving = ref(false)
const showForm = ref(false)
const error = ref('')

// In-app delete modal state
const showDeleteModal = ref(false)
const deleteTarget = ref<any>(null)
const deleting = ref(false)

const form = reactive<any>({
  day_of_week: 'শনিবার',
  class_id: '',
  subject_id: '',
  start_time: '09:00',
  end_time: '10:00'
})

const filtered = computed(() => rows.value.filter(r => r.day_of_week === activeDay.value))

async function load() {
  loading.value = true
  try {
    const [r, c, s] = await Promise.all([
      api.get('/timetable').catch(() => ({ data: { data: [] } })),
      api.get('/academic/classes').catch(() => ({ data: { data: [] } })),
      api.get('/academic/subjects').catch(() => ({ data: { data: [] } }))
    ])
    rows.value = r.data?.data || []
    classes.value = c.data?.data || []
    subjects.value = s.data?.data || []
  } catch (e) {
    console.error('Error loading timetable:', e)
  } finally {
    loading.value = false
  }
}

async function create() {
  saving.value = true
  error.value = ''
  try {
    await api.post('/timetable', {
      ...form,
      class_id: form.class_id || null,
      subject_id: form.subject_id || null
    })
    showForm.value = false
    await load()
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'রুটিন তৈরি করা যায়নি'
  } finally {
    saving.value = false
  }
}

function confirmDelete(slot: any) {
  deleteTarget.value = slot
  showDeleteModal.value = true
}

async function executeDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await api.delete(`/timetable/${deleteTarget.value.id}`)
    showDeleteModal.value = false
    deleteTarget.value = null
    await load()
  } catch (e) {
    console.error('Error removing slot:', e)
  } finally {
    deleting.value = false
  }
}

function time(v: string) {
  return v?.slice(0, 5) || '—'
}

watch(activeDay, (v) => {
  form.day_of_week = v
})

onMounted(load)
</script>

<style scoped>
.routine-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}
.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 1.3rem;
  flex-wrap: wrap;
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
.day-tabs {
  display: flex;
  gap: 0.35rem;
  overflow-x: auto;
  margin-bottom: 1.25rem;
  padding: 0.3rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md, 12px);
}
.day-tabs button {
  flex: 1;
  min-width: 90px;
  padding: 0.6rem 0.8rem;
  border: 0;
  border-radius: var(--radius-sm, 8px);
  background: transparent;
  color: var(--color-text-muted);
  font: 600 0.85rem var(--font-bn);
  cursor: pointer;
  transition: all 0.2s;
}
.day-tabs button.active {
  color: #fff;
  background: var(--color-primary);
  box-shadow: 0 2px 8px rgba(20, 80, 50, 0.2);
}
.create-panel {
  padding: 1.25rem;
  margin-bottom: 1.25rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md, 14px);
}
.form-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.form-heading h2 {
  font: 700 1.05rem var(--font-bn);
  margin: 0;
}
.close-btn {
  border: 0;
  background: transparent;
  font-size: 1.5rem;
  color: var(--color-text-muted);
  cursor: pointer;
}
.form-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.85rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.35rem;
  font: 600 0.78rem var(--font-bn);
  color: var(--color-text);
}
.form-control {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  background: var(--color-bg);
  color: var(--color-text);
  font-size: 0.88rem;
}
.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.2rem;
}
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 3.5rem 0;
  color: var(--color-text-muted);
}
.schedule {
  display: grid;
  gap: 0.85rem;
}
.slot-card {
  display: grid;
  grid-template-columns: 90px 3px 1fr auto;
  align-items: center;
  gap: 1.2rem;
  padding: 1rem 1.25rem;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-md, 14px);
  box-shadow: var(--shadow-sm);
}
.time {
  text-align: right;
}
.time b, .time span {
  display: block;
}
.time b {
  color: var(--color-primary);
  font: 700 0.95rem var(--font-sans);
}
.time span {
  margin-top: 0.15rem;
  color: var(--color-text-muted);
  font: 0.75rem var(--font-sans);
}
.slot-line {
  height: 44px;
  border-radius: 99px;
  background: var(--color-accent, #10b981);
}
.slot-body {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}
.subject-badge {
  display: grid;
  place-items: center;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  color: #7c3aed;
  background: rgba(124, 58, 237, 0.1);
  font-size: 1.2rem;
}
.slot-body h3 {
  font: 700 0.95rem var(--font-bn);
  margin: 0;
  color: var(--color-text);
}
.slot-body p {
  margin: 0.15rem 0 0 0;
  color: var(--color-text-muted);
  font: 0.78rem var(--font-bn);
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
  background: rgba(239, 68, 68, 0.2);
}
.empty-card {
  padding: 3.5rem 1rem;
  text-align: center;
  background: var(--color-bg-card);
  border: 1px dashed var(--color-border);
  border-radius: var(--radius-md, 16px);
}
.empty-icon {
  display: grid;
  place-items: center;
  width: 56px;
  height: 56px;
  margin: 0 auto 1rem;
  border-radius: 16px;
  color: var(--color-primary);
  background: var(--color-primary-50, rgba(20, 80, 50, 0.08));
  font-size: 1.6rem;
}
.empty-card h3 {
  font: 700 1.05rem var(--font-bn);
  margin: 0;
  color: var(--color-text);
}
.empty-card p {
  color: var(--color-text-muted);
  font: 0.82rem var(--font-bn);
  margin: 0.3rem 0 0 0;
}

/* In-App Modal */
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

@media(max-width: 900px) {
  .form-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media(max-width: 550px) {
  .page-header-row {
    align-items: flex-start;
    flex-direction: column;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
  .slot-card {
    grid-template-columns: 1fr;
    gap: 0.65rem;
  }
  .time {
    text-align: left;
  }
  .slot-line {
    width: 100%;
    height: 3px;
  }
  .delete-btn {
    justify-self: end;
  }
}
</style>
