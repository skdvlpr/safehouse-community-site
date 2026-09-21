# Contract: one-shot production legal sync

**Command**: `php artisan site:sync-legal-pages --force`  
Cite: [Laravel Artisan](https://laravel.com/docs/13.x/artisan); implementation `app/Console/Commands/SyncLegalPagesCommand.php`.

**Local preview**: `ddev exec php artisan site:sync-legal-pages --force`

**Production (this feature only)**:

1. Add `deploy/sync-legal-pages-once.sh` that `cd`s to `DEPLOY_PATH`, runs the command with `--force`, then self-deletes on success (same pattern as other `deploy/*-once.sh`).
2. `deploy/post-deploy.sh` invokes that script **if the file exists**. After the first successful deploy the file is gone, so later pushes do not overwrite CMS legal HTML.

**Copy change in `LegalPagesContent`**: remove the instruction to reopen preferences from the footer; keep the cookie-page button.

**Must not**: run on every future deploy; author a DPA; publish `/ru`.
