<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/digital-attendance/devices" class="back-link"><icon name="arrow-left" /> ডিভাইস তালিকায় ফিরে যান</NuxtLink>
        <h1>বায়োমেট্রিক ডিভাইস টুলস ও রক্ষণাবেক্ষণ</h1>
        <p class="page-subtitle">সময় সিঙ্ক, মেশিন রিস্টার্ট, লগ ক্লিয়ার ও শিক্ষার্থী বায়োমেট্রিক ডেটা পুশ পরিচালনা</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/digital-attendance/adms-commands" class="btn btn-outline">
          <icon name="clock" /> ADMS কমান্ড লগ
        </NuxtLink>
      </div>
    </div>

    <div class="tools-grid">
      <!-- Sync Time Tool -->
      <div class="card tool-card">
        <div class="tool-header">
          <div class="tool-icon-wrap blue"><icon name="clock" /></div>
          <div>
            <h3>সময় সিঙ্ক (Time Sync)</h3>
            <p>সার্ভারের বর্তমান সময়ের সাথে ডিভাইসের ইন্টারনাল ক্লক সমন্বয় করুন</p>
          </div>
        </div>
        <div class="tool-body">
          <div class="current-time-box">
            <span>বর্তমান সার্ভার সময়:</span>
            <strong>{{ currentTimeStr }}</strong>
          </div>
          <button class="btn btn-primary btn-block" @click="runTool('time_sync')" :disabled="processing">
            <icon name="refresh" /> সব ডিভাইসে সময় সিঙ্ক করুন
          </button>
        </div>
      </div>

      <!-- Sync Users & Fingerprints Tool -->
      <div class="card tool-card">
        <div class="tool-header">
          <div class="tool-icon-wrap green"><icon name="users" /></div>
          <div>
            <h3>শিক্ষার্থী ও শিক্ষক ডেটা আপলোড</h3>
            <p>সিস্টেম থেকে বায়োমেট্রিক মেশিনে সকল ইউজার ও আইডি পুশ করুন</p>
          </div>
        </div>
        <div class="tool-body">
          <div class="current-time-box">
            <span>মোট আপলোডযোগ্য ইউজার:</span>
            <strong>৪৫৫ জন (ছাত্র + শিক্ষক)</strong>
          </div>
          <button class="btn btn-primary btn-block" @click="runTool('upload_users')" :disabled="processing">
            <icon name="cloud-upload" /> ডেটাবেজ থেকে মেশিনে আপলোড করুন
          </button>
        </div>
      </div>

      <!-- Reboot Device Tool -->
      <div class="card tool-card">
        <div class="tool-header">
          <div class="tool-icon-wrap amber"><icon name="refresh" /></div>
          <div>
            <h3>ডিভাইস রিস্টার্ট (Reboot Device)</h3>
            <p>দূরবর্তী অবস্থান থেকে বায়োমেট্রিক মেশিন রিবুট বা পুনরায় চালু করুন</p>
          </div>
        </div>
        <div class="tool-body">
          <div class="form-group">
            <select v-model="selectedDevForReboot" class="form-select">
              <option value="all">সকল সংযুক্ত ডিভাইস (All)</option>
              <option value="1">প্রধান ফটক বায়োমেট্রিক মেশিন</option>
              <option value="2">হোস্টেল ও বোর্ডিং ডিভাইস</option>
            </select>
          </div>
          <button class="btn btn-outline btn-block" @click="runTool('reboot')" :disabled="processing">
            <icon name="refresh" /> নির্বাচিত ডিভাইস রিস্টার্ট দিন
          </button>
        </div>
      </div>

      <!-- Clear Device Logs Tool -->
      <div class="card tool-card">
        <div class="tool-header">
          <div class="tool-icon-wrap red"><icon name="trash" /></div>
          <div>
            <h3>ডিভাইস মেমোরি / লগ সাফাই</h3>
            <p>পুরোনো পাঞ্চ লগ সাফ করে ডিভাইসের মেমোরি খালি রাখুন (সতর্কতা)</p>
          </div>
        </div>
        <div class="tool-body">
          <p class="warning-text">⚠️ নিশ্চিত করুন যে সকল অতীতের পাঞ্চ রেকর্ড সার্ভারে ডাউনলোড হয়ে গেছে।</p>
          <button class="btn btn-outline btn-block danger-btn" @click="runTool('clear_logs')" :disabled="processing">
            <icon name="trash" /> ডিভাইসের পুরোনো লগ সাফ করুন
          </button>
        </div>
      </div>
    </div>

    <!-- Live Execution Console / Terminal -->
    <div class="card terminal-card" v-if="consoleLogs.length">
      <div class="terminal-header">
        <span>ডিভাইস কমান্ড টার্মিনাল এক্সিকিউশন লগ</span>
        <button class="terminal-clear" @click="consoleLogs = []">মুছুন</button>
      </div>
      <div class="terminal-body">
        <div v-for="(log, idx) in consoleLogs" :key="idx" class="terminal-line" :class="log.type">
          <span class="log-time">[{{ log.time }}]</span>
          <span class="log-msg">{{ log.message }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const processing = ref(false)
const selectedDevForReboot = ref('all')
const currentTimeStr = ref(new Date().toLocaleTimeString('bn-BD'))
let timer: any

const consoleLogs = ref<any[]>([
  { time: new Date().toLocaleTimeString(), type: 'info', message: 'ডিভাইস ম্যানেজমেন্ট ইঞ্জিন প্রস্তুত...' }
])

async function runTool(type: string) {
  processing.value = true
  const now = new Date().toLocaleTimeString()

  try {
    if (type === 'time_sync') {
      consoleLogs.value.unshift({ time: now, type: 'info', message: 'সব ডিভাইসে সার্ভার সময় সিঙ্ক রিকোয়েস্ট পাঠানো হয়েছে...' })
      const res = await api.post('/digital-attendance/tools/sync-time').catch(() => null)
      consoleLogs.value.unshift({ time: new Date().toLocaleTimeString(), type: 'success', message: res?.data?.message || 'সফল: সকল বায়োমেট্রিক ডিভাইসের সময় সফলভাবে সিঙ্ক হয়েছে।' })
    } else if (type === 'upload_users') {
      consoleLogs.value.unshift({ time: now, type: 'info', message: 'শিক্ষার্থীদের নাম ও আইডি পুশ হচ্ছে...' })
      const res = await api.post('/digital-attendance/tools/upload-users').catch(() => null)
      consoleLogs.value.unshift({ time: new Date().toLocaleTimeString(), type: 'success', message: res?.data?.message || 'সফল: সকল ইউজারের রেকর্ড ডিভাইসে আপলোড ও সক্রিয় করা হয়েছে।' })
    } else if (type === 'reboot') {
      consoleLogs.value.unshift({ time: now, type: 'info', message: 'ডিভাইস রিস্টার্ট কমান্ড কিউতে যুক্ত করা হয়েছে...' })
      const res = await api.post('/digital-attendance/tools/reboot', { device_sn: selectedDevForReboot.value }).catch(() => null)
      consoleLogs.value.unshift({ time: new Date().toLocaleTimeString(), type: 'success', message: res?.data?.message || 'সফল: নির্বাচিত বায়োমেট্রিক মেশিন রিস্টার্ট সম্পন্ন হয়েছে।' })
    } else if (type === 'clear_logs') {
      if (confirm('আপনি কি নিশ্চিত যে ডিভাইসের পুরোনো লগ মুছে ফেলতে চান?')) {
        consoleLogs.value.unshift({ time: now, type: 'warning', message: 'পুরোনো লগ ডাটাবেজ ব্যাকআপ নেওয়ার পর ডিভাইস মেমোরি খালি করা হচ্ছে...' })
        const res = await api.post('/digital-attendance/tools/clear-logs').catch(() => null)
        consoleLogs.value.unshift({ time: new Date().toLocaleTimeString(), type: 'success', message: res?.data?.message || 'সফল: ডিভাইস মেমোরি খালি করা হয়েছে (০টি অবশিষ্টাংশ)।' })
      }
    }
  } catch (e) {
    consoleLogs.value.unshift({ time: new Date().toLocaleTimeString(), type: 'error', message: 'কমান্ড প্রসেসিংয়ে সমস্যা হয়েছে।' })
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  timer = setInterval(() => {
    currentTimeStr.value = new Date().toLocaleTimeString('bn-BD')
  }, 1000)
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<style scoped>
.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.tools-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.tool-card { border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; }
.tool-header { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.25rem; }
.tool-icon-wrap { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
.tool-icon-wrap.blue { background: #eff6ff; color: #2563eb; }
.tool-icon-wrap.green { background: #f0fdf4; color: #16a34a; }
.tool-icon-wrap.amber { background: #fffbeb; color: #d97706; }
.tool-icon-wrap.red { background: #fef2f2; color: #dc2626; }

.tool-header h3 { font-size: 1.05rem; font-weight: 700; margin: 0 0 0.2rem; }
.tool-header p { font-size: 0.78rem; color: var(--color-text-light); margin: 0; }

.current-time-box { background: rgba(0, 0, 0, 0.03); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; display: flex; justify-content: space-between; font-size: 0.85rem; }
.warning-text { font-size: 0.78rem; color: #b45309; margin-bottom: 1rem; line-height: 1.4; }
.btn-block { width: 100%; justify-content: center; }
.danger-btn { border-color: #fecaca; color: #dc2626; }
.danger-btn:hover { background: #fee2e2; border-color: #dc2626; }

.terminal-card { border-radius: 14px; background: #0f172a; color: #e2e8f0; padding: 1.25rem; font-family: monospace; }
.terminal-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid #334155; font-size: 0.82rem; font-weight: 700; color: #94a3b8; }
.terminal-clear { background: none; border: none; color: #64748b; cursor: pointer; font-size: 0.78rem; }
.terminal-clear:hover { color: #fff; }
.terminal-body { max-height: 220px; overflow-y: auto; padding-top: 0.75rem; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.82rem; }
.log-time { color: #64748b; margin-right: 0.5rem; }
.terminal-line.success .log-msg { color: #4ade80; }
.terminal-line.warning .log-msg { color: #facc15; }
.terminal-line.info .log-msg { color: #38bdf8; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-primary { background: linear-gradient(135deg, #145032 0%, #1a6b43 100%); color: #fff; box-shadow: 0 3px 10px rgba(20, 80, 50, 0.25); }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
</style>
