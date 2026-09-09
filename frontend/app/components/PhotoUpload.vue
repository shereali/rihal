<template>
  <div class="photo-upload">
    <label class="upload-box" :class="{ dragging }" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="onDrop">
      <input type="file" accept="image/jpeg,image/png,image/webp" hidden @change="onSelect" />
      <img v-if="modelValue" :src="modelValue" alt="আপলোড করা ছবি" class="preview" />
      <div v-else class="placeholder">
        <icon name="plus" />
        <strong>ছবি নির্বাচন বা এখানে ছেড়ে দিন</strong>
        <span>JPG, PNG বা WebP — সর্বোচ্চ ৫ MB</span>
      </div>
    </label>
    <div v-if="uploading" class="progress">ছবি আপলোড হচ্ছে...</div>
    <div v-if="error" class="error">{{ error }}</div>
    <button v-if="modelValue" type="button" class="remove" @click="removePhoto">ছবি সরান</button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useApiClient } from '~/utils/api'

const props = defineProps<{ modelValue: string }>()
const emit = defineEmits<{ (event: 'update:modelValue', value: string): void }>()
const api = useApiClient()
const uploading = ref(false)
const dragging = ref(false)
const error = ref('')
const uploadedPath = ref('')

function onSelect(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (file) upload(file)
}
function onDrop(event: DragEvent) {
  dragging.value = false
  const file = event.dataTransfer?.files?.[0]
  if (file) upload(file)
}
async function upload(file: File) {
  error.value = ''
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) {
    error.value = 'JPG, PNG বা WebP ছবি দিন; সর্বোচ্চ ৫ MB।'
    return
  }
  uploading.value = true
  try {
    const body = new FormData()
    body.append('file', file)
    const response = await api.post('/uploads/photos', body, { headers: { 'Content-Type': 'multipart/form-data' } })
    const previousPath = uploadedPath.value
    uploadedPath.value = response.data?.data?.path || ''
    emit('update:modelValue', response.data?.data?.url || '')
    if (previousPath) {
      await api.delete('/uploads/photos', { data: { path: previousPath } }).catch(() => undefined)
    }
  } catch (exception: any) {
    error.value = exception?.response?.data?.message || 'ছবি আপলোড করা যায়নি।'
  } finally {
    uploading.value = false
  }
}

async function removePhoto() {
  error.value = ''
  const path = uploadedPath.value
  emit('update:modelValue', '')
  uploadedPath.value = ''
  if (!path) return
  try {
    await api.delete('/uploads/photos', { data: { path } })
  } catch (exception: any) {
    error.value = exception?.response?.data?.message || 'আপলোড করা ছবি মুছে ফেলা যায়নি।'
  }
}
</script>

<style scoped>
.photo-upload{display:grid;gap:.55rem}.upload-box{display:block;min-height:170px;border:2px dashed #b9c9be;border-radius:16px;background:#f8faf8;cursor:pointer;overflow:hidden;transition:.2s}.upload-box:hover,.upload-box.dragging{border-color:#145032;background:#eef6f1}.preview{display:block;width:100%;height:220px;object-fit:cover}.placeholder{min-height:170px;display:grid;place-items:center;align-content:center;gap:.45rem;color:#4d6556;text-align:center}.placeholder :deep(svg){width:28px;height:28px}.placeholder span{font-size:.78rem;color:#7b8b81}.progress{color:#145032;font-size:.84rem}.error{color:#a9322c;font-size:.84rem}.remove{justify-self:start;border:0;background:none;color:#a9322c;text-decoration:underline;cursor:pointer;padding:0}
</style>
