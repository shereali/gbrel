import { computed } from 'vue'
import { useApiUrl } from '~/composables/useApi'

export interface UserProfile {
  id: number
  name: string
  email: string
  role: string
  role_id?: number
  role_name?: string
  phone?: string
  region?: string
  status?: string
  avatar?: string
  permissions: string[]
  is_admin?: boolean
  is_super_admin?: boolean
  savedProperties?: number[]
  scheduledViewings?: any[]
}

export const useAuth = () => {
  // Persistent Cookie Storage (30-day session)
  const tokenCookie = useCookie<string | null>('gbrel_token', {
    maxAge: 60 * 60 * 24 * 30, // 30 days
    sameSite: 'lax',
    path: '/'
  })

  const userCookie = useCookie<UserProfile | null>('gbrel_user', {
    maxAge: 60 * 60 * 24 * 30,
    sameSite: 'lax',
    path: '/',
    default: () => null
  })

  // Synchronize with localStorage on client initialization for bulletproof persistence
  if (process.client) {
    try {
      const localToken = localStorage.getItem('gbrel_token')
      const localUser = localStorage.getItem('gbrel_user')
      if (!tokenCookie.value && localToken) {
        tokenCookie.value = localToken
      }
      if (!userCookie.value && localUser) {
        userCookie.value = JSON.parse(localUser)
      }
    } catch {
      //
    }
  }

  const user = computed<UserProfile>(() => {
    return userCookie.value || {
      id: 0,
      name: 'Guest User',
      email: '',
      role: 'guest',
      permissions: [],
      savedProperties: [],
      scheduledViewings: []
    }
  })

  const currentUser = computed(() => userCookie.value)
  const token = computed(() => tokenCookie.value)
  const isAuthenticated = computed(() => !!tokenCookie.value && !!userCookie.value)

  // Role Computeds
  const isSuperAdmin = computed(() => {
    if (!userCookie.value) return false
    return userCookie.value.role === 'admin' || (userCookie.value.permissions || []).includes('*')
  })

  const isAdmin = computed(() => {
    if (!userCookie.value) return false
    const r = userCookie.value.role
    return (
      r === 'admin' ||
      r === 'property_manager' ||
      r === 'legal_compliance' ||
      r === 'finance_auditor' ||
      !!userCookie.value.is_admin ||
      (userCookie.value.permissions || []).includes('*')
    )
  })

  const isAgent = computed(() => userCookie.value?.role === 'agent')
  const isBuyer = computed(() => userCookie.value?.role === 'buyer' || userCookie.value?.role === 'guest')

  // Permission Checks
  const hasPermission = (permissionSlug: string): boolean => {
    if (!userCookie.value) return false
    const perms = userCookie.value.permissions || []
    if (perms.includes('*') || isSuperAdmin.value) {
      return true
    }
    return perms.includes(permissionSlug)
  }

  const hasAnyPermission = (permissionSlugs: string[]): boolean => {
    if (!userCookie.value) return false
    const perms = userCookie.value.permissions || []
    if (perms.includes('*') || isSuperAdmin.value) {
      return true
    }
    return permissionSlugs.some(slug => perms.includes(slug))
  }

  // Real Database Login via Laravel Sanctum API
  const login = async (emailInput: string, passwordInput: string, remember: boolean = true) => {
    const cleanEmail = emailInput.toLowerCase().trim()

    const res = await fetch(useApiUrl('/auth/login'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: cleanEmail,
        password: passwordInput,
        remember
      })
    })

    const data = await res.json().catch(() => null)

    if (!res.ok || !data?.success) {
      throw new Error(data?.message || 'Invalid email or password. Please verify your credentials.')
    }

    const receivedToken = data.token
    const userPayload: UserProfile = {
      ...data.user,
      savedProperties: userCookie.value?.savedProperties || [1, 3],
      scheduledViewings: userCookie.value?.scheduledViewings || []
    }

    tokenCookie.value = receivedToken
    userCookie.value = userPayload

    if (process.client) {
      try {
        localStorage.setItem('gbrel_token', receivedToken)
        localStorage.setItem('gbrel_user', JSON.stringify(userPayload))
      } catch {
        //
      }
    }

    return userPayload
  }

  // Token Validation & Session Synchronization
  const initAuth = async (): Promise<UserProfile | null> => {
    // 1. Client fallback recovery
    if (process.client) {
      try {
        const localToken = localStorage.getItem('gbrel_token')
        const localUser = localStorage.getItem('gbrel_user')
        if (!tokenCookie.value && localToken) {
          tokenCookie.value = localToken
        }
        if (!userCookie.value && localUser) {
          userCookie.value = JSON.parse(localUser)
        }
      } catch {
        //
      }
    }

    const currentToken = tokenCookie.value
    if (!currentToken) return null

    // 2. Validate against live backend
    try {
      const res = await fetch(useApiUrl('/auth/me'), {
        headers: {
          'Authorization': `Bearer ${currentToken}`
        }
      })

      if (res.ok) {
        const data = await res.json()
        if (data && data.success && data.user) {
          const freshUser: UserProfile = {
            ...data.user,
            savedProperties: userCookie.value?.savedProperties || [1, 3],
            scheduledViewings: userCookie.value?.scheduledViewings || []
          }
          userCookie.value = freshUser
          if (process.client) {
            localStorage.setItem('gbrel_user', JSON.stringify(freshUser))
          }
          return freshUser
        }
      } else if (res.status === 401 || res.status === 403) {
        // Invalid or expired token
        tokenCookie.value = null
        userCookie.value = null
        if (process.client) {
          localStorage.removeItem('gbrel_token')
          localStorage.removeItem('gbrel_user')
        }
        return null
      }
    } catch (err) {
      // Keep cached session during temporary network drop
      console.warn('GBREL Auth offline check notice:', err)
    }

    return userCookie.value
  }

  // Logout
  const logout = async () => {
    const currentToken = tokenCookie.value || (process.client ? localStorage.getItem('gbrel_token') : null)
    try {
      if (currentToken) {
        await fetch(useApiUrl('/auth/logout'), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${currentToken}`
          }
        })
      }
    } catch {
      //
    } finally {
      tokenCookie.value = null
      userCookie.value = null
      if (process.client) {
        try {
          localStorage.removeItem('gbrel_token')
          localStorage.removeItem('gbrel_user')
        } catch {
          //
        }
      }
    }
  }

  // Wishlist property helpers
  const toggleSaveProperty = (propertyId: number) => {
    const current = userCookie.value
      ? { ...userCookie.value }
      : {
          id: 0,
          name: 'Guest Buyer',
          email: '',
          role: 'buyer',
          permissions: [],
          savedProperties: [],
          scheduledViewings: []
        }

    if (!Array.isArray(current.savedProperties)) {
      current.savedProperties = []
    }
    const idx = current.savedProperties.indexOf(propertyId)
    if (idx > -1) {
      current.savedProperties.splice(idx, 1)
    } else {
      current.savedProperties.push(propertyId)
    }
    userCookie.value = current
    if (process.client) {
      try {
        localStorage.setItem('gbrel_user', JSON.stringify(current))
      } catch {}
    }
  }

  const isPropertySaved = (propertyId: number) => {
    return (userCookie.value?.savedProperties || []).includes(propertyId)
  }

  const addScheduledViewing = async (viewing: any) => {
    const current = userCookie.value
      ? { ...userCookie.value }
      : {
          id: 0,
          name: viewing.name || 'Guest Buyer',
          email: viewing.email || '',
          role: 'buyer',
          phone: viewing.phone || '',
          permissions: [],
          savedProperties: [],
          scheduledViewings: []
        }

    if (!Array.isArray(current.scheduledViewings)) {
      current.scheduledViewings = []
    }
    const item = {
      id: Date.now(),
      ...viewing,
      status: 'Confirmed'
    }
    current.scheduledViewings.push(item)
    userCookie.value = current
    if (process.client) {
      try {
        localStorage.setItem('gbrel_user', JSON.stringify(current))
      } catch {}
    }

    try {
      await fetch(useApiUrl('/schedule-viewing'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: viewing.name || user.value.name,
          phone: viewing.phone || user.value.phone,
          email: viewing.email || user.value.email,
          contact_method: viewing.contactMethod || 'WhatsApp',
          property_id: viewing.propertyId || 1,
          property_title: viewing.propertyTitle || 'Property Viewing',
          scheduled_date: viewing.date || new Date().toISOString().split('T')[0],
          scheduled_time: viewing.timeSlot || '03:00 PM - 04:00 PM',
          vip_pickup: !!viewing.vipPickup,
          pickup_location: viewing.pickupLocation || 'Gulshan-2 Diplomatic Enclave',
          assigned_agent: viewing.agentName || 'Tanvir Ahmed',
          notes: viewing.notes || 'Booked online via GBREL platform'
        })
      })
    } catch {
      //
    }
  }

  return {
    user,
    currentUser,
    token,
    isAuthenticated,
    isAdmin,
    isSuperAdmin,
    isAgent,
    isBuyer,
    hasPermission,
    hasAnyPermission,
    login,
    logout,
    initAuth,
    toggleSaveProperty,
    isPropertySaved,
    addScheduledViewing
  }
}
