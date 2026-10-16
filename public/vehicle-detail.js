const photoDialog=document.querySelector('#vehicle-photo-dialog');
const gallery=document.querySelector('.vehicle-gallery');
if(photoDialog&&gallery){
 const labels=['外装｜フロント','外装｜リア','内装｜運転席','内装｜後席・荷室'];
 const thumbs=[...gallery.querySelectorAll('[data-photo-index]')];
 let selected=0;
 const select=index=>{selected=(index+labels.length)%labels.length;const x=`${selected*100/3}%`;const y=`${Number(gallery.dataset.galleryRow)*100/3}%`;for(const frame of [gallery.querySelector('.vehicle-gallery-main .gallery-photo-frame'),photoDialog.querySelector('.gallery-photo-frame')]){frame.style.setProperty('--photo-x',x);frame.style.setProperty('--photo-y',y);frame.setAttribute('aria-label',`${labels[selected]}・生成サンプル`);}thumbs.forEach((button,i)=>button.setAttribute('aria-pressed',String(i===selected)));const count=`${String(selected+1).padStart(2,'0')} / 04`;gallery.querySelector('.gallery-count').textContent=count;photoDialog.querySelector('.dialog-photo-count').textContent=count;gallery.querySelector('.gallery-current-caption').textContent=labels[selected];photoDialog.querySelector('h2').textContent=labels[selected]+'｜生成サンプル';};
 thumbs.forEach((button,index)=>button.addEventListener('click',()=>select(index)));
 gallery.querySelector('[data-photo-open]').addEventListener('click',()=>photoDialog.showModal());
 photoDialog.querySelector('.photo-dialog-close').addEventListener('click',()=>photoDialog.close());
 photoDialog.querySelector('[data-gallery-prev]').addEventListener('click',()=>select(selected-1));
 photoDialog.querySelector('[data-gallery-next]').addEventListener('click',()=>select(selected+1));
 photoDialog.addEventListener('keydown',event=>{if(event.key==='ArrowLeft'){event.preventDefault();select(selected-1)}if(event.key==='ArrowRight'){event.preventDefault();select(selected+1)}});
 photoDialog.addEventListener('click',event=>{if(event.target===photoDialog){const r=photoDialog.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)photoDialog.close();}});
}
