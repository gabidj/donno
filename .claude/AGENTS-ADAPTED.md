# Claude Code Agents - Adapted for Laravel + Vue 3

All Claude Code agents have been optimized for the Laravel 12 + Vue 3 + Inertia.js project.

## Agent Overview

### ✅ **test-writer** (NEW - CREATED)

**Purpose**: Generate comprehensive test cases for PHP (Pest) and Vue components.

**Specializes In**:
- Writing Pest tests with `describe()` and `it()` blocks
- Testing PHP Service classes with happy path + error scenarios
- Vue component tests (props, events, rendering)
- Test coverage strategies for Laravel patterns
- Edge case identification and validation error testing

**Key Features**:
- Understands Pest assertion syntax (`expect()`)
- Knows CLAUDE.md testing conventions
- Focuses on behavioral tests, not implementation details
- Prioritizes critical paths (Services, Controllers, validation)
- Places tests in correct directories (tests/Unit/, tests/Feature/)

**When to Use**: After implementing a new Service, Controller, Form Request, or Vue component. Use to generate comprehensive test coverage that catches real bugs.

---

### ✅ **code-reviewer** (ENHANCED)

**Purpose**: Review code for adherence to Laravel + Vue project guidelines.

**Enhanced For**:
- Laravel Service patterns (dependency injection, single responsibility)
- Controller best practices (thin controllers, service delegation)
- Eloquent relationships and query optimization (N+1 prevention)
- Vue 3 Composition API with TypeScript
- Inertia.js prop passing and data flow
- Form Request validation patterns
- Type hints and PSR-12 compliance

**Key Checks**:
- ✅ Services use constructor DI, not instantiation
- ✅ Controllers dispatch to Services, not business logic
- ✅ Eloquent queries use `.with()` for eager loading
- ✅ Form Requests validate input (not in Controllers)
- ✅ Vue props have type checking and validation
- ✅ No prop mutations (one-way data flow)
- ✅ TypeScript in Vue components
- ✅ Proper logging patterns with structured context

**When to Use**: After writing code, before committing. Proactively review to catch violations of CLAUDE.md patterns and detect real bugs.

---

### ✅ **pr-test-analyzer** (ENHANCED)

**Purpose**: Review PR test coverage for quality and completeness.

**Enhanced For**:
- Pest test coverage analysis (Unit and Feature tests)
- Service method coverage (happy path + error scenarios)
- Controller endpoint coverage (status codes, response structure)
- Form Request validation rule coverage
- Vue component coverage (props, events, rendering)
- Authorization and permission check testing

**Key Focus**:
- Tests for critical Service logic (prevents data loss)
- Negative test cases (validation errors, permission failures)
- Edge cases (boundary values, empty arrays)
- Integration points between layers
- Vue component user interactions

**Ratings Guide**:
- 9-10: Missing Service error handling, untested authorization
- 7-8: Missing Form Request validation tests, Controller endpoint tests
- 5-6: Edge cases, validation combinations, rendering conditions
- 3-4: Nice-to-have coverage, helper methods
- 1-2: Trivial code (simple getters)

**When to Use**: Before PR creation to identify test coverage gaps. Helps ensure critical paths are tested.

---

## Unchanged Agents

The following agents are already excellent and remain unchanged:

- **silent-failure-hunter** - Finds inadequate error handling (PHP try-catch, Vue error boundaries)
- **comment-analyzer** - Audits PHPDoc, TypeScript comments for accuracy
- **type-design-analyzer** - Reviews type/interface design (PHP type hints, Vue prop types)
- **code-simplifier** - Simplifies complex code while maintaining behavior

All these agents work well with Laravel + Vue patterns without modification.

---

## Agent Usage in Workflows

### Development Workflow
```
1. Implement code
   ↓
2. Run /validate (composer test:lint && composer test)
   ↓
3. Use test-writer agent to fill gaps
   ↓
4. Use code-reviewer agent to check patterns
   ↓
5. Fix any issues found
   ↓
6. /commit "feat: add user authentication"
```

### PR Review Workflow
```
1. Create PR
   ↓
2. Use code-reviewer (general quality check)
   ↓
3. Use pr-test-analyzer (coverage gaps)
   ↓
4. Use silent-failure-hunter (error handling)
   ↓
5. Use comment-analyzer (documentation)
   ↓
6. Address findings, update PR
```

---

## Files Modified

### New Files
- `.claude/agents/test-writer.md` ✅ (comprehensive test generation)

### Enhanced Files
- `.claude/agents/code-reviewer.md` ✅ (Laravel + Vue specific)
- `.claude/agents/pr-test-analyzer.md` ✅ (Pest/Laravel/Vue patterns)

### Unchanged
- `.claude/agents/silent-failure-hunter.md` (already universal)
- `.claude/agents/comment-analyzer.md` (already universal)
- `.claude/agents/type-design-analyzer.md` (works with PHP/Vue)
- `.claude/agents/code-simplifier.md` (language agnostic)

---

## Key Agent Capabilities

| Agent | PHP Services | Controllers | Vue Components | Testing | Review |
|-------|--------------|-------------|----------------|---------|--------|
| test-writer | ✅ | ✅ | ✅ | Write | N/A |
| code-reviewer | ✅ | ✅ | ✅ | N/A | Check |
| pr-test-analyzer | ✅ | ✅ | ✅ | Analyze | Check |
| silent-failure-hunter | ✅ | ✅ | ✅ | N/A | Check |
| comment-analyzer | ✅ | ✅ | ✅ | N/A | Check |
| type-design-analyzer | ✅ | ✅ | ✅ | N/A | Check |
| code-simplifier | ✅ | ✅ | ✅ | N/A | Improve |

---

**Status**: All agents optimized for Laravel + Vue 3 ✅
