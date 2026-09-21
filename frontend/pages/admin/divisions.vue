<template>
  <AdminMasterDataCrud
    entity-key="divisions"
    api-endpoint="/divisions"
    title="Divisions & Geographic Regions"
    subtitle="Configure administrative divisions, metropolitan districts, and bilingual zone labels across Bangladesh"
    entity-title="Locations & Regions"
    singular-name="Division"
    table-name="divisions"
    name-placeholder="e.g. Dhaka North, Sylhet, Cox's Bazar"
  />
</template>

<script setup lang="ts">
import AdminMasterDataCrud from '~/components/admin/MasterDataCrud.vue'

definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

useHead({
  title: 'Divisions & Regions Management | GBREL Admin'
})
</script>
