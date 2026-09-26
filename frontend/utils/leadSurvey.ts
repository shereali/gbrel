// Buyer survey used on property landing pages (Facebook ad traffic).
// Stored values stay in English so the admin panel's existing filters keep working
// ("Within 30 days", "Listed price fits budget" drive its hot-lead view).

export interface SurveyOption { value: string; label: string; note?: string; points: number }
export interface SurveyQuestion { key: 'purpose' | 'timeline' | 'budget' | 'payment' | 'residence'; title: string; help?: string; options: SurveyOption[] }

export const purposeOptions: SurveyOption[] = [
  { value: 'Own / family use', label: 'নিজে বা পরিবার নিয়ে ব্যবহার করব', points: 1 },
  { value: 'Investment', label: 'বিনিয়োগ হিসেবে কিনব', points: 1 },
  { value: 'Own use and investment', label: 'দুটোই ভাবছি', points: 1 }
]

// Plots and land are often bought to build on, so developers get their own answer there.
const landTypes = ['Plot', 'Land', 'Commercial']
export const purposeOptionsFor = (propertyType?: string): SurveyOption[] =>
  propertyType && landTypes.includes(propertyType)
    ? [...purposeOptions, { value: 'Development / construction', label: 'ভবন নির্মাণ বা ডেভেলপমেন্টের জন্য', points: 1 }]
    : purposeOptions

export const buildQuestions = (hidePrice: boolean, propertyType?: string): SurveyQuestion[] => [
  { key: 'purpose', title: 'প্রপার্টিটি কী কাজে লাগাতে চান?', options: purposeOptionsFor(propertyType) },
  {
    key: 'timeline',
    title: 'কবে নাগাদ কিনতে চান?',
    help: 'সঠিক উত্তর দিন — এখনই কেনার চাপ নেই।',
    options: [
      { value: 'Within 30 days', label: 'আগামী ৩০ দিনের মধ্যে', points: 3 },
      { value: '1–3 months', label: '১ থেকে ৩ মাসের মধ্যে', points: 2 },
      { value: '3–6 months', label: '৩ থেকে ৬ মাসের মধ্যে', points: 1 },
      { value: 'Researching; no fixed timeline', label: 'এখন শুধু খোঁজ নিচ্ছি', points: 0 }
    ]
  },
  {
    key: 'budget',
    title: hidePrice ? 'বাজেট নিয়ে আপনার অবস্থা কী?' : 'এই দাম কি আপনার বাজেটের মধ্যে?',
    options: hidePrice
      ? [
          { value: 'Budget ready; needs price', label: 'বাজেট ঠিক আছে, দাম জানতে চাই', points: 3 },
          { value: 'Needs full cost breakdown', label: 'মোট খরচ জেনে ঠিক করব', points: 1 }
        ]
      : [
          { value: 'Listed price fits budget', label: 'হ্যাঁ, বাজেটের মধ্যে', points: 3 },
          { value: 'Needs payment / financing discussion', label: 'কিস্তির শর্ত জানলে সম্ভব', points: 2 },
          { value: 'Needs full cost breakdown', label: 'মোট খরচ জেনে ঠিক করব', points: 1 },
          { value: 'Budget below listed price', label: 'আমার বাজেট এর চেয়ে কম', points: 0 }
        ]
  },
  {
    key: 'payment',
    title: 'টাকা কীভাবে দেওয়ার পরিকল্পনা?',
    options: [
      { value: 'Own funds, lump sum', label: 'নিজের টাকায়, এককালীন', points: 2 },
      { value: 'Own funds, installments', label: 'নিজের টাকায়, কিস্তিতে', points: 2 },
      { value: 'Bank / home loan', label: 'ব্যাংক বা হোম লোন নিয়ে', points: 1 },
      { value: 'Not decided', label: 'এখনো ঠিক করিনি', points: 0 }
    ]
  },
  {
    key: 'residence',
    title: 'আপনি এখন কোথায় থাকেন?',
    help: 'সাইট ভিজিট বা ভিডিও কল কীভাবে সাজাব, তা বুঝতে।',
    options: [
      { value: 'Bangladesh, can visit site', label: 'বাংলাদেশে, সাইটে আসতে পারব', points: 1 },
      { value: 'Bangladesh, other district', label: 'বাংলাদেশে, অন্য জেলায়', points: 0 },
      { value: 'Abroad (NRB)', label: 'দেশের বাইরে থাকি', points: 0 }
    ]
  }
]

export type Answers = Partial<Record<SurveyQuestion['key'], string>>

// 0–9 points. Hot leads are close in time AND able to pay; the team calls them first.
export const scoreLead = (questions: SurveyQuestion[], answers: Answers) => {
  const points = questions.reduce((sum, q) => sum + (q.options.find(o => o.value === answers[q.key])?.points ?? 0), 0)
  const max = questions.reduce((sum, q) => sum + Math.max(...q.options.map(o => o.points)), 0)
  const tier = points >= 7 ? 'HOT' : points >= 4 ? 'WARM' : 'COLD'
  return { points, max, tier } as const
}

export const labelFor = (questions: SurveyQuestion[], key: SurveyQuestion['key'], value?: string) =>
  questions.find(q => q.key === key)?.options.find(o => o.value === value)?.label || ''
