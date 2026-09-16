"use client";

import { ExternalLink, Menu } from "lucide-react";
import Link from "next/link";
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from "@/components/ui/avatar";

import { useAuth } from "@/hooks/use-auth";
import Sidebar from "@/components/admin/sidebar";
import { Sheet, SheetContent, SheetTrigger } from "@/components/ui/sheet";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

export default function AdminHeader() {
  const { user } = useAuth();

  return (
    <header className="h-16 border-b bg-background px-6 flex items-center justify-between">
      <Sheet>
        <SheetTrigger className="mr-3 rounded-lg border p-2 md:hidden" aria-label="Open admin navigation">
          <Menu className="h-5 w-5" />
        </SheetTrigger>
        <SheetContent side="left" className="w-80 p-0">
          <Sidebar mobile />
        </SheetContent>
      </Sheet>

      <div className="ml-auto flex items-center gap-3 sm:gap-5">
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
