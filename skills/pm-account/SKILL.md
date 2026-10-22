---
name: pm-account
description: Answer questions about the user's own Pulsed Media services (which services, their status, next due date, recurring price, billing cycle, seedbox disk use and traffic so far) from the Pulsed Media customer API feed. Use when the user asks about their Pulsed Media services, renewal dates, or how full their seedbox is.
homepage: https://github.com/MagnaCapax/mcxKokko
metadata: { "openclaw": { "emoji": "🧾", "requires": { "bins": ["curl"] } } }
---

# Pulsed Media services (customer API feed)

## The key

The account owner creates the key in the client area, under Services, "API access". Leave "Allow this key to place orders" unticked: this skill only reads. The key is shown once. The user saves it in a header file, mode 0600, from a terminal:

```sh
umask 077; mkdir -p ~/.config/pulsedmedia; read -rs -p 'API key: ' K; printf 'X-API-Key: %s\n' "$K" > ~/.config/pulsedmedia/pmapi-header; unset K
```

Never print, copy or search for the key, and never ask the user to paste it into the chat.

## Command (no credits, read-only)

```sh
curl -sS --fail-with-body -H @"$HOME/.config/pulsedmedia/pmapi-header" https://pulsedmedia.com/remote/pmapi-feed.php
```

The feed takes no query string (one gets HTTP 400). HTTP 401 means the key is wrong, was replaced or revoked, or the account is not active; 503 means the API is switched off.

## Reading the answer

- Top level: `generated_at`, `currency`, `services`.
- Each service: `product`, `status`, `registered`, `next_due`, `billing_cycle`, `recurring_amount` (in `currency`), `domain`.
- Seedbox services add `seedbox`: `username`, `login_url`, and `usage`, which can be `null`.
- `usage.disk_used_bytes` and `usage.disk_quota_bytes` are the last nightly reading, not live. `usage.sent_bytes` and `usage.received_bytes` are totals since `usage.tracked_since`, not this month and not against a limit. `received_bytes` can be `null`.

## Rules

- Say only what the feed says. Never state a plan's limits, a traffic allowance or a price from memory; the feed carries none of those besides `recurring_amount`.
- `next_due` is a date; say "due on", never "will be charged" or "unpaid".
- On a Pulsed Media seedbox, `~/bin/userShowLimits --json` gives live numbers for that account.
- The links in the feed are for the user to open; do not fetch them.
