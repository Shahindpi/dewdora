import Link from "next/link";
import type { ReactNode } from "react";

const links = [["Articles", "/posts"], ["Products", "/products"], ["Categories", "/categories"], ["Brands", "/brands"], ["Search", "/search"]] as const;
export function SiteShell({ children }: { children: ReactNode }) {
  return <div className="min-h-screen bg-[#f8f8f2] text-[#18352d]">
    <header className="border-b border-[#dce6d9] bg-white/90"><div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-5 px-6 py-5">
      <Link href="/" className="text-2xl font-black tracking-tight text-[#165e46]">Dewdora<span className="text-[#e8a854]">.</span></Link>
      <nav aria-label="Main navigation" className="flex flex-wrap gap-5 text-sm font-semibold">{links.map(([label, href]) => <Link key={href} href={href} className="hover:text-[#2c9873]">{label}</Link>)}<Link href="/contact" className="hover:text-[#2c9873]">Contact</Link></nav>
    </div></header>
    <main className="mx-auto max-w-6xl px-6 py-12">{children}</main>
    <footer className="mt-16 border-t border-[#dce6d9] bg-white"><div className="mx-auto flex max-w-6xl flex-wrap justify-between gap-4 px-6 py-8 text-sm"><span>© {new Date().getFullYear()} Dewdora</span><div className="flex gap-5"><Link href="/contact">Contact</Link><Link href="/auth/login">Admin</Link></div></div></footer>
  </div>;
}
