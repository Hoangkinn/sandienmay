const { spawn } = require('child_process');
const http = require('http');
const { writeFileSync } = require('fs');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9369;
const getJSON = (p) => new Promise((res, rej) => http.get({ host: '127.0.0.1', port: PORT, path: p }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej));
const sleep = ms => new Promise(r => setTimeout(r, ms));
(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT, '--user-data-dir=' + process.env.TEMP + '/v-' + Date.now(), '--window-size=1440,900', 'about:blank'], { stdio: 'ignore' });
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
  await S('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
  await S('Page.navigate', { url: 'http://sandienmay.test/' });
  await sleep(5000);
  // sidebar trang chu: hover item dau
  const li = await ev(`(() => { const l = document.querySelector('.home-side .mega-rail-item[data-mega-index="0"]'); if (!l) return null; const r = l.getBoundingClientRect(); return { x: Math.round(r.left + r.width / 2), y: Math.round(r.top + r.height / 2) }; })()`);
  if (li) {
    await S('Input.dispatchMouseEvent', { type: 'mouseMoved', x: li.x, y: li.y });
    await sleep(500);
    console.log('sidebar cols:', await ev(`[...document.querySelectorAll('.home-side .mega-pane.is-active .mega-col')].map(c => { const r = c.getBoundingClientRect(); return c.querySelector('.mega-col-title').textContent.trim() + '@x' + Math.round(r.left) + ' w' + Math.round(r.width); })`));
    const s = await S('Page.captureScreenshot', { format: 'png' });
    writeFileSync('D:/laragon/www/sandienmay/deploy/mega-side-after.png', Buffer.from(s.data, 'base64'));
    console.log('shot -> deploy/mega-side-after.png');
  } else console.log('khong co home-side');

  // panel header o 1200 + 993
  for (const W of [1200, 993]) {
    await S('Emulation.setDeviceMetricsOverride', { width: W, height: 900, deviceScaleFactor: 1, mobile: false });
    await S('Page.navigate', { url: 'http://sandienmay.test/' });
    await sleep(4500);
    await ev(`document.getElementById('mega-toggle').click()`);
    await sleep(300);
    const p = await ev(`(() => { const l = document.querySelector('.site-header .mega-rail-item[data-mega-index="0"]'); const r = l.getBoundingClientRect(); return { x: Math.round(r.left + r.width / 2), y: Math.round(r.top + r.height / 2) }; })()`);
    await S('Input.dispatchMouseEvent', { type: 'mouseMoved', x: p.x, y: p.y });
    await sleep(500);
    console.log(W, 'cols:', await ev(`[...document.querySelectorAll('.site-header .mega-pane.is-active .mega-col')].map(c => { const r = c.getBoundingClientRect(); return Math.round(r.left) + ':' + Math.round(r.width); })`));
  }
  const s2 = await S('Page.captureScreenshot', { format: 'png' });
  writeFileSync('D:/laragon/www/sandienmay/deploy/mega-993-after.png', Buffer.from(s2.data, 'base64'));
  console.log('shot -> deploy/mega-993-after.png');
  chrome.kill(); process.exit(0);
})();
