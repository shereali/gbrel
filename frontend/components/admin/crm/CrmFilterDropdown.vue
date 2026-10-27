<template>
  <div
    ref="rootRef"
    class="cf-dropdown"
    :class="[
      `align-${align}`,
      { 'is-open': isOpen, 'is-active': isActive }
    ]"
  >
    <button
      type="button"
      class="cf-trigger"
      :class="{ 'has-icon': !!icon }"
      :aria-expanded="isOpen"
      :aria-label="ariaLabel || placeholder"
      @click="toggleOpen"
    >
      <component :is="icon" v-if="icon" class="cf-icon" :size="15" aria-hidden="true" />
      <span class="cf-label" :title="displayLabel">{{ displayLabel }}</span>
      <span v-if="displayCount !== null && displayCount !== undefined" class="cf-badge">
        {{ displayCount }}
      </span>
      <div class="cf-arrow">
        <ChevronDown class="cf-chevron" :size="14" aria-hidden="true" />
      </div>
    </button>

    <transition name="cf-pop">
      <div v-if="isOpen" ref="menuRef" class="cf-menu" :style="menuStyle" role="listbox">
        <template v-for="(item, idx) in options" :key="idx">
          <!-- Grouped Options -->
          <div v-if="isGrouped(item)" class="cf-group">
            <div class="cf-group-label">{{ item.label }}</div>
            <button
              v-for="opt in item.options"
              :key="String(opt.value)"
              type="button"
              class="cf-item"
              :class="{ 'is-selected': isSelected(opt.value), 'is-disabled': opt.disabled }"
              :disabled="opt.disabled"
              @click="selectOption(opt)"
            >
              <div class="cf-item-left">
                <Check v-if="isSelected(opt.value)" class="cf-check" :size="14" aria-hidden="true" />
                <span v-else class="cf-check-spacer" aria-hidden="true" />
                <span class="cf-item-text">{{ opt.label || opt.text || opt.value }}</span>
              </div>
              <span v-if="opt.count !== undefined" class="cf-item-count">{{ opt.count }}</span>
            </button>
          </div>

          <!-- Flat Option -->
          <button
            v-else
            type="button"
            class="cf-item"
            :class="{ 'is-selected': isSelected(item.value), 'is-disabled': item.disabled }"
            :disabled="item.disabled"
            @click="selectOption(item)"
          >
            <div class="cf-item-left">
              <Check v-if="isSelected(item.value)" class="cf-check" :size="14" aria-hidden="true" />
              <span v-else class="cf-check-spacer" aria-hidden="true" />
              <div class="cf-item-titles">
                <span class="cf-item-text">{{ item.label || item.text || item.value }}</span>
                <span v-if="item.subtext" class="cf-item-sub">{{ item.subtext }}</span>
              </div>
            </div>
            <span v-if="item.count !== undefined" class="cf-item-count">{{ item.count }}</span>
          </button>
        </template>

        <div v-if="!allFlatOptions.length" class="cf-empty">
          {{ emptyText || 'No options available' }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { ChevronDown, Check } from 'lucide-vue-next'

export interface DropdownOption {
  value: string | number
  label?: string
  text?: string
  count?: number
  subtext?: string
  disabled?: boolean
}

export interface DropdownGroup {
  label: string
  options: DropdownOption[]
}

const props = withDefaults(
  defineProps<{
    modelValue: string | number | null
    options: (DropdownOption | DropdownGroup)[]
    placeholder?: string
    defaultValue?: string | number | null
    align?: 'left' | 'right' | 'center'
    ariaLabel?: string
    icon?: any
    emptyText?: string
  }>(),
  {
    placeholder: 'Select...',
    defaultValue: 'all',
    align: 'left',
    emptyText: 'No options'
  }
)

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
  (e: 'change', value: string | number): void
}>()

const rootRef = ref<HTMLElement | null>(null)
const isOpen = ref(false)

const isGrouped = (item: DropdownOption | DropdownGroup): item is DropdownGroup => {
  return 'options' in item && Array.isArray((item as any).options)
}

const allFlatOptions = computed<DropdownOption[]>(() => {
  const result: DropdownOption[] = []
  for (const item of props.options) {
    if (isGrouped(item)) {
      result.push(...item.options)
    } else {
      result.push(item)
    }
  }
  return result
})

const selectedOption = computed(() => {
  return allFlatOptions.value.find(o => String(o.value) === String(props.modelValue))
})

const displayLabel = computed(() => {
  if (selectedOption.value) {
    return selectedOption.value.label || selectedOption.value.text || String(selectedOption.value.value)
  }
  return props.placeholder
})

const displayCount = computed(() => {
  return selectedOption.value?.count ?? null
})

const isActive = computed(() => {
  if (props.defaultValue === undefined) {
    return props.modelValue !== '' && props.modelValue !== null
  }
  return String(props.modelValue) !== String(props.defaultValue)
})

const isSelected = (val: string | number) => {
  return String(props.modelValue) === String(val)
}

const menuRef = ref<HTMLElement | null>(null)
const menuStyle = ref<Record<string, string>>({})

const updatePosition = () => {
  if (!rootRef.value) return
  const vw = window.innerWidth
  if (vw > 900) {
    menuStyle.value = {}
    return
  }

  const triggerRect = rootRef.value.getBoundingClientRect()
  const triggerCenter = triggerRect.left + triggerRect.width / 2
  const padding = 10

  if (triggerCenter < vw * 0.35) {
    // Left column: align left with trigger, clamp max-width to right screen edge
    const maxW = Math.min(320, Math.floor(vw - triggerRect.left - padding))
    menuStyle.value = {
      left: '0',
      right: 'auto',
      maxWidth: `${maxW}px`
    }
  } else if (triggerCenter > vw * 0.65) {
    // Right column: align right with trigger, clamp max-width to left screen edge
    const maxW = Math.min(320, Math.floor(triggerRect.right - padding))
    menuStyle.value = {
      right: '0',
      left: 'auto',
      maxWidth: `${maxW}px`
    }
  } else {
    // Middle column: center horizontally under trigger
    const halfAvail = Math.min(triggerCenter - padding, vw - padding - triggerCenter)
    const maxW = Math.min(320, Math.floor(halfAvail * 2))
    menuStyle.value = {
      left: '50%',
      right: 'auto',
      transform: 'translateX(-50%)',
      maxWidth: `${maxW}px`
    }
  }

  // Double check after render to guarantee zero clipping
  nextTick(() => {
    if (!menuRef.value || !rootRef.value) return
    const menuRect = menuRef.value.getBoundingClientRect()
    if (menuRect.left < padding) {
      const shift = Math.floor(padding - menuRect.left)
      menuStyle.value = {
        ...menuStyle.value,
        left: `${shift}px`,
        right: 'auto',
        transform: 'none'
      }
    } else if (menuRect.right > vw - padding) {
      const shift = Math.floor(menuRect.right - (vw - padding))
      menuStyle.value = {
        ...menuStyle.value,
        right: `${shift}px`,
        left: 'auto',
        transform: 'none'
      }
    }
  })
}

const toggleOpen = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    updatePosition()
  }
}

const selectOption = (opt: DropdownOption) => {
  if (opt.disabled) return
  emit('update:modelValue', opt.value)
  emit('change', opt.value)
  isOpen.value = false
}

const handlePointerDown = (e: MouseEvent) => {
  if (!isOpen.value) return
  if (rootRef.value && !rootRef.value.contains(e.target as Node)) {
    isOpen.value = false
  }
}

const handleKeyDown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && isOpen.value) {
    isOpen.value = false
  }
}

const handleScrollOrResize = () => {
  if (isOpen.value && window.innerWidth <= 900) {
    updatePosition()
  }
}

onMounted(() => {
  window.addEventListener('pointerdown', handlePointerDown)
  window.addEventListener('keydown', handleKeyDown)
  window.addEventListener('resize', handleScrollOrResize)
})

onBeforeUnmount(() => {
  window.removeEventListener('pointerdown', handlePointerDown)
  window.removeEventListener('keydown', handleKeyDown)
  window.removeEventListener('resize', handleScrollOrResize)
})
</script>

<style scoped>
.cf-dropdown {
  position: relative;
  width: 100%;
  min-width: 0;
}

.cf-dropdown.is-open {
  z-index: 120;
}

/* Trigger Button */
.cf-trigger {
  width: 100%;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 10px;
  border: 1px solid var(--admin-border-subtle);
  background: var(--admin-bg-surface);
  color: var(--admin-text-primary);
  font: inherit;
  font-size: 0.84rem;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  outline: none;
  transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}

.cf-trigger:hover {
  border-color: var(--admin-border-hover);
  background: var(--admin-bg-surface-alt);
}

/* Active filter state (when value is not default) */
.cf-dropdown.is-active .cf-trigger {
  border-color: var(--admin-text-gold, #D9A21B);
  background: rgba(251, 191, 36, 0.1);
  color: var(--admin-text-primary);
}

/* Open menu state */
.cf-dropdown.is-open .cf-trigger {
  border-color: var(--admin-text-emerald, #0B7A58);
  box-shadow: 0 0 0 2px rgba(11, 122, 88, 0.2);
}

.cf-trigger:focus-visible {
  outline: 2px solid var(--admin-text-gold);
  outline-offset: 2px;
}

.cf-icon {
  flex: none;
  color: var(--admin-text-secondary);
  opacity: 0.85;
}

.cf-dropdown.is-active .cf-icon {
  color: var(--admin-text-gold);
  opacity: 1;
}

.cf-label {
  flex: 1 1 auto;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.cf-badge {
  flex: none;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 999px;
  background: var(--admin-chip-bg);
  color: var(--admin-text-secondary);
}

.cf-dropdown.is-active .cf-badge {
  background: rgba(251, 191, 36, 0.25);
  color: var(--admin-text-primary);
}

.cf-arrow {
  flex: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cf-chevron {
  opacity: 0.65;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.cf-dropdown.is-open .cf-chevron {
  transform: rotate(180deg);
  opacity: 1;
  color: var(--admin-text-emerald);
}

/* Menu Popover */
.cf-menu {
  position: absolute;
  top: calc(100% + 6px);
  z-index: 100;
  min-width: 100%;
  width: max-content;
  max-width: min(320px, 92vw);
  max-height: 290px;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: thin;
  background: var(--admin-bg-surface);
  border: 1px solid var(--admin-border-hover);
  border-radius: 12px;
  padding: 6px;
  box-shadow: 0 16px 36px -4px rgba(0, 0, 0, 0.35), 0 6px 14px rgba(0, 0, 0, 0.18);
  backdrop-filter: blur(8px);
}

.cf-menu::-webkit-scrollbar {
  width: 5px;
}
.cf-menu::-webkit-scrollbar-thumb {
  background: var(--admin-border-hover);
  border-radius: 10px;
}

/* Alignment */
.align-left .cf-menu {
  left: 0;
  right: auto;
}
.align-right .cf-menu {
  right: 0;
  left: auto;
}
.align-center .cf-menu {
  left: 50%;
  transform: translateX(-50%);
}

/* Menu Items */
.cf-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  width: 100%;
  padding: 8px 10px;
  border-radius: 8px;
  border: 0;
  background: transparent;
  color: var(--admin-text-primary);
  font: inherit;
  font-size: 0.83rem;
  font-weight: 500;
  text-align: left;
  cursor: pointer;
  transition: background 0.12s ease;
}

.cf-item:hover:not(.is-disabled) {
  background: var(--admin-table-row-hover, rgba(255, 255, 255, 0.06));
}

.cf-item.is-selected {
  background: rgba(11, 122, 88, 0.14);
  color: var(--admin-text-emerald);
  font-weight: 700;
}

.cf-item.is-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.cf-item-left {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
  flex: 1 1 auto;
}

.cf-check {
  flex: none;
  color: var(--admin-text-emerald);
}

.cf-check-spacer {
  flex: none;
  width: 14px;
  height: 14px;
}

.cf-item-titles {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.cf-item-text {
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.cf-item-sub {
  font-size: 0.72rem;
  color: var(--admin-text-muted);
  font-weight: 400;
  line-height: 1.2;
}

.cf-item-count {
  flex: none;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 999px;
  background: var(--admin-chip-bg);
  color: var(--admin-text-secondary);
}

.cf-item.is-selected .cf-item-count {
  background: rgba(11, 122, 88, 0.25);
  color: var(--admin-text-emerald);
}

/* Groups */
.cf-group {
  margin-bottom: 6px;
  padding-bottom: 4px;
  border-bottom: 1px dashed var(--admin-border-subtle);
}

.cf-group:last-child {
  margin-bottom: 0;
  padding-bottom: 0;
  border-bottom: 0;
}

.cf-group-label {
  padding: 6px 10px 4px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--admin-text-muted);
}

.cf-empty {
  padding: 16px 12px;
  text-align: center;
  font-size: 0.8rem;
  color: var(--admin-text-muted);
}

/* Popover Transition */
.cf-pop-enter-active,
.cf-pop-leave-active {
  transition: opacity 0.16s cubic-bezier(0.16, 1, 0.3, 1), transform 0.16s cubic-bezier(0.16, 1, 0.3, 1);
}

.cf-pop-enter-from,
.cf-pop-leave-to {
  opacity: 0;
  transform: translateY(-4px) scale(0.98);
}

/* Mobile responsive */
@media (max-width: 900px) {
  .cf-trigger {
    min-height: 38px;
    padding: 4px 8px;
    font-size: 0.78rem;
    gap: 4px;
  }
  .cf-icon {
    display: none;
  }
  .cf-badge {
    padding: 0 5px;
    font-size: 0.68rem;
  }
  .cf-chevron {
    width: 12px;
    height: 12px;
  }
}
</style>
