---
description: Create global rules (CLAUDE.md) from codebase analysis
---

# Create Global Rules

Generate a CLAUDE.md file by analyzing the codebase and extracting patterns.

---

## Objective

Create project-specific global rules that give Claude context about:
- What this project is
- Technologies used
- How the code is organized
- Patterns and conventions to follow
- How to build, test, and validate

---

## Phase 1: DISCOVER

### Identify Project Type

First, determine what kind of project this is:

| Type | Indicators |
|------|------------|
| Web App (Full-stack) | Laravel app/ + resources/js/, Inertia.js integration |
| Web App (Frontend-only) | Vue.js/React, no backend code |
| Laravel API | app/Http/Controllers, routes/api.php, no frontend |
| Laravel SPA | Full Laravel + Vue/Inertia setup |
| PHP Library | composer.json with main entry, PSR-4 autoload |
| CLI Tool | artisan commands, console/ directory |
| Monorepo | Multiple packages in workspace |

### Analyze Configuration

Look at root configuration files:

```
package.json       → dependencies, scripts, type
tsconfig.json      → TypeScript settings
vite.config.*      → Build tool
*.config.js/ts     → Various tool configs
```

### Map Directory Structure

Explore the codebase to understand organization:
- Where does source code live?
- Where are tests?
- Any shared code?
- Configuration locations?

---

## Phase 2: ANALYZE

### Extract Tech Stack

From composer.json, package.json, and config files, identify:
- **Backend**: PHP version, Laravel version, core packages (Fortify, Inertia)
- **Frontend**: Vue.js version, Vite, Tailwind
- **Database**: SQLite/PostgreSQL, migrations, Eloquent patterns
- **Testing**: Pest (PHP), Vitest (JS)
- **Build**: Vite, Laravel asset compilation
- **Linting**: Pint (PHP), ESLint (JavaScript)

### Identify Patterns

Study existing code for:
- **Naming**: Service classes, Controller methods, Model relationships (PHP conventions)
- **Structure**: Services in app/Services/, Controllers in app/Http/Controllers/, Vue in resources/js/
- **Errors**: Exception handling, Laravel validation, error responses
- **Types**: Type hints in PHP methods, TypeScript in Vue files
- **Tests**: Pest test structure in tests/Unit/ and tests/Feature/

### Find Key Files

Identify files that are important to understand:
- Entry points
- Configuration
- Core business logic
- Shared utilities
- Type definitions

---

## Phase 3: GENERATE

### Create CLAUDE.md

Use the template at `.agents/CLAUDE-template.md` as a starting point.

**Output path**: `CLAUDE.md` (project root)

**Adapt to the project:**
- Remove sections that don't apply
- Add sections specific to this project type
- Keep it concise - focus on what's useful

**Key sections to include:**

1. **Project Overview** - What is this and what does it do?
2. **Tech Stack** - What technologies are used?
3. **Commands** - How to dev, build, test, lint?
4. **Structure** - How is the code organized?
5. **Patterns** - What conventions should be followed?
6. **Key Files** - What files are important to know?

**Optional sections (add if relevant):**
- Architecture (for complex apps)
- API endpoints (for backends)
- Component patterns (for frontends)
- Database patterns (if using a DB)
- On-demand context references

---

## Phase 4: OUTPUT

```markdown
## Global Rules Created

**File**: `CLAUDE.md`

### Project Type

{Detected project type}

### Tech Stack Summary

{Key technologies detected}

### Structure

{Brief structure overview}

### Next Steps

1. Review the generated `CLAUDE.md`
2. Add any project-specific notes
3. Remove any sections that don't apply
4. Optionally create reference docs in `.agents/reference/`
```

---

## Tips

- Keep CLAUDE.md focused and scannable
- Don't duplicate information that's in other docs (link instead)
- Focus on patterns and conventions, not exhaustive documentation
- Update it as the project evolves
