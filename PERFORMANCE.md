# Dewdora production performance

## Reproducible baseline

The audit uses a seeded Laravel API and a Next.js production build. `frontend/scripts/performance-baseline.mjs` records response/load timing, transferred bytes, JavaScript bytes, image bytes, request count and cumulative layout shift for the homepage, product/category/post lists and details, login, dashboard, admin products, admin posts and analytics. Timings vary by machine; compare JSON files made on the same host and database state.

Before provider isolation, route-referenced JavaScript from the production client-reference manifests was:

| Route | Before | After provider/image group | Change |
| --- | ---: | ---: | ---: |
| Homepage | 209,603 B | 115,539 B | -44.9% |
| Products/categories/posts list | 196,274 B | 51,496 B | -73.8% |
| Product detail | 196,604 B | 51,826 B | -73.6% |
| Post detail | 199,728 B | 105,589 B | -47.1% |
| Login | 627,068 B | 598,045 B | -4.6% |
| Admin dashboard | 365,171 B | 365,614 B | effectively unchanged |

The public reduction comes from scoping Redux, React Query, authentication, theme and toast providers to `/admin` or `/auth`, and rendering post cards on the server. Admin functionality retains those dependencies, while the rich-text editor is dynamically loaded only by post/product forms. Public product/post media now uses responsive AVIF/WebP delivery through Next Image with stable dimensions and lazy loading; only above-the-fold detail media, the first latest-product image and site logo are prioritized.

The post-change production benchmark on GitHub's Ubuntu runner (seeded SQLite, local Laravel and Next production servers) recorded:

| Page | Response end | Load | Transfer | CLS |
| --- | ---: | ---: | ---: | ---: |
| Homepage | 155 ms | 608 ms | 257 KB | 0 |
| Products | 43 ms | 82 ms | 203 KB | 0 |
| Product detail | 48 ms | 239 ms | 191 KB | 0.0058 |
| Categories | 36 ms | 55 ms | 349 KB | 0 |
| Category detail | 42 ms | 65 ms | 198 KB | 0 |
| Posts | 48 ms | 72 ms | 202 KB | 0 |
| Post detail | 57 ms | 269 ms | 211 KB | 0.0005 |
| Login | 6 ms | 68 ms | 307 KB | 0.0001 |
| Admin dashboard | 7 ms | 58 ms | 389 KB | 0 |

Warm API medians were 45.93 ms for the rich homepage response, 12.88 ms for products, 14.24 ms for product detail, 9.27 ms for categories, 14.69 ms for category detail, 13.53 ms for posts, 15.70 ms for post detail, 12.71 ms for the admin dashboard, and 17.13 ms for admin products. These figures are CI baselines, not Internet user timings; retain the generated JSON artifact and compare future runs on the same runner/database shape.

## Backend changes

The homepage remains cached for six hours and preserves its response contract. Product detail now caches the product plus related products/posts under the public cache version. Related post recommendations and category detail result pages are cached with resource version, slug and pagination parameters. New composite indexes cover latest products, category/brand product listings, real-event popularity counts, popular posts and category posts.

No new environment variable is required. For production use Redis for `CACHE_STORE` where available, enable HTTP Brotli/gzip at the reverse proxy, run queue workers separately, use OPcache, and serve storage through a CDN whose host is added to `images.remotePatterns`. Run `php artisan migrate --force` before warming caches.

## Commands

```bash
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan test

cd ../frontend
npm ci
npm run lint
npm run typecheck
npm run build
npm run start
PERF_BASE_URL=http://127.0.0.1:3000 PERF_ADMIN_EMAIL=admin@example.com PERF_ADMIN_PASSWORD='your-password' npm run performance

cd ../backend
PERF_API_URL=http://127.0.0.1:8000/api/v1 PERF_ADMIN_EMAIL=admin@example.com PERF_ADMIN_PASSWORD='your-password' python3 scripts/benchmark-public-api.py
```

Remaining bottlenecks include the deliberately rich homepage payload, per-view post counter write, browser analytics requests, external affiliate destinations, and first-request image transformation cost. Next Image transformations should be cached at the CDN/proxy. Offset pagination remains appropriate for the current archive size; cursor pagination would be a contract change and was not introduced.
