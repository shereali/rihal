<template>
  <div class="changelog-page">
    <div class="page-header">
      <div class="header-left">
        <h1>সিস্টেম চেঞ্জলগ</h1>
        <p class="subtitle">রিহাল সিস্টেমের আপডেট ও রিলিজ হিস্টরি</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /></div>
        <div v-else>
          <div v-for="version in versions" :key="version.version" class="version-card">
            <div class="version-header">
              <span class="version-badge">{{ version.version }}</span>
              <span class="version-date">{{ version.release_date }}</span>
              <span class="version-type">
                <span class="badge" :class="version.type === 'major' ? 'badge-primary' : 'badge-secondary'">{{ version.type === 'major' ? 'বড় আপডেট' : 'ছোট আপডেট' }}</span>
              </span>
            </div>
            <div class="version-changes">
              <div v-for="(change, i) in version.changes" :key="i" class="change-item">
                <span class="change-dot"></span>
                <span>{{ change }}</span>
              </div>
            </div>
          </div>

          <div v-if="!versions.length" class="empty-state">
            <p>কোনো চেঞ্জলগ পাওয়া যায়নি</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

const loading = ref(false)

// Static changelog data (could be fetched from API if a changelog endpoint exists)
const versions = ref([
  {
    version: '2.0.0',
    release_date: 'আগস্ট ২০২৬',
    type: 'major',
    changes: [
      'ডিজিটাল হাজিরা সিস্টেম যুক্ত (B.3)',
      'প্রসন্ন ও গ্র্যাডুএশন মডিউল (B.4)',
      'সার্টিফিকেট জেনারেশন সিস্টেম (B.5)',
      'রিমাইন্ডার টাস্ক ও লিভ ব্যবস্থাপনা (B.1, B.2)',
      'প্রশাসনিক ড্যাশবোর্ড (E.1)',
      'ফিন্যান্স লিস্ট পৃষ্ঠা (D)',
    ],
  },
  {
    version: '1.3.0',
    release_date: 'জুন ২০২৬',
    type: 'minor',
    changes: [
      'শিক্ষার্থী ভর্তি ব্যবস্থাপনা',
      'পরীক্ষা ও ফলাফল সিস্টেম',
      'হাজিরা রেকর্ড',
      'একাডেমিক সেটআপ (শ্রেণি, সেশন, বিভাগ)',
    ],
  },
  {
    version: '1.0.0',
    release_date: 'এপ্রিল ২০২৬',
    type: 'major',
    changes: [
      'রিহাল প্ল্যাটফর্মের প্রাথমিক রিলিজ',
      'স্টুডেন্ট, টিচার, গার্ডিয়ান রোল',
      'ড্যাশবোর্ড ও বিজ্ঞপ্তি সিস্টেম',
      'ফি ও আর্থিক ব্যবস্থাপনা',
    ],
  },
])

onMounted(() => {
  loading.value = false
})
</script>

<style scoped>
.changelog-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.header-left h1 { margin: 0; font-family: 'Noto Sans Bengali', sans-serif; }
.subtitle { color: var(--color-text-light); font-size: 0.9rem; font-family: 'Noto Sans Bengali', sans-serif; }
.card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: 12px; }
.card-body { padding: 1.25rem; }
.version-card { border: 1px solid var(--color-border-light); border-radius: 10px; padding: 1rem; margin-bottom: 1rem; background: var(--color-bg); }
.version-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap; }
.version-badge { font-size: 1.1rem; font-weight: 700; color: var(--color-primary); font-family: 'Inter', sans-serif; }
.version-date { font-size: 0.85rem; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
.version-type { margin-left: auto; }
.badge { padding: 0.25rem 0.7rem; border-radius: 12px; font-size: 0.75rem; font-family: 'Noto Sans Bengali', sans-serif; }
.badge-primary { background: rgba(20, 80, 50, 0.1); color: var(--color-primary); }
.badge-secondary { background: rgba(107, 114, 128, 0.15); color: #6b7280; }
.version-changes { display: flex; flex-direction: column; gap: 0.4rem; }
.change-item { display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; font-family: 'Noto Sans Bengali', sans-serif; }
.change-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--color-primary); margin-top: 0.3rem; flex-shrink: 0; }
.loading-state { display: flex; justify-content: center; padding: 2rem; }
.spinner { width: 24px; height: 24px; border: 3px solid var(--color-border); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.empty-state { padding: 1.5rem; text-align: center; color: var(--color-text-light); font-family: 'Noto Sans Bengali', sans-serif; }
</style>
