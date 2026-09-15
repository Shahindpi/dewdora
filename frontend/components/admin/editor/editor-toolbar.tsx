"use client";

import type { Editor } from "@tiptap/react";

import {
  Bold,
  Italic,
  Underline,
  Heading1,
  Heading2,
  Heading3,
  List,
  ListOrdered,
  Quote,
  Undo2,
  Redo2,
  ImageIcon,
} from "lucide-react";

import { Button } from "@/components/ui/button";

interface Props {
  editor: Editor | null;
  onInsertImage: () => void;
}

export default function EditorToolbar({
  editor,
  onInsertImage,
}: Props) {
  if (!editor) return null;

  const ToolbarButton = ({
    active = false,
    onClick,
    children,
  }: {
    active?: boolean;
    onClick: () => void;
    children: React.ReactNode;
  }) => (
    <Button
        type="button"
        size="sm"
        variant={active ? "default" : "outline"}
        className="cursor-pointer"
        onClick={onClick}
        >
        {children}
    </Button>
  );

  return (
    <div className="flex flex-wrap gap-2 border-b bg-muted/40 p-3">
      <ToolbarButton
        active={editor.isActive("bold")}
        onClick={() =>
          editor.chain().focus().toggleBold().run()
        }
      >
        <Bold className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        active={editor.isActive("italic")}
        onClick={() =>
          editor.chain().focus().toggleItalic().run()
        }
      >
        <Italic className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        active={editor.isActive("underline")}
        onClick={() =>
          editor.chain().focus().toggleUnderline().run()
        }
      >
        <Underline className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        active={editor.isActive("heading", { level: 1 })}
        onClick={() =>
          editor
            .chain()
            .focus()
            .toggleHeading({ level: 1 })
            .run()
        }
      >
        <Heading1 className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        active={editor.isActive("heading", { level: 2 })}
        onClick={() =>
          editor
            .chain()
            .focus()
            .toggleHeading({ level: 2 })
            .run()
        }
      >
        <Heading2 className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        active={editor.isActive("heading", { level: 3 })}
        onClick={() =>
          editor
            .chain()
            .focus()
            .toggleHeading({ level: 3 })
            .run()
        }
      >
        <Heading3 className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        active={editor.isActive("bulletList")}
        onClick={() =>
          editor.chain().focus().toggleBulletList().run()
        }
      >
        <List className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        active={editor.isActive("orderedList")}
        onClick={() =>
          editor.chain().focus().toggleOrderedList().run()
        }
      >
        <ListOrdered className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        onClick={onInsertImage}
      >
        <ImageIcon className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        active={editor.isActive("blockquote")}
        onClick={() =>
          editor.chain().focus().toggleBlockquote().run()
        }
      >
        <Quote className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        onClick={() =>
          editor.chain().focus().undo().run()
        }
      >
        <Undo2 className="h-4 w-4" />
      </ToolbarButton>

      <ToolbarButton
        onClick={() =>
          editor.chain().focus().redo().run()
        }
      >
        <Redo2 className="h-4 w-4" />
      </ToolbarButton>
    </div>
  );
}