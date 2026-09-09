<template>
  <div class="assignment-page">
    <div class="page-header">
      <div class="header-left">
        <h1>শিক্ষক বরাদ্দ</h1>
        <p class="text-muted">{{ assignments?.data?.total || 0 }}টি বরাদ্দ</p>
      </div>
      <div class="header-actions">
        <select v-model="filter.teacherId" class="form-select form-select-sm" @change="loadAssignments">
          <option value="">সব শিক্ষক</option>
          <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name_bn || t.name_en }}</option>
        </select>
        <NuxtLink to="/teacher-assignments/create" class="btn btn-primary btn-sm"><icon name="plus" /> নতুন বরাদ্দ</NuxtLink>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="loading-state"><div class="spinner" /><p>লোড হচ্ছে...</p></div>
        <div v-else-if="(assignments?.data?.data || []).length === 0" class="empty-state"><p>কোনো শিক্ষক বরাদ্দ নেই</p></div>
        <div v-else class="table-responsive">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>শিক্ষক</th><th>শ্রেণি</th><th>সেকশন</th><th>বিষয়</th>
                <th>বিষয়বস্তু</th><th>অবস্থা</th><th>কার্যক্রম</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="a in (assignments?.data?.data || [])" :key="a.id">
                <td>{{ a.teacher?.name_bn || a.teacher?.name_en || '-' }}</td>
                <td>{{ a.class?.name_bn || '-' }}</td>
                <td>{{ a.section?.name_bn || '-' }}</td>
                <td>{{ a.subject?.name_bn || '-' }}</td>
                <td>{{ a.topic_bn || '-' }}</td>
                <td>
                  <span class="badge" :class="a.is_active ? 'badge-success' : 'badge-secondary'">
                    {{ a.is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(a)"><icon name="delete" /></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const loading = ref(true)
const assignments = ref<any>(null)
const teachers = ref<any[]>([])
const filter = ref({ teacherId: '' })

async function loadAssignments() {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.set('per_page', '50')
    if (filter.value.teacherId) params.set('teacher_id', filter.value.teacherId)
    const res = await api.get(`/teacher-assignments?${params.toString()}`)
    assignments.value = res.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

async function loadTeachers() {
  try {
    const res = await api.get('/teachers')
    teachers.value = res.data?.data?.data || res.data?.data || []
  } catch (e) { console.error(e) }
}

const confirmDelete = (a: any) => {
  if (confirm('এই শিক্ষক বরাদ্দ মুছে ফেলতে চান?')) {
    api.delete(`/teacher-assignments/${a.id}`).then(() => loadAssignments())
  }
}

onMounted(async () => { await Promise.all([loadTeachers(), loadAssignments()]) })
</script>

<style scoped>
.assignment-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.header-left h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.header-actions { display: flex; gap: 0.5rem; align-items: center; }
.table-responsive { overflow-x: auto; }
</style>
