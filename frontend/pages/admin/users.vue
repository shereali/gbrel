<template>
  <div class="admin-page animate-fade-in">
    <!-- Top Header -->
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">User Accounts & Role-Based Access Control (RBAC)</h1>
        <p class="page-subtitle">
          Granular role assignments, team directory, and enterprise permission matrix.
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
          <span>+ Create Staff / Buyer Account</span>
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
          <span>+ Define Custom Enterprise Role</span>
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="panel-tabs flex items-center gap-2" style="margin-bottom: 20px; border-bottom: 1px solid var(--admin-border-subtle); padding-bottom: 8px;">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'users' }" 
        @click="activeTab = 'users'"
        style="padding: 8px 18px; font-weight: 700; border-radius: 6px; cursor: pointer; border: none; font-size: 0.9rem;"
        :style="activeTab === 'users' ? 'background: var(--color-gold); color: #000;' : 'background: transparent; color: var(--admin-text-muted);'"
      >
        👥 Team Directory & Accounts ({{ userAccounts.length }})
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'matrix' }" 
        @click="activeTab = 'matrix'"
        style="padding: 8px 18px; font-weight: 700; border-radius: 6px; cursor: pointer; border: none; font-size: 0.9rem;"
        :style="activeTab === 'matrix' ? 'background: var(--color-gold); color: #000;' : 'background: transparent; color: var(--admin-text-muted);'"
      >
        🛡 Roles & Permissions Matrix ({{ roles.length }} Roles)
      </button>
    </div>

    <!-- TAB 1: TEAM ACCOUNTS & ACCESS DIRECTORY -->
    <div v-if="activeTab === 'users'" class="panel-card" style="padding:0; overflow:hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>User Profile</th>
              <th>Assigned Role</th>
              <th>Effective Privileges</th>
              <th>Phone Number</th>
              <th>Region</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading">
              <td colspan="7" style="text-align:center; padding:30px; color:var(--admin-text-muted);">
                <span class="animate-spin inline-block mr-2">◌</span> Loading team directory...
              </td>
            </tr>
            <tr v-else-if="userAccounts.length === 0">
              <td colspan="7" style="text-align:center; padding:30px; color:var(--admin-text-muted);">
                No users found. Click "+ Create Staff / Buyer Account" to register a member.
              </td>
            </tr>
            <tr v-for="user in userAccounts" :key="user.id">
              <td>
                <div class="flex items-center gap-3">
                  <img :src="user.avatar" :alt="user.name" class="table-thumb" style="border-radius:50%; width:38px; height:38px; object-fit:cover;" />
                  <div>
                    <strong style="display:block; font-size:0.92rem;">{{ user.name }}</strong>
                    <div style="font-size:0.78rem;" class="text-subtle">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span 
                  class="badge-admin" 
                  :class="user.role === 'admin' ? 'urgent' : user.role === 'property_manager' || user.role === 'agent' ? 'active' : 'neutral'"
                  style="font-weight:700;"
                >
                  {{ user.role_name || user.role.toUpperCase() }}
                </span>
              </td>
              <td>
                <div class="flex items-center gap-1 flex-wrap" style="max-width:260px;">
                  <span 
                    v-if="isSuperAdmin(user)" 
                    class="badge" 
                    style="background:rgba(212,175,55,0.15); color:var(--color-gold); font-size:0.75rem; border:1px solid rgba(212,175,55,0.3);"
                  >
                    ★ Full System Access (*)
                  </span>
                  <template v-else>
                    <span 
                      class="badge" 
                      style="background:rgba(59,130,246,0.15); color:#60A5FA; font-size:0.75rem; border:1px solid rgba(59,130,246,0.3);"
                    >
                      {{ (user.effective_permissions || []).length }} Capabilities
                    </span>
                    <span 
                      v-if="user.custom_permissions && user.custom_permissions.length > 0" 
                      class="badge" 
                      style="background:rgba(16,185,129,0.15); color:#34D399; font-size:0.72rem; border:1px solid rgba(16,185,129,0.3);"
                      title="Custom permission overrides active"
                    >
                      +{{ user.custom_permissions.length }} Overrides
                    </span>
                  </template>
                </div>
              </td>
              <td>{{ user.phone }}</td>
              <td>{{ user.region }}</td>
              <td>
                <button 
                  class="badge-admin" 
                  :class="user.status === 'Active' ? 'active' : 'urgent'" 
                  style="cursor:pointer;"
                  @click="toggleUserStatus(user)"
                  title="Click to toggle Active / Suspended"
                >
                  {{ user.status }}
                </button>
              </td>
              <td style="text-align:right;">
                <div class="action-btn-group justify-end">
                  <button class="btn btn-sm btn-outline-white" @click="openEditUserModal(user)" title="Edit permissions and role">
                    ⚙ Edit & Permissions
                  </button>
                  <button 
                    v-if="user.id !== 1" 
                    class="action-btn delete" 
                    @click="promptDeleteUser(user)" 
                    title="Delete Account"
                  >
                    ✕
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB 2: ROLES & PERMISSIONS MATRIX -->
    <div v-else class="space-y-6">
      <div class="panel-card" style="padding: 20px;">
        <div class="flex justify-between items-center flex-wrap gap-3" style="margin-bottom: 16px;">
          <div>
            <h2 style="font-size: 1.15rem; font-weight: 700; color: #FFF;">Configured Enterprise Roles</h2>
            <p style="font-size: 0.82rem; color: var(--admin-text-muted);">
              Manage role definitions, capabilities, and system safety restrictions.
            </p>
          </div>
          <button class="btn btn-sm btn-emerald" @click="openCreateRoleModal">
            + Add New Role
          </button>
        </div>

        <div class="grid grid-3" style="gap: 16px;">
          <div 
            v-for="role in roles" 
            :key="role.id" 
            class="role-card" 
            style="background: var(--admin-bg-surface-secondary); border: 1px solid var(--admin-border-subtle); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between;"
          >
            <div>
              <div class="flex justify-between items-start gap-2" style="margin-bottom: 8px;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-gold); margin: 0;">{{ role.name }}</h3>
                <span 
                  class="badge" 
                  :style="role.is_system ? 'background:rgba(239,68,68,0.15); color:#F87171; border:1px solid rgba(239,68,68,0.3); font-size:0.7rem;' : 'background:rgba(16,185,129,0.15); color:#34D399; border:1px solid rgba(16,185,129,0.3); font-size:0.7rem;'"
                >
                  {{ role.is_system ? 'System Role' : 'Custom Role' }}
                </span>
              </div>
              <p style="font-size: 0.82rem; color: var(--admin-text-muted); line-height: 1.4; margin-bottom: 14px;">
                {{ role.description }}
              </p>
              
              <div style="margin-bottom: 14px;">
                <div style="font-size: 0.76rem; font-weight: 700; color: #FFF; margin-bottom: 6px;">Permitted Modules:</div>
                <div class="flex flex-wrap gap-1">
                  <span 
                    v-if="role.permissions && role.permissions.includes('*')" 
                    class="badge" 
                    style="background:rgba(212,175,55,0.18); color:var(--color-gold); font-size:0.72rem;"
                  >
                    ★ All System Capabilities
                  </span>
                  <span 
                    v-else 
                    v-for="perm in (role.permissions || []).slice(0, 5)" 
                    :key="perm" 
                    class="badge" 
                    style="background:rgba(255,255,255,0.06); color:#E2E8F0; font-size:0.7rem;"
                  >
                    {{ perm }}
                  </span>
                  <span 
                    v-if="!role.permissions?.includes('*') && (role.permissions?.length || 0) > 5" 
                    class="badge" 
                    style="background:rgba(59,130,246,0.15); color:#60A5FA; font-size:0.7rem;"
                  >
                    +{{ (role.permissions?.length || 0) - 5 }} more
                  </span>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between pt-3" style="border-top: 1px solid var(--admin-border-subtle); margin-top: 10px;">
              <span style="font-size: 0.78rem; color: var(--admin-text-muted);">
                {{ role.users_count || 0 }} Active Users
              </span>
              <div class="flex items-center gap-2">
                <button class="btn btn-sm btn-outline-white" style="font-size:0.78rem; padding:4px 10px;" @click="openEditRoleModal(role)">
                  Edit Capabilities
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
         MODAL 1: CREATE USER ACCOUNT
         ====================================================================== -->
    <div v-if="showCreateModal" class="admin-modal-overlay" @click.self="showCreateModal = false">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:680px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Register Enterprise User Account</h3>
          <button class="admin-modal-close" @click="showCreateModal = false">✕</button>
        </div>

        <form @submit.prevent="saveNewUser">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input v-model="form.name" type="text" required placeholder="e.g. Barrister Shafiul Alam" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input v-model="form.email" type="email" required placeholder="name@gbrel.com" class="form-input" />
              </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Primary Role *</label>
                <select v-model="form.role_id" class="form-select" @change="onRoleSelectChange">
                  <option v-for="r in roles" :key="r.id" :value="r.id">
                    {{ r.name }} ({{ r.slug }})
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Region / Jurisdiction</label>
                <select v-model="form.region" class="form-select">
                  <option value="Dhaka HQ">Dhaka HQ (Central)</option>
                  <option value="Dhaka North">Dhaka North (Gulshan/Banani/Purbachal)</option>
                  <option value="Dhaka South">Dhaka South (Dhanmondi/Motijheel)</option>
                  <option value="Chittagong">Chittagong & Cox's Bazar</option>
                  <option value="Sylhet">Sylhet Division</option>
                </select>
              </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input v-model="form.phone" type="tel" required placeholder="+880 1711-000000" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Initial Password</label>
                <input v-model="form.password" type="password" placeholder="Default: gbrel2026!" class="form-input" />
              </div>
            </div>

            <!-- Custom Permission Overrides -->
            <div style="background:var(--admin-bg-surface); padding:14px; border-radius:var(--radius-sm); border:1px solid var(--admin-border-subtle); margin-top:14px;">
              <label class="form-label" style="color:var(--color-gold); font-weight:700; margin-bottom:8px;">
                Custom Permission Overrides (Optional granular privileges)
              </label>
              <div class="space-y-3" style="max-height: 220px; overflow-y: auto; padding-right: 6px;">
                <div v-for="(perms, moduleName) in permissionsGrouped" :key="moduleName" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 8px;">
                  <strong style="display:block; font-size:0.8rem; color:#60A5FA; margin-bottom:4px;">{{ moduleName }}</strong>
                  <div class="grid grid-2" style="gap:6px;">
                    <label v-for="p in perms" :key="p.slug" class="flex items-center gap-2" style="font-size:0.78rem; cursor:pointer; color:#CBD5E1;">
                      <input 
                        type="checkbox" 
                        :value="p.slug" 
                        v-model="form.custom_permissions" 
                        style="accent-color:var(--color-gold); width:14px; height:14px;" 
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
              {{ isSubmitting ? 'Creating Account...' : 'Confirm & Create Account' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 2: EDIT USER & PERMISSIONS
         ====================================================================== -->
    <div v-if="editingUser" class="admin-modal-overlay" @click.self="editingUser = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:680px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Edit User Access & Permissions: {{ editingUser.name }}</h3>
          <button class="admin-modal-close" @click="editingUser = null">✕</button>
        </div>

        <form @submit.prevent="saveUserChanges">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <input v-model="editForm.name" type="text" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input v-model="editForm.email" type="email" required class="form-input" />
              </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Assigned Role</label>
                <select v-model="editForm.role_id" class="form-select">
                  <option v-for="r in roles" :key="r.id" :value="r.id">
                    {{ r.name }} ({{ r.slug }})
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Account Status</label>
                <select v-model="editForm.status" class="form-select">
                  <option value="Active">Active</option>
                  <option value="Suspended">Suspended</option>
                </select>
              </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Phone</label>
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

            <!-- Granular Permissions Grid -->
            <div style="background:var(--admin-bg-surface); padding:14px; border-radius:var(--radius-sm); border:1px solid var(--admin-border-subtle);">
              <div class="flex justify-between items-center mb-2">
                <label class="form-label" style="color:var(--color-gold); font-weight:700; margin:0;">
                  Custom User Permission Overrides
                </label>
                <button 
                  type="button" 
                  class="btn btn-sm btn-outline-white" 
                  style="font-size:0.7rem; padding:2px 8px;"
                  @click="editForm.custom_permissions = []"
                >
                  Clear Overrides
                </button>
              </div>
              <p style="font-size:0.75rem; color:var(--admin-text-muted); margin-bottom:10px;">
                Checked items grant additional capabilities beyond the assigned role.
              </p>

              <div class="space-y-3" style="max-height: 240px; overflow-y: auto; padding-right: 6px;">
                <div v-for="(perms, moduleName) in permissionsGrouped" :key="moduleName" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 8px;">
                  <strong style="display:block; font-size:0.8rem; color:#60A5FA; margin-bottom:4px;">{{ moduleName }}</strong>
                  <div class="grid grid-2" style="gap:6px;">
                    <label v-for="p in perms" :key="p.slug" class="flex items-center gap-2" style="font-size:0.78rem; cursor:pointer; color:#CBD5E1;">
                      <input 
                        type="checkbox" 
                        :value="p.slug" 
                        v-model="editForm.custom_permissions" 
                        style="accent-color:var(--color-gold); width:14px; height:14px;" 
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
              {{ isSubmitting ? 'Saving...' : 'Save User Capabilities' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 3: DEFINE / EDIT ROLE
         ====================================================================== -->
    <div v-if="showRoleModal" class="admin-modal-overlay" @click.self="showRoleModal = false">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:680px;">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">{{ editingRoleTarget ? 'Edit Role Capabilities' : 'Create Custom Enterprise Role' }}</h3>
          <button class="admin-modal-close" @click="showRoleModal = false">✕</button>
        </div>

        <form @submit.prevent="saveRole">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label">Role Display Name *</label>
                <input v-model="roleForm.name" type="text" required placeholder="e.g. Senior Land Valuer" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Role Slug *</label>
                <input 
                  v-model="roleForm.slug" 
                  type="text" 
                  required 
                  :disabled="!!editingRoleTarget" 
                  placeholder="e.g. senior_land_valuer" 
                  class="form-input" 
                />
              </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
              <label class="form-label">Role Description</label>
              <textarea v-model="roleForm.description" rows="2" class="form-textarea" placeholder="Describe role responsibilities..."></textarea>
            </div>

            <div style="background:var(--admin-bg-surface); padding:14px; border-radius:var(--radius-sm); border:1px solid var(--admin-border-subtle);">
              <div class="flex justify-between items-center mb-2">
                <label class="form-label" style="color:var(--color-gold); font-weight:700; margin:0;">
                  Role Capabilities & Granted Privileges
                </label>
                <div class="flex items-center gap-2">
                  <button type="button" class="btn btn-sm btn-outline-white" style="font-size:0.7rem; padding:2px 8px;" @click="selectAllPermissions">
                    Select All
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-white" style="font-size:0.7rem; padding:2px 8px;" @click="roleForm.permissions = []">
                    Clear All
                  </button>
                </div>
              </div>

              <div class="space-y-3" style="max-height: 250px; overflow-y: auto; padding-right: 6px;">
                <div v-for="(perms, moduleName) in permissionsGrouped" :key="moduleName" style="border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 8px;">
                  <strong style="display:block; font-size:0.8rem; color:#60A5FA; margin-bottom:4px;">{{ moduleName }}</strong>
                  <div class="grid grid-2" style="gap:6px;">
                    <label v-for="p in perms" :key="p.slug" class="flex items-center gap-2" style="font-size:0.78rem; cursor:pointer; color:#CBD5E1;">
                      <input 
                        type="checkbox" 
                        :value="p.slug" 
                        v-model="roleForm.permissions" 
                        style="accent-color:var(--color-gold); width:14px; height:14px;" 
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
              {{ isSubmitting ? 'Saving Role...' : 'Save Role Capabilities' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 4: DELETE USER CONFIRMATION
         ====================================================================== -->
    <div v-if="deleteUserTarget" class="admin-modal-overlay" @click.self="deleteUserTarget = null">
      <div class="admin-modal-card animate-fade-in-up" style="max-width:440px;">
        <div class="admin-modal-header" style="background:#1E1622; border-bottom:1px solid rgba(239,68,68,0.2);">
          <h3 class="admin-modal-title" style="color:#F87171;">Confirm Account Deletion</h3>
          <button class="admin-modal-close" @click="deleteUserTarget = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="color:#E2E8F0; font-size:0.92rem; line-height:1.5;">
            Are you sure you want to delete the account for <strong>"{{ deleteUserTarget.name }}"</strong> ({{ deleteUserTarget.email }})?
          </p>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" @click="deleteUserTarget = null">Cancel</button>
          <button class="btn btn-sm" style="background:#EF4444; color:#FFF;" @click="executeDeleteUser">
            Delete Account
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
  custom_permissions: [] as string[]
})

const roleForm = reactive({
  name: '',
  slug: '',
  description: '',
  permissions: [] as string[]
})

const isSuperAdmin = (u: any) => {
  return u.role === 'admin' || (u.effective_permissions || []).includes('*')
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
        permissionsGrouped.value = pJson.data
        const slugs: string[] = []
        for (const mod in pJson.data) {
          for (const item of pJson.data[mod]) {
            slugs.push(item.slug)
          }
        }
        allPermissionSlugs.value = slugs
      }
    }
  } catch (err) {
    console.error('Failed to load RBAC data:', err)
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

    toast.success('Account Created', `User ${form.name} registered with assigned permissions.`)
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
  editForm.custom_permissions = Array.isArray(u.custom_permissions) ? [...u.custom_permissions] : []
}

const saveUserChanges = async () => {
  if (!editingUser.value) return
  isSubmitting.value = true
  try {
    const res = await fetch(useApiUrl(`/users/${editingUser.value.id}`), {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: editForm.name,
        email: editForm.email,
        role_id: editForm.role_id,
        phone: editForm.phone,
        region: editForm.region,
        status: editForm.status,
        custom_permissions: editForm.custom_permissions
      })
    })

    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || 'Failed to update user')
    }

    toast.success('Permissions Updated', `Capabilities updated for ${editForm.name}.`)
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
    toast.warning('Status Updated', `${user.name} is now ${next}.`)
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
    if (!res.ok) throw new Error('Failed to delete user on server')
    toast.info('Account Removed', `User ${name} delisted.`)
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

    toast.success('Role Saved', `Role ${roleForm.name} saved successfully.`)
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
    toast.info('Role Deleted', `Custom role ${role.name} removed.`)
    await loadData()
  } catch (err: any) {
    toast.error('Deletion Failed', err?.message || 'Could not delete role.')
  }
}
</script>
