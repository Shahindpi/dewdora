"use client";

import Link from "next/link";

import { PopularPost } from "@/types/dashboard";

interface Props {
  posts: PopularPost[];
}

export default function PopularPosts({
  posts,
}: Props) {
  return (
    <div className="rounded-2xl border bg-background p-6">
      <h2 className="text-xl font-semibold mb-6">
        Popular Posts
      </h2>

      <div className="space-y-4">
        {posts.map((post, index) => (
          <div
            key={post.id}
            className="flex items-center justify-between border-b pb-4 last:border-none"
          >
            <div>
              <p className="font-medium">
                {index + 1}. {post.title}
              </p>

              <p className="text-xs text-muted-foreground">
                /{post.slug}
              </p>
            </div>

            <div className="text-right">
              <p className="font-semibold">
                {post.views.toLocaleString()}
              </p>

              <p className="text-xs text-muted-foreground">
                views
              </p>
            </div>
          </div>
        ))}
      </div>

      <Link
        href={"/admin/posts" as any}
        className="mt-6 inline-block text-primary text-sm font-medium"
        >
        View All Posts →
    </Link>
    </div>
  );
}