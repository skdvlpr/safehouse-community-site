<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\LegacyUserNameParser;
use Tests\TestCase;

class LegacyUserNameParserTest extends TestCase
{
    public function test_splits_name_and_bracketed_job_title(): void
    {
        $parsed = LegacyUserNameParser::split('Matteo Grossi [Presidente Safe House]');

        $this->assertSame('Matteo', $parsed['first_name']);
        $this->assertSame('Grossi', $parsed['last_name']);
        $this->assertSame('Presidente Safe House', $parsed['job_title']);
    }

    public function test_splits_two_part_name_without_title(): void
    {
        $parsed = LegacyUserNameParser::split('Maria Editor');

        $this->assertSame('Maria', $parsed['first_name']);
        $this->assertSame('Editor', $parsed['last_name']);
        $this->assertNull($parsed['job_title']);
    }

    public function test_single_word_name_uses_same_last_name(): void
    {
        $parsed = LegacyUserNameParser::split('Admin');

        $this->assertSame('Admin', $parsed['first_name']);
        $this->assertSame('Admin', $parsed['last_name']);
        $this->assertNull($parsed['job_title']);
    }

    public function test_public_author_label_includes_title_only_when_present(): void
    {
        $withTitle = User::factory()->make([
            'first_name' => 'Matteo',
            'last_name' => 'Grossi',
            'job_title' => 'Presidente Safe House',
        ]);

        $withoutTitle = User::factory()->make([
            'first_name' => 'Maria',
            'last_name' => 'Editor',
            'job_title' => null,
        ]);

        $this->assertSame('Matteo Grossi [Presidente Safe House]', $withTitle->publicAuthorLabel());
        $this->assertSame('Maria Editor', $withoutTitle->publicAuthorLabel());
    }
}
