<template>
  <div class="notice-show">
    <div class="page-header">
      <NuxtLink to="/notice" class="btn btn-outline btn-sm">
        <icon name="arrow-left" /> ফিরে যান
      </NuxtLink>
      <div class="header-actions">
        <NuxtLink :to="`/notice/${notice?.id}/edit`" class="btn btn-primary btn-sm">
          <icon name="pencil" /> সম্পাদনা
        </NuxtLink>
        <button class="btn btn-outline-danger btn-sm" @click="confirmDelete">
          <icon name="delete" /> মুছুন
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner" /><p>বিজ্ঞপ্তি লোড হচ্ছে...</p></div>

    <div v-else-if="!notice" class="empty-state">
      <p>বিজ্ঞপ্তি পাওয়া যায়নি</p>
      <NuxtLink to="/notice" class="btn btn-primary">বিজ্ঞপ্তি তালিকায় ফিরে যান</NuxtLink>
    </div>

    <div v-else class="notice-article">
      <header class="notice-header">
        <div class="notice-meta-top">
          <span class="badge" :class="getTypeBadge(notice.type)">
            {{ notice.type === 'notice' ? 'বিজ্ঞপ্তি' : notice.type === 'announcement' ? 'ঘোষণা' : 'সতর্কবার্তা' }}
          </span>
          <span v-if="notice.priority" class="badge" :class="getPriorityBadge(notice.priority)">
            {{ { urgent:'জরুরি', high:'উচ্চ', normal:'সাধারণ', low:'নিম্ন' }[notice.priority] || 'সাধারণ' }}
          </span>
          <span v-if="notice.is_pinned" class="pinned-badge"><icon name="pin" /> পিন করা</span>
        </div>
        <h1 class="notice-title">{{ notice.title_bn }}</h1>
        <p v-if="notice.title_en && notice.title_en !== notice.title_bn" class="notice-title-en">{{ notice.title_en }}</p>
        <p class="notice-meta-bottom">
          <span><icon name="account" /> {{ notice.creator?.name_bn || 'অজ্ঞাত' }}</span>
          <span v-if="notice.read_by_count !== undefined"><icon name="eye" /> {{ notice.read_by_count }} জন পড়েছে</span>
        </p>
      </header>

      <div class="notice-body">
        <div class="content-section">
          <h3 class="content-heading">বিবরণ</h3>
          <div class="content-content">
            <p class="content-text">{{ notice.content_bn }}</p>
          </div>
        </div>
        <div v-if="notice.content_en && notice.content_en !== notice.content_bn" class="content-section">
          <h3 class="content-heading">Description (English)</h3>
          <div class="content-content">
            <p class="content-text">{{ notice.content_en }}</p>
          </div>
        </div>
      </div>

      <footer class="notice-footer">
        <div class="notice-footer-info">
          <span><icon name="calendar" /> প্রকাশিত: {{ formatDateTime(notice.published_at) }}</span>
          <span v-if="notice.scheduled_at"><icon name="clock" /> নির্ধারিত: {{ formatDate(notice.scheduled_at) }}</span>
          <span v-if="notice.expired_at"><icon name="cancel" /> মেয়াদ শেষ: {{ formatDate(notice.expired_at) }}</span>
          <span><icon name="tag" /> বিভাগ: {{ notice.category || 'সাধারণ' }}</span>
          <span><icon name="lock" /> অবস্থা: {{ notice.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
        </div>
        <div v-if="notice.target_audience?.length" class="notice-audience">
          <h4 class="audience-heading">লক্ষ্য শ্রোতা</h4>
          <div class="audience-tags">
            <span v-for="(aud, idx) in notice.target_audience" :key="idx" class="badge badge-outline">{{ aud }}</span>
          </div>
        </div>
      </footer>
    </div>

    <div v-if="notice.attachments?.length" class="card mt-4">
      <div class="card-header"><h3>সংযুক্ত ফাইল</h3></div>
      <div class="card-body">
        <div class="attachments-grid">
          <a v-for="file in notice.attachments" :key="file" :href="file" target="_blank" class="attachment-item" download>
            <icon name="external" /><span class="attachment-name">{{ file.split('/').pop() }}</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const loading = ref(true)
const notice = ref<any>(null)

async function loadNotice() {
  loading.value = true
  try {
    const res = await api.get(`/notices/${route.params.id}`)
    notice.value = res.data.data
  } catch (error) { console.error('Failed to load notice:', error) }
  finally { loading.value = false }
}

const confirmDelete = () => {
  if (confirm(`"${notice.value?.title_bn}" বিজ্ঞপ্তিটি মুছে ফেলতে চান?`)) {
    api.delete(`/notices/${notice.value?.id}`).then(() => navigateTo('/notice'))
  }
}

const formatDate = (d: string | null | undefined) => d ? new Date(d).toLocaleDateString('bn-BD', { day:'numeric', month:'short', year:'numeric' }) : '-'
const formatDateTime = (t: string | null | undefined) => t ? new Date(t).toLocaleString('bn-BD', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '-'
const getTypeBadge = (t: string) => ({ announcement:'badge-dark', warning:'badge-danger', notice:'badge-info' }[t] || 'badge-secondary')
const getPriorityBadge = (p: string) => ({ urgent:'badge-danger', high:'badge-warning', normal:'badge-info', low:'badge-secondary' }[p] || 'badge-outline')

onMounted(() => { loadNotice() })
</script>

<style scoped>
.notice-show { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.header-actions { display: flex; gap: 0.5rem; }
.notice-article { background: var(--color-bg-card); border: 1px solid var(--color-border-light); border-radius: var(--radius-md); overflow: hidden; }
.notice-header { padding: 1.5rem; background: var(--color-bg); border-bottom: 1px solid var(--color-border-light); }
.notice-meta-top { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.75rem; }
.pinned-badge { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; color: var(--color-warning); font-weight: 500; }
.notice-title { font-size: 1.5rem; font-weight: 700; color: var(--color-text); margin: 0 0 0.5rem 0; line-height: 1.3; }
.notice-title-en { font-size: 1rem; color: var(--color-text-muted); margin: 0 0 0.75rem 0; font-style: italic; }
.notice-meta-bottom { display: flex; gap: 1rem; font-size: 0.875rem; color: var(--color-text-muted); margin-top: 1rem; }
.notice-meta-bottom span { display: inline-flex; align-items: center; gap: 0.375rem; }
.notice-body { padding: 1.5rem; }
.content-section { margin-bottom: 1.5rem; }
.content-section:last-child { margin-bottom: 0; }
.content-heading { font-size: 1rem; font-weight: 600; color: var(--color-text); margin: 0 0 0.75rem 0; }
.content-content { background: var(--color-bg); padding: 1rem; border-radius: var(--radius-sm); }
.content-text { font-size: 0.9375rem; line-height: 1.7; color: var(--color-text); margin: 0; white-space: pre-wrap; }
.notice-footer { padding: 1.5rem; background: var(--color-bg); border-top: 1px solid var(--color-border-light); }
.notice-footer-info { display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 1rem; }
.notice-footer-info span { display: inline-flex; align-items: center; gap: 0.375rem; }
.notice-audience { padding-top: 1rem; border-top: 1px solid var(--color-border-light); }
.audience-heading { font-size: 0.875rem; font-weight: 600; color: var(--color-text); margin: 0 0 0.5rem 0; }
.audience-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.attachments-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.attachment-item {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.5rem 0.75rem; background: var(--color-bg);
  border: 1px solid var(--color-border-light); border-radius: var(--radius-sm);
  color: var(--color-primary); text-decoration: none; font-size: 0.875rem;
}
.attachment-item:hover { background: var(--color-bg-hover); border-color: var(--color-primary); }
.attachment-name { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
