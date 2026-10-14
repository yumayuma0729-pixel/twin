const menuButton=document.querySelector('.menu-button');
const menu=document.querySelector('#menu');
menuButton.addEventListener('click',()=>{const open=menuButton.getAttribute('aria-expanded')!=='true';menuButton.setAttribute('aria-expanded',String(open));menu.hidden=!open;});
menu.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>{menu.hidden=true;menuButton.setAttribute('aria-expanded','false');}));
document.querySelectorAll('[data-category]').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('[data-category]').forEach(other=>other.setAttribute('aria-pressed',String(other===button)));}));
document.querySelector('#search-form').addEventListener('submit',event=>{event.preventDefault();const result=document.querySelector('#search-result');result.hidden=false;result.textContent='現在、公開中の在庫はありません。ご希望の車種・予算は、下のトータルサポート案内をご覧ください。';});
const dialog=document.querySelector('#consult-dialog');
document.querySelectorAll('[data-dialog]').forEach(button=>button.addEventListener('click',()=>{document.querySelector('#dialog-title').textContent=button.dataset.dialog==='buy'?'注文販売のご相談':'買取・下取りのご相談';dialog.showModal();}));
dialog.querySelectorAll('.dialog-close,.dialog-done').forEach(button=>button.addEventListener('click',()=>dialog.close()));
dialog.addEventListener('click',event=>{if(event.target===dialog){const rect=dialog.getBoundingClientRect();if(event.clientX<rect.left||event.clientX>rect.right||event.clientY<rect.top||event.clientY>rect.bottom)dialog.close();}});

document.querySelectorAll('[data-category-link]').forEach(link=>link.addEventListener('click',()=>{const choice=document.querySelector('[data-category="'+link.dataset.categoryLink+'"]');if(choice)choice.click();}));
document.querySelectorAll('a[href^="#faq-"]').forEach(link=>link.addEventListener('click',()=>{const item=document.querySelector(link.getAttribute('href'));if(item)item.open=true;}));
