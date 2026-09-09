<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/promotions/alumni" class="back-link"><icon name="arrow-left" /> ফারেগীন ডিরেক্টরিতে ফিরে যান</NuxtLink>
        <h1>কর্মরত গ্রাজুয়েট ও ফারেগীন শিক্ষার্থী</h1>
        <p class="page-subtitle">বিভিন্ন মাদ্রাসা, মসজিদ ও প্রতিষ্ঠানে কর্মরত সাবেক শিক্ষার্থীদের তালিকা ও পদবী</p>
      </div>
    </div>

    <!-- Employed List Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>ফারেগীন নাম</th>
              <th>পাসের ব্যাচ</th>
              <th>মোবাইল নম্বর</th>
              <th>কর্মস্থল / মাদ্রাসার নাম</th>
              <th>বর্তমান পদবী</th>
              <th>যোগদানের তারিখ</th>
              <th class="text-center">স্ট্যাটাস</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in employedList" :key="e.id">
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(e.name) }">
                    {{ e.name.charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ e.name }}</strong>
                    <div class="sub-text">{{ e.degree }}</div>
                  </div>
                </div>
              </td>
              <td><span class="type-tag">{{ e.batch }}</span></td>
              <td class="mono-font">{{ e.phone }}</td>
              <td><strong>{{ e.workplace }}</strong></td>
              <td><span class="fund-tag">{{ e.designation }}</span></td>
              <td>{{ e.joining_date }}</td>
              <td class="text-center">
                <span class="status-pill badge-approved">
                  <span class="status-dot" /> কর্মরত
                </span>
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
const employedList = ref<any[]>([
  {
    id: 1,
    name: 'মাওলানা মুহাম্মদ আবদুল্লাহ',
    degree: 'দাওরায়ে হাদীস',
    batch: '2024-2025',
    phone: '০১৭১২-৩৪৫৬৭৮',
    workplace: 'দারুল উলুম মাদ্রাসা ঢাকা',
    designation: 'সহকারী শিক্ষক',
    joining_date: '০১ মার্চ, ২০২৫'
  }
])

async function loadEmployed() {
  try {
    const res = await api.get('/alumni?status=employed').catch(() => null)
    const list = res?.data?.data || []
    if (list.length > 0) {
      employedList.value = list.map((a: any) => ({
        id: a.id,
        name: a.name,
        degree: a.degree_title || 'দাওরায়ে হাদীস',
        batch: a.passing_year || '২০২৫',
        phone: a.phone || '—',
        workplace: a.organization || 'মাদ্রাসা',
        designation: a.designation || 'শিক্ষক',
        joining_date: a.passing_year || '২০২৫'
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

onMounted(loadEmployed)

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
.fund-tag { display: inline-block; padding: 0.15rem 0.55rem; background: rgba(20, 80, 50, 0.08); color: var(--color-primary); border-radius: 6px; font-size: 0.78rem; font-weight: 600; }
</style>
