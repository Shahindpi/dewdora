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
  Settings,
  Users,
  ShieldCheck,
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
  { label: "Subscribers", href: "/admin/subscribers", icon: Mail },
  { label: "Comments", href: "/admin/comments", icon: MessageSquare },
  { label: "Contacts", href: "/admin/contacts", icon: Mail },
  { label: "Users", href: "/admin/users", icon: Users },
  { label: "Roles", href: "/admin/roles", icon: ShieldCheck },
  { label: "Profile", href: "/admin/profile", icon: Settings },
  { label: "Settings", href: "/admin/settings", icon: Settings },
] as const;

export default function Sidebar({ mobile = false }: { mobile?: boolean }) {
  const pathname = usePathname();

  return (
    <aside className={`${mobile ? "flex w-full" : "hidden w-72 md:flex"} border-r bg-background h-screen flex-col sticky top-0`}>
      <div className="p-6">
        <Logo />
      </div>

      <nav className="flex-1 space-y-1 overflow-y-auto px-3 pb-5">
        {items.map((item) => {
          const Icon = item.icon;

          const active =
            pathname === item.href ||
            pathname.startsWith(`${item.href}/`);

          return (
            <Link
                key={item.href}
                href={item.href}
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
