# Contract: one-shot production legal sync

**Command**: `php artisan site:sync-legal-pages --force`  
Cite: [Laravel Artisan](https://laravel.com/docs/13.x/artisan); implementation `app/Console/Commands/SyncLegalPagesCommand.php`.

**Local preview**: `ddev exec php artisan site:sync-legal-pages --force`

**Production (this feature only)**:

1. `deploy/sync-legal-pages-once.sh` `cd`s to `DEPLOY_PATH` and runs the command with `--force`.
2. After success it writes `storage/app/legal-pages-synced-0025`. Later deploys still rsync the script (it stays in git) but skip the artisan command while that marker exists — so staff CMS edits are not overwritten.
3. `deploy/post-deploy.sh` invokes the script **if the file exists**.

Self-deleting the git-tracked script is not enough: the next authorised rsync would restore it and clobber CMS again.

**Copy change in `LegalPagesContent`**: remove the instruction to reopen preferences from the footer; keep the cookie-page button.

**Must not**: run the overwrite on every future deploy; author a DPA; publish `/ru`.
