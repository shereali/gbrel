import test from 'node:test'
import assert from 'node:assert/strict'
import { normalizePhone, validPhone, safeBrochureUrl, savePropertyInquiry } from '../utils/propertyInquiry.mjs'

test('normalizes Bangladeshi local, Bengali-digit, international and NRB numbers', () => {
  for (const number of ['01712345678', '০১৭১২৩৪৫৬৭৮', '8801712345678', '+880 1712-345678', '008801712345678']) {
    assert.equal(normalizePhone(number), '+8801712345678')
    assert.equal(validPhone(number), true)
  }
  assert.equal(validPhone('+44 7700 900123'), true)
  for (const number of ['017', '11111111111', 'hello', '+8801212345678', '+88017123456789', '017123456789', '+1234567890123456']) assert.equal(validPhone(number), false)
})

test('placeholder and unsafe brochures are never presented as documents', () => {
  for (const url of ['', 'javascript:alert(1)', 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf', 'https://example.com/plan.pdf']) assert.equal(safeBrochureUrl(url), '')
  assert.equal(safeBrochureUrl('/storage/plan.pdf'), 'https://gbrel.com/storage/plan.pdf')
})

test('only a persisted server ID can confirm a lead', async () => {
  const payload = { name: 'Test Buyer', budget_range: 'Listed price fits budget', utm_campaign: 'test' }
  const id = await savePropertyInquiry('/api/leads', payload, async (url, options) => {
    assert.equal(url, '/api/leads'); assert.deepEqual(JSON.parse(options.body), payload)
    return { ok: true, json: async () => ({ success: true, data: { id: 17 } }) }
  })
  assert.equal(id, 17)
  for (const response of [
    { ok: false, json: async () => ({ success: true, data: { id: 17 } }) },
    { ok: true, json: async () => ({ success: false }) },
    { ok: true, json: async () => ({ success: true }) },
    { ok: true, json: async () => { throw new Error('HTML response') } },
  ]) await assert.rejects(savePropertyInquiry('/api/leads', payload, async () => response))
  await assert.rejects(savePropertyInquiry('/api/leads', payload, async () => { throw new Error('Offline') }))
})
