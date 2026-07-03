<?php

namespace App\Policies;

use App\Enums\ArticleSection;
use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminLike() || $user->isJournalist();
    }

    public function view(User $user, Article $article): bool
    {
        return $this->canManage($user, $article);
    }

    public function create(User $user): bool
    {
        return $user->isAdminLike() || $user->isJournalist();
    }

    public function update(User $user, Article $article): bool
    {
        return $this->canManage($user, $article);
    }

    public function delete(User $user, Article $article): bool
    {
        return $this->canManage($user, $article);
    }

    private function canManage(User $user, Article $article): bool
    {
        if ($article->section === ArticleSection::News) {
            return $user->isAdminLike();
        }

        if ($user->isAdminLike()) {
            return true;
        }

        return $user->isJournalist() && $article->author_id === $user->id;
    }
}
