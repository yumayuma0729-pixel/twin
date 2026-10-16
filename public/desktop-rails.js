// Desktop forms stay in this document: no personal information in URLs or storage.
const railForm=document.querySelector('#rail-inquiry-form');
if(railForm){
 const fields=railForm.querySelector('fieldset');
 const review=document.querySelector('#rail-confirmation');
 const summary=document.querySelector('#rail-confirm-list');
 const topic=document.querySelector('#rail-type');
 const context=document.querySelector('#rail-vehicle-context');
 const params=new URLSearchParams(location.search);
 if([...topic.options].some(option=>option.value===params.get('type')))topic.value=params.get('type');
 const vehiclePage=/^\/stock\/demo-(kei|commercial|passenger)-0[12]\/$/.test(location.pathname);
 if(vehiclePage){context.hidden=false;context.textContent='ご覧の車両：'+document.querySelector('h1').textContent;topic.value='vehicle';}
 railForm.addEventListener('submit',event=>{
  event.preventDefault();
  for(const input of railForm.querySelectorAll('input:not([type=checkbox]),textarea'))input.value=input.value.trim();
  if(!railForm.reportValidity())return;
  const values=[['ご相談の内容',topic.selectedOptions[0].textContent],...(vehiclePage?[['ご覧の車両',document.querySelector('h1').textContent]]:[]),['お名前',railForm.elements.name.value],['メールアドレス',railForm.elements.email.value],['ご相談内容',railForm.elements.message.value]];
  summary.replaceChildren();
  for(const [label,value] of values){const dt=document.createElement('dt');dt.textContent=label;const dd=document.createElement('dd');dd.textContent=value;summary.append(dt,dd);}
  railForm.hidden=true;review.hidden=false;review.focus();
 });
 document.querySelector('#rail-edit').addEventListener('click',()=>{review.hidden=true;railForm.hidden=false;document.querySelector('#rail-name').focus();});
 fields.disabled=false;
}
for(const link of document.querySelectorAll('.rail-service-nav a')){
 const url=new URL(link.href);
 if(location.pathname===url.pathname||(url.pathname==='/stock/'&&location.pathname.startsWith('/stock/')))link.setAttribute('aria-current','page');
}
