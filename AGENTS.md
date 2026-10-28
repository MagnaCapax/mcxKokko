# mcxKokko: notes for contributors and coding agents

## What this is

Skills that teach an AI agent (OpenClaw first, any agent that reads AgentSkills folders too) to use Pulsed Media's services with the user's own keys: the AI credit balance, the customer API feed, ordering a new service, support tickets, and the public documentation. Plus one small PHP command, `bin/pm-check`, for a scheduled check that costs no AI credits.

Start with [`development/`](development/README.md). [ADR 0001](docs/adr/0001-layout-languages-and-install.md) maps the layout, the languages and the install paths.

## Principles

KISS, DRY, YAGNI. Delete first. Pit of success: safe defaults, risky paths explicit. Never break old users: a skill's name, its key file paths and `pm-check`'s arguments and exit codes stay as they are. Tests that can fail. PHP for logic, shell only as the one-line commands inside the skills, no Python. Before committing, review the change with the [review unit in mcxPikkumies](https://github.com/MagnaCapax/mcxPikkumies/tree/main/pikkumies/tools/development/code-review).

## Rules

- **License:** Apache-2.0. New files carry no per-file headers; LICENSE and NOTICE cover the project.
- **The user's own keys only.** A skill uses the key the user saved in their own files; it never asks for a key in the chat, prints one, or puts one in a command line, a URL or a log.
- **Nothing spends without the user.** Reads are free. A skill that sends something (a ticket) shows the whole request and waits for the user's yes in a later message; it never sends from a scheduled job.
- **Every endpoint is declared** in SECURITY.md; the tests refuse one that is not.
- **Public repository.** No provider, model or vendor names for the AI service, no internal host names, no credentials or internal data in code, docs, tests or commit messages.
- **No dependencies.** `pm-check` needs PHP >= 8.2 with curl. The skills need only `curl`, `sed` and `grep`.

## Governing law and ADRs

- This file and the accepted ADRs in `docs/adr/` govern the project. A significant change of behaviour, interface, security or workflow adds or amends an ADR in the same commit (template: `docs/adr/0000-template.md`).

| ADR | Decision |
|---|---|
| [0001](docs/adr/0001-layout-languages-and-install.md) | Layout, languages and install paths |

## Invariants the tests protect

1. Every skill has a `pm-` prefixed name and valid frontmatter, and is listed in the README.
2. No key header written into a command line, no key-shaped literal, no `/tmp` path in a skill.
3. Every URL a skill or `pm-check` uses is declared in SECURITY.md.
4. `plugin.json` carries the exact Agent Plugins schema URL, and nothing at the root turns the repository into a native plugin.
5. `pm-check` prints `NO_REPLY` only after a successful read at or above the threshold, warns below it, exits 1 on any failed request or answer and 2 on bad arguments or a key file other accounts can read.
