const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('public/strengths-scene.js','utf8');
const canvas={getContext:()=>({})},section={querySelector:()=>canvas,querySelectorAll:()=>[]};
const {faces,vertices}=vm.runInNewContext(source.slice(0,source.indexOf(' const matrixQuaternion='))+'return {faces,vertices};})();',{document:{querySelector:()=>section},matchMedia:()=>({matches:false})});
assert.equal(vertices.length,48);assert.equal(faces.length,26);assert.equal(faces.filter(f=>f.index>=0).length,6);assert.equal(faces.filter(f=>f.index<0).length,20);
const key=p=>p.map(x=>x.toFixed(7)).join(','),edges=new Map(),lengths=[];
for(const f of faces){assert([4,6,8].includes(f.points.length));for(let i=0;i<f.points.length;i++){const a=f.points[i],b=f.points[(i+1)%f.points.length],edge=[key(a),key(b)].sort().join('|');edges.set(edge,(edges.get(edge)||0)+1);lengths.push(Math.hypot(...a.map((v,j)=>v-b[j])))}assert(f.index<0||f.points.length===6)}
assert.equal(edges.size,72);assert([...edges.values()].every(n=>n===2),'Each edge must join exactly two faces');assert(Math.max(...lengths)-Math.min(...lengths)<1e-7,'All edges must have equal length');assert(vertices.every(p=>Math.abs(Math.hypot(...p)-Math.sqrt(5))<1e-7),'All vertices must lie on one sphere');assert.equal(vertices.length-edges.size+faces.length,2);
console.log('PASS: closed uniform 26-face solid, equal edges and spherical vertex positions, 6 strength hexagons / 20 logo faces');
