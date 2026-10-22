# mcxKokko

Pulsed Media services for [OpenClaw](https://github.com/openclaw/openclaw) and other AI agents that read AgentSkills folders. Your agent can check your AI credits, look up your Pulsed Media services, search Pulsed Media's documentation, and open a support ticket, all with your own keys.

> *Kokko, the great eagle of the Kalevala, carries Väinämöinen out of the sea to wherever he needs to go.*

## What is in it

| Skill | What your agent can do | Key | Costs AI credits? |
|---|---|---|---|
| `pm-ai-credits` | Tell you your AI credit balance, what you spent per day and per model, and what each model alias costs | AI key (`~/.virsilipasKey`) | No; the status calls are free |
| `pm-account` | List your Pulsed Media services, their status, next due date and price, and your seedbox disk use and traffic | customer API key | No |
| `pm-docs` | Search the Pulsed Media knowledge base and wiki, and read the latest announcements, before answering | none | No |
| `pm-ticket` | Open a support ticket after showing you exactly what it will send and getting your yes | AI key + your account email | No |

Only the agent's own chat turns use AI credits, the same as without these skills.

`bin/pm-check credits --below <credits>` is a small PHP command for a scheduled OpenClaw job that warns you when the balance gets low. It runs without a model, so it costs nothing.

On a Pulsed Media seedbox with Pikkumies installed, Pikkumies already gives OpenClaw its own skill for the account's tools; these skills hand over to Pikkumies there.

## Install

Tested with OpenClaw 2026.10.5. You need `git` and `curl`; the scheduled check also needs PHP 8.2 or newer with curl.

```sh
git clone --branch v0.1.1 https://github.com/MagnaCapax/mcxKokko ~/src/mcxKokko
openclaw plugins install -l ~/src/mcxKokko
```

OpenClaw shows what the bundle adds (four skills) and asks before enabling it; in a script without a terminal, add `--force` after you have reviewed it. Update later with `git -C ~/src/mcxKokko pull` (or check out a newer tag).

Without the plugin step, point OpenClaw at the skills folder instead, in `~/.openclaw/openclaw.json`:

```json5
{ skills: { load: { extraDirs: ["~/src/mcxKokko/skills"] } } }
```

`openclaw plugins install git:...` does not work for this repository: in OpenClaw 2026.10 that source type is only for native plugin packages. Use a checkout as above.

## Keys

Save keys from a terminal, never by pasting them into the chat (the chat is stored).

- **AI key** (for `pm-ai-credits`, `pm-ticket` and `pm-check`). A Pulsed Media seedbox already has it in `~/.virsilipasKey`. Elsewhere, copy it from the AI key page in your client area, if your account has one:

  ```sh
  umask 077; read -rs -p 'AI key: ' K; printf 'endpoint=https://virsilipas.mcx.fi/v1\nkey=%s\n' "$K" > ~/.virsilipasKey; unset K
  ```

- **Customer API key** (for `pm-account`). Create it in the client area under Services, "API access", and leave ordering off:

  ```sh
  umask 077; mkdir -p ~/.config/pulsedmedia; read -rs -p 'API key: ' K; printf 'X-API-Key: %s\n' "$K" > ~/.config/pulsedmedia/pmapi-header; unset K
  ```

## Low-balance warning (no AI credits used)

```sh
openclaw automations create "0 9 * * *" --name "AI credits check" \
  --command-argv '["php","/home/YOU/src/mcxKokko/bin/pm-check","credits","--below","200000"]' \
  --announce --channel telegram --to "<your chat id>"
```

Every morning it reads your balance. Above 200,000 credits it posts nothing; below, it tells you how many are left and how many days that lasts at your last week's average. If the check itself fails (wrong key, no answer), the run is recorded as failed, so OpenClaw's failure alert can tell you.

## Using the AI key as OpenClaw's model

A fresh OpenClaw on a Pulsed Media seedbox, or one set up with the one-line installer from the AI key page, is already configured. For an OpenClaw you set up yourself, add the provider once:

```sh
umask 077; sed -n 's/^key=/VIRSILIPAS_API_KEY=/p' ~/.virsilipasKey >> ~/.openclaw/.env
openclaw config set models.providers.virsilipas '{"baseUrl":"https://virsilipas.mcx.fi/v1","apiKey":"${VIRSILIPAS_API_KEY}","api":"openai-completions","models":[{"id":"default","name":"default","input":["text"],"compat":{"supportsUsageInStreaming":true}}]}'
```

Then pick it in a chat with `/model virsilipas/default`, or set it as the default model. The `.env` line is a copy of your key: replace it when you get a new key. `input: ["text"]` and `supportsUsageInStreaming` match what the service accepts and reports; other aliases and their prices are listed by `pm-ai-credits`.

## Without installing anything

Your agent can read Pulsed Media's public wiki and knowledge base on its own. The [AI credits page](https://wiki.pulsedmedia.com/wiki/Pulsed_Media_AI_Credits) and the [customer API page](https://wiki.pulsedmedia.com/wiki/Pulsed_Media_Customer_API) describe the services these skills use.

## Security

See [SECURITY.md](SECURITY.md) for every endpoint and key file and how they are protected. The skills are instructions to the agent, not a security boundary.

## Development

See [development/](development/README.md) and [AGENTS.md](AGENTS.md). Tests: `php tests/run.php`.

## Attribution

Author: Aleksi Ursin / MCX (with Väinämöinen) — [Pulsed Media](https://pulsedmedia.com)

"OpenClaw" is the name of an independent open-source project; this project is not affiliated with or endorsed by it.

## License

Apache License 2.0. See [LICENSE](LICENSE) and [NOTICE](NOTICE).
