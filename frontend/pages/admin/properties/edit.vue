<template>
  <div class="admin-page">
    <PropertyEditor />
  </div>
</template>

<script setup lang="ts">
import PropertyEditor from '~/components/admin/PropertyEditor.vue'

definePageMeta({
  layout: 'admin'
})
</script>
