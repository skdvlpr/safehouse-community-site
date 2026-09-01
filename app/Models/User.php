<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['first_name', 'last_name', 'job_title', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Determine if the user can access the Filament CMS panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'editor', 'journalist']);
    }

    public function isJournalist(): bool
    {
        return $this->hasRole('journalist');
    }

    public function isAdminLike(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'editor']);
    }

    public function canManageUsers(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin']);
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function authoredArticles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function publicAuthorLabel(): string
    {
        $name = trim("{$this->first_name} {$this->last_name}");
        $title = trim((string) ($this->job_title ?? ''));

        if ($title !== '') {
            return "{$name} [{$title}]";
        }

        return $name;
    }

    /**
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
