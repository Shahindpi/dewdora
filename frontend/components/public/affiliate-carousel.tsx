"use client";
import { useCallback, useEffect, useRef, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { imageUrl } from "@/lib/image";
import { trackEvent } from "@/lib/analytics";
import type { PublicProduct } from "@/lib/public-api";

export function AffiliateCarousel({ products }: { products: PublicProduct[] }) {
  const viewport = useRef<HTMLDivElement>(null);
  const seen = useRef(new Set<number>());
  const [index, setIndex] = useState(0);
  const [visible, setVisible] = useState(6);
  useEffect(() => {
    const update = () => setVisible(window.innerWidth >= 1024 ? 6 : window.innerWidth >= 768 ? 3 : window.innerWidth >= 640 ? 2 : 1);
    update(); window.addEventListener("resize", update);
    return () => window.removeEventListener("resize", update);
  }, []);
  const maxIndex = Math.max(0, products.length - visible);
  const scroll = useCallback((next: number) => {
    const target = Math.max(0, Math.min(maxIndex, next));
    setIndex(target);
    const item = viewport.current?.children.item(target) as HTMLElement | null;
    if (item && viewport.current) viewport.current.scrollTo({ left: item.offsetLeft - viewport.current.offsetLeft, behavior: "smooth" });
  }, [maxIndex]);
  useEffect(() => {
    const root = viewport.current;
    if (!root) return;
    const observer = new IntersectionObserver(entries => {
      for (const entry of entries) {
        if (!entry.isIntersecting || entry.intersectionRatio < 0.6) continue;
        const position = Number((entry.target as HTMLElement).dataset.position);
        const product = products[position];
        if (!product || seen.current.has(product.id)) continue;
        seen.current.add(product.id);
        trackEvent("affiliate_product_impression", { product_id: product.id, product_name: product.name, brand_id: product.brand_id ?? undefined, brand_name: product.brand?.name, category: product.category?.name, position: position + 1, carousel_name: "homepage_affiliate_products" });
      }
    }, { root, threshold: 0.6 });
    Array.from(root.children).forEach(element => observer.observe(element));
    return () => observer.disconnect();
  }, [products]);
  const currentIndex = Math.min(index, maxIndex);
  return <section aria-label="Affiliate products" className="mt-2">
    <div className="mb-5 flex flex-wrap items-center justify-between gap-3"><div><p className="text-sm font-bold uppercase tracking-widest text-[#2c9873]">Latest discoveries</p><h1 className="text-3xl font-bold">Affiliate products</h1></div><div className="flex gap-2"><button type="button" aria-label="Previous affiliate products" disabled={currentIndex === 0} onClick={() => scroll(currentIndex - 1)} className="rounded-lg border bg-white px-4 py-2 disabled:opacity-40">←</button><button type="button" aria-label="Next affiliate products" disabled={currentIndex >= maxIndex} onClick={() => scroll(currentIndex + 1)} className="rounded-lg border bg-white px-4 py-2 disabled:opacity-40">→</button></div></div>
    <div ref={viewport} className="relative flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pb-3 [scrollbar-width:none]" onScroll={event => { const root = event.currentTarget; let nearest = 0; let distance = Infinity; Array.from(root.children).forEach((node, i) => { const delta = Math.abs((node as HTMLElement).offsetLeft - root.offsetLeft - root.scrollLeft); if (delta < distance) { distance = delta; nearest = i; } }); setIndex(Math.min(nearest, maxIndex)); }}>
      {products.map((product, position) => <article key={product.id} data-position={position} className="min-w-0 shrink-0 basis-full snap-start overflow-hidden rounded-xl border bg-white shadow-sm sm:basis-[calc((100%-0.75rem)/2)] md:basis-[calc((100%-1.5rem)/3)] lg:basis-[calc((100%-3.75rem)/6)]">
        <Link href={`/products/${encodeURIComponent(product.slug)}`} className="block">{product.featured_image ? <Image unoptimized src={imageUrl(product.featured_image)} alt={product.name} width={360} height={240} className="h-32 w-full object-contain p-3" /> : <div className="grid h-32 place-items-center bg-[#eef4ec] text-sm">View product</div>}</Link>
        <div className="p-3"><p className="truncate text-xs text-[#2c9873]">{product.brand?.name || product.category?.name || "Product"}</p><Link href={`/products/${encodeURIComponent(product.slug)}`} className="mt-1 line-clamp-2 min-h-12 text-sm font-bold">{product.name}</Link>{product.price != null && <p className="mt-2 text-sm font-semibold">{product.currency || "USD"} {product.price}</p>}{product.affiliate_url ? <a href={product.affiliate_url} target="_blank" rel="noopener noreferrer sponsored" onClick={() => trackEvent("affiliate_click", { product_id: product.id, product_name: product.name, brand_id: product.brand_id ?? undefined, brand_name: product.brand?.name, network_id: product.affiliate_network_id ?? undefined, network_name: product.affiliate_network?.name, destination: product.affiliate_url, placement: "homepage_carousel" })} className="mt-3 block rounded-lg bg-[#165e46] px-3 py-2 text-center text-xs font-bold text-white">View offer ↗</a> : <Link href={`/products/${encodeURIComponent(product.slug)}`} className="mt-3 block text-sm font-bold text-[#165e46]">Details →</Link>}</div>
      </article>)}
    </div>{products.length === 0 && <p className="py-6 text-[#567069]">Products will appear here when published.</p>}
  </section>;
}
