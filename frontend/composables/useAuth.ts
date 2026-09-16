import { computed } from 'vue'

export interface UserProfile {
  id: number
  name: string
  email: string
  role: 'buyer' | 'agent' | 'admin'
  phone: string
  avatar: string
  savedProperties: number[]
  scheduledViewings: any[]
}

const defaultBuyerUser: UserProfile = {
  id: 3,
  name: 'Shere Ali (VIP Buyer)',
  email: 'buyer@gbrel.com',
  role: 'buyer',
  phone: '+880 1711-234567',
  avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=400&auto=format&fit=crop',
  savedProperties: [1, 3],
  scheduledViewings: [
    {
      id: 101,
      propertyId: 1,
      propertyTitle: 'Lakeview Penthouse at Gulshan-2 Diplomatic Zone',
      date: '2026-09-05',
      timeSlot: '03:00 PM - 04:00 PM',
      agentName: 'Tanvir Ahmed (Senior Broker)',
      status: 'Confirmed'
    }
  ]
}

const defaultAgentUser: UserProfile = {
  id: 2,
  name: 'Tanvir Ahmed (Senior Advisor)',
  email: 'agent@gbrel.com',
  role: 'agent',
  phone: '+880 1819-987654',
  avatar: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=400&auto=format&fit=crop',
  savedProperties: [2],
  scheduledViewings: []
}

const defaultAdminUser: UserProfile = {
  id: 1,
  name: 'Chief Admin (GBREL HQ)',
  email: 'admin@gbrel.com',
  role: 'admin',
  phone: '+880 1912-334455',
  avatar: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=400&auto=format&fit=crop',
  savedProperties: [],
  scheduledViewings: []
}

export const useAuth = () => {
  // Persistent Cookie Storage using Nuxt 3 useCookie (Sanctum / Token Storage)
  const tokenCookie = useCookie<string | null>('gbrel_token', {
    maxAge: 60 * 60 * 24 * 7, // 7 days
    sameSite: 'lax',
    path: '/'
  })

  const userCookie = useCookie<UserProfile | null>('gbrel_user', {
    maxAge: 60 * 60 * 24 * 7,
    sameSite: 'lax',
    path: '/',
    default: () => null
  })

  const user = computed(() => userCookie.value || defaultBuyerUser)
  const currentUser = computed(() => userCookie.value)
  const token = computed(() => tokenCookie.value)
  const isAuthenticated = computed(() => !!tokenCookie.value && !!userCookie.value)
  const isAgent = computed(() => userCookie.value?.role === 'agent')
  const isAdmin = computed(() => userCookie.value?.role === 'admin')
  const isBuyer = computed(() => userCookie.value?.role === 'buyer')

  // Switch role helper
  const switchRole = (newRole: 'buyer' | 'agent' | 'admin') => {
    if (newRole === 'admin') {
      userCookie.value = { ...defaultAdminUser }
      tokenCookie.value = 'sanctum_admin_token_' + Date.now()
    } else if (newRole === 'agent') {
      userCookie.value = { ...defaultAgentUser }
      tokenCookie.value = 'sanctum_agent_token_' + Date.now()
    } else {
      userCookie.value = { ...defaultBuyerUser }
      tokenCookie.value = 'sanctum_buyer_token_' + Date.now()
    }
  }

  // Sanctum Login
  const login = async (email: string, password?: string) => {
    const cleanEmail = email.toLowerCase().trim()
    
    // Try Sanctum Backend API if available
    try {
      const response = await fetch(useApiUrl('/auth/login'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: cleanEmail, password })
      })
      if (response.ok) {
        const data = await response.json()
        if (data.token && data.user) {
          tokenCookie.value = data.token
          userCookie.value = {
            ...data.user,
            savedProperties: userCookie.value?.savedProperties || [1, 3],
            scheduledViewings: userCookie.value?.scheduledViewings || []
          }
          return data.user.role
        }
      }
    } catch {
      // Offline fallback
    }

    // Direct Auth State Resolution
    if (cleanEmail.includes('admin')) {
      switchRole('admin')
      return 'admin'
    } else if (cleanEmail.includes('agent') || cleanEmail.includes('broker') || cleanEmail.includes('tanvir')) {
      switchRole('agent')
      return 'agent'
    } else {
      switchRole('buyer')
      return 'buyer'
    }
  }

  // Logout
  const logout = async () => {
    try {
      if (tokenCookie.value) {
        await fetch(useApiUrl('/auth/logout'), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${tokenCookie.value}`
          }
        })
      }
    } catch {
      //
    }
    tokenCookie.value = null
    userCookie.value = null
  }

  const toggleSaveProperty = (propertyId: number) => {
    const current = userCookie.value 
      ? { ...userCookie.value }
      : { ...defaultBuyerUser, name: 'Guest Buyer', email: '', savedProperties: [], scheduledViewings: [] }
    
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
  }

  const isPropertySaved = (propertyId: number) => {
    return (userCookie.value?.savedProperties || []).includes(propertyId)
  }

  const addScheduledViewing = async (viewing: any) => {
    const current = userCookie.value 
      ? { ...userCookie.value }
      : { 
          ...defaultBuyerUser, 
          name: viewing.name || 'Guest Buyer', 
          email: viewing.email || '', 
          phone: viewing.phone || '', 
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

    // Save to MySQL database via Laravel API
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
    isAgent,
    isAdmin,
    isBuyer,
    login,
    logout,
    switchRole,
    toggleSaveProperty,
    isPropertySaved,
    addScheduledViewing
  }
}
