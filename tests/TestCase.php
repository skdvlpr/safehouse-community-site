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
    }
}
