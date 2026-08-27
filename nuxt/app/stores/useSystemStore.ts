import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useSystemStore = defineStore('system', () => {
  const globalSettings = ref<any>(null)
  const academicClasses = ref<any[]>([])
  const isLoaded = ref(false)

  const fetchInitialData = async () => {
    if (isLoaded.value) return;

    try {
      const api = useApiClient()
      
      // Attempt to load critical data simultaneously
      const [settingsRes, classesRes] = await Promise.all([
        api.get('/settings').catch(() => ({ data: {} })),
        api.get('/settings/classes').catch(() => ({ data: [] }))
      ])

      globalSettings.value = settingsRes.data?.data || settingsRes.data
      academicClasses.value = classesRes.data?.data || classesRes.data
      isLoaded.value = true
    } catch (err) {
      console.error('Failed to load system defaults', err)
    }
  }

  return {
    globalSettings,
    academicClasses,
    isLoaded,
    fetchInitialData
  }
})
