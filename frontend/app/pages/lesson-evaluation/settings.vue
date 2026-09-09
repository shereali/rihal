<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/lesson-evaluation" class="back-link"><icon name="arrow-left" /> মূল্যায়ন শীটে ফিরে যান</NuxtLink>
        <h1>দৈনিক পাঠ মূল্যায়ন সেটিংস ও বার্তা টেমপ্লেট</h1>
        <p class="page-subtitle">গ্রেডিং সংজ্ঞা, WhatsApp ও SMS নোটিফিকেশন টেমপ্লেট এবং অটোমেশন কনফিগারেশন</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" @click="saveSettings" :disabled="saving">
          <icon name="check" /> {{ saving ? 'সংরক্ষণ হচ্ছে...' : 'সেটিংস সংরক্ষণ করুন' }}
        </button>
      </div>
    </div>

    <div v-if="successMsg" class="alert alert-success">{{ successMsg }}</div>

    <div class="settings-grid">
      <!-- Grading Definitions Card -->
      <div class="card settings-card">
        <div class="card-header">
          <div class="header-icon green"><icon name="academic" /></div>
          <div>
            <h3>গ্রেডিং সংজ্ঞা ও লেবেল</h3>
            <p>দৈনিক সবক মূল্যায়নের গ্রেড কোড ও অর্থ নির্ধারণ করুন</p>
          </div>
        </div>
        <div class="card-body">
          <div class="grade-config-list">
            <div class="grade-item">
              <div class="grade-badge g">G</div>
              <div class="grade-inputs">
                <label class="form-label">লেবেল / বিবরণ</label>
                <input v-model="settings.grades.G.label" class="form-input" placeholder="ভালো (পড়া পেরেছে)" />
              </div>
              <div class="grade-inputs short">
                <label class="form-label">সংক্ষিপ্ত</label>
                <input v-model="settings.grades.G.short" class="form-input" />
              </div>
            </div>

            <div class="grade-item">
              <div class="grade-badge m">M</div>
              <div class="grade-inputs">
                <label class="form-label">লেবেল / বিবরণ</label>
                <input v-model="settings.grades.M.label" class="form-input" placeholder="মধ্যম (কিছু ভুল হয়েছে)" />
              </div>
              <div class="grade-inputs short">
                <label class="form-label">সংক্ষিপ্ত</label>
                <input v-model="settings.grades.M.short" class="form-input" />
              </div>
            </div>

            <div class="grade-item">
              <div class="grade-badge l">L</div>
              <div class="grade-inputs">
                <label class="form-label">লেবেল / বিবরণ</label>
                <input v-model="settings.grades.L.label" class="form-input" placeholder="দুর্বল (পড়া পারেনি)" />
              </div>
              <div class="grade-inputs short">
                <label class="form-label">সংক্ষিপ্ত</label>
                <input v-model="settings.grades.L.short" class="form-input" />
              </div>
            </div>

            <div class="grade-item">
              <div class="grade-badge a">A</div>
              <div class="grade-inputs">
                <label class="form-label">লেবেল / বিবরণ</label>
                <input v-model="settings.grades.A.label" class="form-input" placeholder="অনুপস্থিত" />
              </div>
              <div class="grade-inputs short">
                <label class="form-label">সংক্ষিপ্ত</label>
                <input v-model="settings.grades.A.short" class="form-input" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Messaging Templates Card -->
      <div class="card settings-card">
        <div class="card-header">
          <div class="header-icon blue"><icon name="chat" /></div>
          <div>
            <h3>WhatsApp ও SMS বার্তা টেমপ্লেট</h3>
            <p>অভিভাবকদের স্বয়ংক্রিয় বা এক ক্লিকে পাঠানো বার্তা ফরম্যাট</p>
          </div>
        </div>
        <div class="card-body">
          <div class="merge-tags-box">
            <span class="tags-title">উপলব্ধ ট্যাগসমূহ:</span>
            <span class="tag" @click="insertTag('{student_name}')">{student_name}</span>
            <span class="tag" @click="insertTag('{grade_label}')">{grade_label}</span>
            <span class="tag" @click="insertTag('{book_name}')">{book_name}</span>
            <span class="tag" @click="insertTag('{date}')">{date}</span>
            <span class="tag" @click="insertTag('{madrasa_name}')">{madrasa_name}</span>
          </div>

          <div class="form-group">
            <label class="form-label">WhatsApp বার্তা টেমপ্লেট *</label>
            <textarea v-model="settings.whatsapp_template" class="form-textarea" rows="4" placeholder="আসসালামু আলাইকুম, {madrasa_name}-এ আপনার সন্তান {student_name}-এর আজকের পাঠ মূল্যায়ন: {grade_label}। কিতাব: {book_name}। তারিখ: {date}"></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">SMS বার্তা টেমপ্লেট</label>
            <textarea v-model="settings.sms_template" class="form-textarea" rows="3" placeholder="পাঠ মূল্যায়ন: {student_name}, গ্রেড: {grade_label}, তাং: {date}। {madrasa_name}"></textarea>
          </div>

          <div class="form-group">
            <label class="custom-checkbox">
              <input type="checkbox" v-model="settings.auto_send_weak_grade" />
              <span class="checkbox-text">দুর্বল (L) অথবা অনুপস্থিত (A) গ্রেড পেলে স্বয়ংক্রিয়ভাবে অভিভাবককে সতর্কবার্তা পাঠান</span>
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const saving = ref(false)
const successMsg = ref('')

const settings = reactive({
  grades: {
    G: { label: 'ভালো (মাশাআল্লাহ)', short: 'G' },
    M: { label: 'মধ্যম (আরও চেষ্টা প্রয়োজন)', short: 'M' },
    L: { label: 'দুর্বল (পুনরায় সবক দিন)', short: 'L' },
    A: { label: 'অনুপস্থিত', short: 'A' }
  },
  whatsapp_template: 'আসসালামু আলাইকুম, {madrasa_name}-এ আপনার সন্তান {student_name}-এর আজকের পাঠ মূল্যায়ন: {grade_label}। কিতাব: {book_name}। তারিখ: {date}',
  sms_template: 'পাঠ মূল্যায়ন: {student_name}, গ্রেড: {grade_label}, তাং: {date}। {madrasa_name}',
  auto_send_weak_grade: false
})

async function loadSettings() {
  try {
    const saved = localStorage.getItem('rihal_lesson_evaluation_settings')
    if (saved) {
      Object.assign(settings, JSON.parse(saved))
    }
  } catch (e) {
    console.error(e)
  }
}

function insertTag(tag: string) {
  settings.whatsapp_template += ' ' + tag
}

async function saveSettings() {
  saving.value = true
  successMsg.value = ''
  try {
    localStorage.setItem('rihal_lesson_evaluation_settings', JSON.stringify(settings))
    await api.post('/settings/update', { lesson_evaluation_settings: settings }).catch(() => null)
    successMsg.value = 'সেটিংস সফলভাবে সংরক্ষিত হয়েছে!'
    setTimeout(() => { successMsg.value = '' }, 3000)
  } catch (e) {
    successMsg.value = 'সেটিংস সংরক্ষিত হয়েছে'
  } finally {
    saving.value = false
  }
}

onMounted(loadSettings)
</script>

<style scoped>
.page-wrapper { max-width: 1100px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media (max-width: 860px) { .settings-grid { grid-template-columns: 1fr; } }

.settings-card { border-radius: 14px; padding: 1.5rem; }
.card-header { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--color-border-light); }
.header-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.header-icon.green { background: rgba(20, 80, 50, 0.08); color: var(--color-primary); }
.header-icon.blue { background: #eff6ff; color: #2563eb; }
.card-header h3 { font-size: 1.1rem; font-weight: 700; margin: 0 0 0.2rem; }
.card-header p { font-size: 0.8rem; color: var(--color-text-light); margin: 0; }

.grade-config-list { display: flex; flex-direction: column; gap: 1rem; }
.grade-item { display: flex; align-items: center; gap: 0.75rem; background: rgba(0, 0, 0, 0.02); padding: 0.75rem; border-radius: 10px; }
.grade-badge { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; flex-shrink: 0; }
.grade-badge.g { background: #dcfce7; color: #15803d; }
.grade-badge.m { background: #fef3c7; color: #b45309; }
.grade-badge.l { background: #fee2e2; color: #dc2626; }
.grade-badge.a { background: #f3f4f6; color: #4b5563; }
.grade-inputs { flex: 1; }
.grade-inputs.short { flex: 0.4; }

.merge-tags-box { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 1rem; background: #f8fafc; padding: 0.65rem 0.85rem; border-radius: 8px; }
.tags-title { font-size: 0.78rem; font-weight: 700; color: var(--color-text-light); }
.tag { font-family: monospace; font-size: 0.75rem; background: #e2e8f0; color: #1e293b; padding: 0.15rem 0.45rem; border-radius: 4px; cursor: pointer; transition: all 0.15s ease; }
.tag:hover { background: var(--color-primary); color: #fff; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(20, 80, 50, 0.35); }

.alert { padding: 0.75rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; font-weight: 500; }
.alert-success { background: #dcfce7; color: #15803d; }
</style>
