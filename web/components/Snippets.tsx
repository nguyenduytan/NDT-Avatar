'use client';
import {useState} from 'react';
import {Copy,Check} from 'lucide-react';
import type {Messages} from '@/lib/i18n';
export function CopyButton({text,t}:{text:string;t:Messages}){
 const [state,setState]=useState('');
 async function copy(){try{await navigator.clipboard.writeText(text);setState('ok');setTimeout(()=>setState(''),2200);}catch{setState('error');}}
 return <><button className="copy-button" onClick={copy}>{state==='ok'?<Check size={15}/>:<Copy size={15}/>} {state==='ok'?t.copied:t.copy}</button>{state==='error'&&<span role="alert">{t.copyFail}</span>}</>;
}
export default function Snippets({url,t}:{url:string;t:Messages}){
 const [tab,setTab]=useState('URL');
 const html=url.replaceAll('&','&amp;').replaceAll('"','&quot;');
 const snippets:Record<string,string>={URL:url,HTML:`<img src="${html}"\n     width="64" height="64" alt="Avatar" />`,Markdown:`![Avatar](${url})`,cURL:`curl --fail '${url.replaceAll("'",'%27')}' -o avatar`,JavaScript:`const response = await fetch(${JSON.stringify(url)});\nif (!response.ok) throw new Error('Avatar failed');\nconst avatar = await response.blob();`};
 return <div className="snippet"><div className="snippet-toolbar"><div className="tabs" role="tablist" aria-label="Integration">{Object.keys(snippets).map(k=><button role="tab" aria-selected={k===tab} key={k} onClick={()=>setTab(k)}>{k}</button>)}</div><CopyButton text={snippets[tab]} t={t}/></div><pre role="tabpanel"><code>{snippets[tab]}</code></pre></div>;
}
