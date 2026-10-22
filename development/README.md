# Development

Start here if you change this repository, by hand or with a coding agent.

## Read first

1. [`AGENTS.md`](../AGENTS.md): the project's governing text, with its rules and the invariants the tests protect.
2. [Engineering principles](engineering-principles.md): the organisation-wide rules with their reasons and the mistakes that taught them, copied from [mcxPikkumies](https://github.com/MagnaCapax/mcxPikkumies/blob/main/development/engineering-principles.md), the canonical copy.
3. [Decision records](../docs/adr/): accepted ADRs are binding. A significant change ships with a new one or amends one in the same commit ([template](../docs/adr/0000-template.md)). [ADR 0001](../docs/adr/0001-layout-languages-and-install.md) maps the layout, the languages and the install paths.
4. [README](../README.md) and [CHANGELOG](../CHANGELOG.md): what the skills do, how they are installed and released.
5. [Security policy](../SECURITY.md): the endpoints and key files, and how to report a vulnerability privately.

## Before you commit

- `php tests/run.php` passes (no network access needed; run it from a git checkout). Read the summary line and the exit code. CI runs the same suite on PHP 8.2 to 8.5 after a syntax lint.
- New behaviour has a test that fails when the behaviour is broken (break it once and see).
- A new endpoint is added to SECURITY.md in the same commit; the suite refuses an undeclared one.
- Review the change with the [review unit in mcxPikkumies](https://github.com/MagnaCapax/mcxPikkumies/tree/main/pikkumies/tools/development/code-review): correctness, an adversarial pass, weakness classes, and conformance to `AGENTS.md`, the ADRs and the principles.
- Nothing that is not meant for the public: this repository is public. Keep your deployment's own words in a local `.forbidden-words` file (never committed); the suite refuses them in tracked files.

## Other MagnaCapax repositories

Each has its own `AGENTS.md`, decision records and docs. When your change touches another project, read its records first.

| Repository | What it is | Docs | Decisions |
|---|---|---|---|
| [mcxKokko](https://github.com/MagnaCapax/mcxKokko) | This repository | [docs](https://github.com/MagnaCapax/mcxKokko/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxKokko/tree/main/docs/adr) |
| [PMSS](https://github.com/MagnaCapax/PMSS) | Pulsed Media Seedbox Software | [docs](https://github.com/MagnaCapax/PMSS/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/PMSS/tree/main/docs/adr) |
| [mcxPikkumies](https://github.com/MagnaCapax/mcxPikkumies) | Lightweight helper agent that runs on the server it manages; holds the canonical engineering principles | [docs](https://github.com/MagnaCapax/mcxPikkumies/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxPikkumies/tree/main/docs/adr) |
| [mcxVirsilipas](https://github.com/MagnaCapax/mcxVirsilipas) | Per-key prepaid AI credit proxy with an OpenAI-compatible API | [docs](https://github.com/MagnaCapax/mcxVirsilipas/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxVirsilipas/tree/main/docs/adr) |
| [mcxForge](https://github.com/MagnaCapax/mcxForge) | Bare-metal qualification, repair and installation suite | [docs](https://github.com/MagnaCapax/mcxForge/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxForge/tree/main/docs/adr) |
| [mcxRescue](https://github.com/MagnaCapax/mcxRescue) | Rescue live system for bare-metal servers | [docs](https://github.com/MagnaCapax/mcxRescue/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxRescue/tree/main/docs/adr) |
| [mcxTemplate](https://github.com/MagnaCapax/mcxTemplate) | Templating engine for distribution installs | [docs](https://github.com/MagnaCapax/mcxTemplate/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxTemplate/tree/main/docs/adr) |
| [nodeAgent](https://github.com/MagnaCapax/nodeAgent) | Server telemetry and hardware health agent | [docs](https://github.com/MagnaCapax/nodeAgent/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/nodeAgent/tree/main/docs/adr) |
| [mcxSerp](https://github.com/MagnaCapax/mcxSerp) | Search results API client for PHP | [docs](https://github.com/MagnaCapax/mcxSerp/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxSerp/tree/main/docs/adr) |
| [mcxSauces](https://github.com/MagnaCapax/mcxSauces) | Scripts and configs for running systems and services | [docs](https://github.com/MagnaCapax/mcxSauces/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxSauces/tree/main/docs/adr) |
| [mcxBMCView](https://github.com/MagnaCapax/mcxBMCView) | BMC console screenshot and keyboard automation | [docs](https://github.com/MagnaCapax/mcxBMCView/tree/main/docs) | [ADRs](https://github.com/MagnaCapax/mcxBMCView/tree/main/docs/adr) |
