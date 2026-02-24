# Commands Adapted to Laravel + Vue 3

All Claude Code commands have been adapted for the Laravel 12 + Vue 3 + Inertia.js project.

## Adapted Commands Summary

### ✅ Core Workflow (5 commands)

1. **validate** - Run all checks (Pint lint, Pest tests, optional npm lint)
2. **plan** - Create implementation plans with codebase analysis (Services, Controllers, Models)
3. **implement** - Execute plan with validation loops (uses composer test:lint)
4. **commit** - Create atomic commits (tech-agnostic, unchanged)
5. **create-pr** - Create pull requests from branch (tech-agnostic, unchanged)

### ✅ Analysis & Review (4 commands)

6. **review-pr** - Comprehensive PR review with specialized agents (checks Laravel/Vue patterns)
7. **rca** - Root cause analysis (tracks PHP/Laravel debugging techniques)
8. **check-ignores** - Audit suppression comments (@phpstan-ignore, eslint-disable, etc.)
9. **create-rules** - Generate CLAUDE.md from codebase (detects Laravel + Vue stack)

### ✅ Product & Documentation (2 commands)

10. **prd** - Interactive PRD generator (tech-agnostic)
11. **create-command** - Meta command creator (tech-agnostic)

### ✅ Already Complete (1 command)

12. **prime** - Prime agent with codebase context (completed in initial setup)

---

## Key Adaptations Made

| Area | Changed From | Changed To |
|------|--------------|-----------|
| **Lint command** | `bun run lint` | `composer test:lint` (Pint) |
| **Build command** | `bun run build` | Removed (Laravel doesn't have build phase) |
| **Test command** | `bun test` | `composer test` (Pest) |
| **Frontend lint** | N/A | `npm run lint` (ESLint, optional) |
| **Patterns** | TypeScript/Zod | Laravel Services, Controllers, Eloquent |
| **Error handling** | TypeScript errors | PHP type hints, Pest assertions |
| **Suppressions** | `@ts-ignore`, `biome-ignore` | `@phpstan-ignore`, `// eslint-disable` |
| **File exploration** | src/core, src/features | app/, resources/js/, database/ |
| **Test patterns** | Vitest, ESM | Pest with `describe()` and `it()` |

---

## Usage

All commands work exactly as before, but now reference Laravel/Vue patterns:

```bash
/validate                    # Run Pint + Pest tests
/plan "add user authentication"  # Plans with Service patterns
/implement path/to/plan.md   # Implements with Pest validation
/review-pr                   # Reviews for CLAUDE.md compliance
/rca "middleware error"      # RCA for Laravel issues
/check-ignores              # Finds PHP/JS suppressions
```

---

## Files Modified

- `.claude/commands/validate.md` ✅
- `.claude/commands/plan.md` ✅
- `.claude/commands/implement.md` ✅
- `.claude/commands/review-pr.md` ✅
- `.claude/commands/rca.md` ✅
- `.claude/commands/check-ignores.md` ✅
- `.claude/commands/create-rules.md` ✅
- `.claude/commands/prime.md` ✅ (completed earlier)
- `.claude/commands/commit.md` (unchanged - tech agnostic)
- `.claude/commands/create-pr.md` (unchanged - tech agnostic)
- `.claude/commands/prd.md` (unchanged - tech agnostic)
- `.claude/commands/create-command.md` (unchanged - tech agnostic)

---

**Status**: All Laravel + Vue adaptations complete ✅
