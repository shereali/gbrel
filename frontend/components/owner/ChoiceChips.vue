<template>
  <fieldset class="cc">
    <legend>{{ label }}<small v-if="hint">{{ hint }}</small></legend>
    <div class="cc-row" :class="{ 'cc-row--cards': cards }">
      <label v-for="o in options" :key="o.value" class="cc-opt" :class="{ on: modelValue === o.value }">
        <input type="radio" :name="name" :value="o.value" :checked="modelValue === o.value" :disabled="disabled" @change="emit('update:modelValue', o.value)" />
        <span>{{ o.label }}</span>
      </label>
    </div>
  </fieldset>
</template>

<script setup lang="ts">
import type { Option } from '~/utils/ownerListingOptions'

defineProps<{ modelValue?: string | null; options: Option[]; label: string; name: string; hint?: string; cards?: boolean; disabled?: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: string] }>()
</script>

<style scoped>
.cc { border: 0; padding: 0; margin: 0; min-width: 0; }
.cc legend { font-size: var(--gb-t-small); font-weight: 600; color: var(--gb-paddy); margin-bottom: 8px; padding: 0; }
.cc legend small { display: block; font-weight: 400; color: var(--gb-ink-soft); font-size: .82rem; }
.cc-row { display: flex; flex-wrap: wrap; gap: 8px; }
.cc-row--cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
.cc-opt { position: relative; display: inline-flex; align-items: center; min-height: 44px; padding: 8px 16px; border: 1.5px solid var(--gb-silt); border-radius: 999px; background: #fff; cursor: pointer; font-size: .95rem; transition: border-color .15s ease, background-color .15s ease; }
.cc-row--cards .cc-opt { border-radius: 12px; min-height: 54px; font-family: var(--gb-display); font-weight: 600; font-size: 1.02rem; }
.cc-opt:hover { border-color: var(--gb-leaf); }
.cc-opt input { position: absolute; opacity: 0; width: 1px; height: 1px; }
.cc-opt:focus-within { outline: 3px solid var(--gb-sun); outline-offset: 2px; }
.cc-opt.on { background: var(--gb-paddy); border-color: var(--gb-paddy); color: #fff; }
.cc-opt:has(input:disabled) { cursor: not-allowed; opacity: .7; }
</style>
