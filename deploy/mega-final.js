const { spawn } = require('child_process');
const http = require('http');
const { writeFileSync } = require('fs');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9371;
const getJSON = (p) => new Promise((res, rej) => http.get({ host: '127.0.0.1', port: PORT, path: p }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej));
const sleep = ms => new Promise(r => setTimeout(r, ms));
(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT, '--user-data-dir=' + process.env.TEMP + '/f-' + Date.now(), '--window-size=1440,900', 'about:blank'], { stdio: 'ignore' });
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

  for (const W of [1440, 1280]) {
    await S('Emulation.setDeviceMetricsOverride', { width: W, height: 900, deviceScaleFactor: 1, mobile: false });
    await S('Page.navigate', { url: 'http://sandienmay.test/' });
    await sleep(5000);
    await ev(`document.getElementById('mega-toggle').click()`);
    await sleep(400);
    const p = await ev(`(() => { const l = document.querySelector('.site-header .mega-rail-item[data-mega-index="0"]'); const r = l.getBoundingClientRect(); return { x: Math.round(r.left + r.width / 2), y: Math.round(r.top + r.height / 2) }; })()`);
    await S('Input.dispatchMouseEvent', { type: 'mouseMoved', x: p.x, y: p.y });
    await sleep(700);
    console.log(W, await ev(`(() => {
      const rail = document.querySelector('.site-header .mega-rail');
      const cols = [...document.querySelectorAll('.site-header .mega-pane.is-active .mega-col')];
      const rr = rail.getBoundingClientRect();
      // link dai nhat co badge: do overflow khong
      const links = [...document.querySelectorAll('.site-header .mega-pane.is-active .mega-col-link')];
      const overflow = links.filter(a => a.scrollWidth > a.clientWidth + 1).map(a => a.textContent.trim().slice(0, 30) + ' (' + a.clientWidth + '/' + a.scrollWidth + ')');
      return JSON.stringify({ railRight: Math.round(rr.right), firstColX: cols.length ? Math.round(cols[0].getBoundingClientRect().left) : null, gap: cols.length ? Math.round(cols[0].getBoundingClientRect().left - rr.right) : null, colWidths: cols.map(c => Math.round(c.getBoundingClientRect().width)), badgeOverflow: overflow }, null, 1);
    })()`));
    if (W === 1440) {
      const s = await S('Page.captureScreenshot', { format: 'png' });
      writeFileSync('D:/laragon/www/sandienmay/deploy/mega-final-1440.png', Buffer.from(s.data, 'base64'));
    } else {
      const s = await S('Page.captureScreenshot', { format: 'png' });
      writeFileSync('D:/laragon/www/sandienmay/deploy/mega-final-1280.png', Buffer.from(s.data, 'base64'));
    }
  }
  console.log('shots -> deploy/mega-final-1440.png, mega-final-1280.png');
  chrome.kill(); process.exit(0);
})();
