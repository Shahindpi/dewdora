import type { PublicProduct } from "@/lib/public-api";

export function recordAffiliateEvent(product: PublicProduct, kind: "impression" | "click") {
  try {
    const storageKey = "dewdora_visitor_session";
    let session = sessionStorage.getItem(storageKey);
    if (!session) { session = crypto.randomUUID(); sessionStorage.setItem(storageKey, session); }
    const base = (process.env.NEXT_PUBLIC_API_URL || "/api/v1").replace(/\/$/, "");
    const body = JSON.stringify({ affiliate_product_id: product.id, kind, session_id: session });
    const url = `${base}/public/affiliate-events`;
    if (!navigator.sendBeacon?.(url, new Blob([body], { type: "application/json" }))) {
      void fetch(url, { method: "POST", headers: { "Content-Type": "application/json" }, body, keepalive: true }).catch(() => {});
    }
  } catch { /* Analytics must never prevent navigation. */ }
}
