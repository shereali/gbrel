<template>
  <div style="background: #0B1120; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; color: #E2E8F0;">
    <div style="max-width: 460px; width: 100%; background: #0F172A; border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-2xl); padding: 40px; box-shadow: var(--shadow-xl);" class="animate-fade-in-up">
      <!-- Admin Brand Header -->
      <div style="text-align: center; margin-bottom: 32px;">
        <div style="width: 52px; height: 52px; border-radius: 14px; background: var(--color-gold); color: #0A1128; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 8px 24px rgba(212, 175, 55, 0.35);">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
            <path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/>
            <path d="M9 12l2 2 4-4"/>
          </svg>
        </div>
        <h1 style="font-family: var(--font-display); font-size: 1.6rem; font-weight: 800; color: #FFFFFF; line-height: 1.2;">
          GBREL<span style="color: #D4AF37;">.</span>ADMIN
        </h1>
        <p style="font-size: 0.8rem; color: #CBD5E1; margin-top: 4px; letter-spacing: 0.05em; text-transform: uppercase;">
          Enterprise Control Center Authentication
        </p>
      </div>

      <!-- Quick Demo Access Banner -->
      <div style="background: rgba(212, 175, 55, 0.08); border: 1px dashed rgba(212, 175, 55, 0.3); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 24px;">
        <div class="flex justify-between items-center" style="font-size: 0.8rem;">
          <span style="color: #F6E05E; font-weight: 700;">Super Admin Credentials:</span>
          <span class="badge badge-urgent" style="font-size: 0.75rem;">HQ ROLE</span>
        </div>
        <div style="font-size: 0.78rem; color: #CBD5E1; margin-top: 4px;">
          <code>admin@gbrel.com</code> / <code>admin123</code>
        </div>
      </div>

      <!-- Sign In Form -->
      <form @submit.prevent="handleAdminLogin">
        <div class="form-group" style="margin-bottom: 16px;">
          <label class="form-label" style="color: #CBD5E1;">Admin Email</label>
          <input 
            v-model="email" 
            type="email" 
            required 
            placeholder="admin@gbrel.com" 
            class="form-input" 
            style="background: #1E293B; color: #FFF; border-color: rgba(255,255,255,0.15);" 
          />
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
          <label class="form-label" style="color: #CBD5E1;">Security Password</label>
          <input 
            v-model="password" 
            type="password" 
            required 
            placeholder="••••••••" 
            class="form-input" 
            style="background: #1E293B; color: #FFF; border-color: rgba(255,255,255,0.15);" 
          />
        </div>

        <button type="submit" class="btn btn-emerald btn-lg" style="width: 100%; margin-bottom: 14px;">
          <span>Authorize Admin Access →</span>
        </button>

        <button 
          type="button" 
          class="btn btn-gold" 
          style="width: 100%; margin-bottom: 20px;"
          @click="autoFillAndLogin"
        >
          <span>1-Click Fast Admin Sign In</span>
        </button>

        <div style="text-align: center;">
          <NuxtLink to="/" style="font-size: 0.85rem; color: #CBD5E1; text-decoration: none;">
            ‹ Return to Public Website
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: false
})

const router = useRouter()
const { login } = useAuth()
const toast = useToast()

const email = ref('admin@gbrel.com')
const password = ref('admin123')
const loading = ref(false)

const handleAdminLogin = async () => {
  loading.value = true
  try {
    await login(email.value, password.value)
    toast.success('Admin Authenticated', 'Welcome to GBREL Enterprise Control Center.')
    router.push('/admin')
  } catch (err: any) {
    toast.error('Authentication Failed', err.message || 'Invalid administrator credentials.')
  } finally {
    loading.value = false
  }
}

const autoFillAndLogin = () => {
  email.value = 'admin@gbrel.com'
  password.value = 'admin123'
  handleAdminLogin()
}
</script>
