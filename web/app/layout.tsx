import type { Metadata } from 'next';
import './globals.css';
export const metadata: Metadata = {
 title:'NDT Avatar — Small images. Big identity.',
 description:'Generate beautiful initials, identicons and abstract avatars. A fast, databaseless PHP API with a bilingual playground. Self-host on your VPS.',
 icons:{icon:'/icon.svg'},
};
export default function RootLayout({children}:{children:React.ReactNode}) { return <html lang="en"><body>{children}</body></html>; }
