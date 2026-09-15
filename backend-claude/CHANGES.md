# Changes made to the dewdora backend

Everything below is additive or a targeted fix — no existing working
behavior was changed except where noted as a bug fix. Every file was
syntax-checked with `php -l`.

## Bug fixes

- **`config/core.php` → `config/cors.php`**: this file's contents were
  clearly meant to be the CORS config (the header comment says so), but
  Laravel's `HandleCors` middleware reads `config('cors')`, and a file
  named `core.php` registers under the `core` config key instead. This
  meant CORS was silently using framework defaults, not your intended
  settings — credentialed cross-origin requests from the Next.js frontend
  would likely have failed. Renamed, no content changes.
- **`.env.example`**: added `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS`,
  which the CORS fix and Sanctum's SPA cookie auth both depend on and
  which weren't present before.
- **`AffiliateProductResource`**: was returning the raw `affiliate_url` to
  the public API. Now returns `redirect_url` (`/go/{cloaked_slug}`)
  instead; the raw URL is only included when the authenticated user's role
  is `admin`.

## New: link cloaking

- `database/migrations/2026_08_20_100001_..._affiliate_products_table.php`
  — adds `cloaked_slug`, `disclosure_text`, `click_count` to the existing
  `affiliate_products` table
- `app/Http/Controllers/GoLinkController.php` — resolves `GET /go/{slug}`
  for either an `AffiliateProduct` or `AiTool`, logs an `AnalyticsEvent`,
  increments `click_count`, then 302-redirects to the real URL. Registered
  in `routes/web.php` (not `api.php`) since it issues a redirect.
- `AffiliateProduct` model: `cloaked_slug` auto-generated on create,
  `registerClick()` helper, `faqs()` relation added.

## New: AI Tools directory (was entirely missing)

- Migration: `ai_tools` table, built with cloaking from the start
- `app/Models/AiTool.php`
- `app/Http/Resources/Api/AiToolResource.php`
- `app/Http/Requests/{Store,Update}AiToolRequest.php`
- `app/Http/Controllers/Api/Admin/AiToolController.php` (full CRUD,
  mirrors `Admin/AffiliateProductController` conventions exactly)
- `app/Http/Controllers/Api/Public/AiToolController.php` (mirrors
  `Public/AffiliateProductController`)
- `CacheService::clearAiTool()` added for consistency
- Routes: `Route::apiResource('ai-tools', ...)` under `/api/admin`,
  `GET /api/public/ai-tools` + `/api/public/ai-tools/{slug}`

## New: FAQs (polymorphic — was entirely missing)

- Migration: `faqs` table (`faqable_type`/`faqable_id`)
- `app/Models/Faq.php`
- `app/Http/Resources/Api/FaqResource.php`
- `app/Http/Controllers/Api/Admin/FaqController.php` — attach to a `Post`,
  `AffiliateProduct`, or `AiTool` via `faqable_type`/`faqable_id`
- Added `faqs()` relation to `Post`, `AffiliateProduct`, `AiTool`, and
  included `faqs` in `PostResource`/`AffiliateProductResource`/
  `AiToolResource`
- Routes under `/api/admin/faqs`

## New: internal linking (was entirely missing)

- Migration: `internal_links` table (note: explicit short index name —
  the default Laravel-generated name for this 3-column unique constraint
  exceeds MySQL's 64-character identifier limit)
- `app/Models/InternalLink.php`
- `app/Http/Controllers/Api/Admin/InternalLinkController.php` — `store`/
  `destroy` are admin-only; `forPost()` is exposed as a public read route
  (no side effects) for a "related posts" widget
- Routes: admin write under `/api/admin/internal-links`, public read at
  `GET /api/public/internal-links/post/{postId}`

## New: click/event analytics (was entirely missing)

- Migration: `analytics_events` table
- `app/Models/AnalyticsEvent.php`
- `app/Http/Controllers/Api/AnalyticsEventController.php` — public write
  endpoint (`POST /api/public/analytics/events`), meant to be called via
  `navigator.sendBeacon` from the frontend
- `app/Http/Controllers/Api/Admin/AnalyticsSummaryController.php` — totals
  by event type + top-clicked products/tools (`GET /api/admin/analytics/summary`).
  Separate from the existing `DashboardController` on purpose — that one
  covers overview/monthly stats, this one covers click-level attribution.

## New: newsletter + contact (was entirely missing)

- Migration: `newsletter_subscribers` table
- `app/Models/NewsletterSubscriber.php`
- `app/Http/Controllers/Api/NewsletterController.php` — double opt-in
  subscribe/confirm/unsubscribe. **Note**: the confirmation email itself
  is stubbed as a `TODO` — wire up a real Mailable once mail is configured.
- `app/Http/Controllers/Api/ContactController.php` — no dedicated table on
  purpose (low-volume marketing form, not CMS content); currently just
  logs via `Log::info()`. Swap for a real Mailable the same way.
- Routes under `/api/public/newsletter/*` and `/api/public/contact`

## New: SEO feeds

- `app/Http/Controllers/Api/SeoController.php`:
  - `sitemapData()` → `GET /api/public/seo/sitemap-data` — feeds the
    frontend's `app/sitemap.ts`
  - `rss()` → `GET /api/public/feed.xml` — full RSS 2.0 feed of published
    posts
- `PostResource`: added `url_path` (computed per `post_type`, e.g.
  `/reviews/{slug}`) and `faqs`

## New: `PostType::DEAL`

The enum previously only had `article`, `review`, `comparison`,
`tutorial`, `news` — no case for a "deal" write-up (as opposed to the raw
`AffiliateProduct` catalog entry). Added `DEAL = 'deal'`.

## New: seeder

- `database/seeders/AdminUserSeeder.php` — creates `admin@example.com` /
  `password` with the `admin` role, since no user seeder existed at all.
  Registered in `DatabaseSeeder`.

## Route map for everything new

| Method | Path | Auth |
|---|---|---|
| GET/POST/PUT/DELETE | `/api/admin/ai-tools[/{id}]` | admin |
| POST/PUT/DELETE | `/api/admin/faqs[/{id}]` | admin |
| POST/DELETE | `/api/admin/internal-links[/{id}]` | admin |
| GET | `/api/admin/analytics/summary` | admin |
| GET | `/api/public/ai-tools[/{slug}]` | — |
| GET | `/api/public/internal-links/post/{postId}` | — |
| POST | `/api/public/newsletter/subscribe` | — |
| GET | `/api/public/newsletter/confirm/{token}` | — |
| POST | `/api/public/newsletter/unsubscribe` | — |
| POST | `/api/public/contact` | — |
| POST | `/api/public/analytics/events` | — |
| GET | `/api/public/seo/sitemap-data` | — |
| GET | `/api/public/feed.xml` | — |
| GET (web, not `/api`) | `/go/{slug}` | — |

## Run this after pulling these changes

```bash
composer install   # only if you don't already have the packages this needs (none new)
php artisan migrate
php artisan db:seed --class=AdminUserSeeder   # or just re-run the full seeder
```
