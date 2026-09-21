<template>
  <div :class="['admin-shell', { 'admin-theme-light': adminTheme === 'light' }]">
    <!-- Admin Top Header -->
    <header class="admin-topbar">
      <div class="admin-topbar-inner">
        <!-- Left: Hamburger Toggle (Mobile/Tablet) + Brand + Status -->
        <div class="admin-topbar-left">
          <!-- Mobile Menu Hamburger Button (<= 1024px) -->
          <button 
            class="admin-hamburger-btn" 
            @click="mobileNavOpen = true" 
            aria-label="Open Admin Navigation Menu"
            title="Open Admin Navigation Menu"
          >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
              <line x1="3" y1="6" x2="21" y2="6"/>
              <line x1="3" y1="12" x2="21" y2="12"/>
              <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
          </button>

          <!-- Brand Logo -->
          <NuxtLink to="/admin" class="admin-brand" title="GBREL Admin Portal">
            <div class="admin-brand-icon" style="background: transparent; border: none; padding: 2px;">
              <img src="/img/logo-mark.png" alt="GBREL" style="width: 28px; height: 28px; object-fit: contain;" />
            </div>
            <div class="admin-brand-text">
              <span class="admin-brand-name">GBREL <span style="color:#D4AF37;">ADMIN</span></span>
              <span class="admin-brand-sub">PROPERTY MANAGEMENT</span>
            </div>
          </NuxtLink>

          <!-- Live API Indicator (Responsive) -->
          <div class="system-status-indicator" title="API System Connected">
            <span class="status-dot"></span>
            <span class="status-text-full">SYSTEM ONLINE</span>
            <span class="status-text-short">ONLINE</span>
          </div>
        </div>

        <!-- Right: Action Buttons & User Profile -->
        <div class="admin-topbar-right">
          <!-- Dark / Light Theme Toggle -->
          <button 
            class="btn btn-sm btn-outline-white nav-action-btn" 
            @click="toggleTheme" 
            :title="adminTheme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode'"
            aria-label="Toggle Dark or Light Theme"
          >
            <span v-if="adminTheme === 'light'">🌙 <span class="action-label-full">Dark</span></span>
            <span v-else>☀️ <span class="action-label-full">Light</span></span>
          </button>

          <!-- View Live Website -->
          <NuxtLink to="/" class="btn btn-sm btn-outline-white nav-action-btn" title="Return to Public Website">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="action-icon">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
              <polyline points="15 3 21 3 21 9"/>
              <line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
            <span class="action-label-full">Live Site ↗</span>
            <span class="action-label-short">Site</span>
          </NuxtLink>

          <!-- Quick Add Property -->
          <NuxtLink to="/admin/properties/create" class="btn btn-sm btn-gold nav-action-btn" title="Add New Property Listing">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span class="action-label-full">Add Property</span>
            <span class="action-label-short">Add</span>
          </NuxtLink>

          <!-- Admin Avatar Pill -->
          <div class="admin-user-pill" :title="user.name + ' (' + user.role + ')'">
            <img :src="user.avatar" :alt="user.name" class="admin-user-avatar" />
            <div class="admin-user-info">
              <span class="admin-user-name">{{ user.name }}</span>
              <span class="admin-user-role">{{ user.role.toUpperCase() }}</span>
            </div>
          </div>

          <!-- Logout Button -->
          <button class="btn btn-sm btn-logout" @click="handleLogout" title="Sign out of Admin Portal" aria-label="Sign out">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span class="logout-text">Sign Out</span>
          </button>
        </div>
      </div>
    </header>

    <!-- Mobile/Tablet Quick-Nav Chip Bar (<= 1024px) -->
    <div class="mobile-quick-nav-bar">
      <div class="mobile-quick-nav-track">
        <NuxtLink to="/admin" class="quick-nav-chip" exact-active-class="active">Overview</NuxtLink>
        <NuxtLink to="/admin/properties" class="quick-nav-chip" active-class="active">Properties</NuxtLink>
        <NuxtLink to="/admin/approvals" class="quick-nav-chip" active-class="active">Approvals (2)</NuxtLink>
        <NuxtLink to="/admin/viewings" class="quick-nav-chip" active-class="active">Viewings (4)</NuxtLink>
        <NuxtLink to="/admin/leads" class="quick-nav-chip" active-class="active">Leads (4)</NuxtLink>
        <NuxtLink to="/admin/agents" class="quick-nav-chip" active-class="active">Advisors</NuxtLink>
        <NuxtLink to="/admin/users" class="quick-nav-chip" active-class="active">Users</NuxtLink>
        <NuxtLink to="/admin/financials" class="quick-nav-chip" active-class="active">Financials</NuxtLink>
        <NuxtLink to="/admin/settings" class="quick-nav-chip" active-class="active">Settings</NuxtLink>
      </div>
    </div>

    <!-- Admin Workspace Grid -->
    <div class="admin-workspace-container">
      <div :class="['admin-workspace-grid', { 'sidebar-collapsed': sidebarCollapsed }]">
        <!-- 1. Dedicated Admin Desktop Sidebar (Hidden <= 1024px) -->
        <aside :class="['admin-sidebar', 'desktop-only-sidebar', { 'collapsed': sidebarCollapsed }]">
          <div class="sidebar-header-row">
            <span v-if="!sidebarCollapsed" class="sidebar-section-title" style="margin-bottom:0;">Navigation Hub</span>
            <button 
              class="sidebar-collapse-toggle-btn"
              @click="toggleSidebar"
              :title="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
              :aria-label="sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
            >
              <svg v-if="!sidebarCollapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="11 17 6 12 11 7"/>
                <polyline points="18 17 13 12 18 7"/>
              </svg>
              <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="13 17 18 12 13 7"/>
                <polyline points="6 17 11 12 6 7"/>
              </svg>
            </button>
          </div>
          <nav class="sidebar-nav">
            <NuxtLink to="/admin" class="sidebar-link" exact-active-class="active" title="Overview">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="3" y="3" width="7" height="9" rx="1"/>
                <rect x="14" y="3" width="7" height="5" rx="1"/>
                <rect x="14" y="12" width="7" height="9" rx="1"/>
                <rect x="3" y="16" width="7" height="5" rx="1"/>
              </svg>
              <span>Overview</span>
            </NuxtLink>

            <NuxtLink to="/admin/properties" class="sidebar-link" active-class="active" title="Properties">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
              </svg>
              <span>Properties</span>
              <span class="badge badge-status badge-pill-sm">Active</span>
            </NuxtLink>

            <NuxtLink to="/admin/approvals" class="sidebar-link" active-class="active" title="Legal & Verification">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M9 12l2 2 4-4"/>
                <path d="M12 3l7 4v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V7l7-4z"/>
              </svg>
              <span>Verification Queue</span>
              <span class="badge badge-urgent badge-pill-sm">2 Pending</span>
            </NuxtLink>

            <NuxtLink to="/admin/viewings" class="sidebar-link" active-class="active" title="Property Viewings">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              <span>Site Viewings</span>
              <span class="badge badge-rajuk badge-pill-sm">4 Tours</span>
            </NuxtLink>

            <NuxtLink to="/admin/agents" class="sidebar-link" active-class="active" title="Agents & Advisors">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
              </svg>
              <span>Agents & Advisors</span>
            </NuxtLink>

            <NuxtLink to="/admin/brochures" class="sidebar-link" active-class="active" title="Brochures">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
              <span>Brochures</span>
              <span class="badge badge-pill-sm" style="background:rgba(56,189,248,0.15); color:#38BDF8;">PDF</span>
            </NuxtLink>

            <NuxtLink to="/admin/users" class="sidebar-link" active-class="active" title="User Management & Roles">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              <span>Users & Roles</span>
            </NuxtLink>

            <NuxtLink to="/admin/leads" class="sidebar-link" active-class="active" title="Inquiries & Leads">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
                <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
              </svg>
              <span>Buyer Inquiries</span>
              <span class="badge badge-featured badge-pill-sm">4 Leads</span>
            </NuxtLink>

            <NuxtLink to="/admin/financials" class="sidebar-link" active-class="active" title="Financials">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              <span>Financials</span>
            </NuxtLink>

            <NuxtLink to="/admin/settings" class="sidebar-link" active-class="active" title="Settings">
              <svg class="sidebar-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06-.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
              </svg>
              <span>Settings</span>
            </NuxtLink>
          </nav>

          <!-- System Info Box in Sidebar -->
          <div class="sidebar-system-card">
            <div class="flex justify-between items-center" style="margin-bottom:6px;">
              <span style="font-size:0.75rem; color:#CBD5E1; font-weight:700; text-transform:uppercase;">Admin Guide</span>
              <span style="font-size:0.75rem; color:#10B981; font-weight:800;">Support Ready</span>
            </div>
            <div style="font-size:0.8rem; color:#94A3B8; line-height:1.4;">
              All property changes, prices, and user permissions update in real-time across the platform.
            </div>
          </div>
        </aside>

        <!-- 2. Main Admin Viewport -->
        <main class="admin-viewport">
          <slot />
        </main>
      </div>
    </div>

    <!-- ======================================================================
         OFF-CANVAS MOBILE & TABLET ADMIN DRAWER (SLIDE-IN <= 1024px)
         ====================================================================== -->
    <transition name="drawer-fade">
      <div v-if="mobileNavOpen" ref="drawerRoot" class="admin-drawer-overlay" @click.self="mobileNavOpen = false">
        <div class="admin-drawer-panel">
          <!-- Drawer Header -->
          <div class="drawer-header">
            <div class="flex items-center gap-2">
              <div class="admin-brand-icon" style="width:34px; height:34px; background:transparent; border:none;">
                <img src="/img/logo-mark.png" alt="GBREL" style="width:28px; height:28px; object-fit:contain;" />
              </div>
              <span class="admin-brand-name" style="font-size:1.15rem;">GBREL <span style="color:#D4AF37;">ADMIN</span></span>
            </div>
            <button class="drawer-close-btn" @click="mobileNavOpen = false" aria-label="Close Navigation">✕</button>
          </div>

          <!-- Drawer User Banner -->
          <div class="drawer-user-box">
            <img :src="user.avatar" :alt="user.name" class="drawer-avatar" />
            <div class="drawer-user-meta">
              <div class="drawer-name">{{ user.name }}</div>
              <div class="drawer-email">{{ user.email }}</div>
              <span class="badge badge-featured badge-pill-sm" style="margin-top:4px;">{{ user.role.toUpperCase() }}</span>
            </div>
          </div>

          <!-- Drawer Navigation Links -->
          <nav class="drawer-nav">
            <NuxtLink to="/admin" class="drawer-link" exact-active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="9" rx="1"/>
                <rect x="14" y="3" width="7" height="5" rx="1"/>
                <rect x="14" y="12" width="7" height="9" rx="1"/>
                <rect x="3" y="16" width="7" height="5" rx="1"/>
              </svg>
              <span>Overview & Analytics</span>
            </NuxtLink>

            <NuxtLink to="/admin/properties" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/>
              </svg>
              <span>Property Inventory</span>
              <span class="badge badge-status badge-pill-sm">CRUD</span>
            </NuxtLink>

            <NuxtLink to="/admin/approvals" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 12l2 2 4-4"/>
                <path d="M12 3l7 4v5c0 5-3.5 8.5-7 10-3.5-1.5-7-5-7-10V7l7-4z"/>
              </svg>
              <span>RAJUK / Legal Queue</span>
              <span class="badge badge-urgent badge-pill-sm">2 Pending</span>
            </NuxtLink>

            <NuxtLink to="/admin/viewings" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              <span>VIP Viewings Log</span>
              <span class="badge badge-rajuk badge-pill-sm">4 Tours</span>
            </NuxtLink>

            <NuxtLink to="/admin/agents" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="8" r="4"/>
                <path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>
              </svg>
              <span>Advisors & Brokers</span>
            </NuxtLink>

            <NuxtLink to="/admin/brochures" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
              </svg>
              <span>Brochure Vault</span>
              <span class="badge badge-pill-sm" style="background:rgba(56,189,248,0.15); color:#38BDF8;">PDF</span>
            </NuxtLink>

            <NuxtLink to="/admin/users" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              <span>Users & RBAC Control</span>
            </NuxtLink>

            <NuxtLink to="/admin/leads" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
                <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
              </svg>
              <span>Leads CRM & WhatsApp</span>
              <span class="badge badge-featured badge-pill-sm">4 Leads</span>
            </NuxtLink>

            <NuxtLink to="/admin/financials" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
              <span>Financials & Escrow</span>
            </NuxtLink>

            <NuxtLink to="/admin/settings" class="drawer-link" active-class="active" @click="mobileNavOpen = false">
              <svg class="sidebar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
              </svg>
              <span>System & Bank Config</span>
            </NuxtLink>
          </nav>

          <!-- Drawer Footer Action Buttons -->
          <div class="drawer-footer">
            <button class="btn btn-sm btn-outline-white" style="width:100%; margin-bottom:8px;" @click="toggleTheme">
              <span v-if="adminTheme === 'light'">🌙 Switch to Dark Mode</span>
              <span v-else>☀️ Switch to Light Mode</span>
            </button>
            <NuxtLink to="/admin/properties?action=new" class="btn btn-gold btn-sm" style="width:100%; margin-bottom:8px;" @click="mobileNavOpen = false">
              <span>Add Property</span>
            </NuxtLink>
            <NuxtLink to="/" class="btn btn-outline-white btn-sm" style="width:100%; margin-bottom:12px;" @click="mobileNavOpen = false">
              <span>View Live Website ↗</span>
            </NuxtLink>
            <button class="btn btn-sm btn-logout-full" @click="handleLogout">
              <span>Sign Out of Admin</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Global Enterprise Admin Toast Container -->
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
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'
import { useToast } from '~/composables/useToast'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

const router = useRouter()
const { user, logout } = useAuth()
const { toasts, remove: removeToast } = useToast()

const mobileNavOpen = ref(false)
const drawerRoot = ref<HTMLElement | null>(null)

const adminTheme = ref<'dark' | 'light'>('dark')
const sidebarCollapsed = ref(false)

useHead({
  htmlAttrs: {
    class: computed(() => adminTheme.value === 'light' ? 'admin-theme-light' : 'admin-theme-dark')
  },
  bodyAttrs: {
    class: computed(() => adminTheme.value === 'light' ? 'admin-theme-light' : 'admin-theme-dark')
  }
})

onMounted(() => {
  try {
    const savedTheme = localStorage.getItem('gbrel_admin_theme')
    if (savedTheme === 'light' || savedTheme === 'dark') {
      adminTheme.value = savedTheme
    }
    const savedCollapse = localStorage.getItem('gbrel_admin_sidebar_collapsed')
    if (savedCollapse !== null) {
      sidebarCollapsed.value = savedCollapse === 'true'
    }
  } catch {
    //
  }
})

const toggleTheme = () => {
  adminTheme.value = adminTheme.value === 'light' ? 'dark' : 'light'
  try {
    localStorage.setItem('gbrel_admin_theme', adminTheme.value)
  } catch {
    //
  }
}

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
  try {
    localStorage.setItem('gbrel_admin_sidebar_collapsed', String(sidebarCollapsed.value))
  } catch {
    //
  }
}

useOverlayBehavior(mobileNavOpen, () => { mobileNavOpen.value = false }, drawerRoot)

// Close drawer automatically on route change
watch(() => router.currentRoute.value.path, () => {
  mobileNavOpen.value = false
})

const handleLogout = async () => {
  mobileNavOpen.value = false
  await logout()
  router.push('/login')
}
</script>

<style>
.admin-shell {
  background-color: var(--admin-bg-base);
  min-height: 100vh;
  font-family: var(--font-sans);
  color: var(--admin-text-primary);
  overflow-x: hidden;
}

/* 1. Admin Topbar */
.admin-topbar {
  background: var(--admin-bg-surface);
  border-bottom: 1px solid var(--admin-border-subtle);
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
}

.admin-topbar-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  max-width: 100%;
  margin: 0;
  padding: 12px 32px;
  gap: 16px;
  min-height: 68px;
  box-sizing: border-box;
}

.admin-topbar-left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.admin-hamburger-btn {
  display: none;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #FFFFFF;
  width: 42px;
  height: 42px;
  border-radius: var(--radius-sm);
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: all var(--transition-fast);
}

.admin-hamburger-btn:hover {
  background: rgba(255, 255, 255, 0.18);
}

.admin-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  flex-shrink: 0;
}

.admin-brand-icon {
  width: 38px;
  height: 38px;
  background: var(--color-gold);
  border-radius: 10px;
  color: #0A1128;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(212, 175, 55, 0.25);
}

.admin-brand-name {
  font-family: var(--font-display);
  font-size: 1.25rem;
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.1;
  display: block;
  letter-spacing: -0.01em;
}

.admin-brand-sub {
  font-size: 0.68rem;
  color: var(--color-gold);
  font-weight: 700;
  letter-spacing: 0.12em;
  display: block;
}

.system-status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(16, 185, 129, 0.12);
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: #10B981;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 8px #10B981;
  flex-shrink: 0;
}

.status-text-short {
  display: none;
}

.admin-topbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.nav-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
}

.action-label-short {
  display: none;
}

.admin-user-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 3px 10px 3px 4px;
  border-radius: var(--radius-full);
}

.admin-user-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--color-gold);
  flex-shrink: 0;
}

.admin-user-name {
  font-size: 0.8rem;
  font-weight: 700;
  color: #FFFFFF;
  display: block;
  line-height: 1.2;
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.admin-user-role {
  font-size: 0.65rem;
  color: var(--color-gold-bright);
  font-weight: 700;
  display: block;
}

.btn-logout {
  color: #EF4444;
  border-color: rgba(239, 68, 68, 0.35);
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
}

.btn-logout:hover {
  background: rgba(239, 68, 68, 0.15);
  color: #FFFFFF;
}

.btn-logout-full {
  width: 100%;
  background: rgba(239, 68, 68, 0.12);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #FCA5A5;
  font-weight: 600;
  padding: 8px;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.btn-logout-full:hover {
  background: rgba(239, 68, 68, 0.22);
  color: #FFFFFF;
}

/* 2. Mobile Quick-Nav Chip Bar */
.mobile-quick-nav-bar {
  display: none;
  background: var(--admin-bg-surface);
  border-bottom: 1px solid var(--admin-border-subtle);
  padding: 8px 16px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.mobile-quick-nav-track {
  display: flex;
  gap: 8px;
  white-space: nowrap;
}

.quick-nav-chip {
  padding: 5px 12px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #CBD5E1;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: var(--radius-full);
  text-decoration: none;
  transition: all var(--transition-fast);
}

.quick-nav-chip:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #FFFFFF;
}

.quick-nav-chip.active {
  background: var(--color-emerald);
  border-color: var(--color-emerald);
  color: #FFFFFF;
}

/* 3. Admin Workspace Grid (Full Width) */
.admin-workspace-container {
  width: 100%;
  max-width: 100%;
  margin: 0;
  padding: 20px 32px 48px;
  box-sizing: border-box;
}

.admin-workspace-grid {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 24px;
  align-items: start;
  transition: grid-template-columns 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.admin-sidebar {
  background: var(--admin-bg-surface);
  border: 1px solid var(--admin-border-subtle);
  border-radius: var(--radius-xl);
  padding: 20px;
  height: fit-content;
  box-shadow: var(--shadow-sm);
}

.sidebar-section-title {
  font-size: 0.72rem;
  color: #64748B;
  text-transform: uppercase;
  font-weight: 800;
  letter-spacing: 0.08em;
  margin-bottom: 12px;
  padding-left: 8px;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: transparent;
  color: #CBD5E1;
  border-radius: var(--radius-md);
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
  text-decoration: none;
  width: 100%;
}

.sidebar-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #FFFFFF;
}

.sidebar-link.active {
  background: var(--color-emerald);
  color: #FFFFFF;
  font-weight: 700;
  box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
}

.sidebar-icon {
  flex-shrink: 0;
  color: var(--color-gold-bright);
}

.sidebar-link.active .sidebar-icon {
  color: #FFFFFF;
}

.badge-pill-sm {
  margin-left: auto;
  font-size: 0.68rem;
  padding: 2px 7px;
  border-radius: var(--radius-full);
}

.sidebar-system-card {
  margin-top: 24px;
  padding: 16px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px dashed rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-lg);
}

.admin-viewport {
  min-width: 0;
}

/* 4. Slide-in Mobile Drawer (<= 1024px) */
.admin-drawer-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.8);
  z-index: 2000;
  display: flex;
}

.admin-drawer-panel {
  width: 290px;
  max-width: 85vw;
  height: 100%;
  background: var(--admin-bg-surface);
  border-right: 1px solid var(--admin-border-subtle);
  color: var(--admin-text-primary);
  display: flex;
  flex-direction: column;
  padding: 20px;
  box-shadow: 15px 0 35px rgba(0, 0, 0, 0.6);
  overflow-y: auto;
}

.drawer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  margin-bottom: 16px;
}

.drawer-close-btn {
  width: 40px;
  height: 40px;
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

.drawer-user-box {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(212, 175, 55, 0.2);
  padding: 12px;
  border-radius: var(--radius-lg);
  margin-bottom: 18px;
}

.drawer-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--color-gold);
  flex-shrink: 0;
}

.drawer-user-meta {
  overflow: hidden;
}

.drawer-name {
  font-weight: 700;
  color: #FFFFFF;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.drawer-email {
  font-size: 0.74rem;
  color: #94A3B8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.drawer-nav {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
  margin-bottom: 20px;
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
  text-decoration: none;
  transition: all var(--transition-fast);
}

.drawer-link:hover, .drawer-link.active {
  background: rgba(255, 255, 255, 0.08);
  color: #FFFFFF;
}

.drawer-link.active {
  background: var(--color-emerald);
  color: #FFFFFF;
  box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
}

.drawer-footer {
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.drawer-fade-enter-active, .drawer-fade-leave-active {
  transition: opacity 0.22s ease;
}
.drawer-fade-enter-from, .drawer-fade-leave-to {
  opacity: 0;
}

/* 5. Responsive Breakpoint Rules */
@media (max-width: 1024px) {
  .admin-hamburger-btn {
    display: flex !important;
  }
  .desktop-only-sidebar {
    display: none !important;
  }
  .mobile-quick-nav-bar {
    display: block !important;
  }
  .admin-workspace-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  .admin-workspace-container {
    padding: 16px;
  }
}

@media (max-width: 768px) {
  .admin-topbar-inner {
    padding: 10px 14px;
    gap: 10px;
  }
  .admin-brand-sub {
    display: none;
  }
  .status-text-full {
    display: none;
  }
  .status-text-short {
    display: inline;
  }
  .admin-user-info {
    display: none;
  }
  .admin-user-pill {
    padding: 2px;
    border-radius: 50%;
  }
  .logout-text {
    display: none;
  }
  .btn-logout {
    padding: 8px;
    border-radius: var(--radius-sm);
  }
  .admin-workspace-container {
    padding: 14px 10px;
  }
}

@media (max-width: 480px) {
  .system-status-indicator {
    display: none;
  }
  .action-label-full {
    display: none;
  }
  .action-label-short {
    display: inline;
  }
  .admin-brand-name {
    font-size: 1.05rem;
  }
  .admin-brand-icon {
    width: 32px;
    height: 32px;
  }
  .admin-hamburger-btn {
    width: 38px;
    height: 38px;
  }
}
</style>
