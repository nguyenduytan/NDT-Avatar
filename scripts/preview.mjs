// Local-only preview of the static Next.js export with the real PHP API.
import http from 'node:http';
import fs from 'node:fs/promises';
import path from 'node:path';
const root=path.resolve('web/out');
const types={'.html':'text/html; charset=utf-8','.css':'text/css','.js':'text/javascript','.svg':'image/svg+xml','.json':'application/json','.txt':'text/plain','.png':'image/png','.woff2':'font/woff2'};
http.createServer(async(req,res)=>{
 try{
  const pathname=new URL(req.url,'http://localhost').pathname;
  if(pathname.startsWith('/api')||pathname==='/health'){
   const target=http.request('http://127.0.0.1:8091'+req.url,{method:req.method,headers:req.headers},r=>{res.writeHead(r.statusCode,r.headers);r.pipe(res);});
   target.on('error',()=>{res.writeHead(502);res.end('PHP API unavailable');});req.pipe(target);return;
  }
  let file=path.resolve(root,'.'+decodeURIComponent(pathname));
  if(file!==root&&!file.startsWith(root+path.sep)){res.writeHead(403);res.end();return;}
  if((await fs.stat(file)).isDirectory())file=path.join(file,'index.html');
  res.setHeader('Content-Type',types[path.extname(file)]||'application/octet-stream');res.end(await fs.readFile(file));
 }catch{res.writeHead(404);res.end('Not found');}
}).listen(3000,'127.0.0.1',()=>console.log('Preview http://127.0.0.1:3000'));
