<template>
  <header class="app-header" :class="{ 'is-scrolled': isScrolled }">
    <div class="container nav-container">
      <!-- 1. Brand Logo -->
      <NuxtLink to="/" class="brand-logo" title="Gram Bangla Real Estate Limited (GBREL)">
        <img src="/img/logo-mark.png" alt="GBREL Logo" class="brand-logo-mark" />
        <div class="brand-text">
          <span class="brand-title">GBREL<span style="color: #D4AF37;">.</span>COM</span>
          <span class="brand-subtitle">GRAM BANGLA REAL ESTATE</span>
        </div>
      </NuxtLink>

      <!-- 2. Desktop Navigation Menu (Visible > 1024px) -->
      <ul class="nav-links">
        <li><NuxtLink to="/" class="nav-link">Home</NuxtLink></li>
        <li><NuxtLink to="/properties" class="nav-link">Properties</NuxtLink></li>
        <li><NuxtLink to="/properties?type=Plot" class="nav-link">Plots & Lands</NuxtLink></li>
        <li><NuxtLink to="/properties?type=Hotel" class="nav-link">Resorts</NuxtLink></li>
        <li><NuxtLink to="/agents" class="nav-link">Agents</NuxtLink></li>
        <li><NuxtLink to="/directors" class="nav-link">Directors</NuxtLink></li>
        <li><NuxtLink to="/compare" class="nav-link">Compare</NuxtLink></li>
      </ul>

      <!-- 3. Desktop Action Buttons -->
      <div class="nav-actions">
        <!-- Saved Wishlist Counter -->
        <NuxtLink to="/dashboard?tab=favorites" class="header-icon-btn" title="Saved Properties">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
          <span v-if="user?.savedProperties && user.savedProperties.length > 0" class="icon-counter-badge">
            {{ user.savedProperties.length }}
          </span>
        </NuxtLink>

        <!-- Authenticated User Profile Dropdown (Desktop) -->
        <div v-if="isAuthenticated" ref="userDropdownRef" class="user-menu-wrapper desktop-only">
          <button 
            class="user-profile-pill" 
            :class="{ active: userMenuOpen }"
            @click="userMenuOpen = !userMenuOpen"
            aria-haspopup="true"
            :aria-expanded="userMenuOpen"
            title="User Account Menu"
          >
            <img 
              :src="user.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop'" 
              :alt="user.name" 
              class="user-avatar-tiny" 
            />
            <span class="user-name-short">{{ shortName }}</span>
            <span class="badge badge-role-tag">{{ user.role }}</span>
            <svg class="chevron-icon" :class="{ rotated: userMenuOpen }" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
          </button>

          <!-- Dropdown Card -->
          <transition name="dropdown-fade">
            <div v-if="userMenuOpen" class="user-dropdown-card">
              <div class="user-dropdown-header">
                <div class="user-dd-name">{{ user.name }}</div>
                <div class="user-dd-email">{{ user.email }}</div>
                <div class="user-dd-role-badge">Role: {{ user.role.toUpperCase() }}</div>
              </div>

              <div class="user-dropdown-links">
                <NuxtLink to="/dashboard" class="user-dd-link" @click="userMenuOpen = false">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="9" rx="1"/>
                    <rect x="14" y="3" width="7" height="5" rx="1"/>
                    <rect x="14" y="12" width="7" height="9" rx="1"/>
                    <rect x="3" y="16" width="7" height="5" rx="1"/>
                  </svg>
                  <span>My Dashboard</span>
                </NuxtLink>

                <NuxtLink to="/dashboard?tab=favorites" class="user-dd-link" @click="userMenuOpen = false">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                  </svg>
                  <span>Saved Wishlist ({{ user?.savedProperties?.length || 0 }})</span>
                </NuxtLink>

                <NuxtLink to="/dashboard?tab=viewings" class="user-dd-link" @click="userMenuOpen = false">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                  </svg>
                  <span>Scheduled Viewings</span>
                </NuxtLink>

                <NuxtLink v-if="isAdmin" to="/admin" class="user-dd-link admin-link" @click="userMenuOpen = false">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                  </svg>
                  <span>Admin HQ Console</span>
                </NuxtLink>
              </div>

              <!-- Demo Role Quick Switcher -->
              <div class="user-dropdown-roles">
                <span class="role-switch-title">Switch Demo Role:</span>
                <div class="role-pills">
                  <button 
                    class="role-pill-btn" 
                    :class="{ active: user.role === 'buyer' }" 
                    @click="switchRole('buyer')"
                  >Buyer</button>
                  <button 
                    class="role-pill-btn" 
                    :class="{ active: user.role === 'agent' }" 
                    @click="switchRole('agent')"
                  >Agent</button>
                  <button 
                    class="role-pill-btn" 
                    :class="{ active: user.role === 'admin' }" 
                    @click="switchRole('admin')"
                  >Admin</button>
                </div>
              </div>

              <!-- Sign Out Button -->
              <div class="user-dropdown-footer">
                <button class="sign-out-btn" @click="handleLogout">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                  </svg>
                  <span>Sign Out</span>
                </button>
              </div>
            </div>
          </transition>
        </div>

        <!-- Unauthenticated: Sign In Button (Desktop) -->
        <NuxtLink v-else to="/login" class="btn btn-sm btn-outline-white desktop-only">
          <span>Sign In</span>
        </NuxtLink>

        <!-- Sell / List Property CTA (Desktop > 1024px) -->
        <NuxtLink to="/list-property" class="btn btn-sm btn-gold desktop-cta" title="Sell your property with GBREL">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Sell Property</span>
        </NuxtLink>

        <!-- Mobile Hamburger Button (Visible <= 1024px) -->
        <button class="mobile-hamburger-btn" @click="mobileMenuOpen = true" aria-label="Open Navigation Menu">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- ======================================================================
         LUXURY MOBILE & TABLET DRAWER (SLIDE-IN)
         ====================================================================== -->
    <transition name="drawer-fade">
      <div v-if="mobileMenuOpen" ref="drawerRoot" class="mobile-drawer-overlay" @click.self="mobileMenuOpen = false">
        <div class="mobile-drawer-panel">
          <!-- Drawer Header -->
          <div class="drawer-header">
            <div class="flex items-center gap-2">
              <img src="/img/logo-mark.png" alt="GBREL Logo" style="width:34px; height:34px; object-fit:contain;" />
              <span class="brand-title" style="font-size:1.15rem;">GBREL<span style="color:#D4AF37;">.</span>COM</span>
            </div>
            <button class="drawer-close-btn" @click="mobileMenuOpen = false" aria-label="Close Menu">✕</button>
          </div>

          <!-- Drawer Authenticated User Card -->
          <div v-if="isAuthenticated" class="drawer-user-card">
            <div class="flex items-center gap-3">
              <img :src="user.avatar" :alt="user.name" class="drawer-user-avatar" />
              <div style="overflow:hidden;">
                <div class="drawer-user-name">{{ user.name }}</div>
                <div class="drawer-user-email">{{ user.email }}</div>
              </div>
            </div>
            <div class="flex items-center justify-between" style="margin-top: 10px;">
              <span class="badge badge-role-tag">{{ user.role.toUpperCase() }}</span>
              <NuxtLink to="/dashboard" class="drawer-view-profile-link" @click="mobileMenuOpen = false">
                Dashboard →
              </NuxtLink>
            </div>
          </div>

          <!-- Drawer Navigation Links -->
          <nav class="drawer-nav">
            <NuxtLink to="/" class="drawer-link" @click="mobileMenuOpen = false">
              <svg class="drawer-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              <span>Home Overview</span>
            </NuxtLink>

            <NuxtLink to="/properties" class="drawer-link" @click="mobileMenuOpen = false">
              <svg class="drawer-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
              </svg>
              <span>All Properties & Flats</span>
            </NuxtLink>

            <NuxtLink to="/properties?type=Plot" class="drawer-link" @click="mobileMenuOpen = false">
              <svg class="drawer-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M12 21s-6-5.2-6-10a6 6 0 0 1 12 0c0 4.8-6 10-6 10z"/>
                <circle cx="12" cy="11" r="2"/>
              </svg>
              <span>Plots & Freehold Lands</span>
            </NuxtLink>

            <NuxtLink to="/properties?type=Hotel" class="drawer-link" @click="mobileMenuOpen = false">
              <svg class="drawer-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M22 17H2a1 1 0 0 1-1-1V5h2v8h6V8h8a6 6 0 0 1 6 6v2zM4 11h4M20 17v3M4 20v-3"/>
              </svg>
              <span>Beach Resorts & Suites</span>
            </NuxtLink>

            <NuxtLink to="/agents" class="drawer-link" @click="mobileMenuOpen = false">
              <svg class="drawer-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
              </svg>
              <span>Regional Senior Advisors</span>
            </NuxtLink>

            <NuxtLink to="/directors" class="drawer-link" @click="mobileMenuOpen = false">
              <svg class="drawer-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              <span>Board of Directors</span>
            </NuxtLink>

            <NuxtLink to="/compare" class="drawer-link" @click="mobileMenuOpen = false">
              <svg class="drawer-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3M4 12h2M10 12h2M16 12h2"/>
              </svg>
              <span>Compare Properties</span>
            </NuxtLink>

            <NuxtLink v-if="isAuthenticated" to="/dashboard?tab=favorites" class="drawer-link" @click="mobileMenuOpen = false">
              <svg class="drawer-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
              <span>My Wishlist ({{ user?.savedProperties?.length || 0 }})</span>
            </NuxtLink>
          </nav>

          <!-- Drawer Action CTAs -->
          <div class="drawer-actions-footer">
            <NuxtLink to="/list-property" class="btn btn-gold btn-lg" style="width:100%; margin-bottom:10px;" @click="mobileMenuOpen = false">
              <span>+ Sell Your Property</span>
            </NuxtLink>

            <div v-if="isAuthenticated" style="display:flex; flex-direction:column; gap:8px;">
              <NuxtLink to="/dashboard" class="btn btn-primary" style="width:100%;" @click="mobileMenuOpen = false">
                <span>Account Dashboard</span>
              </NuxtLink>
              <button class="btn btn-outline-white" style="width:100%; border-color: rgba(239, 68, 68, 0.4); color: #FCA5A5;" @click="handleLogout">
                <span>Sign Out</span>
              </button>
            </div>
            <div v-else style="display:flex; flex-direction:column; gap:8px;">
              <NuxtLink to="/login" class="btn btn-outline-white" style="width:100%;" @click="mobileMenuOpen = false">
                <span>Sign In to Account</span>
              </NuxtLink>
              <NuxtLink to="/signup" class="btn btn-sm" style="width:100%; text-align:center; color:#CBD5E1; font-weight:600;" @click="mobileMenuOpen = false">
                <span>New here? Register Free →</span>
              </NuxtLink>
            </div>

            <div style="text-align:center; margin-top:16px;">
              <a href="tel:+8801819987654" style="color:#10B981; font-size:0.85rem; font-weight:700;">
                24/7 VIP Hotline: +880 1819-987654
              </a>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

const router = useRouter()
const { user, isAuthenticated, isAdmin, logout, switchRole } = useAuth()

const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)
const isScrolled = ref(false)
const drawerRoot = ref<HTMLElement | null>(null)
const userDropdownRef = ref<HTMLElement | null>(null)

useOverlayBehavior(mobileMenuOpen, () => { mobileMenuOpen.value = false }, drawerRoot)

const shortName = computed(() => {
  if (!user.value?.name) return 'Account'
  return user.value.name.split(' ')[0]
})

const handleLogout = async () => {
  userMenuOpen.value = false
  mobileMenuOpen.value = false
  await logout()
  router.push('/')
}

const handleScroll = () => {
  if (typeof window !== 'undefined') {
    isScrolled.value = window.scrollY > 20
  }
}

const handleClickOutside = (e: MouseEvent) => {
  if (userMenuOpen.value && userDropdownRef.value && !userDropdownRef.value.contains(e.target as Node)) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  handleScroll()
  window.addEventListener('scroll', handleScroll, { passive: true })
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('scroll', handleScroll)
    document.removeEventListener('click', handleClickOutside)
  }
})
</script>

<style scoped>
/* User Profile Pill (Desktop) */
.user-menu-wrapper {
  position: relative;
}

.user-profile-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.16);
  padding: 4px 12px 4px 5px;
  border-radius: var(--radius-full);
  color: #FFFFFF;
  font-size: 0.86rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.user-profile-pill:hover,
.user-profile-pill.active {
  background: rgba(255, 255, 255, 0.16);
  border-color: rgba(212, 175, 55, 0.5);
}

.user-avatar-tiny {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
  border: 1.5px solid var(--color-gold);
}

.user-name-short {
  max-width: 90px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.badge-role-tag {
  background: rgba(212, 175, 55, 0.18);
  color: var(--color-gold-bright);
  border: 1px solid rgba(212, 175, 55, 0.35);
  font-size: 0.68rem;
  padding: 2px 7px;
  border-radius: var(--radius-full);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.chevron-icon {
  transition: transform var(--transition-fast);
  color: #94A3B8;
}

.chevron-icon.rotated {
  transform: rotate(180deg);
}

/* User Dropdown Card */
.user-dropdown-card {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 260px;
  background: #0D1630;
  border: 1px solid rgba(212, 175, 55, 0.25);
  border-radius: var(--radius-lg);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.55);
  padding: 12px;
  z-index: 1100;
}

.user-dropdown-header {
  padding: 8px 10px 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.user-dd-name {
  font-weight: 700;
  color: #FFFFFF;
  font-size: 0.92rem;
  line-height: 1.2;
}

.user-dd-email {
  font-size: 0.76rem;
  color: #94A3B8;
  margin-top: 2px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.user-dd-role-badge {
  display: inline-block;
  margin-top: 6px;
  font-size: 0.68rem;
  color: var(--color-gold-bright);
  font-weight: 700;
}

.user-dropdown-links {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.user-dd-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  color: #CBD5E1;
  font-size: 0.85rem;
  font-weight: 500;
  border-radius: var(--radius-md);
  text-decoration: none;
  transition: all var(--transition-fast);
}

.user-dd-link:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #FFFFFF;
}

.user-dd-link.admin-link {
  color: #6EE7B7;
}

.user-dropdown-roles {
  padding: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.role-switch-title {
  display: block;
  font-size: 0.72rem;
  color: #94A3B8;
  font-weight: 600;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.role-pills {
  display: flex;
  gap: 6px;
}

.role-pill-btn {
  flex: 1;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #CBD5E1;
  font-size: 0.72rem;
  font-weight: 600;
  padding: 4px 0;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.role-pill-btn:hover {
  background: rgba(255, 255, 255, 0.14);
  color: #FFFFFF;
}

.role-pill-btn.active {
  background: var(--color-gold);
  border-color: var(--color-gold);
  color: #0A1128;
  font-weight: 700;
}

.user-dropdown-footer {
  padding-top: 8px;
}

.sign-out-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.25);
  color: #FCA5A5;
  border-radius: var(--radius-md);
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.sign-out-btn:hover {
  background: rgba(239, 68, 68, 0.22);
  color: #FFFFFF;
}

/* Dropdown Animation */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: all 0.18s ease-out;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Mobile Hamburger Button */
.mobile-hamburger-btn {
  display: none;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #FFFFFF;
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm);
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.mobile-hamburger-btn:hover {
  background: rgba(255, 255, 255, 0.18);
}

/* Breakpoint for Desktop vs Mobile/Tablet Header */
@media (max-width: 1024px) {
  .nav-links {
    display: none !important;
  }
  .desktop-only {
    display: none !important;
  }
  .desktop-cta {
    display: none !important;
  }
  .mobile-hamburger-btn {
    display: flex !important;
  }
}

@media (max-width: 640px) {
  .brand-title {
    font-size: 1.15rem;
  }
  .brand-subtitle {
    font-size: 0.58rem;
  }
}

/* Mobile Drawer Styles */
.mobile-drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.78);
  z-index: 3000;
  display: flex;
  justify-content: flex-end;
}

.mobile-drawer-panel {
  width: 320px;
  max-width: 85vw;
  height: 100%;
  background: #0A1128;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  padding: 24px;
  box-shadow: -15px 0 35px rgba(0, 0, 0, 0.6);
  overflow-y: auto;
}

.drawer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 18px;
}

.drawer-close-btn {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #FFFFFF;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.drawer-user-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(212, 175, 55, 0.3);
  border-radius: var(--radius-md);
  padding: 12px;
  margin-bottom: 16px;
}

.drawer-user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--color-gold);
  flex-shrink: 0;
}

.drawer-user-name {
  font-weight: 700;
  color: #FFFFFF;
  font-size: 0.9rem;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.drawer-user-email {
  font-size: 0.74rem;
  color: #94A3B8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.drawer-view-profile-link {
  color: var(--color-gold-bright);
  font-size: 0.8rem;
  font-weight: 700;
  text-decoration: none;
}

.drawer-nav {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
  margin-bottom: 24px;
}

.drawer-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 14px;
  color: #CBD5E1;
  font-size: 0.92rem;
  font-weight: 600;
  border-radius: var(--radius-md);
  transition: all var(--transition-fast);
}

.drawer-link:hover, .drawer-link.router-link-active {
  background: rgba(255, 255, 255, 0.08);
  color: #FFFFFF;
}

.drawer-icon {
  flex-shrink: 0;
  color: var(--color-gold-bright);
}

.drawer-actions-footer {
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

/* Drawer Transitions */
.drawer-fade-enter-active, .drawer-fade-leave-active {
  transition: opacity 0.25s ease;
}
.drawer-fade-enter-from, .drawer-fade-leave-to {
  opacity: 0;
}
</style>
