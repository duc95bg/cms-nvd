---
name: cms-landing-builder
description: Multi-language landing page builder — users create sites from Blade templates, content stored as locale-keyed JSON
status: backlog
created: 2026-04-15T04:24:00Z
---

# PRD: cms-landing-builder

## Executive Summary
A lightweight multi-tenant CMS that lets authenticated users spin up landing pages from a library of Blade templates. Content is stored as a single JSON blob per site with every translatable field keyed by locale, so adding a new language never requires schema changes. Public pages render at `/{locale}/site/{slug}` using the locale-switching middleware already shipped by the `multi-language` epic.

## Problem Statement
The team needs to ship marketing landing pages in both Vietnamese and English without writing one-off Blade files per campaign. Current options (raw Blade edits, duplicated files per language) don't scale: every new language doubles maintenance, and marketers can't self-serve content edits. We need a way to pick a template, fill in content for every supported language in one screen, and publish — with rendering that just works across locales.

## User Stories

### US-1 — Create a site from a template
**As** a CMS user
**I want** to pick a template (product / service / blog) and assign a slug
**So that** I have a draft site seeded with sensible default content I can edit.

**Acceptance criteria:**
- I can view a list of available templates grouped by type.
- I can submit a form with `template_id` + `slug`; slug is validated `[a-z0-9-]+`, unique.
- On success, a new `sites` row is created with `content` copied from the chosen template's `default_content`, owned by me, `published=false`.
- I am redirected to the edit screen for the new site.

### US-2 — Edit content in all supported languages at once
**As** a CMS user
**I want** a single form that shows every translatable field with one input per supported locale
**So that** I don't have to switch screens to keep translations in sync.

**Acceptance criteria:**
- The form auto-generates fields by traversing `content`; translatable leaves (objects whose keys are locale codes) render as side-by-side inputs (one per locale).
- Indexed list nodes (repeaters like `features.items`) render as a raw JSON textarea so advanced users can edit them without breaking the schema.
- Submitting saves the nested JSON shape back to `sites.content`; a flash message confirms the save.
- Changing the `published` toggle persists on save.

### US-3 — Preview an unpublished site
**As** a CMS user
**I want** to preview my draft before publishing
**So that** I can catch content problems without exposing the URL.

**Acceptance criteria:**
- `GET /admin/sites/{site}/preview` renders the site using its template view, even when `published=false`.
- Only the site owner can access the preview route (403 otherwise).

### US-4 — Render a public site
**As** a visitor
**I want** to load `/vi/site/my-slug` or `/en/site/my-slug`
**So that** I see the correct language and can switch without losing context.

**Acceptance criteria:**
- `GET /{locale}/site/{slug}` where `locale in {en, vi}` returns 200 and renders the template view.
- Unsupported locale (`/fr/site/...`) returns 404 from the route constraint.
- The existing language switcher (`partials.lang-switcher`) appears in the shared layout and swaps locale while preserving the slug.
- Unpublished sites return 404 on the public route.

### US-5 — Upload images used in content
**As** a CMS user
**I want** to upload images from the edit screen
**So that** I can reference them in my content JSON.

**Acceptance criteria:**
- `POST /admin/sites/{site}/images` accepts a `multipart/form-data` image, stores under `storage/app/public/sites/{site_id}/`, inserts a `media` row.
- Response includes `{ id, url }` so the UI can insert the URL into a content field.
- Non-image or >4MB uploads fail validation.
- Only the site owner can upload.

## Functional Requirements

### FR-1 — Database schema
- `templates(id, name, type, view, default_content JSON, timestamps)`
- `sites(id, user_id FK, template_id FK, slug UNIQUE, content JSON, published BOOL, timestamps)`
- `media(id, site_id FK, path, disk, mime, size, timestamps)`

### FR-2 — Content model
- All translatable text is an object keyed by locale code: `{ "vi": "...", "en": "..." }`.
- No columns like `title_vi`, `title_en`. Adding a new locale means adding a key to JSON and to the `SUPPORTED` array — zero migrations.
- Non-translatable content (URLs, arrays of objects, booleans) stored as-is inside the same JSON tree.

### FR-3 — Rendering helper
- `Site::t(string $key, ?string $locale = null, string $default = ''): string` does `data_get($content, "$key.$locale")` with fallback to `config('app.fallback_locale')` then `$default`.
- Template Blade files use `{{ $site->t('hero.title') }}` — no manual locale juggling.

### FR-4 — Routing
- Public: `GET /{locale}/site/{slug}` with `where(['locale' => 'en|vi', 'slug' => '[a-z0-9\-]+'])`, name `site.show`.
- Admin: `admin/sites` CRUD + `preview` + `images/upload`, name prefix `admin.sites.*`.

### FR-5 — Locale enforcement (2 layers)
- Route constraint (`en|vi`) → 404 before hitting middleware.
- `SetLocale` middleware (already shipped) → reads segment, sets `app()->setLocale()`, persists to session for cross-prefix navigation.

### FR-6 — Templates shipped on day one
- `product` template (hero / features repeater / pricing CTA) — fully implemented and seeded with default_content.
- `service` and `blog` types are accepted at the schema level but their Blade files are stubs pointing to `templates.product` until follow-up work fills them in.

### FR-7 — Admin authorization
- Only the owner (`sites.user_id === request()->user()->id`) can edit / update / preview / upload for a site.
- Auth middleware is left commented out in the admin route group until Breeze/Jetstream is wired — tracked as a dependency, not a blocker for this epic.

## Non-Functional Requirements

- **Scalability of locales**: adding a third locale must not touch any migration. Verified by changing only `SUPPORTED` + JSON.
- **No new Composer dependencies.** Use Laravel core + Tailwind CDN.
- **Tailwind via CDN** on template views — avoids Vite build pipeline for this iteration.
- **Image storage**: local `public` disk; `php artisan storage:link` documented as a setup step.
- **Test coverage**: feature tests for the public render path + admin create → edit → update → preview happy path.

## Success Criteria

- A user can create a `product` site, fill Vietnamese + English content, publish, and see it rendered at both `/vi/site/<slug>` and `/en/site/<slug>` within one session.
- Language switch on the rendered page swaps content without losing the slug.
- `php artisan test --filter=SiteCms` is green for all shipped test cases.
- Adding a hypothetical third locale (e.g. `ja`) requires only: adding `ja` to `SUPPORTED`, adding `ja` keys to existing JSON content, creating `lang/ja.json`. No migration, no controller change, no template change.

## Constraints & Assumptions

- Laravel 11 + Blade + Tailwind CDN + MySQL (dev can use SQLite for tests).
- `multi-language` epic already shipped: `SetLocale` middleware, `lang/{en,vi}.json`, locale-prefixed route group, language switcher partial — all reusable.
- No WYSIWYG / block editor — this iteration only does form-based editing with per-locale inputs.
- No auth system yet; assume one will be added via Breeze/Jetstream later. The admin route group leaves `auth` middleware commented but the controller enforces ownership manually.
- Migrations will be created but not run automatically — developer runs `php artisan migrate` manually after reviewing.

## Out of Scope

- WYSIWYG / drag-and-drop block editor.
- Auth / registration (will be added in a separate epic).
- Role-based permissions, teams, shared ownership.
- Translation memory / automated translation.
- Custom domains or hosting per site — all sites live under the main app's subpath.
- Scheduled publishing, drafts history, revisions.
- SEO beyond a simple `seo.title` / `seo.description` field in JSON.
- CDN upload, image resizing, cropping.

## Dependencies

- **Shipped:** `multi-language` epic (merged, archived) — `SetLocale` middleware, lang JSON files, language switcher partial, `/{locale}` route group.
- **External:** none beyond Laravel core.
- **Follow-up (separate epic):** auth (Breeze/Jetstream) — tracked but not blocking.
- **Setup:** `php artisan storage:link` must be run once by the developer for image uploads to be served.
