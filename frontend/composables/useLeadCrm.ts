import { useApiUrl } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'

export const LEAD_STAGES = ['New', 'Contacted', 'Qualified', 'Site Visit', 'Visited', 'Negotiation', 'Converted', 'Lost'] as const
export const CALL_OUTCOMES = ['Answered', 'No answer', 'Busy', 'Wrong number'] as const

export interface LeadActivity {
  id: number
  type: 'note' | 'call' | 'whatsapp' | 'email' | 'stage' | 'assign' | 'follow_up' | 'visit' | 'meta_lead' | 'manual'
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
/** A lead form and its fields; "website" is the site's own inquiry form, the rest are Meta lead form IDs. */
export interface HighlightForm { key: string; name: string; fields: string[]; source?: 'website' | 'meta'; project?: string; count?: number }
export interface LeadHighlights { forms: HighlightForm[]; selected: Record<string, string[]> }

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

/** Human-readable follow-up timestamp (e.g. "Today at 3:30 PM", "Tomorrow at 10:00 AM", "Mon, 27 Oct at 11:30 AM (in 2 days)") */
export const formatWhen = (value?: string | null): string => {
  if (!value) return ''
  const target = parseApiDate(value)
  const targetDateStr = target.toLocaleDateString('en-CA', { timeZone: DHAKA })
  const today = dhakaToday()

  const d1 = new Date(`${today}T00:00:00+06:00`).getTime()
  const d2 = new Date(`${targetDateStr}T00:00:00+06:00`).getTime()
  const diffDays = Math.round((d2 - d1) / (1000 * 60 * 60 * 24))

  const timeFormatted = target.toLocaleTimeString('en-GB', { timeZone: DHAKA, hour: 'numeric', minute: '2-digit', hour12: true })
  const dateFormatted = target.toLocaleDateString('en-GB', { timeZone: DHAKA, weekday: 'short', day: 'numeric', month: 'short' })

  if (diffDays === 0) return `Today at ${timeFormatted}`
  if (diffDays === 1) return `Tomorrow at ${timeFormatted}`
  if (diffDays === -1) return `Yesterday at ${timeFormatted}`
  if (diffDays > 1 && diffDays <= 7) return `${dateFormatted} at ${timeFormatted} (in ${diffDays} days)`
  if (diffDays < -1 && diffDays >= -7) return `${dateFormatted} at ${timeFormatted} (${Math.abs(diffDays)} days ago)`
  return `${dateFormatted} at ${timeFormatted}`
}

/** Human-friendly day formatting: "Today (Sun, 25 Oct)", "Tomorrow (Mon, 26 Oct)", "Wed, 28 Oct (in 3 days)" */
export const formatDay = (date?: string | null): string => {
  if (!date) return ''
  const today = dhakaToday()
  const d1 = new Date(`${today}T00:00:00+06:00`).getTime()
  const d2 = new Date(`${date}T00:00:00+06:00`).getTime()
  const diffDays = Math.round((d2 - d1) / (1000 * 60 * 60 * 24))

  const baseDate = new Date(`${date}T00:00:00+06:00`).toLocaleDateString('en-GB', {
    timeZone: DHAKA,
    weekday: 'short',
    day: 'numeric',
    month: 'short'
  })

  if (diffDays === 0) return `Today (${baseDate})`
  if (diffDays === 1) return `Tomorrow (${baseDate})`
  if (diffDays === -1) return `Yesterday (${baseDate})`
  if (diffDays > 1 && diffDays <= 7) return `${baseDate} (in ${diffDays} days)`
  if (diffDays < -1 && diffDays >= -7) return `${baseDate} (${Math.abs(diffDays)} days ago)`
  return baseDate
}

/** "15:30" → "3:30 PM" */
export const formatTime = (time?: string | null): string => {
  if (!time) return ''
  const [h, m] = time.split(':').map(Number)
  return `${h % 12 || 12}:${pad(m)} ${h >= 12 ? 'PM' : 'AM'}`
}

/** Human-readable site visit date & time: "Today (Sun, 25 Oct) · 3:30 PM" */
export const formatHumanVisit = (date?: string | null, time?: string | null): string => {
  if (!date) return ''
  const dayStr = formatDay(date)
  const timeStr = time ? formatTime(time) : ''
  return timeStr ? `${dayStr} · ${timeStr}` : dayStr
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
    bulkAssign: (leadIds: number[], userId: number | null) => call<{ count: number; message: string }>('/leads/bulk-assign', { method: 'POST', body: { lead_ids: leadIds, user_id: userId } }),
    followUp: (id: number, at: string | null) => call(`/leads/${id}/follow-up`, { method: 'PATCH', body: { at } }),
    log: (id: number, body: { type: string; body?: string; outcome?: string; follow_up_at?: string | null }) => call(`/leads/${id}/activities`, { method: 'POST', body }),
    scheduleVisit: (id: number, body: Record<string, unknown>) => call<LeadVisit>(`/leads/${id}/visits`, { method: 'POST', body }),
    updateVisit: (id: number, visitId: number, body: Record<string, unknown>) => call<LeadVisit>(`/leads/${id}/visits/${visitId}`, { method: 'PATCH', body }),
    highlights: () => call<LeadHighlights>('/leads/highlights'),
    saveHighlights: (form: string, fields: string[]) => call<{ form: string; fields: string[] }>('/leads/highlights', { method: 'PUT', body: { form, fields } })
  }
}
