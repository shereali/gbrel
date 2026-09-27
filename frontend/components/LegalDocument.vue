<template>
  <div class="ld">
    <header class="gb-wrap ld-head">
      <h1>{{ title }}</h1>
      <p class="ld-meta">সর্বশেষ হালনাগাদ: {{ updated }}</p>
      <p v-if="intro" class="gb-lead">{{ intro }}</p>
    </header>

    <div class="gb-wrap ld-body">
      <details class="ld-toc" :open="tocOpen" aria-label="এই পেজে যা আছে">
        <summary>এই পেজে</summary>
        <ol>
          <li v-for="(section, i) in sections" :key="section.id"><a :href="`#${section.id}`" :class="{ on: activeId === section.id }">{{ toBn(i + 1) }}. {{ section.title }}</a></li>
        </ol>
        <div v-if="normalizedOtherLinks.length" class="ld-other-links">
          <NuxtLink v-for="link in normalizedOtherLinks" :key="link.to" :to="link.to" class="ld-other">{{ link.label }}</NuxtLink>
        </div>
      </details>

      <article class="ld-article">
        <aside class="ld-short" aria-label="সংক্ষেপে">
          <h2>সংক্ষেপে</h2>
          <ul>
            <li v-for="point in summary" :key="point">{{ point }}</li>
          </ul>
        </aside>

        <section v-for="(section, i) in sections" :id="section.id" :key="section.id" class="ld-section">
          <h2><span>{{ toBn(i + 1) }}.</span> {{ section.title }}</h2>
          <template v-for="(block, j) in section.body" :key="j">
            <p v-if="typeof block === 'string'">{{ block }}</p>
            <template v-else>
              <h3 v-if="block.heading">{{ block.heading }}</h3>
              <ul v-if="block.list">
                <li v-for="item in block.list" :key="item">{{ item }}</li>
              </ul>
              <p v-if="block.note" class="ld-note">{{ block.note }}</p>
            </template>
          </template>
        </section>

        <section class="ld-contact" aria-label="যোগাযোগ">
          <h2>প্রশ্ন থাকলে</h2>
          <p>{{ companyName }}</p>
          <address>{{ address }}</address>
          <p class="ld-contact-ways">
            <a v-if="phone" :href="`tel:${phone.replace(/[^\d+]/g, '')}`">{{ phone }}</a>
            <a v-if="email" :href="`mailto:${email}`">{{ email }}</a>
            <NuxtLink to="/contact">যোগাযোগ পেজ</NuxtLink>
          </p>
        </section>
      </article>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { toBn } from '~/utils/propertyLabels'

type LegalBlock = string | { heading?: string; list?: string[]; note?: string }
interface LegalSection { id: string; title: string; body: LegalBlock[] }

const props = defineProps<{
  title: string
  updated: string
  intro?: string
  summary: string[]
  sections: LegalSection[]
  companyName: string
  address: string
  phone?: string
  email?: string
  otherLink?: { to: string; label: string }
  otherLinks?: Array<{ to: string; label: string }>
}>()

const normalizedOtherLinks = computed(() => {
  if (Array.isArray(props.otherLinks) && props.otherLinks.length) return props.otherLinks
  if (props.otherLink) return [props.otherLink]
  return []
})

// Highlight the section being read in the table of contents.
const activeId = ref('')
// Open beside the text on wide screens; folded on phones so the summary comes first.
const tocOpen = ref(true)
let observer: IntersectionObserver | null = null
onMounted(() => {
  tocOpen.value = window.matchMedia('(min-width: 901px)').matches
  if (typeof IntersectionObserver === 'undefined') return
  observer = new IntersectionObserver(entries => {
    const visible = entries.filter(e => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0]
    if (visible) activeId.value = visible.target.id
  }, { rootMargin: '-20% 0px -65% 0px' })
  props.sections.forEach(s => { const el = document.getElementById(s.id); if (el) observer?.observe(el) })
})
onBeforeUnmount(() => observer?.disconnect())
</script>

<style scoped>
.ld { padding: clamp(40px, 7vw, 80px) 0 96px; }
.ld-head { margin-bottom: clamp(28px, 5vw, 48px); }
.ld-head > * { max-width: 900px; }
.ld-head h1 { font-size: var(--gb-t-h1); font-weight: 800; font-stretch: 112%; color: var(--gb-paddy); line-height: 1.15; }
.ld-meta { margin: 10px 0 14px; color: var(--gb-ink-soft); font-size: var(--gb-t-small); }
.ld-head .gb-lead { max-width: 68ch; }
.ld-body { display: grid; grid-template-columns: 250px minmax(0, 1fr); gap: clamp(28px, 5vw, 72px); align-items: start; }
.ld-toc { position: sticky; top: 110px; font-size: .92rem; }
.ld-toc > summary { font-weight: 700; color: var(--gb-paddy); margin-bottom: 10px; cursor: pointer; list-style: none; }
.ld-toc > summary::-webkit-details-marker { display: none; }
.ld-toc > summary::after { content: ' ▾'; color: var(--gb-leaf); }
.ld-toc[open] > summary::after { content: ''; }
.ld-toc ol { list-style: none; margin: 0; padding: 0; border-left: 2px solid var(--gb-silt); }
.ld-toc a { display: block; padding: 6px 0 6px 14px; margin-left: -2px; border-left: 2px solid transparent; color: var(--gb-ink-soft); line-height: 1.45; }
.ld-toc a:hover { color: var(--gb-paddy); }
.ld-toc a.on { color: var(--gb-paddy); font-weight: 700; border-left-color: var(--gb-sun); }
.ld-other-links { display: flex; flex-direction: column; gap: 8px; margin-top: 18px; }
.ld-other { display: inline-block; margin-top: 18px; color: var(--gb-leaf); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
.ld-other-links .ld-other { margin-top: 0; }
.ld-article { max-width: 72ch; }

/* The plain-language summary is the page's one loud element: most people read only this. */
.ld-short { background: var(--gb-paddy); color: #EEF3E3; border-radius: var(--gb-r-lg); padding: clamp(22px, 4vw, 32px); margin-bottom: 40px; }
.ld-short h2 { font-family: var(--gb-display); font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 12px; }
.ld-short ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 10px; }
.ld-short li { position: relative; padding-left: 26px; line-height: 1.7; font-size: 1.02rem; }
.ld-short li::before { content: ''; position: absolute; left: 2px; top: .62em; width: 10px; height: 10px; border-radius: 50%; background: var(--gb-shoot); }

.ld-section { padding: 28px 0; border-top: 1px solid var(--gb-silt); scroll-margin-top: 100px; }
.ld-section h2 { font-family: var(--gb-display); font-size: 1.45rem; font-weight: 700; color: var(--gb-paddy); margin-bottom: 14px; line-height: 1.3; }
.ld-section h2 span { color: var(--gb-sun); margin-right: 4px; }
.ld-section h3 { font-size: 1.05rem; font-weight: 700; color: var(--gb-ink); margin: 18px 0 8px; }
.ld-section p, .ld-section li { line-height: 1.85; color: var(--gb-ink); }
.ld-section p + p { margin-top: 10px; }
.ld-section ul { margin: 6px 0 4px; padding-left: 22px; display: grid; gap: 6px; }
.ld-section li::marker { color: var(--gb-leaf); }
.ld-note { margin-top: 12px; padding: 12px 16px; border-left: 3px solid var(--gb-sun); background: var(--gb-sheet); border-radius: 0 var(--gb-r-sm) var(--gb-r-sm) 0; }
.ld-contact { margin-top: 12px; padding: 24px; border: 1px solid var(--gb-silt); border-radius: var(--gb-r-lg); background: var(--gb-sheet); }
.ld-contact h2 { font-family: var(--gb-display); font-size: 1.3rem; font-weight: 700; color: var(--gb-paddy); margin-bottom: 8px; }
.ld-contact address { font-style: normal; color: var(--gb-ink-soft); margin: 4px 0 12px; }
.ld-contact-ways { display: flex; flex-wrap: wrap; gap: 8px 20px; }
.ld-contact-ways a { color: var(--gb-leaf); font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
.ld a:focus-visible { outline: 3px solid var(--gb-sun); outline-offset: 3px; }
@media (max-width: 900px) {
  .ld-body { grid-template-columns: minmax(0, 1fr); }
  .ld-toc > summary { margin-bottom: 0; }
  .ld-toc[open] > summary { margin-bottom: 10px; }
  .ld-toc[open] > summary::after { content: ' ▴'; }
  .ld-toc { position: static; padding: 16px 18px; border: 1px solid var(--gb-silt); border-radius: var(--gb-r); background: var(--gb-sheet); }
}
</style>
