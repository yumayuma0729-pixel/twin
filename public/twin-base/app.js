(()=>{
 'use strict';
 const settings=window.TWIN_BASE_SETTINGS||{},toggle=document.querySelector('.tb-menu-toggle'),menu=document.querySelector('#tb-menu'),dialog=document.querySelector('#tb-social-dialog');
 const setMenu=open=>{toggle?.setAttribute('aria-expanded',String(open));if(menu)menu.hidden=!open;document.body.classList.toggle('tb-menu-open',open)};
 toggle?.addEventListener('click',()=>setMenu(toggle.getAttribute('aria-expanded')!=='true'));
 menu?.querySelectorAll('a[href^="#"]').forEach(a=>a.addEventListener('click',()=>setMenu(false)));
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!dialog?.open&&toggle?.getAttribute('aria-expanded')==='true'){setMenu(false);toggle.focus()}});
 const desktop=matchMedia('(min-width:768px)');if(desktop.addEventListener)desktop.addEventListener('change',e=>{if(e.matches)setMenu(false)});
 let lastTrigger;
 const closeDialog=()=>{dialog?.close();lastTrigger?.focus()};
 document.querySelector('.tb-dialog-close')?.addEventListener('click',closeDialog);
 dialog?.addEventListener('click',event=>{if(event.target===dialog){const r=dialog.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)closeDialog()}});
 dialog?.querySelector('[data-close-dialog]')?.addEventListener('click',()=>{setMenu(false);dialog.close()});
 const socialUrl=(value,kind)=>{try{const url=new URL(value);const domains=kind==='instagram'?['instagram.com','www.instagram.com']:['line.me','www.line.me','lin.ee','liff.line.me'];return url.protocol==='https:'&&domains.includes(url.hostname)&&!url.username&&!url.password?url.href:null}catch{return null}};
 document.querySelectorAll('[data-social]').forEach(button=>{const kind=button.dataset.social,url=socialUrl(settings[kind+'Url'],kind);if(url){const link=document.createElement('a');for(const a of button.attributes)if(!['type','data-social'].includes(a.name))link.setAttribute(a.name,a.value);link.href=url;link.target='_blank';link.rel='noopener noreferrer';link.innerHTML=button.innerHTML;button.replaceWith(link)}else button.addEventListener('click',()=>{lastTrigger=button;const label=kind==='instagram'?'Instagram':'公式LINE';document.querySelector('#tb-dialog-title').textContent=label+'のご案内';document.querySelector('#tb-dialog-text').textContent=label+'は、ただいま公開準備中です。正式なアカウントのご案内まで、もうしばらくお待ちください。';dialog.showModal()})});
 const localAsset=value=>{if(typeof value!=='string'||!value.startsWith('/twin-base/assets/')||value.includes('..'))return null;return value};
 document.querySelectorAll('[data-base-asset]').forEach(img=>{const key=img.dataset.baseAsset,path=localAsset(settings[key==='logo'?'logoPath':'facilityPhotoPath']);if(!path)return;img.addEventListener('load',()=>{img.hidden=false;if(key==='logo')img.parentElement.querySelector('.tb-brand-fallback')?.setAttribute('hidden','');else img.parentElement.querySelector('.tb-photo-pending')?.setAttribute('hidden','')});img.addEventListener('error',()=>{img.hidden=true;img.removeAttribute('src')});img.src=path});
 const reduced=matchMedia('(prefers-reduced-motion:reduce)');
 if('IntersectionObserver' in window&&!reduced.matches){document.documentElement.classList.add('tb-motion');const observer=new IntersectionObserver(entries=>{entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('tb-visible');observer.unobserve(entry.target)}})},{threshold:.08});document.querySelectorAll('.tb-reveal').forEach(e=>observer.observe(e));if(reduced.addEventListener)reduced.addEventListener('change',e=>{if(e.matches){document.documentElement.classList.remove('tb-motion');observer.disconnect()}})}
})();
