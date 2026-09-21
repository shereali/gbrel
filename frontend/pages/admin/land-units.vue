<template>
  <AdminMasterDataCrud
    entity-key="land-units"
    api-endpoint="/land-units"
    title="Land & Area Measurement Units"
    subtitle="Configure land units, area symbols, and square feet conversion multipliers (Katha, Bigha, Shotok, Decimal)"
    entity-title="Units of Measure"
    singular-name="Land Unit"
    table-name="land_units"
    name-placeholder="e.g. Katha, Bigha, Shotok, Acre"
  />
</template>

<script setup lang="ts">
import AdminMasterDataCrud from '~/components/admin/MasterDataCrud.vue'

definePageMeta({
  layout: 'admin',
  middleware: ['auth']
})

useHead({
  title: 'Land Units Management | GBREL Admin'
})
</script>
