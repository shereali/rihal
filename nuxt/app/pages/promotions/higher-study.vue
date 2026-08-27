<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div class="header-title-block">
        <NuxtLink to="/promotions/alumni" class="back-link"><icon name="arrow-left" /> ফারেগীন ডিরেক্টরিতে ফিরে যান</NuxtLink>
        <h1>উচ্চ শিক্ষা শিক্ষার্থী (Higher Islamic & General Studies)</h1>
        <p class="page-subtitle">দেশ ও বিদেশের উচ্চতর শিক্ষাপ্রতিষ্ঠানে ইফতা, উলুমুল হাদিস, আদব ও বিশ্ববিদ্যালয়ে অধ্যয়নরত শিক্ষার্থী</p>
      </div>
    </div>

    <!-- Higher Study List Table -->
    <div class="card table-card">
      <div class="table-responsive">
        <table class="premium-table">
          <thead>
            <tr>
              <th>শিক্ষার্থীর নাম</th>
              <th>অধ্যয়নরত কোর্স / বিভাগ</th>
              <th>বর্তমান বিশ্ববিদ্যালয় / জামিয়া</th>
              <th>দেশ / অবস্থান</th>
              <th>যোগাযোগ নম্বর</th>
              <th class="text-center">অবস্থা</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="h in higherStudyList" :key="h.id">
              <td>
                <div class="user-cell">
                  <div class="user-avatar-initials" :style="{ backgroundColor: getAvatarColor(h.name) }">
                    {{ h.name.charAt(0) }}
                  </div>
                  <div>
                    <strong>{{ h.name }}</strong>
                    <div class="sub-text">অত্র মাদ্রাসার দাওরায়ে হাদীস ব্যাচ: {{ h.batch }}</div>
                  </div>
                </div>
              </td>
              <td><strong>{{ h.course }}</strong></td>
              <td>{{ h.institution }}</td>
              <td><span class="type-tag">{{ h.country }}</span></td>
              <td class="mono-font">{{ h.phone }}</td>
              <td class="text-center">
                <span class="status-pill badge-approved">
                  <span class="status-dot" /> অধ্যয়নরত
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
const higherStudyList = ref<any[]>([
  {
    id: 1,
    name: 'মাওলানা তরিকুল ইসলাম',
    batch: '2023-2024',
    course: 'উচ্চতর ইসলামী আইন ও ফতোয়া (ইফতা)',
    institution: 'দারুল উলুম দেওবন্দ',
    country: 'ভারত',
    phone: '+91 98765 43210'
  }
])

async function loadHigherStudy() {
  try {
    const res = await api.get('/alumni?status=higher_study').catch(() => null)
    const list = res?.data?.data || []
    if (list.length > 0) {
      higherStudyList.value = list.map((a: any) => ({
        id: a.id,
        name: a.name,
        batch: a.passing_year || '২০২৫',
        course: a.degree_title || 'উচ্চতর শিক্ষা',
        institution: a.organization || 'বিশ্ববিদ্যালয়',
        country: 'বাংলাদেশ',
        phone: a.phone || '—'
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

onMounted(loadHigherStudy)

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
</style>
