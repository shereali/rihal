<template>
  <div class="module-page">
    <div class="page-header-row">
      <div>
        <h1>সার্টিফিকেট ও পাঠ্যক্রম</h1>
        <p class="page-subtitle">সার্টিফিকেট টেমপলেট তৈরি, পরিচালনা ও স্ট্যাটাস</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary btn-sm" @click="openCreate"><Icon name="plus" /> নতুন টেমপলেট</button>
      </div>
    </div>

    <div class="tabs card" style="margin-bottom: 1rem">
      <button v-for="tab in tabs" :key="tab.key" class="tab" :class="{ active: activeTab === tab.key }" @click="activeTab = tab.key">{{ tab.label }}</button>
    </div>

    <!-- Templates list -->
    <div v-if="loading" class="module-empty-state"><p>লোড হচ্ছে...</p></div>
    <div v-else-if="activeTab === 'templates'">
      <div v-if="!templates?.length" class="module-empty-state">
        <Icon name="file-document" class="empty-icon" />
        <h3>কোনো টেমপলেট নেই</h3>
        <p>সার্টিফিকেট টেমপলেট তৈরি করতে নিচের বাটনে ক্লিক করুন।</p>
        <button class="btn btn-primary" @click="openCreate">নতুন টেমপলেট তৈরি করুন</button>
      </div>
      <div v-else class="module-table-wrap card">
        <div class="module-table-toolbar">
          <select v-model="filters.type" class="form-control">
            <option value="">সব ধরন</option>
            <option value="general">সাধারণ</option>
            <option value="academic">একাডেমিক</option>
            <option value="graduation">গ্র্যাজুয়েশন</option>
            <option value="participation">অংশগ্রহণ</option>
          </select>
          <select v-model="filters.is_active" class="form-control">
            <option value="">সব অবস্থা</option>
            <option :value="true">সক্রিয়</option>
            <option :value="false">নিষ্ক্রিয়</option>
          </select>
        </div>
        <table class="data-table">
          <thead><tr><th>নাম (বাংলা)</th><th>নাম (ইংরেজি)</th><th>ধরন</th><th>অবস্থা</th><th></th></tr></thead>
          <tbody>
            <tr v-for="t in templates" :key="t.id">
              <td>{{ t.name_bn || '—' }}</td>
              <td class="dimmed">{{ t.name_en || '—' }}</td>
              <td><Badge :type="t.type === 'graduation' ? 'success' : t.type === 'academic' ? 'primary' : t.type === 'participation' ? 'warning' : 'info'">{{ t.type || 'general' }}</Badge></td>
              <td><StatusBadge :active="t.is_active" /></td>
              <td>
                <div class="row-actions">
                  <button class="btn-ghost btn-sm" @click="editTemplate(t)"><Icon name="edit" /> সম্পাদনা</button>
                  <button class="btn-ghost btn-sm text-danger" @click="destroy(t)" :disabled="destroying?.id === t.id"><Icon name="delete" /></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="meta" class="table-footer">
          <span v-if="meta.current_page > 1"><button class="btn btn-sm btn-outline" @click="goPage(meta.current_page - 1)">পূর্বে</button></span>
          <span>পৃষ্ঠা {{ meta.current_page }} / {{ meta.last_page }}</span>
          <span v-if="meta.current_page < meta.last_page"><button class="btn btn-sm btn-outline" @click="goPage(meta.current_page + 1)">পরে</button></span>
        </div>
      </div>
    </div>

    <!-- Create/Edit Template -->
    <div v-if="createOpen" class="module-dialog-overlay" @click.self="createOpen = false">
      <div class="module-dialog">
        <div class="module-dialog-header">
          <h1>{{ editingTemplate ? 'টেমপলেট সম্পাদনা' : 'নতুন সার্টিফিকেট টেমপলেট' }}</h1>
          <button class="close-btn" @click="createOpen = false"><Icon name="close" /></button>
        </div>
        <div class="module-dialog-body">
          <form @submit.prevent="saveTemplate">
            <div class="field"><label>নাম (বাংলা) *</label><input v-model="templateForm.name_bn" type="text" class="form-control" required placeholder="যেমন: মাদ্রাসা স্নাতক সার্টিফিকেট" /></div>
            <div class="field"><label>নাম (ইংরেজি)</label><input v-model="templateForm.name_en" type="text" class="form-control" placeholder="Madrasa Graduation Certificate" /></div>
            <div class="field"><label>ধরন</label>
              <select v-model="templateForm.type" class="form-control">
                <option value="general">সাধারণ</option>
                <option value="academic">একাডেমিক</option>
                <option value="graduation">গ্র্যাজুয়েশন</option>
                <option value="participation">অংশগ্রহণ</option>
              </select>
            </div>
            <div class="field checkbox-field"><label class="checkbox-label"><input type="checkbox" v-model="templateForm.is_active" /> ডিফল্টভাবে সক্রিয়</label></div>
            <div class="form-actions">
              <button type="button" class="btn btn-outline" @click="createOpen = false">বাতিল করুন</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving">সংরক্ষণ হচ্ছে...</span>
                <span v-else>সংরক্ষণ করুন</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <ApiAlert :message="alert" v-if="alert" />
    <ConfirmationModal v-if="confirmDelete" :title="confirmDelete.title" :message="confirmDelete.message" @confirm="doDestroy" @cancel="confirmDelete = null" />
    <div v-if="approveLoading" class="module-alert alert-info">অনুমোদন হচ্ছে...</div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'
import { useAuth } from '~/composables/useAuth'
import { useRouter } from 'vue-router'

const api = useApiClient()
const { isAuthenticated, isLoading: authLoading } = useAuth()
const router = useRouter()

const templates = ref<any[]>([])
const meta = ref<any>(null)
const loading = ref(false)
const alert = ref('')
const activeTab = ref('templates')
const createOpen = ref(false)
const editingTemplate = ref<any>(null)
const saving = ref(false)
const destroying = ref<{ id: number } | null>(null)
const confirmDelete = ref<{ id: number; title: string; message: string } | null>(null)
const filters = reactive({
  type: '',
  is_active: '',
  page: 1,
  per_page: 10,
})
const templateForm = reactive({
  name_bn: '',
  name_en: '',
  type: 'general',
  is_active: true,
})
const tabs = [
  { key: 'templates', label: 'টেমপলেট তালিকা' },
  { key: 'issued', label: 'জাত সার্টিফিকেট' },
]

function goPage(page: number) { filters.page = page; load() }

async function load() {
  loading.value = true; alert.value = ''
  try {
    const params = new URLSearchParams({
      page: String(filters.page),
      per_page: String(filters.per_page),
      ...(filters.type && { type: filters.type }),
      ...(filters.is_active !== '' && { is_active: String(filters.is_active) }),
    })
    const res = await api.get(`/certificate-templates?${params}`)
    templates.value = res.data?.data || []
    meta.value = res.data?.meta || null
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'টেমপলেট লোড করতে ত্রুটি হয়েছে।'
  } finally { loading.value = false }
}

async function openCreate() {
  editingTemplate.value = null
  templateForm.name_bn = ''
  templateForm.name_en = ''
  templateForm.type = 'general'
  templateForm.is_active = true
  createOpen.value = true
}

async function editTemplate(t: any) {
  editingTemplate.value = t
  templateForm.name_bn = t.name_bn || ''
  templateForm.name_en = t.name_en || ''
  templateForm.type = t.type || 'general'
  templateForm.is_active = t.is_active ?? true
  createOpen.value = true
}

async function saveTemplate() {
  saving.value = true; alert.value = ''
  try {
    if (editingTemplate.value) {
      await api.put(`/certificate-templates/${editingTemplate.value.id}`, templateForm)
      alert.value = 'টেমপলেট হালনাগাদ করা হয়েছে।'
    } else {
      await api.post('/certificate-templates', templateForm)
      alert.value = 'টেমপলেট তৈরি করা হয়েছে।'
    }
    createOpen.value = false
    editingTemplate.value = null
    load()
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'সংরক্ষণ করতে ত্রুটি হয়েছে।'
  } finally { saving.value = false }
}

async function destroy(t: any) {
  destroying.value = { id: t.id }
  confirmDelete.value = {
    id: t.id,
    title: 'টেমপলেট মুছে ফেলবেন?',
    message: 'এই টেমপলেটটি মুছে ফেলা হলে পুনরুদ্ধার করা যাবে না। আপনি কি ঠিক আছেন?',
  }
}

async function doDestroy() {
  if (!confirmDelete.value) return
  try {
    await api.delete(`/certificate-templates/${confirmDelete.value.id}`)
    alert.value = 'টেমপলেট মুছে ফেলা হয়েছে।'
    confirmDelete.value = null
    load()
  } catch (e: any) {
    alert.value = e.response?.data?.message || 'মুছতে ত্রুটি হয়েছে।'
  } finally { destroying.value = null }
}

onMounted(() => {
  if (!isAuthenticated.value && !authLoading.value) router.push('/login')
  load()
})
</script>
