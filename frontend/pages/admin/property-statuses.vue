<template>
  <AdminMasterDataCrud
    entity-key="property-statuses"
    api-endpoint="/property-statuses"
    title="Property Lifecycle Statuses"
    subtitle="Configure portfolio workflow stages, display privacy levels, and smart badge coloration"
    entity-title="Lifecycle Statuses"
    singular-name="Property Status"
    table-name="property_statuses"
    name-placeholder="e.g. Draft, Active, Under Offer, Sold, Delisted"
  />
</template>

<script setup lang="ts">
import AdminMasterDataCrud from '~/components/admin/MasterDataCrud.vue'

definePageMeta({
  layout: 'admin'
})

useHead({
  title: 'Property Statuses Management | GBREL Admin'
})
</script>
