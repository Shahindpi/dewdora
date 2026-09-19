# Dewdora homepage, SEO and analytics

Set `NEXT_PUBLIC_SITE_URL` to the public frontend origin. Set `NEXT_PUBLIC_API_URL` to the browser-accessible Laravel `/api/v1` origin and `API_URL` to its server-accessible equivalent. Set Laravel `APP_URL` to its public origin and run `php artisan storage:link` so uploaded images resolve.

The Next App Router Metadata API emits canonical, Open Graph and Twitter tags in server HTML. Detail pages prefer Laravel SEO overrides, then the visible content title/description/image, then the site default image. `/robots.txt` and `/sitemap.xml` are generated from public records; admin and authentication pages are `noindex`.

## Homepage

The homepage uses separate product collections from `/api/v1/public/homepage`: `latest_products` sorts active products by creation time, while `popular_products` ranks active products by first-party affiliate clicks, then impressions, then creation time. When no events exist, popularity falls back deterministically to recency. The required section order is latest products, popular products, then the first enabled hero banner. Latest products scroll horizontally, with three visible cards on desktop and one on mobile. The popular carousel keeps manual Previous/Next controls. Other sections follow the banner.

Admin → Homepage sections controls the visibility of each section through `PUT /api/v1/admin/settings/homepage`. This stores a JSON visibility map on `site_settings`; hidden sections keep their underlying content. Both the public settings endpoint and the homepage API expose a complete visibility map, with enabled defaults for installations migrated from earlier versions. Homepage cache is invalidated on save.

## Analytics

Configure a single GA4 Measurement ID in the **frontend environment** as `NEXT_PUBLIC_GA_ID=G-XXXXXXXXXX`. The root Next layout loads the script once with `next/script`. If the variable is absent, the site and first-party analytics work without GA4. The historical `google_analytics_id` setting remains readable for compatibility but does not inject scripts. No executable analytics code is stored in product or brand records.

GA4 events include `view_item_list` when a product becomes visible, `select_item` when its detail link is chosen, `view_item` on product detail, and `affiliate_click` on an outbound offer CTA. Event parameters contain actual product, brand, category and network IDs/names, slug and placement where available; empty values are omitted. Placements include `homepage_latest`, `homepage_popular`, `homepage_featured`, `product_page`, `related_products`, and `product_archive`. Register event-scoped GA4 custom dimensions for the parameters you want to filter in reports.

The browser also sends impressions and clicks to `POST /api/v1/public/affiliate-events` with product ID, session UUID and placement. Impressions require visibility and are deduplicated by product and session; clicks remain individual events. Laravel derives brand and network from the product. Admin → Affiliate analytics reports product, brand, network, placement and date counts with product CTR through `GET /api/v1/admin/affiliate-analytics`. Session IDs are random browser identifiers; no personal details are stored in affiliate events.

The CI browser job seeds a fresh database, runs Laravel and production Next, checks metadata, CRUD, image loading, homepage controls, event payloads, responsive sections and login/logout. It blocks the external Google script while inspecting the GA4 event queue, so CI does not send measurement data. Real GA4 ingestion and reporting require your configured property and are verified in GA4 DebugView after deployment.
