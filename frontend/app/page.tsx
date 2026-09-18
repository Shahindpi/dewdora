import { pageMetadata, JsonLd, siteOrigin } from "@/lib/seo";
import { AffiliateCarousel } from "@/components/public/affiliate-carousel";
import { HeroBannerSection, type HeroBanner } from "@/components/public/hero-banner";
export const metadata = pageMetadata("/", "Product discovery and buying guides");
import Link from "next/link";
import { NewsletterForm } from "@/components/public/forms";
import { SiteShell } from "@/components/public/site-shell";
import { PostCard, ProductCard } from "@/components/public/cards";
import { safePublicGet, type PublicPost, type PublicProduct, type PublicCategory } from "@/lib/public-api";
import type { ApiResponse } from "@/types/api";

type Home = { carousel_products: PublicProduct[]; hero_banners: HeroBanner[]; hero_products: PublicProduct[]; popular_posts: PublicPost[]; latest_posts: PublicPost[]; featured_categories: PublicCategory[]; featured_brands: { id: number; name: string; slug: string; logo?: string | null }[]; statistics: { posts: number; products: number; categories: number; brands: number } };
const empty: Home = { carousel_products: [], hero_banners: [], hero_products: [], popular_posts: [], latest_posts: [], featured_categories: [], featured_brands: [], statistics: { posts: 0, products: 0, categories: 0, brands: 0 } };
export default async function HomePage() {
  const { data } = await safePublicGet<ApiResponse<Home>>("homepage", { data: empty, success: false });
  const reviews = data.latest_posts.filter(post => post.post_type === "review");
  const guides = data.latest_posts.filter(post => post.post_type === "tutorial" || post.post_type === "article");
  return <SiteShell><JsonLd data={{ "@context": "https://schema.org", "@type": "WebSite", name: "Dewdora", url: siteOrigin }} /><JsonLd data={{ "@context": "https://schema.org", "@type": "Organization", name: "Dewdora", url: siteOrigin, logo: `${siteOrigin}/dewdora-logo.svg` }} /><AffiliateCarousel products={data.carousel_products} /><HeroBannerSection banner={data.hero_banners?.[0]} />
    {data.featured_categories.length > 0 && <section className="mt-14"><h2 className="text-2xl font-bold">Explore product categories</h2><div className="mt-5 flex flex-wrap gap-3">{data.featured_categories.map(c => <Link key={c.id} href={`/categories/${c.slug}`} className="rounded-full bg-white px-5 py-3 font-semibold shadow-sm hover:text-[#2c9873]">{c.name}</Link>)}</div></section>}
    <section className="mt-14"><div className="flex justify-between"><div><p className="text-sm font-bold uppercase tracking-widest text-[#2c9873]">Discover tools and offers</p><h2 className="mt-2 text-3xl font-bold">Explore products</h2></div><Link href="/products" className="font-semibold text-[#165e46]">View all →</Link></div><div className="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">{data.hero_products.map(product => <ProductCard key={product.id} product={product} />)}</div>{data.hero_products.length === 0 && <p className="mt-6 text-[#567069]">Products will appear here when published.</p>}</section>
    {data.featured_brands.length > 0 && <section className="mt-14"><h2 className="text-3xl font-bold">Featured brands</h2><div className="mt-6 grid gap-3 sm:grid-cols-2 md:grid-cols-4">{data.featured_brands.map(brand => <Link key={brand.id} href={`/brands/${brand.slug}`} className="rounded-xl border bg-white p-5 text-center font-bold hover:border-[#2c9873]">{brand.name}</Link>)}</div></section>}
    {reviews.length > 0 && <section className="mt-14"><h2 className="text-3xl font-bold">Latest reviews</h2><div className="mt-6 grid gap-6 md:grid-cols-3">{reviews.map(post => <PostCard key={post.id} post={post} />)}</div></section>}
    {guides.length > 0 && <section className="mt-14"><h2 className="text-3xl font-bold">Buying guides & how-tos</h2><div className="mt-6 grid gap-6 md:grid-cols-3">{guides.map(post => <PostCard key={post.id} post={post} />)}</div></section>}
    <section className="mt-14"><div className="flex justify-between"><h2 className="text-3xl font-bold">Popular reads</h2><Link href="/posts" className="font-semibold text-[#165e46]">View all →</Link></div><div className="mt-6 grid gap-6 md:grid-cols-3">{data.popular_posts.map(post => <PostCard key={post.id} post={post} />)}</div>{data.popular_posts.length === 0 && <p className="mt-6 text-[#567069]">Articles will appear here when published.</p>}</section>
    <section className="mt-16 rounded-3xl bg-[#165e46] p-10 text-white"><h2 className="text-2xl font-bold">Fresh finds in your inbox</h2><p className="mt-3 text-[#d7ecdf]">Get new articles and tools as they arrive.</p><NewsletterForm /></section>
  </SiteShell>;
}
