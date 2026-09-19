"use client";
import type { ReactNode } from "react";
import { trackEvent } from "@/lib/analytics";
import { recordAffiliateEvent } from "@/lib/affiliate-events";
import type { PublicProduct } from "@/lib/public-api";
export function AffiliateLink({ product, placement, className, children }: { product: PublicProduct; placement: string; className?: string; children: ReactNode }) {
  if (!product.affiliate_url) return null;
  return <a href={product.affiliate_url} target="_blank" rel="noopener noreferrer sponsored" className={className} onClick={() => { recordAffiliateEvent(product, "click"); trackEvent("affiliate_click", { product_id: product.id, product_name: product.name, brand_id: product.brand_id ?? undefined, brand_name: product.brand?.name, network_id: product.affiliate_network_id ?? undefined, network_name: product.affiliate_network?.name, destination: product.affiliate_url, placement }); }}>{children}</a>;
}
