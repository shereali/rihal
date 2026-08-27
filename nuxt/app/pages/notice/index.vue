<template>
  <div class="notice-page">
    <div class="page-header">
      <div class="header-left">
        <h1>বিজ্ঞপ্তি</h1>
        <p class="text-muted">{{ notices?.data?.meta?.total || 0 }}টি বিজ্ঞপ্তি</p>
      </div>
      <NuxtLink to="/notice/create" class="btn btn-primary">
        <icon name="plus" /> নতুন বিজ্ঞপ্তি
      </NuxtLink>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state">
          <div class="spinner" />
          <p>বিজ্ঞপ্তি লোড হচ্ছে...</p>
        </div>

        <div v-else-if="(notices?.data?.data || []).length === 0" class="empty-state">
          <p>কোনো বিজ্ঞপ্তি নেই</p>
          <NuxtLink to="/notice/create" class="btn btn-primary">প্রথম বিজ্ঞপ্তি পোস্ট করুন</NuxtLink>
        </div>

        <div v-else class="notice-grid">
          <article v-for="notice in sortedNotices" :key="notice.id" :class="{ pinned: notice.is_pinned }" @click="viewNotice(notice)">
            <div class="notice-card-header">
              <div class="notice-type-badge">
                <icon :name="getTypeIcon(notice.type)" />
                {{ notice.type === 'notice' ? 'বিজ্ঞপ্তি' : notice.type === 'announcement' ? 'ঘোষণা' : 'সতর্কবার্তা' }}
              </div>
              <div v-if="notice.is_pinned" class="pinned-badge"><icon name="pin" /> পিন করা হয়েছে</div>
              <NuxtLink :to="`/notice/${notice.id}/edit`" class="btn-icon btn-icon-sm">
                <icon name="pencil" />
              </NuxtLink>
            </div>

            <div class="notice-card-body">
              <h3 class="notice-title">{{ notice.title_bn }}</h3>
              <p class="notice-preview">{{ truncate(notice.content_bn, 120) }}</p>
            </div>

            <div class="notice-card-footer">
              <span class="notice-meta"><icon name="calendar" /> {{ formatDate(notice.published_at) }}</span>
              <span class="notice-meta"><icon name="account" /> {{ notice.creator?.name_bn || 'অজ্ঞাত' }}</span>
              <div class="notice-actions">
                <span v-if="notice.read_by_count !== undefined" class="read-count">{{ notice.read_by_count }} জন পড়েছে</span>
                <NuxtLink :to="`/notice/${notice.id}`" class="btn btn-sm btn-primary"><icon name="eye" /> দেখুন</NuxtLink>
              </div>
            </div>
          </article>
        </div>

        <div v-if="notices?.data?.meta && notices.data.meta.total > notices.data.per_page" class="pagination-wrapper">
          <div class="pagination">
            <button v-for="page in totalPages" :key="page" :class="['page-btn', { active: page === notices?.data?.current_page }]" @click="goToPage(page)">{{ page }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const notices = ref<any>(null)
const totalPages = ref(1)

async function loadNotices(page = 1) {
  loading.value = true
  try {
    const res = await api.get(`/notices?page=${page}&per_page=12&sort=-published_at`)
    notices.value = res.data
    totalPages.value = res.data.meta?.last_page || 1
  } catch (error) { console.error('Failed to load notices:', error) }
  finally { loading.value = false }
}

const sortedNotices = computed(() => {
  if (!notices.value?.data?.data) return []
  const list = [...(notices.value?.data?.data || [])]
  return list.sort((a: any, b: any) => {
    if (a.is_pinned !== b.is_pinned) return a.is_pinned ? -1 : 1
    return new Date(b.published_at || '').getTime() - new Date(a.published_at || '').getTime()
  })
})

const goToPage = (page: number) => loadNotices(page)
const viewNotice = (notice: any) => navigateTo(`/notice/${notice.id}`)
const formatDate = (d: string | null | undefined) => d ? new Date(d).toLocaleDateString('bn-BD', { day:'numeric', month:'short', year:'numeric' }) : '-'
const truncate = (text: string | null | undefined, maxLen: number) => text ? (text.length > maxLen ? text.substring(0, maxLen) + '...' : text) : ''
const getTypeIcon = (type: string) => type === 'announcement' ? 'mdiAnnouncement' : type === 'warning' ? 'mdiAlertCircle' : 'mdiBell'

loadNotices()
</script>

<style scoped>
.notice-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.header-left h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.notice-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }
.notice-card { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: var(--radius-md); padding: 1rem; cursor: pointer; transition: all 0.2s; border-left: 3px solid transparent; }
.notice-card:hover { border-color: var(--color-border-dark); box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.notice-card.pinned { border-left-color: var(--color-warning); background: linear-gradient(to right, rgba(255,193,7,0.04), transparent); }
.notice-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.notice-type-badge { display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-text-muted); }
.pinned-badge { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.7rem; color: var(--color-warning); font-weight: 500; }
.notice-card-body { margin-bottom: 0.75rem; }
.notice-title { font-size: 1rem; font-weight: 600; color: var(--color-text); margin: 0 0 0.5rem 0; line-height: 1.4; }
.notice-preview { font-size: 0.875rem; color: var(--color-text-muted); margin: 0; line-height: 1.5; }
.notice-card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--color-border-light); flex-wrap: wrap; gap: 0.5rem; }
.notice-meta { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: var(--color-text-muted); }
.read-count { font-size: 0.75rem; color: var(--color-text-muted); }
.btn-icon { width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text-muted); border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s; }
.btn-icon:hover { background: var(--color-bg-hover); color: var(--color-primary); }
.pagination { display: flex; gap: 0.5rem; justify-content: center; margin-top: 1.5rem; }
.page-btn { padding: 0.5rem 0.75rem; border: 1px solid var(--color-border); background: var(--color-bg); color: var(--color-text); border-radius: var(--radius-sm); cursor: pointer; transition: all 0.2s; }
.page-btn:hover:not(.active) { background: var(--color-bg-hover); border-color: var(--color-border-dark); }
.page-btn.active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
</style>
