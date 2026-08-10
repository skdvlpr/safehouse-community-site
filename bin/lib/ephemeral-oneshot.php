<?php

/**
 * Helpers for ephemeral one-shot site scripts.
 *
 * Usage:
 *   require __DIR__ . '/lib/ephemeral-oneshot.php';
 *   safehouse_ephemeral_oneshot_register(__FILE__);
 *   // ... work ...
 *   safehouse_ephemeral_oneshot_exit(0);
 *
 * After successful completion the script deletes itself unless
 * SAFEHOUSE_KEEP_ONESHOT=1 is set (debug only).
 */

declare(strict_types=1);

function safehouse_ephemeral_oneshot_register(string $scriptPath): void
{
    $real = realpath($scriptPath) ?: $scriptPath;

    register_shutdown_function(static function () use ($real): void {
        if (getenv('SAFEHOUSE_KEEP_ONESHOT') === '1') {
            fwrite(STDERR, "KEEP: SAFEHOUSE_KEEP_ONESHOT=1 — not deleting {$real}\n");

            return;
        }

        if (function_exists('error_get_last')) {
            $last = error_get_last();
            if (is_array($last) && in_array($last['type'] ?? 0, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                fwrite(STDERR, "KEEP: fatal error — not deleting {$real}\n");

                return;
            }
        }

        $code = $GLOBALS['__safehouse_oneshot_exit'] ?? 0;
        if ((int) $code !== 0) {
            fwrite(STDERR, "KEEP: non-zero exit — not deleting {$real}\n");

            return;
        }

        if (is_file($real) && @unlink($real)) {
            fwrite(STDERR, "ONESHOT: deleted {$real}\n");
        } else {
            fwrite(STDERR, "WARN: failed to delete oneshot {$real}\n");
        }
    });
}

/**
 * @param list<string> $extraPaths Additional files to delete on success (e.g. content helpers).
 */
function safehouse_ephemeral_oneshot_register_many(array $extraPaths): void
{
    foreach ($extraPaths as $path) {
        $real = realpath($path) ?: $path;
        if ($real === '' || ! is_file($real)) {
            continue;
        }

        register_shutdown_function(static function () use ($real): void {
            if (getenv('SAFEHOUSE_KEEP_ONESHOT') === '1') {
                return;
            }

            $code = $GLOBALS['__safehouse_oneshot_exit'] ?? 0;
            if ((int) $code !== 0) {
                return;
            }

            if (is_file($real) && @unlink($real)) {
                fwrite(STDERR, "ONESHOT: deleted {$real}\n");
            }
        });
    }
}

function safehouse_ephemeral_oneshot_exit(int $code = 0): never
{
    $GLOBALS['__safehouse_oneshot_exit'] = $code;
    exit($code);
}
