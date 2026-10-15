// Category links are the selection; render matching demo cars immediately.
const form=document.querySelector('#stock-filter');
if(form){
 const params=new URLSearchParams(location.search);
 const labels={kei:'軽自動車',commercial:'商用車（軽バン・トラック）',passenger:'乗用車'};
 const aliases={van:'commercial',truck:'commercial'};
 let category=params.get('category')||'';
 if(aliases[category]){category=aliases[category];params.set('category',category);history.replaceState(null,'',location.pathname+'?'+params.toString());}
 if(category&&!labels[category]){category='';params.delete('category');history.replaceState(null,'',location.pathname+(params.size?'?'+params.toString():''));}
 const keyword=(params.get('keyword')||'').slice(0,100);
 const price=['under100','100to150','over150'].includes(params.get('price'))?params.get('price'):'';
 const sort=['price-asc','price-desc'].includes(params.get('sort'))?params.get('sort'):'newest';
 for(const [name,value] of Object.entries({category,keyword,price,sort})){const control=form.elements.namedItem(name);if(control)control.value=value;}
 const grid=document.querySelector('#vehicle-grid');
 const cards=[...grid.querySelectorAll('[data-vehicle-card]')];
 const tokens=keyword.toLocaleLowerCase('ja').split(/\s+/).filter(Boolean);
 let count=0;
 for(const card of cards){const amount=Number(card.dataset.price);const words=card.dataset.keywords.toLocaleLowerCase('ja');const matches=(!category||card.dataset.category===category)&&tokens.every(t=>words.includes(t))&&(!price||(price==='under100'&&amount<1000000)||(price==='100to150'&&amount>=1000000&&amount<1500000)||(price==='over150'&&amount>=1500000));card.hidden=!matches;if(matches)count++;}
 cards.sort((a,b)=>sort==='price-asc'?Number(a.dataset.price)-Number(b.dataset.price):sort==='price-desc'?Number(b.dataset.price)-Number(a.dataset.price):Number(a.dataset.order)-Number(b.dataset.order)).forEach(card=>grid.append(card));
 const title=category?labels[category]+'のサンプル在庫':'サンプル在庫一覧';
 document.querySelector('#inventory-title').textContent=title;
 document.title=title+' | Twin Assist';
 document.querySelector('#inventory-results-title').textContent=category?labels[category]+'の掲載サンプル':'すべての掲載サンプル';
 document.querySelector('#inventory-description').textContent=(category?labels[category]+'の条件で表示しています。':'各カテゴリのサンプルをご覧いただけます。')+'掲載内容は制作確認用で、実在庫ではありません。';
 document.querySelector('#stock-filter-summary').textContent='サンプル '+count+'台'+(keyword?' ／ 「'+keyword+'」':'');
 document.querySelector('#inventory-empty').hidden=count!==0;
 for(const link of document.querySelectorAll('[data-inventory-category]')){if(link.dataset.inventoryCategory===category)link.setAttribute('aria-current','page');else link.removeAttribute('aria-current');}
}
