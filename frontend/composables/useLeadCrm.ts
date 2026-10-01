import { useApiUrl } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'

export const LEAD_STAGES = ['New', 'Contacted', 'Qualified', 'Site Visit', 'Negotiation', 'Converted', 'Lost'] as const
export const CALL_OUTCOMES = ['Answered', 'No answer', 'Busy', 'Wrong number'] as const

export interface LeadActivity {
  id: number
  type: 'note' | 'call' | 'whatsapp' | 'email' | 'stage' | 'assign' | 'follow_up' | 'visit'
  body: string | null
  meta: Record<string, any>
  user: { id: number; name: string } | null
  occurred_at: string
}
export interface LeadVisit {
  id: number
  lead_id: number
  date: string
  time: string | null
  time_label: string | null
  visit_type: string
  status: string
  property_title: string
  assigned_to: number | null
  assigned_agent: string | null
  vip_pickup: boolean
  pickup_location: string | null
  notes: string | null
  outcome_notes: string | null
}
export interface StaffMember { id: number; name: string; role: string | null }

// Everyone on the team works in Bangladesh time (UTC+6, no daylight saving), wherever their browser thinks it is.
const DHAKA = 'Asia/Dhaka'
const pad = (n: number) => String(n).padStart(2, '0')

export const dhakaToday = (): string => new Date().toLocaleDateString('en-CA', { timeZone: DHAKA })

/** "2026-10-05" and "15:30" typed in Dhaka time → an ISO string with the right offset. */
export const dhakaIso = (date: string, time: string): string => `${date}T${time}:00+06:00`

/** The Dhaka calendar day `days` from today at the given hour, as an ISO string. */
export const inDays = (days: number, hour = 10): string => {
  const base = new Date(`${dhakaToday()}T00:00:00+06:00`)
  base.setUTCDate(base.getUTCDate() + days)
  return dhakaIso(base.toLocaleDateString('en-CA', { timeZone: DHAKA }), `${pad(hour)}:00`)
}

/** End of today in Dhaka, in milliseconds: a follow-up at or before this is due today. */
export const endOfDhakaDay = (): number => new Date(`${dhakaToday()}T23:59:59+06:00`).getTime()

export const parseApiDate = (value: string): Date => new Date(/^\d{4}-\d\d-\d\d \d/.test(value) ? `${value.replace(' ', 'T')}Z` : value)

export const formatWhen = (value?: string | null): string => value
  ? parseApiDate(value).toLocaleString('en-GB', { timeZone: DHAKA, day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit', hour12: true })
  : ''

export const formatDay = (date?: string | null): string => date
  ? new Date(`${date}T00:00:00+06:00`).toLocaleDateString('en-GB', { timeZone: DHAKA, weekday: 'short', day: 'numeric', month: 'short' })
  : ''

/** "15:30" → "3:30 pm" */
export const formatTime = (time?: string | null): string => {
  if (!time) return ''
  const [h, m] = time.split(':').map(Number)
  return `${h % 12 || 12}:${pad(m)} ${h >= 12 ? 'pm' : 'am'}`
}

export const useLeadCrm = () => {
  const { token } = useAuth()

  const call = async <T = any>(path: string, init: { method?: string; body?: unknown } = {}): Promise<T> => {
    const res = await fetch(useApiUrl(path), {
      method: init.method || 'GET',
      headers: { Accept: 'application/json', ...(init.body !== undefined ? { 'Content-Type': 'application/json' } : {}), Authorization: `Bearer ${token.value}` },
      body: init.body !== undefined ? JSON.stringify(init.body) : undefined
    })
    const json = await res.json().catch(() => ({}))
    if (!res.ok) {
      const firstError = json?.errors ? (Object.values(json.errors)[0] as string[])[0] : ''
      throw new Error(firstError || json?.message || 'Something went wrong. Try again.')
    }
    return json.data as T
  }

  return {
    overview: () => call<{ visits: LeadVisit[]; staff: StaffMember[] }>('/leads/overview'),
    detail: (id: number) => call<{ activities: LeadActivity[]; visits: LeadVisit[] }>(`/leads/${id}/crm`),
    setStage: (id: number, stage: string, lostReason?: string) => call(`/leads/${id}/stage`, { method: 'PATCH', body: { stage, lost_reason: lostReason } }),
    assign: (id: number, userId: number | null) => call(`/leads/${id}/assign`, { method: 'PATCH', body: { user_id: userId } }),
    followUp: (id: number, at: string | null) => call(`/leads/${id}/follow-up`, { method: 'PATCH', body: { at } }),
    log: (id: number, body: { type: string; body?: string; outcome?: string; follow_up_at?: string | null }) => call(`/leads/${id}/activities`, { method: 'POST', body }),
    scheduleVisit: (id: number, body: Record<string, unknown>) => call<LeadVisit>(`/leads/${id}/visits`, { method: 'POST', body }),
    updateVisit: (id: number, visitId: number, body: Record<string, unknown>) => call<LeadVisit>(`/leads/${id}/visits/${visitId}`, { method: 'PATCH', body })
  }
}
