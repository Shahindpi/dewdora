import type { Metadata } from "next";
import { AuthProviders } from "@/components/forms/auth-providers";
export const metadata: Metadata = { robots: { index: false, follow: false } };
export default function AuthLayout({ children }: { children: React.ReactNode }) { return <AuthProviders>{children}</AuthProviders>; }
