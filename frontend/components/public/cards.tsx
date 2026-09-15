import Link from "next/link";
import { imageUrl } from "@/lib/image";
import type { PublicPost, PublicProduct } from "@/lib/public-api";

export function PostCard({ post }: { post: PublicPost }) {
  return <Link href={`/posts/${encodeURIComponent(post.slug)}`} className="group overflow-hidden rounded-2xl border border-[#dce6d9] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
    {post.featured_image && <img src={imageUrl(post.featured_image)} alt="" className="h-48 w-full object-cover" />}
    <div className="p-6"><p className="text-xs font-bold uppercase tracking-widest text-[#2c9873]">{post.category?.name || "Article"}</p><h3 className="mt-2 text-xl font-bold group-hover:text-[#2c9873]">{post.title}</h3><p className="mt-3 line-clamp-3 text-sm text-[#567069]">{post.excerpt}</p></div>
  </Link>;
}
export function ProductCard({ product }: { product: PublicProduct }) {
  return <Link href={`/products/${encodeURIComponent(product.slug)}`} className="group overflow-hidden rounded-2xl border border-[#dce6d9] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
    {product.featured_image && <img src={imageUrl(product.featured_image)} alt="" className="h-48 w-full object-contain bg-[#eef4ec] p-5" />}
    <div className="p-6"><p className="text-xs font-bold uppercase tracking-widest text-[#2c9873]">{product.brand?.name || "Featured tool"}</p><h3 className="mt-2 text-xl font-bold group-hover:text-[#2c9873]">{product.name}</h3><p className="mt-3 line-clamp-3 text-sm text-[#567069]">{product.short_description}</p>{product.rating != null && <p className="mt-4 text-sm font-semibold">★ {product.rating}/5</p>}</div>
  </Link>;
}
