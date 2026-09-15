"use client";
import { useEffect } from "react";
import { useRouter } from "next/navigation";
import Sidebar from "@/components/admin/sidebar";
import AdminHeader from "@/components/admin/header";
import { useAuth } from "@/hooks/use-auth";
export default function AdminLayout({ children }: { children: React.ReactNode }) {
 const router = useRouter(); const { authenticated, loading } = useAuth();
 useEffect(() => { if (!loading && !authenticated) router.replace("/auth/login"); }, [authenticated, loading, router]);
 if (loading) return <div className="flex min-h-screen items-center justify-center">Loading dashboard…</div>;
 if (!authenticated) return null;
 return <div className="flex min-h-screen bg-muted/30"><Sidebar /><div className="flex flex-1 flex-col"><AdminHeader /><main className="p-6">{children}</main></div></div>;
}
