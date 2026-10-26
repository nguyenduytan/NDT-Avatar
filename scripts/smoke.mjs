import assert from 'node:assert/strict';
const base=process.env.API_URL||'http://127.0.0.1:8091';
let count=0;
function check(condition,message){assert.ok(condition,message);count++;console.log('PASS '+message);}
const health=await fetch(base+'/health');check(health.status===200,'health ready');
const path='/api/v1/avatar?name=Tony%20Nguy%E1%BB%85n&format=svg';
const svg=await fetch(base+path);const body=await svg.text();
check(svg.status===200&&body.includes('TN</text>'),'Vietnamese HTTP SVG');
check(svg.headers.get('access-control-allow-origin')==='*','CORS');
const etag=svg.headers.get('etag');check(!!etag,'ETag');
const cached=await fetch(base+path,{headers:{'If-None-Match':etag}});check(cached.status===304&&(await cached.text())==='','304 has no body');
const head=await fetch(base+path,{method:'HEAD'});check(head.status===200&&(await head.text())===''&&Number(head.headers.get('content-length'))>0,'HEAD');
const options=await fetch(base+path,{method:'OPTIONS'});check(options.status===204,'OPTIONS');
for(const [path,status] of [['/unknown',404],['/api/v1/avatar?size=0',400],['/api/v1/avatar?name[]=x',400],['/api/v1/avatar?color=%22%3E%3Cscript%3E',400]]){
 const res=await fetch(base+path);check(res.status===status&&!!(await res.json()).error,'error '+path);
}
const post=await fetch(base+'/api/v1/avatar',{method:'POST'});check(post.status===405,'method restriction');
for(const format of ['png','webp','json']){
 const r=await fetch(base+'/api/v1/avatar?name=Đặng%20Ánh&format='+format);check(r.ok,'format '+format);
 const bytes=new Uint8Array(await r.arrayBuffer());
 if(format==='png')check(bytes[0]===137&&bytes[1]===80,'PNG signature');
 if(format==='webp')check(new TextDecoder().decode(bytes.slice(8,12))==='WEBP','WebP signature');
}
const bypass=await fetch(base+path+'&no-cache');check(bypass.headers.get('cache-control')==='no-store','cache bypass');
console.log(`${count} HTTP assertions passed`);
