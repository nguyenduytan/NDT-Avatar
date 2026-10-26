import type {Messages} from '@/lib/i18n';
import type {Settings} from '@/lib/avatar';
type Props={s:Settings;t:Messages;update:(key:keyof Settings,value:string|number|boolean)=>void};
export default function Controls({s,t,update}:Props){
 return <div className="controls">
  <label className="field">{t.name}<input maxLength={128} value={s.name} onChange={e=>update('name',e.target.value)} placeholder="Tony Nguyễn"/></label>
  <fieldset><legend>{t.style}</legend><div className="segments">{['initials','identicon','rings'].map(k=><button type="button" key={k} aria-pressed={s.style===k} className={s.style===k?'selected':''} onClick={()=>update('style',k)}>{t[k as 'initials']}</button>)}</div></fieldset>
  {s.style!=='initials'&&<label className="field">{t.seed}<input value={s.seed} maxLength={128} onChange={e=>update('seed',e.target.value)} placeholder={s.name}/></label>}
  <div className="field"><label htmlFor="size">{t.size}<output>{s.size} × {s.size}</output></label><input id="size" type="range" min="16" max="2048" step="16" value={s.size} onChange={e=>update('size',+e.target.value)}/></div>
  <h4>{t.appearance}</h4><div className="two-fields">
   <label className="field">{t.background}<div className="color-input"><input type="color" aria-label={t.background} value={/^\w{6}$/.test(s.background)?'#'+s.background:'#e95365'} onChange={e=>update('background',e.target.value.slice(1))}/><span>{['random','transparent'].includes(s.background)?s.background:'#'+s.background}</span></div></label>
   <label className="field">{t.foreground}<div className="color-input"><input type="color" aria-label={t.foreground} value={s.color==='auto'?'#ffffff':'#'+s.color} onChange={e=>update('color',e.target.value.slice(1))}/><span>{s.color==='auto'?'auto':'#'+s.color}</span></div></label>
  </div>
  <div className="check-row"><label><input type="checkbox" checked={s.background==='random'} onChange={e=>update('background',e.target.checked?'random':'e95365')}/>{t.randomColor}</label><label><input type="checkbox" checked={s.color==='auto'} onChange={e=>update('color',e.target.checked?'auto':'ffffff')}/>{t.autoColor}</label></div>
  <div className="check-row"><label><input type="checkbox" checked={s.background==='transparent'} onChange={e=>update('background',e.target.checked?'transparent':'e95365')}/>{t.transparent}</label><label><input type="checkbox" checked={!!s.gradient} onChange={e=>update('gradient',e.target.checked?'9d3768':'')}/>{t.enableGradient}</label></div>
  {s.gradient&&<label className="field">{t.gradient}<input type="color" value={'#'+s.gradient} onChange={e=>update('gradient',e.target.value.slice(1))}/></label>}
  <div className="field"><label htmlFor="radius">{t.radius}<output>{s.radius}%</output></label><input id="radius" type="range" min="0" max="50" value={s.radius} onChange={e=>update('radius',+e.target.value)}/></div>
  {s.style==='initials'&&<><h4>{t.typography}</h4><div className="two-fields"><label className="field">{t.length}<select value={s.length} onChange={e=>update('length',+e.target.value)}>{[1,2,3,4,5,6,7,8].map(n=><option key={n}>{n}</option>)}</select></label><label className="field">{t.fontSize}<select value={s.fontSize} onChange={e=>update('fontSize',+e.target.value)}>{[.2,.3,.4,.4375,.5,.6,.8].map(n=><option key={n} value={n}>{Math.round(n*100)}%</option>)}</select></label></div><div className="check-row"><label><input type="checkbox" checked={s.bold} onChange={e=>update('bold',e.target.checked)}/>{t.bold}</label><label><input type="checkbox" checked={s.uppercase} onChange={e=>update('uppercase',e.target.checked)}/>{t.uppercase}</label></div></>}
  <label className="field">{t.format}<select value={s.format} onChange={e=>update('format',e.target.value)}>{['svg','png','webp','json'].map(f=><option key={f} value={f}>{f.toUpperCase()}</option>)}</select></label>
 </div>;
}
