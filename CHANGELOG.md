# Changelog

## 0.1.1 (2026-10-22)

- Tests: read and close a child process's pipes before waiting for it, so the missing-key-file check passes on CI.
- CI: the forbidden-word list is set as a repository secret.

## 0.1.0 (2026-10-22)

- Four skills: `pm-ai-credits`, `pm-account`, `pm-docs`, `pm-ticket`.
- `bin/pm-check credits`: a low-balance check for an OpenClaw command job.
- Agent Plugins `plugin.json`, so the repository installs as one bundle.
