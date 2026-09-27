import test from 'node:test'
import assert from 'node:assert/strict'
import { telHref, whatsappLink, whatsappNumber } from '../utils/contact.mjs'

test('WhatsApp numbers typed in local format still open WhatsApp', () => {
  assert.equal(whatsappNumber('01712-345678'), '8801712345678')
  assert.equal(whatsappNumber('+880 1712 345678'), '8801712345678')
  assert.equal(whatsappNumber('০১৭১২৩৪৫৬৭৮'), '8801712345678')
  assert.equal(whatsappNumber('00447700900123'), '447700900123')
  assert.equal(whatsappNumber('8801711000000'), '')
  assert.equal(whatsappNumber('12345'), '')
  assert.equal(whatsappNumber(''), '')
  assert.equal(whatsappLink('', 'hi'), '')
  assert.equal(whatsappLink('01712345678', 'হ্যালো'), 'https://wa.me/8801712345678?text=%E0%A6%B9%E0%A7%8D%E0%A6%AF%E0%A6%BE%E0%A6%B2%E0%A7%8B')
})

test('tel links keep digits and one leading plus', () => {
  assert.equal(telHref('+880 1712-345678'), 'tel:+8801712345678')
  assert.equal(telHref('01712 345678'), 'tel:01712345678')
  assert.equal(telHref('12+34'), '')
  assert.equal(telHref(''), '')
})
