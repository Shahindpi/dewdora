"use client";

import AuthStatus from "@/components/common/auth-status";

export default function HomePage() {
  return (
    <main className="p-10 space-y-6">
      <h1 className="text-3xl font-bold">
        Dewdora Frontend
      </h1>

      <AuthStatus />
    </main>
  );
}