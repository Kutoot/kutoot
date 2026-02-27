<?php

namespace App\Policies;

use App\Models\NewsArticle;
use App\Models\User;

class NewsArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-any-news-article');
    }

    public function view(User $user, NewsArticle $newsArticle): bool
    {
        return $user->can('view-news-article');
    }

    public function create(User $user): bool
    {
        return $user->can('create-news-article');
    }

    public function update(User $user, NewsArticle $newsArticle): bool
    {
        return $user->can('update-news-article');
    }

    public function delete(User $user, NewsArticle $newsArticle): bool
    {
        return $user->can('delete-news-article');
    }

    public function restore(User $user, NewsArticle $newsArticle): bool
    {
        return $user->can('restore-news-article');
    }

    public function forceDelete(User $user, NewsArticle $newsArticle): bool
    {
        return $user->can('force-delete-news-article');
    }
}
