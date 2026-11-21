// Do LCP co throttle giong Lighthouse mobile (1.4Mbps / RTT 150ms / CPU 4x) va so sanh
// thoi diem LCP render vs thoi diem Glide mount + chuoi script .min.js
// Dung: node deploy/lcp-throttled.js <url> [w] [h]
const { spawn } = require('child_process');
const http = require('http');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9377;
const URL_T = process.argv[2] || 'https://demo.sandienmay.com/';
const W = +(process.argv[3] || 390);
const H = +(process.argv[4] || 844);

const getJSON = (path) => new Promise((res, rej) => {
  http.get({ host: '127.0.0.1', port: PORT, path }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej);
});
const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT,
    '--user-data-dir=' + process.env.TEMP + '/lcpt-' + Date.now(), '--window-size=' + W + ',' + H, 'about:blank'], { stdio: 'ignore' });

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
  // Phoi canh mac dinh cua Lighthouse mobile
  await S('Network.emulateNetworkConditions', { offline: false, latency: 150, downloadThroughput: 1.6 * 1024 * 1024 / 8, uploadThroughput: 0.75 * 1024 * 1024 / 8 });
  await S('Emulation.setCPUThrottlingRate', { rate: 4 });
  await S('Page.addScriptToEvaluateOnNewDocument', {
    source: `
    window.__lcp=[];window.__mark={};
    new PerformanceObserver(l=>l.getEntries().forEach(e=>window.__lcp.push({
      url:(e.url||'').split('/').pop().slice(0,44),start:+e.startTime.toFixed(0),loadStart:e.loadStart&&+e.loadStart.toFixed(0),
      loadEnd:e.loadTime&&+e.loadTime.toFixed(0),render:e.renderTime&&+e.renderTime.toFixed(0),size:e.size})))
      .observe({type:'largest-contentful-paint',buffered:true});
    // ghi lai luc Glide mount (class glide--slider xuat hien) va luc script chay
    new MutationObserver(()=>{
      if(!window.__mark.glideMounted && document.querySelector('.glide--slider')) window.__mark.glideMounted=+performance.now().toFixed(0);
      if(!window.__mark.heroImgInDom && document.querySelector('.home-hero img')) window.__mark.heroImgInDom=+performance.now().toFixed(0);
    }).observe(document.documentElement,{subtree:true,childList:true,attributes:true,attributeFilter:['class']});
    addEventListener('DOMContentLoaded',()=>window.__mark.dcl=+performance.now().toFixed(0));
    addEventListener('load',()=>window.__mark.load=+performance.now().toFixed(0));`
  });
  await S('Page.navigate', { url: URL_T });
  await sleep(16000);

  const ev = await S('Runtime.evaluate', {
    expression: `JSON.stringify({
      fcp:+(performance.getEntriesByName('first-contentful-paint')[0]||{}).startTime.toFixed(0),
      n:(()=>{const x=performance.getEntriesByType('navigation')[0]||{};return{ttfb:+(x.domContentLoadedEventStart-x.startTime).toFixed(0),dcl:+x.domContentLoadedEventEnd.toFixed(0)}})(),
      lcp:window.__lcp, mark:window.__mark,
      res:performance.getEntriesByType('resource').filter(x=>/main\\.min\\.js|glide|CustomGlide|mobile_.*webp|_6ae.*webp/.test(x.name))
        .map(x=>({u:x.name.split('/').pop().slice(0,34),start:+x.startTime.toFixed(0),end:+x.responseEnd.toFixed(0),kb:+(x.transferSize/1024).toFixed(1)}))
    })`,
    returnByValue: true
  });
  const d = JSON.parse(ev.result.value);
  console.log('URL', URL_T, '| LH mobile throttle (1.6Mbps/150ms RTT, CPU 4x)');
  console.log('FCP', d.fcp, 'ms | navigation', JSON.stringify(d.n), '| marks', JSON.stringify(d.mark));
  console.table(d.lcp);
  for (const e of d.lcp) if (e.loadEnd && e.render) console.log(`  ${e.url}: requestMuon=${e.loadStart - e.start}  tai=${e.loadEnd - e.loadStart}  renderMuon=${e.render - e.loadEnd}  => LCP=${e.render}`);
  console.log('--- script/banner resource:'); console.table(d.res);
  chrome.kill(); process.exit(0);
})();
