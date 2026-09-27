<template>
  <Teleport to="body">
    <div v-if="open" class="mpk" role="dialog" aria-modal="true" :aria-label="title">
      <div class="mpk-backdrop" @click="$emit('close')"></div>
      <div ref="panel" class="mpk-panel" tabindex="-1">
        <div class="mpk-head">
          <h2>{{ title }}</h2>
          <button type="button" class="mpk-close" aria-label="Close" @click="$emit('close')"><X :size="20" /></button>
        </div>
        <AdminMediaLibrary mode="pick" :accept="accept" :multiple="multiple" :pick-label="pickLabel" @pick="items => { $emit('pick', items); $emit('close') }" />
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { X } from 'lucide-vue-next'
import type { MediaItem } from '~/composables/useMediaLibrary'
import { useOverlayBehavior } from '~/composables/useOverlayBehavior'

const props = withDefaults(defineProps<{ open: boolean; accept?: 'image' | 'video' | 'any'; multiple?: boolean; title?: string; pickLabel?: string }>(), { accept: 'image', multiple: false, title: 'Choose from media library', pickLabel: 'Use selected' })
const emit = defineEmits<{ close: []; pick: [items: MediaItem[]] }>()
const panel = ref<HTMLElement | null>(null)
// Shared dialog behaviour: Escape closes, Tab stays inside the picker, the page stops scrolling while it is open,
// focus returns to the button that opened it, and scrolling is restored even if the editor unmounts while open.
useOverlayBehavior(computed(() => props.open), () => emit('close'), panel)
</script>

<style scoped>
.mpk { position: fixed; inset: 0; z-index: 2000; display: grid; place-items: center; padding: 24px; }
.mpk-backdrop { position: absolute; inset: 0; background: rgba(22, 36, 26, .55); }
.mpk-panel { position: relative; width: min(1100px, 100%); max-height: calc(100vh - 48px); overflow: auto; background: var(--admin-bg-surface, #fff); border-radius: 18px; padding: 20px 22px 0; box-shadow: 0 30px 80px -20px rgba(0, 0, 0, .45); outline: none; }
.mpk-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.mpk-head h2 { font-family: 'Anek Bangla', 'Plus Jakarta Sans', sans-serif; font-size: 1.4rem; font-weight: 800; color: var(--admin-text-primary); }
.mpk-close { border: 0; background: none; cursor: pointer; color: var(--admin-text-muted); padding: 6px; }
.mpk-panel :deep(.ml-pickbar) { padding-bottom: 16px; }
@media (max-width: 640px) { .mpk { padding: 0; } .mpk-panel { max-height: 100vh; height: 100vh; border-radius: 0; padding: 16px 14px 0; } }
</style>
