"use client";

import { Bell, Search } from "lucide-react";

import { Input } from "@/components/ui/input";
import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from "@/components/ui/avatar";

import { useAuth } from "@/hooks/use-auth";

export default function AdminHeader() {
  const { user } = useAuth();

  return (
    <header className="h-16 border-b bg-background px-6 flex items-center justify-between">
      <div className="relative hidden md:block w-80">
        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />

        <Input
          placeholder="Search posts, products..."
          className="pl-10"
        />
      </div>

      <div className="flex items-center gap-5 ml-auto">
        <Bell className="h-5 w-5 cursor-pointer" />

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