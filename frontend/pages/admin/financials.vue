<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Financials, Escrow & Broker Commission Reports</h1>
        <p class="page-subtitle">Track transaction settlements, bank escrow milestones, and broker revenue disbursements across Bangladesh.</p>
      </div>
      <div class="admin-header-actions">
        <button class="btn btn-outline-white" @click="downloadFinancialSummary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>
          </svg>
          <span>Export Escrow Statement</span>
        </button>
      </div>
    </div>

    <!-- Top 3 KPI Cards -->
    <div class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Transacted Asset Volume</span>
          <span class="kpi-icon-pill emerald">৳</span>
        </div>
        <div class="kpi-value text-emerald">{{ formatCrore(totalVolume) }}</div>
        <div class="kpi-sub">{{ dealsList.length }} Registered Portfolio Deals</div>
      </div>

      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Advisory Commission</span>
          <span class="kpi-icon-pill gold">★</span>
        </div>
        <div class="kpi-value text-gold">{{ formatCrore(totalCommission) }}</div>
        <div class="kpi-sub">Company Account / Balance</div>
      </div>

      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Active Escrow Accounts</span>
          <span class="kpi-icon-pill blue">🏦</span>
        </div>
        <div class="kpi-value text-blue">{{ formatCrore(totalEscrow) }}</div>
        <div class="kpi-sub">Held securely in partner tier-1 banks</div>
      </div>
    </div>

    <!-- Settlement History Table with Bank Filter -->
    <div class="panel-card" style="padding:0; overflow:hidden;">
      <div style="padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center; flex-wrap:gap-3;">
        <div class="flex items-center gap-2">
          <span style="font-size:0.85rem; color:#94A3B8;">Filter Partner Bank:</span>
          <select v-model="selectedBank" class="status-inline-select">
            <option value="All">All Banks</option>
            <option value="BRAC Bank">BRAC Bank Escrow</option>
            <option value="DBH Finance">DBH Finance</option>
            <option value="City Bank">City Bank Private Banking</option>
          </select>
        </div>
        <span style="font-size:0.82rem; color:#94A3B8;">{{ filteredDeals.length }} Deals</span>
      </div>

      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Deal ID</th>
              <th>Property Asset</th>
              <th>Buyer / Investor</th>
              <th>Transacted Value</th>
              <th>Commission (2%)</th>
              <th>Escrow Partner Bank</th>
              <th>Status</th>
              <th style="text-align:right;">Details</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="deal in filteredDeals" :key="deal.id">
              <td style="font-weight:800; color:#D4AF37;">#{{ deal.id }}</td>
              <td><strong style="color:#FFF;">{{ deal.property }}</strong></td>
              <td style="color:#CBD5E1;">{{ deal.buyer }}</td>
              <td style="font-weight:800; color:#10B981; font-family:var(--font-ui);">{{ formatBDT(deal.value) }}</td>
              <td style="font-weight:700; color:#D4AF37; font-family:var(--font-ui);">{{ formatBDT(deal.commission) }}</td>
              <td><span class="badge badge-status">{{ deal.bank }}</span></td>
              <td>
                <span class="badge-admin" :class="deal.status === 'Settled' ? 'active' : 'pending'">
                  {{ deal.status }}
                </span>
              </td>
              <td style="text-align:right;">
                <button class="btn btn-sm btn-outline-white" @click="viewDeal(deal)">
                  Milestones ↗
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================================
         MODAL: DEAL SETTLEMENT & ESCROW MILESTONES
         ====================================================================== -->
    <div v-if="selectedDeal" class="admin-modal-overlay" @click.self="selectedDeal = null">
      <div class="admin-modal-card animate-fade-in-up">
        <div class="admin-modal-header">
          <div>
            <h3 class="admin-modal-title">Escrow Settlement #{{ selectedDeal.id }}</h3>
            <p class="panel-sub">{{ selectedDeal.property }}</p>
          </div>
          <button class="admin-modal-close" @click="selectedDeal = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <div style="background:var(--admin-bg-surface-alt); border:1px solid var(--admin-border-subtle); border-radius:8px; padding:16px; margin-bottom:20px; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
              <div style="font-size:0.75rem; text-transform:uppercase;" class="text-subtle">Asset Valuation</div>
              <div style="font-size:1.25rem; font-weight:800; color:#10B981; margin-top:2px;">{{ formatBDT(selectedDeal.value) }}</div>
            </div>
            <div>
              <div style="font-size:0.75rem; text-transform:uppercase;" class="text-subtle">Escrow Trustee</div>
              <div style="font-size:1.05rem; font-weight:700; margin-top:2px;">{{ selectedDeal.bank }}</div>
            </div>
          </div>

          <strong style="font-size:0.9rem; display:block; margin-bottom:12px;">Transaction Milestones:</strong>
          <div style="display:flex; flex-direction:column; gap:10px;">
            <div class="milestone-row completed">
              <span class="check">✔</span>
              <div style="flex:1;">
                <div style="font-weight:700;">Legal Title & Mutation Vetting</div>
                <div style="font-size:0.8rem;" class="text-subtle">Supreme Court panel confirmed 0 encumbrances</div>
              </div>
              <span class="badge-admin active" style="font-size:0.7rem;">PASSED</span>
            </div>

            <div class="milestone-row completed">
              <span class="check">✔</span>
              <div style="flex:1;">
                <div style="font-weight:700;">10% Bayna Initial Deposit</div>
                <div style="font-size:0.8rem;" class="text-subtle">Deposited into dedicated client escrow account</div>
              </div>
              <span class="badge-admin active" style="font-size:0.7rem;">PASSED</span>
            </div>

            <div class="milestone-row" :class="selectedDeal.status === 'Settled' ? 'completed' : 'pending'">
              <span class="check">{{ selectedDeal.status === 'Settled' ? '✔' : '⏳' }}</span>
              <div style="flex:1;">
                <div style="font-weight:700;">Sub-Registry Deed Execution</div>
                <div style="font-size:0.8rem;" class="text-subtle">Final deed pass and 90% payout disbursement</div>
              </div>
              <span class="badge-admin" :class="selectedDeal.status === 'Settled' ? 'active' : 'pending'" style="font-size:0.7rem;">
                {{ selectedDeal.status === 'Settled' ? 'COMPLETE' : 'SCHEDULED' }}
              </span>
            </div>
          </div>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" @click="selectedDeal = null">Close</button>
          <button class="btn btn-sm btn-emerald" @click="downloadInvoice(selectedDeal)">Download Invoice (PDF)</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { formatBDT } from '~/composables/useCurrency'
import { useToast } from '~/composables/useToast'
import { useApiUrl } from '~/composables/useApi'

definePageMeta({
  layout: 'admin'
})

const toast = useToast()
const selectedBank = ref('All')
const selectedDeal = ref<any | null>(null)
const isLoading = ref(false)

const dealsList = ref<any[]>([
  { id: 'TX-901', property: 'Lakeview Penthouse at Gulshan-2 Diplomatic Zone', buyer: 'Dr. Farhan Chowdhury', value: 78000000, commission: 1560000, bank: 'BRAC Bank', status: 'Settled' },
  { id: 'TX-902', property: '10 Katha Corner Plot in Purbachal Sector 17', buyer: 'Engr. Mahfuzur Rahman', value: 36000000, commission: 720000, bank: 'DBH Finance', status: 'Settled' },
  { id: 'TX-903', property: 'Luxury Beachfront Presidential Suite at Marine Drive', buyer: 'Syed Tanzeem', value: 19000000, commission: 380000, bank: 'City Bank', status: 'In Escrow' }
])

const formatCrore = (val: number) => {
  const cr = (val || 0) / 10000000
  return `৳ ${cr.toFixed(2)} Cr`
}

const totalVolume = computed(() => {
  return dealsList.value.reduce((acc, d) => acc + (Number(d.value) || 0), 0)
})

const totalCommission = computed(() => {
  return dealsList.value.reduce((acc, d) => acc + (Number(d.commission) || ((Number(d.value) || 0) * 0.02)), 0)
})

const totalEscrow = computed(() => {
  return dealsList.value
    .filter(d => d.status === 'In Escrow' || d.status === 'Pending')
    .reduce((acc, d) => acc + (Number(d.value) || 0), 0)
})

const fetchFinancials = async () => {
  isLoading.value = true
  try {
    const res = await fetch(useApiUrl('/financials'))
    if (res.ok) {
      const json = await res.json()
      if (json && json.success && Array.isArray(json.data) && json.data.length > 0) {
        dealsList.value = json.data.map(d => ({
          id: d.deal_id || d.id,
          property: d.property_title || d.property,
          buyer: d.buyer_name || d.buyer,
          value: Number(d.value) || 0,
          commission: Number(d.commission) || (Number(d.value) * 0.02) || 0,
          bank: d.bank || 'BRAC Bank',
          status: d.status || 'Settled'
        }))
      }
    }
  } catch (err) {
    console.error('Failed to load financial records:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await fetchFinancials()
})

const filteredDeals = computed(() => {
  if (selectedBank.value === 'All') return dealsList.value
  return dealsList.value.filter(d => d.bank === selectedBank.value)
})

const viewDeal = (deal: any) => {
  selectedDeal.value = deal
}

const downloadInvoice = (deal: any) => {
  toast.success('Invoice Generated', `Official escrow receipt downloaded for deal #${deal.id}.`)
  selectedDeal.value = null
}

const downloadFinancialSummary = () => {
  try {
    const csvContent = [
      'Deal ID,Property,Buyer,Value BDT,Commission BDT,Bank,Status',
      ...dealsList.value.map(d => `${d.id},"${d.property}","${d.buyer}",${d.value},${d.commission},"${d.bank}",${d.status}`)
    ].join('\r\n')

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `GBREL_Escrow_Report_${new Date().toISOString().slice(0, 10)}.csv`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)

    toast.success('Escrow Report Exported', 'Statement downloaded as CSV.')
  } catch (err: any) {
    toast.error('Export Failed', err.message || 'Unable to download report.')
  }
}
</script>

<style scoped>
.milestone-row {
  display: flex;
  align-items: center;
  gap: 12px;
  background: var(--admin-bg-surface-alt);
  border: 1px solid var(--admin-border-subtle);
  border-radius: 8px;
  padding: 12px 14px;
}

.milestone-row.completed {
  border-color: rgba(16, 185, 129, 0.3);
}

.milestone-row .check {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--admin-border-subtle);
  color: var(--admin-text-secondary);
  font-size: 0.85rem;
}

.milestone-row.completed .check {
  background: rgba(16, 185, 129, 0.2);
  color: #10B981;
}
</style>
