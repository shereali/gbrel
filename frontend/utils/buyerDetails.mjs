// These fields contain public listing information only, never private identity documents.
const field = (key, label, bn, type = 'text', extra = {}) => ({ key, label, bn, type, ...extra })
const choice = (key, label, bn, options) => field(key, label, bn, 'select', { options })
export const buyerDetailGroups = [
  { key: 'building', title: 'Land & existing building', bn: 'জমি ও স্থাপনার বিবরণ', fields: [
    choice('landUse', 'Declared land use', 'তালিকায় উল্লেখিত ব্যবহার', [['Residential', 'আবাসিক'], ['Commercial', 'বাণিজ্যিক'], ['Mixed', 'মিশ্র ব্যবহার']]),
    choice('cornerPlot', 'Corner plot', 'কর্নার প্লট', [['Yes', 'হ্যাঁ'], ['No', 'না']]),
    field('roadWidth', 'Access road width (feet)', 'সামনের রাস্তার প্রস্থ', 'number', { max: 1000, suffix: ' ফুট' }),
    field('buildingDescription', 'Existing building / condition', 'বর্তমান স্থাপনা ও অবস্থা', 'textarea'),
    field('utilities', 'Utility connections and status', 'ইউটিলিটি সংযোগ', 'textarea'),
  ] },
  { key: 'terms', title: 'Price, payment & buyer costs', bn: 'মূল্য, পেমেন্ট ও অতিরিক্ত খরচ', fields: [
    choice('priceBasis', 'How is the asking price quoted?', 'দামের ভিত্তি', [['Total', 'সম্পূর্ণ প্রপার্টির মূল্য'], ['Per land unit', 'প্রতি জমির একক'], ['Per sqft', 'প্রতি বর্গফুট'], ['Per share', 'প্রতি শেয়ার']]),
    field('shareLandSize', 'Land per share (in the listing land unit)', 'প্রতি শেয়ারে জমির পরিমাণ', 'number', { max: 100000 }),
    choice('negotiable', 'Negotiable price', 'দাম আলোচনা সাপেক্ষ', [['Yes', 'হ্যাঁ'], ['No', 'না']]),
    field('priceIncludes', 'What does the price include?', 'মূল্যের মধ্যে যা আছে', 'textarea'),
    field('depositPercent', 'Proposed advance / bayna (%)', 'প্রস্তাবিত বায়না', 'number', { max: 100, suffix: '%' }),
    field('agreementDuration', 'Bayna agreement duration', 'বায়না চুক্তির মেয়াদ'),
    field('paymentSchedule', 'Payment schedule', 'পেমেন্টের সময়সূচি', 'textarea'),
    field('paymentMethod', 'Payment method', 'লেনদেনের মাধ্যম'),
    field('buyerCommission', 'Buyer commission (%)', 'ক্রেতার সার্ভিস চার্জ', 'number', { max: 100, suffix: '%' }),
    field('sellerCommission', 'Seller commission (%)', 'বিক্রেতার সার্ভিস চার্জ', 'number', { max: 100, suffix: '%' }),
    field('registrationCost', 'Registration cost / basis (do not guess)', 'রেজিস্ট্রেশন খরচের বিবরণ'),
    field('registrationValue', 'Proposed deed value (BDT, if supplied)', 'প্রস্তাবিত দলিল মূল্য', 'number', { max: 1000000000000, prefix: '৳ ', grouped: true }),
    field('buyerCosts', 'Other costs paid by buyer', 'ক্রেতার অন্যান্য খরচ', 'textarea'),
    field('sellerCosts', 'Costs paid by seller', 'বিক্রেতার বহনযোগ্য খরচ', 'textarea'),
    field('transferTimeline', 'Proposed transfer time', 'প্রস্তাবিত হস্তান্তরের সময়'),
    field('transferTrigger', 'When does that period start?', 'সময়সীমা শুরুর শর্ত', 'textarea'),
  ] },
  { key: 'ownership', title: 'Ownership & possession', bn: 'মালিকানা ও দখল', fields: [
    field('ownerCount', 'Number of owners', 'মালিকের সংখ্যা', 'number', { max: 10000, integer: true, suffix: ' জন' }),
    choice('ownershipSource', 'Ownership acquired through', 'মালিকানার সূত্র', [['Purchase', 'ক্রয়সূত্রে'], ['Inheritance', 'ওয়ারিশসূত্রে'], ['Allotment', 'বরাদ্দসূত্রে'], ['Other', 'অন্যান্য']]),
    choice('possession', 'Current possession', 'বর্তমান দখল', [['Owner', 'মালিকের দখলে'], ['Tenant', 'ভাড়াটিয়ার দখলে'], ['Vacant', 'খালি'], ['Other', 'অন্যান্য']]),
    choice('bankLoan', 'Existing bank loan / mortgage', 'ঋণ বা বন্ধকের অবস্থা', [['None declared', 'নেই বলে জানানো হয়েছে'], ['Exists', 'আছে']]),
    choice('existingAgreement', 'Any existing sale agreement?', 'অন্য ক্রেতার সঙ্গে চুক্তি', [['None declared', 'নেই বলে জানানো হয়েছে'], ['Exists', 'আছে']]),
    field('saleAuthority', 'Who is authorized to sell? (role only)', 'বিক্রয়ের প্রতিনিধিত্ব / ক্ষমতা', 'textarea'),
    field('ownershipNotes', 'Ownership or possession conditions', 'মালিকানা ও দখলের শর্ত', 'textarea'),
  ] },
  { key: 'records', title: 'Documents & information source', bn: 'কাগজপত্র ও তথ্যের উৎস', fields: [
    choice('mutationStatus', 'Mutation / namjari status', 'নামজারির অবস্থা', [['Available', 'আছে বলে জানানো হয়েছে'], ['Pending', 'প্রক্রিয়াধীন'], ['Unavailable', 'পাওয়া যায়নি']]),
    field('taxPaidThrough', 'Land tax paid through (include calendar)', 'খাজনা পরিশোধের সর্বশেষ সাল'),
    field('serviceChargeStatus', 'Service / documentation fee status', 'সার্ভিস ও ডকুমেন্টেশন ফি'),
    field('approvalDetails', 'Use / building / sale permission details', 'ব্যবহার, ভবন ও বিক্রয়ের অনুমোদন', 'textarea'),
    field('documentSummary', 'Available documents and unresolved issues', 'উপলব্ধ নথি ও অসম্পূর্ণ তথ্য', 'textarea'),
    field('sourceDate', 'Source document date', 'উৎস নথির তারিখ', 'date'),
    field('updatedOn', 'Listing information updated on', 'তথ্য হালনাগাদের তারিখ', 'date'),
  ] },
]
// The public page is in Bangla, so numbers and dates are shown in Bangla too: "৩০%", "২ জন", "১৫ সেপ্টেম্বর ২০২৬".
const BN_DIGITS = '০১২৩৪৫৬৭৮৯'
export const bnDigits = value => String(value).replace(/\d/g, d => BN_DIGITS[Number(d)])
const BN_MONTHS = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর']
export function bnDate(value) {
  const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(value || ''))
  if (!m || Number(m[2]) < 1 || Number(m[2]) > 12) return value ? bnDigits(value) : ''
  return `${bnDigits(Number(m[3]))} ${BN_MONTHS[Number(m[2]) - 1]} ${bnDigits(m[1])}`
}
function displayValue(f, value) {
  if (f.type === 'date') return bnDate(value)
  if (f.type === 'number' && value !== '' && !Number.isNaN(Number(value))) {
    const n = f.grouped ? Number(value).toLocaleString('en-IN') : String(Number(value))
    return `${f.prefix || ''}${bnDigits(n)}${f.suffix || ''}`
  }
  return typeof value === 'string' ? value : bnDigits(value)
}
export const hasDetail = value => value !== undefined && value !== null && String(value).trim() !== ''
// `price` is quoted on the basis chosen in priceBasis: the whole property, one land unit, one sqft or one share.
export function totalAskingPrice(property) {
  const basis = property.buyerDetails?.priceBasis
  const price = Number(property.price)
  if (!(price > 0)) return null
  if (basis === 'Per land unit') return Number(property.landSize) > 0 ? price * Number(property.landSize) : null
  if (basis === 'Per sqft') return Number(property.squareFootage) > 0 ? price * Number(property.squareFootage) : null
  return basis === 'Total' ? price : null
}
export function unitAskingPrice(property) {
  const basis = property.buyerDetails?.priceBasis
  const price = Number(property.price)
  return price > 0 && (basis === 'Per land unit' || basis === 'Per sqft' || basis === 'Per share') ? price : null
}
export function askingPriceSummary(property) {
  const total = totalAskingPrice(property)
  const basis = property.buyerDetails?.priceBasis
  const labels = { Total: 'সম্পূর্ণ প্রপার্টির চাওয়া মূল্য', 'Per land unit': `প্রতি ${property.landUnit || 'জমির একক'}`, 'Per sqft': 'প্রতি বর্গফুট', 'Per share': 'প্রতি শেয়ার' }
  const label = property.priceUnit || (total !== null ? labels.Total : labels[basis] || 'মূল্য ও অন্তর্ভুক্ত খরচ নিশ্চিত করুন')
  return { amount: total ?? property.price, label }
}
export function detailGroups(property) {
  const data = property.buyerDetails || {}
  return buyerDetailGroups.map(group => ({ ...group, fields: group.fields
    .filter(f => hasDetail(data[f.key]) && !(property.hidePrice && group.key === 'terms'))
    .map(f => {
      const value = f.options?.find(option => option[0] === data[f.key])?.[1] ?? data[f.key]
      return { ...f, value, display: displayValue(f, value) }
    })
  })).filter(group => group.fields.length)
}
