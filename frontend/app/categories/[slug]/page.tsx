import Link from "next/link";
import { notFound } from "next/navigation";
import { SiteShell } from "@/components/public/site-shell";
import { PostCard, ProductCard } from "@/components/public/cards";
import { publicGet, type PublicPost, type PublicProduct, type PublicCategory, type ListResult } from "@/lib/public-api";
import type { ApiResponse } from "@/types/api";

type CategoryDetail = {
  category: PublicCategory;
  posts: ListResult<PublicPost>;
  products: ListResult<PublicProduct>;
  brands: { id: number; name: string; slug: string }[];
};

export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  let result: ApiResponse<CategoryDetail>;
  try { result = await publicGet(`categories/${encodeURIComponent(slug)}`); } catch { notFound(); }
  const { category, products, brands, posts } = result.data;
  return <SiteShell><h1 className="text-4xl font-black">{category.name}</h1><p className="mt-4 max-w-2xl text-[#567069]">{category.description}</p>
    <section className="mt-10"><h2 className="text-3xl font-bold">Recommended products</h2><div className="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">{products?.data?.map(product => <ProductCard key={product.id} product={product} />)}</div>{!products?.data?.length && <p className="mt-5 text-[#567069]">No active products in this category yet.</p>}</section>
    {brands?.length > 0 && <section className="mt-14"><h2 className="text-2xl font-bold">Brands in this category</h2><div className="mt-5 flex flex-wrap gap-3">{brands.map(brand => <Link key={brand.id} href={`/brands/${brand.slug}`} className="rounded-full border bg-white px-5 py-3 font-semibold">{brand.name}</Link>)}</div></section>}
    <section className="mt-14"><h2 className="text-3xl font-bold">Guides & articles</h2><div className="mt-6 grid gap-6 md:grid-cols-3">{posts?.data?.map(post => <PostCard key={post.id} post={post} />)}</div>{!posts?.data?.length && <p className="mt-5 text-[#567069]">No published guides in this category yet.</p>}</section>
  </SiteShell>;
}
