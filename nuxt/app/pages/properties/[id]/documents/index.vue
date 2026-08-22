<template>
  <div class="page-wrapper">
    <div class="breadcrumb">
      <NuxtLink :to="`/properties/${propertyId}`" class="breadcrumb-current">সম্পত্তির বিবরণী</NuxtLink>
      <span class="sep">/</span>
      <span class="breadcrumb-current">ডকুমেন্ট</span>
    </div>
    <div class="page-header">
      <h1>ডকুমেন্ট</h1>
      <div class="breadcrumb-back">
        <NuxtLink :to="`/properties/${propertyId}`">
          <icon name="arrow-left" /> সম্পত্তির বিবরণীতে ফিরে যান
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>ডকুমেন্ট লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!documents.length" class="empty-state">
      <div class="empty-icon"><icon name="file-text" /></div>
      <h3>কোনো ডকুমেন্ট নেই</h3>
      <p>জমির ডকুমেন্ট যোগ করুন</p>
      <button class="btn btn-primary" @click="showUpload = true">প্রথম ডকুমেন্ট আপলোড করুন</button>
    </div>

    <div v-else class="documents-grid">
      <article v-for="doc in documents" :key="doc.id" class="doc-card card">
        <div class="doc-header">
          <div class="doc-type-icon" :class="docTypeClass(doc.type)">
            <icon :name="docIcon(doc.type)" />
          </div>
          <div class="doc-info">
            <h3 class="doc-title">{{ doc.name_bn || doc.title || doc.name || 'অজানা ডকুমেন্ট' }}</h3>
            <span class="doc-meta">{{ doc.format || 'PDF' }} · {{ formatSize(doc.size) }}</span>
          </div>
          <span v-if="doc.expiry_date" class="expiry-badge">
            মেয়াদ: {{ formatDate(doc.expiry_date) }}
          </span>
        </div>
        <p v-if="doc.description_bn" class="doc-description">{{ doc.description_bn }}</p>
        <div class="doc-footer">
          <a :href="doc.url" target="_blank" class="view-link" v-if="doc.url">
            <icon name="download" /> ডাউনলোড
          </a>
          <span v-if="doc.is_verified" class="verified-badge">✓ যাচাইকৃত</span>
          <span v-else class="pending-badge">পেন্ডিং</span>
        </div>
      </article>
    </div>

    <!-- Upload Modal -->
    <div class="modal-overlay" v-if="showUpload" @click.self="showUpload = false">
      <div class="modal card">
        <div class="modal-header">
          <h2>ডকুমেন্ট আপলোড</h2>
          <button class="close-btn" @click="showUpload = false">×</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>ডকুমেন্টের নাম</label>
            <input v-model="uploadForm.name" class="form-control" placeholder="যেমন: জমির খতিয়ান" />
          </div>
          <div class="form-group">
            <label>ধরন</label>
            <select v-model="uploadForm.type" class="form-control">
              <option value="deed">খতিয়ান / জমিনামা</option>
              <option value="ownership">অধিকার প্রমাণপত্র</option>
              <option value="tax">কর ভাড়া রসিদ</option>
              <option value="utility">সেবা রসিদ (বিদ্যুৎ, পানি, গ্যাস)</option>
              <option value="approval">অনুমোদন পত্র</option>
              <option value="other">অন্যান্য</option>
            </select>
          </div>
          <div class="form-group">
            <label>লিংক ইউআরএল</label>
            <input v-model="uploadForm.url" type="url" class="form-control" placeholder="ডকুমেন্টের URL (গুগল ড্রাইভ, এক্সেল, পিডিএফ ইত্যাদি)" />
          </div>
          <div class="form-group">
            <label>মেয়াদ শেষ (যদি থাকে)</label>
            <input v-model="uploadForm.expiry_date" type="date" class="form-control" />
          </div>
          <div class="form-group">
            <label>বিবরণ</label>
            <textarea v-model="uploadForm.description" class="form-control" rows="2" placeholder="তথ্য, সম্পত্তির বিবরণ..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" @click="uploadDocument" :disabled="uploading">
            {{ uploading ? 'আপলোড হচ্ছে...' : 'ডকুমেন্ট যোগ করুন' }}
          </button>
          <button class="btn btn-ghost" @click="showUpload = false">বাতিল</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useApiClient } from '~/utils/api'

const route = useRoute()
const api = useApiClient()
const propertyId = computed(() => route.params.id as string)
const documents = ref([])
const loading = ref(true)
const showUpload = ref(false)
const uploading = ref(false)
const uploadForm = ref({
  name: '',
  type: 'deed',
  url: '',
  expiry_date: '',
  description: '',
})

onMounted(async () => {
  try {
    const r = await api.get(`/properties/${propertyId.value}/documents`)
    documents.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error('Failed to load documents:', e)
  } finally {
    loading.value = false
  }
})

function docTypeClass(type) {
  const map = {
    deed: 'type-deed',
    ownership: 'type-ownership',
    tax: 'type-tax',
    utility: 'type-utility',
    approval: 'type-approval',
    other: 'type-other',
  }
  return map[type] || 'type-other'
}

function docIcon(type) {
  const map = {
    deed: 'file-text',
    ownership: 'shield-check',
    tax: 'receipt',
    utility: 'lightning',
    approval: 'checkmark-circle',
    other: 'file',
  }
  return map[type] || 'file'
}

function formatSize(bytes) {
  if (!bytes) return '—'
  const mb = bytes / (1024 * 1024)
  if (mb > 1) return mb.toFixed(1) + ' MB'
  return (bytes / 1024).toFixed(1) + ' KB'
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('bn-BD', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

async function uploadDocument() {
  uploading.value = true
  try {
    await api.post(`/properties/${propertyId.value}/documents`, uploadForm.value)
    showUpload.value = false
    uploadForm.value = { name: '', type: 'deed', url: '', expiry_date: '', description: '' }
    await onMounted(async () => {
      const r = await api.get(`/properties/${propertyId.value}/documents`)
      documents.value = r.data?.data?.data || r.data?.data || []
    })
  } catch (e) {
    console.error('Upload failed:', e)
  } finally {
    uploading.value = false
  }
}
</script>

<style scoped>
.page-wrapper {
  max-width: 1000px;
  margin: 0 auto;
  padding: 1.5rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-bottom: 1rem;
  font-size: 0.82rem;
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.breadcrumb a {
  color: var(--color-primary);
  text-decoration: none;
}

.breadcrumb .sep {
  color: var(--color-text-muted);
}

.breadcrumb-back {
  margin-left: auto;
}

.breadcrumb-back a {
  color: var(--color-text-muted);
  font-size: 0.78rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.breadcrumb-back a:hover {
  color: var(--color-primary);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.2rem;
}

.page-header h1 {
  font-size: 1.4rem;
  color: var(--color-primary);
  font-family: var(--font-bn);
  margin: 0;
}

.loading-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 4rem 0;
}

.empty-state {
  text-align: center;
  padding: 3rem 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.7rem;
}

.empty-icon {
  width: 48px;
  height: 48px;
  color: var(--color-text-muted);
  margin-bottom: 0.5rem;
}

.empty-state h3 {
  font-family: var(--font-bn);
  font-size: 1.05rem;
  color: var(--color-text);
  margin: 0;
}

.empty-state p {
  color: var(--color-text-muted);
  font-family: var(--font-bn);
  margin: 0;
  font-size: 0.82rem;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.doc-card {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.doc-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.doc-header {
  display: flex;
  align-items: flex-start;
  gap: 0.7rem;
  padding: 1rem;
  border-bottom: 1px solid var(--color-border-light);
}

.doc-type-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.doc-type-icon.type-deed {
  background: #e6f4ec;
  color: #19724a;
}

.doc-type-icon.type-ownership {
  background: #e3f2fa;
  color: #1a5276;
}

.doc-type-icon.type-tax {
  background: #fef3e2;
  color: #a07035;
}

.doc-type-icon.type-utility {
  background: #f0eafb;
  color: #7857a9;
}

.doc-type-icon.type-approval {
  background: #e6f4ec;
  color: #19724a;
}

.doc-type-icon.type-other {
  background: #f0f0f0;
  color: #666;
}

.doc-type-icon icon {
  width: 20px;
  height: 20px;
}

.doc-info {
  flex: 1;
}

.doc-title {
  font-family: var(--font-bn);
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--color-text);
  margin: 0 0 0.2rem;
}

.doc-meta {
  font-size: 0.7rem;
  color: var(--color-text-muted);
  font-family: var(--font-bn);
}

.expiry-badge {
  font-size: 0.65rem;
  color: var(--color-text-muted);
  background: var(--color-bg-muted);
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  font-family: var(--font-bn);
  white-space: nowrap;
}

.doc-description {
  padding: 0.7rem 1rem;
  font-family: var(--font-bn);
  font-size: 0.78rem;
  color: var(--color-text-light);
  margin: 0;
  line-height: 1.5;
}

.doc-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.6rem 1rem;
  border-top: 1px solid var(--color-border-light);
}

.view-link {
  color: var(--color-primary);
  text-decoration: none;
  font-size: 0.78rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-family: var(--font-bn);
}

.view-link:hover {
  text-decoration: underline;
}

.verified-badge {
  font-size: 0.7rem;
  color: #19724a;
  background: #e6f4ec;
  padding: 0.15rem 0.5rem;
  border-radius: 99px;
  font-weight: 600;
  font-family: var(--font-bn);
}

.pending-badge {
  font-size: 0.7rem;
  color: #a05c35;
  background: #fff0e4;
  padding: 0.15rem 0.5rem;
  border-radius: 99px;
  font-weight: 600;
  font-family: var(--font-bn);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 500;
}

.modal {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 15px;
  box-shadow: var(--shadow-lg);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.2rem;
  border-bottom: 1px solid var(--color-border-light);
}

.modal-header h2 {
  font-family: var(--font-bn);
  font-size: 1.1rem;
  color: var(--color-primary);
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--color-text-muted);
  cursor: pointer;
  padding: 0;
}

.modal-body {
  padding: 1.2rem;
}

.form-group {
  margin-bottom: 0.8rem;
}

.form-group label {
  display: block;
  font-size: 0.78rem;
  color: var(--color-text-muted);
  font-weight: 600;
  margin-bottom: 0.3rem;
  font-family: var(--font-bn);
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.form-control {
  width: 100%;
  padding: 0.55rem 0.8rem;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  font-size: 0.85rem;
  font-family: var(--font-bn);
  background: white;
  color: var(--color-text);
  outline: none;
}

.form-control:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px var(--color-primary-100);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  padding: 0.8rem 1.2rem;
  border-top: 1px solid var(--color-border-light);
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.82rem;
  font-family: var(--font-bn);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  border: 1px solid transparent;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.9;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-ghost {
  background: transparent;
  color: var(--color-text-muted);
}

.btn-ghost:hover {
  background: var(--color-bg-muted);
}
</style>