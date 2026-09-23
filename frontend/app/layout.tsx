import "./globals.css";
import type { Metadata } from "next";
import { siteOrigin, defaultImage } from "@/lib/seo";
export const metadata: Metadata = { metadataBase: new URL(siteOrigin), applicationName: "Dewdora", title: { default: "Dewdora | Product discovery and buying guides", template: "%s | Dewdora" }, description: "Discover useful products, independent reviews and practical buying guides from Dewdora.", openGraph: { title: "Dewdora", description: "Discover useful products, reviews and buying guides.", siteName: "Dewdora", type: "website", images: [defaultImage] }, twitter: { card: "summary_large_image", images: [defaultImage] } };

import { GoogleAnalytics } from "@/components/analytics/google-analytics";

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body>
        {children}
      </body>
      <GoogleAnalytics />
    </html>
  );
}
