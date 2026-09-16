<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">User Accounts & Role-Based Access Control (RBAC)</h1>
        <p class="page-subtitle">Manage Enterprise Administrator, Regional Broker, and VIP Buyer permissions across the platform.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-gold" @click="openCreateUserModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          <span>+ Create Staff / Buyer Account</span>
        </button>
      </div>
    </div>

    <div class="panel-card" style="padding:0; overflow:hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>User Profile</th>
              <th>Assigned Role</th>
              <th>Phone Number</th>
              <th>Region / Division</th>
              <th>Account Status</th>
              <th style="text-align:right;">Role & Account Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in userAccounts" :key="user.id">
              <td>
                <div class="flex items-center gap-3">
                  <img :src="user.avatar" :alt="user.name" class="table-thumb" style="border-radius:50%;" />
                  <div>
                    <strong style="color:#FFF; display:block; font-size:0.92rem;">{{ user.name }}</strong>
                    <div style="font-size:0.78rem; color:#94A3B8;">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge-admin" :class="user.role === 'admin' ? 'urgent' : user.role === 'agent' ? 'active' : 'neutral'">
                  {{ user.role.toUpperCase() }}
                </span>
              </td>
              <td style="color:#CBD5E1;">{{ user.phone }}</td>
              <td style="color:#CBD5E1;">{{ user.region }}</td>
              <td>
                <button 
                  class="badge-admin" 
                  :class="user.status === 'Active' ? 'active' : 'urgent'" 
                  style="cursor:pointer;"
                  @click="toggleUserStatus(user)"
                  title="Click to toggle Active / Suspended state"
                >
                  {{ user.status }}
                </button>
              </td>
              <td style="text-align:right;">
                <div class="action-btn-group">
                  <button class="btn btn-sm btn-outline-white" @click="cycleRole(user)">
                    Switch: {{ user.role }}
                  </button>
                  <button class="action-btn delete" @click="promptDeleteUser(user)" title="Delete User">
                    ✕
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 1: CREATE USER ACCOUNT
         ====================================================================== -->
    <div v-if="showCreateModal" class="admin-modal-overlay" @click.self="showCreateModal = false">
      <div class="admin-modal-card animate-fade-in-up">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Create User Account</h3>
          <button class="admin-modal-close" @click="showCreateModal = false">✕</button>
        </div>

        <form @submit.prevent="saveNewUser">
          <div class="admin-modal-body">
            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label" style="color:#CBD5E1;">Full Name *</label>
                <input v-model="form.name" type="text" required placeholder="e.g. Barrister Shafiul Alam" class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
              </div>
              <div class="form-group">
                <label class="form-label" style="color:#CBD5E1;">Email Address *</label>
                <input v-model="form.email" type="email" required placeholder="name@gbrel.com" class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
              </div>
            </div>

            <div class="grid grid-2" style="gap:14px; margin-bottom:14px;">
              <div class="form-group">
                <label class="form-label" style="color:#CBD5E1;">Assigned Role *</label>
                <select v-model="form.role" class="form-select" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);">
                  <option value="admin">Administrator (Full Access)</option>
                  <option value="agent">Licensed Real Estate Agent</option>
                  <option value="buyer">VIP Client / Buyer</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" style="color:#CBD5E1;">Region / Division</label>
                <select v-model="form.region" class="form-select" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);">
                  <option value="Dhaka HQ">Dhaka HQ (Central)</option>
                  <option value="Dhaka North">Dhaka North (Gulshan/Purbachal)</option>
                  <option value="Dhaka South">Dhaka South (Dhanmondi)</option>
                  <option value="Chittagong">Chittagong & Cox's Bazar</option>
                  <option value="Sylhet">Sylhet Division</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" style="color:#CBD5E1;">Phone Number *</label>
              <input v-model="form.phone" type="tel" required placeholder="+880 1711-..." class="form-input" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);" />
            </div>
          </div>

          <div class="admin-modal-footer">
            <button type="button" class="btn btn-sm btn-outline-white" @click="showCreateModal = false">Cancel</button>
            <button type="submit" class="btn btn-sm btn-emerald">Create Account</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 2: DELETE USER CONFIRMATION
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
import { ref, reactive } from 'vue'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()

const userAccounts = ref([
  { id: 1, name: 'Chief Administrator', email: 'admin@gbrel.com', role: 'admin', phone: '+880 1912-334455', region: 'Dhaka HQ', status: 'Active', avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=200&auto=format&fit=crop' },
  { id: 2, name: 'Tanvir Ahmed', email: 'tanvir@gbrel.com', role: 'agent', phone: '+880 1819-987654', region: 'Dhaka North', status: 'Active', avatar: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=200&auto=format&fit=crop' },
  { id: 3, name: 'Nusrat Jahan', email: 'nusrat@gbrel.com', role: 'agent', phone: '+880 1711-889900', region: 'Chittagong', status: 'Active', avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=200&auto=format&fit=crop' },
  { id: 4, name: 'Shere Ali', email: 'buyer@gbrel.com', role: 'buyer', phone: '+880 1711-234567', region: 'Dhaka', status: 'Active', avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop' }
])

const showCreateModal = ref(false)
const deleteUserTarget = ref<any | null>(null)

const form = reactive({
  name: '',
  email: '',
  role: 'agent',
  region: 'Dhaka HQ',
  phone: ''
})

const openCreateUserModal = () => {
  form.name = ''
  form.email = ''
  form.role = 'agent'
  form.region = 'Dhaka HQ'
  form.phone = ''
  showCreateModal.value = true
}

const saveNewUser = () => {
  userAccounts.value.push({
    id: Date.now(),
    name: form.name,
    email: form.email,
    role: form.role,
    phone: form.phone,
    region: form.region,
    status: 'Active',
    avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop'
  })
  toast.success('Account Created', `User ${form.name} registered with ${form.role.toUpperCase()} role.`)
  showCreateModal.value = false
}

const cycleRole = (user: any) => {
  if (user.role === 'buyer') user.role = 'agent'
  else if (user.role === 'agent') user.role = 'admin'
  else user.role = 'buyer'
  toast.info('Role Updated', `${user.name} switched to ${user.role.toUpperCase()}.`)
}

const toggleUserStatus = (user: any) => {
  user.status = user.status === 'Active' ? 'Suspended' : 'Active'
  toast.warning('Account Status Changed', `${user.name} is now ${user.status}.`)
}

const promptDeleteUser = (user: any) => {
  deleteUserTarget.value = user
}

const executeDeleteUser = () => {
  if (deleteUserTarget.value) {
    const name = deleteUserTarget.value.name
    const idx = userAccounts.value.findIndex(u => u.id === deleteUserTarget.value.id)
    if (idx > -1) {
      userAccounts.value.splice(idx, 1)
    }
    toast.info('User Deleted', `Account for ${name} removed.`)
    deleteUserTarget.value = null
  }
}
</script>
