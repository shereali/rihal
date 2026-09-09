<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/digital-attendance/devices" class="back-link"><icon name="arrow-left" /> ডিভাইস তালিকায় ফিরে যান</NuxtLink>
        <h1>লাইভ বায়োমেট্রিক ও পাঞ্চ যাচাইকরণ</h1>
        <p class="page-subtitle">হাজিরা মেশিনের আঙুলের ছাপ, ফেস রিকগনিশন ও RFID কার্ড পাঞ্চের তাৎক্ষণিক লাইভ টেস্ট</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="clearLogs">
          <icon name="trash" /> লগ পরিষ্কার
        </button>
      </div>
    </div>

    <div class="verify-layout">
      <!-- Live Punch Tester / Simulator Panel -->
      <div class="card tester-card">
        <div class="tester-header">
          <div class="fingerprint-scan-animation" :class="{ scanning: isScanning, matched: lastMatch }">
            <icon name="check-circle" v-if="lastMatch" />
            <icon name="fingerprint" v-else />
            <div class="scan-laser" v-if="isScanning" />
          </div>
          <h3>বায়োমেট্রিক সেন্সর প্রস্তুত</h3>
          <p>হাজিরা পাঞ্চ টেস্ট করতে নিচের বাটনে চাপ দিন</p>
        </div>

        <div class="tester-controls">
          <div class="form-group">
            <label class="form-label">পরীক্ষামূলক শিক্ষার্থী / ইউজার</label>
            <select v-model="selectedStudentId" class="form-select">
              <option v-for="s in demoStudents" :key="s.id" :value="s.id">
                {{ s.name }} (রোল: {{ s.roll }}, {{ s.class }})
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">হাজিরা পাঞ্চের মাধ্যম</label>
            <div class="punch-mode-toggle">
              <button class="mode-btn" :class="{ active: punchMode === 'fingerprint' }" @click="punchMode = 'fingerprint'">
                <icon name="fingerprint" /> আঙুলের ছাপ
              </button>
              <button class="mode-btn" :class="{ active: punchMode === 'rfid' }" @click="punchMode = 'rfid'">
                <icon name="document-text" /> RFID কার্ড
              </button>
              <button class="mode-btn" :class="{ active: punchMode === 'face' }" @click="punchMode = 'face'">
                <icon name="users" /> ফেস স্ক্যান
              </button>
            </div>
          </div>

          <button class="btn btn-primary btn-block glow" @click="simulatePunch" :disabled="isScanning">
            <icon name="check" /> {{ isScanning ? 'পাঞ্চ যাচাই হচ্ছে...' : 'হাজিরা পাঞ্চ যাচাই করুন' }}
          </button>
        </div>

        <!-- Matched Student Result Banner -->
        <div v-if="lastMatch" class="match-result-card animate-fade">
          <div class="user-avatar-initials lg">
            {{ lastMatch.name.charAt(0) }}
          </div>
          <div class="match-info">
            <div class="match-badge">✅ যাচাইকরণ সফল (Score: {{ lastMatch.score }}%)</div>
            <h4>{{ lastMatch.name }}</h4>
            <div class="match-meta">
              <span>রোল: {{ lastMatch.roll }}</span> · <span>{{ lastMatch.class }}</span> · <time>{{ lastMatch.time }}</time>
            </div>
          </div>
        </div>
      </div>

      <!-- Realtime Punch Stream Feed -->
      <div class="card feed-card">
        <div class="feed-header">
          <div class="feed-title-wrap">
            <span class="live-dot" />
            <h3>রিয়েল-টাইম পাঞ্চ স্ট্রিম</h3>
          </div>
          <span class="sub-text">মোট পাঞ্চ: {{ punchLogs.length }}টি</span>
        </div>

        <div class="feed-list" v-if="punchLogs.length">
          <div v-for="log in punchLogs" :key="log.id" class="feed-item animate-slide">
            <div class="feed-icon" :class="log.status">
              <icon name="check" />
            </div>
            <div class="feed-content">
              <div class="feed-top">
                <strong>{{ log.name }}</strong>
                <time class="feed-time">{{ log.time }}</time>
              </div>
              <div class="feed-sub">
                <span>{{ log.class }}</span> · <span>ডিভাইস: {{ log.device }}</span> · <span class="method-tag">{{ log.method }}</span>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="empty-feed">
          <p>এখনও কোনো লাইভ পাঞ্চ রেকর্ড নেই। বাম পাশের টেস্ট প্যানেল থেকে পাঞ্চ সিমুলেট করুন।</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const isScanning = ref(false)
const punchMode = ref('fingerprint')
const selectedStudentId = ref(1)
const lastMatch = ref<any>(null)

const demoStudents = ref<any[]>([
  { id: 1, name: 'মুহাম্মদ সালমান ফারসি', roll: '০১', class: 'মিজান জামাত' },
  { id: 2, name: 'মাওলানা মাহমুদ হাসান', roll: 'টিচার', class: 'মুহাদ্দিস (শিক্ষক)' }
])

const punchLogs = ref<any[]>([
  {
    id: 101,
    name: 'মুহাম্মদ সালমান ফারসি',
    class: 'মিজান জামাত',
    device: 'প্রধান ফটক (ZK9821448102)',
    method: 'আঙুলের ছাপ',
    time: '০৮:১৪:২৫',
    status: 'success'
  }
])

async function loadStudents() {
  try {
    const res = await api.get('/students?per_page=50').catch(() => null)
    const studs = res?.data?.data?.data || res?.data?.data || []
    if (studs.length > 0) {
      demoStudents.value = studs.map((s: any) => ({
        id: s.id,
        name: s.name_bn || s.name_en || 'শিক্ষার্থী',
        roll: s.roll_number || String(s.id),
        class: s.academic_class?.name || 'হিফজ বিভাগ'
      }))
      selectedStudentId.value = demoStudents.value[0].id
    }
  } catch (e) {
    console.error(e)
  }
}

async function simulatePunch() {
  isScanning.value = true
  lastMatch.value = null

  try {
    const student = demoStudents.value.find(s => s.id === Number(selectedStudentId.value)) || demoStudents.value[0]
    await api.post('/digital-attendance/punch-simulate', {
      user_id: student?.id,
      punch_state: 'check_in',
      device_id: 1
    }).catch(() => null)

    const nowTime = new Date().toLocaleTimeString('bn-BD')
    lastMatch.value = {
      name: student?.name || 'শিক্ষার্থী',
      roll: student?.roll || '০১',
      class: student?.class || 'মিজান জামাত',
      score: 99.4,
      time: nowTime
    }

    const methodLabels: Record<string, string> = {
      fingerprint: 'আঙুলের ছাপ',
      rfid: 'RFID কার্ড',
      face: 'ফেস স্ক্যান'
    }

    punchLogs.value.unshift({
      id: Date.now(),
      name: student?.name || 'শিক্ষার্থী',
      class: student?.class || 'মিজান জামাত',
      device: 'প্রধান ফটক (ZK9821448102)',
      method: methodLabels[punchMode.value] || 'আঙুলের ছাপ',
      time: nowTime,
      status: 'success'
    })
  } catch (e) {
    console.error(e)
  } finally {
    isScanning.value = false
  }
}

function clearLogs() {
  punchLogs.value = []
  lastMatch.value = null
}

onMounted(loadStudents)
</script>

<style scoped>
.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.verify-layout { display: grid; grid-template-columns: 1.1fr 1fr; gap: 1.5rem; }
@media (max-width: 860px) { .verify-layout { grid-template-columns: 1fr; } }

.tester-card { border-radius: 14px; padding: 1.75rem; text-align: center; }
.tester-header { margin-bottom: 1.5rem; }

.fingerprint-scan-animation { width: 90px; height: 90px; border-radius: 50%; background: #f0fdf4; border: 3px solid #86efac; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 2.75rem; margin: 0 auto 1rem; position: relative; overflow: hidden; }
.fingerprint-scan-animation.scanning { border-color: #3b82f6; background: #eff6ff; color: #2563eb; }
.fingerprint-scan-animation.matched { border-color: #22c55e; background: #dcfce7; color: #15803d; }

.scan-laser { position: absolute; left: 0; right: 0; height: 3px; background: #3b82f6; box-shadow: 0 0 10px #3b82f6; animation: laserSweep 1s infinite alternate; }

.tester-header h3 { font-size: 1.2rem; font-weight: 700; margin: 0 0 0.2rem; }
.tester-header p { font-size: 0.84rem; color: var(--color-text-light); margin: 0; }

.tester-controls { text-align: left; margin-bottom: 1.5rem; }
.punch-mode-toggle { display: flex; gap: 0.5rem; }
.mode-btn { flex: 1; padding: 0.55rem; border: 1px solid var(--color-border); background: var(--color-bg); border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; }
.mode-btn.active { background: rgba(20, 80, 50, 0.09); border-color: var(--color-primary); color: var(--color-primary); font-weight: 700; }

.btn-block { width: 100%; justify-content: center; padding: 0.75rem; font-size: 0.95rem; }
.btn.glow { box-shadow: 0 4px 20px rgba(20, 80, 50, 0.35); }

/* Matched result banner */
.match-result-card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 1rem; text-align: left; }
.user-avatar-initials.lg { width: 50px; height: 50px; border-radius: 50%; background: #15803d; color: #fff; font-size: 1.3rem; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.match-badge { font-size: 0.75rem; font-weight: 700; color: #15803d; margin-bottom: 0.15rem; }
.match-info h4 { margin: 0 0 0.2rem; font-size: 1.05rem; }
.match-meta { font-size: 0.8rem; color: var(--color-text-light); }

/* Feed */
.feed-card { border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; }
.feed-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-border-light); }
.feed-title-wrap { display: flex; align-items: center; gap: 0.5rem; }
.live-dot { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 8px #22c55e; animation: pulse 1.5s infinite; }
.feed-header h3 { font-size: 1.05rem; font-weight: 700; margin: 0; }

.feed-list { display: flex; flex-direction: column; gap: 0.75rem; max-height: 460px; overflow-y: auto; }
.feed-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 10px; background: rgba(0, 0, 0, 0.02); border: 1px solid var(--color-border-light); }
.feed-icon { width: 34px; height: 34px; border-radius: 50%; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.feed-content { flex: 1; min-width: 0; }
.feed-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.15rem; }
.feed-top strong { font-size: 0.88rem; }
.feed-time { font-size: 0.75rem; color: var(--color-text-light); font-family: monospace; }
.feed-sub { font-size: 0.76rem; color: var(--color-text-light); }
.method-tag { font-weight: 600; color: var(--color-primary); }

.empty-feed { text-align: center; padding: 3rem 1rem; color: var(--color-text-light); font-size: 0.85rem; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }

@keyframes laserSweep { 0% { top: 10%; } 100% { top: 90%; } }
@keyframes pulse { 0% { opacity: 0.4; } 50% { opacity: 1; } 100% { opacity: 0.4; } }
</style>
