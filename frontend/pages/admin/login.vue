<template>
  <div style="background: radial-gradient(circle at top center, #131E3A 0%, #0A1128 65%, #050B18 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; color: #E2E8F0;">
    <div style="max-width: 440px; width: 100%; background: #0F172A; border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-2xl); padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);" class="animate-fade-in-up">
      <!-- Admin Brand Header -->
      <div style="text-align: center; margin-bottom: 32px;">
        <NuxtLink to="/" style="text-decoration: none; display: inline-block;">
          <img src="/img/logo-mark.png" alt="GBREL" style="width: 48px; height: 48px; object-fit: contain; margin-bottom: 12px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));" />
        </NuxtLink>
        <h1 style="font-family: var(--font-display, inherit); font-size: 1.55rem; font-weight: 800; color: #FFFFFF; line-height: 1.2;">
          GBREL Administrator
        </h1>
        <p style="font-size: 0.82rem; color: #94A3B8; margin-top: 4px;">
          Property Catalog & Brokerage Management
        </p>
      </div>

      <!-- Sign In Form -->
      <form @submit.prevent="handleAdminLogin">
        <div class="form-group" style="margin-bottom: 18px;">
          <label for="admin-email" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: #CBD5E1; margin-bottom: 6px;">Email or Username</label>
          <div style="position: relative; display: flex; align-items: center;">
            <svg style="position: absolute; left: 14px; color: #64748B; pointer-events: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input 
              id="admin-email"
              v-model="email" 
              type="text" 
              required 
              autocomplete="username email"
              placeholder="admin@gbrel.com or admin" 
              class="form-input" 
              style="width: 100%; background: #1E293B; color: #FFF; border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 11px 14px 11px 42px; font-size: 0.9rem;" 
            />
          </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
          <label for="admin-password" class="form-label" style="display: block; font-size: 0.82rem; font-weight: 600; color: #CBD5E1; margin-bottom: 6px;">Password</label>
          <div style="position: relative; display: flex; align-items: center;">
            <svg style="position: absolute; left: 14px; color: #64748B; pointer-events: none;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input 
              id="admin-password"
              v-model="password" 
              :type="showPassword ? 'text' : 'password'" 
              required 
              autocomplete="current-password"
              placeholder="Enter password (e.g. admin123 or password)" 
              class="form-input" 
              style="width: 100%; background: #1E293B; color: #FFF; border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 11px 40px 11px 42px; font-size: 0.9rem;" 
            />
            <button 
              type="button" 
              @click="showPassword = !showPassword"
              style="position: absolute; right: 12px; background: none; border: none; color: #64748B; cursor: pointer; padding: 4px; display: flex; align-items: center;"
              :title="showPassword ? 'Hide password' : 'Show password'"
              :aria-label="showPassword ? 'Hide password' : 'Show password'"
            >
              <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Quick Credentials Autofill Selector -->
        <div style="margin-bottom: 20px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 10px 12px;">
          <div style="font-size: 0.73rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94A3B8; font-weight: 700; margin-bottom: 8px;">
            Quick Fill Demo Credentials
          </div>
          <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <button 
              type="button" 
              @click="setCredentials('admin@gbrel.com', 'admin123')"
              style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34D399; font-size: 0.75rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
            >
              <span>👑 Super Admin</span>
            </button>
            <button 
              type="button" 
              @click="setCredentials('manager@gbrel.com', 'manager123')"
              style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: #60A5FA; font-size: 0.75rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
            >
              <span>🏢 Manager</span>
            </button>
            <button 
              type="button" 
              @click="setCredentials('legal@gbrel.com', 'legal123')"
              style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #FBBF24; font-size: 0.75rem; font-weight: 600; padding: 4px 10px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
            >
              <span>⚖️ Legal</span>
            </button>
          </div>
        </div>

        <button 
          type="submit" 
          class="btn btn-emerald btn-lg" 
          style="width: 100%; margin-bottom: 20px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 20px; font-weight: 700; border-radius: 10px; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);"
          :disabled="loading"
        >
          <svg v-if="loading" class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
            <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
          </svg>
          <span>{{ loading ? 'Authenticating...' : 'Sign In as Administrator' }}</span>
        </button>

        <div style="text-align: center; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 16px;">
          <NuxtLink to="/" style="font-size: 0.82rem; color: #64748B; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="19" y1="12" x2="5" y2="12"/>
              <polyline points="12 19 5 12 12 5"/>
            </svg>
            <span>Return to Public Website</span>
          </NuxtLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '~/composables/useAuth'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: false
})

useHead({
  title: 'Administrator Sign In | GBREL',
  meta: [
    { name: 'robots', content: 'noindex, nofollow' }
  ]
})

const router = useRouter()
const route = useRoute()
const { login, logout, isAdmin } = useAuth()
const toast = useToast()

const email = ref('admin@gbrel.com')
const password = ref('admin123')
const showPassword = ref(false)
const loading = ref(false)

const setCredentials = (e: string, p: string) => {
  email.value = e
  password.value = p
}

const handleAdminLogin = async () => {
  if (!email.value || !password.value) {
    toast.error('Missing Credentials', 'Please enter your administrator email and password.')
    return
  }

  loading.value = true
  try {
    const userPayload = await login(email.value, password.value, true)
    
    // Role Authorization Check
    if (!isAdmin.value && !userPayload.is_admin) {
      await logout()
      toast.error('Access Denied', 'Your account does not have administrative privileges.')
      return
    }

    toast.success('Welcome Back', `Authenticated as ${userPayload.name}`)
    const redirectUrl = route.query.redirect ? decodeURIComponent(String(route.query.redirect)) : '/admin'
    router.push(redirectUrl)
  } catch (err: any) {
    toast.error('Authentication Failed', err.message || 'Invalid administrator credentials.')
  } finally {
    loading.value = false
  }
}
</script>
