# RTK - Rust Token Killer (Google Antigravity)

**Usage**: Token-optimized CLI proxy for shell commands.

## Rule (CRITICAL for Windows/PowerShell)

**DO NOT USE HOOKS.** In this environment (Windows/PowerShell), always prefix shell commands with `rtk` manually. This avoids noisy Git Bash popups and ensures token optimization.

Examples:

```bash
rtk git status
rtk php artisan list
rtk composer install
rtk npm run build
rtk ls (if available via git-bash path) or rtk dir
```

## Meta Commands

```bash
rtk gain              # Show token savings
rtk gain --history    # Command history with savings
rtk discover          # Find missed RTK opportunities
rtk proxy <cmd>       # Run raw (no filtering, for debugging)
```

## Why

RTK filters and compresses command output before it reaches the LLM context, saving 60-90% tokens on common operations. Manual prefixing is the most stable way to achieve this on Windows.
