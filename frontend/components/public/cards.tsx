import Link from "next/link";
import Image from "next/image";
import { imageUrl } from "@/lib/image";
import type { PublicPost, PublicProduct } from "@/lib/public-api";

export function PostCard({ post }: { post: PublicPost }) {
  return <Link href={`/posts/${encodeURIComponent(post.slug)}`} className="group overflow-hidden rounded-2xl border border-[#dce6d9] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
    {post.featured_image && <Image unoptimized width={720} height={384} src={imageUrl(post.featured_image)} alt={`${post.title} featured image`} className="h-48 w-full object-cover" />}
    <div className="p-6"><p className="text-xs font-bold uppercase tracking-widest text-[#2c9873]">{post.category?.name || "Article"}</p><h3 className="mt-2 text-xl font-bold group-hover:text-[#2c9873]">{post.title}</h3><p className="mt-3 line-clamp-3 text-sm text-[#567069]">{post.excerpt}</p></div>
  </Link>;
}
export function ProductCard({ product }: { product: PublicProduct }) {
  return <article className="group overflow-hidden rounded-2xl border border-[#dce6d9] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
    <Link href={`/products/${encodeURIComponent(product.slug)}`}>{product.featured_image && <Image unoptimized width={720} height={384} src={imageUrl(product.featured_image)} alt={`${product.name} product`} className="h-48 w-full bg-[#eef4ec] object-contain p-5" />}</Link>
    <div className="p-6"><p className="text-xs font-bold uppercase tracking-widest text-[#2c9873]">{product.brand?.name || product.category?.name || "Recommended"}</p><Link href={`/products/${encodeURIComponent(product.slug)}`}><h3 className="mt-2 text-xl font-bold group-hover:text-[#2c9873]">{product.name}</h3></Link><p className="mt-3 line-clamp-3 text-sm text-[#567069]">{product.short_description}</p><div className="mt-5 flex items-center justify-between gap-3">{product.price != null ? <span className="font-bold">{product.currency || "USD"} {product.price}</span> : <span />}{product.affiliate_url ? <a href={product.affiliate_url} target="_blank" rel="noopener noreferrer sponsored" className="rounded-lg bg-[#165e46] px-4 py-2 text-sm font-bold text-white">View offer ↗</a> : <Link href={`/products/${product.slug}`} className="text-sm font-bold text-[#165e46]">View product →</Link>}</div></div>
  </article>;
}
