"use client";
import { use } from "react";
import { useQuery } from "@tanstack/react-query";
import { UserForm } from "@/components/admin/users/user-form";
import { getUser } from "@/services/users";
export default function EditUserPage({ params }: { params: Promise<{ id: string }> }) { const { id } = use(params); const { data, isLoading } = useQuery({ queryKey: ["user", id], queryFn: () => getUser(Number(id)) }); if (isLoading || !data) return <p>Loading user…</p>; return <div><h1 className="mb-6 text-3xl font-bold">Edit user</h1><UserForm user={data} /></div>; }
