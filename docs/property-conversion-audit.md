# Property landing page: UX research and implementation

Reviewed 23 September 2026. Primary target: https://gbrel.com/properties/7. Follow-up review: https://gbrel.com/properties/10.

## What was actually inspected

Live browser inspection at desktop and 390px mobile; the property page, editor, mapping layer, lead API and CRM source; local browser interaction; isolated backend tests; frontend production builds. These are an expert review and implementation checks, not recorded customer interviews or a conversion experiment. No conversion uplift is claimed.

The live and local databases differ. At review time live property 10 was **Dynamic Modern Triplex**, ৳9.50 crore, 5,200 sq ft, classified as **Duplex**. Local property 10 was a land-share listing. Live observations are not interchangeable with local fixtures.

## Findings and fixes

| Problem observed | Buyer / business consequence | Implementation |
| --- | --- | --- |
| Mobile first screen was dominated by badges and jargon; image was below the initial viewport | Hard to understand the property quickly after an ad click | Compact headline, location, price basis, actual listing images and readable facts |
| Multiple floating widgets competed with the mobile contact bar | Distraction and covered content | One mobile inquiry CTA; property-specific sidebar; global hunter widget excluded from detail routes |
| “Institutional mandate”, “private dossier”, “VIP” and English-heavy form | Buyers must decode the offer before expressing interest | Bengali benefit-led copy and clear next action |
| Buyer category and cash readiness were selected by default | Inflated lead quality and poor follow-up context | Explicit purpose, timing and price-fit selections; no preselected readiness |
| Requests looked successful even after network/server failure | Lost inquiries and falsely reported Meta leads | Success requires HTTP success, `success: true` and a saved server ID; failure retains form answers |
| Brochure linked to a W3C dummy PDF | Broken credibility and misleading value exchange | Known placeholder URLs excluded; real brochures remain public; no fake instant-delivery promise |
| Gallery inserted extra stock architecture images | Images could imply features not supplied for the property | Only listing-provided images are used; missing-image state |
| Display helpers invented facing, handover year, amenities and document claims | Unknown facts looked verified | Strict detail loading/mapping avoids these substitutions |
| Detail tabs synthesized ROI and history | Unsubstantiated financial expectations | New detail page uses property facts, cost questions, document list and location; synthesized panels are not used |
| Unknown property/API failure could fall back to a different seeded listing | A visitor could inquire about the wrong property | Strict detail fetch, error state and retry |
| Desktop and mobile had separate forms | Different qualification and success behavior | One shared, accessible two-step form |
| No contact-specific consent; excessive identity categories | Unclear callback expectation | Explicit unchecked consent; no occupation, citizenship or income questions |
| Lead API had no validation or access checks | Garbage leads and exposed contact records | Server validation, lead read/write authorization, stats redaction, throttling, consent timestamp and retry idempotency |
| Editor omitted facing and construction status on existing-property saves | Admin changes did not reach the listing | Load/save wiring repaired; additional decision-detail fields exposed |

## Why this form

The offer is an informed property discussion: total costs, payment conditions, available documents and a possible site visit. The page does not promise a discount, legal verification, immediate callback or downloadable document that the business has not supplied.

Step 1 asks three questions: intended use, buying timeframe and whether the displayed price fits. Step 2 asks name, phone, preferred contact channel and consent. An optional disclosure contains callback time and the topic to discuss first. Email and open-ended essays are unnecessary for this callback workflow.

| Captured information | Use in follow-up |
| --- | --- |
| Property ID and server-derived title | Identify the exact listing without trusting a client-supplied name |
| Own/family use or investment | Begin with the buyer's stated purpose |
| 30 days, 1–3 months, 3–6 months, or researching | Agree on a suitable next step |
| Price fits, financing discussion needed, below price, or costs unclear | Discuss affordability without demanding income details |
| Name and normalized phone | Contact the person; Bengali digits and international numbers supported |
| Phone or WhatsApp preference | Respect the requested channel |
| Optional time and first topic | Reduce unwanted calls and repetitive questions |
| Consent timestamp and form version | Record permission for this inquiry |
| UTM source, medium, campaign, content, term and CTA | Associate actual saved inquiries with campaigns |

The CRM's “Ready within 3 months” filter means the buyer explicitly selected a timeframe within three months and said the listed price fits. It is **self-reported readiness**, not proof of funds, identity, affordability or a guaranteed purchase. Other inquiries remain available. The sales team should confirm budget, total-cost understanding and willingness to visit during the call.

## Research basis

NN/g recommends nearby, persistent labels, clear required/optional fields and reducing unnecessary work. This informed visible labels, explicit required markers, and removal of irrelevant identity questions. [Website Forms Usability](https://www.nngroup.com/articles/web-form-design/), [Marking Required Fields](https://www.nngroup.com/articles/required-fields/).

Secondary preferences are disclosed only when needed. This follows the principle of keeping an initial interface focused while leaving further options available. [Progressive Disclosure](https://www.nngroup.com/articles/progressive-disclosure/).

Native inputs, stable field names, `autocomplete`, telephone input mode and appropriately sized controls reduce mobile entry effort. [web.dev: Avoid re-entering form data](https://web.dev/learn/forms/auto).

Production performance should be measured with real mobile traffic, separately from desktop. Target good Core Web Vitals at the 75th percentile: LCP ≤2.5s, INP ≤200ms, CLS ≤0.1. These targets have **not** been established as achieved by this implementation. [web.dev: Core Web Vitals thresholds](https://web.dev/articles/defining-core-web-vitals-thresholds).

## Admin editability

Property title, category, transaction type, location, price, price visibility, size, rooms, facing, construction status, tagline, description, cover/gallery and brochure are editable. Added editor controls cover price basis, city, handover/construction year, coordinates, amenities and listed documents. Clear-brochure behavior and zero-parking preservation are fixed.

The Bengali section headings, CTA wording, explanatory text, FAQs, form labels/options, qualification rule and layout remain shared application code. They are not per-property CMS fields. Agent records are maintained separately. Adding arbitrary form editing needs a defined field schema so the CRM does not lose consistent qualification values.

## Property 10 is not yet persuasive enough

The live listing has a triplex/duplex mismatch, no descriptive narrative, missing bedroom/bathroom details, no listed amenities, no available-document list and no real brochure shown. It has two images; their accuracy was not verified. A ৳9.50 crore buyer needs these gaps resolved before a persuasive campaign can be evaluated.

The owner should supply:

1. Correct property type, floor arrangement, bedrooms, bathrooms, parking, facing and floor plan.
2. Actual interior/exterior photos; a walkthrough video can be a later addition.
3. Full asking-price basis, included/excluded charges, payment schedule and current availability.
4. Ownership/approval document availability and who can explain them.
5. A specific description: what makes this property useful for its intended buyer, without unsupported superlatives.
6. Accurate location/access details and a practical site-visit process.

For property 7, resolve the listed 2025 handover/construction year versus “Upcoming Project”; supply actual site photography, a real brochure and a clear distinction between land-share price and construction/registration costs. These cannot be truthfully invented in code.

## Verification and release notes

- Phone normalization, invalid numbers, placeholder documents, network failure and non-success API response tested with Node's test runner.
- Laravel tests cover successful persistence, consent, attribution, normalized phones, idempotent retry, legacy general inquiries, invalid property IDs, rate limiting and unauthorized/suspended access.
- A synthetic local inquiry was submitted through the browser, its saved qualification/UTM/consent fields inspected, and that exact test record removed. No live inquiry was submitted.
- Browser layout measured at 320, 360, 390, 430, 768, 1024 and 1440px: no horizontal page overflow. A 320×568 modal remained within the viewport; focus trap, Escape dismissal and focus restoration were checked.
- Production build succeeded. A dependency export deprecation warning remains; it did not fail the build.
- Apply `2026_09_23_080246_add_contact_preferences_to_leads_table.php` together with the new API/form. The earlier qualification-fields migration is also required. The new migration was applied locally.
- A Meta `Lead` hook runs only after saved success. No Pixel initialization/ID existed in inspected source. Configure the actual Pixel through the site's approved analytics setup and verify Events Manager before relying on ad attribution. No personal contact fields are included in the Lead event payload.
- The site is still a client-rendered SPA. Dynamic browser metadata is not proof that social crawlers see property-specific previews; server-rendered/prerendered social metadata remains a separate deployment concern.
- **Existing platform security release concern:** routes outside the lead workflow still expose administrative mutations without equivalent authorization, and the existing login route includes developer-password fallback logic. The targeted lead protections do not constitute platform-wide hardening. Resolve those paths before collecting real customer data at scale. No live exploitation was attempted.

Evaluate the redesign with form-start/completion rate, valid-contact rate, sales-confirmed qualification rate, booked/attended site visits and cost per qualified inquiry. Test with actual customers and a bounded ad experiment; visual polish alone cannot establish conversion performance.
