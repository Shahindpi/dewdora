import Link from "next/link";
import type { ReactNode } from "react";

const links = [["Products", "/products"], ["Categories", "/categories"], ["Reviews & Guides", "/posts"], ["Brands", "/brands"], ["Search", "/search"]] as const;
export function SiteShell({ children }: { children: ReactNode }) {
  return <div className="min-h-screen bg-[#f8f8f2] text-[#18352d]">
    <header className="border-b border-[#dce6d9] bg-white/90"><div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-5 px-6 py-5">
      <Link href="/" className="text-2xl font-black tracking-tight text-[#165e46]">Dewdora<span className="text-[#e8a854]">.</span></Link>
      <nav aria-label="Main navigation" className="hidden flex-wrap gap-5 text-sm font-semibold md:flex">{links.map(([label, href]) => <Link key={href} href={href} className="hover:text-[#2c9873]">{label}</Link>)}<Link href="/contact" className="hover:text-[#2c9873]">Contact</Link></nav>
      <details className="relative md:hidden"><summary className="cursor-pointer list-none rounded-lg border px-4 py-2 font-semibold">Menu</summary><nav className="absolute right-0 z-20 mt-2 grid min-w-52 gap-1 rounded-xl border bg-white p-3 shadow-xl">{links.map(([label, href]) => <Link key={href} href={href} className="rounded-lg px-3 py-2 hover:bg-[#eef4ec]">{label}</Link>)}<Link href="/contact" className="rounded-lg px-3 py-2 hover:bg-[#eef4ec]">Contact</Link></nav></details>
    </div></header>
    <main className="mx-auto max-w-6xl px-6 py-8 sm:py-12">{children}</main>
    <footer className="mt-16 border-t border-[#dce6d9] bg-white"><div className="mx-auto max-w-6xl px-6 py-8 text-sm"><p className="max-w-3xl text-[#567069]"><strong>Affiliate disclosure:</strong> Dewdora may earn a commission when you purchase through eligible links, at no additional cost to you. Recommendations remain editorially selected.</p><div className="mt-6 flex flex-wrap justify-between gap-4"><span>© {new Date().getFullYear()} Dewdora</span><div className="flex gap-5"><Link href="/contact">Contact</Link><Link href="/auth/login">Admin</Link></div></div></div></footer>
  </div>;
}
