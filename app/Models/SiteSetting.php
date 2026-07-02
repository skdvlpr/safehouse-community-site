<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SiteSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public function decryptedValue(): ?string
    {
        if ($this->value === null || $this->value === '') {
            return null;
        }

        if ($this->is_encrypted) {
            return Crypt::decryptString($this->value);
        }

        return $this->value;
    }

    public function storePlaintext(?string $plaintext, bool $encrypt): void
    {
        if ($plaintext === null || $plaintext === '') {
            $this->value = null;
            $this->is_encrypted = false;

            return;
        }

        $this->is_encrypted = $encrypt;
        $this->value = $encrypt ? Crypt::encryptString($plaintext) : $plaintext;
    }
}
