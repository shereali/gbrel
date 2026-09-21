<template>
  <AdminMasterDataCrud
    entity-key="categories"
    api-endpoint="/categories"
    title="Property Categories"
    subtitle="Manage luxury real estate categories, classifications, and portal grouping filters"
    entity-title="Taxonomy & Types"
    singular-name="Category"
    table-name="categories"
    name-placeholder="e.g. Land Share, Penthouse, Duplex"
  />
</template>

<script setup lang="ts">
import AdminMasterDataCrud from '~/components/admin/MasterDataCrud.vue'

definePageMeta({
  layout: 'admin'
})

useHead({
  title: 'Property Categories Management | GBREL Admin'
})
</script>
