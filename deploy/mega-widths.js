const { spawn } = require('child_process');
const http = require('http');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9365;
const getJSON = (p) => new Promise((res, rej) => http.get({ host: '127.0.0.1', port: PORT, path: p }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej));
const sleep = ms => new Promise(r => setTimeout(r, ms));
(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT, '--user-data-dir=' + process.env.TEMP + '/w-' + Date.now(), 'about:blank'], { stdio: 'ignore' });
  let tabs; for (let i = 0; i < 40; i++) { try { tabs = await getJSON('/json'); if (tabs.length) break; } catch (e) { } await sleep(250); }
  const ws = new WS(tabs[0].webSocketDebuggerUrl, { maxPayload: 64 * 1024 * 1024 });
  await new Promise(r => ws.on('open', r));
  let id = 0; const pend = new Map();
  const send = (m, p = {}, sid) => new Promise(r => { const i = ++id; pend.set(i, r); ws.send(JSON.stringify({ id: i, method: m, params: p, sessionId: sid })); });
  ws.on('message', m => { const j = JSON.parse(m); if (j.id && pend.has(j.id)) { pend.get(j.id)(j.result); pend.delete(j.id); } });
  const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
  const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p = {}) => send(m, p, sessionId);
  const ev = async (e) => (await S('Runtime.evaluate', { expression: e, returnByValue: true })).result.value;
  await S('Page.enable'); await S('Runtime.enable');
  for (const W of [993, 1024, 1200, 1366, 1440, 1920]) {
    await S('Emulation.setDeviceMetricsOverride', { width: W, height: 900, deviceScaleFactor: 1, mobile: false });
    await S('Page.navigate', { url: 'http://sandienmay.test/' });
    await sleep(2500);
    await ev(`document.getElementById('mega-toggle').click()`);
    await sleep(300);
    console.log(W, await ev(`(() => {
      const c = document.querySelector('.site-header .mega .container');
      const inner = document.querySelector('.site-header .mega-inner');
      const rail = document.querySelector('.site-header .mega-rail');
      const cs = getComputedStyle(c);
      const ir = inner.getBoundingClientRect(), rr = rail.getBoundingClientRect();
      return JSON.stringify({ vw: innerWidth, ccw: c.clientWidth, pad: cs.paddingLeft + '/' + cs.paddingRight, maxW: cs.maxWidth, innerW: Math.round(ir.width), innerH: Math.round(ir.height), railW: Math.round(rr.width), railH: Math.round(rr.height) });
    })()`));
  }
  chrome.kill(); process.exit(0);
})();
