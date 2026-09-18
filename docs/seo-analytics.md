# Dewdora SEO, analytics and homepage configuration

Set `NEXT_PUBLIC_SITE_URL` to the public frontend origin in each environment. Set `NEXT_PUBLIC_API_URL` to the browser-accessible Laravel `/api/v1` origin and `API_URL` to its server-accessible equivalent. Set Laravel `APP_URL` to its public origin so `/storage` image URLs are absolute. Run `php artisan storage:link` after deployment.

The Next App Router Metadata API emits canonical, Open Graph and Twitter tags in server HTML. Content detail pages use the Laravel `seo` fields first, then the visible content title/description/image, then a site OG PNG. The frontend serves `/robots.txt` and `/sitemap.xml`; the latter fetches every page of public products, posts, categories and brands and revalidates hourly. Admin and auth pages use `noindex`.

The public header uses the uploaded Site Settings logo when available and a bundled Dewdora wordmark when none is configured. Upload a logo in Admin → Settings. Create hero banners in Admin → Hero banners; image paths may come from the media library. Enabled banners are ordered by `sort_order` and ID. The first enabled banner is displayed after the affiliate product carousel.

The existing Site Settings `google_analytics_id` stores one GA4 Measurement ID (for example `G-XXXXXXXXXX`). Only that public ID is exposed to the browser. With no configured ID, the tracking calls are inert. In development, emitted events also appear in the browser console as `[Dewdora analytics]`.

- `affiliate_product_impression`: fires once per visible product card during a carousel mount when IntersectionObserver reports at least 60% visibility; carries actual `product_id`, `product_name`, `brand_id`, `brand_name`, `category`, `position`, `carousel_name` where available.
- `affiliate_click`: fires when an affiliate CTA is clicked, without delaying navigation; carries actual `product_id`, `product_name`, `brand_id`, `brand_name`, `network_id`, `network_name`, `destination`, `placement` where available. Affiliate destinations remain the backend product's configured URL.

In GA4, register event-scoped custom dimensions for `brand_id`, `brand_name`, `product_id` and `placement` to filter product and brand activity in reports. Each product uses the same site Measurement ID; no product-specific analytics script is needed.

The browser acceptance job seeds eight active products, a post, hero banner and admin account into an isolated SQLite database. It runs Laravel and the production Next app, checks the initial HTML metadata and canonical tags, CRUD, carousel bounds, images, GA event parameters and responsive navigation. The test blocks the external Google script while inspecting queued GA4 events, so no measurement is sent from CI.
