<template>
  <section v-if="groups.length" id="property-buyer-details" class="public-buyer-details" lang="bn">
    <p class="kicker">সিদ্ধান্তের প্রয়োজনীয় তথ্য</p><h2>মূল্য, মালিকানা ও ক্রয়ের শর্ত</h2>
    <p class="source-note">বিক্রেতা / প্রতিনিধির দেওয়া তথ্য। মূল নথির সঙ্গে মিলিয়ে নিশ্চিত করুন। ফাঁকা তথ্য এখানে দেখানো হয়নি।</p>
    <div v-if="!property.hidePrice && total" class="total-price"><span>সম্পূর্ণ প্রপার্টির চাওয়া মূল্য</span><strong>{{ priceBn(total) }}</strong><span v-if="property.buyerDetails?.priceBasis === 'Per land unit'">{{ toBn(property.landSize) }} {{ unitLabels[property.landUnit] || property.landUnit }} × {{ priceBn(unitPrice || property.price) }}</span><span v-else-if="property.buyerDetails?.priceBasis === 'Per sqft'">{{ toBn(property.squareFootage) }} বর্গফুট × {{ priceBn(unitPrice || property.price) }}</span><small v-if="property.priceUnit">{{ property.priceUnit }}</small><small>অতিরিক্ত খরচ আলাদা। কোনো খরচ উল্লেখ না থাকলে তা মূল্যের অন্তর্ভুক্ত ধরে নেবেন না।</small></div>
    <details v-for="group in groups" :key="group.key" :open="group.key === 'terms' || group.key === 'building'">
      <summary>{{ group.bn }}</summary>
      <dl><div v-for="field in group.fields" :key="field.key"><dt>{{ field.bn }}</dt><dd>{{ field.display ?? field.value }}</dd></div></dl>
    </details>
    <button type="button" class="buyer-inquiry-btn" @click="$emit('inquire')">{{ ctaLabel || 'ক্রয় তথ্য ও সাইট ভিজিট' }}</button>
  </section>
</template>
<script setup lang="ts">
import { detailGroups, totalAskingPrice, unitAskingPrice } from '~/utils/buyerDetails.mjs'
import { priceBn, toBn, unitLabels } from '~/utils/propertyLabels'
const props = defineProps<{ property: any; ctaLabel?: string }>()
defineEmits(['inquire'])
const groups = computed(() => detailGroups(props.property))
const total = computed(() => totalAskingPrice(props.property))
const unitPrice = computed(() => unitAskingPrice(props.property))
</script>
<style scoped>
.public-buyer-details{padding:32px 0;scroll-margin-top:100px;min-width:0}.kicker{color:#16654d;font-size:12px;font-weight:700}.public-buyer-details h2{font-size:25px;margin:8px 0 12px}.source-note{color:#64736a;line-height:1.8;font-size:14px}.total-price{background:#eff7f1;border:1px solid #d8e7dd;border-radius:14px;padding:20px;margin:20px 0;display:grid;gap:6px}.total-price strong{font-size:28px;color:#174d3b}.total-price small{line-height:1.7;color:#506259}details{border-bottom:1px solid #dce6df}summary{padding:20px 4px;min-height:48px;box-sizing:border-box;cursor:pointer;font-weight:700}dl{margin:0 0 20px}dl>div{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.3fr);gap:16px;padding:12px 4px;border-top:1px solid #edf1ee;line-height:1.8}dt{color:#63736a}dd{margin:0;white-space:pre-line;overflow-wrap:anywhere}
.buyer-inquiry-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:13px 26px;min-height:50px;border:0;border-radius:999px;background:linear-gradient(135deg,#E2651C 0%,#C2530F 100%);color:#fff;font-family:'Anek Bangla','Noto Sans Bengali',sans-serif;font-size:16px;font-weight:700;margin-top:24px;cursor:pointer;box-shadow:0 8px 20px -6px rgba(226,101,28,.45);transition:transform .15s ease,box-shadow .15s ease,background .15s ease}
.buyer-inquiry-btn:hover{background:#C2530F;box-shadow:0 10px 24px -4px rgba(194,83,15,.55)}
.buyer-inquiry-btn:active{transform:scale(.98)}
summary:focus-visible,.buyer-inquiry-btn:focus-visible{outline:3px solid #E2651C;outline-offset:3px}
@media(max-width:600px){dl>div{grid-template-columns:1fr;gap:3px}.public-buyer-details h2{font-size:22px}.buyer-inquiry-btn{width:100%;min-height:50px;font-size:16px}}
</style>
