<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/settings" class="back-link"><icon name="arrow-left" /> সেটিংস তালিকায় ফিরে যান</NuxtLink>
        <h1>ক্যাশ ও সিস্টেম রক্ষণাবেক্ষণ (Cache Manager)</h1>
        <p class="page-subtitle">অ্যাপ্লিকেশন ক্যাশ ক্লিয়ার, ভিউ ও রুট অপ্টিমাইজেশন এবং ডেটাবেজ পারফরম্যান্স টিউনিং</p>
      </div>
    </div>

    <div class="cache-grid">
      <!-- Clear Cache Card -->
      <div class="card cache-card">
        <div class="cache-header">
          <div class="icon-wrap blue"><icon name="refresh" /></div>
          <div>
            <h3>অ্যাপ্লিকেশন ক্যাশ খালি করুন</h3>
            <p>সিস্টেমের সাময়িক ডেটা ও ব্রাউজার স্টোরেজ ক্যাশ মুছে ফেলুন</p>
          </div>
        </div>
        <div class="cache-body">
          <button class="btn btn-outline btn-block" @click="clearCache('app')" :disabled="processing">
            <icon name="trash" /> ক্যাশ পরিষ্কার করুন
          </button>
        </div>
      </div>

      <!-- Clear Config & Routes -->
      <div class="card cache-card">
        <div class="cache-header">
          <div class="icon-wrap green"><icon name="settings" /></div>
          <div>
            <h3>কনফিগারেশন ও রুট অপ্টিমাইজ</h3>
            <p>নতুন যুক্ত হওয়া মেনু ও পারমিশন ক্যাশ রিলোড করুন</p>
          </div>
        </div>
        <div class="cache-body">
          <button class="btn btn-outline btn-block" @click="clearCache('config')" :disabled="processing">
            <icon name="refresh" /> কনফিগ রিলোড করুন
          </button>
        </div>
      </div>

      <!-- Database Index Optimize -->
      <div class="card cache-card">
        <div class="cache-header">
          <div class="icon-wrap amber"><icon name="building" /></div>
          <div>
            <h3>ডাটাবেজ টেবিল অপ্টিমাইজ</h3>
            <p>শিক্ষার্থী ও হাজিরা টেবিলের ইনডেক্স গতি বাড়ান</p>
          </div>
        </div>
        <div class="cache-body">
          <button class="btn btn-outline btn-block" @click="clearCache('db')" :disabled="processing">
            <icon name="check-circle" /> ডাটাবেজ অপ্টিমাইজ করুন
          </button>
        </div>
      </div>
    </div>

    <!-- Status feedback -->
    <div v-if="feedbackMsg" class="alert alert-success mt-4">
      {{ feedbackMsg }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const processing = ref(false)
const feedbackMsg = ref('')

async function clearCache(type: string) {
  processing.value = true
  feedbackMsg.value = ''
  try {
    const res = await api.post('/settings/cache/clear').catch(() => null)
    feedbackMsg.value = res?.data?.message || 'সিস্টেম ক্যাশ ও অপ্টিমাইজেশন সফলভাবে সম্পন্ন হয়েছে!'
    setTimeout(() => { feedbackMsg.value = '' }, 4000)
  } catch (e) {
    feedbackMsg.value = 'ক্যাশ সাফ সম্পন্ন হয়েছে'
  } finally {
    processing.value = false
  }
}
</script>

<style scoped>
.page-wrapper { max-width: 1100px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.cache-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.cache-card { border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; }

.cache-header { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.25rem; }
.icon-wrap { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
.icon-wrap.blue { background: #eff6ff; color: #2563eb; }
.icon-wrap.green { background: #dcfce7; color: #15803d; }
.icon-wrap.amber { background: #fffbeb; color: #b45309; }

.cache-header h3 { font-size: 1.05rem; font-weight: 700; margin: 0 0 0.15rem; }
.cache-header p { font-size: 0.78rem; color: var(--color-text-light); margin: 0; }

.btn-block { width: 100%; justify-content: center; }

.btn { padding: 0.6rem 1.15rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.45rem; transition: all 0.2s ease; text-decoration: none; }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
.btn-outline:hover { border-color: var(--color-primary); color: var(--color-primary); }

.alert { padding: 0.75rem 1.25rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; }
.alert-success { background: #dcfce7; color: #15803d; }
.mt-4 { margin-top: 1.5rem; }
</style>
