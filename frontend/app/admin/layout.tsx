import type { Metadata } from "next";
import AdminShell from "@/components/admin/admin-shell";
import { AdminProviders } from "@/components/admin/admin-providers";
export const metadata: Metadata = { robots: { index: false, follow: false } };
export default function AdminLayout({ children }: { children: React.ReactNode }) { return <AdminProviders><AdminShell>{children}</AdminShell></AdminProviders>; }
