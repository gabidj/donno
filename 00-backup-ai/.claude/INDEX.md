# Claude Code Index - Laravel + Vue 3

Complete reference for all Claude Code configuration files and their purposes.

## 📑 Table of Contents

### Getting Started
- [SETUP-COMPLETE.md](./SETUP-COMPLETE.md) - Summary of what's been created
- [README.md](./README.md) - Complete setup and usage guide
- [QUICK-REFERENCE.md](./QUICK-REFERENCE.md) - Commands and patterns at a glance

### Project Conventions
- [CLAUDE.md](../CLAUDE.md) - **START HERE**: All architecture and patterns
- [COMMANDS-ADAPTED.md](./COMMANDS-ADAPTED.md) - Command modifications for Laravel + Vue
- [AGENTS-ADAPTED.md](./AGENTS-ADAPTED.md) - Agent enhancements for Laravel + Vue

### Slash Commands (11 Total)
See [.claude/commands/](./commands/) for all definitions

**Core Workflow**
- [commands/validate.md](./commands/validate.md) - Run Pint + Pest tests
- [commands/plan.md](./commands/plan.md) - Create implementation plans
- [commands/implement.md](./commands/implement.md) - Execute plans with validation

**Git & PR**
- [commands/commit.md](./commands/commit.md) - Create atomic commits
- [commands/create-pr.md](./commands/create-pr.md) - Create pull requests

**Code Review & Analysis**
- [commands/review-pr.md](./commands/review-pr.md) - Comprehensive PR review
- [commands/rca.md](./commands/rca.md) - Root cause analysis
- [commands/check-ignores.md](./commands/check-ignores.md) - Audit suppressions

**Utilities**
- [commands/create-rules.md](./commands/create-rules.md) - Generate CLAUDE.md
- [commands/prd.md](./commands/prd.md) - Product spec generation
- [commands/create-command.md](./commands/create-command.md) - Command creator
- [commands/prime.md](./commands/prime.md) - Codebase primer

### Specialized Agents (7 Total)
See [.claude/agents/](./agents/) for all definitions

**Testing & Code Review**
- [agents/test-writer.md](./agents/test-writer.md) - **NEW**: Generate Pest + Vue tests
- [agents/code-reviewer.md](./agents/code-reviewer.md) - **ENHANCED**: Laravel/Vue pattern review
- [agents/pr-test-analyzer.md](./agents/pr-test-analyzer.md) - **ENHANCED**: Test coverage analysis

**Code Quality**
- [agents/silent-failure-hunter.md](./agents/silent-failure-hunter.md) - Error handling audits
- [agents/comment-analyzer.md](./agents/comment-analyzer.md) - Documentation review
- [agents/type-design-analyzer.md](./agents/type-design-analyzer.md) - Type design review
- [agents/code-simplifier.md](./agents/code-simplifier.md) - Code improvement

---

## 🎯 Quick Navigation

**I want to...**

| Task | Go To |
|------|-------|
| Understand project architecture | [CLAUDE.md](../CLAUDE.md) |
| Get quick command reference | [QUICK-REFERENCE.md](./QUICK-REFERENCE.md) |
| Learn development workflow | [README.md](./README.md) |
| Check what's been set up | [SETUP-COMPLETE.md](./SETUP-COMPLETE.md) |
| Create a new feature | [commands/plan.md](./commands/plan.md) |
| Review my code | [agents/code-reviewer.md](./agents/code-reviewer.md) |
| Write tests | [agents/test-writer.md](./agents/test-writer.md) |
| Find test gaps | [agents/pr-test-analyzer.md](./agents/pr-test-analyzer.md) |
| Fix a bug | [commands/rca.md](./commands/rca.md) |
| Review a PR | [commands/review-pr.md](./commands/review-pr.md) |
| Check test coverage | [agents/pr-test-analyzer.md](./agents/pr-test-analyzer.md) |

---

## 📚 Documentation Structure

```
.claude/
├── README.md                    ← Setup and usage guide
├── QUICK-REFERENCE.md           ← Commands at a glance
├── SETUP-COMPLETE.md            ← What's been created
├── COMMANDS-ADAPTED.md          ← Command changes
├── AGENTS-ADAPTED.md            ← Agent enhancements
├── INDEX.md                     ← This file
│
├── commands/                    ← Slash command definitions (11 files)
│   ├── validate.md
│   ├── plan.md
│   ├── implement.md
│   ├── commit.md
│   ├── create-pr.md
│   ├── review-pr.md
│   ├── rca.md
│   ├── check-ignores.md
│   ├── create-rules.md
│   ├── prd.md
│   └── create-command.md
│
├── agents/                      ← Specialized agent instructions (7 files)
│   ├── test-writer.md           ← NEW
│   ├── code-reviewer.md         ← ENHANCED
│   ├── pr-test-analyzer.md      ← ENHANCED
│   ├── silent-failure-hunter.md
│   ├── comment-analyzer.md
│   ├── type-design-analyzer.md
│   └── code-simplifier.md
│
└── skills/                      ← Custom skills for IDE features
    ├── pest-testing/
    ├── tailwindcss-development/
    ├── inertia-vue-development/
    ├── wayfinder-development/
    └── ...
```

---

## 🔧 How to Use This Setup

### First Time
1. Read [CLAUDE.md](../CLAUDE.md) - understand architecture
2. Run `/prime` - load codebase context
3. Run `/validate` - confirm setup works

### Creating Features
1. Run `/plan "feature description"` - create plan
2. Execute plan with `/implement path/to/plan.md`
3. Write code following patterns in CLAUDE.md
4. Run `/validate` - check quality
5. Use agents via `/review-pr` for feedback
6. Run `/commit` - create commit
7. Run `/create-pr` - create PR

### Reviewing Code
1. Run `/review-pr` - comprehensive review
2. Address issues found
3. Run `/validate` - confirm fixes
4. Create PR

### Debugging Issues
1. Run `/rca "error message"` - root cause analysis
2. Implement fix
3. Write tests with test-writer agent
4. Run `/validate` - confirm fix

---

## 📖 Essential Reading

**Must Read (before writing code):**
- [CLAUDE.md](../CLAUDE.md) - All conventions, 347 lines

**Good to Know:**
- [README.md](./README.md) - Complete guide to all features
- [QUICK-REFERENCE.md](./QUICK-REFERENCE.md) - Quick lookup for patterns

**Reference (as needed):**
- Individual command files in [commands/](./commands/)
- Individual agent files in [agents/](./agents/)

---

## ✨ What's New

### New Agents
- **test-writer** - Specialized for generating Pest + Vue tests

### Enhanced Agents
- **code-reviewer** - Now understands Laravel Services, Controllers, Vue patterns
- **pr-test-analyzer** - Now understands Pest test structure and Laravel test placement

### Adapted Commands
- All 11 commands adapted for Laravel + Vue
- Validation uses `composer test:lint` + `composer test`
- Planning uses Service/Controller/Model patterns
- RCA uses PHP/Laravel debugging techniques

---

## 🎯 Key Features

**Slash Commands**: 11 commands for common development workflows
**Specialized Agents**: 7 agents for code review, testing, and quality
**Complete Documentation**: 5 guides + CLAUDE.md + command reference
**Code Patterns**: Full examples for Services, Controllers, Models, Vue, Tests
**Architecture Support**: Service-based architecture with thin controllers

---

## 💡 Tips

1. **Keep CLAUDE.md open** - Reference it while coding
2. **Use `/validate` frequently** - Catch issues early
3. **Review code with agents** - Get detailed feedback before PRs
4. **Test-writer agent** - Use for comprehensive test generation
5. **RCA for bugs** - Understand root causes, not just symptoms

---

## 📞 Support

**Questions about architecture?**
→ See [CLAUDE.md](../CLAUDE.md)

**Need command help?**
→ See [QUICK-REFERENCE.md](./QUICK-REFERENCE.md)

**Want detailed guide?**
→ See [README.md](./README.md)

**Looking for specific command?**
→ Check [commands/](./commands/) directory

**Need agent help?**
→ Check [agents/](./agents/) directory

---

## ✅ Checklist

Before getting started:
- [ ] Read [CLAUDE.md](../CLAUDE.md)
- [ ] Run `/prime`
- [ ] Run `/validate`
- [ ] Review [QUICK-REFERENCE.md](./QUICK-REFERENCE.md)

Before creating features:
- [ ] Understand Service pattern
- [ ] Know Controller pattern
- [ ] Understand Form Request validation
- [ ] Know Pest testing patterns

Before committing code:
- [ ] Run `/validate`
- [ ] Run `/review-pr`
- [ ] Tests pass and cover critical paths
- [ ] Code follows CLAUDE.md patterns

---

**Status**: ✅ Complete and ready to use!

Start here: [CLAUDE.md](../CLAUDE.md) → `/prime` → [QUICK-REFERENCE.md](./QUICK-REFERENCE.md)
