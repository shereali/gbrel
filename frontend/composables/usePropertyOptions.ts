import { ref } from 'vue'

export interface PropertyOptions {
  categories: string[]
  divisions: string[]
  transaction_types: string[]
  statuses: string[]
  land_units: string[]
}

const defaultCategories = [
  'Land Share',
  'Flat',
  'Plot',
  'Land',
  'Hotel',
  'Duplex',
  'Commercial',
  'Penthouse'
]

const defaultDivisions = [
  'Dhaka North',
  'Dhaka South',
  'Chittagong',
  'Sylhet',
  "Cox's Bazar",
  'Gazipur',
  'Narayanganj',
  'Rajshahi',
  'Khulna',
  'Barisal',
  'Rangpur',
  'Mymensingh'
]

const defaultTransactionTypes = [
  'Sale',
  'Lease',
  'Joint Venture',
  'Auction'
]

const defaultStatuses = [
  'Draft',
  'Active',
  'Under Offer',
  'Sold',
  'Delisted'
]

const defaultLandUnits = [
  'Katha',
  'Bigha',
  'Shotok',
  'Decimal',
  'Sqft',
  'Acre'
]

// Global reactive state shared across pages and components
const categories = ref<string[]>([...defaultCategories])
const divisions = ref<string[]>([...defaultDivisions])
const transactionTypes = ref<string[]>([...defaultTransactionTypes])
const statuses = ref<string[]>([...defaultStatuses])
const landUnits = ref<string[]>([...defaultLandUnits])
const isLoading = ref(false)
const hasLoaded = ref(false)

export const usePropertyOptions = () => {
  const fetchOptions = async (force = false) => {
    if (hasLoaded.value && !force) {
      return {
        categories: categories.value,
        divisions: divisions.value,
        transaction_types: transactionTypes.value,
        statuses: statuses.value,
        land_units: landUnits.value
      }
    }

    isLoading.value = true
    try {
      const res = await fetch(useApiUrl('/property-options'))
      if (res.ok) {
        const json = await res.json()
        if (json && json.success && json.data) {
          if (Array.isArray(json.data.categories) && json.data.categories.length > 0) {
            categories.value = json.data.categories
          }
          if (Array.isArray(json.data.divisions) && json.data.divisions.length > 0) {
            divisions.value = json.data.divisions
          }
          if (Array.isArray(json.data.transaction_types) && json.data.transaction_types.length > 0) {
            transactionTypes.value = json.data.transaction_types
          }
          if (Array.isArray(json.data.statuses) && json.data.statuses.length > 0) {
            statuses.value = json.data.statuses
          }
          if (Array.isArray(json.data.land_units) && json.data.land_units.length > 0) {
            landUnits.value = json.data.land_units
          }
          hasLoaded.value = true
        }
      }
    } catch (err) {
      console.warn('Realtime property options fetch failed, using defaults:', err)
    } finally {
      isLoading.value = false
    }

    return {
      categories: categories.value,
      divisions: divisions.value,
      transaction_types: transactionTypes.value,
      statuses: statuses.value,
      land_units: landUnits.value
    }
  }

  const addCustomOption = async (
    key: 'categories' | 'divisions' | 'transaction_types' | 'statuses' | 'land_units',
    rawItem: string
  ): Promise<string> => {
    const item = rawItem.trim()
    if (!item) throw new Error('Option name cannot be empty')

    // Optimistic local update
    if (key === 'categories' && !categories.value.includes(item)) categories.value.push(item)
    if (key === 'divisions' && !divisions.value.includes(item)) divisions.value.push(item)
    if (key === 'transaction_types' && !transactionTypes.value.includes(item)) transactionTypes.value.push(item)
    if (key === 'statuses' && !statuses.value.includes(item)) statuses.value.push(item)
    if (key === 'land_units' && !landUnits.value.includes(item)) landUnits.value.push(item)

    try {
      const res = await fetch(useApiUrl('/property-options/add-item'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ key, item })
      })
      if (!res.ok) {
        throw new Error(`Failed to save option on server: ${res.statusText}`)
      }
      const json = await res.json()
      if (json && json.success && Array.isArray(json.data)) {
        if (key === 'categories') categories.value = json.data
        if (key === 'divisions') divisions.value = json.data
        if (key === 'transaction_types') transactionTypes.value = json.data
        if (key === 'statuses') statuses.value = json.data
        if (key === 'land_units') landUnits.value = json.data
      }
    } catch (err) {
      console.warn('Backend persistence failed for custom option:', err)
    }

    return item
  }

  return {
    categories,
    divisions,
    transactionTypes,
    statuses,
    landUnits,
    isLoading,
    fetchOptions,
    addCustomOption
  }
}
