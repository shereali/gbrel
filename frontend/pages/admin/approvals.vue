<template>
  <div class="admin-page animate-fade-in">
    <div class="admin-header-row">
      <div>
        <h1 class="page-title">Legal Due Diligence & RAJUK Clearance Queue</h1>
        <p class="page-subtitle">Audit uploaded municipal documents, land registry Khatians (CS, RS, BS), mutation receipts, and issue verification badges.</p>
      </div>
      <div class="admin-header-actions">
        <span class="badge-admin urgent">{{ pendingApprovalItems.length }} Pending Legal Review</span>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="pendingApprovalItems.length === 0" class="panel-card text-center" style="padding:60px 20px;">
      <div style="font-size:2.5rem; margin-bottom:12px;">⚖️</div>
      <h3 class="panel-title" style="justify-content:center;">Legal Queue Clear</h3>
      <p class="panel-sub" style="margin-top:6px;">All seller submissions and title dossiers have been fully audited.</p>
    </div>

    <!-- Items List -->
    <div v-else style="display:flex; flex-direction:column; gap:20px;">
      <div v-for="item in pendingApprovalItems" :key="item.id" class="panel-card" style="border-left: 4px solid var(--color-gold);">
        <div class="flex justify-between items-start flex-wrap gap-4" style="margin-bottom:16px;">
          <div>
            <span class="badge-admin pending" style="margin-bottom:8px;">Awaiting Legal Vetting</span>
            <h3 style="font-size:1.3rem; font-weight:800; color:#FFF; margin-top:4px;">{{ item.title }}</h3>
            <div style="font-size:0.85rem; color:#CBD5E1; margin-top:2px;">Seller: <strong>{{ item.seller }}</strong> (Phone: {{ item.phone }})</div>
          </div>
          <div style="text-align:right;">
            <div style="font-family:var(--font-ui); font-size:1.45rem; font-weight:800; color:#10B981; font-variant-numeric:tabular-nums;">
              {{ formatBDT(item.price) }}
            </div>
            <div style="font-size:0.8rem; color:#94A3B8;">{{ item.location }}</div>
          </div>
        </div>

        <!-- Document Checklist -->
        <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:var(--radius-md); padding:16px; margin-bottom:20px;">
          <div class="flex justify-between items-center" style="margin-bottom:10px;">
            <strong style="color:#FFF; font-size:0.88rem;">Uploaded Document Dossier:</strong>
            <span style="font-size:0.75rem; color:#D4AF37;">Click file to preview dossier records</span>
          </div>
          <div class="grid grid-3" style="gap:10px;">
            <div 
              v-for="(doc, idx) in item.documents" 
              :key="idx" 
              class="doc-attachment-pill" 
              style="cursor:pointer;"
              @click="openDocModal(item, doc)"
            >
              <span>📄</span>
              <span style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ doc }}</span>
              <span style="color:#10B981; font-size:0.75rem;">View ↗</span>
            </div>
          </div>
        </div>

        <div class="flex justify-between items-center flex-wrap gap-4">
          <div style="font-size:0.85rem; color:#10B981; font-weight:600; display:flex; align-items:center; gap:6px;">
            <span>✔</span>
            <span>0 Encumbrance Guarantee Verified by Supreme Court Panel</span>
          </div>
          <div class="flex gap-3">
            <button class="btn btn-sm btn-outline-white" style="color:#EF4444; border-color:rgba(239,68,68,0.4);" @click="openRejectModal(item)">
              Request Clarification / Reject
            </button>
            <button class="btn btn-sm btn-emerald" @click="approveListing(item.id)">
              ✔ Approve & Issue RAJUK Verified Badge
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 1: DOCUMENT PREVIEW MODAL
         ====================================================================== -->
    <div v-if="previewDoc" class="admin-modal-overlay" @click.self="previewDoc = null">
      <div class="admin-modal-card animate-fade-in-up">
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">Official Document Dossier</h3>
          <button class="admin-modal-close" @click="previewDoc = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <div style="background:#1E293B; border-radius:8px; padding:16px; margin-bottom:16px;">
            <div style="font-size:0.8rem; color:#94A3B8; text-transform:uppercase; font-weight:700;">Document Title</div>
            <div style="font-size:1.1rem; color:#FFF; font-weight:700; margin-top:2px;">{{ previewDoc.docName }}</div>
            <div style="font-size:0.85rem; color:#D4AF37; margin-top:4px;">Mandate: {{ previewDoc.item.title }}</div>
          </div>
          <div style="border:1px dashed rgba(255,255,255,0.15); border-radius:8px; padding:32px 20px; text-align:center;">
            <div style="font-size:2.5rem; margin-bottom:10px;">📜</div>
            <div style="font-weight:700; color:#FFF;">Digital Certification Pass Verified</div>
            <p style="color:#94A3B8; font-size:0.85rem; max-width:400px; margin:8px auto 0;">
              Certified by the Land Records & Survey Directorate (Govt. of Bangladesh) with QR verification hash #GBREL-{{ Date.now().toString().slice(-6) }}.
            </p>
          </div>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" @click="previewDoc = null">Close Preview</button>
          <button class="btn btn-sm btn-emerald" @click="markDocVerified">Mark Document Verified</button>
        </div>
      </div>
    </div>

    <!-- ======================================================================
         MODAL 2: REJECT / CLARIFICATION REASON MODAL
         ====================================================================== -->
    <div v-if="rejectItem" class="admin-modal-overlay" @click.self="rejectItem = null">
      <div class="admin-modal-card animate-fade-in-up">
        <div class="admin-modal-header" style="background:#1E1622; border-bottom:1px solid rgba(239,68,68,0.2);">
          <h3 class="admin-modal-title" style="color:#F87171;">Request Clarification / Reject</h3>
          <button class="admin-modal-close" @click="rejectItem = null">✕</button>
        </div>
        <div class="admin-modal-body">
          <p style="color:#E2E8F0; font-size:0.92rem; margin-bottom:14px;">
            Specify the legal clarification needed from seller <strong>{{ rejectItem.seller }}</strong> for <em>"{{ rejectItem.title }}"</em>:
          </p>
          <div class="form-group" style="margin-bottom:14px;">
            <label class="form-label" style="color:#CBD5E1;">Clarification Reason</label>
            <select v-model="rejectReason" class="form-select" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);">
              <option value="Mutation record not updated for current financial year">Mutation record not updated for current financial year</option>
              <option value="CS / RS / BS Khatian ownership lineage mismatch">CS / RS / BS Khatian ownership lineage mismatch</option>
              <option value="RAJUK approved plan floor clearance document missing">RAJUK approved plan floor clearance document missing</option>
              <option value="Boundary demarcation certificate required">Boundary demarcation certificate required</option>
              <option value="Custom inquiry">Other legal inquiry</option>
            </select>
          </div>
          <div v-if="rejectReason === 'Custom inquiry'" class="form-group">
            <textarea v-model="customReason" placeholder="Type custom clarification notes..." rows="3" class="form-textarea" style="background:#1E293B; color:#FFF; border-color:rgba(255,255,255,0.15);"></textarea>
          </div>
        </div>
        <div class="admin-modal-footer">
          <button class="btn btn-sm btn-outline-white" @click="rejectItem = null">Cancel</button>
          <button class="btn btn-sm" style="background:#EF4444; color:#FFF;" @click="confirmReject">
            Dispatch Clarification Notice
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useProperties } from '~/composables/useProperties'
import { formatBDT } from '~/composables/useCurrency'
import { useToast } from '~/composables/useToast'

definePageMeta({
  layout: 'admin'
})

const { addProperty } = useProperties()
const toast = useToast()

const pendingApprovalItems = ref([
  {
    id: 901,
    title: '7.5 Katha Corner Plot in Purbachal Sector 20',
    seller: 'Kazi Rashedul Islam',
    phone: '+880 1712-445566',
    price: 27000000,
    location: 'Sector 20, Purbachal New Town',
    documents: [
      'RAJUK Allotment Letter.pdf',
      'Mutation & Khajna Receipt.pdf',
      'CS/RS/BS Khatian Record.pdf'
    ]
  },
  {
    id: 902,
    title: 'Modern 3,200 Sqft Duplex Villa in Bashundhara Block-I',
    seller: 'Engr. Asadullah Chowdhury',
    phone: '+880 1819-334455',
    price: 39000000,
    location: 'Block I, Bashundhara R/A',
    documents: [
      'RAJUK Approved 3-Storey Villa Plan.pdf',
      'Sub-Registry Title Deed.pdf',
      'Fire & Civil Aviation NOC.pdf'
    ]
  }
])

const previewDoc = ref<{ item: any; docName: string } | null>(null)
const rejectItem = ref<any | null>(null)
const rejectReason = ref('Mutation record not updated for current financial year')
const customReason = ref('')

const openDocModal = (item: any, docName: string) => {
  previewDoc.value = { item, docName }
}

const markDocVerified = () => {
  toast.success('Document Certified', `Verified ${previewDoc.value?.docName}.`)
  previewDoc.value = null
}

const openRejectModal = (item: any) => {
  rejectItem.value = item
  rejectReason.value = 'Mutation record not updated for current financial year'
  customReason.value = ''
}

const confirmReject = () => {
  if (rejectItem.value) {
    const idx = pendingApprovalItems.value.findIndex(i => i.id === rejectItem.value.id)
    if (idx > -1) {
      pendingApprovalItems.value.splice(idx, 1)
    }
    const finalReason = rejectReason.value === 'Custom inquiry' ? customReason.value : rejectReason.value
    toast.warning('Clarification Dispatched', `Notice sent to ${rejectItem.value.seller}: "${finalReason}".`)
    rejectItem.value = null
  }
}

const approveListing = async (id: number) => {
  const idx = pendingApprovalItems.value.findIndex(item => item.id === id)
  if (idx > -1) {
    const item = pendingApprovalItems.value[idx]
    pendingApprovalItems.value.splice(idx, 1)
    await addProperty({
      title: item.title,
      price: item.price,
      propertyType: 'Plot',
      state: 'Dhaka North',
      areaName: 'Purbachal',
      address: item.location,
      isRajukApproved: true,
      status: 'Active',
      images: ['https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1600&auto=format&fit=crop'],
      documentsVerified: item.documents
    })
    toast.success('Title Approved & Published', `RAJUK Verified badge issued for "${item.title}".`)
  }
}
</script>

<style scoped>
.doc-attachment-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: var(--radius-sm);
  padding: 8px 12px;
  font-size: 0.82rem;
  color: #CBD5E1;
  transition: all var(--transition-fast);
}

.doc-attachment-pill:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: var(--color-gold);
}
</style>
