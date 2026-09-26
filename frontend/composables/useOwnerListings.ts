// Property owner's own listings: create, edit, upload ownership papers, submit for GBREL review.
import { useApiUrl } from '~/composables/useApi'

export interface OwnerDocument {
  id: number
  document_type: string
  original_name: string
  mime_type?: string
  size: number
  status: 'pending' | 'verified' | 'rejected'
  review_note?: string | null
  created_at: string
}

export interface OwnerListing {
  id: number
  title: string
  property_type: string | null
  state?: string | null
  city: string | null
  area_name: string | null
  address: string | null
  land_size: number | null
  land_unit: string | null
  square_footage: number | null
  total_floors: number | null
  bedrooms: number | null
  bathrooms: number | null
  parking: number | null
  facing: string | null
  completion_status: string | null
  description: string | null
  images: string[] | null
  owner_details: Record<string, any> | null
  owner_pending_changes: Record<string, any> | null
  owner_agreement: Record<string, any> | null
  review_status: 'draft' | 'submitted' | 'in_review' | 'changes_requested' | 'approved' | 'update_submitted' | 'rejected'
  review_note: string | null
  status: string
  submitted_at: string | null
  published_at: string | null
  updated_at: string
  documents: OwnerDocument[]
  missing_required_documents: Array<{ key: string; label: string }>
  is_live: boolean
}

export class ApiError extends Error {
  constructor(message: string, public errors: Record<string, string[]> = {}, public status = 0) {
    super(message)
  }
}

const readJson = async (res: Response) => {
  const data = await res.json().catch(() => null)
  if (!res.ok || data?.success === false) {
    const errors = data?.errors || {}
    const first = Object.values(errors).flat()[0] as string | undefined
    throw new ApiError(first || data?.message || 'অনুরোধটি সম্পন্ন করা যায়নি। আবার চেষ্টা করুন।', errors, res.status)
  }
  return data
}

const json = (method: string, body?: unknown): RequestInit => ({
  method,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
  body: body === undefined ? undefined : JSON.stringify(body)
})

export const useOwnerListings = () => {
  const list = async (): Promise<OwnerListing[]> => (await readJson(await fetch(useApiUrl('/owner/listings')))).data
  const get = async (id: number): Promise<OwnerListing> => (await readJson(await fetch(useApiUrl(`/owner/listings/${id}`)))).data
  const create = async (payload: Record<string, any>): Promise<OwnerListing> => (await readJson(await fetch(useApiUrl('/owner/listings'), json('POST', payload)))).data
  const update = async (id: number, payload: Record<string, any>): Promise<OwnerListing> => (await readJson(await fetch(useApiUrl(`/owner/listings/${id}`), json('PUT', payload)))).data
  const submit = async (id: number, termsVersion: string): Promise<OwnerListing> =>
    (await readJson(await fetch(useApiUrl(`/owner/listings/${id}/submit`), json('POST', { accept_terms: true, terms_version: termsVersion })))).data

  const uploadDocument = async (id: number, documentType: string, file: File): Promise<OwnerDocument> => {
    const form = new FormData()
    form.append('document_type', documentType)
    form.append('file', file)
    return (await readJson(await fetch(useApiUrl(`/owner/listings/${id}/documents`), { method: 'POST', body: form, headers: { Accept: 'application/json' } }))).data
  }
  const deleteDocument = async (id: number, documentId: number) => readJson(await fetch(useApiUrl(`/owner/listings/${id}/documents/${documentId}`), json('DELETE')))

  const uploadPhotos = async (files: FileList | File[]): Promise<string[]> => {
    const form = new FormData()
    Array.from(files).forEach(file => form.append('images[]', file))
    const data = await readJson(await fetch(useApiUrl('/upload'), { method: 'POST', body: form, headers: { Accept: 'application/json' } }))
    return data.urls || []
  }

  // Private documents need the sign-in token, so they are fetched as a blob and opened locally.
  const openDocument = async (documentId: number) => {
    const res = await fetch(useApiUrl(`/listing-documents/${documentId}/file`))
    if (!res.ok) throw new ApiError('ফাইলটি খোলা যায়নি।')
    const url = URL.createObjectURL(await res.blob())
    window.open(url, '_blank', 'noopener')
    setTimeout(() => URL.revokeObjectURL(url), 60_000)
  }

  return { list, get, create, update, submit, uploadDocument, deleteDocument, uploadPhotos, openDocument }
}

export const reviewStatusLabels: Record<string, string> = {
  draft: 'খসড়া',
  submitted: 'জমা হয়েছে',
  in_review: 'যাচাই চলছে',
  changes_requested: 'তথ্য দরকার',
  approved: 'অনুমোদিত',
  update_submitted: 'পরিবর্তন যাচাই হচ্ছে',
  rejected: 'বাতিল'
}
