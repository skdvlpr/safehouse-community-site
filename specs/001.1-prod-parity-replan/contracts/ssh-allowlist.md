# Contract: SSH allowlist for production inspect

Use only when the owner has asked for production inspect in this feature. Identity: `~/.ssh/safehouse-deploy` as `deploy@77.81.234.138`. `ForwardAgent=no`. `BatchMode=yes`.

## Allowed

- `whoami`, `id`, `hostname`, `php -v`, `php artisan --version`, `php artisan env`
- Read `/etc/caddy/Caddyfile` (no secrets expected)
- `curl -sI` to local public Host
- `php artisan tinker` **only** to list `pages` key/template/published/slug/title and campaign slug/title/`is_active`
- Log **counts** / exception **class names**, with secret-shape redaction
- Public HTTPS from the agent machine

## Forbidden

- `cat` / `less` of `.env`
- Dump `site_settings` or Integrations
- `mysql` with `SELECT` on settings/users passwords
- Any write: files, `artisan migrate`, `config:cache`, `optimize`, `systemctl`, Caddy reload, chmod/chown
- `sudo`
- Agent forwarding
- Root login

## Safety statement (for the owner)

Using this key from an agent session is the same privilege as a GitHub Actions deploy user on the VPS. Residual risk is **high**. Keep sessions read-only and short.
