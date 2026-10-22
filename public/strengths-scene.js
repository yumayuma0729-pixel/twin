(()=>{
 const section=document.querySelector('.strengths-scene'),canvas=section?.querySelector('canvas');
 if(!canvas)return;const ctx=canvas.getContext('2d');if(!ctx)return;
 const copies=[...section.querySelectorAll('[data-strength-face]')],links=[...section.querySelectorAll('[data-strength-jump]')];
 const reduced=matchMedia('(prefers-reduced-motion: reduce)'),labels=['車を買う','車を売る','車検','整備・修理','ロードサービス','洗車・コーティング'];
 const en=['BUY','SELL','INSPECTION','MAINTENANCE','ROADSIDE','CAR CARE'];
 const dot=(a,b)=>a.reduce((s,v,i)=>s+v*b[i],0),cross=(a,b)=>[a[1]*b[2]-a[2]*b[1],a[2]*b[0]-a[0]*b[2],a[0]*b[1]-a[1]*b[0]],norm=a=>{const l=Math.hypot(...a);return a.map(v=>v/l)},add=(a,b)=>a.map((v,i)=>v+b[i]);
 const vertices=[];for(let zero=0;zero<3;zero++)for(const swap of [false,true])for(const a of [-1,1])for(const b of [-2,2]){const v=[0,0,0],other=[0,1,2].filter(i=>i!==zero);v[other[0]]=swap?b:a;v[other[1]]=swap?a:b;vertices.push(v)}
 const normals=[];for(const x of [-1,1])for(const y of [-1,1])for(const z of [-1,1])normals.push([x,y,z]);
 const selected=[[1,-1,1],[-1,-1,1],[-1,1,1],[1,1,1],[1,1,-1],[1,-1,-1]];
 const faces=[...normals,...[[1,0,0],[-1,0,0],[0,1,0],[0,-1,0],[0,0,1],[0,0,-1]]].map(raw=>{
  const limit=raw.filter(Boolean).length===3?3:2,n=norm(raw),c=n.map(v=>v*limit/Math.hypot(...raw));
  const u=norm(cross(Math.abs(n[1])>.9?[1,0,0]:[0,1,0],n)),v=cross(n,u);
  const points=vertices.filter(p=>Math.abs(dot(p,raw)-limit)<.001).sort((a,b)=>Math.atan2(dot(a,v),dot(a,u))-Math.atan2(dot(b,v),dot(b,u)));
  return {n,c,u,v,points,index:selected.findIndex(s=>s.every((x,i)=>x===raw[i]))};
 });
 const matrixQuaternion=m=>{
  const trace=m[0]+m[4]+m[8];let x,y,z,w,s;
  if(trace>0){s=2*Math.sqrt(trace+1);w=s/4;x=(m[7]-m[5])/s;y=(m[2]-m[6])/s;z=(m[3]-m[1])/s}
  else if(m[0]>m[4]&&m[0]>m[8]){s=2*Math.sqrt(1+m[0]-m[4]-m[8]);w=(m[7]-m[5])/s;x=s/4;y=(m[1]+m[3])/s;z=(m[2]+m[6])/s}
  else if(m[4]>m[8]){s=2*Math.sqrt(1+m[4]-m[0]-m[8]);w=(m[2]-m[6])/s;x=(m[1]+m[3])/s;y=s/4;z=(m[5]+m[7])/s}
  else{s=2*Math.sqrt(1+m[8]-m[0]-m[4]);w=(m[3]-m[1])/s;x=(m[2]+m[6])/s;y=(m[5]+m[7])/s;z=s/4}return norm([x,y,z,w]);
 };
 const orientations=selected.map((_,i)=>{const face=faces.find(f=>f.index===i);return matrixQuaternion([...face.u,...face.v,...face.n])});
 const slerp=(a,b,t)=>{let d=dot(a,b);if(d<0){b=b.map(v=>-v);d=-d}if(d>.999)return norm(a.map((v,i)=>v+(b[i]-v)*t));const angle=Math.acos(Math.min(1,d)),s=Math.sin(angle);return a.map((v,i)=>(v*Math.sin((1-t)*angle)+b[i]*Math.sin(t*angle))/s)};
 const rotate=(p,q)=>{const xyz=q.slice(0,3),t=cross(xyz,p).map(v=>2*v);return add(add(p,t.map(v=>v*q[3])),cross(xyz,t))};
 const logo=new Image();logo.src='/images/twin-logo-mark.png';logo.onload=()=>schedule();
 let progress=0,target=0,frame=0,active=-1,enhanced=false,inView=false;
 function setActive(i){if(active===i)return;active=i;copies.forEach((e,j)=>{e.hidden=enhanced&&j!==i});links.forEach((e,j)=>{if(i===j)e.setAttribute('aria-current','true');else e.removeAttribute('aria-current')})}
 function draw(value){
  const rect=canvas.getBoundingClientRect(),dpr=Math.min(devicePixelRatio||1,2),w=rect.width,h=rect.height;if(!w||!h)return;
  if(canvas.width!==Math.round(w*dpr)||canvas.height!==Math.round(h*dpr)){canvas.width=Math.round(w*dpr);canvas.height=Math.round(h*dpr)}
  ctx.setTransform(dpr,0,0,dpr,0,0);ctx.clearRect(0,0,w,h);
  const step=Math.min(4,Math.floor(value*5)),t=value===1?1:value*5-step,q=slerp(orientations[step],orientations[step+1],t*t*(3-2*t));
  const size=Math.min(w*.19,h*.19),camera=10,project=p=>[w*.5+p[0]*size*camera/(camera-p[2]),h*.47-p[1]*size*camera/(camera-p[2])];
  ctx.save();ctx.translate(w*.5,h*.92);ctx.scale(1,.14);const shadow=ctx.createRadialGradient(0,0,0,0,0,size*2);shadow.addColorStop(0,'#0009');shadow.addColorStop(1,'#0000');ctx.fillStyle=shadow;ctx.beginPath();ctx.arc(0,0,size*2,0,Math.PI*2);ctx.fill();ctx.restore();
  const ordered=faces.map(f=>({...f,rn:rotate(f.n,q),rc:rotate(f.c,q),rp:f.points.map(p=>rotate(p,q))})).filter(f=>f.rn[2]>.01).sort((a,b)=>a.rc[2]-b.rc[2]);
  for(const face of ordered){const points=face.rp.map(project),light=Math.max(0,dot(face.rn,norm([-.6,.8,1]))),metal=Math.round(35+light*85),isHex=face.points.length===6;
   const gradient=ctx.createLinearGradient(points[0][0],points[0][1],points[2][0],points[2][1]);
   gradient.addColorStop(0,`rgb(${metal+22},${metal+27},${metal+31})`);gradient.addColorStop(.46,`rgb(${metal},${metal+5},${metal+10})`);gradient.addColorStop(1,`rgb(${Math.max(18,metal-25)},${Math.max(22,metal-20)},${Math.max(26,metal-15)})`);
   ctx.beginPath();points.forEach((p,i)=>i?ctx.lineTo(...p):ctx.moveTo(...p));ctx.closePath();ctx.fillStyle=gradient;ctx.fill();ctx.strokeStyle=isHex?'#dce3e790':'#8c969e70';ctx.lineWidth=isHex?1.2:.8;ctx.stroke();
   if(!isHex||face.rn[2]<.23)continue;
   const c=project(face.rc),u=project(rotate(add(face.c,face.u),q)),v=project(rotate(add(face.c,face.v),q));ctx.save();ctx.transform(u[0]-c[0],u[1]-c[1],c[0]-v[0],c[1]-v[1],c[0],c[1]);ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillStyle='#f1f3f4';
   if(face.index>=0){const i=face.index;ctx.font='500 .26px Arial,sans-serif';ctx.fillStyle='#c3cdd4';ctx.fillText('0'+(i+1)+' / '+en[i],0,-.46);ctx.fillStyle='#fff';ctx.font='600 .34px Arial,"Noto Sans JP",sans-serif';if(i===5){ctx.fillText('洗車・',0,-.04);ctx.fillText('コーティング',0,.36)}else{ctx.fillText(labels[i],0,.08)}ctx.strokeStyle='#ffffff50';ctx.lineWidth=.015;ctx.beginPath();ctx.moveTo(-.5,.66);ctx.lineTo(.5,.66);ctx.stroke();}
   else if(logo.complete&&logo.naturalWidth){ctx.drawImage(logo,-.8,-.8,1.6,1.6)}ctx.restore();
  }
 }
 function render(){frame=0;progress=reduced.matches?target:progress+(target-progress)*.18;if(Math.abs(target-progress)<.0002)progress=target;draw(progress);if(progress!==target&&inView)schedule()}
 function schedule(){if(!frame)frame=requestAnimationFrame(render)}
 function update(){const r=section.getBoundingClientRect();inView=r.bottom>0&&r.top<innerHeight;if(enhanced){target=Math.max(0,Math.min(1,-r.top/(section.offsetHeight-innerHeight)));setActive(Math.round(target*5))}if(inView)schedule()}
 function configure(){enhanced=!reduced.matches&&innerHeight>=650;section.dataset.enhanced=String(enhanced);active=-1;setActive(Math.round(target*5));copies.forEach((e,i)=>e.hidden=enhanced&&i!==active);update()}
 links.forEach((link,i)=>link.addEventListener('click',event=>{if(enhanced){event.preventDefault();const top=scrollY+section.getBoundingClientRect().top+(section.offsetHeight-innerHeight)*i/5;window.scrollTo({top,behavior:'instant'})}else{target=i/5;setActive(i);schedule()}}));
 addEventListener('scroll',update,{passive:true});addEventListener('resize',configure);reduced.addEventListener('change',configure);configure();draw(0);
})();
