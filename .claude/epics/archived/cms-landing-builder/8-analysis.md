---
issue: 8
title: Data layer — migrations and Eloquent models
analyzed: 2026-04-15T05:20:18Z
estimated_hours: 1.5
parallelization_factor: 1.0
---

# Parallel Work Analysis: Issue #8

## Overview
3 migrations + 3 models. Chuỗi file khác nhau nhưng coherent — giữ 1 stream duy nhất để shared interface (Site::t, fillable fields, casts) đồng bộ.

## Parallel Streams

### Stream A: schema + models + helper
**Files**: database/migrations/2026_04_15_*.php (3), app/Models/{Template,Site,Media}.php
**Can Start**: immediately
