---
name: pm-ai-credits
description: Answer questions about the user's Pulsed Media AI credits (balance, spending per day and per model, the price of each model alias) by reading the AI key's own free status routes. Use when the user asks how many AI credits are left, what they spent, which model is cheaper, or whether a job will run out of credits.
homepage: https://github.com/MagnaCapax/mcxKokko
metadata: { "openclaw": { "emoji": "🪙", "requires": { "bins": ["curl", "sed"] } } }
---

# Pulsed Media AI credits

If Pikkumies is installed on this machine (`~/pikkumies/bin/pikkumies` exists), use its credits tool instead (`pikkumies credits balance`, `usage`, `models`); it reads the same routes.

## The key

The AI key lives in `~/.virsilipasKey`, one `key=vsk-...` line and an `endpoint=` line. A Pulsed Media seedbox writes this file for its user. Elsewhere the user saves the key from the AI key page in their client area into the same file, mode 0600:

```sh
umask 077; read -rs -p 'AI key: ' K; printf 'endpoint=https://virsilipas.mcx.fi/v1\nkey=%s\n' "$K" > ~/.virsilipasKey; unset K
```

Never print, copy, search for or paste the key, and never ask the user to paste it into the chat (the chat is stored). The commands below read it straight from the file into curl's standard input, so it never appears in a command line.

## Commands (they cost no credits)

The commands use the standard endpoint, `https://virsilipas.mcx.fi/v1`. If the key file's `endpoint=` line names a different one, use that instead.

```sh
sed -n 's/^key=/Authorization: Bearer /p' ~/.virsilipasKey | curl -sS --fail-with-body -H @- https://virsilipas.mcx.fi/v1/balance
sed -n 's/^key=/Authorization: Bearer /p' ~/.virsilipasKey | curl -sS --fail-with-body -H @- 'https://virsilipas.mcx.fi/v1/usage?days=7&grants=10'
sed -n 's/^key=/Authorization: Bearer /p' ~/.virsilipasKey | curl -sS --fail-with-body -H @- https://virsilipas.mcx.fi/v1/models
```

## Reading the answers

- `balance`: credits left, already net of running requests. It can be negative after a refund.
- `reserved`, `in_flight`: credits held by requests still running, and how many are running.
- `window`: requests and credits in the **last 60 seconds**, not a day or a month.
- `limits`: the key's own limits; `null` means none.
- `usage`: `daily` rows per UTC day with requests (days without any are left out), `models` totals per model alias, `grants` the credits added (negative for a refund).
- `models`: one entry per alias with `credits_per_million_tokens` for `input`, `cached_input` and `output`, and `context_length`.

## Rules

- These status calls are free. A chat answer, a scheduled agent turn or a tool loop is what spends credits.
- "Which model is cheaper" is computed from the user's own `/v1/models` numbers, never assumed: it depends on how much of the input is cached.
- How much a new job will cost cannot be known before it runs. Say so, suggest running it once, then measure the change in `usage`.
- For a daily low-balance warning that costs nothing, see the `pm-check` job in this repository's README.
- An HTTP 401 means the key file is missing, wrong or the key was replaced: tell the user where the key comes from. Do not retry in a loop.
