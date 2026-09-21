# Quickstart (local only)

1. Do **not** `git push`.
2. `ddev exec php artisan site:sync-legal-pages --force` so privacy/cookie show Anzio.
3. If Contatti still shows a pasted FAQ URL, replace the Italian body with the email paragraph only (FAQ is the template button).
4. `bash bin/dev-rebuild-frontend.sh`
5. Open `https://safehouse-community-site.ddev.site/it` — banner wide, opaque, Nunito Sans.
6. Light + dark: cookie labels readable.
7. `/it/diventa-socio` — Compila domanda, fill official fields, send (SMTP must be configured in CMS).
8. `/it/privacy-policy`, `/it/cookie-policy`, `/it/contact` — Anzio seat; Contatti FAQ is a button.
9. Owner UAT in Russian after implement. Production waits for an explicit deploy message.
