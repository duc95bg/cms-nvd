---
issue: 5
stream: Blade views & switcher
started: 2026-04-15T03:29:39Z
status: completed
---
## Scope
resources/views/home.blade.php, about.blade.php, partials/lang-switcher.blade.php

## Progress
- Starting implementation

## Update 2026-04-15T03:32:44Z
- Created partials/lang-switcher.blade.php (swaps first URL segment)
- Created home.blade.php and about.blade.php using __() helper
- Updated routes/web.php to return home/about views (replacing placeholder welcome)
- Committed as a3304b5
- Status: completed
