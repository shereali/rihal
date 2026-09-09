<template>
  <div class="module-page">
    <header class="hero">
      <div>
        <NuxtLink to="/dashboard" class="back-link"><icon name="arrow-left" /> মূল ড্যাশবোর্ড</NuxtLink>
        <span class="eyebrow">প্রতিষ্ঠানের সার্বিক অবস্থা</span>
        <h1>মডিউল ড্যাশবোর্ড</h1>
        <p>শিক্ষার্থী, একাডেমিক, আর্থিক ও প্রশাসনিক কার্যক্রম এক জায়গায় পর্যবেক্ষণ করুন।</p>
      </div>
      <button class="refresh-btn" :disabled="loading" @click="load">
        <icon name="refresh" /> {{ loading ? 'হালনাগাদ হচ্ছে...' : 'হালনাগাদ' }}
      </button>
    </header>

    <div v-if="error" class="alert-error">{{ error }} <button @click="load">আবার চেষ্টা করুন</button></div>

    <section v-if="loading && !Object.keys(stats).length" class="skeleton-grid">
      <div v-for="n in 10" :key="n" class="skeleton-card" />
    </section>

    <section v-else class="module-grid">
      <NuxtLink v-for="item in cards" :key="item.key" :to="item.link" class="module-card">
        <div class="card-top">
          <span class="icon-wrap" :class="item.tone"><icon :name="item.icon" /></span>
          <span class="open-mark">→</span>
        </div>
        <h2>{{ item.label }}</h2>
        <div class="primary-value">{{ format(item.primary) }}</div>
        <div class="primary-label">{{ item.primaryLabel }}</div>
        <div v-if="item.secondaryLabel" class="secondary-row">
          <span>{{ item.secondaryLabel }}</span>
          <strong>{{ format(item.secondary) }}</strong>
        </div>
      </NuxtLink>
    </section>

    <footer class="updated">সর্বশেষ হালনাগাদ: {{ formattedGeneratedAt }}</footer>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const stats = ref<Record<string, any>>({})
const loading = ref(false)
const error = ref('')

const cards = computed(() => [
  { key: 'students', label: 'শিক্ষার্থী ব্যবস্থাপনা', icon: 'students', tone: 'green', primary: stats.value.students?.total, primaryLabel: 'মোট শিক্ষার্থী', link: stats.value.students?.link || '/students' },
  { key: 'teachers', label: 'শিক্ষক ব্যবস্থাপনা', icon: 'students', tone: 'blue', primary: stats.value.teachers?.total, primaryLabel: 'মোট শিক্ষক', link: stats.value.teachers?.link || '/hr' },
  { key: 'attendance', label: 'আজকের হাজিরা', icon: 'attendance', tone: 'gold', primary: stats.value.attendance?.today, primaryLabel: 'হাজিরা রেকর্ড', secondary: stats.value.attendance?.present, secondaryLabel: 'উপস্থিত', link: stats.value.attendance?.link || '/attendance' },
  { key: 'exams', label: 'পরীক্ষা ব্যবস্থাপনা', icon: 'exam', tone: 'purple', primary: stats.value.exams?.total, primaryLabel: 'মোট পরীক্ষা', link: stats.value.exams?.link || '/exams' },
  { key: 'finance', label: 'আর্থিক ব্যবস্থাপনা', icon: 'money', tone: 'green', primary: stats.value.finance?.donations, primaryLabel: 'মোট অনুদান (৳)', secondary: stats.value.finance?.expenses, secondaryLabel: 'মোট ব্যয়', link: stats.value.finance?.link || '/finance' },
  { key: 'fees', label: 'ফি সংগ্রহ', icon: 'fees', tone: 'blue', primary: stats.value.fees?.payments, primaryLabel: 'সংগৃহীত ফি (৳)', link: stats.value.fees?.link || '/fees' },
  { key: 'loans', label: 'ঋণ ও কিস্তি', icon: 'cash', tone: 'red', primary: stats.value.loans?.outstanding, primaryLabel: 'বকেয়া ঋণ (৳)', secondary: stats.value.loans?.overdue, secondaryLabel: 'বিলম্বিত ঋণ', link: stats.value.loans?.link || '/loan-due' },
  { key: 'orphans', label: 'অর্ফান স্পন্সরশিপ', icon: 'child', tone: 'gold', primary: stats.value.orphans?.total, primaryLabel: 'মোট অর্ফান', secondary: stats.value.orphans?.active_sponsorships, secondaryLabel: 'সক্রিয় স্পন্সরশিপ', link: stats.value.orphans?.link || '/orphan-sponsorship' },
  { key: 'hostel', label: 'আবাসিক ব্যবস্থাপনা', icon: 'building', tone: 'purple', primary: stats.value.hostel?.total, primaryLabel: 'মোট কক্ষ', link: stats.value.hostel?.link || '/hostel' },
  { key: 'transport', label: 'পরিবহন ব্যবস্থাপনা', icon: 'bus', tone: 'blue', primary: stats.value.transport?.total, primaryLabel: 'মোট রুট', link: stats.value.transport?.link || '/transport' },
])

const formattedGeneratedAt = computed(() => {
  const value = stats.value.generated_at
  return value ? new Date(value).toLocaleString('bn-BD') : '—'
})

function format(value: unknown) {
  const number = Number(value ?? 0)
  return Number.isFinite(number) ? number.toLocaleString('bn-BD') : '০'
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get('/module-dashboard')
    stats.value = response.data?.data || {}
  } catch (exception: any) {
    error.value = exception?.response?.data?.message || 'মডিউল তথ্য লোড করা যায়নি।'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.module-page { padding: 1.75rem; min-height: 100%; background: #f5f7f5; }
.hero { display:flex; align-items:flex-end; justify-content:space-between; gap:1.5rem; padding:2rem; border-radius:24px; color:white; background:linear-gradient(130deg,#0f402b,#176440 65%,#9a7924); box-shadow:0 18px 45px rgba(20,80,50,.18); margin-bottom:1.5rem; }
.back-link { display:inline-flex; align-items:center; gap:.35rem; color:#e7f4eb; text-decoration:none; margin-bottom:1rem; font-weight:700; }
.eyebrow { display:block; color:#f1d886; font-size:.8rem; letter-spacing:.08em; text-transform:uppercase; font-weight:800; }
h1 { margin:.35rem 0; font-size:clamp(1.8rem,4vw,2.7rem); }
.hero p { margin:0; color:#e8f3ec; max-width:680px; }
.refresh-btn { display:inline-flex; align-items:center; gap:.45rem; border:1px solid rgba(255,255,255,.35); background:rgba(255,255,255,.12); color:white; padding:.75rem 1rem; border-radius:12px; font-weight:700; cursor:pointer; }
.module-grid,.skeleton-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(230px,1fr)); gap:1rem; }
.module-card { display:block; color:#17241c; text-decoration:none; background:white; border:1px solid #e1e8e3; border-radius:18px; padding:1.25rem; box-shadow:0 8px 22px rgba(22,62,38,.06); transition:.2s ease; }
.module-card:hover { transform:translateY(-4px); border-color:#b7cdbd; box-shadow:0 15px 34px rgba(22,62,38,.12); }
.card-top { display:flex; justify-content:space-between; align-items:center; }.icon-wrap { width:46px;height:46px;display:grid;place-items:center;border-radius:14px; }.icon-wrap.green{background:#e1f2e7;color:#145032}.icon-wrap.blue{background:#e4effd;color:#2864a5}.icon-wrap.gold{background:#fff3d0;color:#936f12}.icon-wrap.purple{background:#eee8fb;color:#6948a5}.icon-wrap.red{background:#fde8e7;color:#a53b35}
.open-mark { color:#83948a;font-size:1.3rem; }.module-card h2{font-size:1rem;margin:1rem 0 .6rem}.primary-value{font-size:1.7rem;font-weight:900;color:#145032}.primary-label{font-size:.82rem;color:#6a796f}.secondary-row{border-top:1px solid #edf1ee;margin-top:1rem;padding-top:.75rem;display:flex;justify-content:space-between;color:#647168;font-size:.82rem}.secondary-row strong{color:#24372b}.alert-error{padding:1rem;border-radius:12px;background:#feeceb;color:#9a302b;margin-bottom:1rem}.alert-error button{border:0;background:none;text-decoration:underline;color:inherit;cursor:pointer}.skeleton-card{height:210px;border-radius:18px;background:linear-gradient(90deg,#eef2ef 25%,#f8faf8 50%,#eef2ef 75%);background-size:200% 100%;animation:pulse 1.4s infinite}.updated{text-align:right;color:#718077;font-size:.78rem;margin-top:1rem}@keyframes pulse{to{background-position:-200% 0}}@media(max-width:640px){.module-page{padding:1rem}.hero{padding:1.35rem;align-items:flex-start;flex-direction:column}.refresh-btn{width:100%;justify-content:center}.module-grid{grid-template-columns:1fr 1fr}.module-card{padding:1rem}}@media(max-width:430px){.module-grid{grid-template-columns:1fr}}
</style>
