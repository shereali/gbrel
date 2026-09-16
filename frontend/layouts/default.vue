<template>
  <div class="site-layout">
    <!-- Public Website Navigation Header -->
    <AppHeader />

    <!-- Main Website Viewport -->
    <main class="site-main-content">
      <slot />
    </main>

    <!-- Side-by-side Multi-Property Comparison Bottom Bar & Modal -->
    <ComparisonDrawer />

    <!-- Floating WhatsApp Pulse & VIP Instant Callback Hunter Widget -->
    <LeadHunterWidget />

    <!-- Luxury Footer -->
    <AppFooter />

    <!-- Global Website Toast Container -->
    <div class="toast-container" aria-live="polite">
      <div 
        v-for="toast in toasts" 
        :key="toast.id" 
        class="toast-item" 
        :class="toast.type"
      >
        <div style="font-size:1.2rem; line-height:1;">
          <span v-if="toast.type === 'success'" style="color:#10B981;">✔</span>
          <span v-else-if="toast.type === 'error'" style="color:#EF4444;">✖</span>
          <span v-else-if="toast.type === 'warning'" style="color:#F59E0B;">⚠</span>
          <span v-else style="color:#60A5FA;">ℹ</span>
        </div>
        <div style="flex:1;">
          <div class="toast-title">{{ toast.title }}</div>
          <div v-if="toast.message" class="toast-message">{{ toast.message }}</div>
        </div>
        <button class="toast-close" @click="removeToast(toast.id)" aria-label="Dismiss notification">✕</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import AppHeader from '~/components/AppHeader.vue'
import AppFooter from '~/components/AppFooter.vue'
import ComparisonDrawer from '~/components/ComparisonDrawer.vue'
import LeadHunterWidget from '~/components/LeadHunterWidget.vue'
import { useToast } from '~/composables/useToast'

const { toasts, remove: removeToast } = useToast()
</script>

<style scoped>
.site-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.site-main-content {
  flex: 1;
  min-height: calc(100vh - 80px);
}
</style>
