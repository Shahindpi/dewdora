import { SiteShell } from "@/components/public/site-shell";
import { PostCard, ProductCard } from "@/components/public/cards";
import { safePublicGet, type PublicPost, type PublicProduct } from "@/lib/public-api";
import type { ApiResponse } from "@/types/api";
type Search = { posts?: PublicPost[]; products?: PublicProduct[] };
export default async function Page({ searchParams }: { searchParams: Promise<{ q?: string }> }) {
 const { q } = await searchParams; const query = q?.trim() || "";
 const response = query ? await safePublicGet<ApiResponse<Search>>("search", { success: false, data: {} }, { q: query }) : { data: {} as Search };
 return <SiteShell><h1 className="text-4xl font-black">Search</h1><form className="mt-8 flex max-w-xl gap-2"><input name="q" type="search" defaultValue={query} placeholder="Search articles and products" className="min-w-0 flex-1 rounded-lg border p-3" /><button className="rounded-lg bg-[#165e46] px-5 text-white">Search</button></form>{query && <><h2 className="mt-10 text-2xl font-bold">Articles</h2><div className="mt-5 grid gap-6 md:grid-cols-3">{response.data.posts?.map(post => <PostCard key={post.id} post={post} />)}</div><h2 className="mt-10 text-2xl font-bold">Products</h2><div className="mt-5 grid gap-6 md:grid-cols-3">{response.data.products?.map(product => <ProductCard key={product.id} product={product} />)}</div></>}</SiteShell>;
}
