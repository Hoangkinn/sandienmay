const { spawn } = require('child_process'); const http = require('http'); const WS = require('D:/laragon/www/sandienmay/node_modules/ws');
const CHROME='C:/Program Files/Google/Chrome/Application/chrome.exe', PORT=9366;
const g=(host,port,path)=>new Promise((res,rej)=>{http.get({host,port,path},r=>{let d='';r.on('data',c=>d+=c);r.on('end',()=>{try{res(JSON.parse(d))}catch(e){rej(e)}})}).on('error',rej)});
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
(async()=>{
 const urls=process.argv[2].split(','); const W=+(process.argv[3]||390);
 const ch=spawn(CHROME,['--headless=new','--disable-gpu','--no-sandbox','--remote-debugging-port='+PORT,'--user-data-dir='+process.env.TEMP+'/vdp'+Date.now(),'--window-size='+W+',844','about:blank'],{stdio:'ignore'});
 let tabs; for(let i=0;i<40;i++){try{tabs=await g('127.0.0.1',PORT,'/json');if(tabs.length)break}catch(e){}await sleep(250)}
 const ws=new WS(tabs[0].webSocketDebuggerUrl,{maxPayload:64*1024*1024}); await new Promise(r=>ws.on('open',r));
 let id=0; const pend=new Map(); const errs=[]; const fails=[];
 ws.on('message',m=>{const j=JSON.parse(m); if(j.id&&pend.has(j.id)){pend.get(j.id)(j.result);pend.delete(j.id)} if(j.method==='Log.entryAdded'&&j.params.entry.level==='error')errs.push(j.params.entry.text.slice(0,150)); if(j.method==='Network.loadingFailed')fails.push(j.params.errorText+' '+(j.params.type||'')); if(j.method==='Network.responseReceived'&&j.params.response.status>=400)fails.push(j.params.response.status+' '+j.params.response.url);});
 const send=(method,params={},sessionId)=>new Promise(r=>{const i=++id;pend.set(i,r);ws.send(JSON.stringify({id:i,method,params,sessionId}))});
 for(const u of urls){
  const {targetId}=await send('Target.createTarget',{url:'about:blank'}); const {sessionId}=await send('Target.attachToTarget',{targetId,flatten:true}); const S=(m,p={})=>send(m,p,sessionId);
  errs.length=0; fails.length=0;
  await S('Page.enable');await S('Runtime.enable');await S('Network.enable');await S('Log.enable');
  await S('Emulation.setDeviceMetricsOverride',{width:W,height:844,deviceScaleFactor:2,mobile:true});
  await S('Page.navigate',{url:u}); await sleep(7000);
  const ev=await S('Runtime.evaluate',{expression:`JSON.stringify({iw:innerWidth,glideMounted:document.querySelectorAll('.glide--slider').length,glideTotal:document.querySelectorAll('.glide').length,navHidden:[...document.querySelectorAll('.carousel-wrap [data-glide-el="controls"]')].filter(e=>getComputedStyle(e).display==='none').length,carouselWrap:document.querySelectorAll('.carousel-wrap').length,scrollTopBtn:!!document.getElementById('scrollToTopBtn'),mainNav:!!document.querySelector('.header-bottom .main-nav'),modalBtns:document.querySelectorAll('.jsShowModalDetailProduct').length,wish:document.querySelectorAll('[data-wishlist],.jsWishlist').length,scripts:[...document.querySelectorAll('script[src*="main.min.js"]')].map(s=>s.defer)})`,returnByValue:true});
  const d=JSON.parse(ev.result.value);
  console.log('\n### '+u); console.log('  iw',d.iw,'| main.min.js defer =',JSON.stringify(d.scripts));
  console.log('  glide',d.glideMounted+'/'+d.glideTotal,'| carousel-wrap',d.carouselWrap,'| nav an',d.navHidden,'| quickview btn',d.modalBtns,'| scrollBtn',d.scrollTopBtn,'| header nav',d.mainNav);
  console.log('  LOI console:',errs.length?errs.join(' ;; '):'(khong co)');
  console.log('  LOAD FAIL:',fails.length?[...new Set(fails)].join(' ;; '):'(khong co)');
  await send('Target.closeTarget',{targetId});
 }
 ch.kill(); process.exit(0);
})();
