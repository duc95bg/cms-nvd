---
issue: 8
stream: schema + models
started: 2026-04-15T05:20:18Z
status: completed
---
## Scope
3 migrations + 3 Eloquent models

## Progress
- Starting

## Update 2026-04-15T05:27:09Z
- Created 3 migrations (templates, sites, media) with json columns + FKs
- Created 3 models (Template, Site, Media) with fillable + casts + relationships
- Site::t(key, locale?, default) implemented per contract (fallback locale → default)
- Verified: migrate runs cleanly on sqlite in-memory
- Committed as df462b6
- Status: completed
