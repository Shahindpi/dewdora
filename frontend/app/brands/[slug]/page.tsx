import { notFound } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
import { ProductCard } from "@/components/public/cards";
import { SiteShell } from "@/components/public/site-shell";
import { publicGet, type PublicProduct, type ListResult } from "@/lib/public-api";
import type { ApiResponse } from "@/types/api";

type BrandDetail = {
  brand: { name: string; description?: string; logo?: string | null };
  products: ListResult<PublicProduct>;
};

export default async function Page({ params, searchParams }: { params: Promise<{ slug: string }>; searchParams: Promise<{ page?: string }> }) {
  const { slug } = await params;
  const { page } = await searchParams;
  let result: ApiResponse<BrandDetail>;
  try { result = await publicGet(`brands/${encodeURIComponent(slug)}${page ? `?page=${encodeURIComponent(page)}` : ""}`); } catch { notFound(); }
  const { brand, products } = result.data;
  return <SiteShell><div className="flex items-center gap-5">{brand.logo && <Image unoptimized width={80} height={80} src={brand.logo} alt={`${brand.name} logo`} className="h-20 w-20 rounded-2xl border bg-white object-contain p-2" />}<div><p className="text-sm font-bold uppercase tracking-widest text-[#2c9873]">Brand</p><h1 className="text-4xl font-black">{brand.name}</h1></div></div><p className="mt-5 max-w-2xl text-[#567069]">{brand.description}</p><h2 className="mt-12 text-3xl font-bold">Products from {brand.name}</h2><div className="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">{products.data.map(product => <ProductCard key={product.id} product={product} />)}</div>{!products.data.length && <p className="mt-5 text-[#567069]">No active products from this brand yet.</p>}{products.meta && products.meta.last_page > 1 && <nav aria-label="Product pages" className="mt-8 flex gap-4">{products.meta.current_page > 1 && <Link href={`?page=${products.meta.current_page - 1}`} className="rounded-lg border px-4 py-2">Previous</Link>}<span>Page {products.meta.current_page} of {products.meta.last_page}</span>{products.meta.current_page < products.meta.last_page && <Link href={`?page=${products.meta.current_page + 1}`} className="rounded-lg border px-4 py-2">Next</Link>}</nav>}</SiteShell>;
}
