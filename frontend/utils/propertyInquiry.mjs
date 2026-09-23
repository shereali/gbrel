// Keep contact normalization and transport independently testable.
export function normalizePhone(value) {
  let phone = String(value).replace(/[০-৯]/g, digit => String('০১২৩৪৫৬৭৮৯'.indexOf(digit))).replace(/[\s().-]/g, '')
  if (/^01[3-9]\d{8}$/.test(phone)) phone = '+88' + phone
  if (/^8801[3-9]\d{8}$/.test(phone)) phone = '+' + phone
  if (/^00/.test(phone)) phone = '+' + phone.slice(2)
  return phone
}

export function validPhone(value) {
  const phone = normalizePhone(value)
  if (phone.startsWith('+880')) return /^\+8801[3-9]\d{8}$/.test(phone)
  return /^\+[1-9]\d{7,14}$/.test(phone)
}

export function safeBrochureUrl(value) {
  if (!value || /(?:dummy|sample|testfiles|example\.com)/i.test(value)) return ''
  try {
    const url = new URL(value, 'https://gbrel.com')
    return ['https:', 'http:'].includes(url.protocol) ? url.href : ''
  } catch { return '' }
}

export async function savePropertyInquiry(endpoint, payload, fetcher = fetch) {
  const controller = new AbortController()
  const timeout = setTimeout(() => controller.abort(), 20000)
  try {
    const response = await fetcher(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(payload),
      signal: controller.signal,
    })
    const result = await response.json()
    if (!response.ok || result?.success !== true || !result?.data?.id) throw new Error('Lead was not confirmed by the server')
    return result.data.id
  } finally { clearTimeout(timeout) }
}
