// Phone numbers as staff type them ("01712-345678", "+880 1712 345678") → links that actually open.

// wa.me needs the full international number, digits only. Local Bangladeshi mobiles get the 880 prefix.
// Returns '' for anything that cannot be a WhatsApp number, and for the old demo placeholder.
export function whatsappNumber(raw) {
  const bnDigits = '০১২৩৪৫৬৭৮৯'
  let n = String(raw || '').replace(/[০-৯]/g, d => String(bnDigits.indexOf(d))).replace(/\D/g, '')
  if (n.startsWith('00')) n = n.slice(2)
  if (/^01[3-9]\d{8}$/.test(n)) n = `88${n}`
  if (n === '8801711000000') return ''
  return /^\d{10,15}$/.test(n) ? n : ''
}

export function whatsappLink(raw, message) {
  const n = whatsappNumber(raw)
  return n ? `https://wa.me/${n}?text=${encodeURIComponent(message)}` : ''
}

// tel: links keep only digits and a leading +.
export function telHref(raw) {
  const cleaned = String(raw || '').trim().replace(/[^\d+]/g, '').replace(/(?!^)\+/g, '')
  return cleaned.replace(/\D/g, '').length >= 6 ? `tel:${cleaned}` : ''
}
