// Answer options for the owner listing form. Values are stored in English (validated by the API); labels are Bangla.
export type Option = { value: string; label: string }

export const propertyTypeOptions: Option[] = [
  { value: 'Plot', label: 'প্লট' },
  { value: 'Land', label: 'জমি' },
  { value: 'Flat', label: 'ফ্ল্যাট' },
  { value: 'Duplex', label: 'ডুপ্লেক্স / বাড়ি' },
  { value: 'Commercial', label: 'বাণিজ্যিক' },
  { value: 'Land Share', label: 'জমি শেয়ার' },
  { value: 'Penthouse', label: 'পেন্টহাউস' },
  { value: 'Hotel', label: 'হোটেল / রিসোর্ট' }
]

export const divisionOptions: Option[] = [
  { value: 'Dhaka', label: 'ঢাকা' },
  { value: 'Chattogram', label: 'চট্টগ্রাম' },
  { value: 'Sylhet', label: 'সিলেট' },
  { value: 'Rajshahi', label: 'রাজশাহী' },
  { value: 'Khulna', label: 'খুলনা' },
  { value: 'Barishal', label: 'বরিশাল' },
  { value: 'Rangpur', label: 'রংপুর' },
  { value: 'Mymensingh', label: 'ময়মনসিংহ' }
]

export const landUnitOptions: Option[] = [
  { value: 'Katha', label: 'কাঠা' },
  { value: 'Shotok', label: 'শতক' },
  { value: 'Decimal', label: 'শতাংশ' },
  { value: 'Bigha', label: 'বিঘা' },
  { value: 'Sqft', label: 'বর্গফুট' }
]

export const facingOptions: Option[] = [
  { value: 'North', label: 'উত্তর' }, { value: 'South', label: 'দক্ষিণ' }, { value: 'East', label: 'পূর্ব' }, { value: 'West', label: 'পশ্চিম' },
  { value: 'North-East', label: 'উত্তর-পূর্ব' }, { value: 'North-West', label: 'উত্তর-পশ্চিম' }, { value: 'South-East', label: 'দক্ষিণ-পূর্ব' }, { value: 'South-West', label: 'দক্ষিণ-পশ্চিম' }
]

export const completionOptions: Option[] = [
  { value: 'Ready', label: 'রেডি / তৈরি' },
  { value: 'Under Construction', label: 'নির্মাণাধীন' },
  { value: 'Upcoming Project', label: 'আসন্ন প্রকল্প' }
]

export const landUseOptions: Option[] = [
  { value: 'Residential', label: 'আবাসিক' }, { value: 'Commercial', label: 'বাণিজ্যিক' }, { value: 'Mixed', label: 'মিশ্র' }, { value: 'Agricultural', label: 'কৃষি' }
]

export const yesNo: Option[] = [{ value: 'Yes', label: 'হ্যাঁ' }, { value: 'No', label: 'না' }]

export const submitterRoleOptions: Option[] = [
  { value: 'Owner', label: 'আমি মালিক' },
  { value: 'Co-owner', label: 'আমি একজন অংশীদার মালিক' },
  { value: 'Authorized representative', label: 'মালিকের নিয়োগ করা প্রতিনিধি' }
]

export const allOwnersAgreeOptions: Option[] = [{ value: 'Yes', label: 'হ্যাঁ, সবাই রাজি' }, { value: 'No', label: 'না, সবাই রাজি নন' }, { value: 'Not sure', label: 'নিশ্চিত নই' }]

export const ownershipSourceOptions: Option[] = [
  { value: 'Purchase', label: 'কিনে' }, { value: 'Inheritance', label: 'ওয়ারিশ / উত্তরাধিকার সূত্রে' }, { value: 'Allotment', label: 'সরকারি বরাদ্দ (রাজউক ইত্যাদি)' }, { value: 'Gift', label: 'হেবা / দান' }, { value: 'Other', label: 'অন্যভাবে' }
]

export const possessionOptions: Option[] = [
  { value: 'Owner', label: 'মালিকের দখলে' }, { value: 'Tenant', label: 'ভাড়াটিয়ার দখলে' }, { value: 'Vacant', label: 'খালি' }, { value: 'Other', label: 'অন্য কারও দখলে' }
]

export const noneExistsOptions: Option[] = [{ value: 'None', label: 'নেই' }, { value: 'Exists', label: 'আছে' }]

export const mutationOptions: Option[] = [{ value: 'Done', label: 'হয়েছে' }, { value: 'Pending', label: 'প্রক্রিয়াধীন' }, { value: 'Not done', label: 'হয়নি' }]

export const priceBasisOptions: Option[] = [
  { value: 'Total', label: 'পুরো প্রপার্টির দাম' }, { value: 'Per land unit', label: 'প্রতি একক (কাঠা/শতক)' }, { value: 'Per sqft', label: 'প্রতি বর্গফুট' }
]

export const sellTimelineOptions: Option[] = [
  { value: 'Within 1 month', label: '১ মাসের মধ্যে' }, { value: '1–3 months', label: '১–৩ মাসের মধ্যে' }, { value: '3–6 months', label: '৩–৬ মাসের মধ্যে' }, { value: 'No rush', label: 'তাড়া নেই' }
]

export const labelOf = (options: Option[], value?: string | null) => options.find(o => o.value === value)?.label || value || ''

export const hasRooms = (type?: string | null) => ['Flat', 'Duplex', 'Penthouse', 'Hotel', 'Commercial'].includes(type || '')
