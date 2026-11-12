const {chromium}=require('playwright'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const base=process.env.PREVIEW_URL||'http://127.0.0.1:3002';
const expected=['/stock/','/sell/','/maintenance/','/inspection/','/rental/','/road-assistance/'];
function files(dir){return fs.readdirSync(dir,{withFileTypes:true}).flatMap(f=>f.isDirectory()?files(path.join(dir,f.name)):[path.join(dir,f.name)])}
(async()=>{const b=await chromium.launch({executablePath:'/usr/bin/chromium',args:['--no-sandbox']});try{
 for(const file of files('public').filter(f=>f.endsWith('index.html')&&!f.includes('/admin/')&&!f.includes('/buy/'))){const html=fs.readFileSync(file,'utf8');assert(!/href="\/buy\//.test(html),file);const menu=html.match(/<nav id="menu".*?<\/nav>/s)?.[0];if(html.includes('<header'))assert(menu,file);if(menu)assert.deepEqual([...menu.matchAll(/href="([^"]+)"/g)].slice(0,6).map(x=>x[1]),expected,file)}
 for(const width of [320,390,768,1280,1440,1920]){
  const p=await b.newPage({viewport:{width,height:900}}),errors=[];p.on('pageerror',e=>errors.push(e.message));
  for(const route of ['/','/stock/','/business/','/sell/','/rental/']){await p.goto(base+route);assert.equal(await p.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false,`${route} at ${width}`);assert.deepEqual(await p.locator('#menu a').evaluateAll(es=>es.slice(0,6).map(e=>e.getAttribute('href'))),expected);assert.deepEqual(await p.locator('.rail-service-nav a').evaluateAll(es=>es.map(e=>e.getAttribute('href'))),expected);if(route==='/sell/')assert((await p.locator('body').innerText()).includes('走行距離、修復歴'));}
  await p.locator('.rental-hero .rental-cta').click();await p.waitForURL(/\/contact\/\?type=rental/);assert.equal(await p.locator('#contact-type').inputValue(),'rental');assert((await p.locator('#contact-message').getAttribute('placeholder')).includes('軽貨物'));
  if(width===390){await p.locator('#contact-name').fill('動作確認');await p.locator('#contact-email').fill('rental-check@example.invalid');await p.locator('#contact-message').fill('軽貨物のレンタカーについて、入力・確認の動作テストです。');await p.locator('#contact-consent').check();await p.locator('#contact-form button[type=submit]').click();assert(await p.locator('#contact-confirmation').isVisible());assert((await p.locator('#contact-confirm-list').innerText()).includes('レンタカー・リース'));}
  assert.deepEqual(errors,[]);await p.close();console.log('Services, rental CTA and layout PASS:',width);
 }
 const p=await b.newPage();const response=await p.request.get(base+'/buy/',{maxRedirects:0});assert.equal(response.status(),301);assert.equal(response.headers().location,'/stock/');await p.goto(base+'/buy/');assert(new URL(p.url()).pathname==='/stock/');await p.close();console.log('PASS: all-page menu order, text-only order guidance, rental prefill/confirmation and retired page redirect');
}finally{await b.close()}})().catch(e=>{console.error(e);process.exitCode=1});
