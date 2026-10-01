/*!
 * gb-track.js — visitor journey tracking for gbrel.com (first-party, no third party).
 *
 * Records per visit: ad source (UTM / fbclid / ad ids), page views (including single-page route changes),
 * scroll depth, active time, CTA / call / WhatsApp clicks, video plays, and the survey steps the site
 * already reports through trackPixel() (utils/metaPixel.ts calls GBTrack.pixelEvent).
 *
 * It runs ONLY after the visitor chose "সব গ্রহণ করুন" in the cookie banner (localStorage gbrel_cookie_consent = all),
 * never on admin, sign-in or owner pages, and never reads what people type into inputs. Name and phone are stored only when the
 * survey calls GBTrack.saveDraft() after the visitor confirmed them.
 *
 * Loaded from nuxt.config.ts (app.head.script). Set window.GB_TRACK_API (plugins/gb-track.client.ts) to the API base.
 *
 * window.GBTrack:
 *   track(name, {label, value, step, pid, meta})  log a custom step
 *   pixelEvent(pixelName, params)                 log a Meta Pixel event under its tracker name
 *   ids()                                         {vid, sid} to send with POST /leads ({} when not tracking)
 *   saveDraft({phone, name, answers, step, pid})  keep confirmed contact details for a call-back
 */
(function (w, d) {
  'use strict';
  if (w.GBTrack) return;

  var SESSION_MIN = 30;       // minutes without activity before a new visit starts
  var VISITOR_DAYS = 180;
  var FLUSH_MS = 5000;

  var api = function () { return (w.GB_TRACK_API || '/api').replace(/\/$/, ''); };

  /* ---------- consent ---------- */
  function hasConsent() {
    try { return w.localStorage.getItem('gbrel_cookie_consent') === 'all'; } catch (e) { return false; }
  }
  // Staff, sign-in and owner pages are never tracked.
  function isPrivatePage() { return /^\/(admin|login|register|signup|dashboard|my-listings)(\/|$)/.test(location.pathname); }

  /* ---------- cookies & ids ---------- */
  function getCookie(n) {
    var m = d.cookie.match('(?:^|; )' + n.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)');
    return m ? decodeURIComponent(m[1]) : null;
  }
  function setCookie(n, v, maxAgeSec) {
    d.cookie = n + '=' + encodeURIComponent(v) + '; Max-Age=' + maxAgeSec + '; Path=/; SameSite=Lax' +
      (location.protocol === 'https:' ? '; Secure' : '');
  }
  function rid() {
    if (w.crypto && w.crypto.randomUUID) return w.crypto.randomUUID().replace(/-/g, '');
    return (Date.now().toString(36) + Math.random().toString(36).slice(2) + Math.random().toString(36).slice(2)).slice(0, 32);
  }
  function params() {
    var o = {};
    try { new URLSearchParams(location.search).forEach(function (v, k) { o[k] = v; }); } catch (e) {}
    return o;
  }
  function pidFromPath() {
    var m = location.pathname.match(/\/properties\/(\d+)/);
    return m ? m[1] : null;
  }
  function readSrc(id) {
    try { return JSON.parse(w.sessionStorage.getItem('gb_src_' + id)); } catch (e) { return null; }
  }

  var enabled = false, vid, sid, src = null;

  function initIds() {
    vid = getCookie('gb_vid') || rid();
    setCookie('gb_vid', vid, VISITOR_DAYS * 86400);

    var p = params();
    sid = getCookie('gb_sid');
    var known = sid ? readSrc(sid) : null;
    // A different ad click starts a new visit, so the source is never mixed up. Reloading the same link does not.
    var newAd = (p.fbclid || p.utm_source) && (!known || known.fbclid !== (p.fbclid || undefined) ||
      known.utm_content !== (p.utm_content || undefined) || known.utm_campaign !== (p.utm_campaign || undefined));
    if (!sid || newAd) { sid = rid(); known = null; }
    touchSession();

    src = known;
    if (!src) {
      src = {
        utm_source: p.utm_source, utm_medium: p.utm_medium, utm_campaign: p.utm_campaign,
        utm_content: p.utm_content, utm_term: p.utm_term,
        fb_campaign_id: p.fb_campaign_id, fb_adset_id: p.fb_adset_id, fb_ad_id: p.fb_ad_id,
        fbclid: p.fbclid, landing_url: location.href.split('#')[0], referrer: d.referrer || null
      };
      try { w.sessionStorage.setItem('gb_src_' + sid, JSON.stringify(src)); } catch (e) {}
    }

    // _fbc from the click id when the pixel has not set it yet (format defined by Meta)
    if (p.fbclid && !getCookie('_fbc')) setCookie('_fbc', 'fb.1.' + Date.now() + '.' + p.fbclid, 90 * 86400);
  }
  function touchSession() { setCookie('gb_sid', sid, SESSION_MIN * 60); }

  /* ---------- queue & sending ---------- */
  var queue = [], sentSrc = false, timer = null;

  function push(e, data) {
    if (!enabled || isPrivatePage()) return;
    data = data || {};
    touchSession();
    queue.push({
      e: e, t: Date.now(), p: location.pathname,
      pid: data.pid || pidFromPath(),
      l: data.label != null ? String(data.label).slice(0, 190) : undefined,
      v: data.value != null ? String(data.value).slice(0, 190) : undefined,
      st: data.step != null ? data.step : undefined,
      m: data.meta || undefined
    });
    if (queue.length >= 20) flush(false);
    else if (!timer) timer = setTimeout(function () { flush(false); }, FLUSH_MS);
  }

  function flush(useBeacon) {
    if (timer) { clearTimeout(timer); timer = null; }
    if (!queue.length || !enabled) return;
    var body = JSON.stringify({
      vid: vid, sid: sid, fbc: getCookie('_fbc'), fbp: getCookie('_fbp'),
      src: sentSrc ? undefined : src, events: queue.splice(0, 50)
    });
    sentSrc = true;
    try {
      // text/plain keeps this a "simple" request: no CORS preflight, no token, works with sendBeacon
      if (useBeacon && navigator.sendBeacon) {
        navigator.sendBeacon(api() + '/t', new Blob([body], { type: 'text/plain;charset=UTF-8' }));
      } else {
        fetch(api() + '/t', { method: 'POST', headers: { 'Content-Type': 'text/plain;charset=UTF-8' }, body: body, keepalive: true });
      }
    } catch (e) {}
    if (queue.length) flush(useBeacon);
  }

  /* ---------- page views (the site is a single-page app) ---------- */
  var lastPath = null, scrollMarks = {};
  function pageView() {
    var path = location.pathname + location.search;
    if (path === lastPath) return;
    if (lastPath !== null) sendEngaged();
    lastPath = path; scrollMarks = {};
    push('page_view', { label: d.title });
  }
  function hookHistory() {
    ['pushState', 'replaceState'].forEach(function (fn) {
      var orig = history[fn];
      history[fn] = function () { var r = orig.apply(this, arguments); setTimeout(pageView, 50); return r; };
    });
    w.addEventListener('popstate', function () { setTimeout(pageView, 50); });
  }

  /* ---------- scroll depth ---------- */
  function onScroll() {
    var h = d.documentElement, b = d.body;
    var total = Math.max(h.scrollHeight, b.scrollHeight) - w.innerHeight;
    if (total <= 0) return;
    var pct = Math.round(((w.scrollY || h.scrollTop) / total) * 100);
    [25, 50, 75, 90].forEach(function (m) {
      if (pct >= m && !scrollMarks[m]) { scrollMarks[m] = 1; push('scroll', { value: m }); }
    });
  }

  /* ---------- active time (tab visible and some activity in the last 30 s) ---------- */
  var activeSec = 0, lastActivity = Date.now();
  function markActive() { lastActivity = Date.now(); }
  function tick() {
    if (d.visibilityState === 'visible' && Date.now() - lastActivity < 30000) activeSec++;
    if (activeSec >= 15) sendEngaged();
  }
  function sendEngaged() {
    if (activeSec > 0) { push('engaged_time', { value: activeSec }); activeSec = 0; }
  }

  /* ---------- clicks ----------
   * Add data-track="book_now" (or any name) to important buttons for clean names in reports.
   * Without it, buttons and links whose text looks like a call to action are still recorded.
   */
  var CTA_RE = /book|বুক|বুকিং|যোগাযোগ|আগ্রহী|জানতে|সাইট ভিজিট|site visit|inquir|enquir|call|কল|survey|শুরু|দাম|খরচ/i;
  function onClick(ev) {
    var el = ev.target && ev.target.closest ? ev.target.closest('a,button,[role="button"],[data-track]') : null;
    if (!el) return;
    var text = (el.getAttribute('aria-label') || el.innerText || el.textContent || '').replace(/\s+/g, ' ').trim().slice(0, 80);
    var href = el.getAttribute('href') || '';
    var tag = el.getAttribute('data-track');

    if (/^tel:/i.test(href)) return push('call_click', { label: text });
    if (/wa\.me|whatsapp/i.test(href)) return push('whatsapp_click', { label: text });
    if (tag) return push('cta_click', { label: tag, value: text });
    if (CTA_RE.test(text)) return push('cta_click', { label: text });
    if (el.tagName === 'BUTTON' || el.getAttribute('role') === 'button') push('click', { label: text });
  }
  function onPlay(ev) {
    if (ev.target && ev.target.tagName === 'VIDEO') push('video_play', { label: (ev.target.currentSrc || '').split('/').pop() });
  }

  /* ---------- Meta Pixel events reported by utils/metaPixel.ts ---------- */
  var PIXEL_MAP = {
    ViewContent: 'view_content', SurveyOpen: 'survey_open', SurveyStart: 'survey_start',
    SurveyStep: 'survey_step', Lead: 'lead', QualifiedLead: 'qualified_lead', Contact: 'contact'
  };
  var PII_KEY = /phone|mobile|email|name|nid|address|^ph$|^fn$|^ln$|^em$/i;

  function pixelEvent(name, p) {
    var e = PIXEL_MAP[name];
    if (!e || !enabled) return;
    p = p || {};
    var meta = {};
    Object.keys(p).slice(0, 15).forEach(function (k) {
      if (!PII_KEY.test(k) && (p[k] === null || typeof p[k] !== 'object')) meta[k] = p[k];
    });
    push(e, {
      pid: p.property_id || (p.content_ids && p.content_ids[0]) || null,
      step: p.step, meta: meta,
      label: p.question || p.cta || p.content_name || null,
      value: p.answer != null ? p.answer : (p.lead_tier || null)
    });
  }

  /* ---------- start ---------- */
  function start() {
    if (enabled || !hasConsent()) return;
    enabled = true;
    initIds();
    hookHistory();
    pageView();
    w.addEventListener('scroll', onScroll, { passive: true });
    ['scroll', 'click', 'touchstart', 'keydown', 'mousemove'].forEach(function (t) {
      w.addEventListener(t, markActive, { passive: true });
    });
    d.addEventListener('click', onClick, true);
    d.addEventListener('play', onPlay, true);
    setInterval(tick, 1000);
    d.addEventListener('visibilitychange', function () {
      if (d.visibilityState === 'hidden') { sendEngaged(); push('page_exit', { value: Math.round(performance.now() / 1000) }); flush(true); }
    });
    w.addEventListener('pagehide', function () { sendEngaged(); flush(true); });
  }

  w.GBTrack = {
    track: function (name, data) { push(name, data); },
    pixelEvent: pixelEvent,
    ids: function () { return enabled ? { vid: vid, sid: sid } : {}; },
    saveDraft: function (x) {
      if (!enabled || !x) return Promise.resolve(null);
      push('draft_saved', { step: x.step, pid: x.pid });
      flush(false);
      return fetch(api() + '/t/draft', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          vid: vid, sid: sid, property_id: x.pid || pidFromPath(), name: x.name || null,
          phone: x.phone || null, answers: x.answers || null, step: x.step || null, consent: true
        })
      }).then(function (r) { return r.ok ? r.json() : null; }).catch(function () { return null; });
    }
  };

  // The cookie banner announces the visitor's choice; start the moment they accept.
  w.addEventListener('gbrel:cookie-consent', start);
  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', start); else start();
})(window, document);
