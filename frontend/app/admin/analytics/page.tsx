"use client";
import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import api from "@/lib/axios";
import { PageSizeSelect, type PageSize } from "@/components/admin/page-size";
type Row = { id: number; name: string; brand?: { name: string } | null; impressions_count: number; clicks_count: number };
type Brand = { id: number; name: string; impressions_count: number; clicks_count: number };
type Data = { products: Row[]; brands: Brand[]; totals: { impressions: number; clicks: number }; pagination: { current_page: number; last_page: number } };
export default function AnalyticsPage() {
  const [page, setPage] = useState(1); const [size, setSize] = useState<PageSize>(20);
  const { data, isLoading, error, refetch } = useQuery({ queryKey: ["affiliate-analytics", page, size], queryFn: async () => (await api.get<{ data: Data }>("/admin/affiliate-analytics", { params: { page, per_page: size } })).data.data });
  return <div><h1 className="text-3xl font-bold">Affiliate analytics</h1><p className="mt-2 text-muted-foreground">First-party impressions and clicks, grouped by product and brand.</p>
  {isLoading && <p className="mt-6">Loading analytics…</p>}{error && <p role="alert" className="mt-6 text-destructive">Could not load analytics. <button onClick={() => refetch()} className="underline">Retry</button></p>}
  {data && <><div className="mt-6 grid gap-4 sm:grid-cols-2"><div className="rounded-xl border bg-background p-5"><p className="text-sm">Impressions</p><strong className="text-3xl">{data.totals.impressions}</strong></div><div className="rounded-xl border bg-background p-5"><p className="text-sm">Affiliate clicks</p><strong className="text-3xl">{data.totals.clicks}</strong></div></div>
  <section className="mt-8"><h2 className="text-xl font-bold">Brands</h2><div className="mt-3 overflow-x-auto rounded-xl border bg-background"><table className="w-full text-left text-sm"><thead><tr><th className="p-3">Brand</th><th className="p-3">Impressions</th><th className="p-3">Clicks</th></tr></thead><tbody>{data.brands.map(brand => <tr key={brand.id} className="border-t"><td className="p-3">{brand.name}</td><td className="p-3">{brand.impressions_count}</td><td className="p-3">{brand.clicks_count}</td></tr>)}</tbody></table></div></section>
  <section className="mt-8"><div className="flex items-center justify-between gap-3"><h2 className="text-xl font-bold">Products</h2><PageSizeSelect value={size} onChange={value => { setSize(value); setPage(1); }} /></div><div className="mt-3 overflow-x-auto rounded-xl border bg-background"><table className="w-full text-left text-sm"><thead><tr><th className="p-3">Product</th><th className="p-3">Brand</th><th className="p-3">Impressions</th><th className="p-3">Clicks</th></tr></thead><tbody>{data.products.map(product => <tr key={product.id} className="border-t"><td className="p-3">{product.name}</td><td className="p-3">{product.brand?.name || "—"}</td><td className="p-3">{product.impressions_count}</td><td className="p-3">{product.clicks_count}</td></tr>)}</tbody></table></div><div className="mt-4 flex gap-4"><button disabled={page <= 1} onClick={() => setPage(page - 1)}>Previous</button><span>{page} / {data.pagination.last_page}</span><button disabled={page >= data.pagination.last_page} onClick={() => setPage(page + 1)}>Next</button></div></section></>}
  </div>;
}
