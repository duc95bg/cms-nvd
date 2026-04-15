---
issue: 4
title: Đăng ký middleware và routes
analyzed: 2026-04-15T03:26:30Z
estimated_hours: 0.25
parallelization_factor: 1.0
---

# Parallel Work Analysis: Issue #4

## Overview
Hai file nhỏ, phải wire xong middleware trước rồi mới test routes. Giữ 1 stream duy nhất.

## Parallel Streams

### Stream A: Bootstrap middleware + web routes
**Files**: bootstrap/app.php, routes/web.php
**Can Start**: immediately
**Dependencies**: none
