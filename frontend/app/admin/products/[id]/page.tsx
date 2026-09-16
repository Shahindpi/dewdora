"use client";
import { use } from "react";
import { useQuery } from "@tanstack/react-query";
import { ProductForm } from "@/components/admin/products/product-form";
import { getAdminProduct } from "@/services/admin-products";
export default function EditProductPage({ params }: { params: Promise<{ id: string }> }) { const { id } = use(params); const { data, isLoading } = useQuery({ queryKey: ["admin-product", id], queryFn: () => getAdminProduct(Number(id)) }); if (isLoading || !data) return <p>Loading product…</p>; return <div><h1 className="mb-6 text-3xl font-bold">Edit affiliate product</h1><ProductForm product={data} /></div>; }
