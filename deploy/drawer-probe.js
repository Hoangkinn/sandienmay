const { spawn } = require('child_process');
const http = require('http');
const WS = require('D:/laragon/www/sandienmay/node_modules/ws');

const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = 9363;
const URL_T = process.argv[2] || 'https://demo.sandienmay.com/';
const W = +(process.argv[3] || 390);
const H = +(process.argv[4] || 844);

const getJSON = (path) => new Promise((res, rej) => {
  http.get({ host: '127.0.0.1', port: PORT, path }, r => { let d = ''; r.on('data', c => d += c); r.on('end', () => { try { res(JSON.parse(d)); } catch (e) { rej(e); } }); }).on('error', rej);
});
const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  const chrome = spawn(CHROME, ['--headless=new', '--disable-gpu', '--no-sandbox', '--remote-debugging-port=' + PORT,
    '--user-data-dir=' + process.env.TEMP + '/drw-' + Date.now(), '--window-size=' + W + ',' + (H + 200), 'about:blank'], { stdio: 'ignore' });

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

  console.log('== drawer menu mobile tren', URL_T);
  await ev(`document.getElementById('menu-toggle').click(); 'ok'`);
  await sleep(1200);
  console.log(await ev(`(() => {
    const d = document.getElementById('modelMenuMobi');
    return { drawerOpen: d && d.open };
  })()`));

  const before = await ev(`(() => {
    const btn = document.querySelector('#modelMenuMobi .submenu-toggle-btn');
    if (!btn) return { err: 'khong co .submenu-toggle-btn trong drawer' };
    const li = btn.closest('.has-submenu-custom');
    return { hasBtn: true, activeBefore: li ? li.classList.contains('is-active') : null };
  })()`);
  console.log('truoc click:', before);

  await ev(`document.querySelector('#modelMenuMobi .submenu-toggle-btn').click(); 'ok'`);
  await sleep(700);
  console.log('sau click:', await ev(`(() => {
    const btn = document.querySelector('#modelMenuMobi .submenu-toggle-btn');
    const li = btn.closest('.has-submenu-custom');
    const sub = li.querySelector('.submenu-custom') || li.querySelector('ul');
    const r = sub ? sub.getBoundingClientRect() : null;
    return { activeAfter: li.classList.contains('is-active'), liCls: li.className,
             subTag: sub ? sub.tagName + '.' + sub.className : null,
             subH: r ? r.height|0 : null };
  })()`));

  console.log('cau truc con cua li:', await ev(`(() => {
    const li = document.querySelector('#modelMenuMobi .submenu-toggle-btn').closest('.has-submenu-custom');
    return [...li.children].map(c => c.tagName + '.' + c.className.toString().trim() + ' h=' + (c.getBoundingClientRect().height|0) + ' disp=' + getComputedStyle(c).display + ' maxH=' + getComputedStyle(c).maxHeight);
  })()`));

  console.log('widget bi drawer che?', await ev(`(() => {
    const fab = document.querySelector('.lh-fab').getBoundingClientRect();
    const hit = document.elementFromPoint(fab.left + fab.width/2, fab.top + fab.height/2);
    return { fabZ: getComputedStyle(document.getElementById('lhWrap')).zIndex,
             hitIs: hit ? (hit.closest('dialog') ? 'dialog#' + hit.closest('dialog').id : hit.className.toString().slice(0,40)) : null };
  })()`));

  chrome.kill(); process.exit(0);
})();
