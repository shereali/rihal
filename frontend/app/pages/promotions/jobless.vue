<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/promotions/alumni" class="back-link"><icon name="arrow-left" /> ফারেগীন ডিরেক্টরিতে ফিরে যান</NuxtLink>
        <h1>বেকার ও কর্মসন্ধানী ফারেগীন শিক্ষার্থী</h1>
        <p class="page-subtitle">ডিগ্রি সম্পন্ন করার পর খিদমত বা কর্মসংস্থানের সন্ধানে থাকা সাবেক শিক্ষার্থীদের কর্মসংস্থান সহায়তা তালিকা</p>
      </div>
    </div>

    <!-- Jobless List Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>ফারেগীন নাম</th>
              <th>পাসের ব্যাচ</th>
              <th>মোবাইল নম্বর</th>
              <th>পছন্দনীয় খিদমতের ক্ষেত্র</th>
              <th>আগ্রহের জেলা / এলাকা</th>
              <th>যোগাযোগ স্ট্যাটাস</th>
              <th class="text-right">অ্যাকশন</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="j in joblessList" :key="j.id">
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(j.name) }">
                    {{ j.name.charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ j.name }}</strong>
                    <div class="sub-text">{{ j.degree }}</div>
                  </div>
                </div>
              </td>
              <td><span class="type-tag">{{ j.batch }}</span></td>
              <td class="mono-font">{{ j.phone }}</td>
              <td><strong>{{ j.preferred_job }}</strong></td>
              <td>{{ j.preferred_location }}</td>
              <td>
                <span class="status-pill badge-pending">
                  <span class="status-dot" /> কর্মসন্ধানী
                </span>
              </td>
              <td class="text-right">
                <a :href="'tel:' + j.phone" class="btn btn-sm btn-outline"><icon name="phone" /> কল করুন</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const joblessList = ref<any[]>([
  {
    id: 1,
    name: 'মাওলানা ফাহিম ফয়সাল',
    degree: 'দাওরায়ে হাদীস',
    batch: '2025-2026',
    phone: '০১৯১১-২২৩৩৪৪',
    preferred_job: 'মাদ্রাসার কিতাব বিভাগ শিক্ষকতা / ইমামতি',
    preferred_location: 'ঢাকা / গোপালগঞ্জ / খুলনা'
  }
])

async function loadJobless() {
  try {
    const res = await api.get('/alumni?status=unemployed').catch(() => null)
    const list = res?.data?.data || []
    if (list.length > 0) {
      joblessList.value = list.map((a: any) => ({
        id: a.id,
        name: a.name,
        degree: a.degree_title || 'দাওরায়ে হাদীস',
        batch: a.passing_year || '২০২৬',
        phone: a.phone || '—',
        preferred_job: a.preferred_job || 'শিক্ষকতা / ইমামতি',
        preferred_location: a.preferred_location || 'যেকোনো জেলা'
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

onMounted(loadJobless)

const colorPalette = ['#145032', '#1e40af', '#b45309', '#6b21a8', '#047857', '#be185d', '#0369a1']
function getAvatarColor(name: string) {
  if (!name) return colorPalette[0]
  let hash = 0
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
  return colorPalette[Math.abs(hash) % colorPalette.length]
}
</script>

<style scoped>
.page-wrapper { max-width: 1200px; margin: 0 auto; padding: 1.75rem; }
.page-header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.back-link { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 600; color: var(--color-primary); text-decoration: none; margin-bottom: 0.35rem; }
.back-link:hover { text-decoration: underline; }
.header-title-block h1 { font-size: 1.6rem; font-weight: 800; margin: 0.2rem 0 0.35rem; color: var(--color-text); }
.page-subtitle { color: var(--color-text-light); font-size: 0.88rem; margin: 0; }

.table-card { border-radius: 14px; overflow: hidden; }
.table-responsive { overflow-x: auto; }
.user-cell { display: flex; align-items: center; gap: 0.65rem; }
.user-avatar-initials { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-size: 0.84rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sub-text { font-size: 0.74rem; color: var(--color-text-light); }
.mono-font { font-family: monospace; font-size: 0.84rem; }
.type-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(0, 0, 0, 0.05); border-radius: 4px; font-size: 0.75rem; font-weight: 600; }

.btn { padding: 0.45rem 0.85rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; text-decoration: none; }
.btn-outline { background: var(--color-bg); border: 1px solid var(--color-border); color: var(--color-text); }
.btn-outline:hover { border-color: var(--color-primary); color: var(--color-primary); }
</style>
