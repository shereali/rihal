<template>
  <span class="icon-wrapper" :class="[`icon-${name}`]">
    <svg
      v-if="path"
      :width="size"
      :height="size"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
      aria-hidden="true"
    >
      <path :d="path" />
    </svg>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  name: string
  size?: number
}>(), {
  size: 24,
})

const iconPaths: Record<string, string> = {
  mdiArrowLeft: 'M19 12H5M12 19l-7-7 7-7',
  mdiPencil: 'M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z M15.5 9.5l-2-2',
  mdiDelete: 'M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2',
  mdiClose: 'M18 6 6 18M6 6l12 12',
  mdiContentSave: 'M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z M17 8h4v4H8',
  mdiLoad: 'M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83',
  mdiEye: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z',
  mdiCheckCircle: 'M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4L12 14.01l-3-3',
  mdiPushPin: 'M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V18a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7Z M12 2a7 7 0 0 1 7 7c0 2.38-1.19 4.47-3 5.74V18a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 0 1 7-7Z',
  mdiCalendar: 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z',
  mdiAccount: 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
  mdiSeat: 'M4 7h5v9H4M15 7h5v9h-5M7 11h10v2M7 14h10v2M5 6v2M19 6v2',
  mdiPrinter: 'M19 11h-3v3h-2v-3H8v-2h3V4h2v3h3v2Z M6 10h2v8H6Z',
  mdiPlus: 'M12 5v14M5 12h14',
  mdiClock: 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Z M12 6v6l4 2',
  mdiOpenInNew: 'M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 13H4v6',
  mdiBell: 'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0',
  mdiAnnouncement: 'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9m-1.9 0c0-4.42-3.58-8-8-8',
  mdiAlertCircle: 'M12 2A10 10 0 0 0 2 12s8 4 8 10a14 14 0 0 1-1.33 2.58 M12 22a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z M12 12m-3 3a3 3 0 1 0 6 0',
  mdiTag: 'M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83Z M7 7h.01',
  mdiLock: 'M19 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2Z M7 11V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v6',
  mdiCheck: 'M20 6L9 17l-5-5',
  mdiCancel: 'M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2',
  mdiBook: 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V6a2 2 0 0 0-2-2H6.5A2.5 2.5 0 0 0 4 6.5Z',
}

const aliases: Record<string, string> = {
  arrowLeft: 'mdiArrowLeft', arrowRight: 'mdiOpenInNew',
  pencil: 'mdiPencil', delete: 'mdiDelete', close: 'mdiClose',
  save: 'mdiContentSave', loader: 'mdiLoad', eye: 'mdiEye',
  pin: 'mdiPushPin', calendar: 'mdiCalendar', account: 'mdiAccount',
  plus: 'mdiPlus', clock: 'mdiClock', external: 'mdiOpenInNew',
  bell: 'mdiBell', notice: 'mdiAnnouncement', alert: 'mdiAlertCircle',
  tag: 'mdiTag', lock: 'mdiLock', check: 'mdiCheck', cancel: 'mdiCancel',
  search: 'mdiEye', refresh: 'mdiLoad', 'chevron-down': 'mdiArrowLeft',
  'chevron-right': 'mdiOpenInNew', message: 'mdiAnnouncement',
  sun: 'mdiPushPin', moon: 'mdiLock', mosque: 'mdiPushPin',
  attendance: 'mdiCalendar', students: 'mdiAccount', users: 'mdiAccount',
  exam: 'mdiAnnouncement', money: 'mdiTag', chart: 'mdiOpenInNew',
  academic: 'mdiBook', book: 'mdiBook', 'user-plus': 'mdiPlus',
 wallet: 'mdiTag', bus: 'mdiCalendar', building: 'mdiBook', layers: 'mdiBook',
 chat: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',
 bot: 'M12 2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H12z M12 14a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2h-4z M12 11a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-2z',
 invoice: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8',
 whatsapp: 'M22 4H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z M6 10V8a4 4 0 0 1 4-4h.5A4 4 0 1 1 11 14.5V17l-2 2v-2h-2l-2 2H4a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-2l-2-2v2l-2-2Z',
 }

const path = computed(() => iconPaths[props.name] || iconPaths[aliases[props.name]] || '')
</script>

<style scoped>
.icon-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  vertical-align: middle;
  line-height: 1;
  flex-shrink: 0;
}
.icon-wrapper :deep(svg) {
  display: block;
  color: inherit;
}
</style>
