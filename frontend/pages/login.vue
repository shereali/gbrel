<template>
  <div style="background: #0A1128; min-height: calc(100vh - 80px); display:flex; align-items:center; justify-content:center; padding: 48px 16px;">
    <div style="max-width: 900px; width: 100%; display: grid; grid-template-columns: 1.1fr 1fr; gap: 32px; background: #0F172A; border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-2xl); overflow: hidden; box-shadow: var(--shadow-xl);" class="auth-box-grid">
      
      <!-- Left: Demo Accounts & 1-Click Fast Login -->
      <div style="padding: 40px; background: rgba(255,255,255,0.02); border-right: 1px solid rgba(255,255,255,0.06); display:flex; flex-direction:column; justify-content:space-between;">
        <div>
          <div class="flex items-center gap-3" style="margin-bottom: 20px;">
            <div style="width: 38px; height: 38px; border-radius: 8px; background: var(--color-gold); color: #0A1128; display:flex; align-items:center; justify-content:center;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
            <div>
              <span style="font-family: var(--font-display); font-size: 1.2rem; font-weight: 800; color: #FFF; display:block;">GBREL</span>
              <span style="font-size: 0.7rem; color: var(--color-gold); font-weight: 700; letter-spacing: 0.12em;">DEMO CREDENTIALS HUB</span>
            </div>
          </div>

          <h3 style="font-size: 1.25rem; font-weight: 800; color: #FFF; margin-bottom: 8px;">Select Role for Instant Demo Access</h3>
          <p style="color: #94A3B8; font-size: 0.85rem; margin-bottom: 24px;">Click any profile below to auto-fill credentials and log in directly.</p>

          <!-- 3 Quick Clickable Credential Cards -->
          <div style="display:flex; flex-direction:column; gap: 14px;">
            <!-- 1. Super Admin -->
            <div 
              class="credential-card" 
              :class="{ selected: selectedRole === 'admin' }"
              @click="selectDemo('admin')"
            >
              <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                  <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z"/>
                      <path d="M9 12l2 2 4-4"/>
                    </svg>
                  </div>
                  <div>
                    <strong style="color:#FFF; font-size: 0.95rem; display:block;">Super Administrator</strong>
                    <div style="font-size: 0.78rem; color: #94A3B8;">Full inventory CRUD, RAJUK vetting, User RBAC</div>
                  </div>
                </div>
                <span class="badge badge-urgent" style="font-size:0.75rem;">ADMIN ACCESS</span>
              </div>
              <div class="credentials-code">
                <span>Email: <code>admin@gbrel.com</code></span>
                <span>Pass: <code>admin123</code></span>
              </div>
            </div>

            <!-- 2. Senior Advisor / Broker -->
            <div 
              class="credential-card" 
              :class="{ selected: selectedRole === 'agent' }"
              @click="selectDemo('agent')"
            >
              <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                  <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <circle cx="12" cy="8" r="4"/>
                      <path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
                    </svg>
                  </div>
                  <div>
                    <strong style="color:#FFF; font-size: 0.95rem; display:block;">Senior Real Estate Advisor</strong>
                    <div style="font-size: 0.78rem; color: #94A3B8;">Assigned mandates, VIP tours, client leads</div>
                  </div>
                </div>
                <span class="badge badge-featured" style="font-size:0.75rem;">AGENT PORTAL</span>
              </div>
              <div class="credentials-code">
                <span>Email: <code>agent@gbrel.com</code></span>
                <span>Pass: <code>agent123</code></span>
              </div>
            </div>

            <!-- 3. VIP Buyer / NRB Investor -->
            <div 
              class="credential-card" 
              :class="{ selected: selectedRole === 'buyer' }"
              @click="selectDemo('buyer')"
            >
              <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                  <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <circle cx="12" cy="8" r="4"/>
                      <path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
                    </svg>
                  </div>
                  <div>
                    <strong style="color:#FFF; font-size: 0.95rem; display:block;">VIP Buyer / NRB Investor</strong>
                    <div style="font-size: 0.78rem; color: #94A3B8;">Wishlists, VIP site visits, comparison tool</div>
                  </div>
                </div>
                <span class="badge badge-rajuk" style="font-size:0.75rem;">BUYER VIEW</span>
              </div>
              <div class="credentials-code">
                <span>Email: <code>buyer@gbrel.com</code></span>
                <span>Pass: <code>buyer123</code></span>
              </div>
            </div>
          </div>
        </div>

        <div style="margin-top: 24px; font-size: 0.78rem; color: #64748B; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 16px;">
          Secured by Laravel 13 Session Engine & Token Authentication
        </div>
      </div>

      <!-- Right: Standard Sign In Form -->
      <div style="padding: 40px; display:flex; flex-direction:column; justify-content:center;">
        <h2 style="font-size: 1.6rem; font-weight: 800; color: #FFF; margin-bottom: 6px;">Sign In to Portal</h2>
        <p style="color: #94A3B8; font-size: 0.88rem; margin-bottom: 28px;">Enter your registered credentials or select a demo role from the left.</p>

        <form @submit.prevent="handleLogin">
          <div class="form-group" style="margin-bottom: 16px;">
            <label class="form-label" style="color: #CBD5E1;">Email Address</label>
            <input 
              v-model="email" 
              type="email" 
              required 
              placeholder="e.g. admin@gbrel.com" 
              class="form-input" 
              style="background: #1E293B; color: #FFF; border-color: rgba(255,255,255,0.15);" 
            />
          </div>

          <div class="form-group" style="margin-bottom: 24px;">
            <div class="flex justify-between items-center" style="margin-bottom: 6px;">
              <label class="form-label" style="color: #CBD5E1; margin-bottom:0;">Password</label>
              <button type="button" style="font-size: 0.78rem; color: var(--color-gold); background: none; border: none; cursor: pointer;" @click="showResetHint = true">Forgot Password?</button>
            </div>
            <input 
              v-model="password" 
              type="password" 
              required 
              placeholder="••••••••" 
              class="form-input" 
              style="background: #1E293B; color: #FFF; border-color: rgba(255,255,255,0.15);" 
            />
            <p v-if="showResetHint" style="font-size: 0.78rem; color: #CBD5E1; margin-top: 6px;">Demo reset code: <code style="color: #34D399;">123456</code></p>
          </div>

          <button type="submit" class="btn btn-emerald btn-lg" style="width: 100%; margin-bottom: 16px;">
            <span>Sign In to Account →</span>
          </button>

          <!-- 1-Click Direct Shortcut -->
          <button 
            type="button" 
            class="btn btn-gold" 
            style="width: 100%;"
            @click="directDemoLogin"
          >
            <span>1-Click Fast Login as {{ selectedRole.toUpperCase() }}</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

const router = useRouter()
const { login } = useAuth()

const selectedRole = ref<'admin' | 'agent' | 'buyer'>('admin')
const email = ref('admin@gbrel.com')
const password = ref('admin123')
const showResetHint = ref(false)

const selectDemo = (role: 'admin' | 'agent' | 'buyer') => {
  selectedRole.value = role
  if (role === 'admin') {
    email.value = 'admin@gbrel.com'
    password.value = 'admin123'
  } else if (role === 'agent') {
    email.value = 'agent@gbrel.com'
    password.value = 'agent123'
  } else {
    email.value = 'buyer@gbrel.com'
    password.value = 'buyer123'
  }
}

const handleLogin = async () => {
  const role = await login(email.value, password.value)
  if (role === 'admin') {
    router.push('/admin')
  } else {
    router.push('/dashboard')
  }
}

const directDemoLogin = async () => {
  selectDemo(selectedRole.value)
  await handleLogin()
}
</script>

<style scoped>
.credential-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--radius-lg);
  padding: 14px 16px;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.credential-card:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.credential-card.selected {
  background: rgba(16, 185, 129, 0.08);
  border-color: #10B981;
  box-shadow: 0 0 16px rgba(16, 185, 129, 0.2);
}

.credentials-code {
  margin-top: 10px;
  padding-top: 8px;
  border-top: 1px dashed rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  font-size: 0.78rem;
  color: #94A3B8;
}

.credentials-code code {
  color: #34D399;
  background: rgba(0, 0, 0, 0.3);
  padding: 2px 6px;
  border-radius: 4px;
}

@media (max-width: 860px) {
  .auth-box-grid {
    grid-template-columns: 1fr !important;
  }
}

@media (max-width: 640px) {
  .auth-box-grid > div {
    padding: 28px 20px !important;
  }
  .credentials-code {
    flex-direction: column;
    gap: 4px;
  }
}
</style>
