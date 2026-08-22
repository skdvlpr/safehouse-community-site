<?php

/**
 * Hard block for site bin scripts when running on production.
 *
 * Include as the first lines after `<?php` / declare in every `bin/*.php` script:
 *   require __DIR__ . '/lib/refuse-production.php';
 *
 * Mirrors CRM `bin/lib/refuse-production.php`, but for the public website:
 *   - filesystem: /var/www/safehouse-community-site
 *   - APP_URL containing safehouse.community
 *
 * There is intentionally NO bypass flag. Production changes must go through
 * normal deploy, or an ephemeral oneshot with explicit human approval.
 */

declare(strict_types=1);

(static function (): void {
    $root = dirname(__DIR__, 1); // bin
    if (basename($root) === 'bin') {
        $root = dirname($root);
    } elseif (basename($root) === 'lib') {
        $root = dirname($root, 2);
    }

    $reasons = [];

    $realRoot = realpath($root) ?: $root;
    if (str_starts_with($realRoot, '/var/www/safehouse-community-site')) {
        $reasons[] = "filesystem path is production site ({$realRoot})";
    }

    $envFile = $realRoot.'/.env';
    if (is_file($envFile)) {
        $env = (string) file_get_contents($envFile);
        if (preg_match('/(?m)^APP_URL=.*safehouse\.community/i', $env) === 1) {
            $reasons[] = 'APP_URL is production (safehouse.community)';
        }
    }

    if ($reasons === []) {
        return;
    }

    $script = $_SERVER['SCRIPT_FILENAME'] ?? ($_SERVER['argv'][0] ?? 'bin-script');
    fwrite(STDERR, "REFUSED: blocked on production.\n");
    fwrite(STDERR, '  script: '.$script."\n");
    foreach ($reasons as $reason) {
        fwrite(STDERR, '  reason: '.$reason."\n");
    }
    fwrite(STDERR, "  policy: run bin scripts only on local DDEV (or ephemeral oneshot with approval).\n");
    exit(78);
})();
