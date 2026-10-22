# ADR 0001: Layout, languages and install paths

## Metadata
- Author: Aleksi Ursin / MCX (with Väinämöinen)
- Date: 2026-10-22
- Status: Accepted

## Context
Customers run AI agents on Pulsed Media seedboxes and on their own machines. The agents knew nothing about Pulsed Media's services. Where Pikkumies (Pulsed Media's own helper agent) is installed, its own OpenClaw skill already covers that machine; everywhere else there was nothing. OpenClaw 2026.10 loads plain AgentSkills folders (`SKILL.md` with frontmatter), native TypeScript plugins, MCP servers, and content bundles described by an Agent Plugins `plugin.json`.

## Options Considered
- **Native TypeScript plugin.** Can add a provider entry and show credits in OpenClaw's own status. The plugin API is marked experimental and must be pinned and re-tested on every OpenClaw release; it needs a Node build. Rejected for now: the skills deliver the same answers without code.
- **An MCP server.** Typed tools, usable by other MCP clients. It needs a runtime the user may not have (PHP is not on every OpenClaw host) or a hosted server. Rejected for now: a skill with `curl` works everywhere OpenClaw does.
- **One umbrella skill.** One description to load. Rejected: the four subjects use two different keys and different consent rules; four short descriptions cost about a kilobyte of prompt.
- **Publishing to ClawHub.** ClawHub republishes skills under MIT-0, replacing this project's licence. Rejected; installs come from a checkout.

## Decision
- `skills/<name>/SKILL.md`, one folder per subject, names prefixed `pm-` so a user's own skill or the Pikkumies skill of the same subject is never shadowed. Each skill opens with "if Pikkumies is installed, use its tool".
- Shell in the skills is limited to single `curl`/`sed`/`grep` commands with no logic. Anything with logic is PHP: `bin/pm-check`, dependency-free, PHP >= 8.2 with curl.
- `plugin.json` at the root follows the Agent Plugins 1.0.0 schema, so `openclaw plugins install -l <checkout>` installs every skill at once. Nothing at the root (`openclaw.plugin.json`, `package.json`, `index.*`) may turn the checkout into a native plugin.
- Documented installs: a checkout plus `openclaw plugins install -l`, or `skills.load.extraDirs` pointing at the checkout's `skills/`. `openclaw plugins install git:...` is not documented: in OpenClaw 2026.10 the git source path accepts only native plugin packages.
- Releases are tags (`v0.1.0`, ...). The CHANGELOG lists what each changed.

## Consequences
- Works on any machine with OpenClaw and `curl`; the scheduled check also needs PHP.
- A bundle install shows OpenClaw's capability review and, from a local path, needs `--force` when run without a terminal.
- The skills are guidance to the model, not a security boundary (SECURITY.md).

## References
- README.md, SECURITY.md, AGENTS.md
