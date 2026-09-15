import { notFound } from "next/navigation";
import { SiteShell } from "@/components/public/site-shell";
import { PostCard } from "@/components/public/cards";
import { publicGet, type PublicPost, type PublicCategory, type ListResult } from "@/lib/public-api";
import type { ApiResponse } from "@/types/api";
export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
 const { slug } = await params; let result: ApiResponse<{ category: PublicCategory; posts: ListResult<PublicPost> }>;
 try { result = await publicGet(`categories/${encodeURIComponent(slug)}`); } catch { notFound(); }
 return <SiteShell><h1 className="text-4xl font-black">{result.data.category.name}</h1><p className="mt-4 text-[#567069]">{result.data.category.description}</p><div className="mt-9 grid gap-6 md:grid-cols-3">{result.data.posts?.data?.map(post => <PostCard key={post.id} post={post} />)}</div></SiteShell>;
}
