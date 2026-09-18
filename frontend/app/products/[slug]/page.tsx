import Link from "next/link";
import Image from "next/image";
import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { SiteShell } from "@/components/public/site-shell";
import { PublicApiError, publicGet, type PublicPost, type PublicProduct } from "@/lib/public-api";
import { PostCard, ProductCard } from "@/components/public/cards";
import { imageUrl } from "@/lib/image";
import type { ApiResponse } from "@/types/api";

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
 const { slug } = await params;
 try { const result = await publicGet<ApiResponse<{ product: PublicProduct }>>(`products/${encodeURIComponent(slug)}`); const product = result.data.product; return { title: product.seo?.meta_title || product.name, description: product.seo?.meta_description || product.short_description || undefined, alternates: { canonical: product.seo?.canonical_url }, openGraph: { images: product.seo?.open_graph?.image ? [product.seo.open_graph.image] : [] } }; } catch { return { title: "Product" }; }
}
export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  let detail: { product: PublicProduct; related_products: PublicProduct[]; related_posts: PublicPost[] };
  try { detail = (await publicGet<ApiResponse<typeof detail>>(`products/${encodeURIComponent(slug)}`)).data; } catch (error) { if (error instanceof PublicApiError && error.status === 404) notFound(); throw error; }
  const { product, related_products, related_posts } = detail;
  return <SiteShell><Link href="/products" className="text-sm font-semibold text-[#165e46]">← All products</Link><div className="mt-9 grid gap-10 md:grid-cols-2"><div className="rounded-3xl bg-white p-8">{product.featured_image && <Image unoptimized width={900} height={700} src={imageUrl(product.featured_image)} alt={`${product.name} product`} className="h-auto w-full object-contain" />}</div><div><p className="text-sm font-bold uppercase tracking-widest text-[#2c9873]">{product.brand?.name || product.category?.name || "Recommended product"}</p><h1 className="mt-3 text-4xl font-black">{product.name}</h1><p className="mt-5 text-[#567069]">{product.short_description}</p>{product.rating != null && <p className="mt-5 font-semibold">★ {product.rating}/5</p>}{product.price != null && <p className="mt-4 text-xl font-bold">{product.currency || "USD"} {product.price}</p>}{product.affiliate_url && <a href={product.affiliate_url} target="_blank" rel="noopener noreferrer sponsored" className="mt-8 inline-block rounded-xl bg-[#165e46] px-6 py-3 font-bold text-white">View offer ↗</a>}<p className="mt-4 text-xs leading-5 text-[#71857f]">Affiliate disclosure: Dewdora may earn a commission from eligible purchases through this link.</p></div></div>
    {product.description && <section className="dewdora-content mt-14 max-w-3xl leading-8" dangerouslySetInnerHTML={{ __html: product.description }} />}
    <div className="mt-10 grid gap-8 md:grid-cols-2">{product.pros?.length ? <section><h2 className="text-xl font-bold">What works well</h2><ul className="mt-4 list-inside list-disc space-y-2">{product.pros.map((pro, i) => <li key={i}>{pro}</li>)}</ul></section> : null}{product.cons?.length ? <section><h2 className="text-xl font-bold">Considerations</h2><ul className="mt-4 list-inside list-disc space-y-2">{product.cons.map((con, i) => <li key={i}>{con}</li>)}</ul></section> : null}</div>
    {!!related_products?.length && <section className="mt-16"><h2 className="text-3xl font-bold">Related products</h2><div className="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">{related_products.map(item => <ProductCard key={item.id} product={item} />)}</div></section>}
    {!!related_posts?.length && <section className="mt-16"><h2 className="text-3xl font-bold">Reviews & guides</h2><div className="mt-6 grid gap-6 md:grid-cols-3">{related_posts.map(post => <PostCard key={post.id} post={post} />)}</div></section>}
  </SiteShell>;
}
