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
      category_id: 1,
    },
  });

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
        post.category_id ?? 1,
    });

    setFeaturedImage(
      post.featured_image ?? null
    );
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
        featured_image: featuredImage,
      };

      console.log(
        "POST FORM -> API PAYLOAD:",
        payload
      );

      if (
        mode === "edit" &&
        post?.id
      ) {
        console.log(
          "POST FORM -> UPDATING POST:",
          post.id
        );

        return updatePost(
          post.id,
          payload
        );
      }

      console.log(
        "POST FORM -> CREATING POST"
      );

      return createPost(payload);
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
        "/admin/posts" as any
      );
    },

    onError: (error: any) => {
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