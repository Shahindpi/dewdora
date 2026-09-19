"use client";
import { useEffect, useRef } from "react";
import Image from "next/image";
import Link from "next/link";
import type { PublicProduct } from "@/lib/public-api";
import { imageUrl } from "@/lib/image";
import { AffiliateLink } from "@/components/public/affiliate-link";
import { TrackedProductLink } from "@/components/public/tracked-product-link";
import { productParameters, trackEvent } from "@/lib/analytics";
import { recordAffiliateEvent } from "@/lib/affiliate-events";

export function LatestProducts({ products }: { products: PublicProduct[] }) {
  const rail = useRef<HTMLDivElement>(null);
  const seen = useRef(new Set<number>());
  useEffect(() => {
    const root = rail.current;
    if (!root) return;
    const observer = new IntersectionObserver(entries => {
      for (const entry of entries) {
        if (!entry.isIntersecting || entry.intersectionRatio < 0.6) continue;
        const position = Number((entry.target as HTMLElement).dataset.position);
        const product = products[position];
        if (!product || seen.current.has(product.id)) continue;
        seen.current.add(product.id);
        recordAffiliateEvent(product, "impression", "homepage_latest");
        trackEvent("view_item_list", { ...productParameters(product, "homepage_latest"), position: position + 1 });
      }
    }, { root, threshold: 0.6 });
    Array.from(root.children).forEach(child => observer.observe(child));
    return () => observer.disconnect();
  }, [products]);
  return <section aria-label="Latest Affiliate Products" className="mt-2">
    <div className="mb-6 flex items-end justify-between gap-4"><div><p className="text-sm font-bold uppercase tracking-widest text-[#2c9873]">Freshly added</p><h1 className="mt-2 text-3xl font-black sm:text-4xl">Latest Affiliate Products</h1><p className="mt-2 text-[#567069]">New tools worth a closer look.</p></div><Link href="/products" className="shrink-0 font-semibold text-[#165e46] hover:underline">View all →</Link></div>
    <div ref={rail} className="flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth pb-4" aria-label="Scroll latest products">
      {products.map((product, position) => <article key={product.id} data-position={position} className="w-full shrink-0 snap-start overflow-hidden rounded-2xl border border-[#dce6d9] bg-white shadow-sm sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-2.5rem)/3)]">
        <TrackedProductLink product={product} placement="homepage_latest" className="block bg-[#eef4ec]"><div className="relative h-56 sm:h-64">{product.featured_image ? <Image unoptimized fill sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw" src={imageUrl(product.featured_image)} alt={product.name} className="object-contain p-4" /> : <span className="grid h-full place-items-center">View product</span>}</div></TrackedProductLink>
        <div className="p-5"><p className="text-xs font-bold uppercase tracking-wide text-[#2c9873]">{product.brand?.name || product.category?.name || "Product"}</p><TrackedProductLink product={product} placement="homepage_latest" className="mt-2 block min-h-14 text-lg font-bold hover:text-[#2c9873]">{product.name}</TrackedProductLink><p className="mt-2 line-clamp-2 min-h-10 text-sm text-[#567069]">{product.short_description}</p><div className="mt-5 flex items-center justify-between gap-3">{product.price != null && <strong>{product.currency || "USD"} {product.price}</strong>}{product.affiliate_url ? <AffiliateLink product={product} placement="homepage_latest" className="rounded-lg bg-[#165e46] px-4 py-2 text-sm font-bold text-white hover:bg-[#0e4634]">View offer ↗</AffiliateLink> : <TrackedProductLink product={product} placement="homepage_latest" className="font-bold text-[#165e46]">Details →</TrackedProductLink>}</div><p className="mt-3 text-xs text-[#567069]">Affiliate link: we may earn a commission.</p></div>
      </article>)}
    </div>{products.length === 0 && <p className="py-8 text-[#567069]">New products will appear here when published.</p>}
  </section>;
}
