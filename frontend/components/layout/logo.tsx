"use client";

import Image from "next/image";
import { imageUrl } from "@/lib/image";

export default function Logo() {
  return (
    <div className="flex items-center gap-3">
      <Image
        src={imageUrl("uploads/settings/81d90c73-57f4-44f1-97b5-0950c07e45c1.png")}
        alt="Dewdora"
        width={36}
        height={36}
        unoptimized
      />

      <div>
        <h1 className="text-lg font-bold leading-none">
          Dewdora
        </h1>

        <p className="text-xs text-muted-foreground">
          AI Affiliate CMS
        </p>
      </div>
    </div>
  );
}