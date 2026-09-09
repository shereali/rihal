<template>
  <Teleport to="body">
    <Transition name="floating">
      <div v-if="open" class="ai-float-overlay" @click.self="closeChat">
        <div class="ai-chat-popover" ref="chatPopoverRef" role="dialog" aria-modal="true" aria-label="আর্টিফিশিয়াল ইন্টেলিজেন্স সহায়ক">
          <div class="ai-chat-header">
            <div>
              <div class="ai-chat-title">
                <Icon name="bot" size="18" />
                <span>আর্টিফিশিয়াল ইন্টেলিজেন্স সহায়ক</span>
              </div>
              <small class="text-muted">সমস্যা আছে? আমাকে জিজ্ঞাসা করুন</small>
            </div>
            <button class="ai-close-btn" type="button" aria-label="AI সহায়ক বন্ধ করুন" @click="closeChat">
              <Icon name="close" size="18" />
            </button>
          </div>
          <div ref="chatBodyRef" class="ai-chat-body">
            <div class="ai-message ai-message-bot">
              <p>হ্যালো! আমি রিহালের আর্টিফিশিয়াল ইন্টেলিজেন্স সহায়ক। আপনাকে কীভাবে সাহায্য করতে পারি?</p>
            </div>
            <div v-for="(msg, i) in chatHistory" :key="i" class="ai-message" :class="msg.role === 'user' ? 'ai-message-user' : 'ai-message-bot'">
              <p>{{ msg.text }}</p>
            </div>
          </div>
          <div class="ai-chat-footer">
            <div class="ai-input-wrap">
              <input
                ref="chatInputRef"
                v-model="userMessage"
                type="text"
                class="ai-input"
                aria-label="AI সহায়ককে বার্তা লিখুন"
                placeholder="এখানে লিখুন..."
                @keyup.enter="sendMessage"
              />
              <button class="ai-send-btn" type="button" aria-label="বার্তা পাঠান" @click="sendMessage" :disabled="!userMessage.trim()">
                <Icon name="chat" size="18" />
              </button>
            </div>
            <div class="ai-hint">
              <small>AI প্রস্তাবনা, আসল AI সংযোগ শীঘ্রই আসছে</small>
            </div>
          </div>
        </div>
      </div>
    </Transition>
    <button
      class="ai-float-btn"
      type="button"
      :class="{ 'ai-float-btn--active': open }"
      aria-label="AI সহায়ক"
      :aria-expanded="open"
      @click="open ? closeChat() : openChat()"
    >
      <span class="ai-float-icon">
        <Icon name="bot" size="22" />
      </span>
      <span class="ai-badge">AI</span>
    </button>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'

const open = ref(false)
const userMessage = ref('')
const chatBodyRef = ref<HTMLElement | null>(null)
const chatInputRef = ref<HTMLInputElement | null>(null)
const chatPopoverRef = ref<HTMLElement | null>(null)
const chatHistory = ref<{ role: 'bot' | 'user', text: string }[]>([])
let lastFocused: HTMLElement | null = null

function scrollToBottom() {
  nextTick(() => {
    if (chatBodyRef.value) {
      chatBodyRef.value.scrollTop = chatBodyRef.value.scrollHeight
    }
  })
}

function openChat() {
  lastFocused = document.activeElement as HTMLElement | null
  open.value = true
  nextTick(() => chatInputRef.value?.focus())
}

function closeChat() {
  open.value = false
  // Return focus to the floating trigger once the overlay has left the DOM
  nextTick(() => {
    lastFocused?.focus?.()
    lastFocused = null
  })
}

function sendMessage() {
  if (!userMessage.value.trim()) return
  chatHistory.value.push({ role: 'user', text: userMessage.value.trim() })
  userMessage.value = ''
  scrollToBottom()
  // Simulate a bot reply
  setTimeout(() => {
    chatHistory.value.push({
      role: 'bot',
      text: 'ধন্যবাদ! আপনার প্রশ্নটি রেকর্ড হয়েছে। শীঘ্রই উত্তর প্রদান করা হবে।',
    })
    scrollToBottom()
  }, 500)
}

// Close on Escape
onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    closeChat()
    return
  }
  // Trap focus within the chat dialog while open
  if (e.key === 'Tab' && open.value && chatPopoverRef.value) {
    const focusables = chatPopoverRef.value.querySelectorAll<HTMLElement>(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    )
    if (!focusables.length) return
    const first = focusables[0]
    const last = focusables[focusables.length - 1]
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault()
      last.focus()
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault()
      first.focus()
    }
  }
}
</script>

<style scoped>
.ai-float-btn {
  position: fixed;
  bottom: calc(1.5rem + env(safe-area-inset-bottom));
  right: calc(1.5rem + env(safe-area-inset-right));
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--color-primary);
  color: #fff;
  border: 3px solid #fff;
  box-shadow: 0 4px 16px rgba(20, 80, 50, 0.4);
  cursor: pointer;
  transition: transform var(--transition-fast), box-shadow var(--transition-fast);
  flex-direction: column;
  gap: 0;
}

.ai-float-btn:hover,
.ai-float-btn--active {
  transform: scale(1.08);
  box-shadow: 0 6px 24px rgba(20, 80, 50, 0.5);
}

.ai-float-icon {
  line-height: 1;
  transition: transform var(--transition-fast);
}

.ai-float-btn--active .ai-float-icon {
  transform: rotate(15deg);
}

.ai-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: var(--color-accent);
  color: #1a1a1a;
  font-size: 0.6rem;
  font-weight: 700;
  font-family: var(--font-bn);
  padding: 0.15rem 0.45rem;
  border-radius: 999px;
  border: 2px solid #fff;
  line-height: 1.2;
}

.ai-float-overlay {
  position: fixed;
  inset: 0;
  z-index: 190;
  background: rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(2px);
  display: flex;
  justify-content: flex-end;
  padding: calc(2rem + env(safe-area-inset-top)) 2rem calc(2rem + env(safe-area-inset-bottom));
}

.ai-chat-popover {
  width: 380px;
  max-width: 90vw;
  height: 480px;
  max-height: 85vh;
  background: var(--color-bg-card);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  box-shadow: var(--elevation-popover);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: popIn 0.24s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes popIn {
  from { opacity: 0; transform: scale(0.96) translateY(8px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.ai-chat-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  background: var(--color-primary-50);
}

.ai-chat-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.9rem;
  font-family: var(--font-bn);
  color: var(--color-primary);
}

.ai-chat-body {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}

.ai-message {
  max-width: 85%;
  padding: 0.6rem 0.9rem;
  border-radius: var(--radius-md);
  font-size: 0.85rem;
  font-family: var(--font-bn);
  line-height: 1.5;
}

.ai-message-bot {
  align-self: flex-start;
  background: var(--color-bg-muted);
  color: var(--color-text);
  border-bottom-left-radius: 4px;
}

.ai-message-user {
  align-self: flex-end;
  background: var(--color-primary);
  color: var(--color-text-on-primary);
  border-bottom-right-radius: 4px;
}

.ai-chat-footer {
  padding: 0.75rem 1.25rem;
  border-top: 1px solid var(--color-border);
  background: var(--color-bg-card);
}

.ai-input-wrap {
  display: flex;
  gap: 0.4rem;
  align-items: center;
}

.ai-input {
  flex: 1;
  padding: 0.6rem 0.9rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: 1rem;
  font-family: var(--font-bn);
  background: var(--color-bg);
  color: var(--color-text);
  outline: none;
  transition: border-color var(--transition-fast);
}

.ai-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--color-primary-100);
}

.ai-send-btn {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border-radius: var(--radius-md);
  border: none;
  background: var(--color-primary);
  color: #fff;
  cursor: pointer;
  transition: background var(--transition-fast);
  flex-shrink: 0;
  position: relative;
  touch-action: manipulation;
}
/* Expand hit area to the 44px touch minimum without growing the visual */
.ai-send-btn::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: inherit;
}

.ai-send-btn:hover:not(:disabled) {
  background: var(--color-primary-light);
}

.ai-send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.ai-hint {
  margin-top: 0.4rem;
  text-align: center;
}

/* Transition: enter 200ms, exit ~150ms (exits run shorter), sharp deceleration */
.floating-enter-active {
  transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.floating-leave-active {
  transition: opacity 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}
.floating-enter-from,
.floating-leave-to {
  opacity: 0;
}
@media (prefers-reduced-motion: reduce) {
  .floating-enter-active,
  .floating-leave-active {
    transition: none;
  }
  .ai-chat-popover {
    animation: none;
  }
}
</style>
