import "./globals.css";
import type { Metadata } from "next";
export const metadata: Metadata = { title: { default: "Dewdora", template: "%s | Dewdora" }, description: "Explore useful articles and products curated by Dewdora." };

import { ThemeProvider } from "@/providers/theme-provider";
import { QueryProvider } from "@/providers/query-provider";
import { AuthProvider } from "@/providers/auth-provider";
import { ReduxProvider } from "@/providers/redux-provider";

import { Toaster } from "sonner";

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body>
        <ThemeProvider>
          <ReduxProvider>
            <QueryProvider>
              <AuthProvider>
                {children}
                <Toaster richColors position="top-right" />
              </AuthProvider>
            </QueryProvider>
          </ReduxProvider>
        </ThemeProvider>
      </body>
    </html>
  );
}