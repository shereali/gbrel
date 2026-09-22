<template>
  <div class="login-page-wrapper">
    <div class="login-card-container">
      
      <!-- Left Column: Premium Brand Showcase -->
      <div class="brand-showcase-column">
        <div>
          <!-- Official Logo Lockup -->
          <NuxtLink to="/" class="brand-lockup" title="Return to GBREL Homepage">
            <img src="/img/logo-mark.png" alt="GBREL Logo" class="brand-logo-img" />
            <div>
              <span class="brand-name">GBREL</span>
              <span class="brand-tagline">Green Bay Real Estate Ltd.</span>
            </div>
          </NuxtLink>

          <div class="showcase-content">
            <span class="portal-badge">Premier Real Estate Portal</span>
            <h1 class="showcase-headline">
              Verified Properties, Trusted Escrow & Direct Management
            </h1>
            <p class="showcase-description">
              Secure portal access for property managers, licensed advisors, institutional investors, and registered clients across Bangladesh.
            </p>

            <!-- Value Pillars -->
            <div class="value-pillars">
              <div class="pillar-item">
                <div class="pillar-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4"/>
                  </svg>
                </div>
                <div>
                  <strong class="pillar-title">Verified Property Portfolio</strong>
                  <p class="pillar-desc">RAJUK-cleared plots, Diplomatic Zone penthouses, and commercial assets with audited land records.</p>
                </div>
              </div>

              <div class="pillar-item">
                <div class="pillar-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                  </svg>
                </div>
                <div>
                  <strong class="pillar-title">Client Mandates & VIP Inspections</strong>
                  <p class="pillar-desc">Coordinated site inspections, buyer inquiry routing, and dedicated advisory services.</p>
                </div>
              </div>

              <div class="pillar-item">
                <div class="pillar-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                  </svg>
                </div>
                <div>
                  <strong class="pillar-title">Bank Escrow & Legal Clearance</strong>
                  <p class="pillar-desc">Institutional financial settlement backed by leading banks (BRAC Bank, DBH, IDLC, City Bank).</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Trust Badges Footer -->
        <div class="showcase-footer">
          <div class="trust-tags">
            <span class="trust-tag">
              <span class="dot-indicator"></span>
              RAJUK & CDA Vetted
            </span>
            <span class="trust-tag">
              <span class="dot-indicator"></span>
              Bank Escrow
            </span>
            <span class="trust-tag">
              <span class="dot-indicator"></span>
              256-Bit SSL
            </span>
          </div>
        </div>
      </div>

      <!-- Right Column: Clean, Modern Sign In Form -->
      <div class="login-form-column">
        <div class="form-inner">
          <div class="form-header">
            <h2 class="form-title">Welcome Back</h2>
            <p class="form-subtitle">Enter your credentials to access your account.</p>
          </div>

          <form @submit.prevent="handleLogin" class="auth-form">
            <!-- Email Input -->
            <div class="form-group">
              <label for="login-email" class="form-label">Email Address</label>
              <div class="input-with-icon">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                  <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input 
                  id="login-email"
                  v-model="email" 
                  type="email" 
                  required 
                  autocomplete="email"
                  placeholder="name@gbrel.com" 
                  class="form-input" 
                />
              </div>
            </div>

            <!-- Password Input with Toggle Visibility -->
            <div class="form-group">
              <div class="password-label-row">
                <label for="login-password" class="form-label">Password</label>
                <button 
                  type="button" 
                  class="forgot-link-btn"
                  @click="forgotPasswordOpen = true"
                >
                  Forgot Password?
                </button>
              </div>
              <div class="input-with-icon">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input 
                  id="login-password"
                  v-model="password" 
                  :type="showPassword ? 'text' : 'password'" 
                  required 
                  autocomplete="current-password"
                  placeholder="Enter your password" 
                  class="form-input" 
                />
                <button 
                  type="button" 
                  class="password-toggle-btn"
                  @click="showPassword = !showPassword"
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

            <!-- Remember Me -->
            <div class="form-options-row">
              <label class="remember-label">
                <input v-model="rememberMe" type="checkbox" class="remember-checkbox" />
                <span>Remember this device</span>
              </label>
            </div>

            <!-- Sign In Submit Button -->
            <button 
              type="submit" 
              class="btn btn-emerald btn-lg submit-btn" 
              :disabled="isLoading"
            >
              <svg v-if="isLoading" class="animate-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
              </svg>
              <span>{{ isLoading ? 'Signing In...' : 'Sign In' }}</span>
              <svg v-if="!isLoading" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
              </svg>
            </button>

            <!-- Quick Account Selector (Subtle and Professional) -->
            <div class="demo-selector-section">
              <div class="demo-selector-label">
                <span>Select profile to autofill:</span>
              </div>
              <div class="demo-chips-grid">
                <button 
                  type="button" 
                  class="demo-chip"
                  :class="{ active: selectedPreset === 'admin' }"
                  @click="applyPreset('admin')"
                >
                  <span class="chip-dot dot-admin"></span>
                  <span>Admin</span>
                </button>

                <button 
                  type="button" 
                  class="demo-chip"
                  :class="{ active: selectedPreset === 'agent' }"
                  @click="applyPreset('agent')"
                >
                  <span class="chip-dot dot-agent"></span>
                  <span>Advisor</span>
                </button>

                <button 
                  type="button" 
                  class="demo-chip"
                  :class="{ active: selectedPreset === 'buyer' }"
                  @click="applyPreset('buyer')"
                >
                  <span class="chip-dot dot-buyer"></span>
                  <span>Client</span>
                </button>
              </div>
            </div>

            <!-- Sign Up Redirect -->
            <div class="register-prompt">
              <span>New to Green Bay Real Estate?</span>
              <NuxtLink to="/signup" class="register-link">Register for an account</NuxtLink>
            </div>
          </form>

          <!-- Back to Home Link -->
          <div class="back-home-row">
            <NuxtLink to="/" class="back-home-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
              </svg>
              <span>Return to Public Catalog</span>
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Professional Forgot Password Modal -->
    <div v-if="forgotPasswordOpen" class="modal-overlay" @click.self="forgotPasswordOpen = false">
      <div class="modal-card animate-fade-in-up">
        <div class="modal-header">
          <div class="flex items-center gap-3">
            <div class="modal-icon-wrap">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </div>
            <div>
              <h3 class="modal-title">Password Reset Assistance</h3>
              <p class="modal-subtitle">Recover access to your GBREL account</p>
            </div>
          </div>
          <button class="modal-close-btn" @click="forgotPasswordOpen = false" aria-label="Close modal">✕</button>
        </div>

        <div class="modal-body">
          <p style="color: #94A3B8; font-size: 0.9rem; line-height: 1.6; margin-bottom: 16px;">
            To ensure institutional security, password resets are coordinated through authorized management. Please contact your brokerage manager or system administrator:
          </p>

          <div class="support-contact-box">
            <div class="support-row">
              <span class="support-label">Support Email:</span>
              <a href="mailto:support@gbrel.com" class="support-value">support@gbrel.com</a>
            </div>
            <div class="support-row">
              <span class="support-label">HQ Helpdesk:</span>
              <span class="support-value">+880 1819-987654</span>
            </div>
            <div class="support-row">
              <span class="support-label">Office Hours:</span>
              <span class="support-value">Sunday – Thursday (9:00 AM – 6:00 PM BST)</span>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-emerald" style="width: 100%;" @click="forgotPasswordOpen = false">
            Understood
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'
import { useToast } from '~/composables/useToast'

useHead({
  title: 'Sign In | GBREL - Green Bay Real Estate Ltd.',
  meta: [
    { name: 'description', content: 'Sign in to your Green Bay Real Estate portal to manage listings, inquiries, and property mandates.' }
  ]
})

const router = useRouter()
const { login } = useAuth()
const toast = useToast()

const email = ref('admin@gbrel.com')
const password = ref('admin123')
const rememberMe = ref(true)
const showPassword = ref(false)
const isLoading = ref(false)
const forgotPasswordOpen = ref(false)
const selectedPreset = ref<'admin' | 'agent' | 'buyer'>('admin')

const applyPreset = (role: 'admin' | 'agent' | 'buyer') => {
  selectedPreset.value = role
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
  if (!email.value || !password.value) {
    toast.error('Missing Credentials', 'Please provide both your email address and password.')
    return
  }

  isLoading.value = true
  try {
    const role = await login(email.value, password.value)
    toast.success('Welcome Back', 'You have successfully signed in.')
    if (role === 'admin') {
      router.push('/admin')
    } else {
      router.push('/dashboard')
    }
  } catch (err: any) {
    toast.error('Authentication Error', err.message || 'Unable to sign in. Please verify your credentials.')
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.login-page-wrapper {
  background: radial-gradient(circle at top center, #131E3A 0%, #0A1128 65%, #050B18 100%);
  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 20px;
}

.login-card-container {
  max-width: 980px;
  width: 100%;
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  background: #0F172A;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
}

/* --- Left Column: Brand Showcase --- */
.brand-showcase-column {
  padding: 44px;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.02) 0%, rgba(15, 23, 42, 0.6) 100%);
  border-right: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.brand-lockup {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  margin-bottom: 32px;
}

.brand-logo-img {
  width: 42px;
  height: 42px;
  object-fit: contain;
  filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.3));
}

.brand-name {
  font-family: var(--font-display, inherit);
  font-size: 1.35rem;
  font-weight: 800;
  color: #FFFFFF;
  letter-spacing: 0.02em;
  display: block;
  line-height: 1.1;
}

.brand-tagline {
  font-size: 0.75rem;
  color: #D4AF37;
  font-weight: 600;
  letter-spacing: 0.03em;
  display: block;
}

.portal-badge {
  display: inline-block;
  padding: 4px 10px;
  background: rgba(212, 175, 55, 0.12);
  border: 1px solid rgba(212, 175, 55, 0.3);
  border-radius: 9999px;
  font-size: 0.72rem;
  font-weight: 700;
  color: #E6C665;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 14px;
}

.showcase-headline {
  font-family: var(--font-display, inherit);
  font-size: 1.45rem;
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.35;
  margin-bottom: 12px;
}

.showcase-description {
  color: #94A3B8;
  font-size: 0.88rem;
  line-height: 1.55;
  margin-bottom: 32px;
}

.value-pillars {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.pillar-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}

.pillar-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: rgba(16, 185, 129, 0.12);
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: #34D399;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.pillar-title {
  display: block;
  color: #F1F5F9;
  font-size: 0.9rem;
  font-weight: 700;
  margin-bottom: 2px;
}

.pillar-desc {
  color: #94A3B8;
  font-size: 0.78rem;
  line-height: 1.45;
  margin: 0;
}

.showcase-footer {
  margin-top: 36px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.trust-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.trust-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: #94A3B8;
  font-weight: 600;
}

.dot-indicator {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10B981;
}

/* --- Right Column: Sign In Form --- */
.login-form-column {
  padding: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form-inner {
  width: 100%;
  max-width: 360px;
}

.form-header {
  margin-bottom: 26px;
}

.form-title {
  font-family: var(--font-display, inherit);
  font-size: 1.65rem;
  font-weight: 800;
  color: #FFFFFF;
  margin-bottom: 6px;
  line-height: 1.2;
}

.form-subtitle {
  color: #94A3B8;
  font-size: 0.88rem;
  margin: 0;
}

.form-group {
  margin-bottom: 18px;
}

.form-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  color: #CBD5E1;
  margin-bottom: 6px;
}

.password-label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 6px;
}

.password-label-row .form-label {
  margin-bottom: 0;
}

.forgot-link-btn {
  background: none;
  border: none;
  padding: 0;
  font-size: 0.78rem;
  font-weight: 600;
  color: #D4AF37;
  cursor: pointer;
  transition: color 0.15s;
}

.forgot-link-btn:hover {
  color: #F6E05E;
  text-decoration: underline;
}

.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 14px;
  color: #64748B;
  pointer-events: none;
}

.input-with-icon .form-input {
  width: 100%;
  background: #1E293B;
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #FFFFFF;
  border-radius: 10px;
  padding: 11px 40px 11px 42px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.input-with-icon .form-input:focus {
  outline: none;
  border-color: #10B981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
  background: #1E293B;
}

.password-toggle-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #64748B;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s;
}

.password-toggle-btn:hover {
  color: #E2E8F0;
}

.form-options-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
}

.remember-label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  color: #94A3B8;
  cursor: pointer;
  user-select: none;
}

.remember-checkbox {
  accent-color: #10B981;
  width: 15px;
  height: 15px;
  border-radius: 4px;
  cursor: pointer;
}

.submit-btn {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 20px;
  font-size: 0.95rem;
  font-weight: 700;
  border-radius: 10px;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
  margin-bottom: 22px;
  transition: all 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

/* Subtle Preset Profile Chips */
.demo-selector-section {
  padding: 14px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 10px;
  margin-bottom: 20px;
}

.demo-selector-label {
  font-size: 0.75rem;
  color: #94A3B8;
  margin-bottom: 8px;
  font-weight: 600;
  text-align: center;
}

.demo-chips-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 6px;
}

.demo-chip {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 6px;
  padding: 6px 10px;
  font-size: 0.76rem;
  font-weight: 600;
  color: #CBD5E1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.demo-chip:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #FFFFFF;
  border-color: rgba(255, 255, 255, 0.18);
}

.demo-chip.active {
  background: rgba(16, 185, 129, 0.15);
  border-color: rgba(16, 185, 129, 0.4);
  color: #34D399;
}

.chip-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.dot-admin { background: #F59E0B; }
.dot-agent { background: #38BDF8; }
.dot-buyer { background: #10B981; }

.register-prompt {
  text-align: center;
  font-size: 0.82rem;
  color: #94A3B8;
  margin-bottom: 16px;
}

.register-link {
  color: #10B981;
  font-weight: 700;
  text-decoration: none;
  margin-left: 5px;
  transition: color 0.15s;
}

.register-link:hover {
  color: #34D399;
  text-decoration: underline;
}

.back-home-row {
  text-align: center;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding-top: 16px;
}

.back-home-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  color: #64748B;
  text-decoration: none;
  transition: color 0.15s;
}

.back-home-link:hover {
  color: #CBD5E1;
}

/* Modal Styling */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 16px;
}

.modal-card {
  background: #0F172A;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  max-width: 480px;
  width: 100%;
  padding: 24px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 18px;
}

.modal-icon-wrap {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(212, 175, 55, 0.15);
  border: 1px solid rgba(212, 175, 55, 0.3);
  color: #D4AF37;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-title {
  color: #FFFFFF;
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
}

.modal-subtitle {
  color: #94A3B8;
  font-size: 0.8rem;
  margin: 2px 0 0;
}

.modal-close-btn {
  background: none;
  border: none;
  color: #64748B;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 4px;
  transition: color 0.15s;
}

.modal-close-btn:hover {
  color: #FFFFFF;
}

.support-contact-box {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.support-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.82rem;
}

.support-label {
  color: #94A3B8;
}

.support-value {
  color: #E2E8F0;
  font-weight: 600;
  text-decoration: none;
}

a.support-value:hover {
  color: #10B981;
  text-decoration: underline;
}

.modal-footer {
  margin-top: 20px;
}

/* Responsive adjustments */
@media (max-width: 900px) {
  .login-card-container {
    grid-template-columns: 1fr;
    max-width: 500px;
  }
  .brand-showcase-column {
    display: none;
  }
  .login-form-column {
    padding: 36px 24px;
  }
}

@media (max-width: 480px) {
  .login-form-column {
    padding: 28px 16px;
  }
  .demo-chips-grid {
    grid-template-columns: 1fr;
  }
}
</style>
