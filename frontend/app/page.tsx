import Link from "next/link";
import { NewsletterForm } from "@/components/public/forms";
import { SiteShell } from "@/components/public/site-shell";
import { PostCard, ProductCard } from "@/components/public/cards";
import { safePublicGet, type PublicPost, type PublicProduct, type PublicCategory } from "@/lib/public-api";
import type { ApiResponse } from "@/types/api";

type Home = { hero_products: PublicProduct[]; popular_posts: PublicPost[]; latest_posts: PublicPost[]; featured_categories: PublicCategory[]; statistics: { posts: number; products: number; categories: number; brands: number } };
const empty: Home = { hero_products: [], popular_posts: [], latest_posts: [], featured_categories: [], statistics: { posts: 0, products: 0, categories: 0, brands: 0 } };
export default async function HomePage() {
  const { data } = await safePublicGet<ApiResponse<Home>>("homepage", { data: empty, success: false });
  return <SiteShell><section className="rounded-3xl bg-[#165e46] px-8 py-16 text-white md:px-16"><p className="text-sm font-bold uppercase tracking-widest text-[#bde4cb]">Discover • Compare • Grow</p><h1 className="mt-5 max-w-2xl text-5xl font-black leading-tight">Fresh ideas and tools for a better everyday.</h1><p className="mt-6 max-w-xl text-[#d7ecdf]">Explore practical articles, trusted products, and discoveries curated by Dewdora.</p><div className="mt-8 flex gap-3"><Link href="/posts" className="rounded-xl bg-white px-6 py-3 font-semibold text-[#165e46]">Read articles</Link><Link href="/products" className="rounded-xl border border-white px-6 py-3 font-semibold">Explore tools</Link></div></section>
    {data.featured_categories.length > 0 && <section className="mt-14"><h2 className="text-2xl font-bold">Explore topics</h2><div className="mt-5 flex flex-wrap gap-3">{data.featured_categories.map(c => <Link key={c.id} href={`/categories/${c.slug}`} className="rounded-full bg-white px-5 py-3 font-semibold shadow-sm hover:text-[#2c9873]">{c.name}</Link>)}</div></section>}
    <section className="mt-14"><div className="flex justify-between"><h2 className="text-2xl font-bold">Latest articles</h2><Link href="/posts" className="font-semibold text-[#165e46]">View all →</Link></div><div className="mt-6 grid gap-6 md:grid-cols-3">{data.latest_posts.map(post => <PostCard key={post.id} post={post} />)}</div>{data.latest_posts.length === 0 && <p className="mt-6 text-[#567069]">Articles will appear here when published.</p>}</section>
    <section className="mt-14"><div className="flex justify-between"><h2 className="text-2xl font-bold">Featured products</h2><Link href="/products" className="font-semibold text-[#165e46]">View all →</Link></div><div className="mt-6 grid gap-6 md:grid-cols-3">{data.hero_products.map(product => <ProductCard key={product.id} product={product} />)}</div>{data.hero_products.length === 0 && <p className="mt-6 text-[#567069]">Products will appear here when featured.</p>}</section>
    <section className="mt-16 rounded-3xl bg-[#165e46] p-10 text-white"><h2 className="text-2xl font-bold">Fresh finds in your inbox</h2><p className="mt-3 text-[#d7ecdf]">Get new articles and tools as they arrive.</p><NewsletterForm /></section>
  </SiteShell>;
}
