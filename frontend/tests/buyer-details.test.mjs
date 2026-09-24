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
  const property = { buyerDetails: { buyerCommission: 0, ownerCount: 3, bankLoan: '', ownerNid: 'must not render' } }
  const fields = detailGroups(property).flatMap(g => g.fields)
  assert.deepEqual(fields.map(f => f.key), ['buyerCommission', 'ownerCount'])
  assert.equal(fields[0].value, 0)
  assert.deepEqual(detailGroups({ ...property, hidePrice: true }).map(g => g.key), ['ownership'])
  assert.deepEqual(detailGroups({ buyerDetails: {} }), [])
})
