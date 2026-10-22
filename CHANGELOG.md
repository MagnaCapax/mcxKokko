# Changelog

## 0.2.0 (2026-10-22)

- New skill `pm-order` with its helper `skills/pm-order/bin/pm-order`: quote a new service, then order it at that price with the account's ORDER key. The confirm step refuses unless OpenClaw asks a person before each command, sends the quoted price so the server refuses a changed price, and never retries.
- `pm-account` and the README describe the read key and the separate order key.

## 0.1.1 (2026-10-22)

- Tests: read and close a child process's pipes before waiting for it, so the missing-key-file check passes on CI.
- CI: the forbidden-word list is set as a repository secret.

## 0.1.0 (2026-10-22)

- Four skills: `pm-ai-credits`, `pm-account`, `pm-docs`, `pm-ticket`.
- `bin/pm-check credits`: a low-balance check for an OpenClaw command job.
- Agent Plugins `plugin.json`, so the repository installs as one bundle.
