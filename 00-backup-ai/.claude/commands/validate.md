---
allowed-tools: Bash(composer test:*), Bash(npm run lint), Bash(composer test)
description: Run all checks (lint, test)
---

Run comprehensive validation. Execute in sequence:

1. **Lint** (PHP style with Pint):
   ```bash
   composer test:lint
   ```

2. **Tests** (Pest PHP tests):
   ```bash
   composer test
   ```

3. **Frontend Lint** (optional, JavaScript/Vue):
   ```bash
   npm run lint
   ```

## Report

Summarize results:
- PHP Lint (Pint): X errors, Y warnings (or PASS)
- Tests (Pest): X passed, Y failed (or all passed)
- Frontend Lint: X errors (or PASS)

**Overall: PASS or FAIL**
