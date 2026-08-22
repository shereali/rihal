<template>
  <div class="page-wrapper">
    <div class="page-header-row">
      <div>
        <h1>হোস্টেল কক্ষ</h1>
        <p class="page-subtitle">কক্ষ, ধারণক্ষমতা, আবাসিক অবস্থা ও ওয়ার্ডেন পরিচালনা করুন</p>
      </div>
      <div class="header-actions">
        <NuxtLink to="/hostel/rooms/create" class="btn btn-primary">
          <icon name="plus" /> নতুন কক্ষ
        </NuxtLink>
      </div>
    </div>

    <div v-if="loading" class="loading-overlay">
      <div class="spinner" />
      <p>কক্ষ লোড হচ্ছে...</p>
    </div>

    <div v-else-if="!rooms.length" class="empty-state">
      <div class="empty-icon"><icon name="building" /></div>
      <h3>কোনো কক্ষ নেই</h3>
      <p>নতুন কক্ষ তৈরি করে শুরু করুন</p>
      <NuxtLink to="/hostel/rooms/create" class="btn btn-primary">প্রথম কক্ষ তৈরি করুন</NuxtLink>
    </div>

    <div v-else class="rooms-grid">
      <article v-for="room in rooms" :key="room.id" class="room-card">
        <div class="room-header">
          <div class="room-number-badge">
            <span class="room-number">{{ room.room_number }}</span>
            <span v-if="room.block" class="room-block">{{ room.block }}</span>
          </div>
          <span class="room-status" :class="room.is_available ? 'available' : 'occupied'">
            {{ room.is_available ? 'খালি' : (room.current_occupancy >= room.capacity ? 'পূর্ণ' : 'আংশিক') }}
          </span>
        </div>
        <div class="room-illustration">
          <div class="occupancy-bar">
            <div class="occupancy-fill" :style="{ width: room.occupancy + '%' }" />
          </div>
          <span class="occupancy-label">{{ room.current_occupancy || 0 }} / {{ room.capacity || 1 }} জন</span>
        </div>
        <div class="room-attributes">
          <div v-if="room.floor" class="attr-item">
            <icon name="home" />
            <span>তলা {{ room.floor }}</span>
          </div>
          <div v-if="room.monthly_rent" class="attr-item">
            <icon name="money" />
            <span>{{ room.monthly_rent.toLocaleString('bn-BD') }} টাকা/মাস</span>
          </div>
          <div v-if="room.amenities?.length" class="attr-item">
            <icon name="check" />
            <span>{{ room.amenities.slice(0, 3).join(', ') }}</span>
          </div>
        </div>
        <div v-if="room.warden" class="room-warden">
          <span class="warden-label">ওয়ার্ডেন:</span>
          <span class="warden-name">{{ room.warden.name_bn || room.warden.name_en }}</span>
        </div>
        <div class="room-footer">
          <NuxtLink :to="`/hostel/rooms/${room.id}`" class="view-link">
            বিস্তারিত <icon name="arrow-right" />
          </NuxtLink>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useApiClient } from '~/utils/api'

const api = useApiClient()
const rooms = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const r = await api.get('/hostel-rooms')
    rooms.value = r.data?.data?.data || r.data?.data || []
  } catch (e) {
    console.error('Failed to load rooms:', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page-wrapper {
  max-width: 1300px;
  margin: 0 auto;
  padding: 1.5rem;
}

.page-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

h1 {
  font-size: 1.7rem;
  color: var(--color-primary);
  font-family: var(--font-bn);
  margin: 0 0 0.3rem;
}

.page-subtitle {
  color: var(--color-text-light);
  font-family: var(--font-bn);
  margin: 0;
  font-size: 0.9rem;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 10px;
  font-family: var(--font-bn);
  font-weight: 600;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  cursor: pointer;
  text-decoration: none;
  border: 1px solid transparent;
  transition: all 0.15s ease;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
}

.btn-primary:hover {
  opacity: 0.9;
  transform: translateY(-1px);
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
  padding: 4rem 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.7rem;
}

.empty-icon {
  width: 56px;
  height: 56px;
  color: var(--color-text-muted);
  margin-bottom: 0.5rem;
}

.empty-state h3 {
  font-family: var(--font-bn);
  font-size: 1.1rem;
  margin: 0;
  color: var(--color-text);
}

.empty-state p {
  color: var(--color-text-muted);
  font-family: var(--font-bn);
  margin: 0;
}

.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1rem;
}

.room-card {
  background: white;
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.room-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-md);
}

.room-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1rem 0.6rem;
  border-bottom: 1px solid var(--color-border-light);
}

.room-number-badge {
  display: flex;
  align-items: baseline;
  gap: 0.4rem;
}

.room-number {
  font-family: var(--font-bn);
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--color-primary);
}

.room-block {
  font-size: 0.72rem;
  color: var(--color-text-muted);
  background: var(--color-bg-muted);
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
}

.room-status {
  padding: 0.18rem 0.5rem;
  border-radius: 99px;
  font-size: 0.65rem;
  font-weight: 600;
  white-space: nowrap;
}

.room-status.available {
  background: #e6f4ec;
  color: #19724a;
}

.room-status.occupied {
  background: #fff0e4;
  color: #a05c35;
}

.room-illustration {
  padding: 0.8rem 1rem;
  border-bottom: 1px solid var(--color-border-light);
  text-align: center;
}

.occupancy-bar {
  height: 8px;
  background: var(--color-bg-muted);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.3rem;
}

.occupancy-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--color-primary), var(--color-primary));
  border-radius: 4px;
  transition: width 0.4s ease;
}

.occupancy-label {
  font-family: var(--font-bn);
  font-size: 0.7rem;
  color: var(--color-text-muted);
}

.room-attributes {
  padding: 0.6rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.attr-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-family: var(--font-bn);
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

.attr-item icon {
  width: 14px;
  height: 14px;
}

.room-warden {
  padding: 0.4rem 1rem 0.6rem;
  font-family: var(--font-bn);
  font-size: 0.75rem;
  display: flex;
  gap: 0.3rem;
  align-items: center;
}

.warden-label {
  color: var(--color-text-muted);
}

.warden-name {
  color: var(--color-text);
  font-weight: 500;
}

.room-footer {
  padding: 0.6rem 1rem 0.8rem;
}

.view-link {
  color: var(--color-primary);
  text-decoration: none;
  font-family: var(--font-bn);
  font-size: 0.8rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.view-link:hover {
  opacity: 0.8;
}

@media (max-width: 768px) {
  .page-header-row {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>