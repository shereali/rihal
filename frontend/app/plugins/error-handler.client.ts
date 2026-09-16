export default defineNuxtPlugin((nuxtApp) => {
  if (import.meta.client) {
    // Filter out known third-party browser extension errors and content-script noise
    window.addEventListener('unhandledrejection', (event) => {
      const reason = event.reason?.message || String(event.reason || '');
      
      const isExtensionNoise =
        reason.includes('No Listener: tabs:outgoing.message.ready') ||
        reason.includes('message channel closed before a response was received') ||
        reason.includes('The message port closed before a response was received') ||
        reason.includes('Cannot read properties of undefined (reading \'startTime\')') ||
        reason.includes('Extension context invalidated') ||
        reason.includes('ResizeObserver loop completed with undelivered notifications');

      if (isExtensionNoise) {
        // Prevent noisy extension rejection from bubbling up
        event.preventDefault();
        event.stopImmediatePropagation();
      }
    });

    // Also catch global window errors from injected extension scripts
    window.addEventListener('error', (event) => {
      const msg = event.message || '';
      const filename = event.filename || '';

      const isExtensionNoise =
        msg.includes('No Listener: tabs:outgoing.message.ready') ||
        msg.includes('Cannot read properties of undefined (reading \'startTime\')') ||
        filename.includes('content.js') ||
        filename.includes('extensions::') ||
        filename.startsWith('chrome-extension://') ||
        filename.startsWith('moz-extension://');

      if (isExtensionNoise) {
        event.preventDefault();
        event.stopImmediatePropagation();
      }
    });
  }
});
