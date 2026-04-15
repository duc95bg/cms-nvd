---
issue: 14
stream: routes
started: 2026-04-15T06:15:39Z
status: completed
---
## Progress
- Starting

## Update 2026-04-15T06:21:19Z
- Added public GET /{locale}/site/{slug} → SiteController@show (site.show)
- Added admin.sites.* prefix group with 7 routes (index/create/store/edit/update/preview/images.upload)
- auth middleware left commented pending auth epic
- Verified route:list — all 8 new routes present, no regressions
- Committed as 5f92a38
