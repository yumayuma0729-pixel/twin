const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const base=process.env.PREVIEW_URL||'http://127.0.0.1:3001';
(async()=>{const browser=await chromium.launch({executablePath:'/usr/bin/chromium',args:['--no-sandbox']});try{
 for(const [width,height] of [[320,568],[390,620],[390,844],[1280,600],[1440,900]]){
  const page=await browser.newPage({viewport:{width,height}}),errors=[];page.on('pageerror',e=>errors.push(e.message));await page.goto(base);
  let previousCanvas;
  for(let index=0;index<6;index++){
   await page.locator('#services').evaluate((e,i)=>scrollTo({top:scrollY+e.getBoundingClientRect().top+(e.offsetHeight-innerHeight)*i/5,behavior:'instant'}),index);
   await page.waitForTimeout(450);
   assert.equal(await page.locator('.strength-scene-copy:visible').getAttribute('data-strength-face'),String(index));
   assert.equal(await page.locator('.strength-scene-nav [aria-current=true]').getAttribute('data-strength-jump'),String(index));
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
   const bottom=await page.locator('.strength-scene-all').evaluate(e=>e.getBoundingClientRect().bottom);
   assert(bottom<=height-(width<768?60:0),`CTA clipped at ${width}: ${bottom}`);
   const image=await page.locator('canvas.strength-polyhedron').evaluate(e=>e.toDataURL());if(previousCanvas)assert.notEqual(image,previousCanvas,'Scrolling must change the rendered polyhedron, not just the caption');previousCanvas=image;
   const stage=await page.locator('.strength-scene-viewport').boundingBox();assert(Math.abs(stage.y)<2,`Stage must stay pinned while scrolling: ${stage.y}`);
   if(index===0){const canvas=page.locator('canvas.strength-polyhedron');const before=await canvas.evaluate(e=>e.toDataURL());await page.waitForTimeout(150);assert.equal(await canvas.evaluate(e=>e.toDataURL()),before,'Object must remain still without scrolling');}
  }
  await page.locator('[data-strength-jump="1"]').click();await page.waitForTimeout(350);assert.equal(await page.locator('.strength-scene-copy:visible').getAttribute('data-strength-face'),'1');
  assert.equal(await page.locator('[data-strength-variant]').count(),0);
  console.log('Scroll states PASS at',width);await page.emulateMedia({reducedMotion:'reduce'});await page.reload();assert.equal(await page.locator('.strength-scene-copy:visible').count(),6);assert.equal(await page.locator('#services').getAttribute('data-enhanced'),'false');assert.deepEqual(errors,[]);await page.close();
 }
 const page=await browser.newPage({javaScriptEnabled:false});await page.goto(base);assert.equal(await page.locator('.strength-scene-copy:visible').count(),6);await page.close();
 console.log('PASS: approved polyhedron only, six scroll states, navigation, no autonomous rotation, compact viewport fit, reduced-motion and no-JS content');
}finally{await browser.close()}})().catch(e=>{console.error(e);process.exitCode=1});
