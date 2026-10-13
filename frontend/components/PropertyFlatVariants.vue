<template>
  <section v-if="variants.length" id="property-flat-variants" class="flat-variants-section" lang="bn">
    <div class="variants-header">
      <div class="variants-badge">
        <span>ফ্ল্যাট ভ্যারিয়েন্ট</span>
        <span class="count-tag">{{ toBn(variants.length) }}টি সাইজ উপলব্ধ</span>
      </div>
      <h2>বিল্ডিংয়ের উপলব্ধ ফ্ল্যাট সাইজ ও লেআউট</h2>
      <p class="subtitle">আপনার পছন্দের সাইজ বেছে নিয়ে ফ্লোর প্ল্যান, বেডরুম ও মূল্যের হিসাব দেখুন।</p>
    </div>

    <!-- Variant Selector Tabs -->
    <div class="variants-tabs" role="tablist" aria-label="ফ্ল্যাট সাইজ নির্বাচন">
      <button 
        v-for="(variant, idx) in variants" 
        :key="variant.name || idx"
        type="button"
        role="tab"
        :aria-selected="selectedIndex === idx"
        class="variant-tab-btn"
        :class="{ active: selectedIndex === idx }"
        @click="selectVariant(idx)"
      >
        <span class="tab-name">{{ variant.name }}</span>
        <strong v-if="variant.sqft" class="tab-sqft">{{ toBn(variant.sqft) }} বর্গফুট</strong>
        <span v-if="variant.price" class="tab-price">{{ formatBDT(variant.price) }}</span>
      </button>
    </div>

    <!-- Active Variant Details Panel -->
    <div v-if="current" class="variant-detail-card animate-fade-in" role="tabpanel">
      <div class="variant-info-col">
        <div class="variant-title-row">
          <div>
            <h3 class="current-name">{{ current.name }}</h3>
            <span v-if="current.floor_available" class="current-floors">
              তলা / অবস্থান: <strong>{{ current.floor_available }}</strong>
            </span>
          </div>
          <div v-if="current.price" class="current-price-badge">
            <span class="price-caption">আনুমানিক মূল্য</span>
            <strong class="price-val">{{ formatBDT(current.price) }}</strong>
            <small v-if="current.price_per_sqft">প্রতি বর্গফুট ৳ {{ toBn(current.price_per_sqft) }}</small>
          </div>
        </div>

        <!-- Spec Strip -->
        <div class="variant-specs-grid">
          <div v-if="current.sqft" class="spec-cell">
            <span class="spec-label">আয়তন</span>
            <strong class="spec-val">{{ toBn(current.sqft) }} বর্গফুট</strong>
          </div>
          <div v-if="current.bedrooms" class="spec-cell">
            <span class="spec-label">বেডরুম</span>
            <strong class="spec-val">{{ toBn(current.bedrooms) }}টি</strong>
          </div>
          <div v-if="current.bathrooms" class="spec-cell">
            <span class="spec-label">বাথরুম</span>
            <strong class="spec-val">{{ toBn(current.bathrooms) }}টি</strong>
          </div>
          <div v-if="current.balconies" class="spec-cell">
            <span class="spec-label">বারান্দা</span>
            <strong class="spec-val">{{ toBn(current.balconies) }}টি</strong>
          </div>
          <div v-if="current.facing" class="spec-cell">
            <span class="spec-label">অভিমুখ</span>
            <strong class="spec-val">{{ facingLabel(current.facing) }}</strong>
          </div>
        </div>

        <div class="variant-actions">
          <button type="button" class="cta-sun variant-cta" @click="handleInquire">
            {{ current.name }}-এর জন্য কথা বলুন
          </button>
          <a 
            v-if="whatsappUrl" 
            :href="whatsappUrl" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="cta-wa variant-wa"
          >
            WhatsApp-এ জিজ্ঞেস করুন
          </a>
        </div>
      </div>

      <!-- Floor Plan Preview Col -->
      <div class="variant-media-col">
        <div v-if="current.floor_plan_image" class="floorplan-box" @click="openImage(current.floor_plan_image)">
          <img :src="current.floor_plan_image" :alt="`${current.name} ফ্লোর প্ল্যান`" loading="lazy" decoding="async" />
          <div class="floorplan-overlay">
            <span>🔍 বড় করে দেখুন</span>
          </div>
        </div>
        <div v-else class="floorplan-placeholder">
          <span>📐 এই সাইজের আর্কিটেকচারাল ফ্লোর প্ল্যান দেখতে টিমের সঙ্গে যোগাযোগ করুন।</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { toBn, facingLabel } from '~/utils/propertyLabels'
import { formatBDT } from '~/composables/useCurrency'
import { whatsappLink } from '~/utils/contact'

export interface FlatVariant {
  name: string
  sqft?: number
  bedrooms?: number
  bathrooms?: number
  balconies?: number
  price?: number
  price_per_sqft?: number
  floor_available?: string
  facing?: string
  floor_plan_image?: string
  is_available?: boolean
}

const props = defineProps<{
  variants: FlatVariant[]
  property: any
  whatsapp?: string
}>()

const emit = defineEmits<{
  (e: 'select', variant: FlatVariant): void
  (e: 'inquire', variant: FlatVariant): void
  (e: 'open-image', url: string): void
}>()

const selectedIndex = ref(0)
const current = computed<FlatVariant | null>(() => props.variants[selectedIndex.value] || props.variants[0] || null)

const selectVariant = (idx: number) => {
  selectedIndex.value = idx
  if (current.value) {
    emit('select', current.value)
  }
}

const handleInquire = () => {
  if (current.value) {
    emit('inquire', current.value)
  }
}

const openImage = (url: string) => {
  emit('open-image', url)
}

const whatsappUrl = computed(() => {
  if (!props.whatsapp || !current.value) return ''
  const propTitle = props.property?.title || 'প্রপার্টি'
  const propId = props.property?.id ? ` (GBR-${props.property.id})` : ''
  const text = `আসসালামু আলাইকুম, "${propTitle}"${propId}-এর ${current.value.name}${current.value.sqft ? ` (${toBn(current.value.sqft)} বর্গফুট)` : ''} ফ্ল্যাটটি নিয়ে বিস্তারিত জানতে চাই।`
  return whatsappLink(props.whatsapp, text)
})
</script>

<style scoped>
.flat-variants-section {
  padding: 36px 0;
  border-bottom: 1px solid #dce4dc;
  scroll-margin-top: 100px;
}
.variants-header {
  margin-bottom: 20px;
}
.variants-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #e8efe7;
  color: #1b543c;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 10px;
}
.count-tag {
  background: #1b543c;
  color: #fff;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 11px;
}
.variants-header h2 {
  font-size: 24px;
  color: #173c34;
  margin: 0 0 6px;
  line-height: 1.4;
}
.subtitle {
  font-size: 14px;
  color: #5b6f5c;
  margin: 0;
  line-height: 1.7;
}

/* Tabs */
.variants-tabs {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  padding: 4px 0 16px;
  -webkit-overflow-scrolling: touch;
}
.variant-tab-btn {
  flex: 1 0 auto;
  min-width: 140px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 3px;
  padding: 14px 18px;
  background: #fff;
  border: 1.5px solid #dce4dc;
  border-radius: 12px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s ease;
}
.variant-tab-btn:hover {
  border-color: #216d50;
  background: #f7faf7;
}
.variant-tab-btn.active {
  border-color: #165c43;
  background: #eef6f0;
  box-shadow: 0 4px 14px rgba(22, 92, 67, 0.12);
}
.tab-name {
  font-size: 13px;
  font-weight: 700;
  color: #173c34;
}
.tab-sqft {
  font-size: 15px;
  color: #165c43;
  font-family: 'Outfit', 'Noto Sans Bengali', sans-serif;
  font-weight: 700;
}
.tab-price {
  font-size: 12px;
  color: #687968;
}

/* Detail Card */
.variant-detail-card {
  background: #fff;
  border: 1px solid #dbe5db;
  border-radius: 16px;
  padding: 26px;
  display: grid;
  grid-template-columns: 1.3fr 1fr;
  gap: 28px;
  align-items: center;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
}
.variant-title-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  border-bottom: 1px solid #edf2ec;
  padding-bottom: 18px;
  margin-bottom: 18px;
}
.current-name {
  font-size: 22px;
  color: #173c34;
  margin: 0 0 4px;
}
.current-floors {
  font-size: 13px;
  color: #617364;
}
.current-floors strong {
  color: #173c34;
}
.current-price-badge {
  text-align: right;
  display: flex;
  flex-direction: column;
}
.price-caption {
  font-size: 11px;
  color: #687968;
}
.price-val {
  font-size: 22px;
  color: #1b543c;
  font-family: 'Outfit', 'Noto Sans Bengali', sans-serif;
  font-weight: 700;
}
.current-price-badge small {
  font-size: 11px;
  color: #7b8e7c;
}

/* Specs */
.variant-specs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
  gap: 12px;
  background: #f8faf7;
  border-radius: 12px;
  padding: 16px 14px;
  margin-bottom: 22px;
  border: 1px solid #eef2ec;
}
.spec-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.spec-label {
  font-size: 11px;
  color: #6b7d6d;
}
.spec-val {
  font-size: 14px;
  color: #173c34;
  font-weight: 700;
}

/* Actions */
.variant-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.variant-cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 700;
  min-height: 48px;
  padding: 12px 24px;
  border: 0;
  border-radius: 999px;
  background: #E2651C;
  color: #fff;
  cursor: pointer;
  box-shadow: 0 8px 20px -6px rgba(226, 101, 28, 0.45);
  transition: transform 0.15s ease, background 0.15s ease;
  font-family: 'Anek Bangla', 'Noto Sans Bengali', sans-serif;
}
.variant-cta:hover {
  background: #C2530F;
}
.variant-cta:active {
  transform: scale(0.98);
}
.variant-wa {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 48px;
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 600;
  border: 1.5px solid #1FA855;
  border-radius: 999px;
  color: #137A3D;
  background: #fff;
  text-decoration: none;
  transition: background 0.15s ease;
}
.variant-wa:hover {
  background: #EAF7EF;
}
.variant-wa:active {
  transform: scale(0.98);
}

/* Media Box */
.variant-media-col {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.floorplan-box {
  width: 100%;
  height: 240px;
  border-radius: 12px;
  overflow: hidden;
  background: #edf2ec;
  position: relative;
  cursor: pointer;
  border: 1px solid #dce4dc;
}
.floorplan-box img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 8px;
  transition: transform 0.25s ease;
}
.floorplan-box:hover img {
  transform: scale(1.03);
}
.floorplan-overlay {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background: rgba(14, 42, 30, 0.85);
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 999px;
  backdrop-filter: blur(4px);
}
.floorplan-placeholder {
  width: 100%;
  padding: 40px 20px;
  border: 1.5px dashed #cbd6ca;
  border-radius: 12px;
  text-align: center;
  color: #5c6e5e;
  font-size: 13px;
  line-height: 1.6;
  background: #fbfdfa;
}

@media (max-width: 768px) {
  .variant-detail-card {
    grid-template-columns: 1fr;
    padding: 16px;
    gap: 16px;
    border-radius: 14px;
  }
  .variant-title-row {
    flex-direction: column;
    gap: 8px;
    padding-bottom: 14px;
    margin-bottom: 14px;
  }
  .current-price-badge {
    text-align: left;
  }
  .variant-specs-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    padding: 12px;
  }
  .variant-actions {
    flex-direction: column;
    gap: 10px;
  }
  .variant-actions button,
  .variant-actions a {
    width: 100%;
    text-align: center;
    min-height: 48px;
  }
  .floorplan-box {
    height: 200px;
  }
}
</style>
