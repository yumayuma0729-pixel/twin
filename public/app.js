const menuButton=document.querySelector('.menu-button');
const menu=document.querySelector('#menu');
menuButton?.addEventListener('click',()=>{const open=menuButton.getAttribute('aria-expanded')!=='true';menuButton.setAttribute('aria-expanded',String(open));menu.hidden=!open;});
menu?.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>{menu.hidden=true;menuButton.setAttribute('aria-expanded','false');}));
document.querySelectorAll('.chips button[data-category]').forEach(button=>button.addEventListener('click',()=>{const category=button.dataset.category;location.assign('/stock/'+(category&&category!=='all'?'?category='+encodeURIComponent(category):''));}));
document.querySelector('#search-form')?.addEventListener('submit',event=>{event.preventDefault();const params=new URLSearchParams();const data=new FormData(event.currentTarget);for(const key of ['keyword','price']){const value=data.get(key);if(typeof value==='string'&&value.trim())params.set(key,value.trim());}location.assign('/stock/'+(params.size?'?'+params.toString():''));});
const dialog=document.querySelector('#consult-dialog');
const dialogInfo={buy:['購入・注文販売のご相談','相談窓口は公開準備中です。正式なLINE・電話の接続先を確認後、ご案内します。'],sell:['買取・下取りのご相談','相談窓口は公開準備中です。正式なLINE・電話の接続先を確認後、ご案内します。'],line:['LINE簡単相談','公式LINEのURLは現在確認中です。確認後、このボタンから直接ご相談いただけるようになります。'],phone:['電話相談','電話番号は現在確認中です。電話受付時間は13:00〜22:00です。正式な番号の登録後、発信できるようになります。']};
document.querySelectorAll('[data-dialog]').forEach(button=>button.addEventListener('click',()=>{const info=dialogInfo[button.dataset.dialog]||dialogInfo.buy;document.querySelector('#dialog-title').textContent=info[0];const description=document.querySelector('#dialog-description');if(description)description.textContent=info[1];dialog?.showModal();}));
dialog?.querySelectorAll('.dialog-close,.dialog-done').forEach(button=>button.addEventListener('click',()=>dialog.close()));
dialog?.addEventListener('click',event=>{if(event.target===dialog){const rect=dialog.getBoundingClientRect();if(event.clientX<rect.left||event.clientX>rect.right||event.clientY<rect.top||event.clientY>rect.bottom)dialog.close();}});
document.querySelectorAll('a[href^="#faq-"]').forEach(link=>link.addEventListener('click',()=>{const item=document.querySelector(link.getAttribute('href'));if(item)item.open=true;}));

// Progress follows natural scrolling; the content also remains readable without JS.
const scrollStrengths=[...document.querySelectorAll('.scroll-strength')];
if(scrollStrengths.length){const navItems=[...document.querySelectorAll('[data-strength-nav]')];const motion=matchMedia('(prefers-reduced-motion: reduce)');let rafPending=false;const activate=section=>{for(const item of scrollStrengths)item.classList.toggle('is-active',item===section);for(const link of navItems){if(link.dataset.strengthNav===section.id)link.setAttribute('aria-current','step');else link.removeAttribute('aria-current');}const current=document.querySelector('.rail-current');if(current)current.textContent=section.querySelector('.strength-detail-number').textContent;};const update=()=>{rafPending=false;const anchor=innerHeight*.45;const closest=scrollStrengths.reduce((best,section)=>Math.abs(section.getBoundingClientRect().top+section.getBoundingClientRect().height/2-anchor)<Math.abs(best.getBoundingClientRect().top+best.getBoundingClientRect().height/2-anchor)?section:best);activate(closest);};addEventListener('scroll',()=>{if(!rafPending){rafPending=true;requestAnimationFrame(update);}},{passive:true});addEventListener('resize',update);addEventListener('hashchange',update);if('IntersectionObserver' in window&&!motion.matches){const observer=new IntersectionObserver(entries=>{for(const entry of entries)if(entry.isIntersecting){entry.target.classList.remove('has-pending-reveal');observer.unobserve(entry.target);}},{threshold:.12});for(const section of scrollStrengths){if(section.getBoundingClientRect().top>innerHeight)section.classList.add('has-pending-reveal');observer.observe(section);}}update();}

// Keep the dimensional brand mark still while it is offscreen or the tab is hidden.
{
 const section=document.querySelector('#services.strengths-atmosphere');
 if(section?.querySelector('.hex-metal-solid')){
  let visible=false;
  const update=()=>section.classList.toggle('is-motion-visible',visible&&!document.hidden);
  if('IntersectionObserver' in window){new IntersectionObserver(entries=>{visible=entries[0].isIntersecting;update();},{threshold:0.1}).observe(section);}else{visible=true;update();}
  document.addEventListener('visibilitychange',update);
 }
}
