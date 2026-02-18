---
allowed-tools: Read, Glob, Bash
description: Prime agent with codebase context
---

Read these files to understand the codebase before starting work:

1. `CLAUDE.md` - Commands, patterns, and conventions
2. `composer.json` - PHP dependencies and Laravel scripts
3. `package.json` - Node.js dependencies (Vue, Vite, Tailwind)
4. `phpunit.xml` - Test configuration
5. `pint.json` - Laravel PHP linting rules
6. `app/Models/` - Eloquent models
7. `app/Services/` - Business logic services
8. `app/Http/Controllers/` - Thin route controllers
9. `resources/js/` - Vue 3 components and Inertia.js pages
10. `database/migrations/` - Database schema

Then run:
```bash
composer test:lint && composer test
```

Confirm all checks pass before proceeding.
