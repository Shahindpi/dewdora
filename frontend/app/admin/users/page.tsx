"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { apiErrorMessage } from "@/lib/api-error";
import { deleteUser, getUsers } from "@/services/users";
import type { PaginationMeta } from "@/types/api";
import type { User } from "@/types/user";

export default function UsersPage() {
  const [users, setUsers] = useState<User[]>([]);
  const [meta, setMeta] = useState<PaginationMeta>();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState("");
  const [revision, setRevision] = useState(0);

  useEffect(() => {
    getUsers({ page, search: search || undefined }).then(({ users: result, meta: pagination }) => { setUsers(result); setMeta(pagination); }).catch(error => toast.error(apiErrorMessage(error, "Could not load users.")));
  }, [page, search, revision]);

  async function remove(user: User) {
    if (!window.confirm(`Delete ${user.name}? This cannot be undone.`)) return;
    try { await deleteUser(user.id); toast.success("User deleted."); setRevision(value => value + 1); }
    catch (error) { toast.error(apiErrorMessage(error, "Could not delete user.")); }
  }

  return <div><div className="flex flex-wrap items-center justify-between gap-4"><div><h1 className="text-3xl font-bold">Users</h1><p className="mt-1 text-muted-foreground">Manage administrators, editors and authors.</p></div><Link href="/admin/users/new" className="rounded-lg bg-primary px-5 py-3 text-primary-foreground">Add user</Link></div>
    <input type="search" value={search} onChange={event => { setSearch(event.target.value); setPage(1); }} placeholder="Search users" className="mt-6 w-full max-w-sm rounded-lg border p-3" />
    <div className="mt-6 overflow-x-auto rounded-2xl border bg-background"><table className="w-full min-w-[760px] text-left text-sm"><thead className="bg-muted/50"><tr><th className="p-4">Name</th><th className="p-4">Email</th><th className="p-4">Role</th><th className="p-4">Status</th><th className="p-4">Created</th><th className="p-4">Actions</th></tr></thead><tbody>{users.map(user => <tr key={user.id} className="border-t"><td className="p-4 font-medium">{user.name}<span className="block text-xs text-muted-foreground">@{user.username}</span></td><td className="p-4">{user.email}</td><td className="p-4">{user.role?.name || "—"}</td><td className="p-4"><span className={`rounded-full px-2 py-1 text-xs ${user.status ? "bg-emerald-100 text-emerald-800" : "bg-slate-100 text-slate-600"}`}>{user.status ? "Active" : "Inactive"}</span></td><td className="p-4">{user.created_at ? new Date(user.created_at).toLocaleDateString() : "—"}</td><td className="p-4"><Link href={`/admin/users/${user.id}`} className="mr-4 text-primary">Edit</Link><button onClick={() => remove(user)} className="text-destructive">Delete</button></td></tr>)}</tbody></table>{!users.length && <p className="p-6 text-muted-foreground">No users found.</p>}</div>
    {meta && meta.last_page > 1 && <div className="mt-5 flex gap-3"><button disabled={page <= 1} onClick={() => setPage(page - 1)} className="rounded border px-3 py-2 disabled:opacity-40">Previous</button><span className="py-2">{page} / {meta.last_page}</span><button disabled={page >= meta.last_page} onClick={() => setPage(page + 1)} className="rounded border px-3 py-2 disabled:opacity-40">Next</button></div>}
  </div>;
}
