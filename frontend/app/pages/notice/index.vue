<template>
  <div class="notice-page">
    <!-- Printable Header -->
    <div class="print-header-block print-only">
      <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
      <h2 class="inst-title">মারকাযুল উলূম আল-ইসলামিয়া</h2>
      <p class="inst-sub">নোটিশ ও গুরুত্বপূর্ণ সাধারণ বিজ্ঞপ্তি তালিকা</p>
      <div class="print-meta-row">
        <span>তারিখ: {{ new Date().toLocaleDateString('bn-BD', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
        <span>মোট বিজ্ঞপ্তি: {{ toBn(filteredNotices.length) }} টি</span>
      </div>
    </div>

    <div class="page-header no-print">
      <div class="header-left">
        <h1>বিজ্ঞপ্তি ও নোটিশ বোর্ড</h1>
        <p class="text-muted">প্রতিষ্ঠান ও জামাতের সকল সাধারণ ঘোষণা ও সতর্কবার্তা ({{ toBn(filteredNotices.length) }} টি)</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="printPage" title="প্রিন্ট করুন">
          <icon name="printer" /> প্রিন্ট
        </button>
        <NuxtLink to="/notice/create" class="btn btn-primary">
          <icon name="plus" /> নতুন বিজ্ঞপ্তি
        </NuxtLink>
      </div>
    </div>

    <!-- Toolbar Filters -->
    <div class="toolbar-card no-print">
      <div class="search-input-wrap">
        <icon name="search" class="search-icon" />
        <input v-model="searchQuery" type="text" placeholder="বিজ্ঞপ্তির শিরোনাম বা বিবরণ খুঁজুন..." class="search-input" />
      </div>
      <div class="filter-group">
        <select v-model="selectedType" class="filter-select">
          <option value="">সকল ধরন</option>
          <option value="notice">সাধারণ বিজ্ঞপ্তি</option>
          <option value="announcement">জরুরি ঘোষণা</option>
          <option value="warning">সতর্কবার্তা</option>
        </select>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state">
          <div class="spinner" />
          <p>বিজ্ঞপ্তি লোড হচ্ছে...</p>
        </div>

        <div v-else-if="filteredNotices.length === 0" class="empty-state">
          <p>কোনো বিজ্ঞপ্তি পাওয়া যায়নি</p>
          <NuxtLink to="/notice/create" class="btn btn-primary no-print">প্রথম বিজ্ঞপ্তি পোস্ট করুন</NuxtLink>
        </div>

        <div v-else class="notice-grid">
          <article
            v-for="notice in filteredNotices"
            :key="notice.id"
            class="notice-card"
            :class="{ pinned: notice.is_pinned }"
            @click="viewNotice(notice)"
          >
            <div class="notice-card-header">
              <div class="notice-type-badge" :class="notice.type">
                <icon :name="getTypeIcon(notice.type)" />
                {{ notice.type === 'notice' ? 'বিজ্ঞপ্তি' : notice.type === 'announcement' ? 'ঘোষণা' : 'সতর্কবার্তা' }}
              </div>
              <div v-if="notice.is_pinned" class="pinned-badge"><icon name="pin" /> পিন করা</div>
              <NuxtLink :to="`/notice/${notice.id}/edit`" class="btn-icon btn-icon-sm no-print" @click.stop>
                <icon name="pencil" />
              </NuxtLink>
            </div>

            <div class="notice-card-body">
              <h3 class="notice-title">{{ notice.title_bn || notice.title_en }}</h3>
              <p class="notice-preview">{{ truncate(notice.content_bn || notice.content_en, 120) }}</p>
            </div>

            <div class="notice-card-footer">
              <span class="notice-meta"><icon name="calendar" /> {{ formatDate(notice.published_at || notice.created_at) }}</span>
              <span class="notice-meta"><icon name="account" /> {{ notice.creator?.name_bn || notice.creator?.name || 'প্রশাসন' }}</span>
              <div class="notice-actions no-print">
                <NuxtLink :to="`/notice/${notice.id}`" class="btn btn-sm btn-primary" @click.stop><icon name="eye" /> দেখুন</NuxtLink>
              </div>
            </div>
          </article>
        </div>

        <div v-if="notices?.data?.meta && notices.data.meta.total > notices.data.per_page" class="pagination-wrapper no-print">
          <div class="pagination">
            <button v-for="page in totalPages" :key="page" :class="['page-btn', { active: page === notices?.data?.current_page }]" @click="goToPage(page)">{{ toBn(page) }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const notices = ref<any>(null)
const totalPages = ref(1)
const searchQuery = ref('')
const selectedType = ref('')

async function loadNotices(page = 1) {
  loading.value = true
  try {
    const res = await api.get(`/notices?page=${page}&per_page=20&sort=-published_at`)
    notices.value = res.data
    totalPages.value = res.data.meta?.last_page || 1
  } catch (error) {
    console.error('Failed to load notices:', error)
  } finally {
    loading.value = false
  }
}

const filteredNotices = computed(() => {
  const list = notices.value?.data?.data || notices.value?.data || []
  let res = [...list]
  if (selectedType.value) {
    res = res.filter((n: any) => n.type === selectedType.value)
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.trim().toLowerCase()
    res = res.filter((n: any) =>
      (n.title_bn && n.title_bn.toLowerCase().includes(q)) ||
      (n.title_en && n.title_en.toLowerCase().includes(q)) ||
      (n.content_bn && n.content_bn.toLowerCase().includes(q))
    )
  }
  return res.sort((a: any, b: any) => {
    if (a.is_pinned !== b.is_pinned) return a.is_pinned ? -1 : 1
    return new Date(b.published_at || b.created_at || '').getTime() - new Date(a.published_at || a.created_at || '').getTime()
  })
})

function toBn(n: any) {
  if (n === null || n === undefined) return ''
  return Number(n).toLocaleString('bn-BD')
}

function printPage() {
  window.print()
}

const goToPage = (page: number) => loadNotices(page)
const viewNotice = (notice: any) => navigateTo(`/notice/${notice.id}`)
const formatDate = (d: string | null | undefined) => d ? new Date(d).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'
const truncate = (text: string | null | undefined, maxLen: number) => text ? (text.length > maxLen ? text.substring(0, maxLen) + '...' : text) : ''
const getTypeIcon = (type: string) => type === 'announcement' ? 'bullhorn' : type === 'warning' ? 'alert-circle' : 'bell'

loadNotices()
</script>

<style scoped>
.notice-page { padding: 1.5rem; max-width: 1300px; margin: 0 auto; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { font-size: 1.5rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.25rem; }
.header-actions { display: flex; gap: 0.75rem; align-items: center; }

.toolbar-card { display: flex; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap; background: #fff; padding: 0.75rem 1rem; border-radius: 0.75rem; border: 1px solid var(--color-border-light); }
.search-input-wrap { display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 260px; background: var(--color-bg-muted); padding: 0.5rem 0.85rem; border-radius: 0.5rem; }
.search-input { border: none; background: transparent; outline: none; width: 100%; font-size: 0.9rem; }
.filter-select { border: 1px solid var(--color-border-light); padding: 0.5rem 0.85rem; border-radius: 0.5rem; font-size: 0.9rem; outline: none; background: #fff; }

.notice-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem; }
.notice-card { background: #fff; border: 1px solid var(--color-border-light); border-radius: 12px; padding: 1.25rem; cursor: pointer; transition: all 0.2s; border-left: 4px solid var(--color-border-light); display: flex; flex-direction: column; justify-content: space-between; }
.notice-card:hover { border-color: var(--color-border-dark); box-shadow: 0 4px 12px rgba(0,0,0,0.06); transform: translateY(-2px); }
.notice-card.pinned { border-left-color: #f59e0b; background: linear-gradient(to right, rgba(245,158,11,0.04), transparent); }

.notice-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.notice-type-badge { display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-primary); background: var(--color-bg-muted); padding: 0.2rem 0.6rem; border-radius: 99px; }
.notice-type-badge.announcement { background: #e0e7ff; color: #3730a3; }
.notice-type-badge.warning { background: #fee2e2; color: #991b1b; }
.pinned-badge { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: #b45309; font-weight: 600; }

.notice-card-body { margin-bottom: 1rem; flex: 1; }
.notice-title { font-size: 1.05rem; font-weight: 700; color: var(--color-text); margin: 0 0 0.5rem 0; line-height: 1.4; }
.notice-preview { font-size: 0.88rem; color: var(--color-text-muted); margin: 0; line-height: 1.5; }

.notice-card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--color-border-light); flex-wrap: wrap; gap: 0.5rem; }
.notice-meta { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; color: var(--color-text-muted); }
.btn-icon { width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text-muted); border-radius: 6px; cursor: pointer; transition: all 0.2s; }
.btn-icon:hover { background: var(--color-bg-hover); color: var(--color-primary); }

.pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 1.5rem; }
.page-btn { padding: 0.5rem 0.85rem; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); border-radius: 6px; cursor: pointer; transition: all 0.2s; }
.page-btn:hover:not(.active) { background: var(--color-bg-hover); border-color: var(--color-border-dark); }
.page-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }

@media print {
  .no-print { display: none !important; }
  .print-only { display: block !important; }
  .notice-card { break-inside: avoid; border: 1px solid #000; box-shadow: none; margin-bottom: 1rem; }
}
</style>
