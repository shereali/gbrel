<template>
  <div class="admin-page animate-fade-in">
    <h1 class="page-title">Financials, Escrow & Broker Commission Reports</h1>
    <p class="page-subtitle">Track transaction settlements, escrow milestones, and broker revenue disbursements across Bangladesh.</p>

    <div class="grid grid-3" style="margin: 24px 0;">
      <div class="kpi-card">
        <div class="kpi-label">YTD Transacted Asset Volume</div>
        <div class="kpi-value text-emerald">৳ 124.50 Cr</div>
        <div class="kpi-sub">8 Completed Land & Luxury Deals</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Earned Advisory Commission (2%)</div>
        <div class="kpi-value text-gold">৳ 2.49 Cr</div>
        <div class="kpi-sub">Disbursed to GBREL Treasury</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">Active Escrow Accounts</div>
        <div class="kpi-value" style="color:#60A5FA;">৳ 45.00 Cr</div>
        <div class="kpi-sub">Held securely in partner banks</div>
      </div>
    </div>

    <!-- Settlement History Table -->
    <div class="panel-card" style="padding:0; overflow:hidden;">
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Deal ID</th>
              <th>Property Asset</th>
              <th>Buyer / Mandate</th>
              <th>Transacted Value</th>
              <th>Commission (2%)</th>
              <th>Escrow Partner Bank</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="deal in dealsList" :key="deal.id">
              <td style="font-weight:800; color:#D4AF37;">#{{ deal.id }}</td>
              <td><strong style="color:#FFF;">{{ deal.property }}</strong></td>
              <td style="color:#CBD5E1;">{{ deal.buyer }}</td>
              <td style="font-weight:800; color:#10B981;">{{ formatBDT(deal.value) }}</td>
              <td style="font-weight:700; color:#D4AF37;">{{ formatBDT(deal.commission) }}</td>
              <td><span class="badge badge-status">{{ deal.bank }}</span></td>
              <td><span class="badge badge-rajuk">{{ deal.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { formatBDT } from '~/composables/useCurrency'

definePageMeta({
  layout: 'admin'
})

const dealsList = ref([
  { id: 'TX-901', property: 'Lakeview Penthouse at Gulshan-2', buyer: 'Dr. Farhan Chowdhury', value: 78000000, commission: 1560000, bank: 'BRAC Bank Escrow', status: 'Settled' },
  { id: 'TX-902', property: '10 Katha Plot in Purbachal Sector 17', buyer: 'Engr. Mahfuzur Rahman', value: 36000000, commission: 720000, bank: 'DBH Finance', status: 'Settled' },
  { id: 'TX-903', property: 'Cox\'s Bazar Marine Drive Sea Suite', buyer: 'Syed Tanzeem', value: 19000000, commission: 380000, bank: 'City Bank Private Banking', status: 'In Escrow' }
])
</script>

<style scoped>
.page-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.2;
}

.page-subtitle {
  color: #CBD5E1;
  font-size: 0.95rem;
  margin-top: 4px;
}

.kpi-card {
  background: #0F172A;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: var(--radius-xl);
  padding: 22px;
}

.kpi-label {
  font-size: 0.78rem;
  color: #CBD5E1;
  text-transform: uppercase;
  font-weight: 700;
}

.kpi-value {
  font-family: var(--font-ui);
  font-size: 1.8rem;
  font-weight: 800;
  margin: 6px 0;
  font-variant-numeric: tabular-nums;
}

.kpi-sub {
  font-size: 0.8rem;
  color: #64748B;
}

.panel-card {
  background: #0F172A;
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: var(--radius-xl);
  padding: 24px;
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 780px;
}

.admin-table th {
  padding: 14px 16px;
  text-align: left;
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.78rem;
  color: #CBD5E1;
  text-transform: uppercase;
  font-weight: 700;
}

.admin-table td {
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  font-size: 0.88rem;
  vertical-align: middle;
}
</style>
