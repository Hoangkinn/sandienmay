const { spawn } = require('child_process');
const http = require('http');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9357;
const URL_T = process.argv[2] || 'http://sandienmay.test/';
const W = +(process.argv[4] || 1280); const H = +(process.argv[5] || 900); const MOB = W < 600;
const SHOT = process.argv[3] || '';

const getJSON = (path) => new Promise((res, rej) => {
  http.get({ host: '127.0.0.1', port: PORT, path }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej);
});
const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT,
    '--user-data-dir=' + process.env.TEMP + '/font-audit-' + Date.now(), '--window-size=' + W + ',' + H, 'about:blank'], { stdio: 'ignore' });

  let tabs;
  for (let i = 0; i < 40; i++) { try { tabs = await getJSON('/json'); if (tabs.length) break; } catch (e) {} await sleep(250); }
  const ws = new WS(tabs[0].webSocketDebuggerUrl, { maxPayload: 64 * 1024 * 1024 });
  await new Promise(r => ws.on('open', r));
  let id = 0; const pend = new Map(); const events = [];
  const send = (method, params = {}, sessionId) => new Promise(r => { const i = ++id; pend.set(i, r); ws.send(JSON.stringify({ id: i, method, params, sessionId })); });
  ws.on('message', m => {
    const j = JSON.parse(m);
    if (j.id && pend.has(j.id)) { pend.get(j.id)(j.result); pend.delete(j.id); return; }
    events.push(j);
  });

  const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
  const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p = {}) => send(m, p, sessionId);

  await S('Page.enable'); await S('Runtime.enable'); await S('Network.enable');
  await S('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 1, mobile: MOB });
  await S('Page.navigate', { url: URL_T });
  await sleep(8000);

  const byMethod = {};
  for (const e of events) byMethod[e.method] = (byMethod[e.method] || 0) + 1;
  console.log('CDP events:', events.length, JSON.stringify(byMethod));
  const reqs = events.filter(e => e.method === 'Network.requestWillBeSent' && e.params && e.params.request).map(e => e.params.request.url);
  const gf = reqs.filter(u => /fonts\.(googleapis|gstatic)\.com/.test(u));
  const local = reqs.filter(u => /\/css-new\/fonts\/inter\//.test(u));
  console.log('URL:', URL_T);
  console.log('requests total:', reqs.length);
  console.log('Google Fonts requests:', gf.length, gf.map(u => u.slice(0, 90)));
  console.log('self-host Inter requests:', local.length, local.map(u => u.replace(/^https?:\/\/[^/]+/, '')));

  const fontResp = events.filter(e => e.method === 'Network.responseReceived' && e.params && e.params.response && /\/css-new\/fonts\/inter\//.test(e.params.response.url))
    .map(e => ({ url: e.params.response.url.replace(/^https?:\/\/[^/]+/, ''), mime: e.params.response.mimeType, status: e.params.response.status }));
  console.table(fontResp);

  const probe = `(() => {
    const faces = [...document.fonts].map(f => ({ fam: f.family, wght: f.weight, style: f.style, status: f.status, unicode: (f.unicodeRange || '').slice(0, 26) }));
    const host = document.createElement('div');
    host.style.cssText = 'position:fixed;left:-9999px;top:0;font-size:40px;line-height:1.4;white-space:pre;font-family:Inter,sans-serif';
    document.body.appendChild(host);
    const widths = {};
    for (const w of [400, 500, 600, 700, 800, 900]) {
      const s = document.createElement('span');
      s.style.fontWeight = w;
      s.textContent = 'Máy hút bụi 1.234₫ ABCabc ỳ Đ ă Ư ơ ạ';
      host.appendChild(s);
      widths[w] = +s.getBoundingClientRect().width.toFixed(2);
    }
    const bodyFont = getComputedStyle(document.body).fontFamily;
    const overflow = { voiInter: document.documentElement.scrollWidth, voiSans: null };
    const prev = document.body.style.fontFamily;
    document.body.style.fontFamily = 'sans-serif';
    overflow.voiSans = document.documentElement.scrollWidth;
    overflow.wide = [...document.querySelectorAll('body *')]
      .filter(e => e.getBoundingClientRect().right > innerWidth + 1)
      .slice(0, 4).map(e => (e.tagName + '.' + String(e.className).slice(0, 30) + ' w' + (e.getBoundingClientRect().width | 0)));
    document.body.style.fontFamily = prev;
    const res = performance.getEntriesByType('resource')
      .filter(r => /[.]woff2|css2/.test(r.name) || /fonts[.]googleapis|fonts[.]gstatic/.test(r.name))
      .map(r => ({ name: r.name.slice(r.name.indexOf('/', 8)).slice(0, 60), KB: +(r.transferSize / 1024).toFixed(1), start: +r.startTime.toFixed(0), end: +r.responseEnd.toFixed(0) }));
    return JSON.stringify({ faces, widths, bodyFont, res, overflow, ready: document.fonts.status });
  })()`;
  const ev = await S('Runtime.evaluate', { expression: probe, returnByValue: true });
  if (!ev.result || ev.result.value === undefined) { console.log('EVAL FAILED:', JSON.stringify(ev).slice(0, 2000)); chrome.kill(); process.exit(1); }
  const d = JSON.parse(ev.result.value);
  console.log('body font-family:', d.bodyFont, '| document.fonts status:', d.ready);
  console.table(d.faces);
  console.table(d.widths);
  console.log('overflow:', JSON.stringify(d.overflow), 'viewport', W);
  console.log('--- resource timing (fonts + google css):');
  console.table(d.res);

  // Font that the renderer actually used for real Vietnamese page text.
  await S('DOM.enable'); await S('CSS.enable');
  const { root } = await S('DOM.getDocument', { depth: 1 });
  const picked = [];
  for (const sel of ['.current-price', '.old-price', 'h3', '.site-header .logo', 'body']) {
    const q = await S('DOM.querySelector', { nodeId: root.nodeId, selector: sel });
    if (!q.nodeId) continue;
    const f = await S('CSS.getPlatformFontsForNode', { nodeId: q.nodeId });
    picked.push({ sel, fonts: (f.fonts || []).map(x => x.familyName + ' x' + x.glyphCount).join(', ') });
  }
  console.table(picked);

  if (SHOT) {
    await S('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 1, mobile: MOB });
    const shot = await S('Page.captureScreenshot', { format: 'png' });
    require('fs').writeFileSync(SHOT, Buffer.from(shot.data, 'base64'));
    console.log('screenshot ->', SHOT);
  }
  chrome.kill(); process.exit(0);
})();
