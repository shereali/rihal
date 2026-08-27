<template>
  <Teleport to="body">
    <div v-if="isOpen" class="license-overlay" @click.self="emit('close')">
      <div class="license-modal" role="dialog" aria-modal="true" aria-label="লাইসেন্সের বিবরণ">
        <button class="license-close" type="button" @click="emit('close')" aria-label="বন্ধ করুন"><Icon name="close" /></button>
        <div class="license-header">
          <div class="license-badge" :class="licenseStatusClass">{{ licenseStatusLabel }}</div>
          <div class="license-plan">{{ licensePlan }}</div>
        </div>
        <div class="license-body">
          <div class="license-row">
            <span class="license-item-label">পরিকল্পনা</span>
            <span class="license-item-value">{{ licensePlan }}</span>
          </div>
          <div class="license-row">
            <span class="license-item-label">সক্রিয় করা হয়েছে</span>
            <span class="license-item-value">{{ licenseActivated }}</span>
          </div>
          <div class="license-row">
            <span class="license-item-label">মেয়াদ শেষ</span>
            <span class="license-item-value">{{ licenseExpiry }}</span>
          </div>
          <div class="license-row">
            <span class="license-item-label">বাকি দিন</span>
            <span class="license-item-value license-remaining">{{ licenseRemaining }} দিন</span>
          </div>
          <div class="license-progress-wrap">
            <div class="license-progress-header"><span>ব্যবহৃত দিন</span><span>গড়ে {{ licenseUsed }} / {{ licenseTotal }} দিন</span></div>
            <div class="license-progress-bar"><span :style="{ width: `${licenseProgress}%` }" /></div>
            <div class="license-progress-footer">{{ licenseProgress }}% ব্যবহৃত</div>
          </div>
          <div class="license-renewal">
            <div class="renewal-icon"><Icon name="refresh" /></div>
            <div>
              <strong>লাইসেন্স তাজা করুন</strong>
              <p>আপনার মাদ্রাসা অনলাইন থাকুক, সীমিত দিন দিয়ে বেশি শিক্ষার্থী নিন। আমাদের সাথে যোগাযোগ করুন।</p>
            </div>
          </div>
          <a class="license-whatsapp" :href="licenseWhatsapp" target="_blank" rel="noopener">
            <Icon name="whatsapp" /> ওয়াটসঅ্যাপে মেসেজ করুন
          </a>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  isOpen: boolean
  license: any
}>()

const emit = defineEmits<{ 'close': [] }>()

const licenseStatusClass = computed(() => {
  const st = props.license?.status
  if (st === 'active') return 'badge-green'
  if (st === 'trial') return 'badge-amber'
  return 'badge-red'
})
const licenseStatusLabel = computed(() => {
  const st = props.license?.status
  if (st === 'active') return '✅ সক্রিয়'
  if (st === 'trial') return '⏳ ট্রায়াল'
  if (st === 'expired') return '❌ মেয়াদ উত্তীর্ণ'
  return '⏸ নিষ্ক্রিয়'
})
const licensePlan = computed(() => 'রিহাল অ্যাডমিন')
const licenseActivated = computed(() => props.license?.activated_at ? new Date(props.license.activated_at).toLocaleDateString('bn-BD') : '—')
const licenseRemaining = computed(() => Number(props.license?.remaining_days ?? 0))
const licenseExpiry = computed(() => props.license?.expiry_date || '—')
const licenseTotal = computed(() => Number(props.license?.total_days ?? 0) || 0)
const licenseUsed = computed(() => Number(props.license?.used_days ?? 0) || 0)
const licenseProgress = computed(() => {
  if (licenseTotal.value === 0) return 0
  return Math.min(100, Math.round((licenseUsed.value / licenseTotal.value) * 100))
})
const licenseWhatsapp = computed(() => 'https://wa.me/8801XXXXXXXXXX')
</script>

<style scoped>
/* License modal */
.license-overlay { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background: rgba(10, 30, 20, .55); backdrop-filter: blur(5px); padding: 1rem; }
.license-modal { position: relative; width: 100%; max-width: 480px; background: var(--color-bg-card); border-radius: 20px; box-shadow: 0 24px 60px rgba(0,0,0,.3); overflow: hidden; animation: license-modal-in .3s cubic-bezier(0.16, 1, 0.3, 1); border: 1px solid var(--color-border-light); }
@keyframes license-modal-in { from { opacity: 0; transform: translateY(20px) scale(.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
.license-close { position: absolute; top: 12px; right: 12px; z-index: 2; width: 32px; height: 32px; display: grid; place-items: center; border: 0; background: var(--color-bg-muted); color: var(--color-text-light); border-radius: 10px; cursor: pointer; font-size: 1rem; transition: background .15s; }
.license-close:hover { background: var(--color-primary-100); color: var(--color-primary); }
.license-header { display: flex; align-items: center; gap: 1rem; padding: 1.25rem 1.5rem 1rem; border-bottom: 1px solid var(--color-border-light); }
.license-badge { display: inline-flex; align-items: center; gap: .4rem; padding: .45rem .9rem; border-radius: 999px; font: 600 .82rem var(--font-bn); }
.badge-green { background: var(--color-primary-50); color: var(--color-primary); }
.badge-amber { background: #f5e9c0; color: #8a6512; }
.badge-red { background: #f8ddd8; color: #a31b2a; }
.license-plan { flex: 1; font: 700 1.15rem var(--font-sans); color: var(--color-text); }
.license-body { padding: 1.25rem 1.5rem 1.75rem; display: flex; flex-direction: column; gap: 1rem; }
.license-row { display: grid; grid-template-columns: 140px 1fr; align-items: center; gap: 1rem; padding-bottom: .65rem; border-bottom: 1px dashed var(--color-border-light); font: .85rem var(--font-bn); }
.license-row:last-of-type { border-bottom: 0; padding-bottom: 0; }
.license-item-label { color: var(--color-text-light); }
.license-item-value { color: var(--color-text); font-weight: 600; text-align: right; }
.license-remaining { color: var(--color-primary); font-weight: 800; font-size: 1.05rem; }
.license-progress-wrap { margin: .5rem 0; padding: 1rem; background: var(--color-bg-muted); border-radius: 12px; }
.license-progress-header { display: flex; justify-content: space-between; font: 600 .75rem var(--font-bn); color: var(--color-text); margin-bottom: .45rem; }
.license-progress-bar { height: 8px; background: rgba(0,0,0,.08); border-radius: 99px; overflow: hidden; }
.license-progress-bar span { display: block; height: 100%; background: linear-gradient(90deg, var(--color-primary-light), var(--color-primary)); border-radius: inherit; }
.license-progress-footer { font: .7rem var(--font-bn); color: var(--color-text-light); text-align: right; margin-top: .4rem; }
.license-renewal { display: flex; gap: 1rem; padding: 1rem; background: #fffdf5; border: 1px solid #f2e6c5; border-radius: 12px; align-items: flex-start; }
.renewal-icon { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 9px; background: #f7e6b0; color: #9c6c06; font-size: 1.2rem; flex-shrink: 0; }
.license-renewal strong { display: block; color: #875c00; font: 700 .9rem var(--font-bn); margin-bottom: .2rem; }
.license-renewal p { color: #8f6e1e; font: .76rem var(--font-bn); margin: 0; line-height: 1.4; }
.license-whatsapp { display: flex; align-items: center; justify-content: center; gap: .5rem; padding: .85rem; border-radius: 12px; background: #25d366; color: #fff; font: 700 .95rem var(--font-bn); text-decoration: none; transition: transform .15s, background .15s; margin-top: .5rem; }
.license-whatsapp:hover { transform: translateY(-2px); background: #1ebd5a; }
</style>
