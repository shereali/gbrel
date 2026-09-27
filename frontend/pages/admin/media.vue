<template>
  <div class="mp">
    <header class="mp-head">
      <h1>Media library</h1>
      <p>Photos and videos for your listings. Upload once, then choose them from any property's editor.</p>
    </header>
    <AdminMediaLibrary mode="manage" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' })
useHead({ title: 'Media library | GBREL Admin' })
</script>

<style scoped>
.mp { max-width: 1320px; }
.mp-head { margin-bottom: 20px; }
.mp-head h1 { font-family: 'Anek Bangla', 'Plus Jakarta Sans', sans-serif; font-size: 2rem; font-weight: 800; margin-bottom: 4px; color: var(--admin-text-primary); }
.mp-head p { color: var(--admin-text-secondary); max-width: 70ch; }
</style>
