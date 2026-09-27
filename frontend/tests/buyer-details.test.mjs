import test from 'node:test'
import assert from 'node:assert/strict'
import { totalAskingPrice, askingPriceSummary, detailGroups } from '../utils/buyerDetails.mjs'

test('per-unit pricing calculates the full property price without multiplying totals or shares', () => {
  const property = { price: 140000000, landSize: 31, buyerDetails: { priceBasis: 'Per land unit' } }
  assert.equal(totalAskingPrice(property), 4340000000)
  assert.equal(askingPriceSummary(property).amount, 4340000000)
  assert.equal(totalAskingPrice({ ...property, buyerDetails: { priceBasis: 'Total' } }), 140000000)
  assert.equal(totalAskingPrice({ ...property, buyerDetails: { priceBasis: 'Per share' } }), null)
  assert.equal(totalAskingPrice({ ...property, landSize: 0 }), null)
  assert.equal(totalAskingPrice({ price: 100, buyerDetails: {} }), null)
})
test('public facts preserve zero, omit unknowns and hide cost details for confidential listings', () => {
  const property = { buyerDetails: { depositPercent: 0, ownerCount: 3, bankLoan: '', ownerNid: 'must not render' } }
  const fields = detailGroups(property).flatMap(g => g.fields)
  assert.deepEqual(fields.map(f => f.key), ['depositPercent', 'ownerCount'])
  assert.equal(fields[0].value, 0)
  assert.deepEqual(detailGroups({ ...property, hidePrice: true }).map(g => g.key), ['ownership'])
  assert.deepEqual(detailGroups({ buyerDetails: {} }), [])
})
test('public facts are shown with Bangla digits, units and dates', () => {
  const property = { buyerDetails: { depositPercent: 30, ownerCount: 2, updatedOn: '2026-09-15', paymentMethod: 'ব্যাংক ড্রাফট' } }
  const shown = Object.fromEntries(detailGroups(property).flatMap(g => g.fields).map(f => [f.key, f.display]))
  assert.equal(shown.depositPercent, '৩০%')
  assert.equal(shown.ownerCount, '২ জন')
  assert.equal(shown.updatedOn, '১৫ সেপ্টেম্বর ২০২৬')
  assert.equal(shown.paymentMethod, 'ব্যাংক ড্রাফট')
})
test('impossible dates and non-numbers are left out instead of shown wrong', () => {
  const property = { buyerDetails: { updatedOn: '2026-02-31', sourceDate: '2026-09-15T10:00:00Z', ownerCount: 'Infinity', roadWidth: '0x10', depositPercent: '12.5' } }
  const shown = Object.fromEntries(detailGroups(property).flatMap(g => g.fields).map(f => [f.key, f.display]))
  assert.equal(shown.updatedOn, undefined)
  assert.equal(shown.sourceDate, '১৫ সেপ্টেম্বর ২০২৬')
  assert.equal(shown.ownerCount, undefined)
  assert.equal(shown.roadWidth, undefined)
  assert.equal(shown.depositPercent, '১২.৫%')
})
