// In ra cac giai cua LCP tren demo: ttfb -> loadDelay -> loadTime -> renderDelay + thoi diem tai cua anh
// Dung: node deploy/lcp-phases.js <url> [w] [h]
const { spawn } = require('child_process');
const http = require('http');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9366;
const URL_T = process.argv[2] || 'https://demo.sandienmay.com/';
const W = +(process.argv[3] || 390);
const H = +(process.argv[4] || 844);

const getJSON = (path) => new Promise((res, rej) => {
  http.get({ host: '127.0.0.1', port: PORT, path }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej);
});
const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT,
    '--user-data-dir=' + process.env.TEMP + '/lcpph-' + Date.now(), '--window-size=' + W + ',' + H, 'about:blank'], { stdio: 'ignore' });

  let tabs;
  for (let i = 0; i < 40; i++) { try { tabs = await getJSON('/json'); if (tabs.length) break; } catch (e) { } await sleep(250); }
  const ws = new WS(tabs[0].webSocketDebuggerUrl, { maxPayload: 64 * 1024 * 1024 });
  await new Promise(r => ws.on('open', r));
  let id = 0; const pend = new Map();
  const send = (method, params = {}, sessionId) => new Promise(r => { const i = ++id; pend.set(i, r); ws.send(JSON.stringify({ id: i, method, params, sessionId })); });
  ws.on('message', m => { const j = JSON.parse(m); if (j.id && pend.has(j.id)) { pend.get(j.id)(j.result); pend.delete(j.id); } });

  const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
  const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p = {}) => send(m, p, sessionId);

  await S('Page.enable'); await S('Runtime.enable'); await S('Network.enable');
  await S('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 2, mobile: true });
  await S('Page.addScriptToEvaluateOnNewDocument', {
    source: `window.__lcp=[];new PerformanceObserver(l=>l.getEntries().forEach(e=>window.__lcp.push({
      url:(e.url||'').split('/').pop().slice(0,46), tag:e.element&&e.element.tagName, cls:e.element&&String(e.element.className).slice(0,36),
      start:+e.startTime.toFixed(0), loadStart:e.loadStart&&+e.loadStart.toFixed(0), loadEnd:e.loadTime&&+e.loadTime.toFixed(0),
      render:e.renderTime&&+e.renderTime.toFixed(0), size:e.size}))).observe({type:'largest-contentful-paint',buffered:true});`
  });
  await S('Page.navigate', { url: URL_T });
  await sleep(9000);

  const ev = await S('Runtime.evaluate', {
    expression: `JSON.stringify({
      n:(()=>{const x=performance.getEntriesByType('navigation')[0]||{};return{ttfb:+(x.responseEnd-x.startTime).toFixed(0),domContentLoaded:+x.domContentLoadedEventEnd.toFixed(0),load:+x.loadEventEnd.toFixed(0)}})(),
      lcp:window.__lcp,
      res:performance.getEntriesByType('resource').filter(x=>/\\.(webp|jpe?g|png)/.test(x.name)).map(x=>({u:x.name.split('/').pop().slice(0,40),req:+x.requestStart.toFixed(0),start:+x.startTime.toFixed(0),end:+x.responseEnd.toFixed(0),bytes:+(x.transferSize/1024).toFixed(1)})).sort((a,b)=>b.bytes-a.bytes).slice(0,8)
    })`,
    returnByValue: true
  });
  const d = JSON.parse(ev.result.value);
  console.log('URL', URL_T, '| viewport', W + 'x' + H + ' dpr2');
  console.log('navigation:', JSON.stringify(d.n));
  console.log('--- LCP entries:'); console.table(d.lcp);
  for (const e of d.lcp) if (e.loadEnd && e.render) console.log(`  ${e.url}: taiMuon=${e.loadStart - e.start}ms  tai=${e.loadEnd - e.loadStart}ms  renderMuon=${e.render - e.loadEnd}ms  (start=${e.start} loadStart=${e.loadStart} loadEnd=${e.loadEnd} render=${e.render})`);
  console.log('--- 8 anh nang nhat (thoi diem request -> xong tai):'); console.table(d.res);
  chrome.kill(); process.exit(0);
})();
