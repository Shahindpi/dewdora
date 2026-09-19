"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { useRouter } from "next/navigation";

import { AuthContext } from "@/contexts/auth-context";

import {
  clearAuthStorage,
  getToken,
  setStoredUser,
  setToken,
} from "@/lib/storage";

import {
  login as loginRequest,
  logout as logoutRequest,
  me,
} from "@/services/auth";

import { User } from "@/types/user";

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  const authenticated = !!user;

  const refreshUser = useCallback(async () => {
    setLoading(true);

    const token = getToken();

    if (!token) {
      setUser(null);
      setLoading(false);
      return;
    }

    try {
      const currentUser = await me();
      setUser(currentUser);
      setStoredUser(currentUser);
    } catch {
      clearAuthStorage();
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const response = await loginRequest({ email, password });

    setToken(response.token);
    setStoredUser(response.user);
    setUser(response.user);
  }, []);

  const logout = useCallback(async () => {
    try {
      await logoutRequest();
    } finally {
      clearAuthStorage();
      setUser(null);

      router.replace("/auth/login");
    }
  }, [router]);

  useEffect(() => {
    const unauthorized = () => {
      setUser(null);
      router.replace("/auth/login");
    };
    window.addEventListener("dewdora:unauthorized", unauthorized);
    return () =>
      window.removeEventListener("dewdora:unauthorized", unauthorized);
  }, [router]);

  useEffect(() => {
    void Promise.resolve().then(refreshUser);
  }, [refreshUser]);

  const value = useMemo(
    () => ({
      user,

      loading,

      authenticated,

      login,

      logout,

      refreshUser,
    }),
    [user, loading, authenticated, login, logout, refreshUser],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
