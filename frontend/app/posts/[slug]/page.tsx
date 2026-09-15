import Link from "next/link";
import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { SiteShell } from "@/components/public/site-shell";
import { PostCard, ProductCard } from "@/components/public/cards";
import { publicGet, type PublicPost, type PublicProduct } from "@/lib/public-api";
import { imageUrl } from "@/lib/image";
import { Comments } from "@/components/public/forms";
import type { ApiResponse } from "@/types/api";

type Detail = ApiResponse<{ post: PublicPost; related_posts: PublicPost[] }>;
export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
 const { slug } = await params;
 try { const result = await publicGet<Detail>(`posts/${encodeURIComponent(slug)}`); const post = result.data.post; return { title: post.seo?.meta_title || post.title, description: post.seo?.meta_description || post.excerpt }; } catch { return { title: "Article" }; }
}
export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  let detail: Detail;
  try { detail = await publicGet<Detail>(`posts/${encodeURIComponent(slug)}`); } catch { notFound(); }
  const { post, related_posts } = detail.data;
  return <SiteShell><article className="mx-auto max-w-3xl"><Link href="/posts" className="text-sm font-semibold text-[#165e46]">← All articles</Link><p className="mt-10 text-sm font-bold uppercase tracking-widest text-[#2c9873]">{post.category?.name || "Article"}</p><h1 className="mt-3 text-4xl font-black md:text-5xl">{post.title}</h1><p className="mt-5 text-[#567069]">{post.excerpt}</p>{post.featured_image && <img src={imageUrl(post.featured_image)} alt="" className="mt-8 w-full rounded-2xl object-cover" />}
    <div className="mt-9 whitespace-pre-wrap leading-8 text-[#34554a]">{post.content?.replace(/<[^>]*>/g, " ").replace(/&nbsp;/g, " ")}</div>
    <div className="mt-8 flex flex-wrap gap-2">{post.tags?.map(tag => <Link key={tag.id} href={`/tags/${tag.slug}`} className="rounded-full border px-4 py-2 text-sm">#{tag.name}</Link>)}</div>
    {post.allow_comments && <Comments slug={slug} />}</article>
    {!!post.affiliate_products?.length && <section className="mt-16"><h2 className="text-2xl font-bold">Tools mentioned</h2><div className="mt-6 grid gap-6 md:grid-cols-3">{post.affiliate_products.map((product: PublicProduct) => <ProductCard key={product.id} product={product} />)}</div></section>}
    {!!related_posts?.length && <section className="mt-16"><h2 className="text-2xl font-bold">Keep reading</h2><div className="mt-6 grid gap-6 md:grid-cols-3">{related_posts.map(related => <PostCard key={related.id} post={related} />)}</div></section>}
  </SiteShell>;
}
