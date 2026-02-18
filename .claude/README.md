# Claude Code Setup for Laravel + Vue 3

Complete Claude Code configuration for Laravel 12 + Vue 3 + Inertia.js development.

## 📋 What's Configured

### ✅ Core Files
- **CLAUDE.md** - Project conventions, patterns, and architecture
- **.claude/commands/prime.md** - Priming agent with codebase context
- **.claude/COMMANDS-ADAPTED.md** - Command documentation

### ✅ Slash Commands (11 adapted for Laravel + Vue)

1. `/validate` - Run Pint + Pest tests + optional npm lint
2. `/plan` - Create implementation plans with codebase analysis
3. `/implement` - Execute plan with validation loops
4. `/commit` - Create atomic commits with conventional prefixes
5. `/create-pr` - Create pull requests from current branch
6. `/review-pr` - Comprehensive PR review with specialized agents
7. `/rca` - Root cause analysis for issues
8. `/check-ignores` - Audit PHP/JS suppression comments
9. `/create-rules` - Generate CLAUDE.md from codebase
10. `/prd` - Interactive product requirements document
11. `/create-command` - Meta command creator for new slash commands

### ✅ Specialized Agents (7 total)

**Test Writing & Review:**
- **test-writer** (NEW) - Generate comprehensive Pest + Vue tests
- **pr-test-analyzer** (Enhanced) - Analyze PR test coverage
- **code-reviewer** (Enhanced) - Review Laravel + Vue patterns

**Code Quality:**
- **silent-failure-hunter** - Find inadequate error handling
- **comment-analyzer** - Audit PHPDoc/TypeScript comments
- **type-design-analyzer** - Review type/prop design
- **code-simplifier** - Simplify complex code

### ✅ Architecture & Patterns

**Backend (Laravel 12)**
- Service classes handle business logic
- Controllers are thin (validation + service dispatch)
- Form Requests validate input
- Eloquent models with relationships
- Pest testing (Unit + Feature tests)
- Pint for code style (PSR-12)

**Frontend (Vue 3)**
- Composition API with `<script setup>`
- TypeScript in components
- Inertia.js for server-driven components
- Tailwind CSS for styling
- ESLint + Prettier for JS/Vue

**Integration (Inertia.js)**
- Controllers return `Inertia::render('Component', $data)`
- Component props match Controller data
- One-way data flow from server to client

---

## 🚀 Quick Start

### First Time Setup

Run the prime command to load codebase context:
```bash
/prime
```

This reads:
- CLAUDE.md (conventions)
- composer.json (PHP dependencies)
- package.json (Node dependencies)
- phpunit.xml (test config)
- Key directories (Models, Services, Controllers, Vue pages)

Then validates:
```bash
composer test:lint && composer test
```

### Development Workflow

```bash
# 1. Create a new feature
/plan "add user authentication"

# 2. Review the plan (adjust if needed)
# 3. Implement the plan
/implement .agents/plans/add-user-authentication.plan.md

# 4. Write tests (use test-writer agent)
# 5. Review code (use code-reviewer agent)
# 6. Run validation
/validate

# 7. Create commit
/commit

# 8. Create PR
/create-pr
```

### Code Review Workflow

```bash
# Before submitting PR, review everything
/review-pr

# This runs:
# - code-reviewer (patterns, bugs)
# - pr-test-analyzer (test coverage)
# - silent-failure-hunter (error handling)
# - comment-analyzer (documentation)

# Address findings, then create PR
/create-pr
```

---

## 📚 Key Files to Know

### Architecture
- **CLAUDE.md** - Read this first. Contains all conventions and patterns.
- **.claude/commands/** - Slash command definitions
- **.claude/agents/** - Specialized review agents

### Code Organization
- **app/Services/** - Business logic (main work happens here)
- **app/Http/Controllers/** - Request handlers (should be thin)
- **app/Http/Requests/** - Input validation
- **app/Models/** - Eloquent models with relationships
- **resources/js/pages/** - Inertia page components
- **resources/js/components/** - Reusable Vue components
- **tests/Unit/** - Unit tests for Services/Models
- **tests/Feature/** - Integration tests for routes
- **database/migrations/** - Schema definitions

### Configuration
- **composer.json** - PHP dependencies and scripts
- **package.json** - Node dependencies and build scripts
- **phpunit.xml** - Pest test configuration
- **pint.json** - PHP code style (Pint)
- **.env** - Environment variables (git-ignored)

---

## 🎯 Common Tasks

### Create a New Feature

```bash
# 1. Plan it
/plan "add product listing page"

# 2. Implement
/implement path/to/plan.md

# 3. Write tests
# (Use test-writer agent or write directly)

# 4. Review
/review-pr

# 5. Commit
/commit

# 6. Create PR
/create-pr
```

### Fix a Bug

```bash
# 1. Analyze the issue
/rca "users can't update profile"

# 2. Implement the fix
# 3. Write tests for the fix
# 4. Validate
/validate

# 5. Commit and PR
/commit
/create-pr
```

### Review Code Before Committing

```bash
# After making changes
/validate          # Run tests/linting
/review-pr         # Get detailed feedback
# Fix any issues found
/commit             # Create commit
```

---

## 🔍 Agent Usage Quick Reference

### When to Use Each Agent

| Agent | When | What It Does |
|-------|------|------------|
| test-writer | After implementing code | Generates Pest + Vue tests |
| code-reviewer | Before committing | Checks Laravel/Vue patterns and bugs |
| pr-test-analyzer | Before PR creation | Identifies test coverage gaps |
| silent-failure-hunter | After error handling | Audits try-catch blocks |
| comment-analyzer | Before PR submission | Checks PHPDoc accuracy |
| type-design-analyzer | For new Services/types | Reviews design quality |
| code-simplifier | After review feedback | Improves code clarity |

### Running Agents

Agents run automatically via `/review-pr` command, or manually via:
```bash
/review-pr [aspects]
```

Examples:
```bash
/review-pr                    # All reviews
/review-pr code errors        # Code quality + error handling
/review-pr tests              # Just test coverage
```

---

## ✅ Validation Commands

Run validation frequently:

```bash
# All checks (Pint lint + Pest tests)
/validate

# Or manually:
composer test:lint    # Pint style check
composer test         # Pest unit + feature tests
npm run lint         # ESLint for Vue/JS (optional)
```

---

## 📖 Standards & Patterns

### PHP (Laravel)
- **Services**: Encapsulate business logic, injected via DI
- **Controllers**: Thin, validate + delegate to Services
- **Models**: Eloquent, use relationships and scopes
- **Validation**: Form Requests, not in controllers
- **Errors**: Custom exceptions, proper error handling
- **Logging**: `Log::info('action', ['key' => $value])`

### Vue 3
- **Script**: `<script setup lang="ts">` with TypeScript
- **Props**: Define with type checking
- **Events**: Emit events, don't mutate props
- **Styles**: Tailwind CSS classes
- **State**: `ref()` for state, `computed()` for calculations

### Tests
- **Unit Tests** (tests/Unit/): Service and Model tests
- **Feature Tests** (tests/Feature/): Route and Controller tests
- **Vue Tests**: Component behavior and interactions
- **Framework**: Pest with `describe()` and `it()`

---

## 🔧 Troubleshooting

### "CLAUDE.md not found"
Make sure you're in the project root directory.

### "Tests fail after changes"
1. Run `/validate` to see the errors
2. Read the test failure message carefully
3. Check if you need to update tests
4. Use test-writer agent if unsure

### "Code review finds issues"
1. Run `/review-pr` to see all issues
2. Review CLAUDE.md for the violated pattern
3. Fix the issue
4. Run `/validate` to confirm

### "Can't create PR"
1. Ensure you're on a feature branch (not main/master)
2. Ensure all changes are committed
3. Run `/create-pr` to create the PR

---

## 📞 Getting Help

**In-app help:**
```bash
/help
```

**Report issues:**
https://github.com/anthropics/claude-code/issues

**Learn more about:**
- **CLAUDE.md**: All conventions, patterns, and best practices
- **Commands**: See `.claude/commands/` for all slash commands
- **Agents**: See `.claude/agents/` for specialized agents

---

## 🎓 Learning Path

1. **Read CLAUDE.md** - Understand the architecture and patterns
2. **Run `/prime`** - Load codebase context
3. **Try `/plan`** - Create a small implementation plan
4. **Study Services** - Look at existing Services in app/Services/
5. **Study Controllers** - See how Controllers use Services
6. **Study Tests** - Review tests/Unit/ and tests/Feature/
7. **Create a feature** - Build a small feature following the patterns
8. **Review with agents** - Use /review-pr to get feedback

---

**Status**: ✅ All systems configured and ready for Laravel + Vue 3 development!
