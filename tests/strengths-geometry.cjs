const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('public/strengths-scene.js','utf8');
const canvas={getContext:()=>({})},section={querySelector:()=>canvas,querySelectorAll:()=>[]};
const {faces,vertices}=vm.runInNewContext(source.slice(0,source.indexOf(' const matrixQuaternion='))+'return {faces,vertices};})();',{document:{querySelector:()=>section},matchMedia:()=>({matches:false})});
assert.equal(vertices.length,12);assert.equal(faces.length,20);assert.equal(faces.filter(f=>f.index>=0).length,6);assert.equal(faces.filter(f=>f.index<0).length,14);
const key=p=>p.map(x=>x.toFixed(7)).join(','),edges=new Map(),lengths=[];
for(const f of faces){assert.equal(f.points.length,3);for(let i=0;i<3;i++){const a=f.points[i],b=f.points[(i+1)%3],edge=[key(a),key(b)].sort().join('|');edges.set(edge,(edges.get(edge)||0)+1);lengths.push(Math.hypot(...a.map((v,j)=>v-b[j])))}assert(f.n.reduce((sum,x,i)=>sum+x*f.c[i],0)>0)}
assert.equal(edges.size,30);assert([...edges.values()].every(n=>n===2),'Each edge must join two faces');assert(Math.max(...lengths)-Math.min(...lengths)<1e-7,'Edges must be equal');assert(vertices.every(p=>Math.abs(Math.hypot(...p)-Math.sqrt(5))<1e-7));assert.equal(vertices.length-edges.size+faces.length,2);
console.log('PASS: closed regular icosahedron, 12 spherical vertices / 30 equal edges / 20 triangles; 6 strengths / 14 logos');
