import { rmSync } from "node:fs";
import { resolve } from "node:path";

// A stopped or concurrently running development server can leave partially
// written declarations here. Production type generation uses .next/types, so
// removing only the development declarations is safe before build/typecheck.
rmSync(resolve(".next", "dev", "types"), { recursive: true, force: true });
