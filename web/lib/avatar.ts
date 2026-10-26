export type Settings = {name:string;style:string;size:number;background:string;color:string;radius:number;length:number;fontSize:number;bold:boolean;uppercase:boolean;format:string;gradient:string;seed:string};
export const defaults:Settings={name:'Tony Nguyễn',style:'initials',size:256,background:'e95365',color:'ffffff',radius:24,length:2,fontSize:.4,bold:true,uppercase:true,format:'svg',gradient:'',seed:''};
export const apiBase=process.env.NEXT_PUBLIC_API_BASE || '/api/v1/avatar';
export function query(s:Settings):URLSearchParams {
 const p=new URLSearchParams({name:s.name,style:s.style,size:String(s.size),background:s.background,color:s.color,radius:String(s.radius),length:String(s.length),'font-size':String(s.fontSize),bold:String(s.bold),uppercase:String(s.uppercase),format:s.format});
 if(s.gradient) p.set('gradient',s.gradient); if(s.seed) p.set('seed',s.seed); return p;
}
export function avatarUrl(s:Settings){return `${apiBase}?${query(s)}`;}
export const presets=[{name:'Rose',bg:'e95365',fg:'ffffff'},{name:'Ocean',bg:'3266aa',fg:'dbeafe'},{name:'Forest',bg:'24685c',fg:'dcfce7'},{name:'Lavender',bg:'7c65a9',fg:'f5f0ff'},{name:'Amber',bg:'f4bc68',fg:'382816'},{name:'Midnight',bg:'202a3b',fg:'d8e4f9'}];
