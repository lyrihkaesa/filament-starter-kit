---
trigger: shell_command, environment_setup
description: when executing shell commands, managing files, or checking development environment on Windows/PowerShell
---

# Development Environment & Shell Standards

This project is primarily developed on **Windows**, and AI agents must use **PowerShell** for all shell operations.

## Shell Syntax
- **Command Separation**: Use `;` (semicolon) to separate commands. Do **NOT** use `&&` as it is not supported in many PowerShell environments.
- **File Operations**: Prefer PowerShell-compatible flags (e.g., `rm -Recurse` instead of `rm -rf`).
- **Environment Detection**: Always verify the OS (win32) before assuming shell capabilities.

## Code Standards
- **Line Endings**: Maintain **LF** line endings even on Windows to ensure compatibility with Laravel standards and CI/CD pipelines.
- **Execution**: When running Artisan or vendor binaries, ensure the pathing is correct for the Windows environment.

## Agent Mandate
If you are an AI agent, always check the current operating system from the session context. If it is `win32`, strictly adhere to PowerShell syntax to avoid execution errors.
