import Link from "next/link";
import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { SiteShell } from "@/components/public/site-shell";
import { publicGet, type PublicProduct } from "@/lib/public-api";
import { imageUrl } from "@/lib/image";
import type { ApiResponse } from "@/types/api";

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
 const { slug } = await params;
 try { const result = await publicGet<ApiResponse<{ product: PublicProduct }>>(`products/${encodeURIComponent(slug)}`); const product = result.data.product; return { title: product.seo?.meta_title || product.name, description: product.seo?.meta_description || product.short_description || undefined }; } catch { return { title: "Product" }; }
}
export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  let product: PublicProduct;
  try { product = (await publicGet<ApiResponse<{ product: PublicProduct }>>(`products/${encodeURIComponent(slug)}`)).data.product; } catch { notFound(); }
  return <SiteShell><Link href="/products" className="text-sm font-semibold text-[#165e46]">← All products</Link><div className="mt-9 grid gap-10 md:grid-cols-2"><div className="rounded-3xl bg-white p-8">{product.featured_image && <img src={imageUrl(product.featured_image)} alt="" className="w-full object-contain" />}</div><div><p className="text-sm font-bold uppercase tracking-widest text-[#2c9873]">{product.brand?.name || "Tool"}</p><h1 className="mt-3 text-4xl font-black">{product.name}</h1><p className="mt-5 text-[#567069]">{product.short_description}</p>{product.rating != null && <p className="mt-5 font-semibold">★ {product.rating}/5</p>}{product.price != null && <p className="mt-4 text-xl font-bold">{product.currency || "USD"} {product.price}</p>}{product.affiliate_url && <a href={product.affiliate_url} target="_blank" rel="noopener noreferrer sponsored" className="mt-8 inline-block rounded-xl bg-[#165e46] px-6 py-3 font-bold text-white">Visit product ↗</a>}</div></div>
    {product.description && <section className="mt-14 max-w-3xl whitespace-pre-wrap leading-8">{product.description.replace(/<[^>]*>/g, " ")}</section>}
    <div className="mt-10 grid gap-8 md:grid-cols-2">{product.pros?.length ? <section><h2 className="text-xl font-bold">What works well</h2><ul className="mt-4 list-inside list-disc space-y-2">{product.pros.map((pro, i) => <li key={i}>{pro}</li>)}</ul></section> : null}{product.cons?.length ? <section><h2 className="text-xl font-bold">Considerations</h2><ul className="mt-4 list-inside list-disc space-y-2">{product.cons.map((con, i) => <li key={i}>{con}</li>)}</ul></section> : null}</div>
  </SiteShell>;
}
