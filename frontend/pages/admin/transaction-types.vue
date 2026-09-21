<template>
  <AdminMasterDataCrud
    entity-key="transaction-types"
    api-endpoint="/transaction-types"
    title="Transaction Types"
    subtitle="Configure commercial transaction frameworks, joint ventures, sales mandates, and leases"
    entity-title="Deal Types"
    singular-name="Transaction Type"
    table-name="transaction_types"
    name-placeholder="e.g. Sale, Lease, Joint Venture, Auction"
  />
</template>

<script setup lang="ts">
import AdminMasterDataCrud from '~/components/admin/MasterDataCrud.vue'

definePageMeta({
  layout: 'admin'
})

useHead({
  title: 'Transaction Types Management | GBREL Admin'
})
</script>
