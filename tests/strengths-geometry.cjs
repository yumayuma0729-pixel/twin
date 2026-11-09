const fs=require('node:fs'),vm=require('node:vm'),assert=require('node:assert/strict');
const source=fs.readFileSync('public/strengths-scene.js','utf8');
const canvas={getContext:()=>({})},section={querySelector:()=>canvas,querySelectorAll:()=>[]};
const {faces}=vm.runInNewContext(source.slice(0,source.indexOf(' const matrixQuaternion='))+'return {faces};})();',{document:{querySelector:()=>section},matchMedia:()=>({matches:false})});
assert.equal(faces.length,26);assert.equal(faces.filter(f=>f.index>=0).length,6);assert.equal(faces.filter(f=>f.index<0).length,20);
for(const face of faces){assert.equal(face.points.length,6);const distances=face.points.map(p=>Math.hypot(...p.map((x,i)=>x-face.c[i])));assert(Math.max(...distances)-Math.min(...distances)<1e-7);const edges=face.points.map((p,i)=>Math.hypot(...p.map((x,j)=>x-face.points[(i+1)%6][j])));assert(Math.max(...edges)-Math.min(...edges)<1e-7);assert(face.points.every(p=>Math.abs(p.reduce((n,x,i)=>n+(x-face.c[i])*face.n[i],0))<1e-7));}
console.log('PASS: all 26 panels are planar regular hexagons; 6 strengths and 20 logo panels');
