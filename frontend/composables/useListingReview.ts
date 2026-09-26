// Staff review of properties submitted by owners.
import { ApiError } from '~/composables/useOwnerListings'

export interface ReviewQueueItem {
  id: number
  title: string
  property_type: string | null
  area_name: string | null
  city: string | null
  land_size: number | null
  land_unit: string | null
  expected_price: number | null
  price_basis: string | null
  review_status: string
  status: string
  submitted_at: string | null
  updated_at: string
  owner: { id: number; name: string; phone: string | null; email: string | null } | null
  documents_total: number
  documents_verified: number
  missing_required_documents: number
  has_pending_changes: boolean
  cover: string | null
}

const readJson = async (res: Response) => {
  const data = await res.json().catch(() => null)
  if (!res.ok || data?.success === false) {
    const errors = data?.errors || {}
    const first = Object.values(errors).flat()[0] as string | undefined
    throw new ApiError(first || data?.message || 'The request could not be completed.', errors, res.status)
  }
  return data
}
const json = (method: string, body?: unknown): RequestInit => ({
  method,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
  body: body === undefined ? undefined : JSON.stringify(body)
})

export const useListingReview = () => {
  const queue = async (reviewStatus = ''): Promise<{ items: ReviewQueueItem[]; counts: Record<string, number> }> => {
    const data = await readJson(await fetch(useApiUrl(`/admin/listing-requests${reviewStatus ? `?review_status=${reviewStatus}` : ''}`)))
    return { items: data.data, counts: data.counts || {} }
  }
  const get = async (id: number) => (await readJson(await fetch(useApiUrl(`/admin/listing-requests/${id}`)))).data
  const act = async (id: number, action: string, note?: string) => (await readJson(await fetch(useApiUrl(`/admin/listing-requests/${id}/review`), json('PATCH', { action, note })))).data
  const pendingChanges = async (id: number, decision: 'apply' | 'discard') => (await readJson(await fetch(useApiUrl(`/admin/listing-requests/${id}/pending-changes`), json('POST', { decision })))).data
  const reviewDocument = async (documentId: number, status: string, reviewNote?: string) =>
    (await readJson(await fetch(useApiUrl(`/admin/listing-documents/${documentId}`), json('PATCH', { status, review_note: reviewNote || null })))).data
  const updatePublicDetails = async (id: number, payload: Record<string, unknown>) => (await readJson(await fetch(useApiUrl(`/properties/${id}`), json('PUT', payload)))).data

  return { queue, get, act, pendingChanges, reviewDocument, updatePublicDetails }
}

// Maps the owner's private answers onto the public "price, ownership & terms" fields.
// Only facts the buyer may see are copied; exact address, mouza/dag numbers and notes stay private.
export const ownerFactsToBuyerDetails = (d: Record<string, any>): Record<string, unknown> => {
  const out: Record<string, unknown> = {}
  const map: Record<string, Record<string, string>> = {
    bankLoan: { None: 'None declared', Exists: 'Exists' },
    existingAgreement: { None: 'None declared', Exists: 'Exists' },
    mutationStatus: { Done: 'Available', Pending: 'Pending', 'Not done': 'Unavailable' },
    ownershipSource: { Purchase: 'Purchase', Inheritance: 'Inheritance', Allotment: 'Allotment', Gift: 'Other', Other: 'Other' },
    possession: { Owner: 'Owner', Tenant: 'Tenant', Vacant: 'Vacant', Other: 'Other' },
    landUse: { Residential: 'Residential', Commercial: 'Commercial', Mixed: 'Mixed' },
    cornerPlot: { Yes: 'Yes', No: 'No' },
    negotiable: { Yes: 'Yes', No: 'No' },
    priceBasis: { Total: 'Total', 'Per land unit': 'Per land unit', 'Per sqft': 'Per sqft' }
  }
  Object.entries(map).forEach(([key, values]) => { if (d[key] && values[d[key]]) out[key] = values[d[key]] })
  if (d.ownerCount) out.ownerCount = Number(d.ownerCount)
  if (d.roadWidth) out.roadWidth = Number(d.roadWidth)
  if (d.taxPaidThrough) out.taxPaidThrough = String(d.taxPaidThrough)
  if (d.buildingDescription) out.buildingDescription = String(d.buildingDescription)
  return out
}
