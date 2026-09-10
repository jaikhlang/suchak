---
paths:
  - 'tests/**'
---

# Tests

## Clear bootstrap config cache if tests return 419
If feature tests fail with HTTP 419 CSRF token mismatch, ensure bootstrap/cache is cleared via `php artisan optimize:clear`. A cached config freezes APP_ENV to local, causing PreventRequestForgery::runningUnitTests() to return false.
