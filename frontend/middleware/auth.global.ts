import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware(async (to, from) => {
  const { isAuthenticated, isAdmin, isSuperAdmin, hasPermission, initAuth } = useAuth()

  // Make sure auth is initialized from cookies or storage
  if (process.client && !isAuthenticated.value) {
    await initAuth()
  }

  const isAdminRoute = to.path.startsWith('/admin')
  const isAdminLogin = to.path === '/admin/login'

  // 1. Handling /admin/login
  if (isAdminLogin) {
    if (isAuthenticated.value && isAdmin.value) {
      return navigateTo('/admin')
    }
    return
  }

  // 2. Handling Protected /admin/** Routes
  if (isAdminRoute) {
    // A. Must be authenticated
    if (!isAuthenticated.value) {
      return navigateTo(`/admin/login?redirect=${encodeURIComponent(to.fullPath)}`)
    }

    // B. Must have administrative role
    if (!isAdmin.value) {
      return navigateTo('/dashboard')
    }

    // C. Granular RBAC Permission Matrix Checks
    if (to.path.startsWith('/admin/users')) {
      if (!hasPermission('users.view') && !hasPermission('users.manage') && !isSuperAdmin.value) {
        return navigateTo('/admin?forbidden=users')
      }
    } else if (to.path.startsWith('/admin/settings')) {
      if (!hasPermission('settings.manage') && !isSuperAdmin.value) {
        return navigateTo('/admin?forbidden=settings')
      }
    } else if (to.path.startsWith('/admin/financials')) {
      if (!hasPermission('financials.view') && !hasPermission('financials.manage') && !isSuperAdmin.value) {
        return navigateTo('/admin?forbidden=financials')
      }
    } else if (to.path.startsWith('/admin/approvals')) {
      if (!hasPermission('properties.verify_rajuk') && !hasPermission('properties.edit') && !isSuperAdmin.value) {
        return navigateTo('/admin?forbidden=approvals')
      }
    } else if (to.path.startsWith('/admin/viewings')) {
      if (!hasPermission('viewings.view') && !hasPermission('viewings.manage') && !isSuperAdmin.value) {
        return navigateTo('/admin?forbidden=viewings')
      }
    } else if (to.path.startsWith('/admin/leads')) {
      if (!hasPermission('leads.view') && !hasPermission('leads.manage') && !isSuperAdmin.value) {
        return navigateTo('/admin?forbidden=leads')
      }
    } else if (to.path.startsWith('/admin/agents')) {
      if (!hasPermission('agents.manage') && !isSuperAdmin.value) {
        return navigateTo('/admin?forbidden=agents')
      }
    }
  }

  // 3. Handling /dashboard
  if (to.path.startsWith('/dashboard')) {
    if (!isAuthenticated.value) {
      return navigateTo(`/login?redirect=${encodeURIComponent(to.fullPath)}`)
    }
  }
})
