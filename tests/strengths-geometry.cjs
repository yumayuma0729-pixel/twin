const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('public/strengths-scene.js','utf8');
const canvas={getContext:()=>({})},section={querySelector:()=>canvas,querySelectorAll:()=>[]};
const {faces,vertices}=vm.runInNewContext(source.slice(0,source.indexOf(' const matrixQuaternion='))+'return {faces,vertices};})();',{document:{querySelector:()=>section},matchMedia:()=>({matches:false})});
assert.equal(vertices.length,24);assert.equal(faces.length,14);assert.equal(faces.filter(f=>f.points.length===6).length,8);assert.equal(faces.filter(f=>f.points.length===4).length,6);assert.equal(faces.filter(f=>f.index>=0).length,6);assert.equal(faces.filter(f=>f.index<0).length,8);
const key=p=>p.map(x=>x.toFixed(7)).join(','),edges=new Map(),lengths=[],areas=[];
for(const f of faces){assert([4,6].includes(f.points.length));assert(f.index<0||f.points.length===6);for(let i=0;i<f.points.length;i++){const a=f.points[i],b=f.points[(i+1)%f.points.length],edge=[key(a),key(b)].sort().join('|');edges.set(edge,(edges.get(edge)||0)+1);lengths.push(Math.hypot(...a.map((v,j)=>v-b[j])))}const r=Math.hypot(...f.points[0].map((x,i)=>x-f.c[i]));areas.push(f.points.length*r*r*Math.sin(2*Math.PI/f.points.length)/2)}
assert.equal(edges.size,36);assert([...edges.values()].every(n=>n===2),'Shared edges must close without gaps');assert(Math.max(...lengths)-Math.min(...lengths)<1e-7);assert(vertices.every(p=>Math.abs(Math.hypot(...p)-Math.sqrt(5))<1e-7));assert.equal(vertices.length-edges.size+faces.length,2);
console.log('PASS: restored closed 14-face polyhedron, equal edges and original 6 strength hexagons');
