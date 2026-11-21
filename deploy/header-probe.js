const { spawn } = require('child_process');
const http = require('http');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9361;
const URL_T = process.argv[2] || 'https://demo.sandienmay.com/';
const W = +(process.argv[3] || 390);
const H = +(process.argv[4] || 844);

const getJSON = (path) => new Promise((res, rej) => {
  http.get({ host: '127.0.0.1', port: PORT, path }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej);
});
const sleep = ms => new Promise(r => setTimeout(r, ms));

const SNAP = `(() => {
  const h = document.querySelector('.site-header');
  if (!h) return {err:'khong co .site-header'};
  const cs = getComputedStyle(h);
  const r = h.getBoundingClientRect();
  return {
    y: window.scrollY|0,
    cls: h.className,
    pos: cs.position, top: cs.top, transform: cs.transform,
    rectTop: r.top|0, rectH: r.height|0, visible: r.bottom > 0 && r.top < window.innerHeight,
    navOffsetTop: document.querySelector('main, .main') ? (document.querySelector('main, .main').getBoundingClientRect().top|0) : null,
    bgSelf: cs.backgroundColor, z: cs.zIndex,
    bgTop: (() => { const t = document.querySelector('.site-header .header-top'); return t ? getComputedStyle(t).backgroundColor + ' / h' + (t.getBoundingClientRect().height|0) : null; })(),
    htmlScrollW: document.documentElement.scrollWidth
  };
})()`;

(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT,
    '--user-data-dir=' + process.env.TEMP + '/hdr-' + Date.now(), '--window-size=' + W + ',' + (H + 200), 'about:blank'], { stdio: 'ignore' });

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
  const ev = async (expr) => (await S('Runtime.evaluate', { expression: expr, returnByValue: true })).result.value;

  await S('Page.enable'); await S('Runtime.enable');
  await S('Emulation.setDeviceMetricsOverride', { width: W, height: H, deviceScaleFactor: 2, mobile: true });
  await S('Page.navigate', { url: URL_T });
  await sleep(7000);

  const rows = [];
  const pts = [[0, 'dau trang'], [1500, 'cuon xuong 1500'], [1200, 'cuon NGUOC len 1200'], [900, 'tiep tuc len 900'], [600, '600'], [0, 've 0']];
  for (const [y, label] of pts) {
    await ev(`window.scrollTo(0, ${y}); 'ok'`);
    // dispatch scroll that JS thật cũng nghe
    await sleep(700);
    rows.push({ label, ...(await ev(SNAP)) });
  }
  console.log(URL_T, '| viewport', W + 'x' + H);
  console.table(rows);

  const { createFileSync, writeFileSync } = require('fs');
  const SHOT = process.argv[5];
  if (SHOT) {
    await ev(`window.scrollTo(0, 1500); 'ok'`);
    await sleep(700);
    await ev(`window.scrollTo(0, 1200); 'ok'`);
    await sleep(900);
    const s = await S('Page.captureScreenshot', { format: 'png' });
    writeFileSync(SHOT, Buffer.from(s.data, 'base64'));
    console.log('shot ->', SHOT);
    if (process.argv[6] === 'drawer') {
        await ev(`document.getElementById('menu-toggle').click(); 'ok'`);
        await sleep(1200);
        const s2 = await S('Page.captureScreenshot', { format: 'png' });
        writeFileSync(SHOT.replace(/\.png$/, '-drawer.png'), Buffer.from(s2.data, 'base64'));
        console.log('drawer opened, header rectTop =', await ev(`document.querySelector('.site-header').getBoundingClientRect().top|0`),
          '| thu tu: header dau day?', await ev(`(() => { const d = document.getElementById('modelMenuMobi'); const h = document.querySelector('.site-header');
            return 'dialogOpen=' + d.open + ' headerHitTop=' + (document.elementFromPoint(150, 20) ? document.elementFromPoint(150, 20).closest('header, dialog')?.id || document.elementFromPoint(150, 20).closest('header, dialog')?.className : null); })()`));
    }
  }

  // A/B: scrollWidth khi ep header ve position: relative (CSS truoc vong nay)
  console.log('scrollWidth ep relative =', await ev(`(() => {
    const h = document.querySelector('.site-header'); const o = h.style.position;
    h.style.position = 'relative'; const w = document.documentElement.scrollWidth;
    h.style.position = o; return w + ' (sticky: ' + document.documentElement.scrollWidth + ')';
  })()`));
  console.log('--- phan tu vo rong ' + W + 'px:');
  console.log(await ev(`JSON.stringify([...document.querySelectorAll('body *')].map(e => {
    const r = e.getBoundingClientRect();
    return { t: e.tagName + '.' + (e.className && e.className.toString ? e.className.toString().split(' ').slice(0,2).join('.') : ''), l: r.left|0, w: r.width|0 };
  }).filter(x => x.l + x.w > ${W} + 1).slice(0, 12))`));

  // tinh cao header that
  console.log('header offsetHeight =', await ev(`document.querySelector('.site-header').offsetHeight`));
  console.log('innerWidth =', await ev(`window.innerWidth`));
  chrome.kill(); process.exit(0);
})();
