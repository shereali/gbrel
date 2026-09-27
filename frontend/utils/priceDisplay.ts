import { totalAskingPrice } from '~/utils/buyerDetails.mjs'
import { priceBn, toBn, unitLabels } from '~/utils/propertyLabels'

// How each kind of property is priced for buyers:
//   land / plot  → rate per land unit (katha, shotok…), total underneath
//   land share   → price per share, with land per share and the per-katha equivalent
//   flat & homes → one fixed price
//   everything else → rate per sqft, total underneath
export interface PriceDisplay {
  amount: string // big number, e.g. "৳ ৭ কোটি"
  per: string // what that number is for, e.g. "প্রতি কাঠা"
  note: string // supporting line, e.g. "মোট ৳ ১২০.২৬ কোটি · ১৭.১৮ কাঠা"
  total: number | null
  hidden: boolean
}

const LAND_TYPES = ['Plot', 'Land']
const FIXED_TYPES = ['Flat', 'Duplex', 'Penthouse']
const fixedPer: Record<string, string> = { Flat: 'সম্পূর্ণ ফ্ল্যাটের দাম', Duplex: 'সম্পূর্ণ ডুপ্লেক্সের দাম', Penthouse: 'সম্পূর্ণ পেন্টহাউসের দাম' }

const num = (v: unknown) => (Number(v) > 0 ? Number(v) : 0)
const sqftText = (sqft: number) => `${toBn(sqft.toLocaleString('en-IN'))} বর্গফুট`
const join = (...parts: (string | false | null | undefined)[]) => parts.filter(Boolean).join(' · ')

export function priceDisplay(property: any): PriceDisplay {
  if (!property) return { amount: '', per: '', note: '', total: null, hidden: false }
  if (property.hidePrice) {
    return { amount: property.priceDisplayText || 'দাম জানতে যোগাযোগ করুন', per: 'দাম প্রকাশ করা হয়নি', note: '', total: null, hidden: true }
  }

  const details = property.buyerDetails || {}
  const basis: string = details.priceBasis || 'Total'
  const price = num(property.price)
  const land = num(property.landSize)
  const unit = unitLabels[property.landUnit] || property.landUnit || 'কাঠা'
  const sqft = num(property.squareFootage)
  // An unset basis is treated as the whole-property price.
  const total = totalAskingPrice({ ...property, buyerDetails: { ...details, priceBasis: basis } })
  const negotiable = details.negotiable === 'Yes' ? 'আলোচনা সাপেক্ষ' : ''
  const type: string = property.propertyType || ''
  const landText = land ? `${toBn(land)} ${unit}` : ''

  const perLand = (rate: number, exact: boolean): PriceDisplay => ({
    amount: priceBn(rate),
    per: exact ? `প্রতি ${unit}` : `গড়ে প্রতি ${unit}`,
    note: join(total && `মোট ${priceBn(total)}`, landText, negotiable),
    total,
    hidden: false
  })
  const whole = (per: string, extra?: string): PriceDisplay => ({ amount: priceBn(total ?? price), per, note: join(extra, negotiable), total: total ?? price, hidden: false })

  // Land share: sold per share.
  if (type === 'Land Share' && basis === 'Per share') {
    const shareLand = num(details.shareLandSize)
    return {
      amount: priceBn(price),
      per: 'প্রতি শেয়ার',
      note: join(shareLand && `প্রতি শেয়ারে ${toBn(shareLand)} ${unit}`, shareLand && `${unit}প্রতি ${priceBn(price / shareLand)}`, negotiable),
      total: null,
      hidden: false
    }
  }

  // Land, plots and land shares quoted by area.
  if (LAND_TYPES.includes(type) || type === 'Land Share' || (basis === 'Per land unit' && !FIXED_TYPES.includes(type))) {
    if (basis === 'Per land unit') return perLand(price, true)
    if (land && total) return perLand(total / land, false)
    return whole('সম্পূর্ণ জমির দাম', landText)
  }

  // Flats and homes: one fixed price.
  if (FIXED_TYPES.includes(type)) {
    return whole(fixedPer[type] || 'সম্পূর্ণ দাম', join(sqft && sqftText(sqft), basis === 'Per sqft' && `প্রতি বর্গফুট ${priceBn(price)}`))
  }

  // Commercial space, hotels and the rest: rate per sqft.
  if (basis === 'Per sqft') {
    return { amount: priceBn(price), per: 'প্রতি বর্গফুট', note: join(total && `মোট ${priceBn(total)}`, sqft && sqftText(sqft), negotiable), total, hidden: false }
  }
  if (sqft && total) {
    return { amount: priceBn(total / sqft), per: 'গড়ে প্রতি বর্গফুট', note: join(`মোট ${priceBn(total)}`, sqftText(sqft), negotiable), total, hidden: false }
  }
  return whole('সম্পূর্ণ প্রপার্টির দাম', join(landText || (sqft && sqftText(sqft))))
}
