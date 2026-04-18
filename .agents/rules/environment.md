---
trigger: model_decision
description: when executing shell commands, managing files, or checking development environment on Windows/PowerShell
---

# Development Environment & Shell Standards

## Use When
- Running shell commands.
- Managing files from terminal.
- Validating local environment assumptions.

## Rules
- Default shell is PowerShell on Windows.
- Use `;` as command separator, not `&&`.
- Use PowerShell-friendly flags (`rm -Recurse`, not `rm -rf`).
- Detect OS from session context before assuming shell syntax.
- Keep line endings as LF.
- Use Windows-compatible paths when running Artisan/vendor binaries.

## Agent Mandate
- If OS is `win32`, use PowerShell syntax strictly.
