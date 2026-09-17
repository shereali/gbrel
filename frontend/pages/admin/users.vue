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
                    <strong style="display:block; font-size:0.92rem;">{{ user.name }}</strong>
                    <div style="font-size:0.78rem;" class="text-subtle">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge-admin" :class="user.role === 'admin' ? 'urgent' : user.role === 'agent' ? 'active' : 'neutral'">
                  {{ user.role.toUpperCase() }}
                </span>
              </td>
              <td>{{ user.phone }}</td>
              <td>{{ user.region }}</td>
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
                <label class="form-label">Assigned Role *</label>
                <select v-model="form.role" class="form-select">
                  <option value="admin">Administrator (Full Access)</option>
                  <option value="agent">Licensed Real Estate Agent</option>
                  <option value="buyer">VIP Client / Buyer</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Region / Division</label>
                <select v-model="form.region" class="form-select">
                  <option value="Dhaka HQ">Dhaka HQ (Central)</option>
                  <option value="Dhaka North">Dhaka North (Gulshan/Purbachal)</option>
                  <option value="Dhaka South">Dhaka South (Dhanmondi)</option>
                  <option value="Chittagong">Chittagong & Cox's Bazar</option>
                  <option value="Sylhet">Sylhet Division</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Phone Number *</label>
              <input v-model="form.phone" type="tel" required placeholder="+880 1711-..." class="form-input" />
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
import { ref, reactive, onMounted } from 'vue'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()

const userAccounts = ref<any[]>([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const showCreateModal = ref(false)
const deleteUserTarget = ref<any | null>(null)

const form = reactive({
  name: '',
  email: '',
  role: 'agent',
  region: 'Dhaka HQ',
  phone: ''
})

const fetchUsers = async () => {
  isLoading.value = true
  try {
    const res = await fetch(useApiUrl('/users'))
    if (res.ok) {
      const json = await res.json()
      if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
        userAccounts.value = json.data.map(u => ({
          id: u.id,
          name: u.name,
          email: u.email,
          role: u.role || 'buyer',
          phone: u.phone || '+880 1711-000000',
          region: u.region || 'Dhaka HQ',
          status: u.status || 'Active',
          avatar: u.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop'
        }))
      }
    }
  } catch (err) {
    console.error('Failed to load users:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await fetchUsers()
})

const openCreateUserModal = () => {
  form.name = ''
  form.email = ''
  form.role = 'agent'
  form.region = 'Dhaka HQ'
  form.phone = ''
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
        role: form.role,
        phone: form.phone,
        region: form.region,
        status: 'Active',
        avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop'
      })
    })

    if (!res.ok) {
      const err = await res.json().catch(() => null)
      throw new Error(err?.message || `Failed to create user: status ${res.status}`)
    }

    toast.success('Account Created', `User ${form.name} registered in database.`)
    showCreateModal.value = false
    await fetchUsers()
  } catch (err: any) {
    toast.error('Creation Failed', err?.message || 'Could not create user.')
  } finally {
    isSubmitting.value = false
  }
}

const cycleRole = async (user: any) => {
  const currentRole = user.role
  const nextRole = currentRole === 'buyer' ? 'agent' : currentRole === 'agent' ? 'admin' : 'buyer'
  user.role = nextRole

  try {
    const res = await fetch(useApiUrl(`/users/${user.id}`), {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ role: nextRole })
    })
    if (!res.ok) throw new Error('Failed to update role on server')
    toast.info('Role Updated', `${user.name} switched to ${nextRole.toUpperCase()}.`)
  } catch (err: any) {
    user.role = currentRole
    toast.error('Update Failed', err?.message || 'Could not update role.')
  }
}

const toggleUserStatus = async (user: any) => {
  const prev = user.status
  const next = prev === 'Active' ? 'Suspended' : 'Active'
  user.status = next

  try {
    const res = await fetch(useApiUrl(`/users/${user.id}`), {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: next })
    })
    if (!res.ok) throw new Error('Failed to update status on server')
    toast.warning('Account Status Changed', `${user.name} is now ${next}.`)
  } catch (err: any) {
    user.status = prev
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
    userAccounts.value = userAccounts.value.filter(u => u.id !== id)
    toast.info('User Deleted', `Account for ${name} removed from database.`)
    deleteUserTarget.value = null
  } catch (err: any) {
    toast.error('Delete Failed', err?.message || 'Could not delete user account.')
  }
}
</script>
