const { spawn } = require('child_process');
const http = require('http');
const { writeFileSync } = require('fs');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9363;
const URL_T = process.argv[2] || 'http://sandienmay.test/';
const W = +(process.argv[3] || 1440);
const H = +(process.argv[4] || 900);

const getJSON = (path) => new Promise((res, rej) => {
  http.get({ host: '127.0.0.1', port: PORT, path }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej);
});
const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT,
    '--user-data-dir=' + process.env.TEMP + '/mega-' + Date.now(), '--window-size=' + W + ',' + H, 'about:blank'], { stdio: 'ignore' });

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
  const ev = async (expr) => { const r = await S('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }); return r.result.value; };

  await S('Page.enable'); await S('Runtime.enable');
  await S('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 1, mobile: false });
  await S('Page.navigate', { url: URL_T });
  await sleep(5000);
  console.log('viewport', await ev('window.innerWidth + "x" + window.innerHeight'));

  await ev(`document.getElementById('mega-toggle').click(); 'ok'`);
  await sleep(500);

  const rects = () => ev(`(() => {
    const g = s => { const e = document.querySelector(s); if (!e) return null; const r = e.getBoundingClientRect(); const cs = getComputedStyle(e); return { t: Math.round(r.top), b: Math.round(r.bottom), h: Math.round(r.height), w: Math.round(r.width), mt: cs.marginTop, pb: cs.paddingBottom, maxH: cs.maxHeight, ov: cs.overflowY, disp: cs.display, trans: cs.transition }; };
    return { inner: g('.site-header .mega-inner'), rail: g('.site-header .mega-rail'), body: g('.site-header .mega-body'), mega: g('.site-header .mega'), active: document.querySelector('.site-header .mega-pane.is-active') ? { idx: document.querySelector('.site-header .mega-pane.is-active').dataset.megaPane, h: Math.round(document.querySelector('.site-header .mega-pane.is-active').getBoundingClientRect().height) } : null, railScrollH: document.querySelector('.site-header .mega-rail')?.scrollHeight, railClientH: document.querySelector('.site-header .mega-rail')?.clientHeight };
  })()`);

  const itemRect = i => ev(`(() => { const li = document.querySelector('.site-header .mega-rail-item[data-mega-index="${i}"]'); const r = li.getBoundingClientRect(); return { x: Math.round(r.left + r.width / 2), y: Math.round(r.top + r.height / 2), name: li.querySelector('.mega-rail-name').textContent.trim() }; })()`);

  console.log('\n== panel vua mo (chua hover) ==');
  console.log(await rects());

  const hover = async i => {
    const p = await itemRect(i);
    await S('Input.dispatchMouseEvent', { type: 'mouseMoved', x: p.x, y: p.y });
    return p;
  };

  const frameTrace = async (label, i) => {
    const p = await hover(i);
    const samples = await ev(`(async () => {
      const out = [];
      const t0 = performance.now();
      await new Promise(done => {
        const tick = () => {
          const inner = document.querySelector('.site-header .mega-inner');
          const rail = document.querySelector('.site-header .mega-rail');
          const body = document.querySelector('.site-header .mega-body');
          const ir = inner.getBoundingClientRect();
          out.push({ t: Math.round(performance.now() - t0), ih: Math.round(ir.height), iw: Math.round(ir.width), rh: Math.round(rail.getBoundingClientRect().height), bh: Math.round(body.getBoundingClientRect().height), bscroll: body.scrollHeight });
          if (performance.now() - t0 > 420) return done();
          requestAnimationFrame(tick);
        };
        tick();
      });
      return out;
    })()`);
    // only print rows where values change
    const rows = [];
    let prev = '';
    for (const s of samples) {
      const key = [s.ih, s.iw, s.rh, s.bh].join('/');
      if (key !== prev) { rows.push(s); prev = key; }
    }
    console.log('\n== hover', i, p.name, '-> so frame thay doi layout:', rows.length, '==');
    console.table(rows);
    await sleep(300);
    console.log(await rects());
    return p;
  };

  await frameTrace('A', 1);
  await frameTrace('B', 0);
  await frameTrace('C', 2);
  await frameTrace('D', 0);

  const s = await S('Page.captureScreenshot', { format: 'png' });
  writeFileSync('D:/laragon/www/sandienmay/deploy/mega-shot.png', Buffer.from(s.data, 'base64'));
  console.log('shot -> deploy/mega-shot.png');

  // CSS thuc te cua .mega-inner
  console.log('\ninner css:', await ev(`(() => { const e = document.querySelector('.site-header .mega-inner'); const cs = getComputedStyle(e); return JSON.stringify({ width: cs.width, height: cs.height, transition: cs.transition, overflow: cs.overflow, alignItems: cs.alignItems }); })()`));
  console.log('rail css:', await ev(`(() => { const e = document.querySelector('.site-header .mega-rail'); const cs = getComputedStyle(e); return JSON.stringify({ maxHeight: cs.maxHeight, height: cs.height, flex: cs.flex, overflowY: cs.overflowY, transition: cs.transition, padding: cs.padding }); })()`));

  chrome.kill(); process.exit(0);
})();
