"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
// import type { Route } from "next";

import Logo from "@/components/layout/logo";
import { cn } from "@/lib/utils";

import {
  LayoutDashboard,
  FileText,
  FolderTree,
  Tags,
  ShoppingBag,
  Building2,
  BadgeDollarSign,
  ImageIcon,
  Mail,
  MessageSquare,
  Users,
  Settings,
} from "lucide-react";

const items = [
  { label: "Dashboard", href: "/admin", icon: LayoutDashboard },
  { label: "Posts", href: "/admin/posts", icon: FileText },
  { label: "Categories", href: "/admin/categories", icon: FolderTree },
  { label: "Tags", href: "/admin/tags", icon: Tags },
  { label: "Products", href: "/admin/products", icon: ShoppingBag },
  { label: "Brands", href: "/admin/brands", icon: Building2 },
  { label: "Networks", href: "/admin/networks", icon: BadgeDollarSign },
  { label: "Media", href: "/admin/media", icon: ImageIcon },
  { label: "Newsletter", href: "/admin/newsletter", icon: Mail },
  { label: "Comments", href: "/admin/comments", icon: MessageSquare },
  { label: "Users", href: "/admin/users", icon: Users },
  { label: "Settings", href: "/admin/settings", icon: Settings },
] as const;

export default function Sidebar() {
  const pathname = usePathname();

  return (
    <aside className="hidden md:flex w-72 border-r bg-background h-screen flex-col sticky top-0">
      <div className="p-6">
        <Logo />
      </div>

      <nav className="flex-1 px-3 space-y-1">
        {items.map((item) => {
          const Icon = item.icon;

          const active =
            pathname === item.href ||
            pathname.startsWith(`${item.href}/`);

          return (
            <Link
                key={item.href}
                href={item.href as any}
                className={cn(
                    "flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors",
                    active
                    ? "bg-primary text-primary-foreground"
                    : "hover:bg-muted"
                )}
                >
                <Icon className="h-5 w-5" />
                {item.label}
            </Link>
          );
        })}
      </nav>

      <div className="border-t p-4 text-xs text-muted-foreground">
        Dewdora CMS v1.0
      </div>
    </aside>
  );
}