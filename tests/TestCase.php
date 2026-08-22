<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->ensureTestingDatabaseConfig();

        parent::setUp();
    }

    /**
     * PHPUnit sqlite settings are ignored when bootstrap/cache/config.php exists.
     * Without this guard, RefreshDatabase wipes the DDEV MariaDB dev database.
     */
    protected function ensureTestingDatabaseConfig(): void
    {
        $cachedConfig = dirname(__DIR__).'/bootstrap/cache/config.php';

        if (is_file($cachedConfig)) {
            @unlink($cachedConfig);
        }

        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['DB_URL'] = '';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_URL'] = '';

        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('DB_URL');

        $connection = strtolower((string) (getenv('DB_CONNECTION') ?: 'sqlite'));

        if (! in_array($connection, ['sqlite', ''], true)) {
            throw new \RuntimeException(
                "REFUSED: tests must use sqlite (:memory:), not {$connection}. "
                .'PHPUnit sets DB_CONNECTION in phpunit.xml; delete bootstrap/cache/config.php if present.'
            );
        }
    }
}
