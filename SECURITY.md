# Security policy

Please report vulnerabilities privately to support@pulsedmedia.com, not in a public issue. Include the version (or commit), what you found and how to reproduce it.

## Endpoints

Every network call these skills, `bin/pm-check` and `skills/pm-order/bin/pm-order` make goes to one of these, over HTTPS:

| Endpoint | Used by | Key | Changes anything? |
|---|---|---|---|
| https://virsilipas.mcx.fi/v1 (`/balance`, `/usage`, `/models`) | `pm-ai-credits`, `pm-check` | AI key | no |
| https://pulsedmedia.com/remote/pmapi-feed.php | `pm-account` | customer API key | no |
| https://pulsedmedia.com/remote/ticket.php | `pm-ticket` | AI key + account email | opens one support ticket |
| https://pulsedmedia.com/remote/pmapi-order.php | `pm-order` | order key | a quote changes nothing; a confirm places one order |
| https://pulsedmedia.com/data/v1/products.json and https://pulsedmedia.com/data/v1/product-specs.json | `pm-order` | none | no |
| https://pulsedmedia.com/remote/kb.json | `pm-docs` | none | no |
| https://wiki.pulsedmedia.com/api.php | `pm-docs` | none | no |
| https://pulsedmedia.com/data/v1/announcements.xml | `pm-docs` | none | no |

Nothing is fetched from `pulsedmedia.com/clients/`.

## Files

| File | Holds | Mode |
|---|---|---|
| `~/.virsilipasKey` | `endpoint=` and `key=` for the AI key | 0600 (`pm-check` refuses a file other accounts can read) |
| `~/.config/pulsedmedia/pmapi-header` | `X-API-Key: pm_...` | 0600, directory 0700 |
| `~/.config/pulsedmedia/pmapi-order-header` | `X-API-Key: pm_...` of the ORDER key, never the read key | 0600 (`pm-order` refuses a file other accounts can read) |
| `~/.config/pulsedmedia/tickets/draft.json` | a ticket draft until it is sent | 0600 |
| `~/.cache/pulsedmedia/kb.json` | a copy of the public knowledge base | 0600, directory 0700 |

## Threats and controls

| Threat | Control |
|---|---|
| A key shows up in the process list, shell history or a log | The skills feed headers to `curl -H @-` or `-H @file`, so no key is in any command line; keys are saved with `read -rs`, never typed into a command; `pm-check` puts the key in a request header only. |
| Another account on a shared server reads a key file | Files are created under `umask 077`; `pm-check` refuses a key file readable by group or others. |
| Text the agent reads (a web page, a KB article, a message) tells it to open a ticket | The ticket skill sends only after the user's yes in a later message and recommends an exec approval prompt for the send command. This is guidance to the model, not a boundary; the ticket server limits each account to 5 tickets an hour and 20 a day, and a ticket carries no authority over the account. |
| The agent itself reads a key it can reach | Nothing in a skill stops an agent that decides to read its own user's files. If that matters to you, run the agent where it cannot read the key files and inject the key at an egress proxy instead. |
| Text the agent reads tells it to place an order | Only `pm-order confirm` places an order. It refuses unless OpenClaw's own policy report shows that a person is asked before each command (mode `ask` or ask `always`), so the owner sees the product, price and terms flag on the approval card. It sends that price, and the server refuses (409, nothing ordered) when the price is not that now. The skill forbids confirming from group chats, scheduled jobs, sub-agents or on the say-so of any content. Server limits: 5 unpaid orders, 5 orders in 24 hours, one request at a time per account. Approvals are not a user boundary; see the README for the separate-user setup. |
| A tool that only reads holds a key that can spend money | The order key is a separate key that cannot read services, kept in its own file; the read key cannot order. |
| The AI endpoint is changed in the key file to an attacker's server | `pm-check` accepts only `https://` endpoints (plain `http://` only to 127.0.0.1, for the tests). |
