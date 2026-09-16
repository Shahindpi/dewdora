"use client";

import Image from "next/image";
import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { getSettings } from "@/services/settings";

export default function Logo() {
  const [imageFailed, setImageFailed] = useState(false);
  const { data: settings } = useQuery({ queryKey: ["public-settings"], queryFn: getSettings, staleTime: 1000 * 60 * 30 });
  return (
    <div className="flex items-center gap-3">
      {settings?.logo && !imageFailed ? <Image src={settings.logo} alt={`${settings.site_name || "Dewdora"} logo`} width={40} height={40} unoptimized onError={() => setImageFailed(true)} /> : <span aria-hidden className="grid h-10 w-10 place-items-center rounded-xl bg-emerald-700 font-black text-white">D</span>}

      <div>
        <h1 className="text-lg font-bold leading-none">
          {settings?.site_name || "Dewdora"}
        </h1>

        <p className="text-xs text-muted-foreground">
          AI Affiliate CMS
        </p>
      </div>
    </div>
  );
}
