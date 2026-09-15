import { notFound } from "next/navigation";
import { ProductCard } from "@/components/public/cards";
import { safePublicGet, type PublicProduct, type ListResult } from "@/lib/public-api";
import { SiteShell } from "@/components/public/site-shell";
import { publicGet } from "@/lib/public-api";
import type { ApiResponse } from "@/types/api";
export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
 const { slug } = await params; let result: ApiResponse<{ name: string; description?: string }>;
 try { result = await publicGet(`brands/${encodeURIComponent(slug)}`); } catch { notFound(); }
 const products = await safePublicGet<ListResult<PublicProduct>>("products", { data: [] }, { brand: slug });
 return <SiteShell><h1 className="text-4xl font-black">{result.data.name}</h1><p className="mt-4 text-[#567069]">{result.data.description}</p><div className="mt-9 grid gap-6 md:grid-cols-3">{products.data.map(product => <ProductCard key={product.id} product={product} />)}</div></SiteShell>;
}
