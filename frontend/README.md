# Dewdora frontend

Next.js 16 frontend for the Dewdora Laravel API in `../backend`. The public site provides articles, products, categories, tags, brands, search, comments, contact and newsletter signup. The authenticated admin provides dashboard, posts, categories, tags, affiliate products, brands, networks, media, comment/contact moderation, subscribers, profile and site settings.

Copy `.env.example` to `.env.local` and set `NEXT_PUBLIC_API_URL` to the **versioned API root** (`/api/v1`), not `/api` or `/api/v1/public`. Set `NEXT_PUBLIC_STORAGE_URL` to the Laravel `/storage` URL. Laravel must allow the frontend origin in `CORS_ALLOWED_ORIGINS` and expose uploaded files with `php artisan storage:link`.

Run `npm ci`, `npm run dev`, `npx tsc --noEmit`, `npm run lint`, and `npm run build`. The public pages request current content from Laravel at render time; publish the Laravel API at an address reachable from the Next.js server. If necessary, configure server-only `API_URL` separately. Admin authentication uses a Sanctum bearer token stored in the browser.
