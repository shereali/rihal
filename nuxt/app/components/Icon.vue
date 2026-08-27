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
  size?: number | string
}>(), {
  size: 20,
})

const iconPaths: Record<string, string> = {
  // Navigation & Chevrons
  arrowLeft: 'M19 12H5M12 19l-7-7 7-7',
  arrowRight: 'M5 12h14M12 5l7 7-7 7',
  chevronDown: 'M6 9l6 6 6-6',
  chevronUp: 'M18 15l-6-6-6 6',
  chevronRight: 'M9 18l6-6-6-6',
  chevronLeft: 'M15 18l-6-6 6-6',

  // Actions & Editing
  pencil: 'M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z M15.5 9.5l-2-2',
  delete: 'M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2',
  close: 'M18 6 6 18M6 6l12 12',
  save: 'M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z M17 8h4v4H8',
  plus: 'M12 5v14M5 12h14',
  loader: 'M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83',
  search: 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm10 2-4.35-4.35',
  refresh: 'M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15',
  play: 'M5 3l14 9-14 9V3z',
  pause: 'M6 4h4v16H6V4zm8 0h4v16h-4V4z',

  // Status & Notifications
  check: 'M20 6L9 17l-5-5',
  checkCircle: 'M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4L12 14.01l-3-3',
  closeCircle: 'M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm3.5-13.5l-7 7m0-7l7 7',
  alertCircle: 'M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20zM12 8v4M12 16h.01',
  bell: 'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0',
  announcement: 'M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9m-1.9 0c0-4.42-3.58-8-8-8',
  cancel: 'M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20z M4.93 4.93l14.14 14.14',
  clock: 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Z M12 6v6l4 2',
  clockOutline: 'M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-14v6l4 2',
  lock: 'M19 11H5a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2Z M7 11V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v6',
  pin: 'M12 17v5M5 17h14l-2-6V4h1V2H6v2h1v7l-2 6z',

  // Visibility & Links
  eye: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z',
  eyeOff: 'M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24M1 1l22 22',
  external: 'M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 13H4v6',

  // Domain & Entity Icons
  account: 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
  users: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
  calendar: 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z',
  book: 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V6a2 2 0 0 0-2-2H6.5A2.5 2.5 0 0 0 4 6.5Z',
  tag: 'M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.83Z M7 7h.01',
  chart: 'M18 20V10M12 20V4M6 20v-6',
  building: 'M3 21h18M5 21V7l8-4v18M13 11h4M13 15h4M13 19h4M9 9H7M9 13H7M9 17H7',
  bus: 'M4 16h16V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v10zm0 0v3a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-3zm12 0v3a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-3zM4 10h16',
  printer: 'M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z',
  seat: 'M4 7h5v9H4M15 7h5v9h-5M7 11h10v2M7 14h10v2M5 6v2M19 6v2',
  child: 'M12 4a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 7a5 5 0 0 1 10 0v4H7v-4zm-2 9h14',
  cash: 'M2 6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6zm8 6a2 2 0 1 0 4 0 2 2 0 0 0-4 0z',
  assignment: 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2',
  sun: 'M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10z',
  moon: 'M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z',
  mosque: 'M12 2l4 5v13H8V7l4-5zm-7 8h2v10H5V10zm12 0h2v10h-2V10z',
  chat: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z',
  bot: 'M12 2a2 2 0 0 0-2 2v1H8a4 4 0 0 0-4 4v7a4 4 0 0 0 4 4h8a4 4 0 0 0 4-4V9a4 4 0 0 0-4-4h-2V4a2 2 0 0 0-2-2z M9 13h.01M15 13h.01M9 17h6',
  invoice: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8',
  whatsapp: 'M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z',
  settings: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1Z',
  logout: 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9',
}

const aliases: Record<string, string> = {
  // Legacy / MDI aliases
  mdiArrowLeft: 'arrowLeft',
  mdiArrowRight: 'arrowRight',
  'arrow-left': 'arrowLeft',
  'arrow-right': 'arrowRight',
  'chevron-down': 'chevronDown',
  'chevron-up': 'chevronUp',
  'chevron-right': 'chevronRight',
  'chevron-left': 'chevronLeft',
  mdiPencil: 'pencil',
  edit: 'pencil',
  mdiDelete: 'delete',
  trash: 'delete',
  mdiClose: 'close',
  mdiContentSave: 'save',
  mdiLoad: 'loader',
  mdiEye: 'eye',
  'eye-off': 'eyeOff',
  mdiCheckCircle: 'checkCircle',
  'check-circle': 'checkCircle',
  mdiCloseCircle: 'closeCircle',
  'close-circle': 'closeCircle',
  mdiPushPin: 'pin',
  mdiCalendar: 'calendar',
  mdiAccount: 'account',
  user: 'account',
  students: 'account',
  users: 'users',
  mdiSeat: 'seat',
  mdiPrinter: 'printer',
  mdiPlus: 'plus',
  'user-plus': 'plus',
  mdiClock: 'clock',
  mdiClockOutline: 'clockOutline',
  'clock-outline': 'clockOutline',
  mdiOpenInNew: 'external',
  mdiBell: 'bell',
  mdiAnnouncement: 'announcement',
  notice: 'announcement',
  message: 'announcement',
  mdiAlertCircle: 'alertCircle',
  alert: 'alertCircle',
  mdiTag: 'tag',
  mdiLock: 'lock',
  mdiCheck: 'check',
  mdiCancel: 'cancel',
  mdiBook: 'book',
  academic: 'book',
  attendance: 'calendar',
  exam: 'assignment',
  money: 'cash',
  fees: 'cash',
  wallet: 'cash',
  donor: 'account',
  dashboard: 'chart',
  layers: 'book',
  
  // New Missing Icon Aliases
  'document-text': 'document',
  'account-group': 'users',
  'cloud-upload': 'upload',
  'file-text': 'document',
  'file-download': 'download',
  'user-circle': 'account',
  'alert-circle': 'alertCircle',
  'clipboard-list': 'assignment',
  'chart-bar': 'chart',
  'map-marker': 'pin',
  'x': 'close',
  'heart-multiple': 'heart',
  'cash-multiple': 'cash',
  'cash-plus': 'cash',
  'cash-minus': 'cash',
  'calendar-check': 'calendar',
  'user-check': 'account',
  'tools': 'wrench',
  'account-plus': 'plus',
  'account-multiple': 'users',
  'clock-outline': 'clockOutline',
  'check-circle-outline': 'checkCircle',
  'magnify': 'search',
  'close-circle-outline': 'closeCircle',
  'eye-outline': 'eye',
  'flask-empty-outline': 'alertCircle',
  'flask-empty': 'alertCircle',
  'text-box-search-outline': 'document',
  'router-wireless': 'wifi',
  'card-account-details-outline': 'account',
  'card-account-details': 'account',
  'sync': 'refresh',
  'circle-medium': 'alertCircle',
  'account-check': 'account',
  'account-cancel': 'delete',
  'router-network': 'wifi',
  'loading': 'loader',
  'clipboard-check-outline': 'assignment',
  'arrow-right': 'arrowRight',
  'open-in-new': 'external',
  'account-group-outline': 'users',
  'cash-clock': 'cash',
  'chart-timeline-variant': 'chart',
  'clipboard-text-clock-outline': 'assignment',
  'google-classroom': 'building',
}

const additionalPaths: Record<string, string> = {
  document: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8',
  fingerprint: 'M12 11v2m-4-2v2m8-2v2M4.93 4.93a10 10 0 1 1 14.14 0 M1 12h2M21 12h2',
  upload: 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12',
  list: 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
  download: 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3',
  timer: 'M12 2v2M12 14v-4M2 12A10 10 0 1 0 22 12A10 10 0 1 0 2 12ZM19.07 4.93l-1.41 1.41',
  star: 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
  award: 'M12 15l-3.5 2 1-4-3-2.5 4-.5 1.5-4 1.5 4 4 .5-3 2.5 1 4z M5.82 20.25l-2.07 1.34V15.5l1.62 1.32M18.18 20.25l2.07 1.34V15.5l-1.62 1.32',
  trophy: 'M8 21h8M12 17v4M7 4h10M4 4h3v5a5 5 0 0 0 5 5h0a5 5 0 0 0 5-5V4h3c1.1 0 2 .9 2 2v1c0 3-2 5-5 5H5c-3 0-5-2-5-5V6c0-1.1.9-2 2-2z',
  'folder-plus': 'M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2v11z M12 11v6 M9 14h6',
  'shield-check': 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z M9 12l2 2 4-4',
  heart: 'M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z',
  repeat: 'M17 1l4 4-4 4 M3 11V9a4 4 0 0 1 4-4h14 M7 23l-4-4 4-4 M21 13v2a4 4 0 0 1-4 4H3',
  phone: 'M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z',
  wrench: 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z',
  percent: 'M19 5L5 19 M6.5 6.5h.01 M17.5 17.5h.01 M6.5 6.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z M17.5 17.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z',
  undo: 'M3 7v6h6 M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13',
  wifi: 'M12 20h.01 M5 12.22a10 10 0 0 1 14 0 M8.53 15.76a5 5 0 0 1 6.94 0 M1 8.68a16 16 0 0 1 22 0',
}

const path = computed(() => {
  if (!props.name) return ''
  
  let name = props.name.replace(/^mdi:/, '') // Strip mdi:
  
  let key = aliases[name] || name
  let p = iconPaths[key] || iconPaths[name] || additionalPaths[key] || additionalPaths[name]
  
  if (!p) {
    // try camelCase
    const camelCase = name.replace(/-([a-z])/g, (g) => g[1].toUpperCase())
    key = aliases[camelCase] || camelCase
    p = iconPaths[key] || iconPaths[camelCase] || additionalPaths[key] || additionalPaths[camelCase]
  }
  
  return p || ''
})
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
