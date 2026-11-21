const { spawn } = require('child_process');
const http = require('http');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9355;
const URL_T = process.argv[2] || 'https://demo.sandienmay.com/';
const W = +(process.argv[3] || 390);
const H = +(process.argv[4] || 844);

const getJSON = (path) => new Promise((res, rej) => {
  http.get({ host: '127.0.0.1', port: PORT, path }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej);
});
const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT,
    '--user-data-dir=' + process.env.TEMP + '/lcp-prof-' + Date.now(), '--window-size=' + W + ',' + H, 'about:blank'], { stdio: 'ignore' });

  let tabs;
  for (let i = 0; i < 40; i++) { try { tabs = await getJSON('/json'); if (tabs.length) break; } catch (e) {} await sleep(250); }
  const ws = new WS(tabs[0].webSocketDebuggerUrl, { maxPayload: 64 * 1024 * 1024 });
  await new Promise(r => ws.on('open', r));
  let id = 0; const pend = new Map();
  const send = (method, params = {}, sessionId) => new Promise(r => { const i = ++id; pend.set(i, r); ws.send(JSON.stringify({ id: i, method, params, sessionId })); });
  ws.on('message', m => { const j = JSON.parse(m); if (j.id && pend.has(j.id)) { pend.get(j.id)(j.result); pend.delete(j.id); } });

  const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
  const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p = {}) => send(m, p, sessionId);

  await S('Page.enable'); await S('DOM.enable'); await S('Runtime.enable');
  await S('Network.enable');
  await S('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 2, mobile: true });
  await S('Page.addScriptToEvaluateOnNewDocument', {
    source: `window.__lcp=[];new PerformanceObserver(l=>l.getEntries().forEach(e=>window.__lcp.push({url:e.url,el:e.element,cls:e.element&&e.element.className,rect:e.element&&[e.element.getBoundingClientRect().left|0,e.element.getBoundingClientRect().top|0,e.element.getBoundingClientRect().width|0,e.element.getBoundingClientRect().height|0],t:+e.startTime.toFixed(0),size:e.size}))).observe({type:'largest-contentful-paint',buffered:true});`
  });
  await S('Page.navigate', { url: URL_T });
  await sleep(9000);

  const ev = await S('Runtime.evaluate', { expression: 'JSON.stringify({w:window.innerWidth,dpr:devicePixelRatio,lcp:window.__lcp,FCP:+(performance.getEntriesByName("first-contentful-paint")[0]||{}).startTime,imgs:[...document.querySelectorAll("main img, .home-v2 img")].slice(0,60).map(i=>{const r=i.getBoundingClientRect();return{src:i.currentSrc.slice(-70),cls:i.className,top:r.top|0,h:r.height|0,w:r.width|0,area:(r.width*r.height)|0,complete:i.complete,n:i.naturalWidth+"x"+i.naturalHeight}}).filter(x=>x.area>0)})', returnByValue: true });
  const d = JSON.parse(ev.result.value);
  console.log('viewport', d.w, 'dpr', d.dpr, 'FCP', d.FCP);
  console.log('--- LCP entries:'); console.table(d.lcp);
  console.log('--- 8 img lớn nhất trong trang:');
  console.table([...d.imgs].sort((a, b) => b.area - a.area).slice(0, 8));
  chrome.kill(); process.exit(0);
})();
