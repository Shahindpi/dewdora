"use client";

import { ExternalLink, Menu, Sun, Moon, Monitor } from "lucide-react";
import Link from "next/link";
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from "@/components/ui/avatar";

import { useTheme } from "next-themes";
import { useAuth } from "@/hooks/use-auth";
import Sidebar from "@/components/admin/sidebar";
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from "@/components/ui/sheet";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

export default function AdminHeader() {
  const { user } = useAuth();
  const { theme, setTheme } = useTheme();

  return (
    <header className="sticky top-0 z-40 h-16 border-b bg-background/95 backdrop-blur px-6 flex items-center justify-between">
      <Sheet>
        <SheetTrigger className="mr-3 rounded-lg border p-2 md:hidden" aria-label="Open admin navigation">
          <Menu className="h-5 w-5" />
        </SheetTrigger>
        <SheetContent side="left" className="w-80 p-0">
          <SheetTitle className="sr-only">Admin navigation</SheetTitle>
          <Sidebar mobile />
        </SheetContent>
      </Sheet>

      <div className="ml-auto flex items-center gap-3 sm:gap-5"><label className="flex items-center gap-1 text-sm"><span className="sr-only">Dashboard theme</span>{theme === "dark" ? <Moon className="h-4 w-4" /> : theme === "light" ? <Sun className="h-4 w-4" /> : <Monitor className="h-4 w-4" />}<select aria-label="Dashboard theme" value={theme || "system"} onChange={event => setTheme(event.target.value)} className="rounded-lg border bg-background px-2 py-1 text-foreground"><option value="system">System</option><option value="light">Light</option><option value="dark">Dark</option></select></label>
        <Link href="/" target="_blank" className={cn(buttonVariants({ variant: "outline", size: "sm" }), "hidden sm:inline-flex")}>
          View site
          <ExternalLink className="ml-2 h-4 w-4" />
        </Link>

        <div className="flex items-center gap-3">
          <Avatar>
            <AvatarImage src={user?.avatar ?? ""} />

            <AvatarFallback>
              {user?.name?.charAt(0)}
            </AvatarFallback>
          </Avatar>

          <div className="hidden sm:block">
            <p className="text-sm font-medium">
              {user?.name}
            </p>

            <p className="text-xs text-muted-foreground">
              {user?.role?.name}
            </p>
          </div>
        </div>
      </div>
    </header>
  );
}
