"use client";

import { useQuery } from "@tanstack/react-query";

import PageTitle from "@/components/admin/page-title";
import PostSearch from "@/components/admin/post-search";
import PostStatusFilter from "@/components/admin/post-status-filter";
import PostsTable from "@/components/admin/posts-table";

import { useAppSelector } from "@/store/hooks";

import { getPosts } from "@/services/posts";
import TablePagination from "@/components/admin/table-pagination";

export default function PostsPage() {
  const filters = useAppSelector(
    (state) => state.postFilters
  );

    const { data: posts, isLoading } = useQuery({
    queryKey: ["posts", filters],
    queryFn: () => getPosts(filters),
    });

    console.log(posts);

  return (
    <main className="space-y-6">
      <PageTitle
        title="Posts"
        description="Manage blog posts and AI tool reviews." />

      <div className="flex flex-col md:flex-row gap-4 justify-between">
        <PostSearch />

        <PostStatusFilter />
      </div>

      {isLoading ? (
        <div className="h-80 rounded-xl bg-muted animate-pulse" />
      ) : (
        <>
  <PostsTable posts={posts?.posts ?? []} />

    {posts?.meta && (
        <TablePagination
        current={posts.meta.current_page}
        last={posts.meta.last_page}
        />
    )}
</>
      )}
    </main>
  );
}