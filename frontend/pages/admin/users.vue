<template>
  <div class="admin-page animate-fade-in">
    <div class="flex justify-between items-center flex-wrap gap-4" style="margin-bottom: 24px;">
      <div>
        <h1 class="page-title">User Accounts & Role-Based Access Control (RBAC)</h1>
        <p class="page-subtitle">Manage Buyer, Agent, and Administrator permissions across the platform.</p>
      </div>
      <button class="btn btn-sm btn-gold" @click="openAddUserModal">+ Create User Account</button>
    </div>

    <div class="panel-card" style="padding:0; overflow:hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>User Profile</th>
              <th>Assigned Role</th>
              <th>Phone Number</th>
              <th>Primary Division</th>
              <th>Account Status</th>
              <th style="text-align:right;">Role Management</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in userAccounts" :key="user.id">
              <td>
                <div class="flex items-center gap-3">
                  <img :src="user.avatar" class="table-thumb" style="border-radius:50%;" />
                  <div>
                    <strong style="color:#FFF;">{{ user.name }}</strong>
                    <div style="font-size:0.78rem; color:#CBD5E1;">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge" :class="user.role === 'admin' ? 'badge-urgent' : user.role === 'agent' ? 'badge-featured' : 'badge-status'">
                  {{ user.role.toUpperCase() }}
                </span>
              </td>
              <td style="color:#CBD5E1;">{{ user.phone }}</td>
              <td style="color:#CBD5E1;">{{ user.region }}</td>
              <td>
                <span class="badge badge-rajuk">Active</span>
              </td>
              <td style="text-align:right;">
                <button class="btn btn-sm btn-outline-white" @click="cycleUserRole(user.id)">
                  Switch Role ({{ user.role }})
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

definePageMeta({
  layout: 'admin'
})

const userAccounts = ref([
  { id: 1, name: 'Chief Administrator', email: 'admin@gbrel.com', role: 'admin', phone: '+880 1912-334455', region: 'Dhaka HQ', avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=200&auto=format&fit=crop' },
  { id: 2, name: 'Tanvir Ahmed', email: 'tanvir@gbrel.com', role: 'agent', phone: '+880 1819-987654', region: 'Dhaka North', avatar: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=200&auto=format&fit=crop' },
  { id: 3, name: 'Nusrat Jahan', email: 'nusrat@gbrel.com', role: 'agent', phone: '+880 1711-889900', region: 'Chittagong & Coxs Bazar', avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=200&auto=format&fit=crop' },
  { id: 4, name: 'Shere Ali', email: 'buyer@gbrel.com', role: 'buyer', phone: '+880 1711-234567', region: 'Dhaka', avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop' }
])

const cycleUserRole = (id: number) => {
  const u = userAccounts.value.find(usr => usr.id === id)
  if (u) {
    if (u.role === 'buyer') u.role = 'agent'
    else if (u.role === 'agent') u.role = 'admin'
    else u.role = 'buyer'
  }
}

const openAddUserModal = () => {
  const name = prompt('Enter staff full name:')
  if (name) {
    userAccounts.value.push({
      id: Date.now(),
      name,
      email: `${name.toLowerCase().replace(/\s+/g, '')}@gbrel.com`,
      role: 'agent',
      phone: '+880 1819-000000',
      region: 'Dhaka HQ',
      avatar: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=200&auto=format&fit=crop'
    })
  }
}
</script>

<style scoped>
.page-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.2;
}

.page-subtitle {
  color: #CBD5E1;
  font-size: 0.95rem;
  margin-top: 4px;
}

.panel-card {
  background: #0F172A;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: var(--radius-xl);
  padding: 24px;
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 780px;
}

.admin-table th {
  padding: 14px 16px;
  text-align: left;
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.78rem;
  color: #CBD5E1;
  text-transform: uppercase;
  font-weight: 700;
}

.admin-table td {
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  font-size: 0.88rem;
  vertical-align: middle;
}

.table-thumb {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  object-fit: cover;
}
</style>
