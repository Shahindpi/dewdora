"use client";

import { useEffect, useState } from "react";
import slugify from "slugify";
import {
  Controller,
  useForm,
} from "react-hook-form";

import { zodResolver } from "@hookform/resolvers/zod";

import {
  useMutation,
  useQuery,
  useQueryClient,
} from "@tanstack/react-query";

import { toast } from "sonner";
import { useRouter } from "next/navigation";

import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";

import {
  postSchema,
  PostFormValues,
} from "@/schemas/post-schema";

import api from "@/lib/axios";
import { getCategories } from "@/services/categories";

import {
  createPost,
  updatePost,
} from "@/services/posts";

import PostSlugInput from "./post-slug-input";
import PostEditorSidebar from "./post-editor-sidebar";

import RichTextEditor from "@/components/admin/editor/rich-text-editor";

interface Props {
  mode: "create" | "edit";

  post?: Partial<PostFormValues> & {
    id?: number;
    featured_image?: string | null;
    tags?: { id: number; name: string }[];
    seo?: { meta_title?: string; meta_description?: string; canonical_url?: string };
  };
}

export default function PostForm({
  mode,
  post,
}: Props) {
  const router = useRouter();
  const queryClient = useQueryClient();

  /*
  |--------------------------------------------------------------------------
  | Featured Image
  |--------------------------------------------------------------------------
  */

  const [tagIds, setTagIds] = useState<number[]>(post?.tags?.map(tag => tag.id) || []);
  const [seoTitle, setSeoTitle] = useState(post?.seo?.meta_title || "");
  const [seoDescription, setSeoDescription] = useState(post?.seo?.meta_description || "");
  const [canonicalUrl, setCanonicalUrl] = useState(post?.seo?.canonical_url || "");
  const { data: availableTags = [] } = useQuery({ queryKey: ["post-tags"], queryFn: async () => (await api.get("/admin/tags", { params: { per_page: 50 } })).data.data as { id: number; name: string }[] });

  const [featuredImage, setFeaturedImage] =
    useState<string | null>(
      post?.featured_image ?? null
    );

  /*
  |--------------------------------------------------------------------------
  | Form
  |--------------------------------------------------------------------------
  */

  const {
    register,
    control,
    handleSubmit,
    watch,
    setValue,
    reset,
    formState: {
      errors,
      isSubmitting,
    },
  } = useForm<PostFormValues>({
    resolver: zodResolver(postSchema),

    defaultValues: {
      title: "",
      slug: "",
      excerpt: "",
      content: "",
      status: "draft",
      post_type: "article",
      allow_comments: true,
      category_id: 0,
    },
  });

  const { data: categories = [] } = useQuery({ queryKey: ["post-categories"], queryFn: getCategories });

  const title = watch("title");
  const slug = watch("slug");
  const status = watch("status");

  /*
  |--------------------------------------------------------------------------
  | Populate Form In Edit Mode
  |--------------------------------------------------------------------------
  */

  useEffect(() => {
    if (mode !== "edit" || !post) {
      return;
    }

    reset({
      title: post.title ?? "",
      slug: post.slug ?? "",
      excerpt: post.excerpt ?? "",
      content: post.content ?? "",
      status: post.status ?? "draft",
      post_type: post.post_type ?? "article",
      allow_comments:
        post.allow_comments ?? true,
      category_id:
        post.category_id ?? 0,
    });

    setFeaturedImage(post.featured_image ?? null);
    setTagIds(post.tags?.map(tag => tag.id) || []);
    setSeoTitle(post.seo?.meta_title || "");
    setSeoDescription(post.seo?.meta_description || "");
    setCanonicalUrl(post.seo?.canonical_url || "");
  }, [
    mode,
    post,
    reset,
  ]);

  /*
  |--------------------------------------------------------------------------
  | Auto Generate Slug
  |--------------------------------------------------------------------------
  */

  useEffect(() => {
    if (mode !== "create") {
      return;
    }

    const generatedSlug = slugify(
      title || "",
      {
        lower: true,
        strict: true,
      }
    );

    setValue(
      "slug",
      generatedSlug,
      {
        shouldDirty: true,
        shouldTouch: true,
        shouldValidate: true,
      }
    );
  }, [
    title,
    mode,
    setValue,
  ]);

  /*
  |--------------------------------------------------------------------------
  | Save Mutation
  |--------------------------------------------------------------------------
  */

  const mutation = useMutation({
    mutationFn: async (
      values: PostFormValues
    ) => {
      const payload = {
        ...values,
        category_id: values.category_id || null,
        featured_image: featuredImage,
      };

      console.log(
        "POST FORM -> API PAYLOAD:",
        payload
      );

      const saved = mode === "edit" && post?.id ? await updatePost(post.id, payload) : await createPost(payload);
      if (saved?.id) {
        await api.put(`/admin/posts/${saved.id}/tags`, { tag_ids: tagIds });
        if (seoTitle || seoDescription || canonicalUrl) {
          await api.put(`/admin/posts/${saved.id}/seo`, { meta_title: seoTitle || null, meta_description: seoDescription || null, canonical_url: canonicalUrl || null });
        }
      }
      return saved;
    },

    onSuccess: async () => {
      console.log(
        "POST FORM -> SAVE SUCCESS"
      );

      await queryClient.invalidateQueries({
        queryKey: ["posts"],
      });

      toast.success(
        mode === "create"
          ? "Post created successfully."
          : "Post updated successfully."
      );

      router.push(
        "/admin/posts"
      );
    },

    onError: (error: { response?: { data?: { message?: string } }; message?: string }) => {
      console.error(
        "POST FORM -> SAVE ERROR:",
        error
      );

      toast.error(
        error?.response?.data?.message ??
          error?.message ??
          "Unable to save post."
      );
    },
  });

  /*
  |--------------------------------------------------------------------------
  | Submit
  |--------------------------------------------------------------------------
  */

  const onSubmit = (
    values: PostFormValues
  ) => {
    console.log(
      "POST FORM -> VALID SUBMIT:",
      values
    );

    mutation.mutate(values);
  };

  /*
  |--------------------------------------------------------------------------
  | Invalid Submit
  |--------------------------------------------------------------------------
  */

  const onInvalid = (
    formErrors: typeof errors
  ) => {
    console.error(
      "POST FORM -> VALIDATION ERRORS:",
      formErrors
    );

    const firstError =
      Object.values(formErrors)[0];

    if (firstError?.message) {
      toast.error(
        String(firstError.message)
      );
    } else {
      toast.error(
        "Please check the form fields."
      );
    }
  };

  /*
  |--------------------------------------------------------------------------
  | Explicit Save Handler
  |--------------------------------------------------------------------------
  |
  | This bypasses relying on the browser's native form submit event.
  | React Hook Form performs validation and then calls onSubmit.
  |
  */

  const handleSave = () => {
    console.log(
      "POST FORM -> SAVE BUTTON CLICKED"
    );

    handleSubmit(
      onSubmit,
      onInvalid
    )();
  };

  /*
  |--------------------------------------------------------------------------
  | Render
  |--------------------------------------------------------------------------
  */

  return (
    <form
      onSubmit={(event) => {
        event.preventDefault();

        console.log(
          "POST FORM -> FORM SUBMIT EVENT"
        );

        handleSubmit(
          onSubmit,
          onInvalid
        )();
      }}
      className="grid grid-cols-1 gap-8 xl:grid-cols-[1fr_320px]"
    >
      {/* ------------------------------------------------------------------ */}
      {/* Main Editor */}
      {/* ------------------------------------------------------------------ */}

      <div className="space-y-6 rounded-2xl border bg-background p-6">

        {/* Title */}
        <div className="space-y-2">
          <label
            htmlFor="title"
            className="text-sm font-medium"
          >
            Title
          </label>

          <Input
            id="title"
            placeholder="Enter post title..."
            {...register("title")}
          />

          {errors.title && (
            <p className="text-sm text-destructive">
              {errors.title.message}
            </p>
          )}
        </div>

        {/* Slug */}
        <PostSlugInput
          value={slug}
          onChange={(value) => {
            setValue(
              "slug",
              value,
              {
                shouldDirty: true,
                shouldTouch: true,
                shouldValidate: true,
              }
            );
          }}
        />

        <input
          type="hidden"
          {...register("slug")}
        />

        {errors.slug && (
          <p className="text-sm text-destructive">
            {errors.slug.message}
          </p>
        )}

        {/* Excerpt */}
        <div className="space-y-2">
          <label
            htmlFor="excerpt"
            className="text-sm font-medium"
          >
            Excerpt
          </label>

          <Textarea
            id="excerpt"
            rows={4}
            placeholder="Short description for blog listing and SEO..."
            {...register("excerpt")}
          />

          {errors.excerpt && (
            <p className="text-sm text-destructive">
              {errors.excerpt.message}
            </p>
          )}
        </div>

        <div className="space-y-2"><label htmlFor="category_id" className="text-sm font-medium">Category</label><select id="category_id" {...register("category_id", { valueAsNumber: true })} className="w-full rounded-lg border p-3"><option value="0">No category</option>{categories.map(category => <option key={category.id} value={category.id}>{category.name}</option>)}</select></div>
        <div className="space-y-2"><label htmlFor="post_type" className="text-sm font-medium">Post type</label><select id="post_type" {...register("post_type")} className="w-full rounded-lg border p-3">{["article", "review", "comparison", "tutorial", "news"].map(type => <option key={type} value={type}>{type}</option>)}</select></div>

        {/* Content */}
        <div className="space-y-2">
          <label className="text-sm font-medium">
            Content
          </label>

          <Controller
            name="content"
            control={control}
            render={({ field }) => (
              <RichTextEditor
                value={field.value ?? ""}
                onChange={field.onChange}
              />
            )}
          />

          {errors.content && (
            <p className="text-sm text-destructive">
              {errors.content.message}
            </p>
          )}
        </div>

        <div className="space-y-3"><h3 className="text-sm font-medium">Tags</h3><div className="flex flex-wrap gap-3">{availableTags.map(tag => <label key={tag.id} className="flex items-center gap-2 text-sm"><input type="checkbox" checked={tagIds.includes(tag.id)} onChange={e => setTagIds(ids => e.target.checked ? [...ids, tag.id] : ids.filter(id => id !== tag.id))} />{tag.name}</label>)}</div></div>
        <div className="space-y-3 rounded-xl border p-4"><h3 className="font-semibold">Search metadata</h3><label className="block text-sm">Meta title<input value={seoTitle} onChange={e => setSeoTitle(e.target.value)} maxLength={255} className="mt-2 w-full rounded-lg border p-3" /></label><label className="block text-sm">Meta description<textarea value={seoDescription} onChange={e => setSeoDescription(e.target.value)} maxLength={500} rows={3} className="mt-2 w-full rounded-lg border p-3" /></label><label className="block text-sm">Canonical URL<input type="url" value={canonicalUrl} onChange={e => setCanonicalUrl(e.target.value)} className="mt-2 w-full rounded-lg border p-3" /></label></div>

        {/* Allow Comments */}
        <div className="flex items-center gap-3 rounded-xl border p-4">
          <input
            id="allow_comments"
            type="checkbox"
            className="h-4 w-4 rounded border-gray-300"
            {...register("allow_comments")}
          />

          <label
            htmlFor="allow_comments"
            className="text-sm font-medium"
          >
            Allow comments on this post
          </label>
        </div>

        {/* Submit */}
        <Button
          type="button"
          onClick={handleSave}
          className="w-full md:w-auto"
          disabled={
            mutation.isPending ||
            isSubmitting
          }
        >
          {mutation.isPending ||
          isSubmitting
            ? mode === "create"
              ? "Publishing..."
              : "Saving..."
            : mode === "create"
              ? "Publish Post"
              : "Save Changes"}
        </Button>
      </div>

      {/* ------------------------------------------------------------------ */}
      {/* Sidebar */}
      {/* ------------------------------------------------------------------ */}

      <PostEditorSidebar
        status={status}
        setStatus={(value) => {
          setValue(
            "status",
            value,
            {
              shouldDirty: true,
              shouldTouch: true,
              shouldValidate: true,
            }
          );
        }}
        featuredImage={featuredImage}
        setFeaturedImage={
          setFeaturedImage
        }
      />
    </form>
  );
}