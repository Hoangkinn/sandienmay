// Chup screenshot that o bat ky width nao (390/320 van duoc - CLI --window-size bi kep 500px).
// Cach dung: node deploy/shot.js <url> <W> <H> <out.png> [choMs] [selector-bam]
//   selector-bam: dung Input.dispatchMouseEvent (click THAT, co toa do) chu khong el.click()
const { spawn } = require('child_process');
const http = require('http');
const fs = require('fs');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9377;
const URL_T = process.argv[2];
const W = +(process.argv[3] || 1280);
const H = +(process.argv[4] || 900);
const OUT = process.argv[5];
const WAIT = +(process.argv[6] || 6000);
const CLICK = process.argv[7] || '';
const SCROLL = +(process.argv[8] || 0);

const getJSON = (path) => new Promise((res, rej) => {
  http.get({ host: '127.0.0.1', port: PORT, path }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej);
});
const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--hide-scrollbars',
    '--remote-debugging-port=' + PORT, '--user-data-dir=' + process.env.TEMP + '/shot-' + Date.now(),
    '--window-size=' + W + ',' + (H + 200), 'about:blank'], { stdio: 'ignore' });

  let tabs;
  for (let i = 0; i < 40; i++) { try { tabs = await getJSON('/json'); if (tabs && tabs.length) break; } catch (e) {} await sleep(250); }
  const ws = new WS(tabs[0].webSocketDebuggerUrl, { maxPayload: 64 * 1024 * 1024 });
  await new Promise(r => ws.on('open', r));
  let id = 0; const pend = new Map();
  const send = (method, params = {}, sessionId) => new Promise(r => { const i = ++id; pend.set(i, r); ws.send(JSON.stringify({ id: i, method, params, sessionId })); });
  ws.on('message', m => { const j = JSON.parse(m); if (j.id && pend.has(j.id)) { pend.get(j.id)(j.result); pend.delete(j.id); } });

  const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
  const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
  const S = (m, p = {}) => send(m, p, sessionId);
  const ev = async (expr) => (await S('Runtime.evaluate', { expression: expr, returnByValue: true })).result.value;

  await S('Page.enable'); await S('Runtime.enable');
  await S('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 2, mobile: W < 800 });
  await S('Page.navigate', { url: URL_T });
  await sleep(WAIT);
  await ev(`document.fonts.ready.then(()=>'ok')`);
  await sleep(900);

  // Debugbar (chi co local) co thanh fixed duoi phai -> che khu vuc can do
  await ev(`document.querySelectorAll('.phpdebugbar,#debugbar,#debugbar-handler,#debugbar-dock-tab').forEach(e=>e.remove())`);
  if (SCROLL) { await ev(`window.scrollTo(0, ${SCROLL})`); await sleep(700); }

  if (CLICK) {
    const r = await ev(`(() => { const e = document.querySelector(${JSON.stringify(CLICK)}); if (!e) return null;
      const b = e.getBoundingClientRect(); return { x: b.left + b.width / 2, y: b.top + b.height / 2 }; })()`);
    if (!r) { console.log('KHONG TIM THAY', CLICK); }
    else {
      for (const type of ['mousePressed', 'mouseReleased']) {
        await S('Input.dispatchMouseEvent', { type, x: r.x, y: r.y, button: 'left', clickCount: 1 });
      }
      await sleep(900);
    }
  }

  const s = await S('Page.captureScreenshot', { format: 'png' });
  fs.writeFileSync(OUT, Buffer.from(s.data, 'base64'));

  // CLIP=WxH+X+Y (CSS px): cat sat khu vuc can do, anh nho nhung doc duoc chi tiet
  const CLIP = process.argv[9] || '';
  if (CLIP) {
    const m = CLIP.match(/^(\d+)x(\d+)\+(\d+)\+(\d+)$/);
    if (!m) throw new Error('CLIP phai dang 360x360+900+500, duoc: ' + CLIP);
    const [, cw, ch, cx, cy] = m.map(Number);
    const c = await S('Page.captureScreenshot', { format: 'png', clip: { x: cx, y: cy, width: cw, height: ch, scale: 2 } });
    fs.writeFileSync(OUT.replace(/\.png$/, '-crop.png'), Buffer.from(c.data, 'base64'));
  }
  console.log('shot ->', OUT, '| viewport', W + 'x' + H, '| innerWidth that =', await ev('window.innerWidth'));

  await send('Browser.close').catch(() => {});
  chrome.kill();
  process.exit(0);
})();
