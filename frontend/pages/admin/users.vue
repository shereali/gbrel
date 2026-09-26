<template>
  <div class="admin-page animate-fade-in">
    <!-- Top Header -->
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Team & User Management</h1>
        <p class="page-subtitle">
          Manage staff accounts, assign roles, and control system access with easy, intuitive controls.
        </p>
      </div>
      <div class="admin-header-actions flex items-center gap-3">
        <button 
          v-if="activeTab === 'users'" 
          class="btn btn-gold" 
          @click="openCreateUserModal"
        >
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Add User</span>
        </button>
        <button 
          v-else 
          class="btn btn-emerald" 
          @click="openCreateRoleModal"
        >
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>Create Role</span>
        </button>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="kpi-grid" style="margin-bottom: 20px;">
      <div class="kpi-card" style="padding: 16px 20px;">
        <div class="flex justify-between items-center mb-1">
          <span class="kpi-sub" style="font-weight: 600; text-transform: uppercase; font-size: 0.72rem;">Total Accounts</span>
          <span class="kpi-icon-pill gold" style="width: 28px; height: 28px; font-size: 0.8rem;">👥</span>
        </div>
        <div class="kpi-value" style="font-size: 1.6rem; margin-bottom: 2px;">{{ userAccounts.length }}</div>
        <div class="kpi-sub" style="font-size: 0.76rem;">Registered team & client accounts</div>
      </div>

      <div class="kpi-card" style="padding: 16px 20px;">
        <div class="flex justify-between items-center mb-1">
          <span class="kpi-sub" style="font-weight: 600; text-transform: uppercase; font-size: 0.72rem;">Active Accounts</span>
          <span class="kpi-icon-pill emerald" style="width: 28px; height: 28px; font-size: 0.8rem;">✔</span>
        </div>
        <div class="kpi-value" style="font-size: 1.6rem; margin-bottom: 2px; color: #10B981;">{{ activeAccountsCount }}</div>
        <div class="kpi-sub" style="font-size: 0.76rem;">Currently enabled for login</div>
      </div>

      <div class="kpi-card" style="padding: 16px 20px;">
        <div class="flex justify-between items-center mb-1">
          <span class="kpi-sub" style="font-weight: 600; text-transform: uppercase; font-size: 0.72rem;">Administrators</span>
          <span class="kpi-icon-pill blue" style="width: 28px; height: 28px; font-size: 0.8rem;">🛡</span>
        </div>
        <div class="kpi-value" style="font-size: 1.6rem; margin-bottom: 2px; color: #60A5FA;">{{ adminsCount }}</div>
        <div class="kpi-sub" style="font-size: 0.76rem;">Full management authority</div>
      </div>

      <div class="kpi-card" style="padding: 16px 20px;">
        <div class="flex justify-between items-center mb-1">
          <span class="kpi-sub" style="font-weight: 600; text-transform: uppercase; font-size: 0.72rem;">Defined Roles</span>
          <span class="kpi-icon-pill gold" style="width: 28px; height: 28px; font-size: 0.8rem;">★</span>
        </div>
        <div class="kpi-value" style="font-size: 1.6rem; margin-bottom: 2px; color: var(--color-gold);">{{ roles.length }}</div>
        <div class="kpi-sub" style="font-size: 0.76rem;">Permission profiles available</div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="panel-tabs flex items-center gap-2" style="margin-bottom: 18px; border-bottom: 1px solid var(--admin-border-subtle); padding-bottom: 8px;">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'users' }" 
        @click="activeTab = 'users'"
        style="padding: 9px 20px; font-weight: 700; border-radius: 8px; cursor: pointer; border: none; font-size: 0.9rem; transition: all 0.2s ease;"
        :style="activeTab === 'users' ? 'background: var(--color-gold); color: #000;' : 'background: transparent; color: var(--admin-text-muted);'"
      >
        👥 Team Directory & Accounts ({{ filteredUsers.length }} of {{ userAccounts.length }})
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'matrix' }" 
        @click="activeTab = 'matrix'"
        style="padding: 9px 20px; font-weight: 700; border-radius: 8px; cursor: pointer; border: none; font-size: 0.9rem; transition: all 0.2s ease;"
        :style="activeTab === 'matrix' ? 'background: var(--color-gold); color: #000;' : 'background: transparent; color: var(--admin-text-muted);'"
      >
        🛡 Roles & Permissions Matrix ({{ roles.length }})
      </button>
    </div>

    <!-- TAB 1: TEAM MEMBERS & USER DIRECTORY -->
    <div v-if="activeTab === 'users'" class="space-y-4">
      <!-- Search & Filters Toolbar -->
      <div class="panel-card" style="padding: 16px 20px;">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <!-- Left: Search Box -->
          <div class="flex-1" style="min-width: 240px; max-width: 420px;">
            <div style="position: relative;">
              <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search user by name, email, or phone..." 
                class="form-input" 
                style="padding-left: 36px; font-size: 0.88rem; width: 100%;"
              />
              <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--admin-text-muted); font-size: 0.9rem;">
                🔍
              </span>
              <button 
                v-if="searchQuery" 
                @click="searchQuery = ''" 
                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--admin-text-muted); cursor: pointer; font-size: 0.85rem;"
                title="Clear search"
              >
                ✕
              </button>
            </div>
          </div>

          <!-- Right: Filters (Role, Status, Reset) -->
          <div class="flex items-center gap-3 flex-wrap">
            <div class="flex items-center gap-2">
              <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: 600;">Role:</span>
              <select v-model="filterRole" class="form-select" style="padding: 6px 12px; font-size: 0.84rem; min-width: 140px;">
                <option value="all">All Roles</option>
                <option v-for="r in roles" :key="r.id" :value="r.slug">
                  {{ r.name }}
                </option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-weight: 600;">Status:</span>
              <select v-model="filterStatus" class="form-select" style="padding: 6px 12px; font-size: 0.84rem; min-width: 120px;">
                <option value="all">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Suspended">Suspended</option>
              </select>
            </div>

            <button 
              v-if="searchQuery || filterRole !== 'all' || filterStatus !== 'all'" 
              class="btn btn-sm btn-outline-white" 
              style="padding: 6px 12px; font-size: 0.8rem;"
              @click="resetFilters"
            >
              Reset Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Users Table Card -->
      <div class="panel-card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>User Details</th>
                <th>Assigned Role</th>
                <th>Access Level</th>
                <th>Phone Number</th>
                <th>Region</th>
                <th>Account Status</th>
                <th style="text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="7" style="text-align: center; padding: 40px; color: var(--admin-text-muted);">
                  <span class="animate-spin inline-block mr-2">◌</span> Loading team directory...
                </td>
              </tr>
              <tr v-else-if="filteredUsers.length === 0">
                <td colspan="7" style="text-align: center; padding: 40px; color: var(--admin-text-muted);">
                  <div style="font-size: 1.8rem; margin-bottom: 8px;">👤</div>
                  <strong style="color: var(--admin-text-primary); display: block; margin-bottom: 4px;">No users found</strong>
                  <p v-if="searchQuery || filterRole !== 'all' || filterStatus !== 'all'" style="font-size: 0.85rem;">
                    Try clearing your search or filter options.
                  </p>
                  <p v-else style="font-size: 0.85rem;">
                    Click the "Add User" button above to register a new staff member.
                  </p>
                </td>
              </tr>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td>
                  <div class="flex items-center gap-3">
                    <img 
                      :src="user.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop'" 
                      :alt="user.name" 
                      class="table-thumb" 
                      style="border-radius: 50%; width: 40px; height: 40px; object-fit: cover;" 
                    />
                    <div>
                      <strong style="display: block; font-size: 0.94rem; color: #FFF;">{{ user.name }}</strong>
                      <div style="font-size: 0.78rem; color: var(--admin-text-muted);">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span 
                    class="badge-admin" 
                    :class="getRoleBadgeClass(user.role)"
                    style="font-weight: 700; font-size: 0.74rem;"
                  >
                    {{ user.role_name || formatRoleName(user.role) }}
                  </span>
                </td>
                <td>
                  <div class="flex items-center gap-1 flex-wrap" style="max-width: 250px;">
                    <span 
                      v-if="isSuperAdmin(user)" 
                      class="badge" 
                      style="background: rgba(226, 101, 28,0.15); color: var(--color-gold); font-size: 0.74rem; border: 1px solid rgba(226, 101, 28,0.3); font-weight: 700;"
                    >
                      ★ Full System Access
                    </span>
                    <template v-else>
                      <span 
                        class="badge" 
                        style="background: rgba(59,130,246,0.15); color: #60A5FA; font-size: 0.74rem; border: 1px solid rgba(59,130,246,0.3);"
                      >
                        {{ (user.effective_permissions || []).length }} Permissions
                      </span>
                      <span 
                        v-if="user.custom_permissions && user.custom_permissions.length > 0" 
                        class="badge" 
                        style="background: rgba(16,185,129,0.15); color: #34D399; font-size: 0.72rem; border: 1px solid rgba(16,185,129,0.3);"
                        title="Special permission overrides enabled"
                      >
                        +{{ user.custom_permissions.length }} Custom
                      </span>
                    </template>
                  </div>
                </td>
                <td style="font-size: 0.86rem; color: #D6DDCB;">{{ user.phone || '—' }}</td>
                <td style="font-size: 0.86rem; color: #D6DDCB;">{{ user.region || 'Dhaka HQ' }}</td>
                <td>
                  <button 
                    class="badge-admin" 
                    :class="user.status === 'Active' ? 'active' : 'urgent'" 
                    style="cursor: pointer; transition: transform 0.15s ease;"
                    @click="toggleUserStatus(user)"
                    :title="'Click to set ' + (user.status === 'Active' ? 'Suspended' : 'Active')"
                  >
                    <span style="font-size: 0.7rem;">●</span>
                    {{ user.status }}
                  </button>
                </td>
                <td style="text-align: right;">
                  <div class="action-btn-group justify-end">
                    <button 
                      class="btn btn-sm btn-outline-white" 
                      style="font-size: 0.78rem; padding: 5px 12px;"
                      @click="openEditUserModal(user)" 
                      title="Edit user details and permissions"
                    >
                      Edit User
                    </button>
                    <button 
                      v-if="user.id !== 1" 
                      class="action-btn delete" 
                      @click="promptDeleteUser(user)" 
                      title="Delete User Account"
                    >
                      ✕
                    </button>
                    <span 
                      v-else 
                      style="font-size: 0.72rem; color: var(--admin-text-muted); padding: 4px 6px; font-style: italic;"
                      title="Primary Root Administrator cannot be removed"
                    >
                      Primary Admin
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB 2: ROLES & PERMISSIONS -->
    <div v-else class="space-y-6">
      <div class="panel-card" style="padding: 24px;">
        <div class="flex justify-between items-center flex-wrap gap-3" style="margin-bottom: 20px;">
          <div>
            <h2 style="font-size: 1.15rem; font-weight: 700; color: #FFF;">Configured Roles</h2>
            <p style="font-size: 0.82rem; color: var(--admin-text-muted); margin-top: 2px;">
              Roles define what features each team member can view and manage.
            </p>
          </div>
          <button class="btn btn-sm btn-emerald" @click="openCreateRoleModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Create Role</span>
          </button>
        </div>

        <div class="grid grid-3" style="gap: 18px;">
          <div 
            v-for="role in roles" 
            :key="role.id" 
            class="role-card" 
            style="background: var(--admin-bg-surface-secondary, rgba(255,255,255,0.03)); border: 1px solid var(--admin-border-subtle); border-radius: 12px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;"
          >
            <div>
              <div class="flex justify-between items-start gap-2" style="margin-bottom: 10px;">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-gold); margin: 0;">{{ role.name }}</h3>
                <span 
                  class="badge" 
                  :style="role.is_system ? 'background:rgba(239,68,68,0.15); color:#F87171; border:1px solid rgba(239,68,68,0.3); font-size:0.7rem;' : 'background:rgba(16,185,129,0.15); color:#34D399; border:1px solid rgba(16,185,129,0.3); font-size:0.7rem;'"
                >
                  {{ role.is_system ? 'System Role' : 'Custom Role' }}
                </span>
              </div>
              <p style="font-size: 0.84rem; color: var(--admin-text-muted); line-height: 1.45; margin-bottom: 16px;">
                {{ role.description }}
              </p>
              
              <div style="margin-bottom: 14px;">
                <div style="font-size: 0.76rem; font-weight: 700; color: #FFF; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                  Allowed Capabilities:
                </div>
                <div class="flex flex-wrap gap-1">
                  <span 
                    v-if="role.permissions && role.permissions.includes('*')" 
                    class="badge" 
                    style="background:rgba(226, 101, 28,0.18); color:var(--color-gold); font-size:0.74rem; font-weight:700;"
                  >
                    ★ Full System Control
                  </span>
                  <span 
                    v-else 
                    v-for="perm in (role.permissions || []).slice(0, 6)" 
                    :key="perm" 
                    class="badge" 
                    style="background:rgba(255,255,255,0.06); color:#D6DDCB; font-size:0.72rem;"
                  >
                    {{ formatPermissionSlug(perm) }}
                  </span>
                  <span 
                    v-if="!role.permissions?.includes('*') && (role.permissions?.length || 0) > 6" 
                    class="badge" 
                    style="background:rgba(59,130,246,0.15); color:#60A5FA; font-size:0.72rem;"
                  >
                    +{{ (role.permissions?.length || 0) - 6 }} more
                  </span>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between pt-3" style="border-top: 1px solid var(--admin-border-subtle); margin-top: 12px;">
              <span style="font-size: 0.8rem; color: var(--admin-text-muted);">
                <strong>{{ role.users_count || 0 }}</strong> Active Users
              </span>
              <div class="flex items-center gap-2">
                <button class="btn btn-sm btn-outline-white" style="font-size:0.78rem; padding:4px 10px;" @click="openEditRoleModal(role)">
                  Edit Role
                </button>
                <button 
                  v-if="!role.is_system" 
                  class="action-btn delete" 
                  style="padding:4px 8px; font-size:0.78rem;" 
                  @click="deleteRole(role)" 
                  title="Delete Custom Role"
                >
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 1: ADD USER
         ====================================================================== -->
    <div v-if="showCreateModal" class="admin-modal-overlay" @click.self="showCreateModal = false">
      <div class="admin-modal-card animate-fade-in-up" style="max-width: 680px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Add New User</h3>
          <button class="admin-modal-close" @click="showCreateModal = false">✕</button>
        </div>

        <form @submit.prevent="saveNewUser">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input v-model="form.name" type="text" required placeholder="e.g. Barrister Shafiul Alam" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input v-model="form.email" type="email" required placeholder="name@gbrel.com" class="form-input" />
              </div>
            </div>

            <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Assign Role *</label>
                <select v-model="form.role_id" class="form-select" @change="onRoleSelectChange">
                  <option v-for="r in roles" :key="r.id" :value="r.id">
                    {{ r.name }}
                  </option>
                </select>
                <p style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 4px;">
                  {{ getSelectedRoleDesc(form.role_id) }}
                </p>
              </div>
              <div class="form-group">
                <label class="form-label">Region / Branch</label>
                <select v-model="form.region" class="form-select">
                  <option value="Dhaka HQ">Dhaka HQ (Central)</option>
                  <option value="Dhaka North">Dhaka North (Gulshan/Banani/Purbachal)</option>
                  <option value="Dhaka South">Dhaka South (Dhanmondi/Motijheel)</option>
                  <option value="Chittagong">Chittagong & Cox's Bazar</option>
                  <option value="Sylhet">Sylhet Division</option>
                </select>
              </div>
            </div>

            <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input v-model="form.phone" type="tel" required placeholder="+880 1711-000000" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Initial Password</label>
                <input v-model="form.password" type="text" placeholder="Default: gbrel2026!" class="form-input" />
                <p style="font-size: 0.72rem; color: var(--admin-text-muted); margin-top: 4px;">
                  Leave blank to use default password (gbrel2026!).
                </p>
              </div>
            </div>

            <!-- Custom Permission Overrides -->
            <div style="background: var(--admin-bg-surface-secondary, rgba(255,255,255,0.03)); padding: 14px; border-radius: 8px; border: 1px solid var(--admin-border-subtle); margin-top: 14px;">
              <div class="flex justify-between items-center mb-2">
                <label class="form-label" style="color: var(--color-gold); font-weight: 700; margin: 0;">
                  Custom Permission Overrides (Optional)
                </label>
                <button 
                  v-if="form.custom_permissions.length > 0"
                  type="button" 
                  class="btn btn-sm btn-outline-white" 
                  style="font-size: 0.7rem; padding: 2px 8px;"
                  @click="form.custom_permissions = []"
                >
                  Clear All
                </button>
              </div>
              <p style="font-size: 0.76rem; color: var(--admin-text-muted); margin-bottom: 10px;">
                You can grant extra capabilities to this user in addition to their assigned role.
              </p>

              <div class="space-y-3" style="max-height: 200px; overflow-y: auto; padding-right: 6px;">
                <div v-for="(perms, moduleName) in permissionsGrouped" :key="moduleName" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 8px;">
                  <div class="flex justify-between items-center mb-1">
                    <strong style="font-size: 0.8rem; color: #60A5FA;">{{ moduleName }}</strong>
                    <button 
                      type="button" 
                      style="background: none; border: none; font-size: 0.72rem; color: var(--admin-text-muted); cursor: pointer; text-decoration: underline;"
                      @click="toggleModulePerms(form.custom_permissions, perms)"
                    >
                      Toggle All
                    </button>
                  </div>
                  <div class="grid grid-2" style="gap: 6px;">
                    <label v-for="p in perms" :key="p.slug" class="flex items-center gap-2" style="font-size: 0.78rem; cursor: pointer; color: #C3CCB6;">
                      <input 
                        type="checkbox" 
                        :value="p.slug" 
                        v-model="form.custom_permissions" 
                        style="accent-color: var(--color-gold); width: 14px; height: 14px;" 
                      />
                      <span>{{ p.name }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="showCreateModal = false">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald" :disabled="isSubmitting">
              {{ isSubmitting ? 'Adding User...' : 'Add User' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 2: EDIT USER
         ====================================================================== -->
    <div v-if="editingUser" class="admin-modal-overlay" @click.self="editingUser = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width: 680px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Edit User: {{ editingUser.name }}</h3>
          <button class="admin-modal-close" @click="editingUser = null">✕</button>
        </div>

        <form @submit.prevent="saveUserChanges">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input v-model="editForm.name" type="text" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input v-model="editForm.email" type="email" required class="form-input" />
              </div>
            </div>

            <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Assigned Role</label>
                <select v-model="editForm.role_id" class="form-select">
                  <option v-for="r in roles" :key="r.id" :value="r.id">
                    {{ r.name }}
                  </option>
                </select>
                <p style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 4px;">
                  {{ getSelectedRoleDesc(editForm.role_id) }}
                </p>
              </div>
              <div class="form-group">
                <label class="form-label">Account Status</label>
                <select v-model="editForm.status" class="form-select">
                  <option value="Active">Active (Can log in)</option>
                  <option value="Suspended">Suspended (Access blocked)</option>
                </select>
              </div>
            </div>

            <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input v-model="editForm.phone" type="tel" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Region</label>
                <select v-model="editForm.region" class="form-select">
                  <option value="Dhaka HQ">Dhaka HQ (Central)</option>
                  <option value="Dhaka North">Dhaka North (Gulshan/Banani/Purbachal)</option>
                  <option value="Dhaka South">Dhaka South (Dhanmondi/Motijheel)</option>
                  <option value="Chittagong">Chittagong & Cox's Bazar</option>
                  <option value="Sylhet">Sylhet Division</option>
                </select>
              </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">New Password (Leave blank to keep existing password)</label>
              <input v-model="editForm.password" type="text" placeholder="Enter new password if changing..." class="form-input" />
            </div>

            <!-- Custom Permission Overrides -->
            <div style="background: var(--admin-bg-surface-secondary, rgba(255,255,255,0.03)); padding: 14px; border-radius: 8px; border: 1px solid var(--admin-border-subtle);">
              <div class="flex justify-between items-center mb-2">
                <label class="form-label" style="color: var(--color-gold); font-weight: 700; margin: 0;">
                  Custom Permission Overrides
                </label>
                <button 
                  v-if="editForm.custom_permissions.length > 0"
                  type="button" 
                  class="btn btn-sm btn-outline-white" 
                  style="font-size: 0.7rem; padding: 2px 8px;"
                  @click="editForm.custom_permissions = []"
                >
                  Clear Overrides
                </button>
              </div>
              <p style="font-size: 0.76rem; color: var(--admin-text-muted); margin-bottom: 10px;">
                Checked items grant capabilities beyond what is included in their role.
              </p>

              <div class="space-y-3" style="max-height: 200px; overflow-y: auto; padding-right: 6px;">
                <div v-for="(perms, moduleName) in permissionsGrouped" :key="moduleName" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 8px;">
                  <div class="flex justify-between items-center mb-1">
                    <strong style="font-size: 0.8rem; color: #60A5FA;">{{ moduleName }}</strong>
                    <button 
                      type="button" 
                      style="background: none; border: none; font-size: 0.72rem; color: var(--admin-text-muted); cursor: pointer; text-decoration: underline;"
                      @click="toggleModulePerms(editForm.custom_permissions, perms)"
                    >
                      Toggle All
                    </button>
                  </div>
                  <div class="grid grid-2" style="gap: 6px;">
                    <label v-for="p in perms" :key="p.slug" class="flex items-center gap-2" style="font-size: 0.78rem; cursor: pointer; color: #C3CCB6;">
                      <input 
                        type="checkbox" 
                        :value="p.slug" 
                        v-model="editForm.custom_permissions" 
                        style="accent-color: var(--color-gold); width: 14px; height: 14px;" 
                      />
                      <span>{{ p.name }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="editingUser = null">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald" :disabled="isSubmitting">
              {{ isSubmitting ? 'Saving Changes...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 3: CREATE / EDIT ROLE
         ====================================================================== -->
    <div v-if="showRoleModal" class="admin-modal-overlay" @click.self="showRoleModal = false">
      <div class="admin-modal-card animate-fade-in-up" style="max-width: 680px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">{{ editingRoleTarget ? 'Edit Role: ' + roleForm.name : 'Create New Role' }}</h3>
          <button class="admin-modal-close" @click="showRoleModal = false">✕</button>
        </div>

        <form @submit.prevent="saveRole">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap: 14px; margin-bottom: 14px;">
              <div class="form-group">
                <label class="form-label">Role Name *</label>
                <input v-model="roleForm.name" type="text" required placeholder="e.g. Senior Land Valuer" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Role Identifier *</label>
                <input 
                  v-model="roleForm.slug" 
                  type="text" 
                  required 
                  :disabled="!!editingRoleTarget" 
                  placeholder="e.g. land_valuer" 
                  class="form-input" 
                />
              </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label">Role Description</label>
              <textarea v-model="roleForm.description" rows="2" class="form-textarea" placeholder="Explain what responsibilities this role handles..."></textarea>
            </div>

            <div style="background: var(--admin-bg-surface-secondary, rgba(255,255,255,0.03)); padding: 14px; border-radius: 8px; border: 1px solid var(--admin-border-subtle);">
              <div class="flex justify-between items-center mb-2">
                <label class="form-label" style="color: var(--color-gold); font-weight: 700; margin: 0;">
                  Granted Capabilities
                </label>
                <div class="flex items-center gap-2">
                  <button type="button" class="btn btn-sm btn-outline-white" style="font-size: 0.7rem; padding: 2px 8px;" @click="selectAllPermissions">
                    Select All
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-white" style="font-size: 0.7rem; padding: 2px 8px;" @click="roleForm.permissions = []">
                    Clear All
                  </button>
                </div>
              </div>

              <div class="space-y-3" style="max-height: 240px; overflow-y: auto; padding-right: 6px;">
                <div v-for="(perms, moduleName) in permissionsGrouped" :key="moduleName" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 8px;">
                  <strong style="display: block; font-size: 0.8rem; color: #60A5FA; margin-bottom: 4px;">{{ moduleName }}</strong>
                  <div class="grid grid-2" style="gap: 6px;">
                    <label v-for="p in perms" :key="p.slug" class="flex items-center gap-2" style="font-size: 0.78rem; cursor: pointer; color: #C3CCB6;">
                      <input 
                        type="checkbox" 
                        :value="p.slug" 
                        v-model="roleForm.permissions" 
                        style="accent-color: var(--color-gold); width: 14px; height: 14px;" 
                      />
                      <span>{{ p.name }}</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="showRoleModal = false">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald" :disabled="isSubmitting">
              {{ isSubmitting ? 'Saving Role...' : 'Save Role' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 4: DELETE CONFIRMATION
         ====================================================================== -->
    <div v-if="deleteUserTarget" class="admin-modal-overlay" @click.self="deleteUserTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width: 440px;">
        <div class="admin-modal-header" style="background: rgba(239,68,68,0.1); border-bottom: 1px solid rgba(239,68,68,0.2);">
          <h3 class="admin-modal-title" style="color: #F87171;">Delete User Account</h3>
          <button class="admin-modal-close" @click="deleteUserTarget = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="color: #D6DDCB; font-size: 0.94rem; line-height: 1.5; margin-bottom: 8px;">
            Are you sure you want to remove the user account for:
          </p>
          <div style="background: rgba(0,0,0,0.25); padding: 12px; border-radius: 8px; border: 1px solid var(--admin-border-subtle); margin-bottom: 12px;">
            <strong style="color: #FFF; font-size: 1rem; display: block;">{{ deleteUserTarget.name }}</strong>
            <span style="color: var(--admin-text-muted); font-size: 0.84rem;">{{ deleteUserTarget.email }}</span>
          </div>
          <p style="color: var(--admin-text-muted); font-size: 0.8rem;">
            This user will no longer be able to log in to the system.
          </p>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" @click="deleteUserTarget = null">Cancel</button>
          <button class="btn btn-sm" style="background: #EF4444; color: #FFF;" @click="executeDeleteUser">
            Confirm Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()

const activeTab = ref<'users' | 'matrix'>('users')
const userAccounts = ref<any[]>([])
const roles = ref<any[]>([])
const permissionsGrouped = ref<Record<string, any[]>>({})
const allPermissionSlugs = ref<string[]>([])

// Filter & Search state
const searchQuery = ref('')
const filterRole = ref('all')
const filterStatus = ref('all')

const isLoading = ref(false)
const isSubmitting = ref(false)

const showCreateModal = ref(false)
const editingUser = ref<any | null>(null)
const deleteUserTarget = ref<any | null>(null)

const showRoleModal = ref(false)
const editingRoleTarget = ref<any | null>(null)

// Forms
const form = reactive({
  name: '',
  email: '',
  role_id: 1,
  role: 'admin',
  region: 'Dhaka HQ',
  phone: '',
  password: '',
  custom_permissions: [] as string[]
})

const editForm = reactive({
  name: '',
  email: '',
  role_id: 1,
  phone: '',
  region: 'Dhaka HQ',
  status: 'Active',
  password: '',
  custom_permissions: [] as string[]
})

const roleForm = reactive({
  name: '',
  slug: '',
  description: '',
  permissions: [] as string[]
})

// Quick KPI Computed properties
const activeAccountsCount = computed(() => {
  return userAccounts.value.filter(u => u.status === 'Active').length
})

const adminsCount = computed(() => {
  return userAccounts.value.filter(u => u.role === 'admin' || (u.effective_permissions || []).includes('*')).length
})

// Filtered Users Computed
const filteredUsers = computed(() => {
  let list = userAccounts.value

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(u => 
      (u.name && u.name.toLowerCase().includes(q)) ||
      (u.email && u.email.toLowerCase().includes(q)) ||
      (u.phone && u.phone.toLowerCase().includes(q))
    )
  }

  if (filterRole.value !== 'all') {
    list = list.filter(u => u.role === filterRole.value)
  }

  if (filterStatus.value !== 'all') {
    list = list.filter(u => u.status === filterStatus.value)
  }

  return list
})

const resetFilters = () => {
  searchQuery.value = ''
  filterRole.value = 'all'
  filterStatus.value = 'all'
}

const isSuperAdmin = (u: any) => {
  return u.role === 'admin' || (u.effective_permissions || []).includes('*')
}

const formatRoleName = (slug: string) => {
  if (!slug) return 'User'
  return slug
    .replace(/_/g, ' ')
    .replace(/\b\w/g, l => l.toUpperCase())
}

const formatPermissionSlug = (slug: string) => {
  if (!slug) return ''
  return slug
    .replace(/[._]/g, ' ')
    .replace(/\b\w/g, l => l.toUpperCase())
}

const getRoleBadgeClass = (role: string) => {
  if (role === 'admin') return 'urgent'
  if (role === 'property_manager' || role === 'agent') return 'active'
  return 'neutral'
}

const getSelectedRoleDesc = (roleId: number) => {
  const r = roles.value.find(item => item.id === roleId)
  return r ? r.description : ''
}

const toggleModulePerms = (targetArray: string[], perms: any[]) => {
  const slugs = perms.map(p => p.slug)
  const allSelected = slugs.every(s => targetArray.includes(s))

  if (allSelected) {
    // Deselect all
    slugs.forEach(s => {
      const idx = targetArray.indexOf(s)
      if (idx !== -1) targetArray.splice(idx, 1)
    })
  } else {
    // Select all
    slugs.forEach(s => {
      if (!targetArray.includes(s)) targetArray.push(s)
    })
  }
}

// Fetch all Data
const loadData = async () => {
  isLoading.value = true
  try {
    const [resUsers, resRoles, resPerms] = await Promise.all([
      fetch(useApiUrl('/users')),
      fetch(useApiUrl('/roles')),
      fetch(useApiUrl('/permissions'))
    ])

    if (resUsers.ok) {
      const uJson = await resUsers.json()
      if (uJson?.data) userAccounts.value = uJson.data
    }

    if (resRoles.ok) {
      const rJson = await resRoles.json()
      if (rJson?.data) roles.value = rJson.data
    }

    if (resPerms.ok) {
      const pJson = await resPerms.json()
      if (pJson?.data) {
        permissionsGrouped.value = pJson.grouped || pJson.data
        const slugs: string[] = []
        for (const mod in permissionsGrouped.value) {
          for (const item of permissionsGrouped.value[mod]) {
            slugs.push(item.slug)
          }
        }
        allPermissionSlugs.value = slugs
      }
    }
  } catch (err) {
    console.error('Failed to load user management data:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await loadData()
})

const onRoleSelectChange = () => {
  const matched = roles.value.find(r => r.id === form.role_id)
  if (matched) {
    form.role = matched.slug
  }
}

const openCreateUserModal = () => {
  form.name = ''
  form.email = ''
  form.role_id = roles.value[0]?.id || 1
  form.role = roles.value[0]?.slug || 'admin'
  form.region = 'Dhaka HQ'
  form.phone = ''
  form.password = ''
  form.custom_permissions = []
  showCreateModal.value = true
}

const saveNewUser = async () => {
  isSubmitting.value = true
  try {
    const res = await fetch(useApiUrl('/users'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: form.name,
        email: form.email,
        role_id: form.role_id,
        role: form.role,
        region: form.region,
        phone: form.phone,
        password: form.password || 'gbrel2026!',
        custom_permissions: form.custom_permissions,
        status: 'Active'
      })
    })

    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || `Failed to create user: status ${res.status}`)
    }

    toast.success('User Added', `Account for ${form.name} created successfully.`)
    showCreateModal.value = false
    await loadData()
  } catch (err: any) {
    toast.error('Creation Failed', err?.message || 'Could not create user.')
  } finally {
    isSubmitting.value = false
  }
}

const openEditUserModal = (u: any) => {
  editingUser.value = u
  editForm.name = u.name
  editForm.email = u.email
  editForm.role_id = u.role_id || (roles.value.find(r => r.slug === u.role)?.id || 1)
  editForm.phone = u.phone || ''
  editForm.region = u.region || 'Dhaka HQ'
  editForm.status = u.status || 'Active'
  editForm.password = ''
  editForm.custom_permissions = Array.isArray(u.custom_permissions) ? [...u.custom_permissions] : []
}

const saveUserChanges = async () => {
  if (!editingUser.value) return
  isSubmitting.value = true
  try {
    const payload: any = {
      name: editForm.name,
      email: editForm.email,
      role_id: editForm.role_id,
      phone: editForm.phone,
      region: editForm.region,
      status: editForm.status,
      custom_permissions: editForm.custom_permissions
    }
    if (editForm.password) {
      payload.password = editForm.password
    }

    const res = await fetch(useApiUrl(`/users/${editingUser.value.id}`), {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || 'Failed to update user')
    }

    toast.success('User Updated', `Changes saved for ${editForm.name}.`)
    editingUser.value = null
    await loadData()
  } catch (err: any) {
    toast.error('Update Failed', err?.message || 'Could not update user.')
  } finally {
    isSubmitting.value = false
  }
}

const toggleUserStatus = async (user: any) => {
  const next = user.status === 'Active' ? 'Suspended' : 'Active'
  user.status = next
  try {
    const res = await fetch(useApiUrl(`/users/${user.id}`), {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: next })
    })
    if (!res.ok) throw new Error('Failed to update status on server')
    toast.warning('Status Changed', `${user.name} is now ${next}.`)
  } catch (err: any) {
    user.status = user.status === 'Active' ? 'Suspended' : 'Active'
    toast.error('Update Failed', err?.message || 'Could not update status.')
  }
}

const promptDeleteUser = (user: any) => {
  deleteUserTarget.value = user
}

const executeDeleteUser = async () => {
  if (!deleteUserTarget.value) return
  const id = deleteUserTarget.value.id
  const name = deleteUserTarget.value.name

  try {
    const res = await fetch(useApiUrl(`/users/${id}`), {
      method: 'DELETE'
    })
    if (!res.ok) throw new Error('Failed to delete user')
    toast.info('User Removed', `Account for ${name} has been deleted.`)
    deleteUserTarget.value = null
    await loadData()
  } catch (err: any) {
    toast.error('Delete Failed', err?.message || 'Could not delete user account.')
  }
}

// Role Management
const openCreateRoleModal = () => {
  editingRoleTarget.value = null
  roleForm.name = ''
  roleForm.slug = ''
  roleForm.description = ''
  roleForm.permissions = []
  showRoleModal.value = true
}

const openEditRoleModal = (role: any) => {
  editingRoleTarget.value = role
  roleForm.name = role.name
  roleForm.slug = role.slug
  roleForm.description = role.description || ''
  roleForm.permissions = Array.isArray(role.permissions) ? [...role.permissions] : []
  showRoleModal.value = true
}

const selectAllPermissions = () => {
  roleForm.permissions = [...allPermissionSlugs.value]
}

const saveRole = async () => {
  isSubmitting.value = true
  try {
    const isEdit = !!editingRoleTarget.value
    const url = isEdit ? useApiUrl(`/roles/${editingRoleTarget.value.id}`) : useApiUrl('/roles')
    const method = isEdit ? 'PUT' : 'POST'

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: roleForm.name,
        slug: roleForm.slug,
        description: roleForm.description,
        permissions: roleForm.permissions
      })
    })

    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || 'Failed to save role')
    }

    toast.success('Role Saved', `Role "${roleForm.name}" saved successfully.`)
    showRoleModal.value = false
    await loadData()
  } catch (err: any) {
    toast.error('Role Save Failed', err?.message || 'Could not save role.')
  } finally {
    isSubmitting.value = false
  }
}

const deleteRole = async (role: any) => {
  if (!confirm(`Are you sure you want to delete custom role "${role.name}"?`)) return
  try {
    const res = await fetch(useApiUrl(`/roles/${role.id}`), { method: 'DELETE' })
    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || 'Failed to delete role')
    }
    toast.info('Role Deleted', `Custom role "${role.name}" has been removed.`)
    await loadData()
  } catch (err: any) {
    toast.error('Deletion Failed', err?.message || 'Could not delete role.')
  }
}
</script>
