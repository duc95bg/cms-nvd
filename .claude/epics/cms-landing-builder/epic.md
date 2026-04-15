---
name: cms-landing-builder
status: completed
created: 2026-04-15T04:24:00Z
updated: 2026-04-15T06:52:34Z
progress: 100%
prd: .claude/prds/cms-landing-builder.md
github: https://github.com/duc95bg/cms-nvd/issues/7
---

# Epic: cms-landing-builder

## Overview
Ship a multi-language landing page builder on top of the existing `multi-language` epic. Users pick a Blade template, get seeded with a localized JSON blob, edit every language from one form, and publish at `/{locale}/site/{slug}`. Rendering and locale plumbing reuse what was already shipped — this epic adds only the data layer, admin CRUD, one production template, and an authoring form that auto-generates per-locale inputs from the JSON shape.

## Architecture Decisions

1. **Single JSON column for content, not per-locale columns.** `sites.content` is a nested array where every translatable leaf is an object `{ "en": "...", "vi": "..." }`. Adding locales = adding keys + extending `SUPPORTED`. Zero schema changes.

2. **Model-level localization helper.** `Site::t(string $key, ?string $locale = null, string $default = '')` does the lookup + fallback chain. Templates only call `$site->t('hero.title')` — never touch locale or `data_get` directly.

3. **Form field discovery by JSON traversal, not schema declarations.** The edit view recursively walks `content`. Leaves whose keys match `SUPPORTED` render as per-locale inputs; indexed lists render as a raw JSON textarea (pragmatic escape hatch for repeaters). No per-template form code.

4. **Dot-notation form names (`content[hero.title][vi]`) + `data_set` rehydration on save.** HTML forms can't easily represent arbitrary nested arrays; flat dot-notation keys survive the round trip and the controller reconstructs nesting with `data_set($nested, $dotKey, $value)`.

5. **Two-layer locale validation.** Route regex (`en|vi`) blocks unsupported locales at routing (404). Middleware handles persistence + `app()->setLocale()`. Controller never checks locale.

6. **Reuse `SetLocale` from the `multi-language` epic.** Do not introduce `SetLocaleFromRoute`. The shipped middleware already reads `segment(1)` and supports the same URL shape — adding a second middleware would be duplicate code.

7. **Manual ownership check instead of Policy.** Until auth lands, `abort_unless($site->user_id === request()->user()?->id, 403)` in the controller. Upgrade to a Policy once Breeze/Jetstream is in.

8. **Tailwind via CDN in Blade.** No Vite wiring in this epic — keeps template files self-contained and avoids bundling drift.

## Technical Approach

### Frontend Components
- **Public template**: `resources/views/templates/layouts/base.blade.php` (shared shell with Tailwind CDN + existing `partials.lang-switcher`) + `resources/views/templates/product.blade.php` (hero / features repeater / pricing CTA, all via `$site->t()`).
- **Admin screens**: `resources/views/admin/sites/create.blade.php` (template picker + slug form), `resources/views/admin/sites/edit.blade.php` (JSON-driven per-locale form).
- No JS framework. A tiny inline script handles image uploads via `fetch()` and inserts the returned URL into the form.

### Backend Services
- **Models**: `Template` (fillable `name, type, view, default_content`, cast `default_content => array`), `Site` (fillable `user_id, template_id, slug, content, published`, casts, relationships, `t()` method), `Media` (belongsTo site, `url` accessor).
- **Controller**: `SiteController` with `show` (public), `index / create / store / edit / update / preview / uploadImage` (admin). `update` rehydrates nested arrays from dot-notation keys.
- **Routing**: public locale-prefixed route added to the existing `{locale}` group; admin routes under a new `admin.sites.*` prefix group. Auth middleware left commented until a future epic.
- **Seeder**: `TemplateSeeder` inserts the `product` template with a realistic `default_content` covering `hero`, `features.items` (3 items), `pricing`, `brand`, `seo`.

### Infrastructure
- Three migrations: `templates`, `sites`, `media`. All use `json` column type; MySQL 5.7+ or SQLite both supported.
- Image uploads land in `storage/app/public/sites/{site_id}/` on the `public` disk. `php artisan storage:link` is a documented setup step, not automated.
- No cache/queue/external services introduced.

## Implementation Strategy

**Phase 1 — Data layer (can parallelize internally):** migrations + models + seeder. No app logic yet. This unblocks everything else.

**Phase 2 — Rendering + public route:** `SiteController@show` + product template Blade + base layout. Verified with a seeded site and a manual GET to `/vi/site/<slug>`.

**Phase 3 — Admin CRUD:** create/store/edit/update routes, both admin Blade views, ownership check. Form field discovery in `edit.blade.php` is the trickiest part; gets its own test coverage for the JSON flattening.

**Phase 4 — Image upload + preview + tests:** media endpoint, `preview` route, feature tests covering public render, admin happy path, ownership 403, and locale scaling (adding `ja` to `SUPPORTED` in a test-only config and asserting no code change needed).

Risks: the JSON-to-form traversal has edge cases (partially translated leaves, deeply nested groups, empty arrays). Mitigation: explicit unit test for the flatten helper with fixture content.

## Task Breakdown Preview

≤10 tasks, ordered by dependency:

1. **Data layer**: 3 migrations + 3 Eloquent models + `Site::t()` helper. Parallelizable internally.
2. **Template seeder + default_content fixture**: one `product` seed with realistic JSON, runnable via `db:seed`.
3. **Public render**: `SiteController@show`, public route added to `{locale}` group, `templates/layouts/base.blade.php` + `templates/product.blade.php`.
4. **Admin create flow**: routes + controller `index/create/store`, `admin/sites/create.blade.php`.
5. **Admin edit flow**: controller `edit/update` with dot-notation rehydration, `admin/sites/edit.blade.php` including the JSON-driven form generator.
6. **Preview route + ownership guard**: `preview` action + shared `authorizeSite()` check, wired into admin routes.
7. **Image upload endpoint**: `uploadImage` controller action + route + storage link documentation in task body.
8. **Feature tests**: public render (200 + locale content), admin happy path (create → edit → update → preview), ownership 403, flatten helper unit test.

Parallelization opportunities:
- Tasks 1 and 2 can run in parallel (seeder references model but not vice-versa once interface is frozen).
- Task 3 depends on 1. Tasks 4, 5, 6, 7 all depend on 3 but can run in parallel among themselves (different files).
- Task 8 depends on all previous.

## Dependencies

- **Shipped (available now):** `multi-language` epic — `SetLocale` middleware, `lang/{en,vi}.json`, `/{locale}` route group, `partials.lang-switcher`.
- **External:** none beyond Laravel core.
- **Setup action (one-off, documented in tasks):** `php artisan storage:link` + a running DB connection in `.env`.
- **Deferred / separate epic:** auth (Breeze). Admin routes leave `auth` middleware commented; controller enforces ownership manually until then.

## Success Criteria (Technical)

- `php artisan migrate --seed` creates the tables and inserts the `product` template cleanly.
- A seeded site renders at `/vi/site/<slug>` and `/en/site/<slug>` with correct translations.
- Admin create → edit → save round-trips a nested multilingual JSON blob without data loss (verified by feature test + by a reload of the edit page showing the same values).
- Non-owner hitting `admin/sites/{site}/edit` gets 403.
- `php artisan test --filter=SiteCms` is green.
- Adding a third locale (`ja`) to `SetLocale::SUPPORTED` + config + JSON content requires zero other code changes — asserted by a dedicated test that temporarily patches `SUPPORTED` and verifies rendering.

## Estimated Effort

- **Wall-clock with parallel execution**: ~1 day (~8h).
- **Serial effort**: ~2 days (~16h).
- **Critical path**: Task 1 (data layer) → Task 3 (public render) → Task 5 (admin edit with form generator) → Task 8 (tests). ~5h on the critical path.
- **Resource assumption**: single developer with parallel agents where the tasks are file-disjoint.

## Tasks Created
- [ ] 001.md - Data layer — migrations and Eloquent models (parallel: true)
- [ ] 002.md - Template seeder with product default_content (parallel: true)
- [ ] 003.md - Base layout and product Blade template (parallel: true)
- [ ] 004.md - SiteController with all actions (public + admin) (parallel: true)
- [ ] 005.md - Admin create site Blade view (parallel: true)
- [ ] 006.md - Admin edit site Blade view with JSON-driven form (parallel: true)
- [ ] 007.md - Wire public and admin routes (parallel: false)
- [ ] 008.md - Feature and unit tests for the CMS flow (parallel: false)

Total tasks: 8
Parallel tasks: 6 (002, 003, 004, 005, 006 run in parallel after 001; 007 serializes on 004; 008 waits on everything)
Sequential tasks: 2 (007, 008)
Estimated total effort: ~11.5 hours serial, ~5 hours on critical path with parallel execution
